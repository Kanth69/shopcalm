<div class="mt-4 mt-md-5 pt-2">
    <div class="d-flex justify-content-between align-items-center mb-3 mb-md-4">
        <div>
            <div class="mb-1 d-none d-md-block">
                <span class="badge rounded-pill px-3 py-1.5 fw-bold text-uppercase d-inline-flex align-items-center gap-1" style="background: rgba(245, 158, 11, 0.12); color: #d97706; font-size: 0.72rem; letter-spacing: 0.05em;">
                    <i class="bi bi-stars me-1 text-warning"></i> Handpicked For You
                </span>
            </div>
            <h4 class="fw-bolder mb-0 text-dark" style="letter-spacing: -0.02em; font-size: clamp(1.15rem, 2.5vw, 1.5rem);">Recommended For You</h4>
            <p class="text-muted small mb-0 d-none d-sm-block mt-0.5" style="font-size: 0.8rem;">
                Handpicked suggestions tailored for your taste
            </p>
        </div>
        <a href="{{ route('shop') }}" class="btn btn-sm btn-light border rounded-pill px-3 py-1.5 fw-semibold text-primary shadow-xs" style="font-size: 0.8rem; background: #ffffff;">
            <span>Explore All</span> <i class="bi bi-arrow-right ms-1"></i>
        </a>
    </div>

    <div class="row row-cols-2 row-cols-md-3 row-cols-lg-4 g-2 g-md-3">
        @foreach($recommendedProducts as $product)
            <div class="col d-flex">
                @include('customer.components.product-card', ['product' => $product])
            </div>
        @endforeach
    </div>
</div>
