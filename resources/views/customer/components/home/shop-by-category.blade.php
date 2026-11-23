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

        <!-- 🚀 Smooth Touch-Swipeable & Auto-Moving Category Track (Mobile Swipe + Desktop Drag + Auto Glide) -->
        <div class="category-marquee-container position-relative py-1 py-md-2" id="categoryScrollContainer">
            <div class="category-marquee-wrapper" id="categoryScrollWrapper">
                {{-- Group 1 --}}
                <div class="category-marquee-group">
                    @foreach($categories as $index => $category)
                        @php 
                            $color = $colors[$index % count($colors)];
                            $hasImage = !empty($category->image) && file_exists(public_path('storage/' . $category->image));
                            $catLower = strtolower($category->name);
                            $catIcon = 'bi-grid';
                            if (str_contains($catLower, 'mobile') || str_contains($catLower, 'phone')) $catIcon = 'bi-phone';
                            elseif (str_contains($catLower, 'electr')) $catIcon = 'bi-laptop';
                            elseif (str_contains($catLower, 'fashion') || str_contains($catLower, 'cloth')) $catIcon = 'bi-bag-heart';
                            elseif (str_contains($catLower, 'kitchen') || str_contains($catLower, 'home')) $catIcon = 'bi-house';
                            elseif (str_contains($catLower, 'sport')) $catIcon = 'bi-trophy';
                            elseif (str_contains($catLower, 'grocer')) $catIcon = 'bi-basket';
                            elseif (str_contains($catLower, 'beaut')) $catIcon = 'bi-stars';
                            elseif (str_contains($catLower, 'book')) $catIcon = 'bi-book';
                        @endphp
                        <a href="{{ route('category.products', $category->slug) }}" class="text-decoration-none text-dark flex-shrink-0 d-flex flex-column align-items-center category-slider-item">
                            <div class="rounded-circle d-flex align-items-center justify-content-center mb-1.5 transition-all overflow-hidden category-avatar-box" 
                                 style="@if(!$hasImage) background: {{ $color['bg'] }}; color: {{ $color['text'] }}; @endif">
                                @if($hasImage)
                                    <img src="{{ asset('storage/' . $category->image) }}" alt="{{ $category->name }}" class="w-100 h-100 object-fit-cover" draggable="false">
                                @else
                                    <i class="bi {{ $catIcon }} category-avatar-icon text-white user-select-none"></i>
                                @endif
                            </div>
                            <span class="fw-semibold text-dark text-truncate w-100 text-center category-title-text">{{ $category->name }}</span>
                        </a>
                    @endforeach
                </div>

                {{-- Group 2 (Clone for infinite seamless loop) --}}
                <div class="category-marquee-group" aria-hidden="true">
                    @foreach($categories as $index => $category)
                        @php 
                            $color = $colors[$index % count($colors)];
                            $hasImage = !empty($category->image) && file_exists(public_path('storage/' . $category->image));
                            $catLower = strtolower($category->name);
                            $catIcon = 'bi-grid';
                            if (str_contains($catLower, 'mobile') || str_contains($catLower, 'phone')) $catIcon = 'bi-phone';
                            elseif (str_contains($catLower, 'electr')) $catIcon = 'bi-laptop';
                            elseif (str_contains($catLower, 'fashion') || str_contains($catLower, 'cloth')) $catIcon = 'bi-bag-heart';
                            elseif (str_contains($catLower, 'kitchen') || str_contains($catLower, 'home')) $catIcon = 'bi-house';
                            elseif (str_contains($catLower, 'sport')) $catIcon = 'bi-trophy';
                            elseif (str_contains($catLower, 'grocer')) $catIcon = 'bi-basket';
                            elseif (str_contains($catLower, 'beaut')) $catIcon = 'bi-stars';
                            elseif (str_contains($catLower, 'book')) $catIcon = 'bi-book';
                        @endphp
                        <a href="{{ route('category.products', $category->slug) }}" class="text-decoration-none text-dark flex-shrink-0 d-flex flex-column align-items-center category-slider-item" tabindex="-1">
                            <div class="rounded-circle d-flex align-items-center justify-content-center mb-1.5 transition-all overflow-hidden category-avatar-box" 
                                 style="@if(!$hasImage) background: {{ $color['bg'] }}; color: {{ $color['text'] }}; @endif">
                                @if($hasImage)
                                    <img src="{{ asset('storage/' . $category->image) }}" alt="{{ $category->name }}" class="w-100 h-100 object-fit-cover" draggable="false">
                                @else
                                    <i class="bi {{ $catIcon }} category-avatar-icon text-white user-select-none"></i>
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
/* Continuous Smooth Auto-Slider with Full Native Swipe & Touch Drag Support */
.category-marquee-container {
    overflow-x: auto !important;
    overflow-y: hidden !important;
    -webkit-overflow-scrolling: touch !important;
    scrollbar-width: none !important;
    -ms-overflow-style: none !important;
    touch-action: pan-x pan-y !important;
    mask-image: linear-gradient(to right, transparent 0%, black 3%, black 97%, transparent 100%);
    -webkit-mask-image: linear-gradient(to right, transparent 0%, black 3%, black 97%, transparent 100%);
    cursor: grab;
    scroll-behavior: auto;
}
.category-marquee-container::-webkit-scrollbar {
    display: none !important;
    width: 0 !important;
    height: 0 !important;
}
.category-marquee-wrapper {
    display: flex;
    width: max-content;
    user-select: none;
    -webkit-user-select: none;
}
.category-marquee-group {
    display: flex;
    align-items: center;
    gap: 1rem;
    padding-right: 1rem;
    flex-shrink: 0;
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
.category-avatar-initial,
.category-avatar-icon {
    font-size: 1.45rem;
    color: #ffffff;
    display: inline-flex;
    align-items: center;
    justify-content: center;
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
    .category-avatar-initial,
    .category-avatar-icon {
        font-size: 2.1rem;
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

<script>
document.addEventListener('DOMContentLoaded', function() {
    const container = document.getElementById('categoryScrollContainer');
    if (!container) return;

    let isDown = false;
    let startX = 0;
    let scrollStart = 0;
    let isInteracting = false;
    let resumeTimeout = null;
    let animId = null;
    const scrollSpeed = 0.65; // Smooth pixels per frame

    function autoGlide() {
        if (!isInteracting && container) {
            container.scrollLeft += scrollSpeed;
            // Seamless infinite wrap: when half of content has scrolled past, reset smoothly
            const half = container.scrollWidth / 2;
            if (half > 0 && container.scrollLeft >= half) {
                container.scrollLeft -= half;
            }
        }
        animId = requestAnimationFrame(autoGlide);
    }

    function pause() {
        isInteracting = true;
        if (resumeTimeout) clearTimeout(resumeTimeout);
    }

    function resume(delay = 1800) {
        if (resumeTimeout) clearTimeout(resumeTimeout);
        resumeTimeout = setTimeout(() => {
            isInteracting = false;
        }, delay);
    }

    // Touch events for Mobile Web (swiping with hand/finger)
    container.addEventListener('touchstart', function() {
        pause();
    }, { passive: true });

    container.addEventListener('touchend', function() {
        resume(2000);
    }, { passive: true });

    container.addEventListener('touchcancel', function() {
        resume(2000);
    }, { passive: true });

    // Mouse drag for Desktop
    container.addEventListener('mousedown', function(e) {
        isDown = true;
        pause();
        container.style.cursor = 'grabbing';
        startX = e.pageX - container.offsetLeft;
        scrollStart = container.scrollLeft;
    });

    window.addEventListener('mouseup', function() {
        if (isDown) {
            isDown = false;
            if (container) container.style.cursor = 'grab';
            resume(2000);
        }
    });

    container.addEventListener('mousemove', function(e) {
        if (!isDown) return;
        e.preventDefault();
        const x = e.pageX - container.offsetLeft;
        const walk = (x - startX) * 1.5;
        container.scrollLeft = scrollStart - walk;
    });

    container.addEventListener('mouseenter', pause);
    container.addEventListener('mouseleave', function() {
        resume(1200);
    });

    animId = requestAnimationFrame(autoGlide);
});
</script>
@endif
