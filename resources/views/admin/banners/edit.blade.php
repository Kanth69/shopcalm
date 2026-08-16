@extends('admin.layouts.app')

@section('header', 'Edit Banner')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item"><a href="{{ route('admin.banners.index') }}">Banners</a></li>
    <li class="breadcrumb-item active" aria-current="page">Edit #{{ $banner->id }}</li>
@endsection

@section('actions')
    <div class="btn-group gap-2">
        <a href="{{ route('admin.banners.index') }}" class="btn btn-outline-secondary rounded-pill px-3">
            <i class="bi bi-arrow-left me-1"></i> Back to Banners
        </a>
    </div>
@endsection

@section('content')
@php
    $currentType = old('banner_type', $banner->banner_type ?? 'GENERAL_PROMO');

    // Parse existing link_category_id / link_brand_id from stored URL
    $existingCatId = null;
    $existingBrandId = null;
    if ($banner->primary_button_link) {
        if (preg_match('/\?category=(\d+)/', $banner->primary_button_link, $m)) $existingCatId = $m[1];
        if (preg_match('/\?brand=(\d+)/', $banner->primary_button_link, $m)) $existingBrandId = $m[1];
    }
@endphp

<form action="{{ route('admin.banners.update', $banner) }}" method="POST" enctype="multipart/form-data" id="bannerForm">
    @csrf
    @method('PUT')

    <div class="row g-4">
        <!-- Left Column -->
        <div class="col-lg-8">
            <!-- 1. Banner Type Selection -->
            <div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-4">
                <div class="card-header bg-white py-3 border-bottom">
                    <h6 class="mb-0 fw-bold text-dark">
                        <i class="bi bi-ui-checks-grid text-primary me-2"></i>1. Banner Format & Purpose
                    </h6>
                </div>
                <div class="card-body p-4">
                    <div class="row g-3">
                        @php
                            $types = [
                                ['val'=>'GENERAL_PROMO',  'icon'=>'bi-image',       'label'=>'Promo Slider',       'desc'=>'Full-width homepage hero slide. Link to any page.'],
                                ['val'=>'CATEGORY_HEADER','icon'=>'bi-grid',        'label'=>'Category Banner',    'desc'=>'Highlights a specific product category.'],
                                ['val'=>'BRAND_PROMO',    'icon'=>'bi-award',       'label'=>'Brand Banner',       'desc'=>"Showcases a brand's collection."],
                                ['val'=>'CAMPAIGN_OFFER', 'icon'=>'bi-fire',        'label'=>'Mega Sale Banner',   'desc'=>'Linked to an active Sale offer campaign.'],
                            ];
                        @endphp
                        @foreach($types as $t)
                        <div class="col-6 col-md-3">
                            <input type="radio" class="btn-check banner-type-radio" name="banner_type" id="type_{{ $t['val'] }}" value="{{ $t['val'] }}" {{ $currentType === $t['val'] ? 'checked' : '' }} autocomplete="off">
                            <label class="btn btn-outline-primary w-100 h-100 text-start p-3 rounded-4 d-flex flex-column justify-content-between" for="type_{{ $t['val'] }}">
                                <div>
                                    <i class="bi {{ $t['icon'] }} fs-3 d-block mb-2"></i>
                                    <strong class="d-block text-dark small fw-bold">{{ $t['label'] }}</strong>
                                </div>
                                <small class="text-muted mt-2" style="font-size: 0.7rem; line-height: 1.3;">{{ $t['desc'] }}</small>
                            </label>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <!-- 2. Dynamic Destination Fields -->
            <div id="section-CATEGORY_HEADER" class="dynamic-section {{ $currentType !== 'CATEGORY_HEADER' ? 'd-none' : '' }} card border-0 shadow-sm rounded-4 overflow-hidden mb-4">
                <div class="card-header bg-white py-3 border-bottom">
                    <h6 class="mb-0 fw-bold text-dark"><i class="bi bi-folder-check text-primary me-2"></i>2. Select Target Category</h6>
                </div>
                <div class="card-body p-4">
                    <label class="form-label fw-bold text-dark small">Associated Category <span class="text-danger">*</span></label>
                    <select name="link_category_id" class="form-select">
                        <option value="">— Select Category —</option>
                        @foreach($categories as $cat)
                            <option value="{{ $cat->id }}" {{ (old('link_category_id', $existingCatId) == $cat->id) ? 'selected' : '' }}>{{ $cat->name }}</option>
                        @endforeach
                    </select>
                    <div class="form-text text-muted mt-2" style="font-size: 0.72rem;">Banner click will automatically redirect customers to <code>/shop?category=ID</code></div>
                </div>
            </div>

            <div id="section-BRAND_PROMO" class="dynamic-section {{ $currentType !== 'BRAND_PROMO' ? 'd-none' : '' }} card border-0 shadow-sm rounded-4 overflow-hidden mb-4">
                <div class="card-header bg-white py-3 border-bottom">
                    <h6 class="mb-0 fw-bold text-dark"><i class="bi bi-award-fill text-primary me-2"></i>2. Select Target Brand</h6>
                </div>
                <div class="card-body p-4">
                    <label class="form-label fw-bold text-dark small">Associated Brand <span class="text-danger">*</span></label>
                    <select name="link_brand_id" class="form-select">
                        <option value="">— Select Brand —</option>
                        @foreach($brands as $brand)
                            <option value="{{ $brand->id }}" {{ (old('link_brand_id', $existingBrandId) == $brand->id) ? 'selected' : '' }}>{{ $brand->name }}</option>
                        @endforeach
                    </select>
                    <div class="form-text text-muted mt-2" style="font-size: 0.72rem;">Banner click will automatically redirect customers to <code>/shop?brand=ID</code></div>
                </div>
            </div>

            <div id="section-CAMPAIGN_OFFER" class="dynamic-section {{ $currentType !== 'CAMPAIGN_OFFER' ? 'd-none' : '' }} card border-0 shadow-sm rounded-4 overflow-hidden mb-4">
                <div class="card-header bg-white py-3 border-bottom">
                    <h6 class="mb-0 fw-bold text-dark"><i class="bi bi-fire text-primary me-2"></i>2. Link to Mega Sale Campaign</h6>
                </div>
                <div class="card-body p-4">
                    <label class="form-label fw-bold text-dark small">Active Offer Campaign <span class="text-danger">*</span></label>
                    <select name="offer_id" id="offer_id_select" class="form-select">
                        <option value="" data-title="">— Select an Active Campaign —</option>
                        @foreach($offers as $offer)
                            <option value="{{ $offer->id }}" data-title="{{ $offer->title }}" {{ old('offer_id', $banner->offer_id) == $offer->id ? 'selected' : '' }}>
                                {{ $offer->title }} &middot; ({{ str_replace('_', ' ', $offer->type) }})
                            </option>
                        @endforeach
                    </select>
                    <div class="alert alert-primary border-0 rounded-3 mt-3 py-2 small" style="font-size: 0.75rem;">
                        <i class="bi bi-info-circle me-1"></i>
                        Clicking this hero slide will take customers directly to the curated sale storefront.
                    </div>
                </div>
            </div>

            <div id="section-GENERAL_PROMO" class="dynamic-section {{ $currentType !== 'GENERAL_PROMO' ? 'd-none' : '' }} card border-0 shadow-sm rounded-4 overflow-hidden mb-4">
                <div class="card-header bg-white py-3 border-bottom">
                    <h6 class="mb-0 fw-bold text-dark"><i class="bi bi-link-45deg text-primary me-2"></i>2. Destination URL</h6>
                </div>
                <div class="card-body p-4">
                    <label class="form-label fw-bold text-dark small">Target Page Link</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light text-muted border-end-0">URL</span>
                        <input type="text" name="primary_button_link" class="form-control border-start-0 ps-0" value="{{ old('primary_button_link', $banner->primary_button_link) }}" placeholder="/shop">
                    </div>
                    <div class="form-text text-muted" style="font-size: 0.72rem;">Relative URL path to any storefront page or promotional landing.</div>
                </div>
            </div>

            <!-- 3. Banner Text & Copy -->
            <div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-4">
                <div class="card-header bg-white py-3 border-bottom">
                    <h6 class="mb-0 fw-bold text-dark">
                        <i class="bi bi-fonts text-primary me-2"></i>3. Banner Content & Button
                    </h6>
                </div>
                <div class="card-body p-4">
                    <div class="row g-3">
                        <div class="col-md-6" id="title-row">
                            <label class="form-label fw-bold text-dark small">Main Headline <span class="text-danger" id="title-required-star">*</span></label>
                            <input type="text" name="title" id="banner-title-input" class="form-control @error('title') is-invalid @enderror" value="{{ old('title', $banner->title) }}">
                            <div class="form-text text-muted d-none" id="title-auto-note"><i class="bi bi-magic me-1 text-primary"></i>Auto-populated from campaign title</div>
                            @error('title') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-bold text-dark small">Subheadline / Tagline</label>
                            <input type="text" name="subtitle" class="form-control" value="{{ old('subtitle', $banner->subtitle) }}">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-bold text-dark small">Call to Action Button Label</label>
                            <input type="text" name="primary_button_text" class="form-control" value="{{ old('primary_button_text', $banner->primary_button_text) }}">
                        </div>
                    </div>
                </div>
            </div>

            <!-- 4. Background Media & Gradient -->
            <div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-4">
                <div class="card-header bg-white py-3 border-bottom">
                    <h6 class="mb-0 fw-bold text-dark">
                        <i class="bi bi-palette-fill text-primary me-2"></i>4. Background Artwork & Colors
                    </h6>
                </div>
                <div class="card-body p-4">
                    @php
                        preg_match('/#([0-9a-f]{6})/i', $banner->bg_gradient ?? '', $m);
                        $existingAccent = old('bg_color_accent', isset($m[0]) ? $m[0] : '#6366f1');
                    @endphp
                    <div class="row g-3 mb-4">
                        <div class="col-md-4">
                            <label class="form-label fw-bold text-dark small">Accent Color</label>
                            <input type="color" name="bg_color_accent" id="bg_color_accent" class="form-control form-control-color w-100 rounded-3" value="{{ $existingAccent }}" style="height: 44px; cursor: pointer;">
                        </div>
                        <div class="col-md-8">
                            <label class="form-label fw-bold text-dark small">Live Gradient Preview</label>
                            <div id="gradient-preview" class="rounded-3 border shadow-xs d-flex align-items-center justify-content-center text-white fw-bold px-3" style="height: 44px; font-size: 13px; background: {{ old('bg_gradient', $banner->bg_gradient ?? 'linear-gradient(135deg, #6366f1 0%, #0f172a 100%)') }};">
                                Banner Gradient Preview
                            </div>
                            <input type="hidden" name="bg_gradient" id="bg_gradient_hidden" value="{{ old('bg_gradient', $banner->bg_gradient ?? 'linear-gradient(135deg, #6366f1 0%, #0f172a 100%)') }}">
                        </div>
                    </div>

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold text-dark small">Desktop Hero Image</label>
                            <input type="file" name="desktop_image" class="form-control" accept="image/*" onchange="previewDesktopImage(this)">
                            @if($banner->desktop_image)
                                <img id="existingDesktopImg" src="{{ asset('storage/' . $banner->desktop_image) }}" class="img-fluid rounded-3 mt-2 border shadow-xs w-100" style="max-height: 90px; object-fit: cover;">
                            @endif
                            <img id="desktopPreviewImg" src="#" alt="Desktop Preview" class="img-fluid rounded-3 mt-2 d-none border shadow-xs" style="max-height: 90px; width: 100%; object-fit: cover;">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-bold text-dark small">Mobile Hero Image</label>
                            <input type="file" name="mobile_image" class="form-control" accept="image/*" onchange="previewMobileImage(this)">
                            @if($banner->mobile_image)
                                <img id="existingMobileImg" src="{{ asset('storage/' . $banner->mobile_image) }}" class="img-fluid rounded-3 mt-2 border shadow-xs" style="max-height: 90px; width: 90px; object-fit: cover;">
                            @endif
                            <img id="mobilePreviewImg" src="#" alt="Mobile Preview" class="img-fluid rounded-3 mt-2 d-none border shadow-xs" style="max-height: 90px; width: 90px; object-fit: cover;">
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Column: Settings & Actions -->
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-4">
                <div class="card-header bg-white py-3 border-bottom">
                    <h6 class="mb-0 fw-bold text-dark"><i class="bi bi-sliders text-primary me-2"></i>Display Settings</h6>
                </div>
                <div class="card-body p-4">
                    <div class="mb-3">
                        <label class="form-label fw-bold text-dark small">Carousel Display Order</label>
                        <input type="number" name="display_order" class="form-control" value="{{ old('display_order', $banner->display_order) }}" min="0">
                    </div>

                    <hr class="my-3">

                    <div class="form-check form-switch mb-0">
                        <input class="form-check-input" type="checkbox" name="is_active" id="is_active" value="1" {{ old('is_active', $banner->is_active) ? 'checked' : '' }}>
                        <label class="form-check-label fw-semibold text-dark small" for="is_active">Publish Banner (Active)</label>
                    </div>
                </div>
            </div>

            <div class="d-grid gap-2">
                <button type="submit" class="btn btn-primary rounded-pill btn-lg fw-bold shadow-sm">
                    <i class="bi bi-check-circle me-1"></i> Update Banner
                </button>
                <a href="{{ route('admin.banners.index') }}" class="btn btn-light rounded-pill border">Cancel</a>
            </div>
        </div>
    </div>
