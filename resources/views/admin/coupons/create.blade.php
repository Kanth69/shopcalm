@extends('admin.layouts.app')

@section('header', 'Create Promotional Coupon')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item"><a href="{{ route('admin.coupons.index') }}">Coupons</a></li>
    <li class="breadcrumb-item active" aria-current="page">Create</li>
@endsection

@section('actions')
    <a href="{{ route('admin.coupons.index') }}" class="btn btn-outline-secondary rounded-pill px-3">
        <i class="bi bi-arrow-left me-1"></i> Back to Coupons
    </a>
@endsection

@section('content')
<form action="{{ route('admin.coupons.store') }}" method="POST" id="couponForm">
    @csrf

    <div class="row g-4">
        <!-- Left Column: Form Configuration -->
        <div class="col-lg-8">
            <!-- 1. Basic Coupon Details -->
            <div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-4">
                <div class="card-header bg-white py-3 border-bottom d-flex align-items-center justify-content-between">
                    <h6 class="mb-0 fw-bold text-dark">
                        <i class="bi bi-ticket-perforated-fill text-primary me-2"></i>1. Coupon Identity
                    </h6>
                    <button type="button" class="btn btn-sm btn-outline-primary rounded-pill px-3" onclick="generateRandomCode()">
                        <i class="bi bi-shuffle me-1"></i> Generate Code
                    </button>
                </div>
                <div class="card-body p-4">
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold text-dark small">Coupon Code <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text bg-light text-muted border-end-0 font-monospace">#</span>
                                <input type="text" name="code" id="coupon_code" 
                                    class="form-control text-uppercase font-monospace fw-bold border-start-0 ps-0 @error('code') is-invalid @enderror" 
                                    value="{{ old('code') }}" required placeholder="e.g. FESTIVE20"
                                    oninput="this.value = this.value.toUpperCase(); updatePreview();">
                            </div>
                            <div class="form-text text-muted" style="font-size: 0.72rem;">Customer enters this code at checkout.</div>
                            @error('code') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-bold text-dark small">Internal Campaign Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" id="coupon_name" 
                                class="form-control @error('name') is-invalid @enderror" 
                                value="{{ old('name') }}" required placeholder="e.g. 20% Off Festive Sale"
                                oninput="updatePreview();">
                            <div class="form-text text-muted" style="font-size: 0.72rem;">Descriptive title for store managers.</div>
                            @error('name') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                        </div>
                    </div>

                    <div class="mb-0">
                        <label class="form-label fw-bold text-dark small">Description (Optional)</label>
                        <textarea name="description" id="coupon_desc" class="form-control" rows="2" 
                            placeholder="e.g. Get 20% discount up to ₹500 on all orders above ₹999."
                            oninput="updatePreview();">{{ old('description') }}</textarea>
                    </div>
                </div>
            </div>

            <!-- 2. Discount & Rules -->
            <div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-4">
                <div class="card-header bg-white py-3 border-bottom">
                    <h6 class="mb-0 fw-bold text-dark">
                        <i class="bi bi-percent text-primary me-2"></i>2. Discount Value & Limits
                    </h6>
                </div>
                <div class="card-body p-4">
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold text-dark small">Discount Type <span class="text-danger">*</span></label>
                            <select name="discount_type" class="form-select" id="discount_type" onchange="toggleDiscountType(); updatePreview();" required>
                                <option value="PERCENTAGE" {{ old('discount_type') === 'PERCENTAGE' ? 'selected' : '' }}>Percentage (%)</option>
                                <option value="FLAT" {{ old('discount_type') === 'FLAT' ? 'selected' : '' }}>Fixed Amount (₹)</option>
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-bold text-dark small">Discount Value <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text bg-light text-muted" id="discount_unit_symbol">%</span>
                                <input type="number" name="discount_value" id="discount_value" step="0.01" min="0.01" 
                                    class="form-control fw-bold @error('discount_value') is-invalid @enderror" 
                                    value="{{ old('discount_value', '10') }}" required placeholder="e.g. 20"
                                    oninput="updatePreview();">
                            </div>
                            @error('discount_value') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                        </div>
                    </div>

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold text-dark small">Minimum Order Spend (₹) <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text bg-light text-muted">₹</span>
                                <input type="number" name="minimum_order_amount" id="min_order_amount" step="0.01" min="0" 
                                    class="form-control" value="{{ old('minimum_order_amount', '0') }}" required
                                    oninput="updatePreview();">
                            </div>
                            <div class="form-text text-muted" style="font-size: 0.72rem;">Set to 0 if no minimum cart value is required.</div>
                        </div>

                        <div class="col-md-6" id="max_discount_container">
                            <label class="form-label fw-bold text-dark small">Max Discount Cap (₹)</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light text-muted">₹</span>
                                <input type="number" name="maximum_discount_amount" id="max_discount_amount" step="0.01" min="0" 
                                    class="form-control" value="{{ old('maximum_discount_amount') }}" placeholder="e.g. 500"
                                    oninput="updatePreview();">
                            </div>
                            <div class="form-text text-muted" style="font-size: 0.72rem;">Caps maximum savings on high value carts.</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 3. Target Scope & Eligibility -->
            <div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-4">
                <div class="card-header bg-white py-3 border-bottom">
                    <h6 class="mb-0 fw-bold text-dark">
                        <i class="bi bi-bullseye text-primary me-2"></i>3. Applicability Scope
                    </h6>
                </div>
                <div class="card-body p-4">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold text-dark small">Applicable To <span class="text-danger">*</span></label>
                            <select name="applicable_type" class="form-select" id="applicable_type" onchange="populateItems(); updatePreview();">
                                <option value="ALL" {{ old('applicable_type') == 'ALL' ? 'selected' : '' }}>Entire Store (All Products)</option>
                                <option value="CATEGORY" {{ old('applicable_type') == 'CATEGORY' ? 'selected' : '' }}>Specific Category</option>
                                <option value="BRAND" {{ old('applicable_type') == 'BRAND' ? 'selected' : '' }}>Specific Brand</option>
                                <option value="PRODUCT" {{ old('applicable_type') == 'PRODUCT' ? 'selected' : '' }}>Specific Product</option>
                            </select>
                        </div>

                        <div class="col-md-6 d-none" id="applicable_id_container">
                            <label class="form-label fw-bold text-dark small" id="applicable_id_label">Target Selection</label>
                            <select name="applicable_id" class="form-select" id="applicable_id_select" onchange="updatePreview();">
                                <option value="">Choose item...</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Column: Live Preview Card & Validity -->
        <div class="col-lg-4">
            <!-- Interactive Live Coupon Preview Voucher -->
            <div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-4" style="background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%);">
                <div class="card-body p-4 text-white position-relative">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <span class="badge bg-white bg-opacity-25 text-white px-2.5 py-1 rounded-pill small">
                            <i class="bi bi-stars me-1"></i>Voucher Preview
                        </span>
                        <span class="badge bg-success rounded-pill px-2 py-1" id="preview_status_badge">Active</span>
                    </div>

                    <div class="text-center py-2">
                        <div class="h2 fw-bolder mb-1" id="preview_discount_text">10% OFF</div>
                        <div class="text-white-50 small mb-3" id="preview_campaign_name">Coupon Name Preview</div>

                        <!-- Coupon Code Box -->
                        <div class="d-inline-flex align-items-center gap-2 px-3 py-2 rounded-3 bg-white text-dark shadow-sm border border-2 border-dashed border-primary">
                            <span class="font-monospace fw-bolder fs-5 text-primary" id="preview_code">CODE2026</span>
                        </div>
                    </div>

                    <hr class="border-white opacity-25 my-3">

                    <div class="small text-white-50 d-flex flex-column gap-1" style="font-size: 0.75rem;">
                        <div><i class="bi bi-bag-check me-1 text-white"></i> <span id="preview_min_spend">Min spend: ₹0</span></div>
                        <div><i class="bi bi-tag me-1 text-white"></i> <span id="preview_scope">Entire Store</span></div>
                        <div><i class="bi bi-calendar3 me-1 text-white"></i> <span id="preview_validity">Valid: No Expiry Set</span></div>
                    </div>
                </div>
            </div>

            <!-- Usage Limits & Validity Card -->
            <div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-4">
                <div class="card-header bg-white py-3 border-bottom">
                    <h6 class="mb-0 fw-bold text-dark">
                        <i class="bi bi-sliders text-primary me-2"></i>Usage & Schedule
                    </h6>
                </div>
                <div class="card-body p-4">
                    <div class="mb-3">
                        <label class="form-label fw-bold text-dark small">Total Global Usage Limit</label>
                        <input type="number" name="usage_limit" min="1" class="form-control" value="{{ old('usage_limit') }}" placeholder="Unlimited if blank">
                        <div class="form-text text-muted" style="font-size: 0.72rem;">Maximum redemptions across all customers.</div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold text-dark small">Per Customer Limit <span class="text-danger">*</span></label>
                        <input type="number" name="usage_limit_per_customer" min="1" class="form-control" value="{{ old('usage_limit_per_customer', 1) }}" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold text-dark small">Priority Order</label>
                        <input type="number" name="priority" min="0" class="form-control" value="{{ old('priority', 0) }}" required>
                        <div class="form-text text-muted" style="font-size: 0.72rem;">Higher numbers evaluated first.</div>
                    </div>

                    <hr class="my-3">

                    <div class="mb-3">
                        <label class="form-label fw-bold text-dark small">Valid From</label>
                        <input type="datetime-local" name="valid_from" id="valid_from" class="form-control" value="{{ old('valid_from') }}" onchange="updatePreview();">
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold text-dark small">Valid Until (Expiry)</label>
                        <input type="datetime-local" name="valid_until" id="valid_until" class="form-control" value="{{ old('valid_until') }}" onchange="updatePreview();">
                    </div>

                    <hr class="my-3">

                    <div class="form-check form-switch mb-2">
                        <input class="form-check-input" type="checkbox" name="is_active" id="is_active" value="1" {{ old('is_active', true) ? 'checked' : '' }} onchange="updatePreview();">
                        <label class="form-check-label fw-semibold text-dark small" for="is_active">Enable Coupon (Active)</label>
                    </div>

                    <div class="form-check form-switch mb-0">
                        <input class="form-check-input" type="checkbox" name="stackable" id="stackable" value="1" {{ old('stackable', false) ? 'checked' : '' }}>
                        <label class="form-check-label fw-semibold text-dark small" for="stackable">Stackable with other offers</label>
                    </div>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="d-grid gap-2">
                <button type="submit" class="btn btn-primary rounded-pill btn-lg fw-bold shadow-sm">
                    <i class="bi bi-check-circle me-1"></i> Save & Publish Coupon
                </button>
                <a href="{{ route('admin.coupons.index') }}" class="btn btn-light rounded-pill border">Cancel</a>
            </div>
        </div>
    </div>
