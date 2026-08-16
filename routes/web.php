<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\BrandController;
use App\Http\Controllers\Admin\CouponController;
use App\Http\Controllers\Admin\CustomerController as AdminCustomerController;
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

// ─── Email Test Route (dev only) ───────────────────────────────────────────
Route::get('/test-email', function () {
    $to      = config('services.brevo.sender_email'); // send to yourself for testing
    $subject = 'WiseKart Test Email';
    $html    = '<h2 style="font-family:sans-serif;">Hello from WiseKart 👋</h2><p style="font-family:sans-serif;">Brevo integration is working successfully.</p>';
    $text    = "Hello from WiseKart.\nBrevo integration is working successfully.";

    \App\Jobs\SendBrevoEmailJob::dispatch($to, $subject, $html, $text);

    return response()->json([
        'status'  => 'queued',
        'message' => "Test email queued for delivery to {$to}. Run 'php artisan queue:work' to process.",
    ]);
});

// Storefront Routes
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
Route::delete('/cart/remove/{itemId}', [CartController::class, 'remove'])->name('cart.remove');
Route::delete('/cart/clear-all', [CartController::class, 'clear'])->name('cart.clear');

// Static Pages
Route::get('/about-us', [PageController::class, 'show'])->defaults('slug', 'about-us')->name('page.about');
Route::get('/contact-us', [ContactController::class, 'show'])->name('page.contact');
Route::post('/contact-us', [ContactController::class, 'submit'])->name('page.contact.submit');
Route::get('/faq', [PageController::class, 'show'])->defaults('slug', 'faq')->name('page.faq');
Route::get('/shipping-policy', [PageController::class, 'show'])->defaults('slug', 'shipping-policy')->name('page.shipping');
Route::get('/return-refund-policy', [PageController::class, 'show'])->defaults('slug', 'return-refund-policy')->name('page.return');
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
    Route::post('/checkout', [CheckoutController::class, 'placeOrder'])->name('checkout.place-order');
    Route::get('/order-success/{order}', [CheckoutController::class, 'success'])->name('checkout.success');

    // Customer Account
    Route::prefix('account')->name('account.')->group(function () {
        Route::get('/orders', [CustomerOrderController::class, 'index'])->name('orders.index');
        Route::get('/orders/{order}', [AccountController::class, 'showOrder'])->name('orders.show');
        Route::get('/reviews', [AccountController::class, 'reviews'])->name('reviews');
        Route::get('/change-password', [AccountController::class, 'changePassword'])->name('change-password');
        Route::resource('addresses', AddressController::class);
    });
});

// Admin Login / Logout Routes
Route::middleware('guest.admin')->group(function () {
    Route::get('admin/login', [AdminAuthController::class, 'create'])->name('admin.login');
    Route::post('admin/login', [AdminAuthController::class, 'store']);
});
Route::match(['get', 'post'], 'admin/logout', [AdminAuthController::class, 'destroy'])->name('admin.logout');

Route::get('/auth/google', [GoogleController::class, 'redirectToGoogle'])->name('google.redirect');
Route::get('/auth/google/callback', [GoogleController::class, 'handleGoogleCallback'])->name('google.callback');

// Unified Auth Routes
Route::post('/auth/check-user', [OtpController::class, 'checkUser'])->name('auth.check');
Route::post('/auth/send-otp', [OtpController::class, 'sendOtp'])->name('auth.otp.send');
Route::post('/auth/verify-otp', [OtpController::class, 'verifyOtp'])->name('auth.otp.verify');

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
    Route::post('enquiries/bulk-destroy', [ContactEnquiryController::class, 'bulkDestroy'])->name('enquiries.bulk-destroy');
    Route::resource('enquiries', ContactEnquiryController::class)->only(['index', 'show', 'destroy']);

    Route::post('customers/{customer}/toggle-status', [AdminCustomerController::class, 'toggleStatus'])->name('customers.toggle-status');
    Route::resource('customers', AdminCustomerController::class)->only(['index', 'show', 'destroy']);

    Route::resource('roles', RoleController::class);

    Route::get('/profile', [AdminProfileController::class, 'index'])->name('profile.index');
    Route::patch('/profile', [AdminProfileController::class, 'update'])->name('profile.update');
    Route::patch('/profile/password', [AdminProfileController::class, 'updatePassword'])->name('profile.password');
    Route::patch('/profile/avatar', [AdminProfileController::class, 'updateAvatar'])->name('profile.avatar');

    Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');

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

    Route::delete('products/gallery/{id}', [ProductController::class, 'deleteGalleryImage'])->name('products.gallery.destroy');

    Route::get('reviews', [ReviewController::class, 'index'])->name('reviews.index');
    Route::post('reviews/bulk-action', [ReviewController::class, 'bulkAction'])->name('reviews.bulk-action');
    Route::patch('reviews/{review}', [ReviewController::class, 'update'])->name('reviews.update');
    Route::delete('reviews/{review}', [ReviewController::class, 'destroy'])->name('reviews.destroy');

    // Subscribers Routes
    Route::get('subscribers', [AdminSubscriberController::class, 'index'])->name('subscribers.index');
    Route::post('subscribers/bulk-action', [AdminSubscriberController::class, 'bulkAction'])->name('subscribers.bulk-action');
    Route::delete('subscribers/{subscriber}', [AdminSubscriberController::class, 'destroy'])->name('subscribers.destroy');

    Route::get('orders', [AdminOrderController::class, 'index'])->name('orders.index');
    Route::get('orders/{order}', [AdminOrderController::class, 'show'])->name('orders.show');
    Route::post('orders/{order}/update-status', [AdminOrderController::class, 'updateStatus'])->name('orders.update-status');

    Route::get('settings', [SettingController::class, 'index'])->name('settings.index');
    Route::patch('settings', [SettingController::class, 'update'])->name('settings.update');
});

require __DIR__.'/auth.php';
