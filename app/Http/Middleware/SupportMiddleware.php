<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;

class SupportMiddleware
{
    /**
     * Handle an incoming request for the Customer Support portal.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $guard = Auth::guard('support')->check() ? 'support' : (Auth::guard('admin')->check() ? 'admin' : null);

        if (!$guard) {
            return redirect()->route('support.login');
        }

        $user = Auth::guard($guard)->user();

        // If an Admin attempts to access Support portal, gracefully redirect to Admin Console
        if ($user->isAdmin() && !$user->isSuperAdmin()) {
            return redirect()->route('admin.dashboard')->with('toast', [
                'type' => 'info',
                'title' => 'Admin Console',
                'message' => 'Redirected to your designated Admin Console.'
            ]);
        }

        // Only Support Agents and Super Admins are permitted
        if (!$user->canAccessSupportPortal()) {
            if ($user->isCustomer()) {
                Auth::guard($guard)->logout();
                return redirect()->route('support.login')->withErrors(['email' => 'Unauthorized access.']);
            }
            abort(403, 'Unauthorized access to the Customer Support Portal.');
        }

        return $next($request);
    }
}
