<div class="row g-4 mb-4">
    <div class="col-md-3">
        <div class="card summary-card h-100">
            <div class="card-body text-center">
                <i class="bi bi-box-seam display-4 text-primary"></i>
                <h5 class="card-title mt-3">{{ $stats['orders'] }}</h5>
                <p class="card-text">Total Orders</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card summary-card h-100">
            <div class="card-body text-center">
                <i class="bi bi-heart display-4 text-danger"></i>
                <h5 class="card-title mt-3">{{ $stats['wishlist'] }}</h5>
                <p class="card-text">Wishlist Items</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card summary-card h-100">
            <div class="card-body text-center">
                <i class="bi bi-cart display-4 text-success"></i>
                <h5 class="card-title mt-3">{{ $stats['cart'] }}</h5>
                <p class="card-text">Items in Cart</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card summary-card h-100">
            <div class="card-body text-center">
                <i class="bi bi-star display-4 text-warning"></i>
                <h5 class="card-title mt-3">{{ $stats['reviews'] }}</h5>
                <p class="card-text">Reviews Written</p>
            </div>
        </div>
    </div>
</div>
