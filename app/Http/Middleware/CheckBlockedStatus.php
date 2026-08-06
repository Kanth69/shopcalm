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
        // Only check the customer guard — admin guard is handled separately
        if (Auth::guard('customer')->check() && Auth::guard('customer')->user()->status === 'Blocked') {
            Auth::guard('customer')->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login')->withErrors([
                'login_identifier' => 'Your account has been blocked. Please contact support.',
            ]);
        }

        return $next($request);
    }
}
