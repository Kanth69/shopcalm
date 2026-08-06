@if($featuredProducts->isNotEmpty())
<section class="py-5">
    <div class="container">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h3 class="fw-bold mb-1">Featured Products</h3>
                <p class="text-muted small mb-0">Handpicked items for the ultimate shopping experience.</p>
            </div>
            <a href="{{ route('shop', ['featured' => 1]) }}" class="btn btn-outline-primary rounded-pill btn-sm px-4 fw-bold">Explore More</a>
        </div>
        <div class="row row-cols-1 row-cols-sm-2 row-cols-md-3 row-cols-lg-4 g-4">
            @foreach($featuredProducts as $product)
                <div class="col">
                    @include('customer.components.product-card', ['product' => $product])
                </div>
            @endforeach
        </div>
    </div>
</section>
@endif
