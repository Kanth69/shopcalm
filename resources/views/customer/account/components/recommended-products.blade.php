<div class="card mt-4">
    <div class="card-header">
        <h5 class="card-title mb-0">Recommended For You</h5>
    </div>
    <div class="card-body">
        <div class="row">
            @foreach($recommendedProducts as $product)
                <div class="col-md-3">
                    @include('customer.components.product-card', ['product' => $product])
                </div>
            @endforeach
        </div>
    </div>
</div>
