@extends('errors.layout')

@section('title', '403 - Access Restricted')

@section('content')
<div class="error-card">
    <div class="error-code-badge bg-secondary bg-opacity-10 text-secondary mx-auto">
        <i class="bi bi-lock-fill"></i>
    </div>
    
    <h1 class="h3 fw-bold mb-2">Access Restricted</h1>
    <p class="text-muted mb-4" style="font-size: 0.95rem; line-height: 1.6;">
        You don't have permission to access this page. If you believe this is an error, please log in with the correct account or return to the store.
    </p>

    <!-- Main Call to Actions -->
    <div class="d-flex flex-column flex-sm-row justify-content-center gap-2 mb-3">
        <a href="{{ url('/') }}" class="btn-primary-custom">
            <i class="bi bi-house-door"></i>
            <span>Return to Store</span>
        </a>
        <a href="{{ route('login') }}" class="btn-outline-custom">
            <i class="bi bi-box-arrow-in-right"></i>
            <span>Log In</span>
        </a>
    </div>
</div>
@endsection
