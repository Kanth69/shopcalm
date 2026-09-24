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
        if ($this->app->environment('production') || config('app.env') === 'production') {
            \Illuminate\Support\Facades\URL::forceScheme('https');
        }

        // Define Gates for Role-based Authorization
        Gate::define('manage-admins', function (?User $user = null) {
            $user = $user ?? auth('admin')->user() ?? auth()->user();
            return $user && $user->isSuperAdmin();
        });

        Gate::define('approve-products', function (?User $user = null) {
            $user = $user ?? auth('admin')->user() ?? auth()->user();
            return $user && $user->isAdmin();
        });

        Gate::define('access-product-manager', function (?User $user = null) {
            $user = $user ?? auth('product_manager')->user() ?? auth('admin')->user() ?? auth()->user();
            return $user && $user->canAccessProductManagerPortal();
        });

        Gate::define('access-order-manager', function (?User $user = null) {
            $user = $user ?? auth('order_manager')->user() ?? auth('admin')->user() ?? auth()->user();
            return $user && $user->canAccessOrderManagerPortal();
        });

        Gate::define('access-support', function (?User $user = null) {
            $user = $user ?? auth('support')->user() ?? auth('admin')->user() ?? auth()->user();
            return $user && $user->canAccessSupportPortal();
        });

        Gate::define('access-admin', function (?User $user = null) {
            $user = $user ?? auth('admin')->user() ?? auth()->user();
            return $user && $user->canAccessAdminPortal();
        });

        // Register User Policy
        Gate::policy(User::class, UserPolicy::class);

        // Share dynamic Store Name globally across all views
        View::composer('*', function ($view) {
            $view->with('storeName', \App\Models\Setting::get('store_name', config('app.name', 'ShopCalm')));
        });

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

        // View composer for Product Manager portal alerts & badges
        View::composer('product-manager.*', function ($view) {
            $pmPendingApprovals = \App\Models\Product::where('status', 'Pending_Approval')->count();
            $pmRejectedProducts = \App\Models\Product::where('status', 'Rejected')->count();
            $pmLowStockCount    = \App\Models\Product::where('stock', '<=', 10)->count();
            $pmPendingReviews   = \App\Models\ProductReview::where('status', 'Pending')->count();

            $view->with([
                'pmPendingApprovals' => $pmPendingApprovals,
                'pmRejectedProducts' => $pmRejectedProducts,
                'pmLowStockCount'    => $pmLowStockCount,
                'pmPendingReviews'   => $pmPendingReviews,
            ]);
        });
    }
}
