<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;

class MarketingMiddleware
{
    /**
     * Handle an incoming request for the Marketing portal.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $guard = Auth::guard('marketing')->check() ? 'marketing' : (Auth::guard('admin')->check() ? 'admin' : null);

        if (!$guard) {
            return redirect()->route('marketing.login');
        }

        $user = Auth::guard($guard)->user();

        // Only Marketing staff and Super Admins are permitted
        if (!$user->canAccessMarketingPortal()) {
            if ($user->isCustomer()) {
                Auth::guard($guard)->logout();
                return redirect()->route('marketing.login')->withErrors(['email' => 'Unauthorized access.']);
            }
            abort(403, 'Unauthorized access to the Marketing & Growth Portal.');
        }

        return $next($request);
    }
}
