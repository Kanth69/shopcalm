@extends('admin.layouts.app')

@section('header', 'Edit Sales Offer Campaign')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item"><a href="{{ route('admin.offers.index') }}">Offers & Sales</a></li>
    <li class="breadcrumb-item active" aria-current="page">Edit {{ $offer->title }}</li>
@endsection

@section('actions')
    <div class="btn-group gap-2">
        <a href="{{ route('admin.offers.index') }}" class="btn btn-outline-secondary rounded-pill px-3">
            <i class="bi bi-arrow-left me-1"></i> Back to Campaigns
        </a>
    </div>
@endsection

@section('content')
<form action="{{ route('admin.offers.update', $offer) }}" method="POST" enctype="multipart/form-data" id="offerForm">
    @csrf
    @method('PUT')

    <div class="row g-4">
        <!-- Left Column: Configurations -->
        <div class="col-lg-8">
            <!-- 1. Campaign Identity -->
            <div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-4">
                <div class="card-header bg-white py-3 border-bottom d-flex align-items-center justify-content-between">
                    <h6 class="mb-0 fw-bold text-dark">
                        <i class="bi bi-megaphone-fill text-primary me-2"></i>1. Campaign Identity
                    </h6>
                    @if($offer->isLive())
                        <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-2.5 py-1">
                            <i class="bi bi-broadcast me-1"></i> LIVE CAMPAIGN
                        </span>
                    @endif
                </div>
                <div class="card-body p-4">
                    <div class="row g-3 mb-3">
                        <div class="col-md-7">
                            <label class="form-label fw-bold text-dark small">Campaign Title <span class="text-danger">*</span></label>
                            <input type="text" name="title" id="offer_title" 
                                class="form-control @error('title') is-invalid @enderror" 
                                value="{{ old('title', $offer->title) }}" required
                                oninput="updatePreview();">
                            @error('title') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-md-5">
                            <label class="form-label fw-bold text-dark small">Campaign Type <span class="text-danger">*</span></label>
                            <select name="type" class="form-select" id="offer_type" onchange="updatePreview();" required>
                                <option value="MEGA_SALE" {{ old('type', $offer->type) == 'MEGA_SALE' ? 'selected' : '' }}>Mega Sale Event</option>
                                <option value="FLASH_DEAL" {{ old('type', $offer->type) == 'FLASH_DEAL' ? 'selected' : '' }}>Flash Lightning Deal</option>
                                <option value="BANK_OFFER" {{ old('type', $offer->type) == 'BANK_OFFER' ? 'selected' : '' }}>Bank Instant Offer</option>
                                <option value="CATEGORY_SALE" {{ old('type', $offer->type) == 'CATEGORY_SALE' ? 'selected' : '' }}>Category Sale</option>
                                <option value="DIRECT_DISCOUNT" {{ old('type', $offer->type) == 'DIRECT_DISCOUNT' ? 'selected' : '' }}>Direct Storefront Discount</option>
                            </select>
                        </div>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-7">
                            <label class="form-label fw-bold text-dark small">Offer Badge Text</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light text-muted border-end-0">🏷️</span>
                                <input type="text" name="badge_text" id="offer_badge" 
                                    class="form-control border-start-0 ps-0" 
                                    value="{{ old('badge_text', $offer->badge_text) }}" placeholder="e.g. 🔥 MEGA DEAL"
                                    oninput="updatePreview();">
                            </div>
                        </div>

                        <div class="col-md-5">
                            <label class="form-label fw-bold text-dark small">Theme Accent Color</label>
                            <div class="d-flex align-items-center gap-2">
                                <input type="color" name="theme_color" id="theme_color" 
                                    class="form-control form-control-color border-0 rounded-3 shadow-sm" 
                                    value="{{ old('theme_color', $offer->theme_color ?? '#6366f1') }}" 
                                    style="width: 48px; height: 38px; cursor: pointer;"
                                    oninput="updatePreview();">
                                <div class="d-flex gap-1">
                                    <button type="button" class="btn btn-sm rounded-circle p-0" style="width: 24px; height: 24px; background: #6366f1;" onclick="setThemeColor('#6366f1')"></button>
                                    <button type="button" class="btn btn-sm rounded-circle p-0" style="width: 24px; height: 24px; background: #ef4444;" onclick="setThemeColor('#ef4444')"></button>
                                    <button type="button" class="btn btn-sm rounded-circle p-0" style="width: 24px; height: 24px; background: #10b981;" onclick="setThemeColor('#10b981')"></button>
                                    <button type="button" class="btn btn-sm rounded-circle p-0" style="width: 24px; height: 24px; background: #f59e0b;" onclick="setThemeColor('#f59e0b')"></button>
                                    <button type="button" class="btn btn-sm rounded-circle p-0" style="width: 24px; height: 24px; background: #0f172a;" onclick="setThemeColor('#0f172a')"></button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="mb-0">
                        <label class="form-label fw-bold text-dark small">Campaign Description</label>
                        <textarea name="description" id="offer_desc" class="form-control" rows="2" 
                            oninput="updatePreview();">{{ old('description', $offer->description) }}</textarea>
                    </div>
                </div>
            </div>

            <!-- 2. Discount & Rules -->
            <div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-4">
                <div class="card-header bg-white py-3 border-bottom">
                    <h6 class="mb-0 fw-bold text-dark">
                        <i class="bi bi-percent text-primary me-2"></i>2. Discount Value & Applicability Rules
                    </h6>
                </div>
                <div class="card-body p-4">
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold text-dark small">Discount Type <span class="text-danger">*</span></label>
                            <select name="discount_type" class="form-select" id="discount_type" onchange="toggleDiscountType(); updatePreview();" required>
                                <option value="PERCENTAGE" {{ old('discount_type', $offer->discount_type) == 'PERCENTAGE' ? 'selected' : '' }}>Percentage (%)</option>
                                <option value="FLAT" {{ old('discount_type', $offer->discount_type) == 'FLAT' ? 'selected' : '' }}>Fixed Flat Amount (₹)</option>
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-bold text-dark small">Discount Value <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text bg-light text-muted" id="discount_unit_symbol">%</span>
                                <input type="number" name="discount_value" id="discount_value" step="0.01" min="0.01" 
                                    class="form-control fw-bold @error('discount_value') is-invalid @enderror" 
                                    value="{{ old('discount_value', $offer->discount_value) }}" required
                                    oninput="updatePreview();">
                            </div>
                            @error('discount_value') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                        </div>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold text-dark small">Minimum Cart Spend (₹)</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light text-muted">₹</span>
                                <input type="number" name="min_purchase_amount" id="min_spend" step="0.01" min="0" 
                                    class="form-control" value="{{ old('min_purchase_amount', $offer->min_purchase_amount) }}"
                                    oninput="updatePreview();">
                            </div>
                        </div>

                        <div class="col-md-6" id="max_discount_container">
                            <label class="form-label fw-bold text-dark small">Max Discount Cap (₹)</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light text-muted">₹</span>
                                <input type="number" name="max_discount_amount" id="max_discount_cap" step="0.01" min="0" 
                                    class="form-control" value="{{ old('max_discount_amount', $offer->max_discount_amount) }}"
                                    oninput="updatePreview();">
                            </div>
                        </div>
                    </div>

                    <hr class="my-3">

                    @php $currentTarget = $offer->targets->first(); @endphp
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold text-dark small">Target Audience Scope</label>
                            <select name="target_type" class="form-select" id="target_type" onchange="populateTargets(); updatePreview();">
                                <option value="" {{ !$currentTarget ? 'selected' : '' }}>All Products (Storewide Sale)</option>
                                <option value="CATEGORY" {{ (old('target_type', $currentTarget?->target_type) == 'CATEGORY') ? 'selected' : '' }}>Specific Category</option>
                                <option value="BRAND" {{ (old('target_type', $currentTarget?->target_type) == 'BRAND') ? 'selected' : '' }}>Specific Brand</option>
                                <option value="PRODUCT" {{ (old('target_type', $currentTarget?->target_type) == 'PRODUCT') ? 'selected' : '' }}>Specific Product</option>
                            </select>
                        </div>

                        <div class="col-md-6 {{ !$currentTarget ? 'd-none' : '' }}" id="target_id_container">
                            <label class="form-label fw-bold text-dark small" id="target_id_label">Target Selection</label>
                            <select name="target_id" class="form-select" id="target_id_select" onchange="updatePreview();">
                                <option value="">Choose target...</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Column: Live Preview & Schedule -->
        <div class="col-lg-4">
            <!-- Live Preview -->
            <div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-4" id="preview_card_container" style="background: {{ $offer->theme_color ?? '#6366f1' }}; transition: all 0.3s ease;">
                <div class="card-body p-4 text-white position-relative">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <span class="badge rounded-pill px-2.5 py-1" id="preview_badge_pill" style="background: rgba(255,255,255,0.25); color: #fff;">
                            {{ $offer->badge_text ?? '🔥 DEAL' }}
                        </span>
                        <span class="badge bg-success rounded-pill px-2 py-1" id="preview_status_indicator">Active</span>
                    </div>

                    <div class="py-2">
                        <div class="h2 fw-bolder mb-1" id="preview_discount_rate">
                            {{ $offer->discount_type === 'PERCENTAGE' ? $offer->discount_value.'%' : '₹'.$offer->discount_value }} OFF
                        </div>
                        <h5 class="fw-bold mb-1" id="preview_title">{{ $offer->title }}</h5>
                        <p class="small text-white-50 mb-0 text-truncate" id="preview_desc_text" style="max-width: 280px;">{{ $offer->description }}</p>
                    </div>

                    <div class="mt-3 p-2 rounded-3 bg-white bg-opacity-10 border border-white border-opacity-25 small text-white-50" style="font-size: 0.72rem;">
                        <div class="d-flex justify-content-between mb-1">
                            <span>Applicability:</span>
                            <strong class="text-white" id="preview_scope_label">Storewide</strong>
                        </div>
                        <div class="d-flex justify-content-between">
                            <span>Min Cart:</span>
                            <strong class="text-white" id="preview_min_cart">₹{{ number_format($offer->min_purchase_amount, 2) }}</strong>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Banner Upload & Scheduling Card -->
            <div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-4">
                <div class="card-header bg-white py-3 border-bottom">
                    <h6 class="mb-0 fw-bold text-dark">
                        <i class="bi bi-image-fill text-primary me-2"></i>3. Banner & Scheduling
                    </h6>
                </div>
                <div class="card-body p-4">
                    <div class="mb-3">
                        <label class="form-label fw-bold text-dark small">Campaign Banner Image</label>
                        @if($offer->banner_image)
                            <div class="mb-2">
                                <img id="existingBannerImg" src="{{ asset('storage/' . $offer->banner_image) }}" class="img-fluid rounded-3 border w-100" style="max-height: 120px; object-fit: cover;">
                            </div>
                        @endif
                        <input type="file" name="banner_image" class="form-control" accept="image/*" onchange="previewBannerImage(this)">
                        <img id="bannerPreviewImg" src="#" alt="Preview" class="img-fluid rounded-3 mt-2 d-none border" style="max-height: 120px; width: 100%; object-fit: cover;">
                    </div>

                    <hr class="my-3">

                    <div class="mb-3">
                        <label class="form-label fw-bold text-dark small">Start Time</label>
                        <input type="datetime-local" name="start_time" class="form-control" value="{{ old('start_time', $offer->start_time ? $offer->start_time->format('Y-m-d\TH:i') : '') }}">
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold text-dark small">End Time (Expiry)</label>
                        <input type="datetime-local" name="end_time" class="form-control" value="{{ old('end_time', $offer->end_time ? $offer->end_time->format('Y-m-d\TH:i') : '') }}">
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold text-dark small">Priority Order</label>
                        <input type="number" name="priority" min="0" class="form-control" value="{{ old('priority', $offer->priority) }}">
                    </div>

                    <hr class="my-3">

                    <div class="form-check form-switch mb-0">
                        <input class="form-check-input" type="checkbox" name="is_active" id="is_active" value="1" {{ old('is_active', $offer->is_active) ? 'checked' : '' }} onchange="updatePreview();">
                        <label class="form-check-label fw-semibold text-dark small" for="is_active">Campaign Active</label>
                    </div>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="d-grid gap-2">
                <button type="submit" class="btn btn-primary rounded-pill btn-lg fw-bold shadow-sm">
                    <i class="bi bi-check-circle me-1"></i> Update Campaign
                </button>
                <a href="{{ route('admin.offers.index') }}" class="btn btn-light rounded-pill border">Cancel</a>
            </div>
        </div>
    </div>
