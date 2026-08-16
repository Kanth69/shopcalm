<x-guest-layout>
    <x-slot name="title">Reset Password</x-slot>

    <div class="auth-card-container">
        <div class="auth-header">
            <a href="{{ route('home') }}" class="auth-logo">
                <span class="logo-w">W</span>
                <div class="logo-text">Shopcalm<span>.in</span></div>
            </a>
            <h2 class="auth-title">Set New Password</h2>
            <p class="auth-subtitle">Enter your new password below to reset your account credentials.</p>
        </div>

        <!-- Green Success Banner Alert -->
        <div id="reset-success-alert" class="auth-alert success mb-3" style="display: none;">
            <i class="bi bi-check-circle-fill me-2 fs-5"></i>
            <span id="reset-success-msg">Your password has been changed successfully! Please go back and login.</span>
        </div>

        <!-- Red Error Banner Alert -->
        <div id="reset-error-alert" class="auth-alert error mb-3" style="display: none;">
            <i class="bi bi-exclamation-triangle-fill me-2 fs-5"></i>
            <span id="reset-error-msg"></span>
        </div>

        <form id="reset-password-form" method="POST" action="{{ route('password.store') }}" class="auth-form">
            @csrf

            <!-- Password Reset Token -->
            <input type="hidden" name="token" value="{{ $request->route('token') }}">

            <!-- Email Address -->
            <div class="form-group">
                <label for="email">Email Address <span class="text-danger">*</span></label>
                <input id="email" class="auth-input readonly-input" type="email" name="email" value="{{ old('email', $request->email) }}" required readonly>
            </div>

            <!-- Password -->
            <div class="form-group">
                <label for="password">New Password <span class="text-danger">*</span></label>
                <div class="password-input-wrapper">
                    <input id="password" class="auth-input" type="password" name="password" required placeholder="Minimum 6 characters" autofocus minlength="6">
                    <button type="button" class="password-toggle-btn" onclick="togglePasswordVisibility('password', this)" title="Show password">
                        <i class="bi bi-eye-slash"></i>
                    </button>
                </div>
            </div>

            <!-- Confirm Password -->
            <div class="form-group">
                <label for="password_confirmation">Confirm New Password <span class="text-danger">*</span></label>
                <div class="password-input-wrapper">
                    <input id="password_confirmation" class="auth-input" type="password" name="password_confirmation" required placeholder="Re-enter new password">
                    <button type="button" class="password-toggle-btn" onclick="togglePasswordVisibility('password_confirmation', this)" title="Show password">
                        <i class="bi bi-eye-slash"></i>
                    </button>
                </div>
            </div>

            <button type="submit" id="btn-submit-reset" class="auth-submit-btn">
                <span>Update Password</span>
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                </svg>
            </button>
        </form>

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
        const form = document.getElementById('reset-password-form');
        if (!form) return;

        form.addEventListener('submit', function(e) {
            e.preventDefault();

            const btn = document.getElementById('btn-submit-reset');
            const originalText = btn.innerHTML;
            btn.disabled = true;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Updating...';

            const errAlert = document.getElementById('reset-error-alert');
            const succAlert = document.getElementById('reset-success-alert');
            if (errAlert) errAlert.style.display = 'none';
            if (succAlert) succAlert.style.display = 'none';

            const formData = new FormData(form);

            fetch(form.action, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                },
                body: formData
            })
            .then(async res => {
                const data = await res.json();
                if (res.status === 200 || data.success) {
                    // Password changed successfully -> hide form and show success state
                    const formContainer = form.parentElement;
                    formContainer.innerHTML = `
                        <style>
                            @keyframes fadeScale { 0% { opacity: 0; transform: scale(0.95) translateY(10px); } 100% { opacity: 1; transform: scale(1) translateY(0); } }
                            @keyframes popInCheck { 0% { transform: scale(0); } 70% { transform: scale(1.15); } 100% { transform: scale(1); } }
                            .premium-success-icon {
                                width: 72px; height: 72px;
                                background: linear-gradient(135deg, rgba(16,185,129,0.15) 0%, rgba(16,185,129,0.05) 100%);
                                border-radius: 50%;
                                display: flex; align-items: center; justify-content: center;
                                box-shadow: 0 8px 32px rgba(16, 185, 129, 0.15), inset 0 2px 0 rgba(255,255,255,0.6);
                                margin: 0 auto 24px auto;
                                position: relative;
                            }
                            .premium-success-icon::after {
                                content: ''; position: absolute; top: -8px; left: -8px; right: -8px; bottom: -8px;
                                border: 1px solid rgba(16,185,129,0.2); border-radius: 50%;
                            }
                        </style>
                        <div style="animation: fadeScale 0.5s ease-out forwards; padding: 20px 10px; text-align: center;">
                            <div class="premium-success-icon">
                                <svg xmlns="http://www.w3.org/2000/svg" width="34" height="34" fill="#10b981" viewBox="0 0 16 16" style="animation: popInCheck 0.6s cubic-bezier(0.175, 0.885, 0.32, 1.275) 0.1s both;">
                                  <path d="M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0zm-3.97-3.03a.75.75 0 0 0-1.08.022L7.477 9.417 5.384 7.323a.75.75 0 0 0-1.06 1.06L6.97 11.03a.75.75 0 0 0 1.079-.02l3.992-4.99a.75.75 0 0 0-.01-1.05z"/>
                                </svg>
                            </div>
                            <h3 style="font-weight: 800; font-size: 24px; color: #1e293b; letter-spacing: -0.5px; margin-bottom: 12px;">Password Changed!</h3>
                            <p style="color: #64748b; font-size: 15px; line-height: 1.6; margin-bottom: 32px; padding: 0 15px;">You can now use your new password to sign in to your account.</p>
                            <div class="d-grid gap-3" style="display: flex; flex-direction: column; gap: 12px;">
                                <a href="{{ route('login') }}" class="btn btn-primary fw-medium" style="background: linear-gradient(135deg, #3b82f6 0%, #1e40af 100%); border: none; border-radius: 12px; padding: 14px; text-decoration: none; color: white; box-shadow: 0 4px 12px rgba(59, 130, 246, 0.2); transition: all 0.2s;">Go to Login</a>
                                <a href="{{ route('home') }}" class="btn fw-medium" style="background: #f1f5f9; color: #334155; border: 1px solid #cbd5e1; border-radius: 12px; padding: 14px; text-decoration: none; transition: all 0.2s;">Go to Home</a>
                            </div>
                        </div>
                    `;
                } else {
                    const msg = data.errors ? Object.values(data.errors).flat().join(' ') : (data.message || 'Failed to reset password. Token may be invalid or expired.');
                    if (errAlert) {
                        document.getElementById('reset-error-msg').textContent = msg;
                        errAlert.style.display = 'flex';
                    }
                }
            })
            .catch(err => {
                console.error(err);
                if (errAlert) {
                    document.getElementById('reset-error-msg').textContent = 'An error occurred. Please try again.';
                    errAlert.style.display = 'flex';
                }
            })
            .finally(() => {
                btn.disabled = false;
                btn.innerHTML = originalText;
            });
        });
    });
    </script>

    <style>
        .auth-card-container {
            padding: 10px 5px;
        }
        .auth-header {
            text-align: center;
            margin-bottom: 24px;
        }
        .auth-logo {
            display: inline-flex;
            align-items: center;
            text-decoration: none;
            margin-bottom: 16px;
        }
        .logo-w {
            background: linear-gradient(135deg, #3b82f6 0%, #1e40af 100%);
            color: white;
            width: 42px;
            height: 42px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            font-weight: 800;
            border-radius: 12px;
            margin-right: 12px;
            box-shadow: 0 4px 12px rgba(59, 130, 246, 0.3);
        }
        .logo-text {
            font-size: 26px;
            font-weight: 800;
            color: #1e293b;
            letter-spacing: -0.5px;
        }
        .logo-text span {
            color: #3b82f6;
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
            gap: 16px;
        }
        .form-group {
            display: flex;
            flex-direction: column;
            gap: 6px;
        }
        .form-group label {
            font-size: 13px;
            font-weight: 600;
            color: #475569;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .auth-input {
            background-color: #f8fafc;
            border: 2px solid #e2e8f0;
            border-radius: 12px;
            padding: 12px 16px;
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

        .readonly-input {
            background-color: #f1f5f9;
            color: #64748b;
            cursor: not-allowed;
            border-color: #cbd5e1;
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
            margin-top: 6px;
        }
        .auth-submit-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(59, 130, 246, 0.3);
        }
        .back-nav-btn {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            font-size: 14px;
            font-weight: 600;
            color: #64748b;
            background: #f1f5f9;
            border: 1px solid #e2e8f0;
            padding: 8px 18px;
            border-radius: 20px;
            cursor: pointer;
            transition: all 0.2s;
        }
        .back-nav-btn:hover {
            background: #e2e8f0;
            color: #1e293b;
        }
        .auth-alert {
            display: flex;
            align-items: center;
            padding: 12px 16px;
            border-radius: 12px;
            font-size: 13px;
            font-weight: 600;
        }
        .auth-alert.success {
            background-color: #d1fae5;
            color: #065f46;
            border: 1px solid #a7f3d0;
        }
        .auth-alert.error {
            background-color: #fee2e2;
            color: #991b1b;
            border: 1px solid #fecaca;
        }
    </style>
</x-guest-layout>
