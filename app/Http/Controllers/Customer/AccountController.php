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

    public function showOrder($orderId)
    {
        $query = Order::with(['items.product.category', 'items.product.brand', 'coupon', 'feedback']);

        if (is_numeric($orderId)) {
            $query->where(function ($q) use ($orderId) {
                $q->where('id', $orderId)->orWhere('order_number', $orderId);
            });
        } else {
            $query->where('order_number', $orderId);
        }

        $order = $query->first();

        if (!$order || $order->user_id !== Auth::id()) {
            return redirect()->route('account.orders.index')->with('error', 'The requested order was not found in your account.');
        }

        $recommendedProducts = \Illuminate\Support\Facades\Cache::remember('recommended_products', 600, function () {
            $products = \App\Models\Product::with(['category', 'brand'])->where('status', 'Active')->inRandomOrder()->take(4)->get();
            return app(\App\Services\OfferService::class)->applyOfferDiscountsToProducts($products);
        });

        return view('customer.account.order_details', compact('order', 'recommendedProducts'));
    }

    public function invoice($orderId)
    {
        $query = Order::with(['user', 'items.product', 'coupon']);

        if (is_numeric($orderId)) {
            $query->where(function ($q) use ($orderId) {
                $q->where('id', $orderId)->orWhere('order_number', $orderId);
            });
        } else {
            $query->where('order_number', $orderId);
        }

        $order = $query->first();

        if (!$order) {
            return redirect()->route('account.orders.index')->with('error', 'Invoice not found.');
        }

        if (Auth::check() && $order->user_id !== Auth::id()) {
            return redirect()->route('account.orders.index')->with('error', 'Unauthorized access to this order invoice.');
        }

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
