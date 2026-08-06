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

        // Final sanity check for roles
        if (!$user->isAdmin()) {
            Auth::guard('admin')->logout();
            return redirect()->route('admin.login')->withErrors(['login_identifier' => 'Unauthorized access.']);
        }

        return $next($request);
    }
}
