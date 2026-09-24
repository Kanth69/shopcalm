<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class StaffAuthController extends Controller
{
    /**
     * Display the unified Staff Hub login view.
     */
    public function showLogin(): View|RedirectResponse
    {
        // Check if staff member is already logged in on any portal guard
        if (Auth::guard('admin')->check()) {
            return redirect()->route('admin.dashboard');
        }
        if (Auth::guard('order_manager')->check()) {
            return redirect()->route('order-manager.dashboard');
        }
        if (Auth::guard('product_manager')->check()) {
            return redirect()->route('product-manager.dashboard');
        }
        if (Auth::guard('support')->check()) {
            return redirect()->route('support.dashboard');
        }
        if (Auth::guard('delivery_partner')->check()) {
            return redirect()->route('delivery.dashboard');
        }

        return view('staff.login');
    }

    /**
     * Authenticate staff credentials and perform smart role-based auto-redirection.
     */
    public function login(Request $request): RedirectResponse
    {
        $request->validate([
            'email'    => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ]);

        $credentials = $request->only('email', 'password');

        // Find user by email
        $user = User::where('email', $credentials['email'])->first();

        if (!$user || !Hash::check($credentials['password'], $user->password)) {
            return back()->withInput($request->only('email'))->withErrors([
                'email' => 'Invalid staff credentials. Please check your email and password.',
            ]);
        }

        // Check if user is blocked or inactive
        if (isset($user->status) && in_array(strtolower($user->status), ['inactive', 'blocked', 'banned'])) {
            return back()->withInput($request->only('email'))->withErrors([
                'email' => 'Your staff account is currently suspended. Contact Administrator.',
            ]);
        }

        // Perform smart role-based authentication and portal redirection
        switch ((int) $user->role_id) {
            case User::ROLE_SUPER_ADMIN:
            case User::ROLE_ADMIN:
                Auth::guard('admin')->login($user, $request->boolean('remember'));
                $request->session()->regenerate();
                return redirect()->intended(route('admin.dashboard'))->with('toast', [
                    'type'    => 'success',
                    'title'   => 'Welcome Back!',
                    'message' => "Logged in as Admin: {$user->name}",
                ]);

            case User::ROLE_ORDER_MANAGER:
                Auth::guard('order_manager')->login($user, $request->boolean('remember'));
                $request->session()->regenerate();
                return redirect()->intended(route('order-manager.dashboard'))->with('toast', [
                    'type'    => 'success',
                    'title'   => 'Logistics Hub Active',
                    'message' => "Welcome to Order Operations, {$user->name}!",
                ]);

            case User::ROLE_PRODUCT_MANAGER:
                Auth::guard('product_manager')->login($user, $request->boolean('remember'));
                $request->session()->regenerate();
                return redirect()->intended(route('product-manager.dashboard'))->with('toast', [
                    'type'    => 'success',
                    'title'   => 'Catalog Console Active',
                    'message' => "Welcome to Catalog Management, {$user->name}!",
                ]);

            case User::ROLE_SUPPORT:
                Auth::guard('support')->login($user, $request->boolean('remember'));
                $request->session()->regenerate();
                return redirect()->intended(route('support.dashboard'))->with('toast', [
                    'type'    => 'success',
                    'title'   => 'Support Desk Active',
                    'message' => "Welcome to Customer Support, {$user->name}!",
                ]);

            case User::ROLE_DELIVERY_PARTNER:
                Auth::guard('delivery_partner')->login($user, $request->boolean('remember'));
                $request->session()->regenerate();
                return redirect()->intended(route('delivery.dashboard'))->with('toast', [
                    'type'    => 'success',
                    'title'   => 'Fleet Active',
                    'message' => "Shift active for {$user->name}!",
                ]);

            case User::ROLE_CUSTOMER:
            default:
                return back()->withInput($request->only('email'))->withErrors([
                    'email' => 'Customer accounts are not permitted on the Staff Operations Hub.',
                ]);
        }
    }
}
