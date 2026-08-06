@if($latestProducts->isNotEmpty())
<section id="new-arrivals" class="py-5">
    <div class="container">
        <h2 class="text-center mb-4">New Arrivals</h2>
        <div class="row row-cols-1 row-cols-sm-2 row-cols-md-3 row-cols-lg-4 g-4">
            @foreach($latestProducts as $product)
                <div class="col">
                    @include('customer.components.product-card', ['product' => $product])
                </div>
            @endforeach
        </div>
    </div>
</section>
@endif
