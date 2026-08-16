<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the customer login view.
     * Admins are redirected to the admin panel if already authenticated.
     */
    public function create(Request $request): View|RedirectResponse
    {
        // If an admin guard session is active, redirect them to the admin panel
        if (Auth::guard('admin')->check()) {
            return redirect()->route('admin.dashboard');
        }

        // If already logged in as customer, redirect to home
        if (Auth::guard('customer')->check()) {
            return redirect()->route('home');
        }

        return view('auth.index');
    }

    /**
     * Handle an incoming customer authentication request.
     */
    public function store(LoginRequest $request): \Symfony\Component\HttpFoundation\Response
    {
        $guestSessionId = $request->session()->getId();

        // Authenticate against the customer guard only
        $request->authenticate('customer');

        $request->session()->regenerate();

        // Merge guest session cart into logged-in customer cart
        app(\App\Services\CartService::class)->mergeSessionCart($guestSessionId);

        // Determine redirect: if intended URL was checkout or cart, preserve it; otherwise go to Home Page
        $intendedUrl = session()->pull('url.intended');
        $redirectUrl = route('home');

        if ($intendedUrl && (str_contains($intendedUrl, '/checkout') || str_contains($intendedUrl, '/cart'))) {
            $redirectUrl = $intendedUrl;
        }

        if ($request->wantsJson()) {
            return response()->json([
                'success'  => true,
                'redirect' => $redirectUrl,
            ]);
        }

        return redirect()->to($redirectUrl);
    }

    /**
     * Destroy the customer authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        if (Auth::guard('customer')->check()) {
            Auth::guard('customer')->logout();
        }

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }
}
