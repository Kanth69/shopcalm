<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\ConfirmablePasswordController;
use App\Http\Controllers\Auth\PasswordController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\Auth\WhatsAppPasswordResetController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest:customer')->group(function () {
    Route::get('register', [RegisteredUserController::class, 'create'])
                ->name('register');

    Route::post('register', [RegisteredUserController::class, 'store']);

    Route::post('auth/check-user', [\App\Http\Controllers\Api\V1\AuthController::class, 'checkUser'])
                ->name('auth.check-user');

    Route::post('auth/send-whatsapp-otp', [RegisteredUserController::class, 'sendOtp'])
                ->name('auth.send-otp');

    Route::post('auth/verify-otp', [RegisteredUserController::class, 'verifyOtp'])
                ->name('auth.verify-otp');

    Route::get('login', [AuthenticatedSessionController::class, 'create'])
                ->name('login');

    Route::post('login', [AuthenticatedSessionController::class, 'store']);

    // WhatsApp OTP Password Reset Routes (Zero Email Reset Links)
    Route::get('forgot-password', [WhatsAppPasswordResetController::class, 'showForgotPasswordForm'])
                ->name('password.request');

    Route::post('forgot-password/send-otp', [WhatsAppPasswordResetController::class, 'sendResetOtp'])
                ->name('password.send-reset-otp');

    Route::post('reset-password', [WhatsAppPasswordResetController::class, 'resetPassword'])
                ->name('password.reset-whatsapp');
});

Route::middleware('auth:customer')->group(function () {
    Route::get('confirm-password', [ConfirmablePasswordController::class, 'show'])
                ->name('password.confirm');

    Route::post('confirm-password', [ConfirmablePasswordController::class, 'store']);

    Route::put('password', [PasswordController::class, 'update'])->name('password.update');
});

Route::match(['get', 'post'], 'logout', [AuthenticatedSessionController::class, 'destroy'])
    ->name('logout');
