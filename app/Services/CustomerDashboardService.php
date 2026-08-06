<?php

namespace App\Services;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class CustomerDashboardService
{
    public function getDashboardData()
    {
        $user = Auth::user();

        $stats = [
            'orders' => $user->orders()->count(),
            'wishlist' => $user->wishlist ? $user->wishlist->items()->count() : 0,
            'reviews' => $user->reviews()->count(),
            'cart' => app(CartService::class)->totalItems(),
        ];

        $recentOrders = $user->orders()->with('items')->latest()->take(5)->get();
        $recentWishlistItems = $user->wishlist ? $user->wishlist->items()->with('product.category')->latest()->take(4)->get() : collect();
        $recentReviews = $user->reviews()->with('product')->latest()->take(3)->get();

        $recommendedProducts = Cache::remember('recommended_products', 600, function () {
            return \App\Models\Product::with(['category', 'brand'])->where('status', 'Active')->inRandomOrder()->take(4)->get();
        });

        return compact('stats', 'recentOrders', 'recentWishlistItems', 'recentReviews', 'recommendedProducts', 'user');
    }
}
