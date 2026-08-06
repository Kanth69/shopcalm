@if($trendingProducts->isNotEmpty())
<section class="py-5 bg-light bg-opacity-50">
    <div class="container">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h3 class="fw-bold mb-1">Trending Now</h3>
                <p class="text-muted small mb-0">Discover what's hot and popular right now.</p>
            </div>
            <a href="{{ route('shop', ['trending' => 1]) }}" class="btn btn-outline-dark rounded-pill btn-sm px-4 fw-bold">View Trending</a>
        </div>
        <div class="row row-cols-1 row-cols-sm-2 row-cols-md-3 row-cols-lg-4 g-4">
            @foreach($trendingProducts as $product)
                <div class="col">
                    @include('customer.components.product-card', ['product' => $product])
                </div>
            @endforeach
        </div>
    </div>
</section>
@endif
