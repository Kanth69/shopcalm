<?php

namespace App\Http\Controllers\OrderManager;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthController extends Controller
{
    /**
     * Show the Order Manager login page.
     */
    public function create(): View|RedirectResponse
    {
        if (Auth::guard('order_manager')->check()) {
            return redirect()->route('order-manager.dashboard');
        }

        return view('order-manager.auth.login');
    }

    /**
     * Authenticate credentials for the Order Manager portal.
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

        if (!Auth::guard('order_manager')->attempt($credentials, $remember)) {
            return back()->withErrors([
                'email' => 'These credentials do not match our Order Management staff records.',
            ])->withInput($request->only('email', 'remember'));
        }

        $user = Auth::guard('order_manager')->user();

        // Strictly check authorization
        if (!$user->canAccessOrderManagerPortal()) {
            Auth::guard('order_manager')->logout();

            if ($user->isAdmin()) {
                return back()->withErrors([
                    'email' => 'Admin account detected. Please log in through the Admin Console at /admin/login.',
                ])->withInput($request->only('email', 'remember'));
            }

            return back()->withErrors([
                'email' => 'Unauthorized: This account does not have Order Management privileges.',
            ])->withInput($request->only('email', 'remember'));
        }

        if ($request->hasSession()) {
            $request->session()->regenerate();
        }

        return redirect()->intended(route('order-manager.dashboard'))->with('toast', [
            'type'    => 'success',
            'title'   => 'Welcome Back',
            'message' => 'Signed into Order Manager Portal as ' . $user->name,
        ]);
    }

    /**
     * Destroy the order manager authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        if (Auth::guard('order_manager')->check()) {
            Auth::guard('order_manager')->logout();
        }

        return redirect()->route('order-manager.login')->with('toast', [
            'type'    => 'info',
            'title'   => 'Logged Out',
            'message' => 'You have been safely signed out.',
        ]);
    }
}
