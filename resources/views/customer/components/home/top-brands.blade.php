@if($brands->isNotEmpty())
<section class="py-4 py-md-5" style="background: #f8fafc;">
    <div class="container px-3 px-md-4">
        <div class="text-center mb-3 mb-md-4">
            <div class="mb-1 d-none d-md-block">
                <span class="badge rounded-pill px-3 py-1.5 fw-bold text-uppercase d-inline-flex align-items-center gap-1 shadow-xs" 
                      style="background: rgba(99,102,241,0.12); color: #6366f1; font-size: 0.75rem; letter-spacing: 0.06em;">
                    <i class="bi bi-patch-check-fill" style="font-size: 0.8rem;"></i> Trusted Partners
                </span>
            </div>
            <h4 class="fw-bolder mb-1 text-dark" style="letter-spacing: -0.02em; font-size: clamp(1.15rem, 2.5vw, 1.6rem);">Shop by Popular Brands</h4>
            <p class="text-muted small mb-0 d-none d-md-block" style="font-size: 0.84rem; max-width: 520px; margin: 0 auto;">Discover authentic products from the world's most trusted manufacturers</p>
        </div>
        <div class="row row-cols-3 row-cols-md-4 row-cols-lg-6 g-2 g-md-3 align-items-center justify-content-center text-center">
            @foreach($brands as $brand)
                <div class="col">
                    <a href="{{ route('brand.products', $brand->slug) }}" class="brand-item group text-decoration-none d-block h-100">
                        <div class="p-2.5 p-md-3 border-0 rounded-4 transition-all brand-card shadow-xs d-flex align-items-center justify-content-center"
                             style="background: #ffffff; border: 1px solid #e2e8f0 !important; min-height: 64px; transition: all 0.2s ease;">
                            @if($brand->logo)
                                <img src="{{ asset('storage/' . $brand->logo) }}" alt="{{ $brand->name }}" class="img-fluid brand-logo-home" style="max-height: 32px; object-fit: contain;">
                            @else
                                <span class="fw-bolder text-dark small" style="font-size: 0.76rem; letter-spacing: 0.05em;">{{ strtoupper($brand->name) }}</span>
                            @endif
                        </div>
                    </a>
                </div>
            @endforeach
        </div>
    </div>
</section>

<style>
.brand-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 10px 24px rgba(15, 23, 42, 0.08) !important;
    border-color: #cbd5e1 !important;
}
</style>
@endif
