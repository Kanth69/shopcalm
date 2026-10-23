<form class="filter-form d-flex flex-column h-100 overflow-auto filter-scroll-area">
    <!-- Hidden inputs for existing query params like sort & search -->
    <input type="hidden" name="sort" value="{{ request('sort', 'latest') }}">
    @if(request('q'))
        <input type="hidden" name="q" value="{{ request('q') }}">
    @endif

    <!-- Filter Options Accordion Content -->
    <div class="p-2.5 p-sm-3">
        
        {{-- 1. CATEGORY ACCORDION SECTION --}}
        @php 
            $catSelectedCount = count((array)request('category', [])); 
            $catOpen = $catSelectedCount > 0 || (empty(request('brand')) && !request('min_price') && !request('max_price') && !request('only_discounted') && !request('featured') && !request('in_stock'));
        @endphp
        <div class="border rounded-3 mb-2 overflow-hidden bg-white shadow-xs" style="border-color: #e2e8f0 !important;">
            <div class="py-2 px-2.5 d-flex align-items-center justify-content-between cursor-pointer filter-accordion-header" 
                 data-bs-toggle="collapse" data-bs-target="#collapseCat_{{ $prefix }}" aria-expanded="{{ $catOpen ? 'true' : 'false' }}"
                 style="background: #f8fafc; cursor: pointer; user-select: none;">
                <div class="d-flex align-items-center gap-2">
                    <div class="rounded-2 d-flex align-items-center justify-content-center flex-shrink-0" style="width: 26px; height: 26px; background: #ede9fe; color: #6366f1; font-size: 0.82rem;">
                        <i class="bi bi-grid-fill"></i>
                    </div>
                    <span class="fw-bold text-dark text-uppercase" style="letter-spacing: 0.04em; font-size: 0.78rem;">Category</span>
                </div>
                <div class="d-flex align-items-center gap-1.5">
                    @if($catSelectedCount > 0)
                        <span class="badge rounded-pill bg-primary px-2 py-0.5 text-white" style="font-size: 0.66rem;">{{ $catSelectedCount }} selected</span>
                    @else
                        <span class="badge rounded-pill bg-white text-secondary border px-1.5 py-0.5" style="font-size: 0.66rem;">{{ count($categories) }}</span>
                    @endif
                    <i class="bi bi-chevron-down text-muted accordion-arrow transition-all" style="font-size: 0.72rem;"></i>
                </div>
            </div>
            
            <div class="collapse {{ $catOpen ? 'show' : '' }}" id="collapseCat_{{ $prefix }}">
                <div class="p-2.5 px-3 border-top filter-scroll" style="max-height: 190px; overflow-y: auto; border-color: #f1f5f9 !important;">
                    <div class="d-flex flex-column" style="gap: 0.35rem !important;">
                        @foreach($categories as $category)
                            <div class="form-check py-0.5 mb-0 d-flex align-items-center justify-content-between">
                                <div class="d-flex align-items-center gap-2 overflow-hidden">
                                    <input class="form-check-input filter-check m-0 flex-shrink-0" type="checkbox" name="category[]" value="{{ $category->id }}" id="cat_{{ $prefix }}_{{ $category->id }}" {{ in_array($category->id, (array)request('category', [])) ? 'checked' : '' }}>
                                    <label class="form-check-label small text-dark fw-medium text-truncate cursor-pointer" for="cat_{{ $prefix }}_{{ $category->id }}" style="font-size: 0.82rem;">
                                        {{ $category->name }}
                                    </label>
                                </div>
                                @if(isset($category->products_count) && $category->products_count > 0)
                                    <span class="text-muted small ms-1 flex-shrink-0" style="font-size: 0.72rem;">({{ $category->products_count }})</span>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        {{-- 2. BRAND ACCORDION SECTION (Always visible) --}}
        @php 
            $brandSelectedCount = count((array)request('brand', [])); 
            $brandOpen = $brandSelectedCount > 0;
        @endphp
        <div class="border rounded-3 mb-2 overflow-hidden bg-white shadow-xs brand-filter-section" style="border-color: #e2e8f0 !important;">
            <div class="py-2 px-2.5 d-flex align-items-center justify-content-between cursor-pointer filter-accordion-header" 
                 data-bs-toggle="collapse" data-bs-target="#collapseBrand_{{ $prefix }}" aria-expanded="{{ $brandOpen ? 'true' : 'false' }}"
                 style="background: #f8fafc; cursor: pointer; user-select: none;">
                <div class="d-flex align-items-center gap-2">
                    <div class="rounded-2 d-flex align-items-center justify-content-center flex-shrink-0" style="width: 26px; height: 26px; background: #cffafe; color: #0891b2; font-size: 0.82rem;">
                        <i class="bi bi-award-fill"></i>
                    </div>
                    <span class="fw-bold text-dark text-uppercase" style="letter-spacing: 0.04em; font-size: 0.78rem;">Brand</span>
                </div>
                <div class="d-flex align-items-center gap-1.5">
                    @if($brandSelectedCount > 0)
                        <span class="badge rounded-pill bg-info px-2 py-0.5 text-white" style="font-size: 0.66rem;">{{ $brandSelectedCount }} selected</span>
                    @else
                        <span class="badge rounded-pill bg-white text-secondary border px-1.5 py-0.5" style="font-size: 0.66rem;">{{ count($brands) }}</span>
                    @endif
                    <i class="bi bi-chevron-down text-muted accordion-arrow transition-all" style="font-size: 0.72rem;"></i>
                </div>
            </div>
            
            <div class="collapse {{ $brandOpen ? 'show' : '' }}" id="collapseBrand_{{ $prefix }}">
                <div class="p-2.5 px-3 border-top brand-list-container filter-scroll" style="max-height: 190px; overflow-y: auto; border-color: #f1f5f9 !important;">
                    @include('customer.components.shop.brand-filter-options', ['brands' => $brands, 'prefix' => $prefix])
                </div>
            </div>
        </div>

        {{-- 3. PRICE RANGE ACCORDION SECTION --}}
        @php 
            $priceActive = request()->filled('min_price') || request()->filled('max_price'); 
        @endphp
        <div class="border rounded-3 mb-2 overflow-hidden bg-white shadow-xs" style="border-color: #e2e8f0 !important;">
            <div class="py-2 px-2.5 d-flex align-items-center justify-content-between cursor-pointer filter-accordion-header" 
                 data-bs-toggle="collapse" data-bs-target="#collapsePrice_{{ $prefix }}" aria-expanded="{{ $priceActive ? 'true' : 'false' }}"
                 style="background: #f8fafc; cursor: pointer; user-select: none;">
                <div class="d-flex align-items-center gap-2">
                    <div class="rounded-2 d-flex align-items-center justify-content-center flex-shrink-0" style="width: 26px; height: 26px; background: #d1fae5; color: #059669; font-size: 0.82rem;">
                        <i class="bi bi-cash-stack"></i>
                    </div>
                    <span class="fw-bold text-dark text-uppercase" style="letter-spacing: 0.04em; font-size: 0.78rem;">Price Range</span>
                </div>
                <div class="d-flex align-items-center gap-1.5">
                    @if($priceActive)
                        <span class="badge rounded-pill bg-success px-2 py-0.5 text-white" style="font-size: 0.66rem;">Active</span>
                    @endif
                    <i class="bi bi-chevron-down text-muted accordion-arrow transition-all" style="font-size: 0.72rem;"></i>
                </div>
            </div>
            
            <div class="collapse {{ $priceActive ? 'show' : '' }}" id="collapsePrice_{{ $prefix }}">
                <div class="p-2.5 px-3 border-top" style="border-color: #f1f5f9 !important;">
                    <div class="row g-2">
                        <div class="col-6">
                            <label class="text-muted small mb-1" style="font-size: 0.7rem;">Min Price</label>
                            <div class="input-group input-group-sm">
                                <span class="input-group-text bg-light text-muted border-end-0 py-1 px-2" style="font-size: 0.75rem; border-radius: 6px 0 0 6px; border-color: #cbd5e1;">₹</span>
                                <input type="number" name="min_price" class="form-control form-control-sm filter-input border-start-0 ps-1" placeholder="0" value="{{ request('min_price') }}" style="border-radius: 0 6px 6px 0; border-color: #cbd5e1; font-size: 0.8rem;">
                            </div>
                        </div>
                        <div class="col-6">
                            <label class="text-muted small mb-1" style="font-size: 0.7rem;">Max Price</label>
                            <div class="input-group input-group-sm">
                                <span class="input-group-text bg-light text-muted border-end-0 py-1 px-2" style="font-size: 0.75rem; border-radius: 6px 0 0 6px; border-color: #cbd5e1;">₹</span>
                                <input type="number" name="max_price" class="form-control form-control-sm filter-input border-start-0 ps-1" placeholder="50000" value="{{ request('max_price') }}" style="border-radius: 0 6px 6px 0; border-color: #cbd5e1; font-size: 0.8rem;">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- 4. OFFERS ACCORDION SECTION --}}
        @php 
            $offersActive = request()->boolean('only_discounted') || request()->boolean('featured'); 
        @endphp
        <div class="border rounded-3 mb-2 overflow-hidden bg-white shadow-xs" style="border-color: #e2e8f0 !important;">
            <div class="py-2 px-2.5 d-flex align-items-center justify-content-between cursor-pointer filter-accordion-header" 
                 data-bs-toggle="collapse" data-bs-target="#collapseOffers_{{ $prefix }}" aria-expanded="{{ $offersActive ? 'true' : 'false' }}"
                 style="background: #f8fafc; cursor: pointer; user-select: none;">
                <div class="d-flex align-items-center gap-2">
                    <div class="rounded-2 d-flex align-items-center justify-content-center flex-shrink-0" style="width: 26px; height: 26px; background: #fee2e2; color: #dc2626; font-size: 0.82rem;">
                        <i class="bi bi-tag-fill"></i>
                    </div>
                    <span class="fw-bold text-dark text-uppercase" style="letter-spacing: 0.04em; font-size: 0.78rem;">Offers</span>
                </div>
                <div class="d-flex align-items-center gap-1.5">
                    @if($offersActive)
                        <span class="badge rounded-pill bg-danger px-2 py-0.5 text-white" style="font-size: 0.66rem;">Active</span>
                    @endif
                    <i class="bi bi-chevron-down text-muted accordion-arrow transition-all" style="font-size: 0.72rem;"></i>
                </div>
            </div>
            
            <div class="collapse {{ $offersActive ? 'show' : '' }}" id="collapseOffers_{{ $prefix }}">
                <div class="p-2.5 px-3 border-top" style="border-color: #f1f5f9 !important;">
                    <div class="form-check py-1 mb-1 d-flex align-items-center gap-2">
                        <input class="form-check-input filter-check m-0 flex-shrink-0" type="checkbox" name="only_discounted" value="1" id="discounted_{{ $prefix }}" {{ request('only_discounted') ? 'checked' : '' }}>
                        <label class="form-check-label small text-dark fw-medium cursor-pointer" for="discounted_{{ $prefix }}" style="font-size: 0.82rem;">
                            🔥 On Sale Deals
                        </label>
                    </div>
                    <div class="form-check py-1 mb-0 d-flex align-items-center gap-2">
                        <input class="form-check-input filter-check m-0 flex-shrink-0" type="checkbox" name="featured" value="1" id="feat_{{ $prefix }}" {{ request('featured') ? 'checked' : '' }}>
                        <label class="form-check-label small text-dark fw-medium cursor-pointer" for="feat_{{ $prefix }}" style="font-size: 0.82rem;">
                            ⭐ Featured Only
                        </label>
                    </div>
                </div>
            </div>
        </div>

        {{-- 5. IN STOCK ACCORDION SECTION --}}
        @php 
            $stockActive = request()->boolean('in_stock'); 
        @endphp
        <div class="border rounded-3 mb-2 overflow-hidden bg-white shadow-xs" style="border-color: #e2e8f0 !important;">
            <div class="py-2 px-2.5 d-flex align-items-center justify-content-between cursor-pointer filter-accordion-header" 
                 data-bs-toggle="collapse" data-bs-target="#collapseStock_{{ $prefix }}" aria-expanded="{{ $stockActive ? 'true' : 'false' }}"
                 style="background: #f8fafc; cursor: pointer; user-select: none;">
                <div class="d-flex align-items-center gap-2">
                    <div class="rounded-2 d-flex align-items-center justify-content-center flex-shrink-0" style="width: 26px; height: 26px; background: #fef3c7; color: #d97706; font-size: 0.82rem;">
                        <i class="bi bi-box-seam-fill"></i>
                    </div>
                    <span class="fw-bold text-dark text-uppercase" style="letter-spacing: 0.04em; font-size: 0.78rem;">In Stock</span>
                </div>
                <div class="d-flex align-items-center gap-1.5">
                    @if($stockActive)
                        <span class="badge rounded-pill bg-warning text-dark px-2 py-0.5" style="font-size: 0.66rem;">Active</span>
                    @endif
                    <i class="bi bi-chevron-down text-muted accordion-arrow transition-all" style="font-size: 0.72rem;"></i>
                </div>
            </div>
            
            <div class="collapse {{ $stockActive ? 'show' : '' }}" id="collapseStock_{{ $prefix }}">
                <div class="p-2.5 px-3 border-top" style="border-color: #f1f5f9 !important;">
                    <div class="form-check py-1 mb-0 d-flex align-items-center gap-2">
                        <input class="form-check-input filter-check m-0 flex-shrink-0" type="checkbox" name="in_stock" value="1" id="stock_{{ $prefix }}" {{ request('in_stock') ? 'checked' : '' }}>
                        <label class="form-check-label small text-dark fw-medium cursor-pointer" for="stock_{{ $prefix }}" style="font-size: 0.82rem;">
                            📦 In Stock Only
                        </label>
                    </div>
                </div>
            </div>
        </div>

    </div>

    <!-- Apply Filters Button - Sticky at the bottom of the form -->
    <div class="mt-auto sticky-bottom p-2.5 px-3 bg-white border-top d-flex gap-2 shadow-sm" style="border-color: #e2e8f0 !important;">
        <button type="submit" class="btn btn-primary rounded-pill py-2 shadow-sm fw-bold flex-grow-1 d-flex align-items-center justify-content-center gap-1" 
                style="background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%); border: none; font-size: 0.84rem;">
            <i class="bi bi-check2-circle"></i> Apply Filters
        </button>
        <button type="button" class="btn btn-light border rounded-pill px-3 py-2 text-muted clear-filters-btn d-flex align-items-center gap-1" title="Reset Filters" style="font-size: 0.82rem;">
            <i class="bi bi-arrow-counterclockwise"></i> Reset
        </button>
    </div>
</form>

<style>
    .filter-scroll::-webkit-scrollbar { width: 3px; }
    .filter-scroll::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 10px; }
    
    .filter-accordion-header {
        transition: background-color 0.15s ease;
    }
    .filter-accordion-header:hover {
        background: #f1f5f9 !important;
    }
    .filter-accordion-header[aria-expanded="true"] .accordion-arrow {
        transform: rotate(180deg);
    }
</style>
