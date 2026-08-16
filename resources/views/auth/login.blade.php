@php
    $isAdminLogin = request()->routeIs('admin.login');
    $pageTitle = $isAdminLogin ? 'Admin Portal' : 'Login';
    $formAction = $isAdminLogin ? route('admin.login') : route('login');
@endphp

<x-guest-layout>
    <x-slot name="title">{{ $pageTitle }}</x-slot>

    <div class="auth-card-container">
        <div class="auth-header">
            <a href="{{ route('home') }}" class="auth-logo">
                <span class="logo-w">W</span>
                <div class="logo-text">Shopcalm<span>.in</span></div>
            </a>
            <h2 class="auth-title">{{ $pageTitle }}</h2>
            <p class="auth-subtitle">{{ $isAdminLogin ? 'Authorized personnel access' : 'Enter details to access your account' }}</p>
        </div>

        <x-auth-session-status class="mb-4" :status="session('status')" />

        <form method="POST" action="{{ $formAction }}" class="auth-form">
            @csrf

            <div class="form-group">
                <label for="login_identifier">Email or Mobile <span class="text-danger">*</span></label>
                <input id="login_identifier" class="auth-input" type="text" name="login_identifier" :value="old('login_identifier')" required autofocus placeholder="Enter email or mobile">
                <x-input-error :messages="$errors->get('login_identifier')" class="mt-2" />
            </div>

            <div class="form-group">
                <div class="label-row">
                    <label for="password">Password <span class="text-danger">*</span></label>
                    @if (Route::has('password.request') && !$isAdminLogin)
                        <a class="forgot-link" href="{{ route('password.request') }}">Forgot?</a>
                    @endif
                </div>
                <div class="password-input-wrapper">
                    <input id="password" class="auth-input" type="password" name="password" required placeholder="••••••••">
                    <button type="button" class="password-toggle-btn" onclick="togglePasswordVisibility('password', this)" title="Show password">
                        <i class="bi bi-eye-slash"></i>
                    </button>
                </div>
                <x-input-error :messages="$errors->get('password')" class="mt-2" />
            </div>

            <div class="form-options">
                <label class="checkbox-container">
                    <input id="remember_me" type="checkbox" name="remember">
                    <span class="checkmark"></span>
                    <span class="label-text">Stay logged in</span>
                </label>
            </div>

            <button type="submit" class="auth-submit-btn">
                {{ __('Sign In') }}
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                </svg>
            </button>

            @if (!$isAdminLogin)
                <div class="auth-divider">
                    <span>OR</span>
                </div>

                <a href="{{ route('google.redirect') }}" class="google-btn">
                    <img src="https://www.gstatic.com/firebasejs/ui/2.0.0/images/auth/google.svg" alt="Google">
                    <span>Continue with Google</span>
                </a>

                <div class="auth-footer">
                    New to Shopcalm? <a href="{{ route('register') }}">Create Account</a>
                </div>
            @endif
        </form>
    </div>

    <style>
        /* Modern Auth Styles */
        .auth-card-container {
            padding: 10px 5px;
        }

        .auth-header {
            text-align: center;
            margin-bottom: 30px;
        }

        .auth-logo {
            display: inline-flex;
            align-items: center;
            text-decoration: none;
            margin-bottom: 20px;
        }

        .logo-w {
            background: linear-gradient(135deg, #3b82f6 0%, #1e40af 100%);
            color: white;
            width: 42px;
            height: 42px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            font-weight: 800;
            border-radius: 12px;
            margin-right: 12px;
            box-shadow: 0 4px 12px rgba(59, 130, 246, 0.3);
        }

        .logo-text {
            font-size: 26px;
            font-weight: 800;
            color: #1e293b;
            letter-spacing: -0.5px;
        }

        .logo-text span {
            color: #3b82f6;
        }

        .auth-title {
            font-size: 22px;
            font-weight: 700;
            color: #1e293b;
            margin-bottom: 6px;
        }

        .auth-subtitle {
            font-size: 14px;
            color: #64748b;
        }

        .auth-form {
            display: flex;
            flex-direction: column;
            gap: 20px;
        }

        .form-group {
            display: flex;
            flex-direction: column;
            gap: 8px;
        }

        .form-group label {
            font-size: 13px;
            font-weight: 600;
            color: #475569;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .label-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .forgot-link {
            font-size: 13px;
            font-weight: 700;
            color: #3b82f6;
            text-decoration: none;
        }

        .auth-input {
            background-color: #f8fafc;
            border: 2px solid #e2e8f0;
            border-radius: 12px;
            padding: 12px 16px;
            font-size: 15px;
            color: #1e293b;
            transition: all 0.2s ease;
            outline: none;
        }

        .auth-input:focus {
            border-color: #3b82f6;
            background-color: #fff;
            box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.1);
        }

        .password-input-wrapper {
            position: relative;
            width: 100%;
        }

        .password-input-wrapper .auth-input {
            padding-right: 46px;
        }

        .password-toggle-btn {
            position: absolute;
            right: 10px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            padding: 6px;
            color: #64748b;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 8px;
            transition: all 0.2s ease;
            z-index: 5;
            font-size: 18px;
        }

        .password-toggle-btn:hover {
            color: #3b82f6;
            background-color: #f1f5f9;
        }

        .form-options {
            margin-bottom: 5px;
        }

        /* Custom Checkbox */
        .checkbox-container {
            display: flex;
            align-items: center;
            position: relative;
            padding-left: 28px;
            cursor: pointer;
            font-size: 14px;
            user-select: none;
            color: #64748b;
        }

        .checkbox-container input {
            position: absolute;
            opacity: 0;
            cursor: pointer;
            height: 0;
            width: 0;
        }

        .checkmark {
            position: absolute;
            top: 0;
            left: 0;
            height: 20px;
            width: 20px;
            background-color: #f1f5f9;
            border: 2px solid #cbd5e1;
            border-radius: 6px;
            transition: all 0.2s;
        }

        .checkbox-container:hover input ~ .checkmark {
            border-color: #3b82f6;
        }

        .checkbox-container input:checked ~ .checkmark {
            background-color: #3b82f6;
            border-color: #3b82f6;
        }

        .checkmark:after {
            content: "";
            position: absolute;
            display: none;
        }

        .checkbox-container input:checked ~ .checkmark:after {
            display: block;
        }

        .checkbox-container .checkmark:after {
            left: 6px;
            top: 2px;
            width: 5px;
            height: 10px;
            border: solid white;
            border-width: 0 2px 2px 0;
            transform: rotate(45deg);
        }

        .auth-submit-btn {
            background: linear-gradient(135deg, #3b82f6 0%, #1e40af 100%);
            color: white;
            padding: 14px;
            border: none;
            border-radius: 12px;
            font-size: 16px;
            font-weight: 700;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            transition: all 0.2s ease;
            box-shadow: 0 4px 12px rgba(59, 130, 246, 0.2);
        }

        .auth-submit-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(59, 130, 246, 0.3);
        }

        .auth-submit-btn:active {
            transform: translateY(0);
        }

        .auth-divider {
            position: relative;
            text-align: center;
            margin: 10px 0;
        }

        .auth-divider:before {
            content: "";
            position: absolute;
            top: 50%;
            left: 0;
            right: 0;
            height: 1px;
            background-color: #e2e8f0;
            z-index: 1;
        }

        .auth-divider span {
            position: relative;
            background-color: #fff;
            padding: 0 15px;
            color: #94a3b8;
            font-size: 12px;
            font-weight: 700;
            z-index: 2;
        }

        .google-btn {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 12px;
            padding: 12px;
            border: 2px solid #e2e8f0;
            border-radius: 12px;
            text-decoration: none;
            color: #475569;
            font-size: 14px;
            font-weight: 700;
            transition: all 0.2s;
            background: white;
        }

        .google-btn:hover {
            background-color: #f8fafc;
            border-color: #cbd5e1;
        }

        .google-btn img {
            width: 20px;
        }

        .auth-footer {
            text-align: center;
            margin-top: 20px;
            font-size: 14px;
            color: #64748b;
        }

        .auth-footer a {
            color: #3b82f6;
            font-weight: 700;
            text-decoration: none;
        }

        .auth-footer a:hover {
            text-decoration: underline;
        }
    </style>
</x-guest-layout>
