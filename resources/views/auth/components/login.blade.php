<div class="auth-header text-center">
    <a href="{{ route('home') }}" class="auth-logo d-inline-flex align-items-center justify-content-center mb-3 text-decoration-none">
        <x-logo height="40" />
    </a>
    <h2 class="auth-title">Welcome Back 👋</h2>
    <p class="auth-subtitle">Enter your password to sign in</p>
</div>

<!-- Prominent Red Alert Banner Above Login Form -->
<div id="login-alert" class="auth-alert error" style="display: none;">
    <i class="bi bi-exclamation-triangle-fill me-2"></i>
    <span id="login-alert-msg"></span>
</div>

<form id="form-login" method="POST" action="{{ route('login') }}" class="auth-form">
    @csrf
    <input type="hidden" name="login_identifier" id="login_identifier_for_login">

    <div class="form-group">
        <div class="label-row">
            <label for="login_identifier_display">Account</label>
            <button type="button" class="change-identifier-btn" id="change-identifier-from-login">Change</button>
        </div>
        <input id="login_identifier_display" class="auth-input readonly-input" type="text" readonly tabindex="-1">
    </div>

    <div id="web-password-section">
        <div class="form-group">
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

        <div class="form-options">
            <label class="checkbox-container">
                <input id="remember_me" type="checkbox" name="remember">
                <span class="checkmark"></span>
                <span class="label-text">Stay logged in</span>
            </label>
        </div>

        <button type="submit" id="login-submit-btn" class="auth-submit-btn">
            <span>Sign In</span>
            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
            </svg>
        </button>
    </div>

    <div class="my-3 d-flex align-items-center" id="web-login-divider">
        <hr class="flex-grow-1 border-secondary opacity-25">
        <span class="px-2 text-muted fw-bold small" style="font-size: 11px; letter-spacing: 0.5px;">OR</span>
        <hr class="flex-grow-1 border-secondary opacity-25">
    </div>

    {{-- 1-Tap Fast WhatsApp OTP Button --}}
    <button type="button" id="web-login-otp-btn" class="auth-submit-btn" style="background: #10b981; border: none; margin-top: 4px;">
        <i class="bi bi-whatsapp me-2 fs-5"></i>
        <span>Login with WhatsApp OTP</span>
    </button>

    {{-- Web WhatsApp OTP Entry Section --}}
    <div id="web-login-otp-section" style="display: none;" class="my-3 p-3 bg-light rounded-4 border">
        <div class="d-flex align-items-center justify-content-between mb-2">
            <span class="fw-bold small text-dark"><i class="bi bi-shield-lock text-success me-1"></i> Enter 6-digit WhatsApp OTP</span>
            <span id="web-login-countdown" class="badge bg-secondary font-monospace">60s</span>
        </div>
        <div class="input-group mb-2">
            <input type="text" id="web-login-otp-input" class="form-control font-monospace fw-bold text-center fs-5" maxlength="6" placeholder="••••••" inputmode="numeric">
            <button type="button" id="web-login-verify-btn" class="btn btn-primary px-3 fw-bold">Verify & Login</button>
        </div>
        <div class="d-flex justify-content-between align-items-center">
            <button type="button" id="web-login-resend-btn" class="btn btn-link btn-sm p-0 text-decoration-none text-success fw-bold" style="display: none;">Resend OTP</button>
            <button type="button" id="web-cancel-otp-btn" class="btn btn-link btn-sm p-0 text-decoration-none text-muted">Use Password Instead</button>
        </div>
    </div>
</form>
