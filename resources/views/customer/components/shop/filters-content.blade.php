<form class="filter-form d-flex flex-column h-100 overflow-auto filter-scroll-area">
    <!-- Filter Options Content -->
    <div class="p-4">
        <!-- Hidden inputs for existing query params like sort -->
        <input type="hidden" name="sort" value="{{ request('sort', 'latest') }}">

        <!-- Category -->
        <div class="mb-4">
            <label class="form-label fw-bold text-dark mb-3 small text-uppercase tracking-wider">Categories</label>
            <div class="filter-scroll pe-2" style="max-height: 250px; overflow-y: auto;">
                @foreach($categories as $category)
                <div class="form-check mb-2">
                    <input class="btn-check filter-check" type="checkbox" name="category[]" value="{{ $category->id }}" id="cat_{{ $prefix }}_{{ $category->id }}" {{ in_array($category->id, (array)request('category', [])) ? 'checked' : '' }}>
                    <label class="btn btn-outline-light text-dark text-start w-100 border rounded-3 py-2 px-3 small fw-medium transition-all" for="cat_{{ $prefix }}_{{ $category->id }}">
                        {{ $category->name }}
                    </label>
                </div>
                @endforeach
            </div>
        </div>

        <!-- Brands (Dynamically loaded) -->
        <div class="mb-4 brand-filter-section" style="display: {{ request('category') ? 'block' : 'none' }};">
            <label class="form-label fw-bold text-dark mb-3 small text-uppercase tracking-wider">Brands</label>
            <div class="brand-list-container filter-scroll pe-2" style="max-height: 250px; overflow-y: auto;">
                @if(request('category'))
                    @include('customer.components.shop.brand-filter-options', ['brands' => $brands, 'prefix' => $prefix])
                @endif
            </div>
        </div>

        <!-- Price Range -->
        <div class="mb-4">
            <label class="form-label fw-bold text-dark mb-3 small text-uppercase tracking-wider">Price Range</label>
            <div class="row g-2">
                <div class="col-6">
                    <input type="number" name="min_price" class="form-control form-control-sm filter-input" placeholder="Min" value="{{ request('min_price') }}">
                </div>
                <div class="col-6">
                    <input type="number" name="max_price" class="form-control form-control-sm filter-input" placeholder="Max" value="{{ request('max_price') }}">
                </div>
            </div>
        </div>

        <!-- Other Filters -->
        <div class="mb-2">
            <label class="form-label fw-bold text-dark mb-3 small text-uppercase tracking-wider">More Options</label>
            <div class="form-check mb-2">
                <input class="form-check-input filter-check" type="checkbox" name="only_discounted" value="1" id="discounted_{{ $prefix }}" {{ request('only_discounted') ? 'checked' : '' }}>
                <label class="form-check-label small" for="discounted_{{ $prefix }}">Only Discounted</label>
            </div>
            <div class="form-check">
                <input class="form-check-input filter-check" type="checkbox" name="featured" value="1" id="feat_{{ $prefix }}" {{ request('featured') ? 'checked' : '' }}>
                <label class="form-check-label small" for="feat_{{ $prefix }}">Featured Only</label>
            </div>
        </div>
    </div>

    <!-- Apply Filters Button - Sticky at the bottom of the form -->
    <div class="mt-auto sticky-bottom p-4 bg-white border-top">
        <button type="submit" class="btn btn-primary rounded-pill py-2 shadow-sm fw-bold w-100">Apply Filters</button>
    </div>
</form>

<style>
    .filter-scroll::-webkit-scrollbar { width: 4px; }
    .filter-scroll::-webkit-scrollbar-thumb { background: #e2e8f0; border-radius: 10px; }
</style>
