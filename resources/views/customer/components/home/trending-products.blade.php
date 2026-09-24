@if($trendingProducts->isNotEmpty())
<section class="py-3 py-md-5" style="background: #f8fafc;">
    <div class="container px-2 px-md-4">
        <div class="d-flex justify-content-between align-items-center mb-3 mb-md-4">
            <div>
                <div class="mb-1 d-none d-md-block">
                    <span class="badge rounded-pill px-3 py-1.5 fw-bold text-uppercase d-inline-flex align-items-center gap-1" style="background: rgba(239, 68, 68, 0.12); color: #dc2626; font-size: 0.72rem; letter-spacing: 0.05em;">
                        <i class="bi bi-fire me-1 text-danger"></i> Trending Right Now
                    </span>
                </div>
                <h4 class="fw-bolder mb-0 text-dark" style="letter-spacing: -0.02em; font-size: clamp(1.15rem, 2.5vw, 1.6rem);">Most Popular Items</h4>
            </div>
            <a href="{{ route('shop', ['trending' => 1]) }}" class="btn btn-sm btn-light border rounded-pill px-3 py-1.5 fw-semibold text-danger" style="font-size: 0.8rem; background: #ffffff;">
                <span>View All</span> <i class="bi bi-arrow-right ms-1"></i>
            </a>
        </div>
        <div class="row row-cols-2 row-cols-md-3 row-cols-lg-4 g-2 g-md-3">
            @foreach($trendingProducts as $product)
                <div class="col d-flex">
                    @include('customer.components.product-card', ['product' => $product])
                </div>
            @endforeach
        </div>
    </div>
</section>
@endif
