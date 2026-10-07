@extends('layouts.customer')

@section('title', 'Explore All Categories — ' . \App\Models\Setting::get('store_name', 'ShopCalm'))

@section('content')

<div class="container-fluid my-3 my-md-4">
    {{-- Breadcrumb --}}
    <nav aria-label="breadcrumb" class="mb-3">
        <ol class="breadcrumb small mb-0">
            <li class="breadcrumb-item"><a href="{{ route('home') }}" class="text-decoration-none text-muted">Home</a></li>
            <li class="breadcrumb-item active text-primary fw-semibold" aria-current="page">Categories</li>
        </ol>
    </nav>

    {{-- ── 1. Clean Header (Matching Home Screen) ──────────────── --}}
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 pb-3 mb-4 border-bottom">
        <div>
            <div class="d-flex align-items-center gap-2 mb-1">
                <span class="badge rounded-pill px-3 py-1 fw-bold text-uppercase d-inline-flex align-items-center gap-1 shadow-2xs" 
                      style="background: rgba(99,102,241,0.12); color: #6366f1; font-size: 0.72rem; letter-spacing: 0.05em;">
                    <i class="bi bi-grid-fill me-0.5"></i> Department Directory
                </span>
                <span class="badge bg-light text-secondary border rounded-pill px-2.5 py-1 small fw-semibold" style="font-size: 0.72rem;">
                    {{ $categories->count() }} Categories
                </span>
            </div>
            <h3 class="fw-extrabold mb-0 text-dark" style="letter-spacing: -0.02em;">Explore All Categories</h3>
            <p class="text-muted small mb-0 mt-0.5">Browse all curated departments and discover handpicked deals</p>
        </div>

        {{-- Realtime Category Filter Input --}}
        <div class="position-relative" style="min-width: 260px; max-width: 340px;">
            <i class="bi bi-search position-absolute top-50 start-0 translate-middle-y ms-3 text-muted"></i>
            <input type="text" id="catFilterInput" 
                   class="form-control rounded-pill ps-5 pe-4 py-2 shadow-xs border" 
                   placeholder="Search categories (e.g. Mobiles)..." 
                   onkeyup="filterCategories()"
                   style="border-color: #e2e8f0; font-size: 0.85rem;">
            <button type="button" id="clearSearchBtn" class="btn btn-link text-muted position-absolute top-50 end-0 translate-middle-y me-2 p-0 d-none" 
                    onclick="clearCatFilter()" style="font-size: 0.85rem; text-decoration: none;">
                <i class="bi bi-x-circle-fill"></i>
            </button>
        </div>
    </div>

    {{-- ── 2. Categories Grid ──────────────────────────────────── --}}
    @php
        $icons = ['bi-phone', 'bi-laptop', 'bi-handbag', 'bi-house-door', 'bi-controller', 'bi-watch', 'bi-headphones', 'bi-camera', 'bi-gift', 'bi-bicycle', 'bi-tv', 'bi-smartwatch'];
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

    <div class="row row-cols-2 row-cols-sm-3 row-cols-md-3 row-cols-lg-5 g-3 g-md-4" id="categoriesGrid">
        @forelse($categories as $index => $category)
            @php
                $color = $colors[$index % count($colors)];
                $icon = $icons[$index % count($icons)];
            @endphp
            <div class="col category-item-col" data-name="{{ strtolower($category->name) }}">
                <a href="{{ route('category.products', $category->slug) }}" class="text-decoration-none text-dark group category-pill-link h-100 d-block">
                    <div class="card h-100 border-0 rounded-4 p-3.5 text-center transition-all category-card shadow-xs d-flex flex-column" 
                         style="background: #f8fafc; border: 1px solid #e2e8f0 !important;">
                        <div class="card-body p-1 d-flex flex-column align-items-center justify-content-center h-100">
                            
                            {{-- Circular Icon Thumbnail --}}
                            <div class="rounded-circle d-flex align-items-center justify-content-center mb-3 transition-all category-icon-circle shadow-xs overflow-hidden" 
                                 style="width: 72px; height: 72px; min-width: 72px; background: {{ $color['bg'] }}; color: {{ $color['text'] }};">
                                @if($category->image)
                                    <img src="{{ asset('storage/' . $category->image) }}" alt="{{ $category->name }}" class="w-100 h-100 rounded-circle object-fit-cover">
                                @else
                                    <i class="bi {{ $icon }} fs-2"></i>
                                @endif
                            </div>

                            {{-- Name --}}
                            <h6 class="card-title fw-bolder mb-1 text-dark text-truncate w-100" style="font-size: 0.95rem; letter-spacing: -0.01em;">
                                {{ $category->name }}
                            </h6>

                            {{-- Product count & starting price badges --}}
                            <div class="d-flex align-items-center justify-content-center gap-1.5 mb-2.5 flex-wrap">
                                <span class="badge bg-white text-secondary border rounded-pill px-2.5 py-0.5 fw-semibold shadow-2xs" style="font-size: 0.7rem;">
                                    {{ $category->products_count }} {{ Str::plural('Item', $category->products_count) }}
                                </span>
                                @if($category->min_price > 0)
                                    <span class="badge rounded-pill px-2 py-0.5 fw-bold" style="background: #ecfdf5; color: #047857; border: 1px solid #a7f3d0; font-size: 0.7rem;">
                                        From ₹{{ number_format($category->min_price) }}
                                    </span>
                                @endif
                            </div>

                            {{-- Explore CTA --}}
                            <div class="mt-auto pt-2">
                                <span class="text-primary fw-bold small explore-cta-text" style="font-size: 0.78rem;">
                                    Explore Collection →
                                </span>
                            </div>
                        </div>
                    </div>
                </a>
            </div>
        @empty
            <div class="col-12 text-center py-5">
                <i class="bi bi-folder-x fs-1 text-muted"></i>
                <h4 class="mt-3 fw-bold text-dark">No Categories Found</h4>
                <p class="text-muted small">Categories will appear here once added in the Admin panel.</p>
            </div>
        @endforelse
    </div>

    {{-- Empty Search Results Notice --}}
    <div id="noMatchNotice" class="text-center py-5 d-none">
        <div class="rounded-circle bg-light d-inline-flex align-items-center justify-content-center mb-3" style="width: 64px; height: 64px;">
            <i class="bi bi-search text-muted fs-4"></i>
        </div>
        <h5 class="fw-bold text-dark mb-1">No Matching Categories</h5>
        <p class="text-muted small mb-3">Try searching for a different keyword or explore all departments.</p>
        <button class="btn btn-sm btn-outline-primary rounded-pill px-3.5 py-1.5 fw-semibold" onclick="clearCatFilter()">Reset Search</button>
    </div>
