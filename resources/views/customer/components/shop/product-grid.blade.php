@forelse($products as $product)
    <div class="col-lg-3 col-md-4 col-sm-6 mb-4">
        @include('customer.components.product-card', ['product' => $product])
    </div>
@empty
    <div class="col-12 text-center py-5">
        <i class="bi bi-search display-1 text-muted opacity-25"></i>
        <h3 class="mt-4 fw-bold">No exact matches</h3>
        <p class="text-muted">Try adjusting your filters to find what you're looking for.</p>
        <button type="button" class="btn btn-primary rounded-pill px-4 mt-2" onclick="clearAllFilters()">Clear All Filters</button>
    </div>
@endforelse
