<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;

class AdminMiddleware
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Enforce the admin guard for all admin paths
        if (!Auth::guard('admin')->check()) {
            return redirect()->route('admin.login');
        }

        $user = Auth::guard('admin')->user();

        // If Product Manager attempts to access Admin Console, logout from admin guard and redirect to PM portal
        if ($user->isProductManager()) {
            Auth::guard('admin')->logout();
            return redirect()->route('product-manager.dashboard')->with('toast', [
                'type' => 'info',
                'title' => 'Product Manager Portal',
                'message' => 'Redirected to your designated Product Manager dashboard.'
            ]);
        }

        // If Order Manager attempts to access Admin Console, logout from admin guard and redirect to OM portal
        if ($user->isOrderManager()) {
            Auth::guard('admin')->logout();
            return redirect()->route('order-manager.dashboard')->with('toast', [
                'type' => 'info',
                'title' => 'Order Manager Portal',
                'message' => 'Redirected to your designated Order Manager dashboard.'
            ]);
        }

        // If Customer Support attempts to access Admin Console, logout from admin guard and redirect to Support portal
        if ($user->isCustomerSupport()) {
            Auth::guard('admin')->logout();
            return redirect()->route('support.dashboard')->with('toast', [
                'type' => 'info',
                'title' => 'Customer Support Portal',
                'message' => 'Redirected to your designated Customer Support dashboard.'
            ]);
        }

        // Final sanity check for admin portal access
        if (!$user->canAccessAdminPortal()) {
            if ($user->isCustomer()) {
                Auth::guard('admin')->logout();
                return redirect()->route('admin.login')->withErrors(['login_identifier' => 'Unauthorized access.']);
            }
            abort(403, 'Unauthorized access to the Admin Portal.');
        }

        return $next($request);
    }
}