</div>

<style>
.category-card {
    transition: transform 0.25s ease, box-shadow 0.25s ease, background-color 0.25s ease, border-color 0.25s ease;
    padding: 1.5rem; /* increase inner spacing for touch targets */
}
.category-pill-link:hover .category-card {
    transform: translateY(-5px);
    background: #ffffff !important;
    border-color: #cbd5e1 !important;
    box-shadow: 0 12px 28px rgba(15, 23, 42, 0.09) !important;
}
.category-pill-link:hover .category-icon-circle {
    transform: scale(1.08);
}
.category-pill-link:hover .explore-cta-text {
    color: #4338ca !important;
    text-decoration: underline;
}
#catFilterInput::placeholder {
    color: rgba(255, 255, 255, 0.6);
}
#catFilterInput:focus {
    background: rgba(255, 255, 255, 0.2) !important;
    box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.35) !important;
}
/* Mobile specific adjustments */
@media (max-width: 576px) {
    .category-card {
        padding: 1rem;
        border: 1px solid #e2e8f0 !important;
    }
    .category-icon-circle {
        width: 56px !important;
        height: 56px !important;
        min-width: 56px !important;
        font-size: 1.5rem !important;
    }
    .category-card .card-title {
        font-size: 0.85rem !important;
    }
    .category-card .badge {
        font-size: 0.65rem !important;
    }
    .explore-cta-text {
        font-size: 0.75rem !important;
    }
    #catFilterInput {
        width: 100% !important;
        max-width: none !important;
    }
}
</style>

<script>
function filterCategories() {
    const input = document.getElementById('catFilterInput');
    const query = input.value.toLowerCase().trim();
    const clearBtn = document.getElementById('clearSearchBtn');
    const items = document.querySelectorAll('.category-item-col');
    let visibleCount = 0;

    if (query.length > 0) {
        clearBtn.classList.remove('d-none');
    } else {
        clearBtn.classList.add('d-none');
    }

    items.forEach(item => {
        const name = item.getAttribute('data-name');
        if (!query || name.includes(query)) {
            item.classList.remove('d-none');
            visibleCount++;
        } else {
            item.classList.add('d-none');
        }
    });

    const notice = document.getElementById('noMatchNotice');
    if (visibleCount === 0 && items.length > 0) {
        notice.classList.remove('d-none');
    } else {
        notice.classList.add('d-none');
    }
}

function clearCatFilter() {
    const input = document.getElementById('catFilterInput');
    input.value = '';
    filterCategories();
    input.focus();
}
</script>
@endsection
