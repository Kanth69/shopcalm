<div class="row g-2 g-sm-2.5 g-md-3 mb-3 mb-md-4">
    {{-- Total Orders --}}
    <div class="col-6 col-md-3">
        <a href="{{ route('account.orders.index') }}" class="card text-decoration-none h-100 border-0 shadow-xs rounded-4 kpi-card position-relative overflow-hidden" 
            style="background: #ffffff; border: 1px solid #e2e8f0 !important;">
            <div class="kpi-top-bar" style="background: linear-gradient(90deg, #6366f1, #8b5cf6);"></div>
            <div class="card-body p-2.5 p-sm-3 p-md-3.5">
                <div class="d-flex align-items-center justify-content-between mb-1.5">
                    <span class="fw-bold text-uppercase" style="font-size: 0.65rem; letter-spacing: 0.05em; color: #6366f1;">Orders</span>
                    <div class="rounded-3 d-flex align-items-center justify-content-center flex-shrink-0" 
                         style="width: 28px; height: 28px; background: #ede9fe; color: #6366f1; font-size: 0.85rem;">
                        <i class="bi bi-box-seam-fill"></i>
                    </div>
                </div>
                <div class="d-flex align-items-baseline justify-content-between">
                    <h3 class="mb-0 fw-bolder font-monospace" style="font-size: clamp(1.2rem, 3vw, 1.45rem); color: #0f172a; letter-spacing: -0.02em;">{{ $stats['orders'] }}</h3>
                    <span class="text-muted small d-none d-sm-inline" style="font-size: 0.72rem;">View all <i class="bi bi-arrow-right"></i></span>
                </div>
            </div>
        </a>
    </div>

    {{-- Wishlist Items --}}
    <div class="col-6 col-md-3">
        <a href="{{ route('wishlist.index') }}" class="card text-decoration-none h-100 border-0 shadow-xs rounded-4 kpi-card position-relative overflow-hidden" 
            style="background: #ffffff; border: 1px solid #e2e8f0 !important;">
            <div class="kpi-top-bar" style="background: linear-gradient(90deg, #ec4899, #f43f5e);"></div>
            <div class="card-body p-2.5 p-sm-3 p-md-3.5">
                <div class="d-flex align-items-center justify-content-between mb-1.5">
                    <span class="fw-bold text-uppercase" style="font-size: 0.65rem; letter-spacing: 0.05em; color: #be185d;">Wishlist</span>
                    <div class="rounded-3 d-flex align-items-center justify-content-center flex-shrink-0" 
                         style="width: 28px; height: 28px; background: #fce7f3; color: #ec4899; font-size: 0.85rem;">
                        <i class="bi bi-heart-fill"></i>
                    </div>
                </div>
                <div class="d-flex align-items-baseline justify-content-between">
                    <h3 class="mb-0 fw-bolder font-monospace" style="font-size: clamp(1.2rem, 3vw, 1.45rem); color: #0f172a; letter-spacing: -0.02em;">{{ $stats['wishlist'] }}</h3>
                    <span class="text-muted small d-none d-sm-inline" style="font-size: 0.72rem;">View all <i class="bi bi-arrow-right"></i></span>
                </div>
            </div>
        </a>
    </div>

    {{-- Items in Cart --}}
    <div class="col-6 col-md-3">
        <a href="{{ route('cart.index') }}" class="card text-decoration-none h-100 border-0 shadow-xs rounded-4 kpi-card position-relative overflow-hidden" 
            style="background: #ffffff; border: 1px solid #e2e8f0 !important;">
            <div class="kpi-top-bar" style="background: linear-gradient(90deg, #10b981, #059669);"></div>
            <div class="card-body p-2.5 p-sm-3 p-md-3.5">
                <div class="d-flex align-items-center justify-content-between mb-1.5">
                    <span class="fw-bold text-uppercase" style="font-size: 0.65rem; letter-spacing: 0.05em; color: #047857;">Cart</span>
                    <div class="rounded-3 d-flex align-items-center justify-content-center flex-shrink-0" 
                         style="width: 28px; height: 28px; background: #d1fae5; color: #10b981; font-size: 0.85rem;">
                        <i class="bi bi-bag-check-fill"></i>
                    </div>
                </div>
                <div class="d-flex align-items-baseline justify-content-between">
                    <h3 class="mb-0 fw-bolder font-monospace" style="font-size: clamp(1.2rem, 3vw, 1.45rem); color: #0f172a; letter-spacing: -0.02em;">{{ $stats['cart'] }}</h3>
                    <span class="text-muted small d-none d-sm-inline" style="font-size: 0.72rem;">Go to cart <i class="bi bi-arrow-right"></i></span>
                </div>
            </div>
        </a>
    </div>

    {{-- Reviews Written --}}
    <div class="col-6 col-md-3">
        <a href="{{ route('account.reviews') }}" class="card text-decoration-none h-100 border-0 shadow-xs rounded-4 kpi-card position-relative overflow-hidden" 
            style="background: #ffffff; border: 1px solid #e2e8f0 !important;">
            <div class="kpi-top-bar" style="background: linear-gradient(90deg, #f59e0b, #d97706);"></div>
            <div class="card-body p-2.5 p-sm-3 p-md-3.5">
                <div class="d-flex align-items-center justify-content-between mb-1.5">
                    <span class="fw-bold text-uppercase" style="font-size: 0.65rem; letter-spacing: 0.05em; color: #b45309;">Reviews</span>
                    <div class="rounded-3 d-flex align-items-center justify-content-center flex-shrink-0" 
                         style="width: 28px; height: 28px; background: #fef3c7; color: #f59e0b; font-size: 0.85rem;">
                        <i class="bi bi-star-fill"></i>
                    </div>
                </div>
                <div class="d-flex align-items-baseline justify-content-between">
                    <h3 class="mb-0 fw-bolder font-monospace" style="font-size: clamp(1.2rem, 3vw, 1.45rem); color: #0f172a; letter-spacing: -0.02em;">{{ $stats['reviews'] }}</h3>
                    <span class="text-muted small d-none d-sm-inline" style="font-size: 0.72rem;">Manage <i class="bi bi-arrow-right"></i></span>
                </div>
            </div>
        </a>
    </div>
</div>

<style>
.kpi-top-bar {
    height: 4px;
    width: 100%;
    position: absolute;
    top: 0;
    left: 0;
}
.kpi-card {
    transition: transform 0.2s ease, box-shadow 0.2s ease;
}
.kpi-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 10px 25px rgba(15, 23, 42, 0.08) !important;
}
</style>
