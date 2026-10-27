@extends('layouts.customer')

@section('title', 'All Brands — ' . \App\Models\Setting::get('store_name', 'ShopCalm'))

@section('content')
<div class="container-xl py-3 py-md-4 px-3 px-md-4">
    {{-- Header with Back Arrow, Title, Count Badge and Realtime Search Input --}}
    <div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-2.5 pb-3 mb-3 border-bottom">
        <div class="d-flex align-items-center gap-2">
            <a href="{{ route('home') }}" class="btn btn-sm btn-light border rounded-circle d-inline-flex align-items-center justify-content-center text-secondary me-1" style="width: 32px; height: 32px;" title="Back to Home">
                <i class="bi bi-arrow-left"></i>
            </a>
            <div>
                <h4 class="fw-bold mb-0 text-dark d-flex align-items-center gap-2" style="font-size: clamp(1.2rem, 2.5vw, 1.5rem);">
                    <i class="bi bi-award-fill text-primary"></i>
                    <span>Top Brands</span>
                    <span class="badge bg-light text-secondary border rounded-pill px-2.5 py-0.5 fw-semibold" style="font-size: 0.75rem;">
                        {{ $brands->count() }}
                    </span>
                </h4>
            </div>
        </div>

        {{-- Realtime Brand Filter Input --}}
        <div class="position-relative w-100" style="max-width: 320px;">
            <i class="bi bi-search position-absolute top-50 start-0 translate-middle-y ms-3 text-muted" style="font-size: 0.85rem;"></i>
            <input type="text" id="brandFilterInput" 
                   class="form-control rounded-pill ps-5 pe-4 py-1.5 shadow-xs border bg-white" 
                   placeholder="Search brands..." 
                   onkeyup="filterBrands()"
                   style="border-color: #cbd5e1; font-size: 0.85rem;"
                   autocomplete="off">
            <button type="button" id="clearBrandSearchBtn" class="btn btn-link text-muted position-absolute top-50 end-0 translate-middle-y me-2 p-0 d-none" 
                    onclick="clearBrandFilter()" style="font-size: 0.85rem; text-decoration: none;">
                <i class="bi bi-x-circle-fill"></i>
            </button>
        </div>
    </div>

    {{-- Brands Grid --}}
    <div class="row g-3 g-md-4" id="brandsGrid">
        @forelse($brands as $brand)
        <div class="col-6 col-md-4 col-lg-3 brand-item-col" data-name="{{ strtolower($brand->name) }}">
            <a href="{{ route('brand.products', $brand->slug) }}" class="text-decoration-none text-dark d-block h-100">
                <div class="card h-100 border-0 shadow-xs rounded-4 overflow-hidden text-center p-3 p-md-4 brand-card transition-all" style="background: #ffffff; border: 1.5px solid #e2e8f0 !important;">
                    @if($brand->logo)
                        <div class="mb-2.5 d-flex align-items-center justify-content-center" style="height: 54px;">
                            <img src="{{ asset('storage/' . $brand->logo) }}" alt="{{ $brand->name }}" class="img-fluid" style="max-height: 48px; max-width: 100%; object-fit: contain;">
                        </div>
                    @else
                        <div class="bg-primary-subtle text-primary rounded-circle d-inline-flex align-items-center justify-content-center mb-2.5 mx-auto" style="width: 54px; height: 54px;">
                            <i class="bi bi-patch-check-fill fs-3"></i>
                        </div>
                    @endif
                    <h6 class="fw-bold mb-1 text-truncate text-dark" style="font-size: 0.92rem;" title="{{ $brand->name }}">{{ $brand->name }}</h6>
                    <span class="badge bg-light text-secondary border rounded-pill px-2.5 py-0.5" style="font-size: 0.72rem;">{{ $brand->products_count ?? 0 }} {{ Str::plural('Product', (int) ($brand->products_count ?? 0)) }}</span>
                </div>
            </a>
        </div>
        @empty
        <div class="col-12 text-center py-5">
            <i class="bi bi-patch-exclamation fs-1 text-muted"></i>
            <h4 class="mt-3 fw-bold text-dark">No Brands Found</h4>
            <p class="text-muted small">Brands will appear here once added.</p>
        </div>
        @endforelse
    </div>

    {{-- Empty Search Results Notice --}}
    <div id="noBrandMatchNotice" class="text-center py-5 d-none">
        <div class="rounded-circle bg-light d-inline-flex align-items-center justify-content-center mb-3" style="width: 56px; height: 56px;">
            <i class="bi bi-search text-muted fs-4"></i>
        </div>
        <h6 class="fw-bold text-dark mb-1">No Matching Brands</h6>
        <p class="text-muted small mb-3">Try searching for a different brand name.</p>
        <button class="btn btn-sm btn-outline-primary rounded-pill px-3.5 py-1 fw-semibold" onclick="clearBrandFilter()">Reset Search</button>
    </div>
