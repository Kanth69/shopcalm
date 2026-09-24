<?php

namespace App\Http\Controllers\Support;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthController extends Controller
{
    /**
     * Show the Customer Support login page.
     */
    public function create(): View|RedirectResponse
    {
        if (Auth::guard('support')->check()) {
            return redirect()->route('support.dashboard');
        }

        return view('support.auth.login');
    }

    /**
     * Authenticate credentials for the Customer Support portal.
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'email'    => ['required', 'email'],
            'password' => ['required'],
        ], [
            'email.required'    => 'Official staff email is required.',
            'password.required' => 'Password is required.',
        ]);

        $credentials = $request->only('email', 'password');
        $remember    = $request->boolean('remember');

        if (!Auth::guard('support')->attempt($credentials, $remember)) {
            return back()->withErrors([
                'email' => 'These credentials do not match our Customer Support staff records.',
            ])->withInput($request->only('email', 'remember'));
        }

        $user = Auth::guard('support')->user();

        // Strictly check authorization
        if (!$user->canAccessSupportPortal()) {
            Auth::guard('support')->logout();

            if ($user->isAdmin()) {
                return back()->withErrors([
                    'email' => 'Admin account detected. Please log in through the Admin Console at /admin/login.',
                ])->withInput($request->only('email', 'remember'));
            }

            return back()->withErrors([
                'email' => 'Unauthorized: This account does not have Customer Support privileges.',
            ])->withInput($request->only('email', 'remember'));
        }

        if ($request->hasSession()) {
            $request->session()->regenerate();
        }

        return redirect()->intended(route('support.dashboard'))->with('toast', [
            'type'    => 'success',
            'title'   => 'Welcome Back',
            'message' => 'Signed into Customer Support Portal as ' . $user->name,
        ]);
    }

    /**
     * Destroy the support agent authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        if (Auth::guard('support')->check()) {
            Auth::guard('support')->logout();
        }

        return redirect()->route('support.login')->with('toast', [
            'type'    => 'info',
            'title'   => 'Logged Out',
            'message' => 'You have been safely signed out.',
        ]);
    }
}
