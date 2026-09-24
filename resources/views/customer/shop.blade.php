@extends('layouts.customer')

@section('title', 'Explore Our Collection - ' . \App\Models\Setting::get('store_name', 'ShopCalm'))

@section('content')
<div class="container my-2 my-md-4 px-2 px-md-4">

    {{-- 1. Shop Page Header Hero --}}
    <div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-3 mb-md-4 position-relative" 
         style="background: linear-gradient(135deg, #0f172a 0%, #1e293b 60%, #312e81 100%); color: #ffffff;">
        <div class="position-absolute w-100 h-100"
             style="background-image: radial-gradient(circle, rgba(255,255,255,0.05) 1px, transparent 1px); background-size: 24px 24px; top: 0; left: 0; pointer-events: none;"></div>
        
        <div class="card-body p-3 p-md-4.5 position-relative">
            <div class="d-flex align-items-center justify-content-between flex-wrap gap-2.5 gap-md-3">
                <div class="d-flex align-items-center gap-2.5 gap-md-3">
                    <div class="rounded-3 d-flex align-items-center justify-content-center flex-shrink-0" 
                         style="width: 46px; height: 46px; background: rgba(99, 102, 241, 0.25); border: 2px solid rgba(99,102,241,0.5); color: #ffffff; font-size: 1.35rem;">
                        <i class="bi bi-grid-3x3-gap-fill"></i>
                    </div>
                    <div>
                        <div class="d-flex align-items-center gap-2 flex-wrap mb-0.5">
                            <h4 class="fw-bolder mb-0 text-white" style="letter-spacing: -0.02em; font-size: clamp(1.05rem, 2.5vw, 1.45rem);">Explore Our Catalog</h4>
                            <span class="badge rounded-pill px-2.5 py-0.5 fw-bold d-none d-sm-inline-block" style="background: rgba(99,102,241,0.3); color: #c7d2fe; border: 1px solid rgba(199,210,254,0.3); font-size: 0.7rem;">
                                Full Store Collection
                            </span>
                        </div>
                        <p class="text-white-50 small mb-0 d-flex align-items-center gap-1.5 gap-md-2 flex-wrap" style="font-size: 0.78rem;">
                            <span><i class="bi bi-box-seam me-1 text-info opacity-75"></i>{{ number_format($products->total()) }} Products</span>
                            <span class="d-none d-sm-inline">•</span>
                            <span class="d-none d-sm-inline"><i class="bi bi-truck me-1 text-success opacity-75"></i>Free Shipping ₹499+</span>
                            <span class="d-none d-md-inline">•</span>
                            <span class="d-none d-md-inline"><i class="bi bi-shield-check me-1 text-warning opacity-75"></i>Authentic Quality</span>
                        </p>
                    </div>
                </div>

                <div class="d-flex align-items-center gap-2 ms-auto ms-sm-0">
                    <a href="{{ route('home') }}" class="btn btn-outline-light rounded-pill px-3 py-1.5 fw-semibold btn-sm" style="font-size: 0.78rem; border-color: rgba(255,255,255,0.25);">
                        <i class="bi bi-house me-1"></i> Home
                    </a>
                    @if(request()->boolean('on_sale') || request()->filled('offer') || request()->filled('category') || request()->filled('brand') || request()->filled('min_price') || request()->filled('max_price'))
                        <a href="{{ route('shop') }}" class="btn btn-primary rounded-pill px-3 py-1.5 fw-semibold btn-sm shadow-sm" style="background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%); border: none; font-size: 0.78rem;">
                            <i class="bi bi-arrow-counterclockwise me-1"></i> Reset
                        </a>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- 2. Controls Bar (Filter Offcanvas Drawer Trigger & Sort) --}}
    <div class="card border-0 shadow-sm rounded-4 mb-3 mb-md-4 p-2.5 p-md-3" style="background: #ffffff; border: 1px solid #e2e8f0 !important;">
        <div class="row g-2 align-items-center">
            
            {{-- Left: Filter Drawer Trigger Button --}}
            <div class="col-6 col-sm-auto">
                <button class="btn btn-dark rounded-pill w-100 px-3 py-2 d-flex align-items-center justify-content-center gap-1.5 shadow-sm fw-bold hover-scale text-nowrap" 
                        type="button" data-bs-toggle="offcanvas" data-bs-target="#offcanvasFilters" aria-controls="offcanvasFilters" 
                        style="background: #0f172a; border-color: #0f172a; font-size: 0.82rem; letter-spacing: -0.01em;">
                    <i class="bi bi-sliders2"></i> Filter
                    @php
                        $filterCount = count((array)request('category', [])) + count((array)request('brand', [])) + (request('min_price') || request('max_price') ? 1 : 0) + (request('only_discounted') ? 1 : 0) + (request('featured') ? 1 : 0);
                    @endphp
                    <span id="filter-count-badge" class="badge rounded-pill bg-primary ms-0.5 px-1.5 py-0.5" style="font-size: 0.68rem; display: {{ $filterCount > 0 ? 'inline-block' : 'none' }};">{{ $filterCount }}</span>
                </button>
            </div>

            {{-- Middle: Count pill on desktop/tablet --}}
            <div class="col-auto d-none d-sm-block me-auto">
                <span class="badge rounded-pill bg-light text-secondary border px-2.5 py-1.5 fw-semibold" style="font-size: 0.78rem;">
                    <strong class="text-dark" id="product-count">{{ number_format($products->total()) }}</strong> items found
                </span>
            </div>
            
            {{-- Right: Sort Dropdown (Right-Anchored, 100% Screen Contained) --}}
            <div class="col-6 col-sm-auto ms-sm-auto position-relative">
                <div class="d-flex align-items-center gap-1.5">
                    <label class="text-muted small fw-semibold d-none d-md-inline text-nowrap" style="font-size: 0.82rem;">Sort By:</label>
                    <div class="dropdown w-100 position-relative">
                        <button class="btn btn-outline-light text-dark bg-white border rounded-pill shadow-xs py-2 px-2.5 px-sm-3 fw-medium w-100 d-flex align-items-center justify-content-between text-nowrap" 
                                type="button" id="sortDropdownMenuBtn" data-bs-toggle="dropdown" data-bs-display="static" aria-expanded="false" 
                                style="font-size: 0.8rem; border-color: #cbd5e1 !important;">
                            <span class="text-truncate" id="currentSortText">
                                @switch(request('sort'))
                                    @case('price_asc') 💰 Low to High @break
                                    @case('price_desc') 💎 High to Low @break
                                    @case('rating_high') ⭐ Top Rated @break
                                    @default ✨ Latest
                                @endswitch
                            </span>
                            <i class="bi bi-chevron-down ms-1.5 text-muted small"></i>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end shadow-lg rounded-4 border-0 p-2 mt-1" aria-labelledby="sortDropdownMenuBtn" style="min-width: 210px; max-width: 90vw; z-index: 1055; right: 0 !important; left: auto !important;">
                            <li>
                                <button type="button" class="dropdown-item rounded-3 py-2 px-3 fw-medium sort-dropdown-item d-flex align-items-center justify-content-between {{ request('sort', 'latest') == 'latest' ? 'active bg-primary text-white' : 'text-dark' }}" data-value="latest">
                                    <span>✨ Latest Arrivals</span>
                                    @if(request('sort', 'latest') == 'latest') <i class="bi bi-check-lg"></i> @endif
                                </button>
                            </li>
                            <li>
                                <button type="button" class="dropdown-item rounded-3 py-2 px-3 fw-medium sort-dropdown-item d-flex align-items-center justify-content-between {{ request('sort') == 'price_asc' ? 'active bg-primary text-white' : 'text-dark' }}" data-value="price_asc">
                                    <span>💰 Price: Low to High</span>
                                    @if(request('sort') == 'price_asc') <i class="bi bi-check-lg"></i> @endif
                                </button>
                            </li>
                            <li>
                                <button type="button" class="dropdown-item rounded-3 py-2 px-3 fw-medium sort-dropdown-item d-flex align-items-center justify-content-between {{ request('sort') == 'price_desc' ? 'active bg-primary text-white' : 'text-dark' }}" data-value="price_desc">
                                    <span>💎 Price: High to Low</span>
                                    @if(request('sort') == 'price_desc') <i class="bi bi-check-lg"></i> @endif
                                </button>
                            </li>
                            <li>
                                <button type="button" class="dropdown-item rounded-3 py-2 px-3 fw-medium sort-dropdown-item d-flex align-items-center justify-content-between {{ request('sort') == 'rating_high' ? 'active bg-primary text-white' : 'text-dark' }}" data-value="rating_high">
                                    <span>⭐ Highest Rated</span>
                                    @if(request('sort') == 'rating_high') <i class="bi bi-check-lg"></i> @endif
                                </button>
                            </li>
                        </ul>
                        {{-- Hidden select kept for AJAX event binding --}}
                        <select class="filter-trigger d-none" name="sort" id="shopSortSelect">
                            <option value="latest" {{ request('sort') == 'latest' ? 'selected' : '' }}>latest</option>
                            <option value="price_asc" {{ request('sort') == 'price_asc' ? 'selected' : '' }}>price_asc</option>
                            <option value="price_desc" {{ request('sort') == 'price_desc' ? 'selected' : '' }}>price_desc</option>
                            <option value="rating_high" {{ request('sort') == 'rating_high' ? 'selected' : '' }}>rating_high</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- 3. Sale / Search Notice --}}
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
        <div class="card border-0 shadow-sm rounded-4 p-3.5 p-md-4 mb-4" 
             style="background: linear-gradient(135deg, #ffffff 0%, #f8fafc 100%); border: 1px solid #e2e8f0 !important;">
            <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
                <div class="d-flex align-items-center" style="gap: 1rem !important;">
                    <div class="rounded-3 d-flex align-items-center justify-content-center flex-shrink-0" 
                         style="width: 44px; height: 44px; background: #ede9fe; color: #6366f1; font-size: 1.25rem;">
                        <i class="bi bi-search"></i>
                    </div>
                    <div>
                        <div class="d-flex align-items-center gap-2 flex-wrap mb-1">
                            <span class="text-muted small fw-semibold text-uppercase" style="font-size: 0.74rem; letter-spacing: 0.05em;">Search Query</span>
                            <span class="badge rounded-pill px-3 py-1 fw-bold fs-6" style="background: rgba(99, 102, 241, 0.12); color: #6366f1; border: 1px solid rgba(99, 102, 241, 0.25);">
                                "{{ request('q') }}"
                            </span>
                        </div>
                        @if(isset($didYouMean) && $didYouMean)
                            <div class="small text-muted" style="font-size: 0.82rem;">
                                Did you mean: <a href="{{ route('shop', array_merge(request()->query(), ['q' => $didYouMean])) }}" class="fw-bold text-primary text-decoration-underline">{{ $didYouMean }}</a>?
                            </div>
                        @else
                            <div class="text-muted small" style="font-size: 0.8rem;">
                                Showing top matching products for your search
                            </div>
                        @endif
                    </div>
                </div>

                <div>
                    @php
                        $queryWithoutSearch = request()->except(['q', 'page', 'suggestion']);
                    @endphp
                    <a href="{{ route('shop', $queryWithoutSearch) }}" class="btn btn-outline-secondary rounded-pill px-3.5 py-1.5 fw-semibold btn-sm d-inline-flex align-items-center gap-1.5 shadow-xs" style="font-size: 0.82rem;">
                        <i class="bi bi-x-lg"></i> Clear Search
                    </a>
                </div>
            </div>
        </div>
    @endif

    {{-- 4. Active Filter Chips --}}
    <div id="active-filter-chips" class="mb-3 d-flex flex-wrap gap-2 align-items-center">
        @include('customer.components.active-filters')
    </div>

    {{-- 5. Full Width Products Container & Grid (4 per row on Desktop, 3 on Tablet, 2 on Mobile) --}}
    <div id="products-container" class="position-relative min-vh-50">
        <div class="row row-cols-2 row-cols-md-3 row-cols-lg-4 g-2 g-md-3" id="product-grid-wrapper">
            @include('customer.components.shop.product-grid', ['products' => $products])
        </div>

        <div id="pagination-container" class="mt-5 d-flex justify-content-center">
            {{ $products->links('pagination::bootstrap-5') }}
        </div>
    </div>

