<?php

namespace App\Http\Controllers\ProductManager;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthController extends Controller
{
    /**
     * Show the Product Manager login page.
     */
    public function create(): View|RedirectResponse
    {
        if (Auth::guard('product_manager')->check()) {
            return redirect()->route('product-manager.dashboard');
        }

        return view('product-manager.auth.login');
    }

    /**
     * Authenticate credentials for the Product Manager portal.
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

        if (!Auth::guard('product_manager')->attempt($credentials, $remember)) {
            return back()->withErrors([
                'email' => 'These credentials do not match our Product Management staff records.',
            ])->withInput($request->only('email', 'remember'));
        }

        $user = Auth::guard('product_manager')->user();

        // Strictly check authorization
        if (!$user->canAccessProductManagerPortal()) {
            Auth::guard('product_manager')->logout();

            if ($user->isAdmin()) {
                return back()->withErrors([
                    'email' => 'Admin account detected. Please log in through the Admin Console at /admin/login.',
                ])->withInput($request->only('email', 'remember'));
            }

            return back()->withErrors([
                'email' => 'Unauthorized: This account does not have Product Management privileges.',
            ])->withInput($request->only('email', 'remember'));
        }

        if ($request->hasSession()) {
            $request->session()->regenerate();
        }

        return redirect()->intended(route('product-manager.dashboard'))->with('toast', [
            'type'    => 'success',
            'title'   => 'Welcome Back',
            'message' => 'Signed into Product Manager Portal as ' . $user->name,
        ]);
    }

    /**
     * Destroy the product manager authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        if (Auth::guard('product_manager')->check()) {
            Auth::guard('product_manager')->logout();
        }

        return redirect()->route('product-manager.login')->with('toast', [
            'type'    => 'info',
            'title'   => 'Logged Out',
            'message' => 'You have been safely signed out.',
        ]);
    }
}
