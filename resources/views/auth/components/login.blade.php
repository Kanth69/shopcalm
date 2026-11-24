@php
    $rawStoreName = \App\Models\Setting::get('store_name', 'ShopCalm');
    $brandDisplay = str_ireplace('shopcalm', 'ShopCalm', $rawStoreName);
@endphp
<div class="auth-header text-center">
    <a href="{{ route('home') }}" class="auth-logo d-inline-flex align-items-center justify-content-center mb-3 text-decoration-none">
        <x-logo height="40" />
    </a>
    <h2 class="auth-title" id="login-auth-title">Welcome Back 👋</h2>
    <p class="auth-subtitle" id="login-auth-subtitle">Enter your password to sign in</p>
</div>

<!-- Prominent Red Alert Banner Above Login Form -->
<div id="login-alert" class="auth-alert error" style="display: none;">
    <i class="bi bi-exclamation-triangle-fill me-2"></i>
    <span id="login-alert-msg"></span>
</div>

<form id="form-login" method="POST" action="{{ route('login') }}" class="auth-form">
    @csrf
    <input type="hidden" name="login_identifier" id="login_identifier_for_login">

    <div class="form-group mb-2">
        <div class="label-row">
            <label for="login_identifier_display">Account</label>
            <button type="button" class="change-identifier-btn" id="change-identifier-from-login">Change</button>
        </div>
        <input id="login_identifier_display" class="auth-input readonly-input" type="text" readonly tabindex="-1">
    </div>

    {{-- MODE 1: PASSWORD LOGIN --}}
    <div id="web-password-mode">
        <input type="hidden" name="remember" value="1">
        <div class="form-group mb-3">
            <div class="label-row">
                <label for="password_for_login">Password</label>
                <a href="#" class="forgot-link" id="show-forgot-password">Forgot?</a>
            </div>
            <div class="password-input-wrapper">
                <input id="password_for_login" class="auth-input" type="password" name="password" required autofocus placeholder="Enter your password" autocomplete="current-password">
                <button type="button" class="password-toggle-btn" onclick="togglePasswordVisibility('password_for_login', this)" title="Show password">
                    <i class="bi bi-eye-slash"></i>
                </button>
            </div>
        </div>

        <button type="submit" id="login-submit-btn" class="auth-submit-btn">
            <span>Sign In</span>
            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
            </svg>
        </button>

        <div class="login-or-divider my-3 text-center position-relative" id="web-login-divider">
            <hr class="border-secondary opacity-25 m-0">
            <span class="bg-white px-3 text-muted fw-bold small position-absolute top-50 start-50 translate-middle" style="font-size: 11px; letter-spacing: 0.5px;">OR</span>
        </div>

        {{-- 1-Tap Fast WhatsApp OTP Button --}}
        <button type="button" id="web-login-otp-btn" class="auth-submit-btn" style="background: #10b981; border: none;">
            <i class="bi bi-whatsapp me-2 fs-5"></i>
            <span>Login with WhatsApp OTP</span>
        </button>
    </div>

    {{-- MODE 2: DIRECT WHATSAPP OTP LOGIN --}}
    <div id="web-login-otp-section" style="display: none;">
        <div class="form-group mb-3">
            <div class="label-row">
                <label for="web-login-otp-input">WhatsApp OTP</label>
                <div class="d-flex align-items-center">
                    <span id="web-login-countdown" class="badge bg-secondary font-monospace">90s</span>
                    <button type="button" id="web-login-resend-btn" class="btn btn-link btn-sm p-0 text-decoration-none text-success fw-bold" style="display: none; font-size: 12.5px;"><i class="bi bi-arrow-repeat me-1"></i>Resend OTP</button>
                </div>
            </div>
            <input type="text" id="web-login-otp-input" class="auth-input font-monospace text-center fs-5" maxlength="6" placeholder="••••••" inputmode="numeric" style="letter-spacing: 6px; font-weight: 700;">
            <span class="field-hint" style="font-size: 12px; color: #64748b; margin-top: 4px;">Enter the 6-digit code sent to your WhatsApp</span>
        </div>

        <button type="button" id="web-login-verify-btn" class="auth-submit-btn">
            <span>Verify & Login</span>
            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
            </svg>
        </button>
    </div>

    <p class="text-center text-muted mt-3 mb-0" style="font-size: 0.76rem; line-height: 1.5;">
        By continuing, you agree to {{ $brandDisplay }}'s 
        <a href="{{ route('page.terms') }}" target="_blank" class="text-primary text-decoration-none fw-semibold">Terms & Conditions</a> 
        and 
        <a href="{{ route('page.privacy') }}" target="_blank" class="text-primary text-decoration-none fw-semibold">Privacy Policy</a>.
    </p>
</form>
