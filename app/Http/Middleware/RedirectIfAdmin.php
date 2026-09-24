<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;

class RedirectIfAdmin
{
    /**
     * Handle an incoming request for staff guest routes.
     */
    public function handle(Request $request, Closure $next, ?string $guard = null): Response
    {
        if ($guard === 'product_manager' && Auth::guard('product_manager')->check()) {
            return redirect()->route('product-manager.dashboard');
        }

        if ($guard === 'order_manager' && Auth::guard('order_manager')->check()) {
            return redirect()->route('order-manager.dashboard');
        }

        if ($guard === 'support' && Auth::guard('support')->check()) {
            return redirect()->route('support.dashboard');
        }

        if (($guard === 'admin' || $guard === null) && Auth::guard('admin')->check()) {
            return redirect()->route('admin.dashboard');
        }

        return $next($request);
    }
}
