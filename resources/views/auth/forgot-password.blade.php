<x-guest-layout>
    <x-slot name="title">Reset Password via WhatsApp OTP</x-slot>

    <div class="auth-card-container">
        <div class="auth-header text-center">
            <a href="{{ route('home') }}" class="auth-logo d-inline-flex align-items-center justify-content-center mb-3 text-decoration-none">
                <x-logo height="40" />
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
        <div id="forgot-success-alert" class="auth-alert success mb-3" style="display: none;">
            <i class="bi bi-check-circle-fill me-2 fs-5"></i>
            <span id="forgot-success-msg"></span>
        </div>

        <div id="forgot-form-container">
            <!-- Step 1: Request WhatsApp OTP Form -->
            <form id="request-otp-form" class="auth-form">
                @csrf
                <div class="form-group">
                    <label for="mobile_number">Mobile Number <span class="text-danger">*</span></label>
                    <div class="input-group auth-phone-input-group">
                        <span class="input-group-text bg-light fw-bold">+91</span>
                        <input id="mobile_number" class="auth-input form-control" type="tel" inputmode="numeric" name="mobile_number" maxlength="10" required autofocus placeholder="9876543210">
                    </div>
                </div>

                <button type="submit" id="btn-send-otp" class="auth-submit-btn">
                    <span>Send WhatsApp OTP</span>
                    <i class="bi bi-whatsapp ms-1"></i>
                </button>
            </form>

            <!-- Step 2: Reset Password Form (Hidden Initially) -->
            <form id="reset-password-form" class="auth-form mt-3" style="display: none;">
                @csrf
                <input type="hidden" id="reset_mobile_number" name="mobile_number">

                <div class="form-group">
                    <label for="otp">WhatsApp 6-Digit OTP Code <span class="text-danger">*</span></label>
                    <input id="otp" class="auth-input form-control" type="tel" inputmode="numeric" name="otp" maxlength="6" required placeholder="123456" style="letter-spacing: 4px; font-weight: bold; text-align: center;">
                </div>

                <div class="form-group">
                    <label for="password">New Password <span class="text-danger">*</span></label>
                    <div class="password-input-wrapper">
                        <input id="password" class="auth-input form-control" type="password" name="password" required placeholder="Minimum 8 characters">
                        <button type="button" class="password-toggle-btn" onclick="togglePasswordVisibility('password', this)" title="Show password">
                            <i class="bi bi-eye-slash"></i>
                        </button>
                    </div>
                </div>

                <div class="form-group">
                    <label for="password_confirmation">Confirm New Password <span class="text-danger">*</span></label>
                    <div class="password-input-wrapper">
                        <input id="password_confirmation" class="auth-input form-control" type="password" name="password_confirmation" required placeholder="Re-enter new password">
                        <button type="button" class="password-toggle-btn" onclick="togglePasswordVisibility('password_confirmation', this)" title="Show password">
                            <i class="bi bi-eye-slash"></i>
                        </button>
                    </div>
                </div>

                <button type="submit" id="btn-submit-reset" class="auth-submit-btn">
                    <span>Reset Password & Sign In</span>
                    <i class="bi bi-shield-check ms-1"></i>
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

    <style>
        .auth-card-container {
            padding: 4px 2px;
        }
        .auth-header {
            text-align: center;
            margin-bottom: 22px;
        }
        .auth-logo {
            display: inline-flex !important;
            align-items: center !important;
            justify-content: center !important;
            text-decoration: none;
            margin-bottom: 14px;
        }
        .auth-title {
            font-size: 22px;
            font-weight: 800;
            color: #0f172a;
            margin-bottom: 6px;
            letter-spacing: -0.4px;
        }
        .auth-subtitle {
            font-size: 14px;
            color: #64748b;
            line-height: 1.5;
            margin-bottom: 0;
        }
        .auth-form {
            display: flex;
            flex-direction: column;
            gap: 16px;
        }
        .form-group {
            display: flex;
            flex-direction: column;
            gap: 6px;
        }
        .form-group label {
            font-size: 12px;
            font-weight: 700;
            color: #475569;
            text-transform: uppercase;
            letter-spacing: 0.45px;
            margin-bottom: 0;
        }
        .auth-input {
            background-color: #f8fafc;
            border: 2px solid #e2e8f0;
            border-radius: 12px;
            padding: 11px 15px;
            font-size: 15px;
            color: #1e293b;
            transition: all 0.2s ease;
            outline: none;
            width: 100%;
        }
        .auth-input:focus {
            border-color: #3b82f6;
            background-color: #fff;
            box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.1);
        }
        .auth-phone-input-group {
            display: flex;
            align-items: stretch;
            width: 100%;
        }
        .auth-phone-input-group .input-group-text {
            background-color: #f1f5f9 !important;
            border: 2px solid #e2e8f0;
            border-right: none;
            border-radius: 12px 0 0 12px;
            color: #334155;
            font-size: 14px;
            padding: 0 14px;
            display: flex;
            align-items: center;
        }
        .auth-phone-input-group .auth-input {
            border-radius: 0 12px 12px 0 !important;
            flex: 1;
        }
        .password-input-wrapper {
            position: relative;
            width: 100%;
        }
        .password-input-wrapper .auth-input {
            padding-right: 46px;
        }
        .password-toggle-btn {
            position: absolute;
            right: 10px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            padding: 6px;
            color: #64748b;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 8px;
            transition: all 0.2s ease;
            z-index: 5;
            font-size: 18px;
        }
        .password-toggle-btn:hover {
            color: #3b82f6;
            background-color: #f1f5f9;
        }
        .auth-submit-btn {
            background: linear-gradient(135deg, #3b82f6 0%, #1e40af 100%);
            color: white;
            padding: 13px 16px;
            border: none;
            border-radius: 12px;
            font-size: 15.5px;
            font-weight: 700;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            transition: all 0.2s ease;
            box-shadow: 0 4px 12px rgba(59, 130, 246, 0.2);
            margin-top: 4px;
        }
        .auth-submit-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(59, 130, 246, 0.3);
        }
        .auth-submit-btn:disabled {
            opacity: 0.7;
            cursor: not-allowed;
            transform: none;
        }
        .back-nav-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            font-size: 13.5px;
            font-weight: 600;
            color: #475569;
            background: #f1f5f9;
            border: 1px solid #e2e8f0;
            padding: 8px 18px;
            border-radius: 20px;
            cursor: pointer;
            transition: all 0.2s;
        }
        .back-nav-btn:hover {
            background: #e2e8f0;
            color: #0f172a;
        }
        .auth-alert {
            display: flex;
            align-items: center;
            padding: 12px 14px;
            border-radius: 12px;
            font-size: 13px;
            font-weight: 600;
        }
        .auth-alert.success {
            background-color: #d1fae5;
            color: #065f46;
            border: 1px solid #10b981;
        }
        .auth-alert.error {
            background-color: #fee2e2;
            color: #991b1b;
            border: 1px solid #fecaca;
        }
        @media (max-width: 576px) {
            .auth-card-container {
                padding: 0;
            }
            .auth-header {
                margin-bottom: 16px;
            }
            .auth-logo {
                margin-bottom: 10px;
            }
            .auth-logo .logo-shopcalm-svg {
                width: 34px !important;
                height: 34px !important;
            }
            .auth-logo .logo-shopcalm-wordmark {
                font-size: 1.38rem !important;
            }
            .auth-title {
                font-size: 20px;
            }
            .auth-subtitle {
                font-size: 13px;
            }
            .auth-form {
                gap: 12px;
            }
            .auth-input {
                padding: 10px 14px;
                font-size: 15px;
                border-radius: 11px;
                min-height: 44px;
            }
            .auth-phone-input-group .input-group-text {
                border-radius: 11px 0 0 11px;
                padding: 0 12px;
            }
            .auth-phone-input-group .auth-input {
                border-radius: 0 11px 11px 0 !important;
            }
            .auth-submit-btn {
                padding: 12px 16px;
                font-size: 15px;
                border-radius: 11px;
                min-height: 46px;
            }
        }
    </style>

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
                    resetPassForm.style.display = 'flex';
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
