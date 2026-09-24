<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;

class ProductManagerMiddleware
{
    /**
     * Handle an incoming request for the Product Manager portal.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $guard = Auth::guard('product_manager')->check() ? 'product_manager' : (Auth::guard('admin')->check() ? 'admin' : null);

        if (!$guard) {
            return redirect()->route('product-manager.login');
        }

        $user = Auth::guard($guard)->user();

        // If an Admin attempts to access Product Manager portal, gracefully redirect to Admin Console
        if ($user->isAdmin() && !$user->isSuperAdmin()) {
            return redirect()->route('admin.dashboard')->with('toast', [
                'type' => 'info',
                'title' => 'Admin Console',
                'message' => 'Redirected to your designated Admin Console.'
            ]);
        }

        // Only Product Managers and Super Admins are permitted
        if (!$user->canAccessProductManagerPortal()) {
            if ($user->isCustomer()) {
                Auth::guard($guard)->logout();
                return redirect()->route('product-manager.login')->withErrors(['email' => 'Unauthorized access.']);
            }
            abort(403, 'Unauthorized access to the Product Manager Portal.');
        }

        return $next($request);
    }
}
