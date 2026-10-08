<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
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
}
