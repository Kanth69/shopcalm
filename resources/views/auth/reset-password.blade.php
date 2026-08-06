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
                <input id="password" class="auth-input" type="password" name="password" required placeholder="Minimum 6 characters" autofocus minlength="6">
            </div>

            <!-- Confirm Password -->
            <div class="form-group">
                <label for="password_confirmation">Confirm New Password <span class="text-danger">*</span></label>
                <input id="password_confirmation" class="auth-input" type="password" name="password_confirmation" required placeholder="Re-enter new password">
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
                    // Password changed successfully -> stay on page & show success banner
                    if (succAlert) {
                        document.getElementById('reset-success-msg').textContent = data.message || 'Your password has been changed successfully! Please go back and login.';
                        succAlert.style.display = 'flex';
                    }
                    form.reset();
                    // Keep email preserved
                    document.getElementById('email').value = "{{ $request->email }}";
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
