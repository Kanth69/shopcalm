<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\ContactEnquiry;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AccountController extends Controller
{
    public function orders()
    {
        $user   = Auth::guard('customer')->user() ?? Auth::user();
        $orders = $user->orders()->with('items.product')->latest()->paginate(10);

        $totalCount     = $user->orders()->count();
        $deliveredCount = $user->orders()->where('status', 'delivered')->count();
        $activeCount    = $user->orders()->whereNotIn('status', ['delivered', 'cancelled'])->count();

        $recommendedProducts = \Illuminate\Support\Facades\Cache::remember('recommended_products', 600, function () {
            $products = \App\Models\Product::with(['category', 'brand'])->where('status', 'Active')->inRandomOrder()->take(4)->get();
            return app(\App\Services\OfferService::class)->applyOfferDiscountsToProducts($products);
        });

        return view('customer.account.orders', compact('orders', 'recommendedProducts', 'totalCount', 'deliveredCount', 'activeCount'));
    }

    public function showOrder(Request $request, Order $order)
    {
        $user = Auth::guard('customer')->user() ?? Auth::user();

        if (!$user || (int)$order->user_id !== (int)$user->id) {
            return redirect()->route('account.orders.index')->with('error', 'The requested order was not found in your account.');
        }

        // If redirected from Razorpay retry callback with payment parameters, verify and mark order paid
        if ($order->payment_status !== 'paid' && $request->filled(['razorpay_payment_id', 'razorpay_order_id', 'razorpay_signature'])) {
            $razorpay = app(\App\Services\RazorpayService::class);
            if ($razorpay->verifyPaymentSignature($request->razorpay_order_id, $request->razorpay_payment_id, $request->razorpay_signature)) {
                $order = app(\App\Services\CheckoutService::class)->markOrderPaid($order, [
                    'gateway'             => 'razorpay',
                    'gateway_order_id'    => $request->razorpay_order_id,
                    'gateway_payment_id'  => $request->razorpay_payment_id,
                    'payment_method_group'=> 'online',
                    'bank_reference'      => $request->razorpay_payment_id,
                    'payment_time'        => now(),
                    'gateway_message'     => 'Razorpay Retry Payment Verified Successfully',
                ], $user);
                return redirect()->route('checkout.success', $order);
            }
        }

        $order->load(['items.product.category', 'items.product.brand', 'coupon', 'feedback', 'cancellation', 'statusHistories']);

        $recommendedProducts = \Illuminate\Support\Facades\Cache::remember('recommended_products', 600, function () {
            $products = \App\Models\Product::with(['category', 'brand'])->where('status', 'Active')->inRandomOrder()->take(4)->get();
            return app(\App\Services\OfferService::class)->applyOfferDiscountsToProducts($products);
        });

        return view('customer.account.order_details', compact('order', 'recommendedProducts'));
    }

    public function invoice(Order $order)
    {
        $user = Auth::guard('customer')->user() ?? Auth::user();

        if ($user && (int)$order->user_id !== (int)$user->id) {
            return redirect()->route('account.orders.index')->with('error', 'Unauthorized access to this order invoice.');
        }

        $order->load(['user', 'items.product', 'coupon']);
        return view('order-manager.orders.invoice', compact('order'));
    }

    public function publicInvoice($orderParam)
    {
        $order = Order::where('id', $orderParam)
            ->orWhere('order_number', $orderParam)
            ->firstOrFail();
        $order->load(['user', 'items.product', 'coupon']);
        return view('order-manager.orders.invoice', compact('order'));
    }

    public function reviews()
    {
        $reviews = Auth::user()->reviews()->with('product')->latest()->paginate(10);
        return view('customer.account.reviews', compact('reviews'));
    }

    public function addresses()
    {
        return view('customer.account.addresses');
    }

    public function changePassword()
    {
        return view('customer.account.change_password');
    }

    public function tickets(Request $request)
    {
        $user = Auth::guard('customer')->user() ?? Auth::user();
        $email = $user?->email;
        $mobile = $user?->mobile_number ?? $user?->phone;

        $cleanMobile = $mobile ? preg_replace('/\D/', '', $mobile) : null;
        if ($cleanMobile && strlen($cleanMobile) > 10) {
            $cleanMobile = substr($cleanMobile, -10);
        }

        $baseQuery = ContactEnquiry::query()
            ->with('resolver')
            ->where(function ($q) use ($email, $mobile, $cleanMobile) {
                $hasCond = false;
                if (!empty($email)) {
                    $q->where('email', $email);
                    $hasCond = true;
                }
                if (!empty($mobile)) {
                    $hasCond ? $q->orWhere('mobile', $mobile) : $q->where('mobile', $mobile);
                    $hasCond = true;
                }
                if (!empty($cleanMobile)) {
                    $hasCond ? $q->orWhere('mobile', 'LIKE', '%' . $cleanMobile . '%') : $q->where('mobile', 'LIKE', '%' . $cleanMobile . '%');
                    $hasCond = true;
                }
                if (!$hasCond) {
                    $q->whereRaw('1 = 0');
                }
            });

        $filter = $request->query('status');
        $query = clone $baseQuery;
        if ($filter === 'open') {
            $query->where(function ($q) {
                $q->whereIn('status', ['unread', 'in_progress'])
                  ->orWhereNull('status');
            });
        } elseif ($filter === 'resolved') {
            $query->whereIn('status', ['resolved', 'closed']);
        }

        $tickets = $query->latest()->paginate(10)->withQueryString();

        $totalTickets = (clone $baseQuery)->count();
        $openTickets = (clone $baseQuery)->where(function ($q) {
            $q->whereIn('status', ['unread', 'in_progress'])
              ->orWhereNull('status');
        })->count();
        $resolvedTickets = (clone $baseQuery)->whereIn('status', ['resolved', 'closed'])->count();

        return view('customer.account.tickets', compact('tickets', 'totalTickets', 'openTickets', 'resolvedTickets', 'user', 'filter'));
    }
}
