@if($categories->isNotEmpty())
<section class="py-3 py-md-5 bg-white border-bottom">
    <div class="container px-3 px-md-4">
        <div class="d-flex justify-content-between align-items-center mb-2.5 mb-md-4">
            <div>
                <div class="mb-1 d-none d-md-block">
                    <span class="badge rounded-pill px-3 py-1.5 fw-bold text-uppercase d-inline-flex align-items-center gap-1 shadow-xs" style="background: rgba(99,102,241,0.12); color: #6366f1; font-size: 0.72rem; letter-spacing: 0.05em;">
                        <i class="bi bi-grid-fill me-1" style="font-size: 0.75rem;"></i> Explore Catalog
                    </span>
                </div>
                <h4 class="fw-bolder mb-0 text-dark" style="letter-spacing: -0.02em; font-size: clamp(1.1rem, 2.5vw, 1.6rem);">Shop by Category</h4>
            </div>
            <a href="{{ route('categories.index') }}" class="btn btn-sm btn-light border rounded-pill px-3 py-1.5 fw-semibold text-primary shadow-xs" style="font-size: 0.8rem; background: #ffffff;">
                <span>All Categories</span> <i class="bi bi-arrow-right ms-1"></i>
            </a>
        </div>

        @php
            $colors = [
                ['bg' => 'linear-gradient(135deg, #6366f1 0%, #4338ca 100%)', 'text' => '#ffffff'],
                ['bg' => 'linear-gradient(135deg, #ec4899 0%, #be185d 100%)', 'text' => '#ffffff'],
                ['bg' => 'linear-gradient(135deg, #0ea5e9 0%, #0369a1 100%)', 'text' => '#ffffff'],
                ['bg' => 'linear-gradient(135deg, #10b981 0%, #047857 100%)', 'text' => '#ffffff'],
                ['bg' => 'linear-gradient(135deg, #f59e0b 0%, #b45309 100%)', 'text' => '#ffffff'],
                ['bg' => 'linear-gradient(135deg, #8b5cf6 0%, #6d28d9 100%)', 'text' => '#ffffff'],
                ['bg' => 'linear-gradient(135deg, #14b8a6 0%, #0f766e 100%)', 'text' => '#ffffff'],
                ['bg' => 'linear-gradient(135deg, #f97316 0%, #c2410c 100%)', 'text' => '#ffffff'],
                ['bg' => 'linear-gradient(135deg, #06b6d4 0%, #0e7490 100%)', 'text' => '#ffffff'],
                ['bg' => 'linear-gradient(135deg, #64748b 0%, #334155 100%)', 'text' => '#ffffff'],
            ];
        @endphp

        <!-- 📱 Mobile Continuous Auto-Slider / Infinite Ticker (< 768px) -->
        <div class="d-block d-md-none category-marquee-container position-relative overflow-hidden py-1">
            <div class="category-marquee-wrapper">
                <div class="category-marquee-group">
                    @foreach($categories as $index => $category)
                        @php 
                            $color = $colors[$index % count($colors)];
                            $hasImage = !empty($category->image) && file_exists(public_path('storage/' . $category->image));
                            $initial = strtoupper(mb_substr($category->name, 0, 1));
                        @endphp
                        <a href="{{ route('category.products', $category->slug) }}" class="text-decoration-none text-dark flex-shrink-0 d-flex flex-column align-items-center category-slider-item" style="width: 74px;">
                            <div class="rounded-circle d-flex align-items-center justify-content-center mb-1 shadow-xs transition-all overflow-hidden category-avatar-box" 
                                 style="width: 56px; height: 56px; @if(!$hasImage) background: {{ $color['bg'] }}; color: {{ $color['text'] }}; @endif border: 2px solid #ffffff; box-shadow: 0 2px 8px rgba(0,0,0,0.08);">
                                @if($hasImage)
                                    <img src="{{ asset('storage/' . $category->image) }}" alt="{{ $category->name }}" class="w-100 h-100 object-fit-cover">
                                @else
                                    <span class="fw-bold fs-4 text-white user-select-none">{{ $initial }}</span>
                                @endif
                            </div>
                            <span class="fw-semibold text-dark text-truncate w-100 text-center" style="font-size: 0.72rem; line-height: 1.2;">{{ $category->name }}</span>
                        </a>
                    @endforeach
                </div>
                <div class="category-marquee-group" aria-hidden="true">
                    @foreach($categories as $index => $category)
                        @php 
                            $color = $colors[$index % count($colors)];
                            $hasImage = !empty($category->image) && file_exists(public_path('storage/' . $category->image));
                            $initial = strtoupper(mb_substr($category->name, 0, 1));
                        @endphp
                        <a href="{{ route('category.products', $category->slug) }}" class="text-decoration-none text-dark flex-shrink-0 d-flex flex-column align-items-center category-slider-item" style="width: 74px;" tabindex="-1">
                            <div class="rounded-circle d-flex align-items-center justify-content-center mb-1 shadow-xs transition-all overflow-hidden category-avatar-box" 
                                 style="width: 56px; height: 56px; @if(!$hasImage) background: {{ $color['bg'] }}; color: {{ $color['text'] }}; @endif border: 2px solid #ffffff; box-shadow: 0 2px 8px rgba(0,0,0,0.08);">
                                @if($hasImage)
                                    <img src="{{ asset('storage/' . $category->image) }}" alt="{{ $category->name }}" class="w-100 h-100 object-fit-cover">
                                @else
                                    <span class="fw-bold fs-4 text-white user-select-none">{{ $initial }}</span>
                                @endif
                            </div>
                            <span class="fw-semibold text-dark text-truncate w-100 text-center" style="font-size: 0.72rem; line-height: 1.2;">{{ $category->name }}</span>
                        </a>
                    @endforeach
                </div>
            </div>
        </div>

        <!-- 💻 Desktop Grid View (>= 768px) -->
        <div class="row row-cols-md-3 row-cols-lg-5 g-3 g-md-4 text-center d-none d-md-flex">
            @foreach($categories as $index => $category)
                @php 
                    $color = $colors[$index % count($colors)];
                    $hasImage = !empty($category->image) && file_exists(public_path('storage/' . $category->image));
                    $initial = strtoupper(mb_substr($category->name, 0, 1));
                @endphp
                <div class="col">
                    <a href="{{ route('category.products', $category->slug) }}" class="text-decoration-none text-dark group category-pill-link">
                        <div class="card h-100 border-0 rounded-4 p-3 transition-all category-card shadow-xs" 
                             style="background: #f8fafc; border: 1px solid #f1f5f9 !important;">
                            <div class="card-body p-2 d-flex flex-column align-items-center justify-content-center">
                                <div class="rounded-circle d-flex align-items-center justify-content-center mb-2.5 transition-all category-icon-circle shadow-xs overflow-hidden" 
                                     style="width: 68px; height: 68px; @if(!$hasImage) background: {{ $color['bg'] }}; color: {{ $color['text'] }}; @endif">
                                    @if($hasImage)
                                        <img src="{{ asset('storage/' . $category->image) }}" alt="{{ $category->name }}" class="w-100 h-100 object-fit-cover">
                                    @else
                                        <span class="fw-bold fs-3 text-white user-select-none">{{ $initial }}</span>
                                    @endif
                                </div>
                                <h6 class="card-title fw-bold mb-0 text-dark text-truncate w-100" style="font-size: 0.92rem;">{{ $category->name }}</h6>
                            </div>
                        </div>
                    </a>
                </div>
            @endforeach
        </div>
    </div>
</section>

<style>
/* Continuous Smooth Auto-Slider (Infinite Ticker) */
.category-marquee-container {
    mask-image: linear-gradient(to right, transparent, black 6%, black 94%, transparent);
    -webkit-mask-image: linear-gradient(to right, transparent, black 6%, black 94%, transparent);
}
.category-marquee-wrapper {
    display: flex;
    width: max-content;
}
.category-marquee-group {
    display: flex;
    align-items: center;
    gap: 0.85rem;
    padding-right: 0.85rem;
    flex-shrink: 0;
    will-change: transform;
    animation: categoryMarqueeSlider 22s linear infinite;
}
.category-marquee-wrapper:hover .category-marquee-group,
.category-marquee-wrapper:active .category-marquee-group {
    animation-play-state: paused;
}
@keyframes categoryMarqueeSlider {
    from {
        transform: translateX(0);
    }
    to {
        transform: translateX(-100%);
    }
}
.category-slider-item:active .category-avatar-box {
    transform: scale(0.94);
}

/* Desktop Grid Hover Styles */
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
