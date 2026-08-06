<x-guest-layout>
    <x-slot name="title">Create Account</x-slot>

    <div class="auth-card-container">
        <div class="auth-header">
            <a href="{{ route('home') }}" class="auth-logo">
                <span class="logo-w">W</span>
                <div class="logo-text">Shopcalm<span>.in</span></div>
            </a>
            <h2 class="auth-title">Get Started</h2>
            <p class="auth-subtitle">Join the premium shopping community</p>
        </div>

        <form method="POST" action="{{ route('register') }}" class="auth-form">
            @csrf

            <div class="form-group">
                <label for="name">Full Name <span class="text-danger">*</span></label>
                <input id="name" class="auth-input" type="text" name="name" :value="old('name')" required autofocus placeholder="Enter your full name">
                <x-input-error :messages="$errors->get('name')" class="mt-2" />
            </div>

            <div class="form-group">
                <label for="mobile_number">Mobile Number <span class="text-danger">*</span></label>
                <input id="mobile_number" class="auth-input" type="tel" name="mobile_number" :value="old('mobile_number')" required placeholder="10-digit mobile number" pattern="[0-9]{10}">
                <x-input-error :messages="$errors->get('mobile_number')" class="mt-2" />
            </div>

            <div class="form-group">
                <label for="email">Email Address (Optional)</label>
                <input id="email" class="auth-input" type="email" name="email" :value="old('email')" placeholder="Enter your email">
                <x-input-error :messages="$errors->get('email')" class="mt-2" />
            </div>

            <div class="form-group">
                <label for="password">Password <span class="text-danger">*</span></label>
                <input id="password" class="auth-input" type="password" name="password" required placeholder="Create a strong password">
                <x-input-error :messages="$errors->get('password')" class="mt-2" />
            </div>

            <div class="form-group">
                <label for="password_confirmation">Confirm Password <span class="text-danger">*</span></label>
                <input id="password_confirmation" class="auth-input" type="password" name="password_confirmation" required placeholder="Repeat your password">
            </div>

            <button type="submit" class="auth-submit-btn">
                {{ __('Create Account') }}
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                </svg>
            </button>

            <div class="auth-divider">
                <span>OR</span>
            </div>

            <a href="{{ route('google.redirect') }}" class="google-btn">
                <img src="https://www.gstatic.com/firebasejs/ui/2.0.0/images/auth/google.svg" alt="Google">
                <span>Join with Google</span>
            </a>

            <div class="auth-footer">
                Already a member? <a href="{{ route('login') }}">Sign In</a>
            </div>
        </form>
    </div>

    <style>
        /* Shared Modern Auth Styles */
        .auth-card-container {
            padding: 5px;
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
            gap: 18px;
        }

        .form-group {
            display: flex;
            flex-direction: column;
            gap: 6px;
        }

        .form-group label {
            font-size: 12px;
            font-weight: 700;
            color: #475569;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .auth-input {
            background-color: #f8fafc;
            border: 2px solid #e2e8f0;
            border-radius: 12px;
            padding: 10px 16px;
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
            margin-top: 10px;
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
            margin: 5px 0;
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
            margin-top: 15px;
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
