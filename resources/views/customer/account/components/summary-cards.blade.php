<div class="row g-3 mb-4">
    {{-- Total Orders --}}
    <div class="col-6 col-md-3">
        <a href="{{ route('account.orders.index') }}" class="card text-decoration-none h-100 shadow-sm transition-all" 
            style="border-radius: 14px !important; border: 1px solid #e2e8f0; border-left: 5px solid #6366f1 !important; background: #ffffff;">
            <div class="card-body p-3.5">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="fw-bold text-uppercase" style="font-size:0.72rem; letter-spacing:0.05em; color: #6366f1;">Total Orders</span>
                    <div style="width:34px; height:34px; border-radius:10px; background:#ede9fe; display:flex; align-items:center; justify-content:center;">
                        <i class="bi bi-box-seam-fill" style="font-size:0.95rem; color:#6366f1;"></i>
                    </div>
                </div>
                <h3 class="mb-0 fw-bolder" style="font-size:1.6rem; color:#0f172a;">{{ $stats['orders'] }}</h3>
            </div>
        </a>
    </div>

    {{-- Wishlist Items --}}
    <div class="col-6 col-md-3">
        <a href="{{ route('wishlist.index') }}" class="card text-decoration-none h-100 shadow-sm transition-all" 
            style="border-radius: 14px !important; border: 1px solid #e2e8f0; border-left: 5px solid #ec4899 !important; background: #ffffff;">
            <div class="card-body p-3.5">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="fw-bold text-uppercase" style="font-size:0.72rem; letter-spacing:0.05em; color: #be185d;">Wishlist Items</span>
                    <div style="width:34px; height:34px; border-radius:10px; background:#fce7f3; display:flex; align-items:center; justify-content:center;">
                        <i class="bi bi-heart-fill" style="font-size:0.95rem; color:#ec4899;"></i>
                    </div>
                </div>
                <h3 class="mb-0 fw-bolder" style="font-size:1.6rem; color:#0f172a;">{{ $stats['wishlist'] }}</h3>
            </div>
        </a>
    </div>

    {{-- Items in Cart --}}
    <div class="col-6 col-md-3">
        <a href="{{ route('cart.index') }}" class="card text-decoration-none h-100 shadow-sm transition-all" 
            style="border-radius: 14px !important; border: 1px solid #e2e8f0; border-left: 5px solid #10b981 !important; background: #ffffff;">
            <div class="card-body p-3.5">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="fw-bold text-uppercase" style="font-size:0.72rem; letter-spacing:0.05em; color: #047857;">Items in Cart</span>
                    <div style="width:34px; height:34px; border-radius:10px; background:#d1fae5; display:flex; align-items:center; justify-content:center;">
                        <i class="bi bi-bag-check-fill" style="font-size:0.95rem; color:#10b981;"></i>
                    </div>
                </div>
                <h3 class="mb-0 fw-bolder" style="font-size:1.6rem; color:#0f172a;">{{ $stats['cart'] }}</h3>
            </div>
        </a>
    </div>

    {{-- Reviews Written --}}
    <div class="col-6 col-md-3">
        <a href="{{ route('account.reviews') }}" class="card text-decoration-none h-100 shadow-sm transition-all" 
            style="border-radius: 14px !important; border: 1px solid #e2e8f0; border-left: 5px solid #f59e0b !important; background: #ffffff;">
            <div class="card-body p-3.5">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="fw-bold text-uppercase" style="font-size:0.72rem; letter-spacing:0.05em; color: #b45309;">Reviews Written</span>
                    <div style="width:34px; height:34px; border-radius:10px; background:#fef3c7; display:flex; align-items:center; justify-content:center;">
                        <i class="bi bi-star-fill" style="font-size:0.95rem; color:#f59e0b;"></i>
                    </div>
                </div>
                <h3 class="mb-0 fw-bolder" style="font-size:1.6rem; color:#0f172a;">{{ $stats['reviews'] }}</h3>
            </div>
        </a>
    </div>
</div>
