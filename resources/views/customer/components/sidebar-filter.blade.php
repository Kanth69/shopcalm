<form action="{{ route('shop') }}" method="GET">
    <!-- Search term -->
    @if(request('search'))
        <input type="hidden" name="search" value="{{ request('search') }}">
    @endif

    <!-- Category Filter -->
    <div class="mb-4">
        <h6>Categories</h6>
        @foreach($categories as $category)
            <div class="form-check">
                <input class="form-check-input" type="checkbox" name="category[]" value="{{ $category->id }}" id="cat-{{ $category->id }}" {{ in_array($category->id, request('category', [])) ? 'checked' : '' }}>
                <label class="form-check-label" for="cat-{{ $category->id }}">
                    {{ $category->name }}
                </label>
            </div>
        @endforeach
    </div>

    <!-- Brand Filter -->
    <div class="mb-4">
        <h6>Brands</h6>
        @foreach($brands as $brand)
            <div class="form-check">
                <input class="form-check-input" type="checkbox" name="brand[]" value="{{ $brand->id }}" id="brand-{{ $brand->id }}" {{ in_array($brand->id, request('brand', [])) ? 'checked' : '' }}>
                <label class="form-check-label" for="brand-{{ $brand->id }}">
                    {{ $brand->name }}
                </label>
            </div>
        @endforeach
    </div>

    <!-- Price Filter -->
    <div class="mb-4">
        <h6>Price Range</h6>
        <div class="row g-2">
            <div class="col">
                <input type="number" name="min_price" class="form-control" placeholder="Min" value="{{ request('min_price') }}">
            </div>
            <div class="col">
                <input type="number" name="max_price" class="form-control" placeholder="Max" value="{{ request('max_price') }}">
            </div>
        </div>
    </div>

    <!-- Availability Filter -->
    <div class="mb-4">
        <h6>Availability</h6>
        <div class="form-check">
            <input class="form-check-input" type="radio" name="availability" id="in_stock" value="in_stock" {{ request('availability') == 'in_stock' ? 'checked' : '' }}>
            <label class="form-check-label" for="in_stock">
                In Stock
            </label>
        </div>
        <div class="form-check">
            <input class="form-check-input" type="radio" name="availability" id="out_of_stock" value="out_of_stock" {{ request('availability') == 'out_of_stock' ? 'checked' : '' }}>
            <label class="form-check-label" for="out_of_stock">
                Out of Stock
            </label>
        </div>
    </div>

    <div class="d-grid gap-2">
        <button type="submit" class="btn btn-primary">Apply Filters</button>
        <a href="{{ route('shop') }}" class="btn btn-light">Clear All</a>
    </div>
</form>
