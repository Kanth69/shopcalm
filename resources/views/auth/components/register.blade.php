<div class="auth-header text-center">
    <button type="button" class="back-nav-btn" id="register-top-back-btn">
        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
        </svg>
        <span>Back</span>
    </button>
    <a href="{{ route('home') }}" class="auth-logo d-inline-flex justify-content-center mb-3 text-decoration-none">
        <x-logo height="42" />
    </a>
    <p class="auth-subtitle">Quick registration to start shopping</p>
</div>

<!-- Prominent Status Alert Banner Above Form -->
<div id="register-alert" class="auth-alert" style="{{ $errors->any() ? 'display: flex;' : 'display: none;' }}">
    <i id="register-alert-icon" class="{{ $errors->any() ? 'bi bi-exclamation-triangle-fill me-2' : 'bi bi-info-circle-fill me-2' }}"></i>
    <span id="register-alert-msg">
        @if($errors->has('otp'))
            {{ $errors->first('otp') }}
        @elseif($errors->any())
            {{ $errors->first() }}
        @endif
    </span>
</div>

<form id="form-register" method="POST" action="{{ route('register') }}" class="auth-form">
    @csrf

    <div class="form-row">
        <div class="form-group flex-1">
            <label for="name">Full Name <span class="text-danger">*</span></label>
            <input id="name" class="auth-input" type="text" name="name" value="{{ old('name') }}" required placeholder="Your full name">
        </div>

        <div class="form-group flex-1">
            <label for="register_mobile">Mobile Number <span class="text-danger">*</span></label>
            <input id="register_mobile" class="auth-input" type="tel" name="mobile_number" value="{{ old('mobile_number') }}" required pattern="[0-9]{10}" maxlength="10" placeholder="10-digit mobile">
        </div>
    </div>

    <div class="form-group">
        <label for="register_email">Email Address <span class="text-danger">*</span></label>
        <input id="register_email" class="auth-input" type="email" name="email" value="{{ old('email') }}" required placeholder="name@example.com">
    </div>

    <div class="form-row">
        <div class="form-group flex-1">
            <label for="register_password">Password <span class="text-danger">*</span></label>
            <div class="password-input-wrapper">
                <input id="register_password" class="auth-input" type="password" name="password" required minlength="8" placeholder="At least 8 chars">
                <button type="button" class="password-toggle-btn" onclick="togglePasswordVisibility('register_password', this)" title="Show password">
                    <i class="bi bi-eye-slash"></i>
                </button>
            </div>
        </div>

        <div class="form-group flex-1">
            <label for="password_confirmation">Confirm Password <span class="text-danger">*</span></label>
            <div class="password-input-wrapper">
                <input id="password_confirmation" class="auth-input" type="password" name="password_confirmation" required placeholder="Re-enter password">
                <button type="button" class="password-toggle-btn" onclick="togglePasswordVisibility('password_confirmation', this)" title="Show password">
                    <i class="bi bi-eye-slash"></i>
                </button>
            </div>
        </div>
    </div>

    <div class="form-group">
        <label for="otp">Email OTP <span class="text-danger">*</span></label>
        <div class="otp-input-group">
            <input id="otp" class="auth-input @error('otp') is-invalid @enderror" type="text" name="otp" required placeholder="6-digit OTP" maxlength="6">
            <button type="button" id="send-otp-btn" class="otp-btn">Send OTP</button>
        </div>
        <span id="otp-inline-error" class="text-danger small mt-1 d-block" style="{{ $errors->has('otp') ? 'display: block;' : 'display: none;' }}">
            <i class="bi bi-exclamation-triangle-fill me-1"></i><span id="otp-error-text">{{ $errors->first('otp') }}</span>
        </span>
    </div>

    <button type="submit" id="create-account-btn" class="auth-submit-btn">
        <span>Create Account</span>
    </button>

    <div class="auth-divider">
        <span>OR</span>
    </div>

    <a href="{{ route('google.redirect') }}" class="google-btn">
        <img src="https://www.gstatic.com/firebasejs/ui/2.0.0/images/auth/google.svg" alt="Google">
        <span>Register with Google</span>
    </a>
</form>
