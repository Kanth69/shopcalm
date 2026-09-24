@extends('layouts.customer')

@section('title', $brand->name . ' - ' . \App\Models\Setting::get('store_name', 'ShopCalm'))
@section('meta_description', 'Explore genuine ' . $brand->name . ' products at ' . \App\Models\Setting::get('store_name', 'ShopCalm') . '. Official brand warranty, best prices, and quick delivery.')

@section('content')
<div class="container my-2 my-md-4 px-2 px-md-3">
    
    {{-- 1. Breadcrumbs --}}
    <nav aria-label="breadcrumb" class="mb-2 mb-md-3">
        <ol class="breadcrumb small mb-0" style="font-size: 0.82rem;">
            <li class="breadcrumb-item"><a href="{{ route('home') }}" class="text-decoration-none text-muted">Home</a></li>
            <li class="breadcrumb-item"><a href="{{ route('brands.index') }}" class="text-decoration-none text-muted">Brands</a></li>
            <li class="breadcrumb-item active text-dark fw-semibold" aria-current="page">{{ $brand->name }}</li>
        </ol>
    </nav>

    {{-- 2. Brand Top Header (Clean Vibrant Gradient Banner) --}}
    <div class="card border-0 shadow-sm rounded-4 mb-4 mb-md-5 overflow-hidden" 
         style="background: linear-gradient(135deg, #4f46e5 0%, #6366f1 50%, #7c3aed 100%); box-shadow: 0 4px 14px rgba(79, 70, 229, 0.25) !important;">
        <div class="card-body p-3 p-md-3.5">
            <div class="d-flex align-items-center justify-content-between gap-2">
                {{-- Left: Back Arrow + Logo/Icon + Title + Count --}}
                <div class="d-flex align-items-center min-w-0">
                    <a href="{{ route('brands.index') }}" class="btn rounded-circle p-0 d-flex align-items-center justify-content-center flex-shrink-0 shadow-xs" 
                       style="width: 38px; height: 38px; color: #ffffff; background: rgba(255, 255, 255, 0.2); border: 1px solid rgba(255, 255, 255, 0.35); backdrop-filter: blur(6px); margin-right: 14px !important;" 
                       title="All Brands">
                        <i class="bi bi-arrow-left fs-6"></i>
                    </a>
                    @if($brand->logo)
                        <img src="{{ asset('storage/' . $brand->logo) }}" alt="{{ $brand->name }}" 
                             class="rounded-3 shadow-xs border p-1 bg-white flex-shrink-0 me-2" style="width: 38px; height: 38px; object-fit: contain;">
                    @endif
                    <div class="min-w-0">
                        <div class="d-flex align-items-center gap-2 flex-wrap">
                            <h1 class="h5 fw-bold text-white mb-0 text-truncate" style="font-size: clamp(1.1rem, 2.3vw, 1.4rem); letter-spacing: -0.01em;">
                                {{ $brand->name }}
                            </h1>
                            <span class="badge rounded-pill fw-bold" 
                                  style="background: rgba(255, 255, 255, 0.22); color: #ffffff; font-size: 0.72rem; border: 1px solid rgba(255, 255, 255, 0.4); backdrop-filter: blur(4px);">
                                {{ number_format($products->total()) }} {{ Str::plural('Item', $products->total()) }}
                            </span>
                        </div>
                        <p class="small mb-0 d-none d-md-block mt-0.5" style="color: rgba(255, 255, 255, 0.9); font-size: 0.8rem;">
                            100% Genuine {{ $brand->name }} catalog with verified brand warranty.
                        </p>
                    </div>
                </div>

                {{-- Right: Quick Action Button --}}
                <div class="d-flex align-items-center flex-shrink-0">
                    <a href="{{ route('shop') }}" class="btn btn-sm rounded-pill px-3 py-1.5 fw-bold shadow-xs transition-all hover-elevate" 
                       style="font-size: 0.82rem; background: #ffffff; color: #4f46e5; border: none;">
                        <i class="bi bi-shop me-1"></i> <span>Shop</span>
                    </a>
                </div>
            </div>
        </div>
    </div>

    {{-- 3. Skeleton Loading State --}}
    @include('components.skeleton-loader', ['count' => 8, 'type' => 'card'])

    {{-- 4. 2-Column Mobile & 4-Column Desktop Product Grid --}}
    <div class="row row-cols-2 row-cols-md-3 row-cols-lg-4 g-2 g-md-3 content-loaded d-none">
        @forelse($products as $product)
            <div class="col d-flex">
                @include('customer.components.product-card', ['product' => $product])
            </div>
        @empty
            <div class="col-12 text-center py-5 bg-white rounded-4 border p-4">
                <i class="bi bi-box-seam display-3 text-muted opacity-25"></i>
                <h4 class="mt-3 fw-bold text-dark">No Products Found</h4>
                <p class="text-muted small mb-3">We are currently restocking {{ $brand->name }} products. Please check back shortly!</p>
                <a href="{{ route('shop') }}" class="btn btn-primary rounded-pill px-4 fw-semibold btn-sm">
                    <i class="bi bi-arrow-left me-1"></i> Explore All Products
                </a>
            </div>
        @endforelse
    </div>

    {{-- 5. Pagination --}}
    @if($products->hasPages())
        <div class="mt-4 mt-md-5 d-flex justify-content-center content-loaded d-none">
            {{ $products->links('pagination::bootstrap-5') }}
        </div>
    @endif

</div>
@endsection