</div>

<style>
.brand-card {
    transition: transform 0.2s ease, box-shadow 0.2s ease, border-color 0.2s ease;
}
.brand-card:hover {
    transform: translateY(-4px);
    border-color: #6366f1 !important;
    box-shadow: 0 10px 24px rgba(99, 102, 241, 0.12) !important;
}
#brandFilterInput::placeholder {
    color: #94a3b8;
}
#brandFilterInput:focus {
    border-color: #6366f1 !important;
    box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.15) !important;
}
</style>

<script>
window.onHeaderSearchInput = function(query) {
    const inPageInput = document.getElementById('brandFilterInput');
    if (inPageInput && inPageInput.value !== query) {
        inPageInput.value = query;
    }
    filterBrands(query);
    return true;
};

window.onHeaderSearchSubmit = function(query) {
    filterBrands(query);
    return true;
};

function filterBrands(customQuery) {
    const input = document.getElementById('brandFilterInput');
    const query = (typeof customQuery === 'string' ? customQuery : (input ? input.value : '')).toLowerCase().trim();

    // Sync header inputs
    const mainInput = document.getElementById('mainSearchInput');
    const mobInput = document.getElementById('mobileSearchInput');
    if (mainInput && mainInput.value !== query) mainInput.value = query;
    if (mobInput && mobInput.value !== query) mobInput.value = query;

    const clearBtn = document.getElementById('clearBrandSearchBtn');
    const items = document.querySelectorAll('.brand-item-col');
    let visibleCount = 0;

    if (query.length > 0) {
        if (clearBtn) clearBtn.classList.remove('d-none');
    } else {
        if (clearBtn) clearBtn.classList.add('d-none');
    }

    items.forEach(item => {
        const name = (item.getAttribute('data-name') || '').toLowerCase();
        if (!query || name.includes(query)) {
            item.classList.remove('d-none');
            visibleCount++;
        } else {
            item.classList.add('d-none');
        }
    });

    const notice = document.getElementById('noBrandMatchNotice');
    if (notice) {
        if (visibleCount === 0 && items.length > 0) {
            notice.classList.remove('d-none');
        } else {
            notice.classList.add('d-none');
        }
    }
}

function clearBrandFilter() {
    const input = document.getElementById('brandFilterInput');
    if (input) input.value = '';
    const mainInput = document.getElementById('mainSearchInput');
    if (mainInput) mainInput.value = '';
    const mobInput = document.getElementById('mobileSearchInput');
    if (mobInput) mobInput.value = '';
    filterBrands('');
    if (input) input.focus();
}

document.addEventListener('DOMContentLoaded', function() {
    const urlParams = new URLSearchParams(window.location.search);
    const initialQ = urlParams.get('q') || urlParams.get('search');
    if (initialQ) {
        const inPageInput = document.getElementById('brandFilterInput');
        if (inPageInput) inPageInput.value = initialQ;
        const mainInput = document.getElementById('mainSearchInput');
        if (mainInput) mainInput.value = initialQ;
        const mobInput = document.getElementById('mobileSearchInput');
        if (mobInput) mobInput.value = initialQ;
        filterBrands(initialQ);
    }
});
</script>
@endsection
