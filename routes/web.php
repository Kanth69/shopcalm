<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\BrandController;
use App\Http\Controllers\Admin\CouponController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\OrderController as AdminOrderController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\ReviewController;
use App\Http\Controllers\Admin\SettingController;
use App\Http\Controllers\Admin\StockManagementController;
use App\Http\Controllers\Admin\ProfileController as AdminProfileController;
use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\BannerController;
use App\Http\Controllers\Admin\SubscriberController as AdminSubscriberController;
use App\Http\Controllers\Admin\AdminAuthController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\OtpController;
use App\Http\Controllers\Customer\AccountController;
use App\Http\Controllers\Customer\AddressController;
use App\Http\Controllers\Customer\CartController;
use App\Http\Controllers\Customer\CheckoutController;
use App\Http\Controllers\Customer\DashboardController;
use App\Http\Controllers\Customer\OrderController as CustomerOrderController;
use App\Http\Controllers\Customer\ProductReviewController;
use App\Http\Controllers\Customer\ShopController;
use App\Http\Controllers\Customer\WishlistController;
use App\Http\Controllers\Customer\ProfileSetupController;
use App\Http\Controllers\Customer\NewsletterController;
use App\Http\Controllers\Customer\OfferController as CustomerOfferController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\Admin\PageController as AdminPageController;
use App\Http\Controllers\Admin\ContactEnquiryController;
use App\Http\Controllers\Auth\GoogleController;
use Illuminate\Support\Facades\Route;

