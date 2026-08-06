<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\HomeController;
use App\Http\Controllers\Api\V1\ProductController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

Route::prefix('v1')->group(function () {
    // Home
    Route::get('/home', [HomeController::class, 'index']);

    // Unified Product Listing & Discovery
    Route::get('/products', [ProductController::class, 'index']);

    // Product Details
    Route::get('/products/{slug}', [ProductController::class, 'show']);
});

// Default Sanctum route
Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
