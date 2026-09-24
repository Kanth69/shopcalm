<form class="filter-form d-flex flex-column h-100 overflow-auto filter-scroll-area">
    <!-- Hidden inputs for existing query params like sort -->
    <input type="hidden" name="sort" value="{{ request('sort', 'latest') }}">

    <!-- Filter Options Accordion Content -->
    <div class="p-3.5 p-md-4">
        
        {{-- 1. CATEGORIES ACCORDION SECTION (2-Column Tiles) --}}
        <div class="border rounded-4 mb-3 overflow-hidden bg-white shadow-xs" style="border-color: #e2e8f0 !important;">
            <div class="p-3 d-flex align-items-center justify-content-between cursor-pointer filter-accordion-header" 
                 data-bs-toggle="collapse" data-bs-target="#collapseCat_{{ $prefix }}" aria-expanded="false"
                 style="background: #f8fafc; cursor: pointer; user-select: none;">
                <div class="d-flex align-items-center" style="gap: 0.85rem !important;">
                    <div class="rounded-3 d-flex align-items-center justify-content-center flex-shrink-0" style="width: 34px; height: 34px; background: #ede9fe; color: #6366f1;">
                        <i class="bi bi-grid-fill" style="font-size: 0.95rem;"></i>
                    </div>
                    <span class="fw-bold text-dark small text-uppercase" style="letter-spacing: 0.05em; font-size: 0.82rem;">Categories</span>
                </div>
                <div class="d-flex align-items-center" style="gap: 0.5rem !important;">
                    @php $catSelectedCount = count((array)request('category', [])); @endphp
                    @if($catSelectedCount > 0)
                        <span class="badge rounded-pill bg-primary px-2.5 py-1 text-white" style="font-size: 0.68rem;">{{ $catSelectedCount }} selected</span>
                    @else
                        <span class="badge rounded-pill bg-white text-secondary border px-2.5 py-1" style="font-size: 0.68rem;">{{ count($categories) }}</span>
                    @endif
                    <i class="bi bi-chevron-down small text-muted accordion-arrow transition-all"></i>
                </div>
            </div>
            
            <div class="collapse" id="collapseCat_{{ $prefix }}">
                <div class="p-3 border-top filter-scroll" style="max-height: 280px; overflow-y: auto; border-color: #f1f5f9 !important;">
                    <div class="row g-2">
                        @foreach($categories as $category)
                        <div class="col-6">
                            <input class="btn-check filter-check" type="checkbox" name="category[]" value="{{ $category->id }}" id="cat_{{ $prefix }}_{{ $category->id }}" {{ in_array($category->id, (array)request('category', [])) ? 'checked' : '' }}>
                            <label class="btn btn-outline-light text-dark text-start w-100 border rounded-4 p-2.5 small fw-medium transition-all category-tile-pill d-flex flex-column justify-content-between h-100" 
                                   for="cat_{{ $prefix }}_{{ $category->id }}" 
                                   style="border-color: #e2e8f0 !important; background: #ffffff; min-height: 60px; transition: all 0.2s ease;">
                                <div class="d-flex align-items-center justify-content-between w-100 mb-1.5">
                                    <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0 cat-icon-sphere" 
                                         style="width: 26px; height: 26px; background: #ede9fe; color: #6366f1; font-size: 0.75rem;">
                                        <i class="bi bi-tag-fill"></i>
                                    </div>
                                    <div class="cat-check-badge rounded-circle d-none align-items-center justify-content-center shadow-xs" 
                                         style="width: 18px; height: 18px; background: #ffffff; color: #6366f1; font-size: 0.7rem;">
                                        <i class="bi bi-check-lg fw-bold"></i>
                                    </div>
                                </div>
                                <span class="text-truncate fw-semibold cat-title" style="font-size: 0.82rem;">{{ $category->name }}</span>
                            </label>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        {{-- 2. BRANDS ACCORDION SECTION (Horizontal Capsule Pills) --}}
        <div class="border rounded-4 mb-3 overflow-hidden bg-white shadow-xs brand-filter-section" 
             style="border-color: #e2e8f0 !important; display: {{ request('category') ? 'block' : 'none' }};">
            <div class="p-3 d-flex align-items-center justify-content-between cursor-pointer filter-accordion-header" 
                 data-bs-toggle="collapse" data-bs-target="#collapseBrand_{{ $prefix }}" aria-expanded="false"
                 style="background: #f8fafc; cursor: pointer; user-select: none;">
                <div class="d-flex align-items-center" style="gap: 0.85rem !important;">
                    <div class="rounded-3 d-flex align-items-center justify-content-center flex-shrink-0" style="width: 34px; height: 34px; background: #cffafe; color: #0891b2;">
                        <i class="bi bi-award-fill" style="font-size: 0.95rem;"></i>
                    </div>
                    <span class="fw-bold text-dark small text-uppercase" style="letter-spacing: 0.05em; font-size: 0.82rem;">Brands</span>
                </div>
                <div class="d-flex align-items-center" style="gap: 0.5rem !important;">
                    @php $brandSelectedCount = count((array)request('brand', [])); @endphp
                    @if($brandSelectedCount > 0)
                        <span class="badge rounded-pill bg-info px-2.5 py-1 text-white" style="font-size: 0.68rem;">{{ $brandSelectedCount }} selected</span>
                    @endif
                    <i class="bi bi-chevron-down small text-muted accordion-arrow transition-all"></i>
                </div>
            </div>
            
            <div class="collapse" id="collapseBrand_{{ $prefix }}">
                <div class="p-3 border-top brand-list-container filter-scroll" style="max-height: 220px; overflow-y: auto; border-color: #f1f5f9 !important;">
                    @if(request('category'))
                        @include('customer.components.shop.brand-filter-options', ['brands' => $brands, 'prefix' => $prefix])
                    @endif
                </div>
            </div>
        </div>

        {{-- 3. PRICE RANGE ACCORDION SECTION --}}
        <div class="border rounded-4 mb-3 overflow-hidden bg-white shadow-xs" style="border-color: #e2e8f0 !important;">
            <div class="p-3 d-flex align-items-center justify-content-between cursor-pointer filter-accordion-header" 
                 data-bs-toggle="collapse" data-bs-target="#collapsePrice_{{ $prefix }}" aria-expanded="false"
                 style="background: #f8fafc; cursor: pointer; user-select: none;">
                <div class="d-flex align-items-center" style="gap: 0.85rem !important;">
                    <div class="rounded-3 d-flex align-items-center justify-content-center flex-shrink-0" style="width: 34px; height: 34px; background: #d1fae5; color: #059669;">
                        <i class="bi bi-cash-stack" style="font-size: 0.95rem;"></i>
                    </div>
                    <span class="fw-bold text-dark small text-uppercase" style="letter-spacing: 0.05em; font-size: 0.82rem;">Price Range (₹)</span>
                </div>
                <div class="d-flex align-items-center" style="gap: 0.5rem !important;">
                    @if(request()->filled('min_price') || request()->filled('max_price'))
                        <span class="badge rounded-pill bg-success px-2.5 py-1 text-white" style="font-size: 0.68rem;">Active</span>
                    @endif
                    <i class="bi bi-chevron-down small text-muted accordion-arrow transition-all"></i>
                </div>
            </div>
            
            <div class="collapse" id="collapsePrice_{{ $prefix }}">
                <div class="p-3 border-top" style="border-color: #f1f5f9 !important;">
                    <div class="row g-2 mb-2">
                        <div class="col-6">
                            <label class="text-muted small mb-1" style="font-size: 0.72rem;">Min Price</label>
                            <div class="input-group input-group-sm">
                                <span class="input-group-text bg-light text-muted border-end-0" style="border-radius: 8px 0 0 8px; border-color: #cbd5e1;">₹</span>
                                <input type="number" name="min_price" class="form-control form-control-sm filter-input border-start-0 ps-1" placeholder="0" value="{{ request('min_price') }}" style="border-radius: 0 8px 8px 0; border-color: #cbd5e1; font-size: 0.84rem;">
                            </div>
                        </div>
                        <div class="col-6">
                            <label class="text-muted small mb-1" style="font-size: 0.72rem;">Max Price</label>
                            <div class="input-group input-group-sm">
                                <span class="input-group-text bg-light text-muted border-end-0" style="border-radius: 8px 0 0 8px; border-color: #cbd5e1;">₹</span>
                                <input type="number" name="max_price" class="form-control form-control-sm filter-input border-start-0 ps-1" placeholder="50000" value="{{ request('max_price') }}" style="border-radius: 0 8px 8px 0; border-color: #cbd5e1; font-size: 0.84rem;">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- 4. SPECIAL OFFERS ACCORDION SECTION --}}
        <div class="border rounded-4 mb-2 overflow-hidden bg-white shadow-xs" style="border-color: #e2e8f0 !important;">
            <div class="p-3 d-flex align-items-center justify-content-between cursor-pointer filter-accordion-header" 
                 data-bs-toggle="collapse" data-bs-target="#collapseOffers_{{ $prefix }}" aria-expanded="false"
                 style="background: #f8fafc; cursor: pointer; user-select: none;">
                <div class="d-flex align-items-center" style="gap: 0.85rem !important;">
                    <div class="rounded-3 d-flex align-items-center justify-content-center flex-shrink-0" style="width: 34px; height: 34px; background: #fee2e2; color: #dc2626;">
                        <i class="bi bi-lightning-charge-fill" style="font-size: 0.95rem;"></i>
                    </div>
                    <span class="fw-bold text-dark small text-uppercase" style="letter-spacing: 0.05em; font-size: 0.82rem;">Special Offers</span>
                </div>
                <div class="d-flex align-items-center" style="gap: 0.5rem !important;">
                    @if(request()->boolean('only_discounted') || request()->boolean('featured'))
                        <span class="badge rounded-pill bg-danger px-2.5 py-1 text-white" style="font-size: 0.68rem;">Active</span>
                    @endif
                    <i class="bi bi-chevron-down small text-muted accordion-arrow transition-all"></i>
                </div>
            </div>
            
            <div class="collapse" id="collapseOffers_{{ $prefix }}">
                <div class="p-3 border-top" style="border-color: #f1f5f9 !important;">
                    <div class="form-check mb-2">
                        <input class="form-check-input filter-check" type="checkbox" name="only_discounted" value="1" id="discounted_{{ $prefix }}" {{ request('only_discounted') ? 'checked' : '' }}>
                        <label class="form-check-label small text-dark fw-medium" for="discounted_{{ $prefix }}" style="font-size: 0.84rem;">
                            🔥 On Sale / Discounted Deals
                        </label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input filter-check" type="checkbox" name="featured" value="1" id="feat_{{ $prefix }}" {{ request('featured') ? 'checked' : '' }}>
                        <label class="form-check-label small text-dark fw-medium" for="feat_{{ $prefix }}" style="font-size: 0.84rem;">
                            ⭐ Featured Products Only
                        </label>
                    </div>
                </div>
            </div>
        </div>

    </div>

    <!-- Apply Filters Button - Sticky at the bottom of the form -->
    <div class="mt-auto sticky-bottom p-3.5 px-4 bg-white border-top d-flex gap-2 shadow-sm" style="border-color: #e2e8f0 !important;">
        <button type="submit" class="btn btn-primary rounded-pill py-2.5 shadow-sm fw-bold flex-grow-1" 
                style="background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%); border: none; font-size: 0.9rem;">
            <i class="bi bi-check2-circle me-1"></i> Apply Filters
        </button>
        <button type="button" class="btn btn-light border rounded-pill px-3 py-2 text-muted clear-filters-btn d-flex align-items-center" title="Reset Filters" style="font-size: 0.88rem;">
            <i class="bi bi-arrow-counterclockwise me-1"></i> Reset
        </button>
    </div>