// ─── 1. STAFF HUB ROUTES ───────────────────────────────────────────────────
$registerStaffRoutes = function () {
    Route::get('/rider', fn() => redirect()->route('delivery.login'))->name('rider.alias');

    // Admin Login / Logout Routes
    Route::middleware('guest.admin:admin')->group(function () {
        Route::get('admin/login', [AdminAuthController::class, 'create'])->name('admin.login');
        Route::post('admin/login', [AdminAuthController::class, 'store']);
    });
    Route::match(['get', 'post'], 'admin/logout', [AdminAuthController::class, 'destroy'])->name('admin.logout');

    // Admin Routes
    Route::middleware(['auth:admin', 'admin'])->prefix('admin')->name('admin.')->group(function () {
        Route::get('/', [AdminDashboardController::class, 'index'])->name('dashboard');
        Route::get('/dashboard/stats', [AdminDashboardController::class, 'stats'])->name('dashboard.stats');

        Route::resource('categories', CategoryController::class)->except(['show']);
        Route::resource('brands', BrandController::class)->except(['show']);
        Route::resource('products', ProductController::class);
        Route::resource('coupons', CouponController::class);
        Route::resource('offers', \App\Http\Controllers\Admin\OfferController::class)->except(['show']);
        Route::resource('banners', BannerController::class)->except(['show']);
        Route::resource('pages', AdminPageController::class)->only(['index', 'edit', 'update']);
        Route::get('enquiries', fn() => redirect()->route('support.enquiries.index'))->name('enquiries.index');
        Route::get('customers', fn() => redirect()->route('support.customers.index'))->name('customers.index');

        Route::resource('roles', RoleController::class);

        Route::get('/profile', [AdminProfileController::class, 'index'])->name('profile.index');
        Route::patch('/profile', [AdminProfileController::class, 'update'])->name('profile.update');
        Route::patch('/profile/password', [AdminProfileController::class, 'updatePassword'])->name('profile.password');
        Route::patch('/profile/avatar', [AdminProfileController::class, 'updateAvatar'])->name('profile.avatar');

        Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
        Route::get('/finance', [\App\Http\Controllers\Admin\FinanceController::class, 'index'])->name('finance.index');

        Route::get('/stock', [StockManagementController::class, 'dashboard'])->name('stock.dashboard');
        Route::prefix('stock')->name('stock.')->group(function () {
            Route::get('/history', [StockManagementController::class, 'history'])->name('history');
            Route::get('/add/{product}', [StockManagementController::class, 'addStockForm'])->name('add-form');
            Route::post('/add/{product}', [StockManagementController::class, 'addStock'])->name('add');
            Route::get('/reduce/{product}', [StockManagementController::class, 'reduceStockForm'])->name('reduce-form');
            Route::post('/reduce/{product}', [StockManagementController::class, 'reduceStock'])->name('reduce');
            Route::get('/adjust/{product}', [StockManagementController::class, 'adjustStockForm'])->name('adjust-form');
            Route::post('/adjust/{product}', [StockManagementController::class, 'adjustStock'])->name('adjust');
            Route::get('/{product}', [StockManagementController::class, 'show'])->name('show');
        });

        Route::post('products/{product}/approve', [ProductController::class, 'approve'])->name('products.approve');
        Route::post('products/{product}/reject', [ProductController::class, 'reject'])->name('products.reject');

        Route::delete('products/gallery/{id}', [ProductController::class, 'deleteGalleryImage'])->name('products.gallery.destroy');

        Route::get('reviews', fn() => redirect()->route('product-manager.reviews.index'))->name('reviews.index');

        // Subscribers Redirect to Support Portal
        Route::get('subscribers', fn() => redirect()->route('support.subscribers.index'))->name('subscribers.index');

        Route::get('orders', [AdminOrderController::class, 'index'])->name('orders.index');
        Route::get('orders/{order}', [AdminOrderController::class, 'show'])->name('orders.show');
        Route::post('orders/{order}/cancel', [AdminOrderController::class, 'cancelOrder'])->name('orders.cancel');
        Route::get('orders/{order}/invoice', [AdminOrderController::class, 'invoice'])->name('orders.invoice');
        Route::get('orders/{order}/packing-slip', [AdminOrderController::class, 'packingSlip'])->name('orders.packing-slip');
        Route::get('live-orders', [\App\Http\Controllers\StaffNotificationController::class, 'liveOrders'])->name('live-orders');

        // Serviceable Pincodes Management
        Route::post('pincodes/bulk-import', [\App\Http\Controllers\Admin\PincodeController::class, 'bulkImport'])->name('pincodes.bulk-import');
        Route::post('pincodes/{pincode}/toggle-serviceable', [\App\Http\Controllers\Admin\PincodeController::class, 'toggleServiceable'])->name('pincodes.toggle-serviceable');
        Route::post('pincodes/{pincode}/toggle-cod', [\App\Http\Controllers\Admin\PincodeController::class, 'toggleCod'])->name('pincodes.toggle-cod');
        Route::resource('pincodes', \App\Http\Controllers\Admin\PincodeController::class)->except(['show']);

        // Customer Wallets & Referral Management
        Route::get('wallets', [\App\Http\Controllers\Admin\WalletController::class, 'index'])->name('wallets.index');
        Route::post('wallets/rules', [\App\Http\Controllers\Admin\WalletController::class, 'updateRules'])->name('wallets.update-rules');
        Route::post('wallets/{wallet}/adjust', [\App\Http\Controllers\Admin\WalletController::class, 'adjust'])->name('wallets.adjust');
        Route::post('wallets/{wallet}/toggle-status', [\App\Http\Controllers\Admin\WalletController::class, 'toggleStatus'])->name('wallets.toggle-status');

        // Customer Refunds Management
        Route::get('refunds', [\App\Http\Controllers\Admin\RefundController::class, 'index'])->name('refunds.index');
        Route::post('refunds/{cancellation}/process', [\App\Http\Controllers\Admin\RefundController::class, 'processRefund'])->name('refunds.process');

        Route::resource('pages', \App\Http\Controllers\Admin\PageController::class)->only(['index', 'edit', 'update']);

        Route::get('settings', [SettingController::class, 'index'])->name('settings.index');
        Route::patch('settings', [SettingController::class, 'update'])->name('settings.update');
    });

    // Dedicated Product Manager Portal Routes
    Route::prefix('product-manager')->name('product-manager.')->group(function () {
        Route::middleware('guest.admin:product_manager')->group(function () {
            Route::get('login', [\App\Http\Controllers\ProductManager\AuthController::class, 'create'])->name('login');
            Route::post('login', [\App\Http\Controllers\ProductManager\AuthController::class, 'store']);
        });

        Route::post('logout', [\App\Http\Controllers\ProductManager\AuthController::class, 'destroy'])->name('logout');

        Route::middleware(['product.manager'])->group(function () {
            Route::get('/', [\App\Http\Controllers\ProductManager\DashboardController::class, 'index']);
            Route::get('dashboard', [\App\Http\Controllers\ProductManager\DashboardController::class, 'index'])->name('dashboard');

            // Products
            Route::get('products/pending', [\App\Http\Controllers\ProductManager\ProductController::class, 'pending'])->name('products.pending');
            Route::get('products/rejected', [\App\Http\Controllers\ProductManager\ProductController::class, 'rejected'])->name('products.rejected');
            Route::post('products/{product}/resubmit', [\App\Http\Controllers\ProductManager\ProductController::class, 'resubmit'])->name('products.resubmit');
            Route::post('products/{product}/toggle-status', [\App\Http\Controllers\ProductManager\ProductController::class, 'toggleStatus'])->name('products.toggle-status');
            Route::delete('products/gallery/{id}', [\App\Http\Controllers\ProductManager\ProductController::class, 'deleteGalleryImage'])->name('products.gallery.destroy');
            Route::resource('products', \App\Http\Controllers\ProductManager\ProductController::class)->except(['destroy']);

            // Categories
            Route::resource('categories', \App\Http\Controllers\ProductManager\CategoryController::class)->except(['destroy', 'show']);

            // Brands
            Route::resource('brands', \App\Http\Controllers\ProductManager\BrandController::class)->except(['destroy', 'show']);

            // Inventory / Stock
            Route::get('stock', [\App\Http\Controllers\ProductManager\StockController::class, 'dashboard'])->name('stock.dashboard');
            Route::get('stock/form/{product}/{action}', [\App\Http\Controllers\ProductManager\StockController::class, 'stockForm'])->name('stock.form');
            Route::post('stock/add/{product}', [\App\Http\Controllers\ProductManager\StockController::class, 'addStock'])->name('stock.add');
            Route::post('stock/reduce/{product}', [\App\Http\Controllers\ProductManager\StockController::class, 'reduceStock'])->name('stock.reduce');
            Route::post('stock/adjust/{product}', [\App\Http\Controllers\ProductManager\StockController::class, 'adjustStock'])->name('stock.adjust');
            Route::get('stock/history', [\App\Http\Controllers\ProductManager\StockController::class, 'history'])->name('stock.history');

            // Product Reviews Moderation
            Route::get('reviews', [\App\Http\Controllers\ProductManager\ReviewController::class, 'index'])->name('reviews.index');
            Route::patch('reviews/{review}', [\App\Http\Controllers\ProductManager\ReviewController::class, 'update'])->name('reviews.update');
            Route::delete('reviews/{review}', [\App\Http\Controllers\ProductManager\ReviewController::class, 'destroy'])->name('reviews.destroy');
            Route::post('reviews/bulk-action', [\App\Http\Controllers\ProductManager\ReviewController::class, 'bulkAction'])->name('reviews.bulk-action');

            // Inventory & Product Reports
            Route::get('reports', [\App\Http\Controllers\ProductManager\ReportController::class, 'index'])->name('reports.index');
        });
    });

    // Dedicated Order Manager Portal Routes
    Route::prefix('order-manager')->name('order-manager.')->group(function () {
        Route::middleware('guest.admin:order_manager')->group(function () {
            Route::get('login', [\App\Http\Controllers\OrderManager\AuthController::class, 'create'])->name('login');
            Route::post('login', [\App\Http\Controllers\OrderManager\AuthController::class, 'store'])->name('login.submit');
        });

        Route::post('logout', [\App\Http\Controllers\OrderManager\AuthController::class, 'destroy'])->name('logout');

        Route::middleware(['order.manager'])->group(function () {
            Route::get('/', [\App\Http\Controllers\OrderManager\DashboardController::class, 'index']);
            Route::get('dashboard', [\App\Http\Controllers\OrderManager\DashboardController::class, 'index'])->name('dashboard');
            Route::get('alerts', [\App\Http\Controllers\OrderManager\DashboardController::class, 'alerts'])->name('alerts');
            Route::get('rider-performance', [\App\Http\Controllers\OrderManager\RiderPerformanceController::class, 'index'])->name('rider-performance.index');
            Route::get('settlements', [\App\Http\Controllers\OrderManager\SettlementController::class, 'index'])->name('settlements.index');
            Route::post('settlements', [\App\Http\Controllers\OrderManager\SettlementController::class, 'store'])->name('settlements.store');
            Route::get('settlements/{settlement}', [\App\Http\Controllers\OrderManager\SettlementController::class, 'show'])->name('settlements.show');

            // Orders Lifecycle Management
            Route::get('orders', [\App\Http\Controllers\OrderManager\OrderController::class, 'index'])->name('orders.index');
            Route::get('orders/{order}', [\App\Http\Controllers\OrderManager\OrderController::class, 'show'])->name('orders.show');
            Route::match(['get', 'post'], 'orders/{order}/status', function (\Illuminate\Http\Request $request, \App\Models\Order $order) {
                if ($request->isMethod('get')) {
                    return redirect()->route('order-manager.orders.show', $order);
                }
                return app(\App\Http\Controllers\OrderManager\OrderController::class)->updateStatus($request, $order);
            })->name('orders.update-status');
            Route::match(['get', 'post'], 'orders/{order}/assign-rider', function (\Illuminate\Http\Request $request, \App\Models\Order $order) {
                if ($request->isMethod('get')) {
                    return redirect()->route('order-manager.orders.show', $order);
                }
                return app(\App\Http\Controllers\OrderManager\OrderController::class)->assignRider($request, $order);
            })->name('orders.assign-rider');
            Route::match(['get', 'post'], 'orders/{order}/tracking', function (\Illuminate\Http\Request $request, \App\Models\Order $order) {
                if ($request->isMethod('get')) {
                    return redirect()->route('order-manager.orders.show', $order);
                }
                return app(\App\Http\Controllers\OrderManager\OrderController::class)->updateTracking($request, $order);
            })->name('orders.update-tracking');
            Route::post('orders/{order}/resolve-exception', [\App\Http\Controllers\OrderManager\OrderController::class, 'resolveException'])->name('orders.resolve-exception');
            Route::get('orders/{order}/manifest', [\App\Http\Controllers\OrderManager\OrderController::class, 'manifest'])->name('orders.manifest');
            Route::get('orders/{order}/invoice', [\App\Http\Controllers\OrderManager\OrderController::class, 'invoice'])->name('orders.invoice');
            Route::get('orders/{order}/packing-slip', [\App\Http\Controllers\OrderManager\OrderController::class, 'packingSlip'])->name('orders.packing-slip');
            Route::get('orders/{order}/shipping-label', [\App\Http\Controllers\OrderManager\OrderController::class, 'shippingLabel'])->name('orders.shipping-label');
            Route::match(['get', 'post'], 'orders/bulk/shipping-labels', [\App\Http\Controllers\OrderManager\OrderController::class, 'bulkShippingLabels'])->name('orders.bulk-shipping-labels');
            Route::post('orders/auto-assign-riders', [\App\Http\Controllers\OrderManager\OrderController::class, 'autoAssignRiders'])->name('orders.auto-assign-riders');
            Route::get('live-orders', [\App\Http\Controllers\StaffNotificationController::class, 'liveOrders'])->name('live-orders');

            // Order Manager Refund Processing
            Route::get('refunds', [\App\Http\Controllers\Admin\RefundController::class, 'index'])->name('refunds.index');
            Route::post('refunds/{cancellation}/process', [\App\Http\Controllers\Admin\RefundController::class, 'processRefund'])->name('refunds.process');
        });
    });

    // Dedicated Customer Support Portal Routes
    Route::prefix('support')->name('support.')->group(function () {
        Route::middleware('guest.admin:support')->group(function () {
            Route::get('login', [\App\Http\Controllers\Support\AuthController::class, 'create'])->name('login');
            Route::post('login', [\App\Http\Controllers\Support\AuthController::class, 'store'])->name('login.submit');
        });

        Route::post('logout', [\App\Http\Controllers\Support\AuthController::class, 'destroy'])->name('logout');

        Route::middleware(['support.agent'])->group(function () {
            Route::get('/', [\App\Http\Controllers\Support\DashboardController::class, 'index']);
            Route::get('dashboard', [\App\Http\Controllers\Support\DashboardController::class, 'index'])->name('dashboard');

            // Customer Inquiries
            Route::get('enquiries', [\App\Http\Controllers\Support\EnquiryController::class, 'index'])->name('enquiries.index');
            Route::post('enquiries/bulk-action', [\App\Http\Controllers\Support\EnquiryController::class, 'bulkAction'])->name('enquiries.bulk-action');
            Route::get('enquiries/{enquiry}', [\App\Http\Controllers\Support\EnquiryController::class, 'show'])->name('enquiries.show');
            Route::patch('enquiries/{enquiry}', [\App\Http\Controllers\Support\EnquiryController::class, 'update'])->name('enquiries.update');
            Route::delete('enquiries/{enquiry}', [\App\Http\Controllers\Support\EnquiryController::class, 'destroy'])->name('enquiries.destroy');

            // Customer 360 Profiles & Account Status Control
            Route::get('customers', [\App\Http\Controllers\Support\CustomerController::class, 'index'])->name('customers.index');
            Route::get('customers/{customer}', [\App\Http\Controllers\Support\CustomerController::class, 'show'])->name('customers.show');
            Route::post('customers/{customer}/toggle-status', [\App\Http\Controllers\Support\CustomerController::class, 'toggleStatus'])->name('customers.toggle-status');

            // Newsletter Subscribers Directory & CSV Export
            Route::get('subscribers/export', [\App\Http\Controllers\Support\SubscriberController::class, 'exportCsv'])->name('subscribers.export');
            Route::post('subscribers/bulk-action', [\App\Http\Controllers\Support\SubscriberController::class, 'bulkAction'])->name('subscribers.bulk-action');
            Route::patch('subscribers/{subscriber}/toggle', [\App\Http\Controllers\Support\SubscriberController::class, 'toggleStatus'])->name('subscribers.toggle');
            Route::resource('subscribers', \App\Http\Controllers\Support\SubscriberController::class)->only(['index', 'destroy']);
        });
    });

    // Dedicated Delivery Partner / Rider Portal Routes
    Route::prefix('delivery')->name('delivery.')->group(function () {
        Route::middleware('guest.admin:delivery_partner')->group(function () {
            Route::get('login', [\App\Http\Controllers\Delivery\DeliveryDashboardController::class, 'login'])->name('login');
            Route::post('login', [\App\Http\Controllers\Delivery\DeliveryDashboardController::class, 'authenticate'])->name('login.submit');
        });

        Route::post('logout', [\App\Http\Controllers\Delivery\DeliveryDashboardController::class, 'logout'])->name('logout');

        Route::middleware(['delivery.partner'])->group(function () {
            Route::get('/', [\App\Http\Controllers\Delivery\DeliveryDashboardController::class, 'index']);
            Route::get('dashboard', [\App\Http\Controllers\Delivery\DeliveryDashboardController::class, 'index'])->name('dashboard');
            Route::get('completed', [\App\Http\Controllers\Delivery\DeliveryDashboardController::class, 'completed'])->name('completed');
            Route::get('settlements', [\App\Http\Controllers\Delivery\DeliveryDashboardController::class, 'settlements'])->name('settlements');
            Route::get('orders/{order}', [\App\Http\Controllers\Delivery\DeliveryDashboardController::class, 'show'])->name('orders.show');
            Route::get('orders/{order}/check-payment', [\App\Http\Controllers\Delivery\DeliveryDashboardController::class, 'checkPayment'])->name('orders.check-payment');
            Route::post('orders/{order}/confirm-doorstep-upi', [\App\Http\Controllers\Delivery\DeliveryDashboardController::class, 'confirmDoorstepUpi'])->name('orders.confirm-doorstep-upi');
            Route::post('orders/{order}/complete', [\App\Http\Controllers\Delivery\DeliveryDashboardController::class, 'completeDelivery'])->name('orders.complete');
            Route::post('orders/{order}/report-issue', [\App\Http\Controllers\Delivery\DeliveryDashboardController::class, 'reportIssue'])->name('orders.report-issue');
        });
    });
};

