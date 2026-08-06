<div class="auth-header text-center">
    <a href="{{ route('home') }}" class="auth-logo d-inline-flex justify-content-center mb-3 text-decoration-none">
        <x-logo height="42" />
    </a>
    <h2 class="auth-title">Forgot Password?</h2>
    <p class="auth-subtitle">No worries! Enter your registered email and click Send Reset Link.</p>
</div>

<!-- Prominent Status Alert Banner Above Form -->
<div id="forgot-alert" class="auth-alert" style="display: none;">
    <i id="forgot-alert-icon" class="bi bi-check-circle-fill me-2"></i>
    <span id="forgot-alert-msg"></span>
</div>

<form id="form-forgot-password" method="POST" action="{{ route('password.email') }}" class="auth-form">
    @csrf
    <div class="form-group">
        <label for="email_for_forgot">Email Address <span class="text-danger">*</span></label>
        <input id="email_for_forgot" class="auth-input @error('email') is-invalid @enderror" type="email" name="email" required placeholder="name@example.com">
        @error('email')
            <span class="text-danger small mt-1 d-block"><i class="bi bi-exclamation-triangle-fill me-1"></i>{{ $message }}</span>
        @enderror
    </div>

    <button type="submit" id="forgot-submit-btn" class="auth-submit-btn">
        <span>Send Reset Link</span>
        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
        </svg>
    </button>

    <div class="text-center mt-3">
        <button type="button" id="back-to-login" class="back-nav-btn mx-auto">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
            <span>Back to Login</span>
        </button>
    </div>
</form>
