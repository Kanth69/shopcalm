<?php

namespace App\Http\Controllers\Marketing;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthController extends Controller
{
    /**
     * Show the Marketing & Growth Portal login page.
     */
    public function create(): View|RedirectResponse
    {
        if (Auth::guard('marketing')->check()) {
            return redirect()->route('marketing.dashboard');
        }

        return view('marketing.auth.login');
    }

    /**
     * Authenticate credentials for the Marketing portal.
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'email'    => ['required', 'email'],
            'password' => ['required'],
        ], [
            'email.required'    => 'Official marketing staff email is required.',
            'password.required' => 'Password is required.',
        ]);

        $credentials = $request->only('email', 'password');
        $remember    = $request->boolean('remember');

        if (!Auth::guard('marketing')->attempt($credentials, $remember)) {
            return back()->withErrors([
                'email' => 'These credentials do not match our Marketing staff records.',
            ])->withInput($request->only('email', 'remember'));
        }

        $user = Auth::guard('marketing')->user();

        if (in_array($user->status, ['Blocked', 'Deleted'])) {
            Auth::guard('marketing')->logout();
            return back()->withErrors([
                'email' => 'Your account has been deactivated. Please contact the Super Administrator.',
            ])->withInput($request->only('email', 'remember'));
        }

        if (!$user->canAccessMarketingPortal()) {
            Auth::guard('marketing')->logout();
            return back()->withErrors([
                'email' => 'Unauthorized: This account does not have Marketing portal privileges.',
            ])->withInput($request->only('email', 'remember'));
        }

        $request->session()->regenerate();

        return redirect()->intended(route('marketing.dashboard'));
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('marketing')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('marketing.login');
    }
}
