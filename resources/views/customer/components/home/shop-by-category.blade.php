@if($categories->isNotEmpty())
<section class="py-4 py-md-5 bg-white border-bottom">
    <div class="container px-3 px-md-4">
        <div class="d-flex justify-content-between align-items-center mb-3 mb-md-4">
            <div>
                <div class="mb-1 d-none d-md-block">
                    <span class="badge rounded-pill px-3 py-1.5 fw-bold text-uppercase d-inline-flex align-items-center gap-1" style="background: rgba(99,102,241,0.12); color: #6366f1; font-size: 0.72rem; letter-spacing: 0.05em;">
                        <i class="bi bi-grid-fill me-1" style="font-size: 0.75rem;"></i> Explore Catalog
                    </span>
                </div>
                <h4 class="fw-bolder mb-0 text-dark" style="letter-spacing: -0.02em; font-size: clamp(1.1rem, 2.5vw, 1.6rem);">Shop by Category</h4>
            </div>
            <a href="{{ route('categories.index') }}" class="btn btn-sm btn-light border rounded-pill px-3 py-1.5 fw-semibold text-primary" style="font-size: 0.8rem;">
                <span>All Categories</span> <i class="bi bi-arrow-right ms-1"></i>
            </a>
        </div>

        @php
            $icons = ['bi-laptop', 'bi-handbag', 'bi-house-door', 'bi-controller', 'bi-watch', 'bi-headphones', 'bi-camera', 'bi-phone', 'bi-gift', 'bi-bicycle'];
            $colors = [
                ['bg' => '#ede9fe', 'text' => '#6366f1'],
                ['bg' => '#fce7f3', 'text' => '#ec4899'],
                ['bg' => '#dbeafe', 'text' => '#3b82f6'],
                ['bg' => '#d1fae5', 'text' => '#10b981'],
                ['bg' => '#fef3c7', 'text' => '#f59e0b'],
                ['bg' => '#cffafe', 'text' => '#06b6d4'],
                ['bg' => '#fee2e2', 'text' => '#ef4444'],
                ['bg' => '#f3e8ff', 'text' => '#a855f7'],
                ['bg' => '#e0e7ff', 'text' => '#4f46e5'],
                ['bg' => '#ccfbf1', 'text' => '#14b8a6'],
            ];
        @endphp

        <!-- Mobile Horizontal Story Scroll Bar (Touch Native) -->
        <div class="d-flex d-md-none overflow-x-auto gap-3 pb-2 no-scrollbar px-1 text-center" style="scroll-snap-type: x mandatory;">
            @foreach($categories as $index => $category)
                @php $color = $colors[$index % count($colors)]; @endphp
                <a href="{{ route('category.products', $category->slug) }}" class="text-decoration-none text-dark flex-shrink-0 d-flex flex-column align-items-center" style="width: 76px; scroll-snap-align: start;">
                    <div class="rounded-circle d-flex align-items-center justify-content-center mb-1.5 shadow-xs transition-all" 
                         style="width: 58px; height: 58px; background: {{ $color['bg'] }}; color: {{ $color['text'] }}; border: 2px solid #ffffff;">
                        <i class="bi {{ $icons[$index % count($icons)] }} fs-3"></i>
                    </div>
                    <span class="fw-bold text-dark text-truncate w-100" style="font-size: 0.74rem; line-height: 1.2;">{{ $category->name }}</span>
                </a>
            @endforeach
        </div>

        <!-- Desktop Grid View -->
        <div class="row row-cols-md-3 row-cols-lg-5 g-3 g-md-4 text-center d-none d-md-flex">
            @foreach($categories as $index => $category)
                @php $color = $colors[$index % count($colors)]; @endphp
                <div class="col">
                    <a href="{{ route('category.products', $category->slug) }}" class="text-decoration-none text-dark group category-pill-link">
                        <div class="card h-100 border-0 rounded-4 p-3 transition-all category-card shadow-xs" 
                             style="background: #f8fafc; border: 1px solid #f1f5f9 !important;">
                            <div class="card-body p-2 d-flex flex-column align-items-center justify-content-center">
                                <div class="rounded-circle d-flex align-items-center justify-content-center mb-3 transition-all category-icon-circle shadow-xs" 
                                     style="width: 68px; height: 68px; background: {{ $color['bg'] }}; color: {{ $color['text'] }};">
                                    <i class="bi {{ $icons[$index % count($icons)] }} fs-2"></i>
                                </div>
                                <h6 class="card-title fw-bold mb-1 text-dark text-truncate w-100" style="font-size: 0.92rem;">{{ $category->name }}</h6>
                                <span class="text-muted small" style="font-size: 0.74rem;">Explore Collection →</span>
                            </div>
                        </div>
                    </a>
                </div>
            @endforeach
        </div>
    </div>
</section>

<style>
.category-card {
    transition: transform 0.2s ease, box-shadow 0.2s ease, background-color 0.2s ease;
}
.category-pill-link:hover .category-card {
    transform: translateY(-5px);
    background: #ffffff !important;
    border-color: #cbd5e1 !important;
    box-shadow: 0 12px 28px rgba(15, 23, 42, 0.08) !important;
}
.category-pill-link:hover .category-icon-circle {
    transform: scale(1.08);
}
</style>
@endif
