@extends('errors.layout')

@section('title', '404 - Page Not Found')

@section('content')
<div class="error-card">
    <div class="error-code-badge bg-primary bg-opacity-10 text-primary mx-auto">
        404
    </div>
    
    <h1 class="h3 fw-bold mb-2">We couldn't find this page</h1>
    <p class="text-muted mb-4" style="font-size: 0.95rem; line-height: 1.6;">
        The product, offer, or link you're looking for might have been moved, sold out, or is temporarily unavailable.
    </p>

    <!-- Main Call to Actions -->
    <div class="d-flex flex-column flex-sm-row justify-content-center gap-2 mb-4">
        <a href="{{ route('shop') }}" class="btn-primary-custom">
            <i class="bi bi-grid-fill"></i>
            <span>Browse Products</span>
        </a>
        <a href="{{ url('/') }}" class="btn-outline-custom">
            <i class="bi bi-house-door"></i>
            <span>Back to Home</span>
        </a>
    </div>

    <!-- Quick Category Shortcuts -->
    <div class="border-top pt-3 mt-2">
        <span class="text-uppercase text-muted fw-bold d-block mb-2" style="font-size: 0.72rem; letter-spacing: 0.05em;">Popular Sections</span>
        <div class="d-flex flex-wrap justify-content-center gap-1.5">
            <a href="{{ route('shop') }}" class="badge rounded-pill bg-light text-dark border text-decoration-none px-3 py-2 fw-medium">All Products</a>
            <a href="{{ url('/offers') }}" class="badge rounded-pill bg-light text-dark border text-decoration-none px-3 py-2 fw-medium">Hot Deals</a>
            <a href="{{ route('contact') }}" class="badge rounded-pill bg-light text-dark border text-decoration-none px-3 py-2 fw-medium">Help Center</a>
            <a href="{{ route('account.orders.index') }}" class="badge rounded-pill bg-light text-dark border text-decoration-none px-3 py-2 fw-medium">Track Order</a>
        </div>
    </div>
</div>
@endsection
