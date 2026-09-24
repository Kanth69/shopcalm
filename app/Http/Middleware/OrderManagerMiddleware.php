<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;

class OrderManagerMiddleware
{
    /**
     * Handle an incoming request for the Order Manager portal.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $guard = Auth::guard('order_manager')->check() ? 'order_manager' : (Auth::guard('admin')->check() ? 'admin' : null);

        if (!$guard) {
            return redirect()->route('order-manager.login');
        }

        $user = Auth::guard($guard)->user();

        // If an Admin attempts to access Order Manager portal, gracefully redirect to Admin Console
        if ($user->isAdmin() && !$user->isSuperAdmin()) {
            return redirect()->route('admin.dashboard')->with('toast', [
                'type' => 'info',
                'title' => 'Admin Console',
                'message' => 'Redirected to your designated Admin Console.'
            ]);
        }

        // Only Order Managers and Super Admins are permitted
        if (!$user->canAccessOrderManagerPortal()) {
            if ($user->isCustomer()) {
                Auth::guard($guard)->logout();
                return redirect()->route('order-manager.login')->withErrors(['email' => 'Unauthorized access.']);
            }
            abort(403, 'Unauthorized access to the Order Manager Portal.');
        }

        return $next($request);
    }
}
