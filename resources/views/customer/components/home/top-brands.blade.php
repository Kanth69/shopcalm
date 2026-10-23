@if($brands->isNotEmpty())
<section class="py-4 py-md-5" style="background: #f8fafc;">
    <div class="container px-3 px-md-4">
        {{-- Section Header --}}
        <div class="mb-3 mb-md-4">
            <div class="mb-1 d-none d-md-block">
                <span class="badge rounded-pill px-3 py-1.5 fw-bold text-uppercase d-inline-flex align-items-center gap-1 shadow-xs" 
                      style="background: rgba(99,102,241,0.12); color: #6366f1; font-size: 0.72rem; letter-spacing: 0.05em;">
                    <i class="bi bi-patch-check-fill" style="font-size: 0.78rem;"></i> Trusted Partners
                </span>
            </div>
            <h4 class="fw-bolder mb-0 text-dark" style="letter-spacing: -0.02em; font-size: clamp(1.1rem, 2.5vw, 1.6rem);">Shop by Popular Brands</h4>
        </div>

        {{-- Top 6 Brands Grid (Desktop: 1 row of 6, Mobile: 2 rows of 3) --}}
        <div class="row row-cols-3 row-cols-md-3 row-cols-lg-6 g-2 g-md-3 align-items-center justify-content-center text-center">
            @foreach($brands->take(6) as $brand)
                <div class="col">
                    <a href="{{ route('brand.products', $brand->slug) }}" class="brand-item group text-decoration-none d-block h-100">
                        <div class="p-2 p-md-2.5 border-0 rounded-4 transition-all brand-card shadow-xs d-flex align-items-center justify-content-center"
                             style="background: #ffffff; border: 1px solid #e2e8f0 !important; min-height: 68px; height: 100%; transition: all 0.2s ease;">
                            @if($brand->logo)
                                <img src="{{ asset('storage/' . $brand->logo) }}" alt="{{ $brand->name }}" class="img-fluid brand-logo-home" style="max-height: 48px; max-width: 88%; width: auto; object-fit: contain; transition: transform 0.2s ease;">
                            @else
                                <span class="fw-bolder text-dark small text-truncate px-1" style="font-size: 0.8rem; letter-spacing: 0.04em;">{{ strtoupper($brand->name) }}</span>
                            @endif
                        </div>
                    </a>
                </div>
            @endforeach
        </div>

        {{-- Bottom View All Button --}}
        <div class="text-center mt-3 mt-md-3.5">
            <a href="{{ route('brands.index') }}" class="btn btn-outline-dark btn-sm rounded-pill px-3.5 py-1.5 fw-semibold shadow-xs" style="font-size: 0.78rem; background: #ffffff;">
                <span>View All Brands ({{ $totalBrandsCount ?? count($brands) }})</span> <i class="bi bi-arrow-right ms-1"></i>
            </a>
        </div>
    </div>
</section>

<style>
.brand-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 10px 24px rgba(15, 23, 42, 0.08) !important;
    border-color: #cbd5e1 !important;
}
.brand-card:hover .brand-logo-home {
    transform: scale(1.05);
}

@media (max-width: 575.98px) {
    .brand-card {
        min-height: 56px !important;
        padding: 0.4rem 0.5rem !important;
        border-radius: 12px !important;
    }
    .brand-logo-home {
        max-height: 38px !important;
        max-width: 90% !important;
    }
}
</style>
@endif
