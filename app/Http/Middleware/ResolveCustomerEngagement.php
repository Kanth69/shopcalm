<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

/**
 * @deprecated This middleware has been removed.
 */
class ResolveCustomerEngagement
{
    public function handle(Request $request, Closure $next)
    {
        // Module Removed
        return $next($request);
    }
}
