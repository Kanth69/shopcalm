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
        $user   = Auth::user();
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

    public function showOrder(Order $order)
    {
        $user = Auth::guard('customer')->user() ?? Auth::user();

        if (!$user || (int)$order->user_id !== (int)$user->id) {
            return redirect()->route('account.orders.index')->with('error', 'The requested order was not found in your account.');
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

    public function publicInvoice(Order $order)
    {
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
