@if($latestProducts->isNotEmpty())
<section id="new-arrivals" class="py-3 py-md-5 bg-white border-bottom">
    <div class="container px-2 px-md-4">
        <div class="d-flex justify-content-between align-items-center mb-3 mb-md-4">
            <div>
                <div class="mb-1 d-none d-md-block">
                    <span class="badge rounded-pill px-3 py-1.5 fw-bold text-uppercase d-inline-flex align-items-center gap-1" style="background: rgba(16, 185, 129, 0.12); color: #059669; font-size: 0.72rem; letter-spacing: 0.05em;">
                        <i class="bi bi-sparkles me-1 text-success"></i> Fresh In Stock
                    </span>
                </div>
                <h4 class="fw-bolder mb-0 text-dark" style="letter-spacing: -0.02em; font-size: clamp(1.15rem, 2.5vw, 1.6rem);">New Arrivals</h4>
            </div>
            <a href="{{ route('shop', ['sort' => 'newest']) }}" class="btn btn-sm btn-light border rounded-pill px-3 py-1.5 fw-semibold text-success" style="font-size: 0.8rem;">
                <span>View All</span> <i class="bi bi-arrow-right ms-1"></i>
            </a>
        </div>
        <div class="row row-cols-2 row-cols-md-3 row-cols-lg-4 g-2 g-md-3">
            @foreach($latestProducts as $product)
                <div class="col d-flex">
                    @include('customer.components.product-card', ['product' => $product])
                </div>
            @endforeach
        </div>
    </div>
</section>
@endif
