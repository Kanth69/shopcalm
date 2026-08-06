<div class="auth-header text-center">
    <a href="{{ route('home') }}" class="auth-logo d-inline-flex justify-content-center mb-3 text-decoration-none">
        <x-logo height="42" />
    </a>
    <h2 class="auth-title">Welcome to ShopCalm</h2>
    <p class="auth-subtitle">Sign in or create your account</p>
</div>

<form id="form-initial" class="auth-form" onsubmit="return false;">
    <div class="form-group">
        <label for="initial_identifier">Email or Mobile Number</label>
        <input
            id="initial_identifier"
            class="auth-input"
            type="text"
            name="identifier"
            required
            autofocus
            autocomplete="username"
            placeholder="Enter your email or mobile"
            inputmode="email"
        >
        <span class="field-hint">We'll check if you have an account</span>
    </div>

    <button type="button" id="initial-continue-btn" class="auth-submit-btn">
        <span class="btn-text">Continue</span>
        <span class="btn-spinner" style="display:none;">
            <svg class="spin-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" width="18" height="18">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
            </svg>
            Checking...
        </span>
        <svg class="btn-arrow" xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
        </svg>
    </button>

    <div class="auth-divider">
        <span>OR</span>
    </div>

    <a href="{{ route('google.redirect') }}" class="google-btn">
        <img src="https://www.gstatic.com/firebasejs/ui/2.0.0/images/auth/google.svg" alt="Google">
        <span>Continue with Google</span>
    </a>
</form>
