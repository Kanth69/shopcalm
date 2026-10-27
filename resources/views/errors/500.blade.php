@extends('errors.layout')

@section('title', '500 - Server Error')

@section('content')
<div class="error-card">
    <div class="error-code-badge bg-danger bg-opacity-10 text-danger mx-auto">
        <i class="bi bi-exclamation-triangle"></i>
    </div>
    
    <h1 class="h3 fw-bold mb-2">Temporary Server Hiccup</h1>
    <p class="text-muted mb-4" style="font-size: 0.95rem; line-height: 1.6;">
        Our servers experienced a brief technical glitch. Don't worry — your shopping cart, orders, and payment records are completely safe.
    </p>

    <!-- Main Call to Actions -->
    <div class="d-flex flex-column flex-sm-row justify-content-center gap-2 mb-4">
        <button type="button" onclick="window.location.reload();" class="btn-primary-custom" style="cursor: pointer;">
            <i class="bi bi-arrow-clockwise"></i>
            <span>Refresh Page</span>
        </button>
        <a href="{{ url('/') }}" class="btn-outline-custom">
            <i class="bi bi-house-door"></i>
            <span>Return to Home</span>
        </a>
    </div>

    <!-- Reassurance / Support Note -->
    <div class="border-top pt-3 mt-2 text-muted" style="font-size: 0.82rem;">
        <i class="bi bi-shield-check text-success me-1"></i>
        If an online payment was just debited from your bank, our automated webhook will auto-credit or fulfill your order within minutes.
    </div>
</div>
@endsection
