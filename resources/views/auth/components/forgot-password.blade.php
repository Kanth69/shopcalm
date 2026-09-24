<div class="auth-header text-center">
    <a href="{{ route('home') }}" class="auth-logo d-inline-flex justify-content-center mb-3 text-decoration-none">
        <x-logo height="42" />
    </a>
    <h2 class="auth-title">Forgot Password</h2>
    <p class="auth-subtitle" id="forgot-subtitle-text">Enter your mobile number to receive a 6-digit WhatsApp OTP code.</p>
</div>

<!-- Status Alert Banner -->
<div id="forgot-alert" class="auth-alert" style="display: none;">
    <i id="forgot-alert-icon" class="bi bi-exclamation-triangle-fill me-2"></i>
    <span id="forgot-alert-msg"></span>
</div>

<!-- Step 1: Request WhatsApp OTP Form -->
<form id="form-forgot-request-otp" class="auth-form">
    @csrf
    <div class="form-group">
        <label for="mobile_number_for_forgot">Registered Mobile Number <span class="text-danger">*</span></label>
        <div class="input-group">
            <span class="input-group-text bg-light fw-bold">+91</span>
            <input id="mobile_number_for_forgot" class="auth-input form-control" type="text" name="mobile_number" maxlength="10" required placeholder="10-digit mobile number">
        </div>
    </div>

    <button type="submit" id="forgot-send-otp-btn" class="auth-submit-btn">
        <span>Send WhatsApp OTP</span>
        <i class="bi bi-whatsapp ms-1"></i>
    </button>
</form>

<!-- Step 2: Reset Password Form (Initially Hidden) -->
<form id="form-forgot-reset-password" class="auth-form mt-2" style="display: none;">
    @csrf
    <input type="hidden" id="reset_modal_mobile_number" name="mobile_number">

    <div class="form-group mb-2">
        <label for="modal_otp">WhatsApp 6-Digit OTP Code <span class="text-danger">*</span></label>
        <input id="modal_otp" class="auth-input form-control" type="text" name="otp" maxlength="6" required placeholder="123456" style="letter-spacing: 4px; font-weight: bold; text-align: center;">
    </div>

    <div class="form-group mb-2">
        <label for="modal_new_password">New Password <span class="text-danger">*</span></label>
        <input id="modal_new_password" class="auth-input form-control" type="password" name="password" required placeholder="Minimum 8 characters">
    </div>

    <div class="form-group mb-2">
        <label for="modal_password_confirmation">Confirm Password <span class="text-danger">*</span></label>
        <input id="modal_password_confirmation" class="auth-input form-control" type="password" name="password_confirmation" required placeholder="Re-enter new password">
    </div>

    <button type="submit" id="forgot-reset-btn" class="auth-submit-btn">
        <span>Reset Password & Sign In</span>
        <i class="bi bi-shield-check ms-1"></i>
    </button>
</form>

<div class="text-center mt-3">
    <button type="button" id="back-to-login" class="back-nav-btn mx-auto">
        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
        </svg>
        <span>Back to Login</span>
    </button>
</div>
