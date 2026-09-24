<x-guest-layout>
    <x-slot name="title">Reset Password via WhatsApp OTP</x-slot>

    <div class="auth-card-container">
        <div class="auth-header text-center">
            <a href="{{ route('home') }}" class="auth-logo d-inline-flex justify-content-center mb-3 text-decoration-none">
                <x-logo height="38" />
            </a>
            <h2 class="auth-title">Forgot Password</h2>
            <p class="auth-subtitle">Enter your registered mobile number to receive a 6-digit WhatsApp OTP code.</p>
        </div>

        <!-- Red Error Banner Alert -->
        <div id="forgot-error-alert" class="auth-alert error mb-3" style="display: none;">
            <i class="bi bi-exclamation-triangle-fill me-2 fs-5"></i>
            <span id="forgot-error-msg"></span>
        </div>

        <!-- Green Success Banner Alert -->
        <div id="forgot-success-alert" class="auth-alert success mb-3" style="display: none; background-color: #d1fae5; color: #065f46; border: 1px solid #10b981; padding: 12px; border-radius: 8px;">
            <i class="bi bi-check-circle-fill me-2 fs-5"></i>
            <span id="forgot-success-msg"></span>
        </div>

        <div id="forgot-form-container">
            <!-- Step 1: Request WhatsApp OTP Form -->
            <form id="request-otp-form" class="auth-form">
                @csrf
                <div class="form-group">
                    <label for="mobile_number">Mobile Number <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <span class="input-group-text bg-light fw-bold">+91</span>
                        <input id="mobile_number" class="auth-input form-control" type="text" name="mobile_number" maxlength="10" required autofocus placeholder="9876543210">
                    </div>
                </div>

                <button type="submit" id="btn-send-otp" class="auth-submit-btn">
                    <span>Send WhatsApp OTP</span>
                    <i class="bi bi-whatsapp ms-2"></i>
                </button>
            </form>

            <!-- Step 2: Reset Password Form (Hidden Initially) -->
            <form id="reset-password-form" class="auth-form mt-4" style="display: none;">
                @csrf
                <input type="hidden" id="reset_mobile_number" name="mobile_number">

                <div class="form-group mb-3">
                    <label for="otp">WhatsApp 6-Digit OTP Code <span class="text-danger">*</span></label>
                    <input id="otp" class="auth-input form-control" type="text" name="otp" maxlength="6" required placeholder="123456" style="letter-spacing: 4px; font-weight: bold; text-align: center;">
                </div>

                <div class="form-group mb-3">
                    <label for="password">New Password <span class="text-danger">*</span></label>
                    <input id="password" class="auth-input form-control" type="password" name="password" required placeholder="Minimum 8 characters">
                </div>

                <div class="form-group mb-3">
                    <label for="password_confirmation">Confirm New Password <span class="text-danger">*</span></label>
                    <input id="password_confirmation" class="auth-input form-control" type="password" name="password_confirmation" required placeholder="Re-enter new password">
                </div>

                <button type="submit" id="btn-submit-reset" class="auth-submit-btn">
                    <span>Reset Password & Sign In</span>
                    <i class="bi bi-shield-check ms-2"></i>
                </button>
            </form>
        </div>

        <div class="text-center mt-4">
            <a href="{{ route('login') }}" class="back-nav-btn mx-auto text-decoration-none">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                <span>Back to Login</span>
            </a>
        </div>
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const reqOtpForm = document.getElementById('request-otp-form');
        const resetPassForm = document.getElementById('reset-password-form');
        const errAlert = document.getElementById('forgot-error-alert');
        const succAlert = document.getElementById('forgot-success-alert');

        function showError(msg) {
            if (succAlert) succAlert.style.display = 'none';
            if (errAlert) {
                document.getElementById('forgot-error-msg').textContent = msg;
                errAlert.style.display = 'flex';
            }
        }

        function showSuccess(msg) {
            if (errAlert) errAlert.style.display = 'none';
            if (succAlert) {
                document.getElementById('forgot-success-msg').textContent = msg;
                succAlert.style.display = 'flex';
            }
        }

        // Step 1: Send WhatsApp OTP
        reqOtpForm.addEventListener('submit', function(e) {
            e.preventDefault();
            const btn = document.getElementById('btn-send-otp');
            const originalText = btn.innerHTML;
            btn.disabled = true;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Sending WhatsApp OTP...';

            const mobile = document.getElementById('mobile_number').value;

            fetch('{{ route("password.send-reset-otp") }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json',
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ mobile_number: mobile })
            })
            .then(async res => {
                const data = await res.json();
                btn.disabled = false;
                btn.innerHTML = originalText;

                if (res.status === 200 && data.success) {
                    showSuccess(data.message + (data.dev_otp ? ' (Dev OTP: ' + data.dev_otp + ')' : ''));
                    document.getElementById('reset_mobile_number').value = mobile;
                    reqOtpForm.style.display = 'none';
                    resetPassForm.style.display = 'block';
                } else {
                    const msg = data.errors ? Object.values(data.errors).flat().join(' ') : (data.message || 'Mobile number not found.');
                    showError(msg);
                }
            })
            .catch(err => {
                console.error(err);
                btn.disabled = false;
                btn.innerHTML = originalText;
                showError('Network error occurred. Please try again.');
            });
        });

        // Step 2: Reset Password & Auto-Login
        resetPassForm.addEventListener('submit', function(e) {
            e.preventDefault();
            const btn = document.getElementById('btn-submit-reset');
            const originalText = btn.innerHTML;
            btn.disabled = true;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Updating Password...';

            const formData = new FormData(resetPassForm);

            fetch('{{ route("password.reset-whatsapp") }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                },
                body: formData
            })
            .then(async res => {
                const data = await res.json();
                if (res.status === 200 && data.success) {
                    showSuccess('Password reset successfully! Redirecting...');
                    setTimeout(() => {
                        window.location.href = data.redirect || '{{ route("home") }}';
                    }, 1000);
                } else {
                    btn.disabled = false;
                    btn.innerHTML = originalText;
                    const msg = data.errors ? Object.values(data.errors).flat().join(' ') : (data.message || 'Could not reset password.');
                    showError(msg);
                }
            })
            .catch(err => {
                console.error(err);
                btn.disabled = false;
                btn.innerHTML = originalText;
                showError('Network error occurred. Please try again.');
            });
        });
    });
    </script>
</x-guest-layout>
