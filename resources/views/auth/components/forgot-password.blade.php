<div class="auth-header text-center">
    <a href="{{ route('home') }}" class="auth-logo d-inline-flex align-items-center justify-content-center mb-3 text-decoration-none">
        <x-logo height="40" />
    </a>
    <h2 class="auth-title">Reset Password</h2>
    <p class="auth-subtitle" id="forgot-subtitle-text">Confirm your registered WhatsApp number</p>
</div>

<!-- Status Alert Banner -->
<div id="forgot-alert" class="auth-alert" style="display: none;">
    <i id="forgot-alert-icon" class="bi bi-exclamation-triangle-fill me-2"></i>
    <span id="forgot-alert-msg"></span>
</div>

<!-- Step 1: Confirmation Card with Masked Mobile -->
<div id="forgot-step-confirm" class="auth-form">
    <div class="p-3 rounded-3 border bg-light text-start mb-2" style="border-color: #e2e8f0 !important;">
        <div id="forgot-user-name-row" class="align-items-center mb-2 pb-2 border-bottom" style="display: none; border-color: #e2e8f0 !important;">
            <i class="bi bi-person-circle text-primary me-2 fs-5"></i>
            <span class="fw-bold text-dark" id="forgot-user-name-display">Customer</span>
        </div>
        <div class="d-flex align-items-center">
            <div class="bg-success-subtle text-success p-2 rounded-2 me-2.5 d-flex align-items-center justify-content-center" style="width: 38px; height: 38px;">
                <i class="bi bi-whatsapp fs-5"></i>
            </div>
            <div>
                <small class="text-muted d-block" style="font-size: 11px; font-weight: 700; letter-spacing: 0.3px;">REGISTERED WHATSAPP NUMBER</small>
                <div class="fw-bold fs-6 text-dark" id="forgot-masked-mobile-display" style="letter-spacing: 1.5px;">+91 ••******••</div>
            </div>
        </div>
        <p class="small text-muted mt-2.5 mb-0" style="font-size: 12px; line-height: 1.45;">
            A 6-digit WhatsApp OTP verification code will be sent to this registered number to safely reset your password.
        </p>
    </div>

    <input type="hidden" id="mobile_number_for_forgot" name="mobile_number">

    <button type="button" id="forgot-send-otp-btn" class="auth-submit-btn w-100 justify-content-center bg-success text-white border-0 py-2.5">
        <span>Yes, Send OTP & Continue</span>
        <i class="bi bi-arrow-right ms-2"></i>
    </button>
</div>

<!-- Step 2: Reset Password Form (Initially Hidden) -->
<form id="form-forgot-reset-password" class="auth-form mt-2" style="display: none;">
    @csrf
    <input type="hidden" id="reset_modal_mobile_number" name="mobile_number">

    <div class="form-group mb-2">
        <label for="modal_otp">WhatsApp 6-Digit OTP Code <span class="text-danger">*</span></label>
        <input id="modal_otp" class="auth-input form-control" type="tel" inputmode="numeric" name="otp" maxlength="6" required placeholder="Enter 6-digit OTP" autocomplete="one-time-code">
    </div>

    <div class="form-group mb-2">
        <label for="modal_new_password">New Password <span class="text-danger">*</span></label>
        <div class="password-input-wrapper">
            <input id="modal_new_password" class="auth-input form-control" type="password" name="password" required placeholder="Minimum 8 characters">
            <button type="button" class="password-toggle-btn" onclick="togglePasswordVisibility('modal_new_password', this)" title="Show password">
                <i class="bi bi-eye-slash"></i>
            </button>
        </div>
    </div>

    <div class="form-group mb-2">
        <label for="modal_password_confirmation">Confirm Password <span class="text-danger">*</span></label>
        <div class="password-input-wrapper">
            <input id="modal_password_confirmation" class="auth-input form-control" type="password" name="password_confirmation" required placeholder="Re-enter new password">
            <button type="button" class="password-toggle-btn" onclick="togglePasswordVisibility('modal_password_confirmation', this)" title="Show password">
                <i class="bi bi-eye-slash"></i>
            </button>
        </div>
    </div>

    <button type="submit" id="forgot-reset-btn" class="auth-submit-btn w-100 justify-content-center">
        <span>Reset Password & Sign In</span>
        <i class="bi bi-shield-check ms-1"></i>
    </button>
</form>

<div class="text-center mt-3">
    <button type="button" id="back-to-login" class="back-bottom-btn mx-auto">
        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
        </svg>
        <span>Back to Login</span>
    </button>
</div>
