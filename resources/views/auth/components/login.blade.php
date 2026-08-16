<div class="auth-header text-center">
    <a href="{{ route('home') }}" class="auth-logo d-inline-flex justify-content-center mb-3 text-decoration-none">
        <x-logo height="42" />
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

    <div class="form-group">
        <div class="label-row">
            <label for="password_for_login">Password</label>
            <a href="#" class="forgot-link" id="show-forgot-password">Forgot?</a>
        </div>
        <div class="password-input-wrapper">
            <input id="password_for_login" class="auth-input" type="password" name="password" required autofocus placeholder="••••••••">
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
</form>