// ─── 2. STOREFRONT CUSTOMER ROUTES ───────────────────────────────────────────
$registerCustomerRoutes = function () {
    Route::get('/', [ShopController::class, 'home'])->name('home');
    Route::get('/shop', [ShopController::class, 'shop'])->name('shop');
    Route::get('/offers', [CustomerOfferController::class, 'index'])->name('offers.index');
    Route::get('/shop/brands-by-category', [ShopController::class, 'getBrandsByCategory'])->name('shop.brands-by-category');
    Route::get('/product/{product:slug}', [ShopController::class, 'productDetails'])->name('product.show');
    Route::get('/categories', [ShopController::class, 'categories'])->name('categories.index');
    Route::get('/brands', [ShopController::class, 'brands'])->name('brands.index');
    Route::get('/category/{category:slug}', [ShopController::class, 'categoryProducts'])->name('category.products');
    Route::get('/brand/{brand:slug}', [ShopController::class, 'brandProducts'])->name('brand.products');

    // Cart Routes
    Route::get('/cart', [CartController::class, 'index'])->name('cart.index');
    Route::post('/cart/add', [CartController::class, 'add'])->name('cart.add');
    Route::patch('/cart/update/{itemId}', [CartController::class, 'update'])->name('cart.update');
    Route::post('/cart/toggle-select/{itemId}', [CartController::class, 'toggleSelect'])->name('cart.toggle-select');
    Route::post('/cart/toggle-select-all', [CartController::class, 'toggleSelectAll'])->name('cart.toggle-select-all');
    Route::delete('/cart/remove/{itemId}', [CartController::class, 'remove'])->name('cart.remove');
    Route::post('/cart/move-to-wishlist/{itemId}', [CartController::class, 'moveToWishlist'])->name('cart.move-to-wishlist');
    Route::delete('/cart/clear-all', [CartController::class, 'clear'])->name('cart.clear');

    // Delivery & Pincode Checker Routes
    Route::post('/delivery/check-pincode', [\App\Http\Controllers\Customer\DeliveryController::class, 'checkPincode'])->name('delivery.check');
    Route::post('/delivery/save-location', [\App\Http\Controllers\Customer\DeliveryController::class, 'saveLocation'])->name('delivery.save');
    Route::get('/delivery/saved-addresses', [\App\Http\Controllers\Customer\DeliveryController::class, 'getSavedAddresses'])->name('delivery.addresses');

    // Static Pages
    Route::get('/about-us', [PageController::class, 'show'])->defaults('slug', 'about-us')->name('page.about');
    Route::get('/contact-us', [ContactController::class, 'show'])->name('page.contact');
    Route::get('/contact', fn() => redirect()->route('page.contact'))->name('contact');
    Route::post('/contact-us', [ContactController::class, 'submit'])->name('page.contact.submit');
    Route::get('/faq', [PageController::class, 'show'])->defaults('slug', 'faq')->name('page.faq');
    Route::get('/shipping-policy', [PageController::class, 'show'])->defaults('slug', 'shipping-policy')->name('page.shipping');
    Route::get('/return-refund-policy', [PageController::class, 'show'])->defaults('slug', 'return-refund-policy')->name('page.return');
    Route::get('/orders/{order}/tax-invoice', [\App\Http\Controllers\Customer\AccountController::class, 'publicInvoice'])->name('orders.public-invoice');
    Route::get('/cancellation-policy', [PageController::class, 'show'])->defaults('slug', 'cancellation-policy')->name('page.cancellation');
    Route::get('/terms-and-conditions', [PageController::class, 'show'])->defaults('slug', 'terms-and-conditions')->name('page.terms');
    Route::get('/privacy-policy', [PageController::class, 'show'])->defaults('slug', 'privacy-policy')->name('page.privacy');
    Route::get('/cookie-policy', [PageController::class, 'show'])->defaults('slug', 'cookie-policy')->name('page.cookie');
    Route::get('/disclaimer', [PageController::class, 'show'])->defaults('slug', 'disclaimer')->name('page.disclaimer');

    // Newsletter Routes
    Route::post('/newsletter/subscribe', [NewsletterController::class, 'subscribe'])->name('newsletter.subscribe');
    Route::middleware(['auth:customer'])->post('/newsletter/toggle', [NewsletterController::class, 'toggle'])->name('newsletter.toggle');

    // Profile Setup
    Route::middleware(['auth:customer'])->prefix('account/setup')->name('profile.setup')->group(function () {
        Route::get('/', [ProfileSetupController::class, 'show']);
        Route::post('/update', [ProfileSetupController::class, 'update'])->name('.update');
        Route::post('/skip', [ProfileSetupController::class, 'skip'])->name('.skip');
    });

    Route::get('/dashboard', [DashboardController::class, 'index'])->middleware(['auth:customer', 'profile.setup'])->name('dashboard');

    Route::middleware(['auth:customer', 'profile.setup'])->group(function () {
        Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
        Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
        Route::get('/profile/export-data', [ProfileController::class, 'exportData'])->name('profile.export');
        Route::delete('/profile/delete-account', [ProfileController::class, 'destroy'])->name('profile.destroy');

        // Wishlist Routes
        Route::get('/wishlist', [WishlistController::class, 'index'])->name('wishlist.index');
        Route::post('/wishlist/add', [WishlistController::class, 'add'])->name('wishlist.add');
        Route::delete('/wishlist/remove/{wishlistItem}', [WishlistController::class, 'remove'])->name('wishlist.remove');
        Route::post('/wishlist/move-to-cart/{wishlistItem}', [WishlistController::class, 'moveToCart'])->name('wishlist.move-to-cart');

        // Review Routes
        Route::post('/products/{product}/reviews', [ProductReviewController::class, 'store'])->name('reviews.store');
        Route::delete('/reviews/{review}', [ProductReviewController::class, 'destroy'])->name('reviews.destroy');

        // Checkout Routes
        Route::get('/checkout', [CheckoutController::class, 'index'])->name('checkout.index');
        Route::post('/checkout/save-address', [CheckoutController::class, 'saveAddress'])->name('checkout.save-address');
        Route::post('/checkout/apply-coupon', [CheckoutController::class, 'applyCoupon'])->name('checkout.apply-coupon');
        Route::post('/checkout/remove-coupon', [CheckoutController::class, 'removeCoupon'])->name('checkout.remove-coupon');
        Route::post('/checkout/toggle-wallet', [CheckoutController::class, 'toggleWallet'])->name('checkout.toggle-wallet');
        Route::post('/checkout', [CheckoutController::class, 'placeOrder'])->name('checkout.place-order');
        Route::post('/checkout/razorpay/verify', [CheckoutController::class, 'verifyRazorpayPayment'])->name('checkout.razorpay.verify');
        Route::get('/order-success/{order}', [CheckoutController::class, 'success'])->name('checkout.success');
        Route::get('/checkout/payment-failed/{order}', [CheckoutController::class, 'paymentFailed'])->name('checkout.payment_failed');
        Route::post('/checkout/orders/{order}/retry-payment', [CheckoutController::class, 'retryPayment'])->name('checkout.retry_payment');
        Route::post('/checkout/orders/{order}/switch-to-cod', [CheckoutController::class, 'switchToCod'])->name('checkout.switch_to_cod');

        // Customer Account
        Route::prefix('account')->name('account.')->group(function () {
            Route::get('/orders', [CustomerOrderController::class, 'index'])->name('orders.index');
            Route::get('/orders/{order}/cancellation-summary', [CustomerOrderController::class, 'cancellationSummary'])->name('orders.cancellation-summary');
            Route::post('/orders/{order}/cancel', [CustomerOrderController::class, 'cancel'])->name('orders.cancel');
            Route::get('/orders/{order}', [AccountController::class, 'showOrder'])->name('orders.show');
            Route::post('/orders/{order}/feedback', [\App\Http\Controllers\Customer\OrderFeedbackController::class, 'store'])->name('orders.feedback.store');
            Route::get('/orders/{order}/invoice', [AccountController::class, 'invoice'])->name('orders.invoice');
            Route::get('/wallet', [\App\Http\Controllers\Customer\WalletController::class, 'index'])->name('wallet');
            Route::get('/reviews', [AccountController::class, 'reviews'])->name('reviews');
            Route::get('/change-password', [AccountController::class, 'changePassword'])->name('change-password');
            Route::resource('addresses', AddressController::class);
        });
    });

    // Unified Auth Routes
    Route::post('/auth/check-user', [OtpController::class, 'checkUser'])->name('auth.check');
    Route::post('/auth/send-otp', [OtpController::class, 'sendOtp'])->name('auth.otp.send');
    Route::post('/auth/verify-otp', [OtpController::class, 'verifyOtp'])->name('auth.otp.verify');

    // Webhook Routes (Exempt from Session Auth)
    Route::post('/checkout/razorpay/webhook', [\App\Http\Controllers\Customer\CheckoutController::class, 'razorpayWebhook'])->name('checkout.razorpay.webhook');

    require __DIR__.'/auth.php';
};

// ─── 3. REGISTER ALL ROUTES DEFINITIONS ─────────────────────────────────────
$registerStaffRoutes();
$registerCustomerRoutes();

// ─── EMAIL TEST ROUTE ───────────────────────────────────────────
Route::get('/test-email', function () {
    $to      = config('services.brevo.sender_email');
    $subject = 'WiseKart Test Email';
    $html    = '<h2 style="font-family:sans-serif;">Hello from WiseKart 👋</h2><p style="font-family:sans-serif;">Brevo integration is working successfully.</p>';
    $text    = "Hello from WiseKart.\nBrevo integration is working successfully.";

    app(\App\Services\EmailService::class)->sendEmail($to, $subject, $html, $text);

    return response()->json([
        'status'  => 'sent',
        'message' => "Test email dispatched to {$to}.",
    ]);
});
