@extends('layouts.customer')

@section('title', $category->name . ' - Shopcalm')

@section('content')
<div class="container my-4">
    <div class="row">
        <!-- Main Content -->
        <div class="col-12">
            <div class="card border-0 shadow-sm rounded-4 mb-4 overflow-hidden">
                <div class="card-body p-4">
                    <div class="row align-items-center">
                        <div class="col-md-6">
                            <h4 class="fw-bold mb-1">{{ $category->name }}</h4>
                            <p class="text-muted mb-0">{{ number_format($products->total()) }} products available</p>
                        </div>
                        <div class="col-md-6 mt-3 mt-md-0 d-flex justify-content-md-end gap-2">
                            <a href="{{ route('shop') }}" class="btn btn-light rounded-pill px-4 border shadow-sm d-flex align-items-center">
                                <i class="bi bi-arrow-left me-2"></i> Back to Shop
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            @include('components.skeleton-loader', ['count' => 8, 'type' => 'card'])
            <div class="row content-loaded d-none g-4">
                @forelse($products as $product)
                    <div class="col-lg-3 col-md-4 col-sm-6">
                        @include('customer.components.product-card', ['product' => $product])
                    </div>
                @empty
                    <div class="col-12 text-center py-5">
                        <i class="bi bi-tags display-1 text-muted opacity-25"></i>
                        <h3 class="mt-4 fw-bold">No Products Found</h3>
                        <p class="text-muted">We are currently restocking this category. Please check back later.</p>
                        <a href="{{ route('shop') }}" class="btn btn-primary rounded-pill px-5 mt-3">Explore All Products</a>
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
