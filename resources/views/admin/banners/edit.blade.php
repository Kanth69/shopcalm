@extends('admin.layouts.app')

@section('header', 'Edit Banner')

@section('actions')
    <a href="{{ route('admin.banners.index') }}" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left me-1"></i> Back to List
    </a>
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

<form action="{{ route('admin.banners.update', $banner) }}" method="POST" enctype="multipart/form-data">
    @csrf
    @method('PUT')

    <div class="row g-4">
        {{-- LEFT COLUMN --}}
        <div class="col-lg-8">

            {{-- Step 1: Banner Type --}}
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white py-3 border-bottom">
                    <h6 class="mb-0 fw-bold"><span class="text-primary me-2">①</span> Banner Type</h6>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        @php
                            $types = [
                                ['val'=>'GENERAL_PROMO',  'icon'=>'bi-image',  'label'=>'Promo Slider',     'desc'=>'Full-width homepage hero slide. Link to any page.'],
                                ['val'=>'CATEGORY_HEADER','icon'=>'bi-grid',   'label'=>'Category Banner',  'desc'=>'Highlights a specific product category.'],
                                ['val'=>'BRAND_PROMO',    'icon'=>'bi-award',  'label'=>'Brand Banner',     'desc'=>"Showcases a brand's collection."],
                                ['val'=>'CAMPAIGN_OFFER', 'icon'=>'bi-fire',   'label'=>'Mega Sale Banner', 'desc'=>'Linked to an active Sale offer campaign.'],
                            ];
                        @endphp
                        @foreach($types as $t)
                        <div class="col-6 col-md-3">
                            <input type="radio" class="btn-check banner-type-radio" name="banner_type" id="type_{{ $t['val'] }}" value="{{ $t['val'] }}" {{ $currentType === $t['val'] ? 'checked' : '' }} autocomplete="off">
                            <label class="btn btn-outline-primary w-100 h-100 text-start p-3 rounded-3" for="type_{{ $t['val'] }}">
                                <i class="bi {{ $t['icon'] }} fs-4 d-block mb-2 text-primary"></i>
                                <strong class="d-block fs-7">{{ $t['label'] }}</strong>
                                <small class="text-muted" style="font-size:.72rem;">{{ $t['desc'] }}</small>
                            </label>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>

            {{-- CATEGORY_HEADER: Category dropdown --}}
            <div id="section-CATEGORY_HEADER" class="dynamic-section {{ $currentType !== 'CATEGORY_HEADER' ? 'd-none' : '' }} card border-0 shadow-sm mb-4">
                <div class="card-header bg-white py-3 border-bottom">
                    <h6 class="mb-0 fw-bold"><span class="text-primary me-2">②</span> Select Category</h6>
                </div>
                <div class="card-body">
                    <label class="form-label fw-bold">Category <span class="text-danger">*</span></label>
                    <select name="link_category_id" class="form-select">
                        <option value="">— Select a Category —</option>
                        @foreach($categories as $cat)
                            <option value="{{ $cat->id }}" {{ (old('link_category_id', $existingCatId) == $cat->id) ? 'selected' : '' }}>{{ $cat->name }}</option>
                        @endforeach
                    </select>
                    <div class="form-text mt-2">Auto-redirects to <code>/shop?category=ID</code></div>
                </div>
            </div>

            {{-- BRAND_PROMO: Brand dropdown --}}
            <div id="section-BRAND_PROMO" class="dynamic-section {{ $currentType !== 'BRAND_PROMO' ? 'd-none' : '' }} card border-0 shadow-sm mb-4">
                <div class="card-header bg-white py-3 border-bottom">
                    <h6 class="mb-0 fw-bold"><span class="text-primary me-2">②</span> Select Brand</h6>
                </div>
                <div class="card-body">
                    <label class="form-label fw-bold">Brand <span class="text-danger">*</span></label>
                    <select name="link_brand_id" class="form-select">
                        <option value="">— Select a Brand —</option>
                        @foreach($brands as $brand)
                            <option value="{{ $brand->id }}" {{ (old('link_brand_id', $existingBrandId) == $brand->id) ? 'selected' : '' }}>{{ $brand->name }}</option>
                        @endforeach
                    </select>
                    <div class="form-text mt-2">Auto-redirects to <code>/shop?brand=ID</code></div>
                </div>
            </div>

            {{-- CAMPAIGN_OFFER: Offer dropdown --}}
            <div id="section-CAMPAIGN_OFFER" class="dynamic-section {{ $currentType !== 'CAMPAIGN_OFFER' ? 'd-none' : '' }} card border-0 shadow-sm mb-4">
                <div class="card-header bg-white py-3 border-bottom">
                    <h6 class="mb-0 fw-bold"><span class="text-primary me-2">②</span> Link to Sale Campaign</h6>
                </div>
                <div class="card-body">
                    <label class="form-label fw-bold">Offer / Sale Campaign <span class="text-danger">*</span></label>
                    <select name="offer_id" id="offer_id_select" class="form-select">
                        <option value="" data-title="">— Select an Offer —</option>
                        @foreach($offers as $offer)
                            <option value="{{ $offer->id }}" data-title="{{ $offer->title }}" {{ old('offer_id', $banner->offer_id) == $offer->id ? 'selected' : '' }}>
                                {{ $offer->title }} &middot; ({{ $offer->type }})
                            </option>
                        @endforeach
                    </select>
                    <div class="alert alert-info border-0 mt-3 py-2 small">
                        <i class="bi bi-info-circle me-2"></i>
                        Redirects customers to the <strong>Offers page</strong> filtered for this sale.
                    </div>
                </div>
            </div>

            {{-- GENERAL_PROMO: Manual URL --}}
            <div id="section-GENERAL_PROMO" class="dynamic-section {{ $currentType !== 'GENERAL_PROMO' ? 'd-none' : '' }} card border-0 shadow-sm mb-4">
                <div class="card-header bg-white py-3 border-bottom">
                    <h6 class="mb-0 fw-bold"><span class="text-primary me-2">②</span> Destination URL</h6>
                </div>
                <div class="card-body">
                    <label class="form-label fw-bold">Button Link URL</label>
                    <div class="input-group">
                        <span class="input-group-text text-muted">yourdomain.com</span>
                        <input type="text" name="primary_button_link" class="form-control" value="{{ old('primary_button_link', $banner->primary_button_link) }}" placeholder="/shop">
                    </div>
                </div>
            </div>

            {{-- Step 3: Content --}}
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white py-3 border-bottom">
                    <h6 class="mb-0 fw-bold"><span class="text-primary me-2">③</span> Banner Text & Button</h6>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6" id="title-row">
                            <label class="form-label fw-bold">Headline <span class="text-danger" id="title-required-star">*</span></label>
                            <input type="text" name="title" id="banner-title-input" class="form-control @error('title') is-invalid @enderror" value="{{ old('title', $banner->title) }}">
                            <div class="form-text text-muted d-none" id="title-auto-note"><i class="bi bi-magic me-1 text-primary"></i>Auto-filled from offer title</div>
                            @error('title') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Subheadline / Tagline</label>
                            <input type="text" name="subtitle" class="form-control" value="{{ old('subtitle', $banner->subtitle) }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Button Label</label>
                            <input type="text" name="primary_button_text" class="form-control" value="{{ old('primary_button_text', $banner->primary_button_text) }}">
                        </div>
                    </div>
                </div>
            </div>

            {{-- Step 4: Background --}}
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-3 border-bottom">
                    <h6 class="mb-0 fw-bold"><span class="text-primary me-2">④</span> Background — Image or Colour Gradient</h6>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label fw-bold">Accent Colour <small class="text-muted">(gradient when no image)</small></label>
                            @php
                                // Extract accent from existing gradient or default
                                preg_match('/#([0-9a-f]{6})/i', $banner->bg_gradient ?? '', $m);
                                $existingAccent = old('bg_color_accent', isset($m[0]) ? $m[0] : '#6d28d9');
                            @endphp
                            <input type="color" name="bg_color_accent" id="bg_color_accent" class="form-control form-control-color w-100" value="{{ $existingAccent }}" style="height: 44px;">
                        </div>
                        <div class="col-md-8">
                            <label class="form-label fw-bold">Live Preview</label>
                            <div id="gradient-preview" class="rounded-3 border shadow-sm d-flex align-items-center justify-content-center text-white fw-bold" style="height: 44px; font-size: 13px; background: {{ old('bg_gradient', $banner->bg_gradient ?? 'linear-gradient(135deg, #6d28d9 0%, #0f172a 100%)') }};">
                                Banner Gradient Preview
                            </div>
                            <input type="hidden" name="bg_gradient" id="bg_gradient_hidden" value="{{ old('bg_gradient', $banner->bg_gradient ?? 'linear-gradient(135deg, #6d28d9 0%, #0f172a 100%)') }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Desktop Image <small class="text-muted">(Optional — overrides gradient)</small></label>
                            <input type="file" name="desktop_image" class="form-control" accept="image/*">
                            @if($banner->desktop_image)
                                <img src="{{ asset('storage/' . $banner->desktop_image) }}" class="rounded mt-2 border shadow-sm" style="width:100%; height:90px; object-fit:cover;">
                            @endif
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Mobile Image <small class="text-muted">(Optional)</small></label>
                            <input type="file" name="mobile_image" class="form-control" accept="image/*">
                            @if($banner->mobile_image)
                                <img src="{{ asset('storage/' . $banner->mobile_image) }}" class="rounded mt-2 border shadow-sm" style="width:100px; height:90px; object-fit:cover;">
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- RIGHT COLUMN --}}
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white py-3 border-bottom">
                    <h6 class="mb-0 fw-bold">Settings</h6>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Display Order</label>
                        <input type="number" name="display_order" class="form-control" value="{{ old('display_order', $banner->display_order) }}" min="0">
                        <div class="form-text">0 = first slide in carousel.</div>
                    </div>
                    <div class="form-check form-switch mt-3">
                        <input class="form-check-input" type="checkbox" name="is_active" id="is_active" value="1" {{ old('is_active', $banner->is_active) ? 'checked' : '' }}>
                        <label class="form-check-label fw-bold" for="is_active">Active (visible on site)</label>
                    </div>
                </div>
            </div>

            <div class="d-grid gap-2">
                <button type="submit" class="btn btn-primary btn-lg fw-bold">
                    <i class="bi bi-check2-circle me-2"></i> Update Banner
                </button>
                <a href="{{ route('admin.banners.index') }}" class="btn btn-light">Cancel</a>
            </div>
        </div>
    </div>
</form>

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

    const checked = document.querySelector('.banner-type-radio:checked');
    if (checked) showSection(checked.value);

    // Live gradient preview from colour picker
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
</script>
@endsection
