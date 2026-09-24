@if($featuredProducts->isNotEmpty())
<section class="py-3 py-md-5 bg-white border-bottom">
    <div class="container px-2 px-md-4">
        <div class="d-flex justify-content-between align-items-center mb-3 mb-md-4">
            <div>
                <div class="mb-1 d-none d-md-block">
                    <span class="badge rounded-pill px-3 py-1.5 fw-bold text-uppercase d-inline-flex align-items-center gap-1" style="background: rgba(99,102,241,0.12); color: #6366f1; font-size: 0.72rem; letter-spacing: 0.05em;">
                        <i class="bi bi-star-fill text-warning me-1"></i> Handpicked Excellence
                    </span>
                </div>
                <h4 class="fw-bolder mb-0 text-dark" style="letter-spacing: -0.02em; font-size: clamp(1.15rem, 2.5vw, 1.6rem);">Featured Products</h4>
            </div>
            <a href="{{ route('shop', ['featured' => 1]) }}" class="btn btn-sm btn-light border rounded-pill px-3 py-1.5 fw-semibold text-primary" style="font-size: 0.8rem;">
                <span>View All</span> <i class="bi bi-arrow-right ms-1"></i>
            </a>
        </div>
        <div class="row row-cols-2 row-cols-md-3 row-cols-lg-4 g-2 g-md-3">
            @foreach($featuredProducts as $product)
                <div class="col d-flex">
                    @include('customer.components.product-card', ['product' => $product])
                </div>
            @endforeach
        </div>
    </div>
</section>
@endif
