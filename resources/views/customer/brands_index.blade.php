@extends('layouts.customer')

@section('title', 'All Brands - Shopcalm')

@section('content')
<div class="container my-3 my-md-4 px-3 px-md-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold mb-1" style="font-size: clamp(1.2rem, 2.5vw, 1.6rem);"><i class="bi bi-award-fill text-primary me-2"></i>Top Brands</h2>
            <p class="text-muted small mb-0">Shop by your favorite brands</p>
        </div>
        <a href="{{ route('shop') }}" class="btn btn-outline-primary rounded-pill px-3.5 py-1.5 fw-semibold btn-sm">Browse Shop</a>
    </div>

    <div class="row g-3 g-md-4">
        @forelse($brands as $brand)
        <div class="col-6 col-md-4 col-lg-3">
            <a href="{{ route('brand.products', $brand->slug) }}" class="text-decoration-none text-dark">
                <div class="card h-100 border-0 shadow-sm rounded-4 overflow-hidden text-center p-3 p-md-4 brand-card transition-all" style="border: 1.5px solid #e2e8f0 !important;">
                    @if($brand->logo)
                        <div class="mb-2.5 d-flex align-items-center justify-content-center" style="height: 52px;">
                            <img src="{{ asset('storage/' . $brand->logo) }}" alt="{{ $brand->name }}" class="img-fluid" style="max-height: 44px; max-width: 100%; object-fit: contain;">
                        </div>
                    @else
                        <div class="bg-primary-subtle text-primary rounded-circle d-inline-flex align-items-center justify-content-center mb-2.5 mx-auto" style="width: 52px; height: 52px;">
                            <i class="bi bi-patch-check-fill fs-4"></i>
                        </div>
                    @endif
                    <h6 class="fw-bold mb-1 text-truncate" style="font-size: 0.92rem;">{{ $brand->name }}</h6>
                    <span class="badge bg-light text-secondary border rounded-pill px-2.5 py-0.5" style="font-size: 0.72rem;">{{ $brand->products_count }} Products</span>
                </div>
            </a>
        </div>
        @empty
        <div class="col-12 text-center py-5">
            <i class="bi bi-patch-exclamation fs-1 text-muted"></i>
            <h4 class="mt-3">No Brands Found</h4>
        </div>
        @endforelse
    </div>
</div>

<style>
.brand-card { transition: all 0.25s ease-in-out; }
.brand-card:hover { transform: translateY(-5px); box-shadow: 0 10px 25px rgba(0,0,0,0.08) !important; }
</style>
@endsection
