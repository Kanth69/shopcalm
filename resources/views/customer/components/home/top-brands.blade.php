@if($brands->isNotEmpty())
<section class="py-5 bg-white">
    <div class="container">
        <h3 class="fw-bold text-center mb-5">Shop by Popular Brands</h3>
        <div class="row row-cols-2 row-cols-md-3 row-cols-lg-6 g-4 align-items-center justify-content-center text-center">
            @foreach($brands as $brand)
                <div class="col">
                    <a href="{{ route('brand.products', $brand->slug) }}" class="brand-item group text-decoration-none">
                        <div class="p-4 border rounded-4 transition-all hover-shadow bg-light bg-opacity-25 h-100 d-flex align-items-center justify-content-center">
                            @if($brand->logo)
                                <img src="{{ asset('storage/' . $brand->logo) }}" alt="{{ $brand->name }}" class="img-fluid brand-logo-home" style="max-height: 40px; filter: grayscale(1);">
                            @else
                                <span class="fw-bolder text-muted letter-spacing-tight">{{ strtoupper($brand->name) }}</span>
                            @endif
                        </div>
                    </a>
                </div>
            @endforeach
        </div>
    </div>
</section>

<style>
    .hover-shadow:hover { box-shadow: 0 10px 15px -3px rgba(0,0,0,0.1); background: #fff !important; transform: scale(1.02); }
    .brand-item:hover .brand-logo-home { filter: grayscale(0) !important; }
</style>
@endif
