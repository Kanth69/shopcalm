<x-guest-layout>
    <x-slot name="title">Forgot Password</x-slot>

    <div class="auth-card-container">
        <div class="auth-header">
            <a href="{{ route('home') }}" class="auth-logo">
                <span class="logo-w">W</span>
                <div class="logo-text">Shopcalm<span>.in</span></div>
            </a>
            <h2 class="auth-title">Forgot Password</h2>
            <p class="auth-subtitle">Enter your email address and we'll send you a link to reset your password.</p>
        </div>

        <!-- Red Error Banner Alert -->
        <div id="forgot-error-alert" class="auth-alert error mb-3" style="display: none;">
            <i class="bi bi-exclamation-triangle-fill me-2 fs-5"></i>
            <span id="forgot-error-msg"></span>
        </div>

        <div id="forgot-form-container">
            <form id="forgot-password-form" method="POST" action="{{ route('password.email') }}" class="auth-form">
                @csrf

                <!-- Email Address -->
                <div class="form-group">
                    <label for="email">Email Address <span class="text-danger">*</span></label>
                    <input id="email" class="auth-input" type="email" name="email" value="{{ old('email') }}" required autofocus placeholder="name@example.com">
                </div>

                <button type="submit" id="btn-submit-forgot" class="auth-submit-btn">
                    <span>Send Reset Link</span>
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                    </svg>
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
        const form = document.getElementById('forgot-password-form');
        if (!form) return;

        form.addEventListener('submit', function(e) {
            e.preventDefault();

            const btn = document.getElementById('btn-submit-forgot');
            const originalText = btn.innerHTML;
            btn.disabled = true;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Sending...';

            const errAlert = document.getElementById('forgot-error-alert');
            if (errAlert) errAlert.style.display = 'none';

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
                    // Success -> Hide form and show tick mark success UI
                    const container = document.getElementById('forgot-form-container');
                    container.innerHTML = `
                        <div class="text-center py-4">
                            <div class="mb-4">
                                <svg xmlns="http://www.w3.org/2000/svg" width="64" height="64" fill="#10b981" viewBox="0 0 16 16" class="mx-auto">
                                  <path d="M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0zm-3.97-3.03a.75.75 0 0 0-1.08.022L7.477 9.417 5.384 7.323a.75.75 0 0 0-1.06 1.06L6.97 11.03a.75.75 0 0 0 1.079-.02l3.992-4.99a.75.75 0 0 0-.01-1.05z"/>
                                </svg>
                            </div>
                            <h3 class="fw-bold mb-3">Link Sent Successfully!</h3>
                            <p class="text-muted mb-4">${data.status || 'If an account exists with this email, a password reset link has been sent.'}</p>
                        </div>
                    `;
                } else {
                    const msg = data.errors ? Object.values(data.errors).flat().join(' ') : (data.message || 'Something went wrong.');
                    if (errAlert) {
                        document.getElementById('forgot-error-msg').textContent = msg;
                        errAlert.style.display = 'flex';
                    }
                    btn.disabled = false;
                    btn.innerHTML = originalText;
                }
            })
            .catch(err => {
                console.error(err);
                if (errAlert) {
                    document.getElementById('forgot-error-msg').textContent = 'An unexpected error occurred. Please try again.';
                    errAlert.style.display = 'flex';
                }
                btn.disabled = false;
                btn.innerHTML = originalText;
            });
        });
    });
    </script>
</x-guest-layout>
