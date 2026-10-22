@extends('layouts.customer')

@section('title', $collection->name . ' - Shopcalm')

@section('content')
<div class="container my-3 my-md-4 px-3 px-md-4">
    <div class="row">
        <!-- Main Content -->
        <div class="col-12">

            <!-- Collection Header -->
            <div class="card border-0 shadow-sm rounded-4 mb-4 overflow-hidden position-relative" style="background: linear-gradient(135deg, #1e3a8a 0%, #3b82f6 100%);">
                @if($collection->image)
                    <img src="{{ asset('storage/' . $collection->image) }}" class="position-absolute w-100 h-100 object-fit-cover opacity-25" style="mix-blend-mode: overlay;">
                @endif
                <div class="card-body p-3.5 p-md-5 position-relative z-1 text-white text-center">
                    <h1 class="fw-bolder mb-2 text-white" style="font-size: clamp(1.3rem, 3.5vw, 2.3rem); letter-spacing: -0.02em;">{{ $collection->name }}</h1>
                    @if($collection->description)
                        <p class="mb-0 opacity-75 mx-auto small" style="max-width: 600px; font-size: 0.88rem;">{{ $collection->description }}</p>
                    @endif
                </div>
            </div>

            <div class="d-flex justify-content-between align-items-center mb-3 mb-md-4 flex-wrap gap-2">
                <p class="text-muted mb-0 small fw-medium">{{ number_format($products->total()) }} items in this collection</p>
                <a href="{{ route('home') }}" class="btn btn-sm btn-light rounded-pill px-3 py-1.5 shadow-xs border text-secondary" style="font-size: 0.8rem;">
                    <i class="bi bi-arrow-left me-1"></i> Back to Home
                </a>
            </div>

            <!-- Product Grid (2 per row on mobile, 4 on desktop) -->
            @include('components.skeleton-loader', ['count' => 8, 'type' => 'card'])
            <div class="row row-cols-2 row-cols-md-3 row-cols-lg-4 g-2 g-md-3 content-loaded d-none">
                @forelse($products as $product)
                    <div class="col d-flex">
                        @include('customer.components.product-card', ['product' => $product])
                    </div>
                @empty
                    <div class="col-12 text-center py-5 bg-white rounded-4 border p-4">
                        <i class="bi bi-basket display-3 text-muted opacity-25 mb-3"></i>
                        <h4 class="fw-bold text-dark">No Products Found</h4>
                        <p class="text-muted small">There are currently no active products in this collection.</p>
                        <a href="{{ route('shop') }}" class="btn btn-primary rounded-pill px-4 py-2 btn-sm fw-semibold mt-2">Explore the Shop</a>
                    </div>
                @endforelse
            </div>

            @if($products->hasPages())
                <div class="mt-5 d-flex justify-content-center content-loaded d-none">
                    {{ $products->links('pagination::bootstrap-5') }}
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
