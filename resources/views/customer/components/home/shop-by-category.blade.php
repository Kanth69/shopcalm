@if($categories->isNotEmpty())
<section class="py-3 py-md-4 bg-white border-bottom">
    <div class="container px-3 px-md-4">
        <div class="d-flex justify-content-between align-items-center mb-2.5 mb-md-3">
            <div>
                <div class="mb-1 d-none d-md-block">
                    <span class="badge rounded-pill px-3 py-1.5 fw-bold text-uppercase d-inline-flex align-items-center gap-1 shadow-xs" style="background: rgba(99,102,241,0.12); color: #6366f1; font-size: 0.72rem; letter-spacing: 0.05em;">
                        <i class="bi bi-grid-fill me-1" style="font-size: 0.75rem;"></i> Explore Catalog
                    </span>
                </div>
                <h4 class="fw-bolder mb-0 text-dark" style="letter-spacing: -0.02em; font-size: clamp(1.1rem, 2.5vw, 1.55rem);">Shop by Category</h4>
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

        <!-- 🚀 Unified Smooth Circular Category Moving Auto-Slider (Mobile + Tablet + Laptop) -->
        <div class="category-marquee-container position-relative overflow-hidden py-1 py-md-2">
            <div class="category-marquee-wrapper">
                {{-- Group 1 --}}
                <div class="category-marquee-group">
                    @foreach($categories as $index => $category)
                        @php 
                            $color = $colors[$index % count($colors)];
                            $hasImage = !empty($category->image) && file_exists(public_path('storage/' . $category->image));
                            $initial = strtoupper(mb_substr($category->name, 0, 1));
                        @endphp
                        <a href="{{ route('category.products', $category->slug) }}" class="text-decoration-none text-dark flex-shrink-0 d-flex flex-column align-items-center category-slider-item">
                            <div class="rounded-circle d-flex align-items-center justify-content-center mb-1.5 transition-all overflow-hidden category-avatar-box" 
                                 style="@if(!$hasImage) background: {{ $color['bg'] }}; color: {{ $color['text'] }}; @endif">
                                @if($hasImage)
                                    <img src="{{ asset('storage/' . $category->image) }}" alt="{{ $category->name }}" class="w-100 h-100 object-fit-cover">
                                @else
                                    <span class="fw-bold category-avatar-initial user-select-none">{{ $initial }}</span>
                                @endif
                            </div>
                            <span class="fw-semibold text-dark text-truncate w-100 text-center category-title-text">{{ $category->name }}</span>
                        </a>
                    @endforeach
                </div>

                {{-- Group 2 (Infinite clone for seamless loop) --}}
                <div class="category-marquee-group" aria-hidden="true">
                    @foreach($categories as $index => $category)
                        @php 
                            $color = $colors[$index % count($colors)];
                            $hasImage = !empty($category->image) && file_exists(public_path('storage/' . $category->image));
                            $initial = strtoupper(mb_substr($category->name, 0, 1));
                        @endphp
                        <a href="{{ route('category.products', $category->slug) }}" class="text-decoration-none text-dark flex-shrink-0 d-flex flex-column align-items-center category-slider-item" tabindex="-1">
                            <div class="rounded-circle d-flex align-items-center justify-content-center mb-1.5 transition-all overflow-hidden category-avatar-box" 
                                 style="@if(!$hasImage) background: {{ $color['bg'] }}; color: {{ $color['text'] }}; @endif">
                                @if($hasImage)
                                    <img src="{{ asset('storage/' . $category->image) }}" alt="{{ $category->name }}" class="w-100 h-100 object-fit-cover">
                                @else
                                    <span class="fw-bold category-avatar-initial user-select-none">{{ $initial }}</span>
                                @endif
                            </div>
                            <span class="fw-semibold text-dark text-truncate w-100 text-center category-title-text">{{ $category->name }}</span>
                        </a>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</section>

<style>
/* Continuous Smooth Auto-Slider (Infinite Ticker) */
.category-marquee-container {
    mask-image: linear-gradient(to right, transparent 0%, black 4%, black 96%, transparent 100%);
    -webkit-mask-image: linear-gradient(to right, transparent 0%, black 4%, black 96%, transparent 100%);
}
.category-marquee-wrapper {
    display: flex;
    width: max-content;
    user-select: none;
}
.category-marquee-group {
    display: flex;
    align-items: center;
    gap: 1rem;
    padding-right: 1rem;
    flex-shrink: 0;
    will-change: transform;
    animation: categoryMarqueeSlider 24s linear infinite;
}

/* Pause on Hover so users can easily click on laptop & mobile */
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

/* Item Sizing & Styling - Default (Mobile) */
.category-slider-item {
    width: 76px;
    transition: transform 0.25s cubic-bezier(0.34, 1.56, 0.64, 1);
}
.category-avatar-box {
    width: 58px;
    height: 58px;
    border: 2.5px solid #ffffff;
    box-shadow: 0 4px 12px rgba(15, 23, 42, 0.08);
    transition: transform 0.25s cubic-bezier(0.34, 1.56, 0.64, 1), box-shadow 0.25s ease, border-color 0.25s ease;
}
.category-avatar-initial {
    font-size: 1.4rem;
    color: #ffffff;
}
.category-title-text {
    font-size: 0.72rem;
    line-height: 1.25;
    transition: color 0.2s ease;
}

/* 💻 Laptop & Desktop Enhancements (>= 768px) */
@media (min-width: 768px) {
    .category-marquee-group {
        gap: 1.75rem;
        padding-right: 1.75rem;
        animation-duration: 32s; /* Slower, ultra-smooth glide on laptop screens */
    }
    .category-slider-item {
        width: 108px;
        padding: 4px 0;
    }
    .category-avatar-box {
        width: 82px;
        height: 82px;
        border: 3.5px solid #ffffff;
        box-shadow: 0 6px 18px rgba(15, 23, 42, 0.09);
    }
    .category-avatar-initial {
        font-size: 2rem;
    }
    .category-title-text {
        font-size: 0.86rem;
        margin-top: 2px;
    }
    .category-slider-item:hover {
        transform: translateY(-6px);
    }
    .category-slider-item:hover .category-avatar-box {
        transform: scale(1.08);
        border-color: #6366f1;
        box-shadow: 0 12px 28px rgba(99, 102, 241, 0.28);
    }
    .category-slider-item:hover .category-title-text {
        color: #6366f1 !important;
        font-weight: 700 !important;
    }
}

/* Touch active feedback */
.category-slider-item:active .category-avatar-box {
    transform: scale(0.94);
}
</style>
@endif
