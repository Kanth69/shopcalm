@extends('layouts.customer')

@section('title', 'Explore Our Collection - Shopcalm')

@section('content')
<div class="container my-4">
    <div class="d-flex justify-content-between align-items-center mb-4 pb-3 border-bottom">
        <div class="d-flex align-items-center gap-3">
            <button class="btn btn-dark rounded-pill px-4 d-flex align-items-center shadow-sm" type="button" data-bs-toggle="collapse" data-bs-target="#leftFilterPanel" aria-expanded="false" aria-controls="leftFilterPanel">
                <i class="bi bi-sliders me-2"></i> Filter
            </button>
            <div class="text-muted small d-none d-sm-block">
                Showing <span id="product-count">{{ number_format($products->total()) }}</span> items
            </div>
        </div>
        <select class="form-select rounded-pill border shadow-sm px-4 filter-trigger" name="sort" style="width: auto;">
            <option value="latest" {{ request('sort') == 'latest' ? 'selected' : '' }}>Latest Arrivals</option>
            <option value="price_asc" {{ request('sort') == 'price_asc' ? 'selected' : '' }}>Price: Low to High</option>
            <option value="price_desc" {{ request('sort') == 'price_desc' ? 'selected' : '' }}>Price: High to Low</option>
            <option value="rating_high" {{ request('sort') == 'rating_high' ? 'selected' : '' }}>Highest Rated</option>
        </select>
    </div>

    <div class="row g-4">
        <!-- LEFT SIDEBAR FILTER PANEL (Collapsed by default) -->
        <aside class="col-lg-3 collapse collapse-horizontal" id="leftFilterPanel">
            <div class="card border-0 shadow-sm rounded-4 overflow-hidden sticky-top d-flex flex-column" style="top: 100px; z-index: 10; height: calc(100vh - 180px); margin-bottom: 20px;">
                <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
                    <h6 class="mb-0 fw-bold">Refine Results</h6>
                    <button type="button" class="btn-close btn-sm shadow-none" data-bs-toggle="collapse" data-bs-target="#leftFilterPanel"></button>
                </div>
                @include('customer.components.shop.filters-content', ['categories' => $categories, 'brands' => $brands, 'prefix' => 'desktop'])
            </div>
        </aside>

        <!-- Main Content -->
        <div class="col" id="mainContentArea">

            @if(request()->boolean('on_sale') || request()->filled('offer'))
                <div class="alert border-0 text-white rounded-4 shadow-sm p-3 mb-4 d-flex align-items-center justify-content-between" style="background: linear-gradient(135deg, #6d28d9 0%, #1e1b4b 100%);">
                    <div class="d-flex align-items-center gap-3">
                        <div class="rounded-circle bg-warning bg-opacity-20 p-2.5 d-flex align-items-center justify-content-center">
                            <i class="bi bi-fire text-warning fs-4"></i>
                        </div>
                        <div>
                            <h6 class="mb-0 fw-bold text-white">🔥 Active Mega Sale & Offer Deals</h6>
                            <small class="text-white-50">Showing only products with active sale discounts applied</small>
                        </div>
                    </div>
                    <a href="{{ route('shop') }}" class="btn btn-sm btn-light text-dark fw-bold rounded-pill px-3 py-1.5 shadow-sm">
                        View All Store Products <i class="bi bi-x-lg ms-1"></i>
                    </a>
                </div>
            @endif

            @if(request()->filled('q'))
                <div class="mb-3">
                    <h5 class="fw-bold mb-1">Search results for "<span class="text-primary">{{ request('q') }}</span>"</h5>
                    @if(isset($didYouMean) && $didYouMean)
                        <p class="text-muted small mb-0">Did you mean <a href="{{ route('shop', array_merge(request()->query(), ['q' => $didYouMean])) }}" class="fw-bold text-primary text-decoration-underline">{{ $didYouMean }}</a>?</p>
                    @endif
                </div>
            @endif

            <!-- Active Filter Chips -->
            <div id="active-filter-chips" class="mb-4 d-flex flex-wrap gap-2 align-items-center">
                @include('customer.components.active-filters')
            </div>

            <div id="products-container" class="position-relative min-vh-50">
                <div class="row g-4 justify-content-center" id="product-grid-wrapper">
                    @include('customer.components.shop.product-grid', ['products' => $products])
                </div>

                <div id="pagination-container" class="mt-5 d-flex justify-content-center">
                    {{ $products->links('pagination::bootstrap-5') }}
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Mobile Offcanvas Filters -->
<div class="offcanvas offcanvas-start border-0 shadow" tabindex="-1" id="offcanvasFilters">
    <div class="offcanvas-header border-bottom py-3">
        <h5 class="offcanvas-title fw-bold">Refine Results</h5>
        <button type="button" class="btn-close shadow-none" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body p-0 d-flex flex-column">
        @include('customer.components.shop.filters-content', ['categories' => $categories, 'brands' => $brands, 'prefix' => 'mobile'])
    </div>
</div>
@endsection

@push('scripts')
<script src="{{ asset('js/shop.js?v=1.9') }}" defer></script>
@endpush
