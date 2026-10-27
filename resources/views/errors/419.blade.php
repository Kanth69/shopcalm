@extends('errors.layout')

@section('title', '419 - Session Expired')

@section('content')
<div class="error-card">
    <div class="error-code-badge bg-info bg-opacity-10 text-info mx-auto">
        <i class="bi bi-shield-lock"></i>
    </div>
    
    <h1 class="h3 fw-bold mb-2">Session Expired</h1>
    <p class="text-muted mb-4" style="font-size: 0.95rem; line-height: 1.6;">
        To protect your checkout security, this page timed out due to a period of inactivity. Please refresh to continue safely.
    </p>

    <!-- Main Call to Actions -->
    <div class="d-flex flex-column flex-sm-row justify-content-center gap-2 mb-3">
        <button type="button" onclick="window.location.reload();" class="btn-primary-custom" style="cursor: pointer;">
            <i class="bi bi-arrow-clockwise"></i>
            <span>Refresh &amp; Continue</span>
        </button>
        <a href="{{ url('/') }}" class="btn-outline-custom">
            <i class="bi bi-house-door"></i>
            <span>Return to Home</span>
        </a>
    </div>
</div>
@endsection