</form>

@push('scripts')
<script>
    const categories = @json($categories ?? []);
    const brands = @json($brands ?? []);
    const products = @json($products ?? []);

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

    function populateItems() {
        const type = document.getElementById('applicable_type').value;
        const container = document.getElementById('applicable_id_container');
        const select = document.getElementById('applicable_id_select');
        const label = document.getElementById('applicable_id_label');
        const selectedId = "{{ old('applicable_id') }}";

        select.innerHTML = '<option value="">Choose item...</option>';

        if (type === 'ALL') {
            container.classList.add('d-none');
            return;
        }

        container.classList.remove('d-none');

        if (type === 'CATEGORY') {
            label.innerText = 'Select Target Category';
            categories.forEach(c => {
                const isSelected = (c.id == selectedId) ? 'selected' : '';
                select.innerHTML += `<option value="${c.id}" ${isSelected}>${c.name}</option>`;
            });
        } else if (type === 'BRAND') {
            label.innerText = 'Select Target Brand';
            brands.forEach(b => {
                const isSelected = (b.id == selectedId) ? 'selected' : '';
                select.innerHTML += `<option value="${b.id}" ${isSelected}>${b.name}</option>`;
            });
        } else if (type === 'PRODUCT') {
            label.innerText = 'Select Target Product';
            products.forEach(p => {
                const isSelected = (p.id == selectedId) ? 'selected' : '';
                select.innerHTML += `<option value="${p.id}" ${isSelected}>${p.name}</option>`;
            });
        }
    }

    function generateRandomCode() {
        const prefixes = ['SAVE', 'FESTIVE', 'SUPER', 'DEAL', 'SPECIAL', 'MEGA'];
        const randomPrefix = prefixes[Math.floor(Math.random() * prefixes.length)];
        const randomNum = Math.floor(10 + Math.random() * 90);
        const randomChars = Math.random().toString(36).substring(2, 5).toUpperCase();
        const code = `${randomPrefix}${randomNum}${randomChars}`;
        
        document.getElementById('coupon_code').value = code;
        updatePreview();
    }

    function updatePreview() {
        const code = document.getElementById('coupon_code').value || 'CODE2026';
        const name = document.getElementById('coupon_name').value || 'Campaign Title';
        const type = document.getElementById('discount_type').value;
        const value = document.getElementById('discount_value').value || '0';
        const minSpend = document.getElementById('min_order_amount').value || '0';
        const maxCap = document.getElementById('max_discount_amount').value;
        const isActive = document.getElementById('is_active').checked;
        const validUntil = document.getElementById('valid_until').value;
        const appType = document.getElementById('applicable_type').value;

        document.getElementById('preview_code').innerText = code;
        document.getElementById('preview_campaign_name').innerText = name;

        if (type === 'PERCENTAGE') {
            let text = `${value}% OFF`;
            if (maxCap && parseFloat(maxCap) > 0) {
                text += ` (Up to ₹${maxCap})`;
            }
            document.getElementById('preview_discount_text').innerText = text;
        } else {
            document.getElementById('preview_discount_text').innerText = `₹${value} OFF`;
        }

        document.getElementById('preview_min_spend').innerText = `Min spend: ₹${minSpend}`;
        
        const scopeTextMap = {
            'ALL': 'Entire Store',
            'CATEGORY': 'Category Exclusive',
            'BRAND': 'Brand Exclusive',
            'PRODUCT': 'Single Product Exclusive'
        };
        document.getElementById('preview_scope').innerText = scopeTextMap[appType] || 'Entire Store';

        if (validUntil) {
            const dateObj = new Date(validUntil);
            document.getElementById('preview_validity').innerText = `Expires: ${dateObj.toLocaleDateString()}`;
        } else {
            document.getElementById('preview_validity').innerText = 'Valid: Ongoing';
        }

        const statusBadge = document.getElementById('preview_status_badge');
        if (isActive) {
            statusBadge.innerText = 'Active';
            statusBadge.className = 'badge bg-success rounded-pill px-2 py-1';
        } else {
            statusBadge.innerText = 'Inactive';
            statusBadge.className = 'badge bg-secondary rounded-pill px-2 py-1';
        }
    }

    document.addEventListener('DOMContentLoaded', function() {
        toggleDiscountType();
        populateItems();
        updatePreview();
    });
</script>
@endpush
@endsection
