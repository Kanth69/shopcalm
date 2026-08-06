@extends('layouts.customer')

@section('title', $offer->name . ' - Shopcalm')

@section('content')
<div class="container my-4">
    <div class="row">
        <!-- Main Content -->
        <div class="col-12">

            <!-- Offer Header -->
            <div class="card border-0 shadow-sm rounded-4 mb-4 overflow-hidden position-relative" style="background: linear-gradient(135deg, #1e3a8a 0%, #3b82f6 100%);">
                @if($offer->banner_image)
                    <img src="{{ asset('storage/' . $offer->banner_image) }}" class="position-absolute w-100 h-100 object-fit-cover opacity-25" style="mix-blend-mode: overlay;">
                @endif
                <div class="card-body p-5 position-relative z-1 text-white text-center">
                    <h1 class="display-4 fw-bolder mb-2 text-white">{{ $offer->name }}</h1>
                    @if($offer->description)
                        <p class="lead mb-0 opacity-75 mx-auto" style="max-width: 600px;">{{ $offer->description }}</p>
                    @endif
                </div>
            </div>

            <div class="d-flex justify-content-between align-items-center mb-4">
                <p class="text-muted mb-0 fw-medium">{{ number_format($products->total()) }} items matching this offer</p>
                <a href="{{ route('home') }}" class="btn btn-light rounded-pill px-4 shadow-sm border">
                    <i class="bi bi-arrow-left me-2"></i> Back to Home
                </a>
            </div>

            <!-- Product Grid -->
            @include('components.skeleton-loader', ['count' => 8, 'type' => 'card'])
            <div class="row content-loaded d-none g-4">
                @forelse($products as $product)
                    <div class="col-lg-3 col-md-4 col-sm-6">
                        @include('customer.components.product-card', ['product' => $product])
                    </div>
                @empty
                    <div class="col-12 text-center py-5 bg-white rounded-4 shadow-sm">
                        <i class="bi bi-basket display-1 text-muted opacity-25 mb-3"></i>
                        <h3 class="fw-bold">No Products Found</h3>
                        <p class="text-muted">There are currently no active products matching this offer.</p>
                        <a href="{{ route('shop') }}" class="btn btn-primary rounded-pill px-5 mt-2">Explore the Shop</a>
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
