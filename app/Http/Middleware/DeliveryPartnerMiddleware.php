<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;

class DeliveryPartnerMiddleware
{
    /**
     * Handle an incoming request for the Delivery Partner portal and Rider APIs.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // 1. API / Sanctum Request handling (Rider Mobile App API)
        if ($request->is('api/*') || $request->expectsJson()) {
            $user = $request->user();

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthenticated.'
                ], 401);
            }

            if (!$user->canAccessDeliveryPortal()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Forbidden. Only authorized Delivery Partners can access this API.'
                ], 403);
            }

            if (in_array($user->status, ['Blocked', 'Deleted'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Your rider account has been deactivated or blocked.'
                ], 403);
            }

            return $next($request);
        }

        // 2. Web Portal handling
        $guard = Auth::guard('delivery_partner')->check() ? 'delivery_partner' : (Auth::guard('admin')->check() ? 'admin' : null);

        if (!$guard) {
            return redirect()->route('delivery.login');
        }

        $user = Auth::guard($guard)->user();

        // Only Delivery Partners and Super Admins are permitted
        if (!$user->canAccessDeliveryPortal()) {
            if ($user->isCustomer()) {
                Auth::guard($guard)->logout();
                return redirect()->route('delivery.login')->withErrors(['email' => 'Unauthorized access. Delivery Partner account required.']);
            }
            abort(403, 'Unauthorized access to the Delivery Partner Portal.');
        }

        return $next($request);
    }
}