</form>

@push('scripts')
<script>
    const categories = @json($categories ?? []);
    const brands = @json($brands ?? []);
    const products = @json($products ?? []);
    const currentTargetId = "{{ old('target_id', $currentTarget?->target_id) }}";

    function setThemeColor(hex) {
        document.getElementById('theme_color').value = hex;
        updatePreview();
    }

    function toggleDiscountType() {
        const type = document.getElementById('discount_type').value;
        const symbol = document.getElementById('discount_unit_symbol');
        const maxCapBox = document.getElementById('max_discount_container');

        if (type === 'PERCENTAGE') {
            symbol.innerText = '%';
            maxCapBox.style.display = 'block';
        } else {
            symbol.innerText = '₹';
            maxCapBox.style.display = 'none';
        }
    }

    function populateTargets() {
        const type = document.getElementById('target_type').value;
        const container = document.getElementById('target_id_container');
        const select = document.getElementById('target_id_select');
        const label = document.getElementById('target_id_label');

        select.innerHTML = '<option value="">Choose target...</option>';

        if (!type) {
            container.classList.add('d-none');
            return;
        }

        container.classList.remove('d-none');

        if (type === 'CATEGORY') {
            label.innerText = 'Select Target Category';
            categories.forEach(c => {
                const isSelected = (c.id == currentTargetId) ? 'selected' : '';
                select.innerHTML += `<option value="${c.id}" ${isSelected}>${c.name}</option>`;
            });
        } else if (type === 'BRAND') {
            label.innerText = 'Select Target Brand';
            brands.forEach(b => {
                const isSelected = (b.id == currentTargetId) ? 'selected' : '';
                select.innerHTML += `<option value="${b.id}" ${isSelected}>${b.name}</option>`;
            });
        } else if (type === 'PRODUCT') {
            label.innerText = 'Select Target Product';
            products.forEach(p => {
                const isSelected = (p.id == currentTargetId) ? 'selected' : '';
                select.innerHTML += `<option value="${p.id}" ${isSelected}>${p.name}</option>`;
            });
        }
    }

    function previewBannerImage(input) {
        const preview = document.getElementById('bannerPreviewImg');
        const existing = document.getElementById('existingBannerImg');
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

    function updatePreview() {
        const title = document.getElementById('offer_title').value || 'Mega Sale Campaign Title';
        const badge = document.getElementById('offer_badge').value || '🔥 DEAL';
        const desc = document.getElementById('offer_desc').value || 'Campaign promotional summary';
        const color = document.getElementById('theme_color').value || '#6366f1';
        const type = document.getElementById('discount_type').value;
        const value = document.getElementById('discount_value').value || '0';
        const minSpend = document.getElementById('min_spend').value || '0';
        const isActive = document.getElementById('is_active').checked;
        const targetType = document.getElementById('target_type').value;

        document.getElementById('preview_title').innerText = title;
        document.getElementById('preview_badge_pill').innerText = badge;
        document.getElementById('preview_desc_text').innerText = desc;
        document.getElementById('preview_card_container').style.background = color;

        if (type === 'PERCENTAGE') {
            document.getElementById('preview_discount_rate').innerText = `${value}% OFF`;
        } else {
            document.getElementById('preview_discount_rate').innerText = `₹${value} FLAT`;
        }

        document.getElementById('preview_min_cart').innerText = `₹${minSpend}`;

        const scopeMap = {
            '': 'Storewide (All Products)',
            'CATEGORY': 'Category Exclusive',
            'BRAND': 'Brand Exclusive',
            'PRODUCT': 'Specific Product'
        };
        document.getElementById('preview_scope_label').innerText = scopeMap[targetType] || 'Storewide';

        const statusIndicator = document.getElementById('preview_status_indicator');
        if (isActive) {
            statusIndicator.innerText = 'Active';
            statusIndicator.className = 'badge bg-success rounded-pill px-2 py-1';
        } else {
            statusIndicator.innerText = 'Inactive';
            statusIndicator.className = 'badge bg-secondary rounded-pill px-2 py-1';
        }
    }

    document.addEventListener('DOMContentLoaded', function() {
        toggleDiscountType();
        populateTargets();
        updatePreview();
    });
</script>
@endpush
@endsection