</form>

<style>
    .filter-scroll::-webkit-scrollbar { width: 4px; }
    .filter-scroll::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 10px; }
    
    .filter-accordion-header:hover {
        background: #f1f5f9 !important;
    }
    .filter-accordion-header[aria-expanded="true"] .accordion-arrow {
        transform: rotate(180deg);
    }
    
    /* 1. Category Tiles Grid UI (2-column layout) */
    .category-tile-pill {
        border: 1px solid #e2e8f0 !important;
        cursor: pointer;
    }
    .category-tile-pill:hover { 
        background-color: #f8fafc !important; 
        border-color: #6366f1 !important; 
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(15, 23, 42, 0.05) !important;
    }
    .btn-check:checked + .category-tile-pill {
        background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%) !important;
        color: #ffffff !important;
        border-color: #6366f1 !important;
        box-shadow: 0 6px 16px rgba(99, 102, 241, 0.35) !important;
        transform: translateY(-1px);
    }
    .btn-check:checked + .category-tile-pill .cat-icon-sphere {
        background: rgba(255, 255, 255, 0.25) !important;
        color: #ffffff !important;
    }
    .btn-check:checked + .category-tile-pill .cat-check-badge {
        display: flex !important;
    }
    .btn-check:checked + .category-tile-pill .cat-title {
        color: #ffffff !important;
        font-weight: 700 !important;
    }

    /* 2. Brand Capsule Pills UI (Distinct cyan/teal layout) */
    .brand-capsule-pill {
        border: 1px solid #e2e8f0 !important;
        cursor: pointer;
    }
    .brand-capsule-pill:hover {
        background-color: #f0fdfa !important;
        border-color: #06b6d4 !important;
        transform: translateX(3px);
        box-shadow: 0 4px 12px rgba(6, 182, 212, 0.1) !important;
    }
    .btn-check:checked + .brand-capsule-pill {
        background: linear-gradient(135deg, #0284c7 0%, #06b6d4 100%) !important;
        color: #ffffff !important;
        border-color: #0284c7 !important;
        box-shadow: 0 4px 14px rgba(6, 182, 212, 0.35) !important;
        transform: translateX(3px);
    }
    .btn-check:checked + .brand-capsule-pill .brand-avatar-sphere {
        background: rgba(255, 255, 255, 0.25) !important;
        color: #ffffff !important;
    }
    .btn-check:checked + .brand-capsule-pill .brand-name-text {
        color: #ffffff !important;
        font-weight: 700 !important;
    }
    .btn-check:checked + .brand-capsule-pill .brand-check-icon {
        display: flex !important;
    }
</style>
