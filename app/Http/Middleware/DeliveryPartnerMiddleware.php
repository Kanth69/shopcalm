<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;

class DeliveryPartnerMiddleware
{
    /**
     * Handle an incoming request for the Delivery Partner portal.
     */
    public function handle(Request $request, Closure $next): Response
    {
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