</form>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const radios = document.querySelectorAll('.banner-type-radio');
    const sections = document.querySelectorAll('.dynamic-section');
    const offerSelect = document.getElementById('offer_id_select');
    const titleInput = document.getElementById('banner-title-input');
    const titleNote = document.getElementById('title-auto-note');
    const titleStar = document.getElementById('title-required-star');

    function showSection(val) {
        sections.forEach(s => {
            if (s.id === 'section-' + val) {
                s.classList.remove('d-none');
            } else {
                s.classList.add('d-none');
            }
        });

        if (val === 'CAMPAIGN_OFFER') {
            syncTitleFromOffer();
            titleInput.readOnly = true;
            titleInput.classList.add('bg-light', 'text-muted');
            titleNote.classList.remove('d-none');
            titleStar.classList.add('d-none');
        } else {
            titleInput.readOnly = false;
            titleInput.classList.remove('bg-light', 'text-muted');
            titleNote.classList.add('d-none');
            titleStar.classList.remove('d-none');
        }
    }

    function syncTitleFromOffer() {
        if (!offerSelect) return;
        const selectedOption = offerSelect.options[offerSelect.selectedIndex];
        const offerTitle = selectedOption ? selectedOption.dataset.title : '';
        if (offerTitle) titleInput.value = offerTitle;
    }

    radios.forEach(radio => {
        radio.addEventListener('change', () => showSection(radio.value));
    });

    if (offerSelect) {
        offerSelect.addEventListener('change', function() {
            const currentType = document.querySelector('.banner-type-radio:checked')?.value;
            if (currentType === 'CAMPAIGN_OFFER') syncTitleFromOffer();
        });
    }

    // Live gradient preview
    const colorPicker = document.getElementById('bg_color_accent');
    const gradientPreview = document.getElementById('gradient-preview');
    const gradientHidden = document.getElementById('bg_gradient_hidden');

    function updateGradient(color) {
        const grad = `linear-gradient(135deg, ${color} 0%, #0f172a 100%)`;
        if (gradientPreview) gradientPreview.style.background = grad;
        if (gradientHidden) gradientHidden.value = grad;
    }

    if (colorPicker) {
        colorPicker.addEventListener('input', () => updateGradient(colorPicker.value));
    }
});

function previewDesktopImage(input) {
    const preview = document.getElementById('desktopPreviewImg');
    const existing = document.getElementById('existingDesktopImg');
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            preview.src = e.target.result;
            preview.classList.remove('d-none');
            if (existing) existing.style.display = 'none';
        };
        reader.readAsDataURL(input.files[0]);
    }
}

function previewMobileImage(input) {
    const preview = document.getElementById('mobilePreviewImg');
    const existing = document.getElementById('existingMobileImg');
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            preview.src = e.target.result;
            preview.classList.remove('d-none');
            if (existing) existing.style.display = 'none';
        };
        reader.readAsDataURL(input.files[0]);
    }
}
</script>
@endpush
@endsection
