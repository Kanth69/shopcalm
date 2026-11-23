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
        $isHubHost = ($host === $configuredHub || $host === 'hub.shopcalm.in' || $host === 'hub.localhost' || str_starts_with($host, 'hub.'));

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
            // If attempting to access staff paths on Main customer domain (shopcalm.in),
            // silently redirect to customer home page so admin login remains 100% cloaked & invisible to public.
            if ($isStaffPath) {
                return redirect('/');
            }
        }

        return $next($request);
    }
}
