<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\HomeController;
use App\Http\Controllers\Api\V1\ProductController;
use App\Http\Controllers\Api\V1\CartController;
use App\Http\Controllers\Api\V1\CheckoutController;
use App\Http\Controllers\Api\V1\AccountController;

/*
|--------------------------------------------------------------------------
| API V1 Routes — WiseKart / ShopCalm Mobile Apps
|--------------------------------------------------------------------------
*/

Route::prefix('v1')->group(function () {

    // ── 1. AUTHENTICATION & PROFILE ENDPOINTS ──
    Route::prefix('auth')->group(function () {
        Route::post('/check-user', [AuthController::class, 'checkUser']);
        Route::post('/send-otp', [AuthController::class, 'sendOtp']);
        Route::post('/verify-otp', [AuthController::class, 'verifyOtp']);
        Route::post('/login', [AuthController::class, 'login']);
        Route::post('/register', [AuthController::class, 'register']);
        Route::post('/reset-password', [AuthController::class, 'resetPassword']);

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
    Route::get('/products', [ProductController::class, 'index']);
    Route::get('/products/{slug}', [ProductController::class, 'show']);
    Route::post('/delivery/check-pincode', [CheckoutController::class, 'checkPincode']);

    // ── 3. CART ENDPOINTS ──
    Route::prefix('cart')->group(function () {
        Route::get('/', [CartController::class, 'index']);
        Route::post('/add', [CartController::class, 'add']);
        Route::patch('/update/{itemId}', [CartController::class, 'update']);
        Route::delete('/remove/{itemId}', [CartController::class, 'remove']);
        Route::delete('/clear', [CartController::class, 'clear']);
    });

    // ── 4. CHECKOUT & ACCOUNT (Protected Sanctum Routes) ──
    Route::middleware('auth:sanctum')->group(function () {

        Route::prefix('checkout')->group(function () {
            Route::post('/validate', [CheckoutController::class, 'validateCheckout']);
            Route::post('/place-order', [CheckoutController::class, 'placeOrder']);
            Route::post('/orders/{order}/switch-to-cod', [CheckoutController::class, 'switchToCod']);
        });

        Route::prefix('account')->group(function () {
            Route::get('/orders', [AccountController::class, 'orders']);
            Route::get('/orders/{order}', [AccountController::class, 'orderDetails']);
            Route::get('/orders/{order}/cancellation-summary', [AccountController::class, 'cancellationSummary']);
            Route::post('/orders/{order}/cancel', [AccountController::class, 'cancelOrder']);
            Route::get('/wallet', [AccountController::class, 'wallet']);
            Route::get('/addresses', [AccountController::class, 'addresses']);
        });

    });

});