</div>

{{-- 6. Slide-in Offcanvas Filters Drawer (Opens smoothly on Desktop & Mobile) --}}
<div class="offcanvas offcanvas-start border-0 shadow-lg" tabindex="-1" id="offcanvasFilters" aria-labelledby="offcanvasFiltersLabel" style="width: 380px; max-width: 90vw; z-index: 1060;">
    <div class="offcanvas-header bg-white py-3.5 px-4 border-bottom d-flex align-items-center justify-content-between">
        <div class="d-flex align-items-center" style="gap: 1rem !important;">
            <div class="rounded-3 d-flex align-items-center justify-content-center flex-shrink-0" 
                 style="width: 44px; height: 44px; background: #ede9fe; color: #6366f1; font-size: 1.25rem;">
                <i class="bi bi-funnel-fill"></i>
            </div>
            <div>
                <h5 class="offcanvas-title fw-bolder mb-0 text-dark" id="offcanvasFiltersLabel" style="font-size: 1.1rem; letter-spacing: -0.01em;">Filter & Refine</h5>
                <div class="text-muted small" style="font-size: 0.78rem; margin-top: 2px;">Customize your product view</div>
            </div>
        </div>
        <button type="button" class="btn-close shadow-none" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    
    <div class="offcanvas-body p-0 d-flex flex-column" style="background: #ffffff;">
        @include('customer.components.shop.filters-content', ['categories' => $categories, 'brands' => $brands, 'prefix' => 'drawer'])
    </div>
</div>
@endsection

@push('scripts')
<script src="{{ asset('js/shop.js?v=2.0') }}" defer></script>
@endpush
