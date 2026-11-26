<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\HomeController;
use App\Http\Controllers\Api\V1\ProductController;
use App\Http\Controllers\Api\V1\CartController;
use App\Http\Controllers\Api\V1\CheckoutController;
use App\Http\Controllers\Api\V1\AccountController;
use App\Http\Controllers\Api\V1\WishlistController;

/*
|--------------------------------------------------------------------------
| API V1 Routes — ShopCalm Mobile Apps
|--------------------------------------------------------------------------
*/

// Standard Razorpay Checkout API Endpoints (/api/create-order & /api/verify-payment)
Route::post('/create-order', [CheckoutController::class, 'createRazorpayOrder']);
Route::post('/verify-payment', [CheckoutController::class, 'verifyPayment']);

Route::prefix('v1')->group(function () {

    // ── 1. AUTHENTICATION & PROFILE ENDPOINTS ──
    Route::prefix('auth')->group(function () {
        Route::post('/check-user', [AuthController::class, 'checkUser'])->middleware('throttle:20,1');
        Route::post('/send-otp', [AuthController::class, 'sendOtp'])->middleware('throttle:6,1');
        Route::post('/send-whatsapp-otp', [AuthController::class, 'sendOtp'])->middleware('throttle:6,1');
        Route::post('/verify-otp', [AuthController::class, 'verifyOtp'])->middleware('throttle:10,1');
        Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:10,1');
        Route::post('/register', [AuthController::class, 'register'])->middleware('throttle:10,1');
        Route::post('/reset-password', [AuthController::class, 'resetPassword'])->middleware('throttle:5,1');

        // Protected Auth Routes (Requires Sanctum Bearer Token)
        Route::middleware('auth:sanctum')->group(function () {
            Route::get('/user', [AuthController::class, 'user']);
            Route::get('/export-data', [AuthController::class, 'exportData']);
            Route::post('/device-token', [AuthController::class, 'registerDeviceToken']);
            Route::post('/logout', [AuthController::class, 'logout']);
            Route::post('/delete-account', [AuthController::class, 'deleteAccount']);
        });
    });

    // ── 2. CATALOG & DISCOVERY ENDPOINTS (Public) ──
    Route::get('/home', [HomeController::class, 'index']);
    Route::get('/offers', [\App\Http\Controllers\Api\V1\OfferController::class, 'index']);
    Route::get('/products', [ProductController::class, 'index']);
    Route::get('/search/suggestions', [ProductController::class, 'suggestions']);
    Route::get('/products/suggestions', [ProductController::class, 'suggestions']);
    Route::get('/products/{slug}', [ProductController::class, 'show']);
    Route::post('/delivery/check-pincode', [CheckoutController::class, 'checkPincode']);
    Route::get('/config/map', [CheckoutController::class, 'getMapConfig']);
    Route::get('/config/settings', [HomeController::class, 'getStoreSettings']);
    Route::post('/contact', [\App\Http\Controllers\ContactController::class, 'submit']);
    Route::get('/tickets', [AccountController::class, 'tickets']);

    // ── 3. CART ENDPOINTS ──
    Route::prefix('cart')->group(function () {
        Route::get('/', [CartController::class, 'index']);
        Route::post('/add', [CartController::class, 'add']);
        Route::patch('/update/{itemId}', [CartController::class, 'update']);
        Route::delete('/remove/{itemId}', [CartController::class, 'remove']);
        Route::delete('/clear', [CartController::class, 'clear']);
        Route::patch('/toggle-select/{itemId}', [CartController::class, 'toggleSelect']);
        Route::patch('/toggle-select-all', [CartController::class, 'toggleSelectAll']);
        Route::get('/validate-stock', [CartController::class, 'validateStock']);
    });

    // ── 4. CHECKOUT & ACCOUNT (Protected Sanctum Routes) ──
    Route::middleware('auth:sanctum')->group(function () {

        Route::prefix('checkout')->group(function () {
            Route::post('/validate', [CheckoutController::class, 'validateCheckout'])->middleware('throttle:30,1');
            Route::post('/apply-coupon', [CheckoutController::class, 'applyCoupon'])->middleware('throttle:15,1');
            Route::post('/create-order', [CheckoutController::class, 'createRazorpayOrder'])->middleware('throttle:10,1');
            Route::post('/place-order', [CheckoutController::class, 'placeOrder'])->middleware('throttle:10,1');
            Route::post('/verify-payment', [CheckoutController::class, 'verifyPayment'])->middleware('throttle:15,1');
            Route::post('/orders/{order}/switch-to-cod', [CheckoutController::class, 'switchToCod'])->middleware('throttle:10,1');
            Route::post('/orders/{order}/retry-payment', [CheckoutController::class, 'retryPayment'])->middleware('throttle:10,1');
        });

        Route::prefix('account')->group(function () {
            Route::get('/orders', [AccountController::class, 'orders']);
            Route::get('/orders/{order}', [AccountController::class, 'orderDetails']);
            Route::get('/orders/{order}/cancellation-summary', [AccountController::class, 'cancellationSummary']);
            Route::post('/orders/{order}/cancel', [AccountController::class, 'cancelOrder']);
            Route::post('/orders/{order}/delivery-decision', [AccountController::class, 'deliveryDecision']);
            Route::post('/orders/{order}/feedback', [AccountController::class, 'orderFeedback']);
            Route::post('/orders/{order}/reorder', [AccountController::class, 'reorder']);
            Route::get('/orders/{order}/status-poll', [AccountController::class, 'statusPoll']);
            Route::get('/wallet', [AccountController::class, 'wallet']);
            Route::get('/addresses', [AccountController::class, 'addresses']);
            Route::post('/addresses', [AccountController::class, 'storeAddress']);
            Route::patch('/addresses/{address}', [AccountController::class, 'updateAddress']);
            Route::delete('/addresses/{address}', [AccountController::class, 'deleteAddress']);
            Route::post('/profile', [AccountController::class, 'updateProfile']);
            Route::post('/change-password', [AccountController::class, 'changePassword']);
            Route::get('/tickets', [AccountController::class, 'tickets']);
        });

        // Wishlist
        Route::prefix('wishlist')->group(function () {
            Route::get('/', [WishlistController::class, 'index']);
            Route::post('/toggle', [WishlistController::class, 'toggle']);
            Route::delete('/remove/{productId}', [WishlistController::class, 'remove']);
            Route::post('/move-to-cart/{productId}', [WishlistController::class, 'moveToCart']);
        });

        // Reviews
        Route::post('/products/{product}/reviews', [\App\Http\Controllers\Customer\ProductReviewController::class, 'store'])->middleware('throttle:5,1');
    });

    // ── 5. RIDER / DELIVERY PARTNER MOBILE APP API ──
    Route::prefix('rider')->group(function () {
        Route::post('/login', [\App\Http\Controllers\Api\V1\RiderController::class, 'login']);
        Route::get('/ping', [\App\Http\Controllers\Api\V1\RiderController::class, 'ping']);
        Route::post('/webhook/razorpay', [\App\Http\Controllers\Api\V1\RiderController::class, 'handleRazorpayWebhook']);

        Route::middleware(['auth:sanctum'])->group(function () {
            Route::get('/dashboard', [\App\Http\Controllers\Api\V1\RiderController::class, 'dashboard']);
            Route::get('/orders/{order}', [\App\Http\Controllers\Api\V1\RiderController::class, 'show']);
            Route::get('/orders/{order}/payment-status', [\App\Http\Controllers\Api\V1\RiderController::class, 'paymentStatus']);
            Route::post('/orders/{order}/complete', [\App\Http\Controllers\Api\V1\RiderController::class, 'completeDelivery']);
            Route::post('/orders/{order}/deliver', [\App\Http\Controllers\Api\V1\RiderController::class, 'completeDelivery']);
            Route::post('/orders/{order}/complete-contactless', [\App\Http\Controllers\Api\V1\RiderController::class, 'completeContactless']);
            Route::post('/orders/{order}/confirm-upi', [\App\Http\Controllers\Api\V1\RiderController::class, 'confirmDoorstepUpi']);
            Route::post('/orders/{order}/doorstep-upi', [\App\Http\Controllers\Api\V1\RiderController::class, 'confirmDoorstepUpi']);
            Route::post('/orders/{order}/report-issue', [\App\Http\Controllers\Api\V1\RiderController::class, 'reportIssue']);
            Route::post('/orders/{order}/issue', [\App\Http\Controllers\Api\V1\RiderController::class, 'reportIssue']);
            Route::post('/bag-load/verify', [\App\Http\Controllers\Api\V1\RiderController::class, 'verifyBagLoad']);
            Route::get('/settlements', [\App\Http\Controllers\Api\V1\RiderController::class, 'settlements']);
            Route::get('/settlement-qr', [\App\Http\Controllers\Api\V1\RiderController::class, 'settlementQr']);
            Route::get('/settlement-status', [\App\Http\Controllers\Api\V1\RiderController::class, 'settlementStatus']);
            Route::get('/completed', [\App\Http\Controllers\Api\V1\RiderController::class, 'completed']);
            Route::post('/logout', [\App\Http\Controllers\Api\V1\RiderController::class, 'logout']);
        });
    });

});
