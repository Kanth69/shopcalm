<div class="text-center">
    <a href="{{ route('home') }}" class="auth-logo d-inline-flex align-items-center justify-content-center mb-3 text-decoration-none">
        <x-logo height="40" />
    </a>
    <div class="mb-3">
        <i class="bi bi-check-circle-fill text-success" style="font-size: 3.5rem;"></i>
    </div>
    <h2 id="success-title" class="auth-title">Success</h2>
    <p id="success-message" class="auth-subtitle">Your action was successful.</p>
    <div class="mt-4 d-flex gap-2 justify-content-center">
        <a href="{{ route('login') }}" class="btn btn-primary px-4 rounded-3 fw-semibold">Go to Login</a>
        <a href="{{ route('home') }}" class="btn btn-outline-secondary px-4 rounded-3 fw-semibold">Go to Home</a>
    </div>
</div>
