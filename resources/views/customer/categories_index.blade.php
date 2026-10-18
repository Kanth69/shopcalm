@extends('layouts.customer')

@section('title', 'Categories — ' . \App\Models\Setting::get('store_name', 'ShopCalm'))

@section('content')

<div class="container-xl py-3 py-md-4 px-3 px-md-4">

    {{-- Clean Header (No duplicate categories text) --}}
    <div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-2.5 pb-3 mb-3 border-bottom">
        <div class="d-flex align-items-center gap-2">
            <a href="{{ route('home') }}" class="btn btn-sm btn-light border rounded-circle d-inline-flex align-items-center justify-content-center text-secondary me-1" style="width: 32px; height: 32px;" title="Back to Home">
                <i class="bi bi-arrow-left"></i>
            </a>
            <h4 class="fw-bold mb-0 text-dark d-flex align-items-center gap-2" style="font-size: clamp(1.2rem, 2.5vw, 1.5rem);">
                <span>Categories</span>
                <span class="badge bg-light text-secondary border rounded-pill px-2.5 py-0.5 fw-semibold" style="font-size: 0.75rem;">
                    {{ $categories->count() }}
                </span>
            </h4>
        </div>

        {{-- Realtime Category Filter Input --}}
        <div class="position-relative w-100" style="max-width: 320px;">
            <i class="bi bi-search position-absolute top-50 start-0 translate-middle-y ms-3 text-muted" style="font-size: 0.85rem;"></i>
            <input type="text" id="catFilterInput" 
                   class="form-control rounded-pill ps-5 pe-4 py-1.5 shadow-xs border bg-white" 
                   placeholder="Search categories..." 
                   onkeyup="filterCategories()"
                   style="border-color: #cbd5e1; font-size: 0.85rem;">
            <button type="button" id="clearSearchBtn" class="btn btn-link text-muted position-absolute top-50 end-0 translate-middle-y me-2 p-0 d-none" 
                    onclick="clearCatFilter()" style="font-size: 0.85rem; text-decoration: none;">
                <i class="bi bi-x-circle-fill"></i>
            </button>
        </div>
    </div>

    {{-- Categories Grid --}}
    @php
        $palette = [
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

    <div class="row row-cols-2 row-cols-sm-3 row-cols-md-4 row-cols-lg-5 g-3 g-md-4" id="categoriesGrid">
        @forelse($categories as $index => $category)
            @php
                $color = $palette[$index % count($palette)];
                $hasImage = !empty($category->image) && file_exists(public_path('storage/' . $category->image));
                $initial = strtoupper(mb_substr($category->name, 0, 1));
            @endphp
            <div class="col category-item-col" data-name="{{ strtolower($category->name) }}">
                <a href="{{ route('category.products', $category->slug) }}" class="text-decoration-none text-dark group category-pill-link h-100 d-block">
                    <div class="card h-100 border-0 rounded-4 text-center category-card shadow-xs d-flex flex-column" 
                         style="background: #ffffff; border: 1.5px solid #e2e8f0 !important;">
                        <div class="card-body p-3 p-md-3.5 d-flex flex-column align-items-center justify-content-center">
                            
                            {{-- Image OR Vibrant Color Circle --}}
                            <div class="rounded-circle d-flex align-items-center justify-content-center mb-2.5 transition-all category-avatar shadow-xs overflow-hidden" 
                                 style="width: 64px; height: 64px; min-width: 64px; @if(!$hasImage) background: {{ $color['bg'] }}; color: {{ $color['text'] }}; @endif">
                                @if($hasImage)
                                    <img src="{{ asset('storage/' . $category->image) }}" alt="{{ $category->name }}" class="w-100 h-100 object-fit-cover">
                                @else
                                    <span class="fw-bold fs-3 text-white user-select-none">{{ $initial }}</span>
                                @endif
                            </div>

                            {{-- Category Name --}}
                            <h6 class="card-title fw-bold mb-1 text-dark text-truncate w-100" style="font-size: 0.88rem;" title="{{ $category->name }}">
                                {{ $category->name }}
                            </h6>

                            {{-- Product Count --}}
                            <span class="text-muted small" style="font-size: 0.72rem;">
                                {{ $category->products_count }} {{ Str::plural('item', $category->products_count) }}
                            </span>
                        </div>
                    </div>
                </a>
            </div>
        @empty
            <div class="col-12 text-center py-5">
                <i class="bi bi-folder-x fs-1 text-muted"></i>
                <h5 class="mt-3 fw-bold text-dark">No Categories Found</h5>
                <p class="text-muted small">Categories will appear here once added.</p>
            </div>
        @endforelse
    </div>

    {{-- Empty Search Results Notice --}}
    <div id="noMatchNotice" class="text-center py-5 d-none">
        <div class="rounded-circle bg-light d-inline-flex align-items-center justify-content-center mb-3" style="width: 56px; height: 56px;">
            <i class="bi bi-search text-muted fs-4"></i>
        </div>
        <h6 class="fw-bold text-dark mb-1">No Matching Categories</h6>
        <p class="text-muted small mb-3">Try searching for a different keyword.</p>
        <button class="btn btn-sm btn-outline-primary rounded-pill px-3.5 py-1 fw-semibold" onclick="clearCatFilter()">Reset Search</button>
    </div>
</div>

<style>
.category-card {
    transition: transform 0.2s ease, box-shadow 0.2s ease, border-color 0.2s ease;
}
.category-pill-link:hover .category-card {
    transform: translateY(-4px);
    border-color: #6366f1 !important;
    box-shadow: 0 10px 24px rgba(99, 102, 241, 0.12) !important;
}
.category-pill-link:hover .category-avatar {
    transform: scale(1.06);
}
#catFilterInput::placeholder {
    color: #94a3b8;
}
#catFilterInput:focus {
    border-color: #6366f1 !important;
    box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.15) !important;
}
@media (max-width: 575.98px) {
    #categoriesGrid {
        --bs-gutter-x: 0.9rem !important;
        --bs-gutter-y: 0.9rem !important;
    }
    .category-avatar {
        width: 56px !important;
        height: 56px !important;
        min-width: 56px !important;
    }
    .category-avatar span {
        font-size: 1.35rem !important;
    }
    .category-card {
        padding: 0.85rem 0.6rem !important;
    }
    .category-card .card-title {
        font-size: 0.82rem !important;
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
