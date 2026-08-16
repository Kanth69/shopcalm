<div class="card border-0 shadow-sm rounded-4 overflow-hidden mt-4">
    <div class="card-header bg-white py-3 border-bottom d-flex align-items-center justify-content-between">
        <h6 class="mb-0 fw-bold text-dark"><i class="bi bi-stars text-warning me-2 fs-5"></i>Recommended For You</h6>
        <a href="{{ route('shop') }}" class="small fw-semibold text-primary text-decoration-none">Explore All <i class="bi bi-arrow-right"></i></a>
    </div>
    <div class="card-body p-4">
        <div class="row g-3">
            @foreach($recommendedProducts as $product)
                <div class="col-6 col-md-3">
                    @include('customer.components.product-card', ['product' => $product])
                </div>
            @endforeach
        </div>
    </div>
</div>
