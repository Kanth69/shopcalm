<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureDomainIsolation
{
    /**
     * Handle an incoming request to enforce strict domain separation.
     * Hub Domain -> ONLY Staff Portals allowed (Customer routes redirected to Staff Hub).
     * Main Domain -> ONLY Customer Storefront allowed (Staff routes redirected to Hub Domain).
     */
    public function handle(Request $request, Closure $next): Response
    {
        $host = strtolower($request->getHost());
        $configuredHub = strtolower(config('app.staff_hub_domain', 'hub.shopcalm.in'));
        $isHubHost = ($host === $configuredHub || $host === 'hub.localhost' || str_starts_with($host, 'hub.'));

        $path = '/' . ltrim($request->path(), '/');

        $isCustomerDeliveryPath = (
            $path === '/delivery/check-pincode' ||
            $path === '/delivery/save-location' ||
            $path === '/delivery/saved-addresses'
        );

        $isStaffPath = !$isCustomerDeliveryPath && (
            $path === '/admin' || str_starts_with($path, '/admin/') ||
            $path === '/order-manager' || str_starts_with($path, '/order-manager/') ||
            $path === '/product-manager' || str_starts_with($path, '/product-manager/') ||
            $path === '/support' || str_starts_with($path, '/support/') ||
            $path === '/marketing' || str_starts_with($path, '/marketing/') ||
            $path === '/delivery' || str_starts_with($path, '/delivery/') ||
            $path === '/rider'
        );

        if ($isHubHost) {
            // ── 1. HUB DOMAIN ISOLATION ──
            // If attempting to access customer storefront pages on Hub domain, redirect to Admin Login
            if (!$isStaffPath && !str_starts_with($path, '/api/') && $path !== '/test-email' && $path !== '/up') {
                return redirect()->route('admin.login');
            }
        } else {
            // ── 2. MAIN CUSTOMER DOMAIN ISOLATION ──
            // If attempting to access staff paths on Main domain, return 404 (stealth security rule)
            if ($isStaffPath) {
                // In local dev when accessing via IP/localhost, allow staff routes directly
                if (app()->environment('local') && ($host === '127.0.0.1' || $host === 'localhost')) {
                    return $next($request);
                }

                // Modern Web Security Rule: Never disclose internal staff hub URL via redirect. Return 404.
                abort(404);
            }
        }

        return $next($request);
    }
}
