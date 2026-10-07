@php
    $rawStoreName = \App\Models\Setting::get('store_name', 'ShopCalm');
    $brandDisplay = str_ireplace('shopcalm', 'ShopCalm', $rawStoreName);
@endphp
<div class="auth-header text-center">
    <a href="{{ route('home') }}" class="auth-logo d-inline-flex align-items-center justify-content-center mb-3 text-decoration-none">
        <x-logo height="40" />
    </a>
    <h2 class="auth-title">Welcome to {{ $brandDisplay }}</h2>
    <p class="auth-subtitle">Sign in or create your account</p>
</div>

<form id="form-initial" class="auth-form" onsubmit="return false;">
    <div id="initial-alert" class="auth-alert error mb-3" style="display: none;">
        <i class="bi bi-exclamation-triangle-fill me-2" id="initial-alert-icon"></i>
        <span id="initial-alert-msg"></span>
    </div>

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

    <p class="text-center text-muted mt-3 mb-0" style="font-size: 0.76rem; line-height: 1.5;">
        By continuing, you agree to {{ $brandDisplay }}'s 
        <a href="{{ route('page.terms') }}" target="_blank" class="text-primary text-decoration-none fw-semibold">Terms & Conditions</a> 
        and 
        <a href="{{ route('page.privacy') }}" target="_blank" class="text-primary text-decoration-none fw-semibold">Privacy Policy</a>.
    </p>
</form>
