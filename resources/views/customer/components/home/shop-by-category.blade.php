@if($categories->isNotEmpty())
<section class="py-5 bg-white">
    <div class="container px-lg-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h3 class="fw-bold mb-0">Shop by Category</h3>
            <a href="{{ route('shop') }}" class="text-primary text-decoration-none fw-semibold">View All Categories <i class="bi bi-arrow-right small"></i></a>
        </div>
        <div class="row row-cols-2 row-cols-md-3 row-cols-lg-5 g-4 text-center">
            @php
                $icons = ['bi-laptop', 'bi-handbag', 'bi-house-door', 'bi-controller', 'bi-watch', 'bi-headphones', 'bi-camera', 'bi-phone', 'bi-gift', 'bi-bicycle'];
            @endphp
            @foreach($categories as $index => $category)
                <div class="col">
                    <a href="{{ route('category.products', $category->slug) }}" class="text-decoration-none text-dark group">
                        <div class="card h-100 border-0 bg-light bg-opacity-50 transition-all hover-translate-y rounded-4">
                            <div class="card-body py-4">
                                <div class="bg-white rounded-circle shadow-sm d-inline-flex align-items-center justify-content-center mb-3 transition-all group-hover-primary" style="width: 70px; height: 70px;">
                                    <i class="bi {{ $icons[$index % count($icons)] }} fs-2 text-secondary"></i>
                                </div>
                                <h6 class="card-title fw-bold mb-0">{{ $category->name }}</h6>
                            </div>
                        </div>
                    </a>
                </div>
            @endforeach
        </div>
    </div>
</section>

<style>
    .hover-translate-y:hover { transform: translateY(-8px); box-shadow: 0 10px 25px -5px rgba(0,0,0,0.1) !important; background: #fff !important; }
    .group:hover .group-hover-primary { background-color: var(--bs-primary) !important; }
    .group:hover .group-hover-primary i { color: #fff !important; }
</style>
@endif
