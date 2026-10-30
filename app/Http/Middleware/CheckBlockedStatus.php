<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;

class CheckBlockedStatus
{
    public function handle(Request $request, Closure $next): Response
    {
        // 1. Check Mobile App & Sanctum API Authenticated Users
        $apiUser = $request->user('sanctum') ?? $request->user();
        if ($apiUser && isset($apiUser->status) && in_array(strtolower($apiUser->status), ['blocked', 'deleted'])) {
            if (method_exists($apiUser, 'tokens')) {
                $apiUser->tokens()->delete();
            }
            return response()->json([
                'success' => false,
                'message' => 'This account has been closed or blocked. Please contact customer support.',
            ], 403);
        }

        // 2. Check Session-based Web Guards
        $guards = ['admin', 'product_manager', 'order_manager', 'support', 'delivery_partner', 'customer'];

        foreach ($guards as $guard) {
            if (Auth::guard($guard)->check()) {
                $user = Auth::guard($guard)->user();
                if ($user && in_array($user->status, ['Blocked', 'Deleted'])) {
                    Auth::guard($guard)->logout();
                    if ($request->hasSession()) {
                        $request->session()->invalidate();
                        $request->session()->regenerateToken();
                    }

                    $loginRoute = match ($guard) {
                        'admin' => 'admin.login',
                        'product_manager' => 'product-manager.login',
                        'order_manager' => 'order-manager.login',
                        'support' => 'support.login',
                        'delivery_partner' => 'delivery.login',
                        default => 'login',
                    };

                    return redirect()->route($loginRoute)->withErrors([
                        'email' => 'Your account has been deactivated or blocked. Please contact a Super Administrator.',
                        'login_identifier' => 'Your account has been deactivated or blocked. Please contact support.',
                    ]);
                }
            }
        }

        return $next($request);
    }
}
