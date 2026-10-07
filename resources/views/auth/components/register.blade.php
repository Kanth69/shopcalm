@php
    $rawStoreName = \App\Models\Setting::get('store_name', 'ShopCalm');
    $brandDisplay = str_ireplace('shopcalm', 'ShopCalm', $rawStoreName);
@endphp
{{-- Registration Sub-Step 1: Personal Details (Name, Phone, Email, Referral) --}}
<div id="reg-substep-details">
    <div class="auth-header text-center position-relative">
        <button type="button" class="back-nav-btn" id="register-top-back-btn">
            <i class="bi bi-arrow-left"></i>
            <span>Back</span>
        </button>
        <a href="{{ route('home') }}" class="auth-logo d-inline-flex align-items-center justify-content-center mb-2 text-decoration-none">
            <x-logo height="36" />
        </a>

        <!-- Stepper Indicator -->
        <div class="auth-stepper">
            <div class="step-pill active">
                <span class="step-dot">1</span>
                <span>Details</span>
            </div>
            <div class="step-connector"></div>
            <div class="step-pill">
                <span class="step-dot">2</span>
                <span>Verify</span>
            </div>
            <div class="step-connector"></div>
            <div class="step-pill">
                <span class="step-dot">3</span>
                <span>Password</span>
            </div>
        </div>

        <h2 class="auth-title">Create Account</h2>
        <p class="auth-subtitle">Step 1: Tell us about yourself</p>
    </div>

    <!-- Alert Banner -->
    <div id="register-details-alert" class="auth-alert error" style="display: none;">
        <i class="bi bi-exclamation-triangle-fill me-2"></i>
        <span id="register-details-alert-msg"></span>
    </div>

    <form id="form-register-details" class="auth-form" onsubmit="return false;">
        <div class="form-group">
            <label for="name">Full Name <span class="text-danger">*</span></label>
            <input id="name" class="auth-input" type="text" name="name" value="{{ old('name') }}" required placeholder="e.g. Rahul Sharma" autofocus>
        </div>

        <div class="form-row">
            <div class="form-group flex-1">
                <label for="register_mobile">Mobile Number <span class="text-danger">*</span></label>
                <input id="register_mobile" class="auth-input" type="tel" name="mobile_number" value="{{ old('mobile_number') }}" required pattern="[0-9]{10}" maxlength="10" inputmode="numeric" placeholder="10-digit mobile number">
            </div>

            <div class="form-group flex-1">
                <label for="register_email">Email Address <small class="text-muted">(Optional)</small></label>
                <input id="register_email" class="auth-input" type="email" name="email" value="{{ old('email') }}" placeholder="name@example.com (Optional)">
            </div>
        </div>

        <div class="form-group">
            <label for="referral_code" class="d-flex justify-content-between">
                <span>Referral Code <small class="text-muted">(Optional)</small></span>
                <span class="text-success small fw-bold"><i class="bi bi-gift-fill me-1"></i>₹50 Bonus</span>
            </label>
            <input id="referral_code" class="auth-input font-monospace text-uppercase" type="text" name="referral_code" value="{{ request('ref', old('referral_code', session('referral_code'))) }}" placeholder="e.g. WISE1234">
        </div>

        <button type="button" id="reg-continue-to-otp-btn" class="auth-submit-btn">
            <span>Continue & Verify WhatsApp OTP</span>
            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
            </svg>
        </button>

        <p class="text-center text-muted mt-3 mb-0" style="font-size: 0.76rem; line-height: 1.5;">
            By signing up, you agree to {{ $brandDisplay }}'s 
            <a href="{{ route('page.terms') }}" target="_blank" class="text-primary text-decoration-none fw-semibold">Terms & Conditions</a> 
            and 
            <a href="{{ route('page.privacy') }}" target="_blank" class="text-primary text-decoration-none fw-semibold">Privacy Policy</a>.
        </p>
    </form>
</div>

