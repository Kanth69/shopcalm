<?php

namespace App\Providers;

use App\Services\CartService;
use App\Services\SearchService;
use App\Models\User;
use App\Policies\UserPolicy;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(SearchService::class, function ($app) {
            return new SearchService();
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Define Gates for Role-based Authorization
        Gate::define('manage-admins', function (User $user) {
            return $user->isSuperAdmin();
        });

        // Register User Policy
        Gate::policy(User::class, UserPolicy::class);

        View::composer('customer.*', function ($view) {
            $cartService = app(CartService::class);
            $cart = $cartService->getCart();
            $cartItemMap = []; // product_id => [item_id, quantity]

            if ($cart) {
                foreach ($cart->items as $item) {
                    $cartItemMap[$item->product_id] = [
                        'id' => $item->id,
                        'qty' => $item->quantity
                    ];
                }
            }

            $wishlistedProductIds = [];
            if (Auth::check() && Auth::user()->wishlist) {
                $wishlistedProductIds = Auth::user()->wishlist->items->pluck('product_id')->toArray();
            }

            $menuCategories = \Illuminate\Support\Facades\Cache::remember('menu_categories', 3600, function() {
                return \App\Models\Category::where('status', 'Active')->orderBy('name')->take(8)->get();
            });

            $menuBrands = \Illuminate\Support\Facades\Cache::remember('menu_brands', 3600, function() {
                return \App\Models\Brand::where('status', 'Active')->orderBy('name')->take(8)->get();
            });

            $view->with([
                'cartItemCount' => $cartService->totalItems(),
                'cartSubtotal' => $cartService->subtotal(),
                'cartItemMap' => $cartItemMap,
                'wishlistedProductIds' => $wishlistedProductIds,
                'menuCategories' => $menuCategories,
                'menuBrands' => $menuBrands,
            ]);
        });
    }
}
