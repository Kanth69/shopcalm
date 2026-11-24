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
     * Authenticate customer directly using verified WhatsApp OTP.
     */
    public function loginWithOtp(Request $request): \Symfony\Component\HttpFoundation\Response
    {
        $request->validate([
            'mobile_number' => ['required', 'string'],
            'otp'           => ['required', 'string', 'size:6'],
        ], [
            'mobile_number.required' => 'Please enter your mobile number.',
            'otp.required'           => 'Please enter the 6-digit WhatsApp OTP.',
            'otp.size'               => 'OTP must be exactly 6 digits.',
        ]);

        $cleanPhone = preg_replace('/\D/', '', $request->mobile_number);
        $last10 = (strlen($cleanPhone) >= 10) ? substr($cleanPhone, -10) : $cleanPhone;

        $whatsAppService = app(\App\Services\WhatsAppService::class);
        $isValid = $whatsAppService->verifyOtp($request->mobile_number, $request->otp, 'LOGIN')
                || $whatsAppService->verifyOtp($request->mobile_number, $request->otp, 'REGISTER');

        if (!$isValid) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid or expired WhatsApp OTP code. Please try again.',
            ], 422);
        }

        $user = \App\Models\User::where('mobile_number', 'LIKE', '%' . $last10)->first();
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'No account found with this mobile number.',
            ], 404);
        }

        if (in_array(strtolower($user->status ?? ''), ['blocked', 'deleted'])) {
            return response()->json([
                'success' => false,
                'message' => 'This account has been closed or blocked. Please contact customer support.',
            ], 403);
        }

        $guestSessionId = $request->session()->getId();
        Auth::guard('customer')->login($user);
        $request->session()->regenerate();

        app(\App\Services\CartService::class)->mergeSessionCart($guestSessionId);

        $intendedUrl = session()->pull('url.intended');
        $redirectUrl = route('home');
        if ($intendedUrl && (str_contains($intendedUrl, '/checkout') || str_contains($intendedUrl, '/cart'))) {
            $redirectUrl = $intendedUrl;
        }

        return response()->json([
            'success'  => true,
            'redirect' => $redirectUrl,
        ]);
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