{{-- Registration Sub-Step 2: Email OTP Verification --}}
<div id="reg-substep-otp" style="display: none;">
    <div class="auth-header text-center position-relative">
        <button type="button" class="back-nav-btn" id="reg-otp-back-btn">
            <i class="bi bi-arrow-left"></i>
            <span>Back</span>
        </button>
        <a href="{{ route('home') }}" class="auth-logo d-inline-flex align-items-center justify-content-center mb-2 text-decoration-none">
            <x-logo height="36" />
        </a>

        <!-- Stepper Indicator -->
        <div class="auth-stepper">
            <div class="step-pill completed">
                <span class="step-dot"><i class="bi bi-check-lg"></i></span>
                <span>Details</span>
            </div>
            <div class="step-connector active"></div>
            <div class="step-pill active">
                <span class="step-dot">2</span>
                <span>Verify</span>
            </div>
            <div class="step-connector"></div>
            <div class="step-pill">
                <span class="step-dot">3</span>
                <span>Password</span>
            </div>
        </div>

        <h2 class="auth-title">Verify WhatsApp OTP 📱</h2>
        <p class="auth-subtitle mb-2">Enter the 6-digit code sent to your mobile number</p>

        <!-- Clean Centered Phone Pill -->
        <div class="d-flex justify-content-center mb-3">
            <div class="email-banner-pill d-inline-flex align-items-center gap-2">
                <i class="bi bi-whatsapp text-success flex-shrink-0" style="font-size: 13px;"></i>
                <span id="reg-display-email" class="fw-bold email-text"></span>
                <button type="button" id="edit-reg-email-btn" class="edit-pill-btn" title="Change mobile">
                    <i class="bi bi-pencil-fill me-0.5" style="font-size: 9.5px;"></i> Edit
                </button>
            </div>
        </div>
    </div>

    <!-- Alert Banner -->
    <div id="register-otp-alert" class="auth-alert" style="display: none;">
        <i id="register-otp-alert-icon" class="bi bi-info-circle-fill me-2"></i>
        <span id="register-otp-alert-msg"></span>
    </div>

    <form id="form-register-otp" class="auth-form" onsubmit="return false;">
        <!-- 6 Individual Digit Boxes -->
        <div class="form-group">
            <label class="text-center text-muted fw-bold mb-1" style="font-size: 12px; letter-spacing: 0.5px;">ENTER 6-DIGIT CODE</label>
            <div class="otp-boxes-wrapper" id="otp-boxes-container">
                <input type="tel" maxlength="1" class="otp-digit-input" data-index="0" inputmode="numeric" pattern="[0-9]*" autocomplete="one-time-code">
                <input type="tel" maxlength="1" class="otp-digit-input" data-index="1" inputmode="numeric" pattern="[0-9]*">
                <input type="tel" maxlength="1" class="otp-digit-input" data-index="2" inputmode="numeric" pattern="[0-9]*">
                <input type="tel" maxlength="1" class="otp-digit-input" data-index="3" inputmode="numeric" pattern="[0-9]*">
                <input type="tel" maxlength="1" class="otp-digit-input" data-index="4" inputmode="numeric" pattern="[0-9]*">
                <input type="tel" maxlength="1" class="otp-digit-input" data-index="5" inputmode="numeric" pattern="[0-9]*">
            </div>
            <input type="hidden" name="otp" id="reg_otp">
        </div>

        <!-- Countdown & Resend Option -->
        <div class="d-flex align-items-center justify-content-between my-1 px-1">
            <span class="text-muted" style="font-size: 12px;">Didn't receive code?</span>
            <div class="d-flex align-items-center gap-1.5">
                <span id="otp-countdown-pill" class="badge rounded-pill bg-light text-muted border font-monospace px-2 py-1" style="font-size: 11px;">
                    <i class="bi bi-clock me-1"></i><span id="otp-countdown-text">60s</span>
                </span>
                <button type="button" id="reg-resend-otp-btn" class="btn btn-sm btn-link text-primary p-0 fw-bold text-decoration-none" style="font-size: 12px; display: none;">
                    <i class="bi bi-arrow-repeat me-0.5"></i> Resend OTP
                </button>
            </div>
        </div>

        <button type="button" id="reg-verify-otp-btn" class="auth-submit-btn">
            <span>Verify & Continue</span>
            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
            </svg>
        </button>
    </form>
</div>

{{-- Registration Sub-Step 3: Set Password --}}
<div id="reg-substep-password" style="display: none;">
    <div class="auth-header text-center position-relative">
        <a href="{{ route('home') }}" class="auth-logo d-inline-flex justify-content-center mb-2 text-decoration-none">
            <x-logo height="36" />
        </a>

        <!-- Stepper Indicator -->
        <div class="auth-stepper">
            <div class="step-pill completed">
                <span class="step-dot"><i class="bi bi-check-lg"></i></span>
                <span>Details</span>
            </div>
            <div class="step-connector active"></div>
            <div class="step-pill completed">
                <span class="step-dot"><i class="bi bi-check-lg"></i></span>
                <span>Verify</span>
            </div>
            <div class="step-connector active"></div>
            <div class="step-pill active">
                <span class="step-dot">3</span>
                <span>Password</span>
            </div>
        </div>

        <h2 class="auth-title">Set Password 🔐</h2>
        <p class="auth-subtitle">Final step: Secure your new account</p>
    </div>

    <!-- Alert Banner -->
    <div id="register-password-alert" class="auth-alert error" style="display: none;">
        <i class="bi bi-exclamation-triangle-fill me-2"></i>
        <span id="register-password-alert-msg"></span>
    </div>

    <form id="form-register-final" method="POST" action="{{ route('register') }}" class="auth-form">
        @csrf
        <input type="hidden" name="name" id="final_reg_name">
        <input type="hidden" name="mobile_number" id="final_reg_mobile">
        <input type="hidden" name="email" id="final_reg_email">
        <input type="hidden" name="referral_code" id="final_reg_referral">
        <input type="hidden" name="otp" id="final_reg_otp">

        <div class="form-group">
            <label for="register_password">Create Password <span class="text-danger">*</span></label>
            <div class="password-input-wrapper">
                <input id="register_password" class="auth-input" type="password" name="password" required minlength="8" placeholder="At least 8 characters" autofocus>
                <button type="button" class="password-toggle-btn" onclick="togglePasswordVisibility('register_password', this)" title="Show password">
                    <i class="bi bi-eye-slash"></i>
                </button>
            </div>
            <span class="field-hint">Use 8+ characters with a mix of letters & numbers</span>
        </div>

        <div class="form-group">
            <label for="password_confirmation">Confirm Password <span class="text-danger">*</span></label>
            <div class="password-input-wrapper">
                <input id="password_confirmation" class="auth-input" type="password" name="password_confirmation" required placeholder="Re-enter your password">
                <button type="button" class="password-toggle-btn" onclick="togglePasswordVisibility('password_confirmation', this)" title="Show password">
                    <i class="bi bi-eye-slash"></i>
                </button>
            </div>
        </div>

        <button type="submit" id="create-account-btn" class="auth-submit-btn">
            <span>Complete & Create Account 🎉</span>
        </button>
    </form>
</div>
