<x-guest-layout>
    <x-slot name="title">Create Account</x-slot>

    <div class="auth-card-container">
        <div class="auth-header text-center">
            <a href="{{ route('home') }}" class="auth-logo d-inline-flex justify-content-center mb-3 text-decoration-none">
                <x-logo height="38" />
            </a>
            <h2 class="auth-title">Get Started</h2>
            <p class="auth-subtitle">Join the WiseKart shopping community</p>
        </div>

        <!-- Alert messages -->
        <div id="register-alert" class="alert alert-danger mb-3" style="display: none;"></div>
        <div id="register-success" class="alert alert-success mb-3" style="display: none;"></div>

        <form id="register-form" method="POST" action="{{ route('register') }}" class="auth-form">
            @csrf

            <div class="form-group">
                <label for="name">Full Name <span class="text-danger">*</span></label>
                <input id="name" class="auth-input" type="text" name="name" value="{{ old('name') }}" required autofocus placeholder="Enter your full name">
                <x-input-error :messages="$errors->get('name')" class="mt-2" />
            </div>

            <div class="form-group">
                <label for="mobile_number">Mobile Number <span class="text-danger">*</span></label>
                <div class="input-group">
                    <input id="mobile_number" class="auth-input form-control" type="tel" name="mobile_number" value="{{ old('mobile_number') }}" required placeholder="10-digit mobile number" maxlength="10">
                    <button type="button" id="btn-send-reg-otp" class="btn btn-outline-success fw-bold px-3">
                        <span>Get OTP</span>
                        <i class="bi bi-whatsapp ms-1"></i>
                    </button>
                </div>
                <small id="otp-sent-info" class="text-success mt-1" style="display: none;"></small>
                <x-input-error :messages="$errors->get('mobile_number')" class="mt-2" />
            </div>

            <div class="form-group">
                <label for="otp">WhatsApp 6-Digit OTP Code <span class="text-danger">*</span></label>
                <input id="otp" class="auth-input" type="text" name="otp" required placeholder="Enter 6-digit WhatsApp OTP" maxlength="6" style="letter-spacing: 2px;">
                <x-input-error :messages="$errors->get('otp')" class="mt-2" />
            </div>

            <div class="form-group">
                <label for="email">Email Address <span class="text-muted fw-normal">(Optional)</span></label>
                <input id="email" class="auth-input" type="email" name="email" value="{{ old('email') }}" placeholder="name@example.com (Optional)">
                <x-input-error :messages="$errors->get('email')" class="mt-2" />
            </div>

            <div class="form-group">
                <label for="password">Password <span class="text-danger">*</span></label>
                <input id="password" class="auth-input" type="password" name="password" required placeholder="Create a strong password">
                <x-input-error :messages="$errors->get('password')" class="mt-2" />
            </div>

            <div class="form-group">
                <label for="password_confirmation">Confirm Password <span class="text-danger">*</span></label>
                <input id="password_confirmation" class="auth-input" type="password" name="password_confirmation" required placeholder="Repeat your password">
            </div>

            <button type="submit" id="btn-submit-register" class="auth-submit-btn">
                <span>Create Account</span>
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                </svg>
            </button>

            <div class="auth-footer mt-4">
                Already a member? <a href="{{ route('login') }}">Sign In</a>
            </div>
        </form>
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const btnSendOtp = document.getElementById('btn-send-reg-otp');
        const mobileInput = document.getElementById('mobile_number');
        const otpInfo = document.getElementById('otp-sent-info');
        const alertBox = document.getElementById('register-alert');

        if (btnSendOtp) {
            btnSendOtp.addEventListener('click', function() {
                const mobile = mobileInput.value.trim();
                if (mobile.length !== 10) {
                    alertBox.textContent = 'Please enter a valid 10-digit mobile number first.';
                    alertBox.style.display = 'block';
                    return;
                }

                alertBox.style.display = 'none';
                btnSendOtp.disabled = true;
                btnSendOtp.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Sending...';

                fetch('{{ route("auth.send-otp") }}', {
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
                    btnSendOtp.disabled = false;
                    btnSendOtp.innerHTML = 'Get OTP <i class="bi bi-whatsapp ms-1"></i>';

                    if (res.status === 200 && data.success) {
                        otpInfo.textContent = 'OTP sent! ' + (data.dev_otp ? '(Dev Code: ' + data.dev_otp + ')' : 'Check WhatsApp.');
                        otpInfo.style.display = 'block';
                    } else {
                        const msg = data.errors ? Object.values(data.errors).flat().join(' ') : (data.message || 'Error sending OTP.');
                        alertBox.textContent = msg;
                        alertBox.style.display = 'block';
                    }
                })
                .catch(err => {
                    console.error(err);
                    btnSendOtp.disabled = false;
                    btnSendOtp.innerHTML = 'Get OTP <i class="bi bi-whatsapp ms-1"></i>';
                    alertBox.textContent = 'Network error sending OTP.';
                    alertBox.style.display = 'block';
                });
            });
        }
    });
    </script>

    <style>
        /* Shared Modern Auth Styles */
        .auth-card-container {
            padding: 5px;
        }

        .auth-header {
            text-align: center;
            margin-bottom: 30px;
        }

        .auth-logo {
            display: inline-flex;
            align-items: center;
            text-decoration: none;
            margin-bottom: 20px;
        }

        .auth-title {
            font-size: 22px;
            font-weight: 700;
            color: #1e293b;
            margin-bottom: 6px;
        }

        .auth-subtitle {
            font-size: 14px;
            color: #64748b;
        }

        .auth-form {
            display: flex;
            flex-direction: column;
            gap: 18px;
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
            letter-spacing: 0.5px;
        }

        .auth-input {
            background-color: #f8fafc;
            border: 2px solid #e2e8f0;
            border-radius: 12px;
            padding: 10px 16px;
            font-size: 15px;
            color: #1e293b;
            transition: all 0.2s ease;
            outline: none;
        }

        .auth-input:focus {
            border-color: #3b82f6;
            background-color: #fff;
            box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.1);
        }

        .auth-submit-btn {
            background: linear-gradient(135deg, #3b82f6 0%, #1e40af 100%);
            color: white;
            padding: 14px;
            border: none;
            border-radius: 12px;
            font-size: 16px;
            font-weight: 700;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            transition: all 0.2s ease;
            box-shadow: 0 4px 12px rgba(59, 130, 246, 0.2);
            margin-top: 10px;
        }

        .auth-submit-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(59, 130, 246, 0.3);
        }

        .auth-footer {
            text-align: center;
            margin-top: 15px;
            font-size: 14px;
            color: #64748b;
        }

        .auth-footer a {
            color: #3b82f6;
            font-weight: 700;
            text-decoration: none;
        }

        .auth-footer a:hover {
            text-decoration: underline;
        }
    </style>
</x-guest-layout>
