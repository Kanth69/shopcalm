@extends('layouts.customer')

@section('title', $category->name . ' - ' . \App\Models\Setting::get('store_name', 'ShopCalm'))
@section('meta_description', 'Explore top-rated ' . $category->name . ' at ' . \App\Models\Setting::get('store_name', 'ShopCalm') . '. Best prices, authentic brands, and fast doorstep delivery.')

@section('content')
<div class="container my-2 my-md-4 px-2 px-md-3 overflow-hidden">
    
    {{-- Category Top Header (Clean Vibrant Gradient Banner) --}}
    <div class="card border-0 shadow-sm rounded-4 mb-3 mb-md-4 overflow-hidden" 
         style="background: linear-gradient(135deg, #4f46e5 0%, #6366f1 50%, #7c3aed 100%); box-shadow: 0 4px 14px rgba(79, 70, 229, 0.25) !important;">
        <div class="card-body p-2.5 p-sm-3 p-md-4">
            <div class="d-flex align-items-center justify-content-between gap-2 overflow-hidden">
                {{-- Left: Back Arrow + Title + Count --}}
                <div class="d-flex align-items-center min-w-0 flex-grow-1 pe-1">
                    <a href="{{ route('categories.index') }}" class="btn rounded-circle p-0 d-flex align-items-center justify-content-center flex-shrink-0 shadow-xs me-2 me-sm-3" 
                       style="width: 34px; height: 34px; color: #ffffff; background: rgba(255, 255, 255, 0.2); border: 1px solid rgba(255, 255, 255, 0.35); backdrop-filter: blur(6px);" 
                       title="All Categories">
                        <i class="bi bi-arrow-left fs-6"></i>
                    </a>
                    <div class="min-w-0 flex-grow-1 overflow-hidden">
                        <div class="d-flex align-items-center gap-1.5 gap-sm-2 flex-nowrap">
                            <h1 class="h5 fw-bold text-white mb-0 text-truncate" style="font-size: clamp(0.95rem, 3vw, 1.35rem); letter-spacing: -0.01em;">
                                {{ $category->name }}
                            </h1>
                            <span class="badge rounded-pill fw-bold text-nowrap flex-shrink-0" 
                                  style="background: rgba(255, 255, 255, 0.22); color: #ffffff; font-size: 0.68rem; border: 1px solid rgba(255, 255, 255, 0.4); backdrop-filter: blur(4px);">
                                {{ number_format($products->total()) }} {{ Str::plural('Item', $products->total()) }}
                            </span>
                        </div>
                        <p class="small mb-0 d-none d-md-block mt-0.5 text-truncate" style="color: rgba(255, 255, 255, 0.9); font-size: 0.8rem;">
                            Browse verified {{ strtolower($category->name) }} with authentic brand warranty & fast doorstep delivery.
                        </p>
                    </div>
                </div>

                {{-- Right: Quick Action Button (Desktop: Full pill button, Mobile: Compact round icon button to prevent overflow) --}}
                <div class="d-flex align-items-center flex-shrink-0 ms-auto">
                    <a href="{{ route('shop') }}" class="btn btn-sm rounded-pill px-2.5 px-sm-3 py-1.5 fw-bold shadow-xs transition-all hover-elevate d-none d-sm-inline-flex align-items-center gap-1.5 text-decoration-none" 
                       style="font-size: 0.82rem; background: #ffffff; color: #4f46e5; border: none;"
                       title="Browse Shop">
                        <i class="bi bi-shop fs-6"></i> <span>Shop</span>
                    </a>
                    <a href="{{ route('shop') }}" class="btn btn-sm rounded-circle p-0 d-inline-flex d-sm-none align-items-center justify-content-center shadow-xs text-decoration-none flex-shrink-0" 
                       style="width: 32px; height: 32px; background: #ffffff; color: #4f46e5; border: none;"
                       title="Browse Shop">
                        <i class="bi bi-shop fs-6"></i>
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
                <p class="text-muted small mb-3">We are currently restocking products in {{ $category->name }}. Please check back shortly!</p>
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
