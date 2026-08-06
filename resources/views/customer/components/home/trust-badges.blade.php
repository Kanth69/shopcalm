<section class="trust-badges-bar bg-white border-top border-bottom py-4 my-4 shadow-sm">
    <div class="container">
        <div class="row g-3 text-center text-md-start">
            <div class="col-6 col-md-3">
                <div class="d-flex align-items-center gap-3 p-2">
                    <div class="bg-primary bg-opacity-10 text-primary p-3 rounded-circle d-flex align-items-center justify-content-center" style="width: 54px; height: 54px;">
                        <i class="bi bi-truck fs-3"></i>
                    </div>
                    <div>
                        <h6 class="fw-bold mb-0">Free Shipping</h6>
                        <small class="text-muted">On orders over ₹{{ $settings['free_shipping_min'] ?? '499' }}</small>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="d-flex align-items-center gap-3 p-2">
                    <div class="bg-success bg-opacity-10 text-success p-3 rounded-circle d-flex align-items-center justify-content-center" style="width: 54px; height: 54px;">
                        <i class="bi bi-shield-check fs-3"></i>
                    </div>
                    <div>
                        <h6 class="fw-bold mb-0">Secure Payment</h6>
                        <small class="text-muted">100% encrypted checkout</small>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="d-flex align-items-center gap-3 p-2">
                    <div class="bg-warning bg-opacity-10 text-warning p-3 rounded-circle d-flex align-items-center justify-content-center" style="width: 54px; height: 54px;">
                        <i class="bi bi-arrow-counterclockwise fs-3"></i>
                    </div>
                    <div>
                        <h6 class="fw-bold mb-0">Easy Returns</h6>
                        <small class="text-muted">7-day hassle free policy</small>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="d-flex align-items-center gap-3 p-2">
                    <div class="bg-info bg-opacity-10 text-info p-3 rounded-circle d-flex align-items-center justify-content-center" style="width: 54px; height: 54px;">
                        <i class="bi bi-headset fs-3"></i>
                    </div>
                    <div>
                        <h6 class="fw-bold mb-0">24/7 Support</h6>
                        <small class="text-muted">Dedicated live assistance</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
