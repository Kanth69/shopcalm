@extends('admin.layouts.app')

@section('header', 'Edit Coupon')

@section('actions')
    <a href="{{ route('admin.coupons.index') }}" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left"></i> Back to List
    </a>
@endsection

@section('content')
<div class="row">
    <div class="col-md-12">
        <form action="{{ route('admin.coupons.update', $coupon) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="row">
                <div class="col-md-8">
                    <!-- General Info -->
                    <div class="card mb-4 border-0 shadow-sm">
                        <div class="card-header bg-white py-3">
                            <h5 class="card-title mb-0 fw-bold">General Information</h5>
                        </div>
                        <div class="card-body">
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label class="form-label fw-bold">Coupon Code <span class="text-danger">*</span></label>
                                    <input type="text" name="code" class="form-control text-uppercase @error('code') is-invalid @enderror" value="{{ old('code', $coupon->code) }}" required>
                                    @error('code') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-bold">Coupon Name <span class="text-danger">*</span></label>
                                    <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $coupon->name) }}" required>
                                    @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                            </div>
                            <div class="mb-0">
                                <label class="form-label fw-bold">Description</label>
                                <textarea name="description" class="form-control" rows="3">{{ old('description', $coupon->description) }}</textarea>
                            </div>
                        </div>
                    </div>

                    <!-- Discount & Eligibility -->
                    <div class="card mb-4 border-0 shadow-sm">
                        <div class="card-header bg-white py-3">
                            <h5 class="card-title mb-0 fw-bold">Discount & Eligibility</h5>
                        </div>
                        <div class="card-body">
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label class="form-label fw-bold">Discount Type <span class="text-danger">*</span></label>
                                    @php $currentType = is_object($coupon->discount_type) ? $coupon->discount_type->value : $coupon->discount_type; @endphp
                                    <select name="discount_type" class="form-select" id="discount_type" required>
                                        <option value="PERCENTAGE" {{ old('discount_type', $currentType) == 'PERCENTAGE' ? 'selected' : '' }}>Percentage (%)</option>
                                        <option value="FLAT" {{ old('discount_type', $currentType) == 'FLAT' ? 'selected' : '' }}>Fixed Amount (₹)</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-bold">Discount Value <span class="text-danger">*</span></label>
                                    <input type="number" name="discount_value" step="0.01" class="form-control" value="{{ old('discount_value', $coupon->discount_value) }}" required>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label class="form-label fw-bold">Minimum Order Amount (₹)</label>
                                    <input type="number" name="minimum_order_amount" step="0.01" class="form-control" value="{{ old('minimum_order_amount', $coupon->minimum_order_amount) }}">
                                </div>
                                <div class="col-md-6" id="max_discount_container">
                                    <label class="form-label fw-bold">Maximum Discount Amount (₹)</label>
                                    <input type="number" name="maximum_discount_amount" step="0.01" class="form-control" value="{{ old('maximum_discount_amount', $coupon->maximum_discount_amount) }}" placeholder="For % coupons">
                                </div>
                            </div>

                            <hr>

                            @php $appType = is_object($coupon->applicable_type) ? $coupon->applicable_type->value : $coupon->applicable_type; @endphp
                            <div class="row">
                                <div class="col-md-6">
                                    <label class="form-label fw-bold">Applicable To</label>
                                    <select name="applicable_type" class="form-select" id="applicable_type">
                                        <option value="ALL" {{ old('applicable_type', $appType) == 'ALL' ? 'selected' : '' }}>Entire Store</option>
                                        <option value="CATEGORY" {{ old('applicable_type', $appType) == 'CATEGORY' ? 'selected' : '' }}>Specific Category</option>
                                        <option value="BRAND" {{ old('applicable_type', $appType) == 'BRAND' ? 'selected' : '' }}>Specific Brand</option>
                                        <option value="PRODUCT" {{ old('applicable_type', $appType) == 'PRODUCT' ? 'selected' : '' }}>Specific Product</option>
                                    </select>
                                </div>
                                <div class="col-md-6 {{ old('applicable_type', $appType) == 'ALL' ? 'd-none' : '' }}" id="applicable_id_container">
                                    <label class="form-label fw-bold" id="applicable_id_label">Select Item</label>
                                    <select name="applicable_id" class="form-select" id="applicable_id_select">
                                        <option value="">Choose...</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Limits & Validity -->
                <div class="col-md-4">
                    <div class="card mb-4 border-0 shadow-sm">
                        <div class="card-header bg-white py-3">
                            <h5 class="card-title mb-0 fw-bold">Usage & Validity</h5>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <label class="form-label fw-bold">Global Usage Limit</label>
                                <input type="number" name="usage_limit" class="form-control" value="{{ old('usage_limit', $coupon->usage_limit) }}" placeholder="Leave blank for unlimited">
                                <div class="form-text">Currently used: {{ $coupon->used_count }} times</div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-bold">Limit Per Customer <span class="text-danger">*</span></label>
                                <input type="number" name="usage_limit_per_customer" class="form-control" value="{{ old('usage_limit_per_customer', $coupon->usage_limit_per_customer) }}" required min="1">
                            </div>

                            <hr>

                            <div class="mb-3">
                                <label class="form-label fw-bold">Valid From</label>
                                <input type="datetime-local" name="valid_from" class="form-control" value="{{ old('valid_from', $coupon->valid_from ? $coupon->valid_from->format('Y-m-d\TH:i') : '') }}">
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-bold">Valid Until</label>
                                <input type="datetime-local" name="valid_until" class="form-control" value="{{ old('valid_until', $coupon->valid_until ? $coupon->valid_until->format('Y-m-d\TH:i') : '') }}">
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-bold">Priority</label>
                                <input type="number" name="priority" class="form-control" value="{{ old('priority', $coupon->priority ?? 0) }}" required>
                            </div>

                            <hr>

                            <div class="form-check form-switch mb-2">
                                <input class="form-check-input" type="checkbox" name="is_active" id="is_active" value="1" {{ old('is_active', $coupon->is_active) ? 'checked' : '' }}>
                                <label class="form-check-label fw-bold" for="is_active">Active</label>
                            </div>

                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" name="stackable" id="stackable" value="1" {{ old('stackable', $coupon->stackable) ? 'checked' : '' }}>
                                <label class="form-check-label fw-bold" for="stackable">Stackable</label>
                            </div>
                        </div>
                    </div>

                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-primary btn-lg">Update Coupon</button>
                        <a href="{{ route('admin.coupons.index') }}" class="btn btn-light">Cancel</a>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const applicableType = document.getElementById('applicable_type');
    const container = document.getElementById('applicable_id_container');
    const select = document.getElementById('applicable_id_select');
    const label = document.getElementById('applicable_id_label');
    const selectedId = "{{ old('applicable_id', $coupon->applicable_id) }}";

    const categories = @json($categories ?? []);
    const brands = @json($brands ?? []);
    const products = @json($products ?? []);

    function populateItems() {
        const val = applicableType.value;
        select.innerHTML = '<option value="">Choose...</option>';

        if (val === 'ALL') {
            container.classList.add('d-none');
            return;
        }

        container.classList.remove('d-none');

        if (val === 'CATEGORY') {
            label.innerText = 'Select Category';
            categories.forEach(c => {
                const sel = (c.id == selectedId) ? 'selected' : '';
                select.innerHTML += `<option value="${c.id}" ${sel}>${c.name}</option>`;
            });
        } else if (val === 'BRAND') {
            label.innerText = 'Select Brand';
            brands.forEach(b => {
                const sel = (b.id == selectedId) ? 'selected' : '';
                select.innerHTML += `<option value="${b.id}" ${sel}>${b.name}</option>`;
            });
        } else if (val === 'PRODUCT') {
            label.innerText = 'Select Product';
            products.forEach(p => {
                const sel = (p.id == selectedId) ? 'selected' : '';
                select.innerHTML += `<option value="${p.id}" ${sel}>${p.name}</option>`;
            });
        }
    }

    applicableType.addEventListener('change', populateItems);
    populateItems(); // Run on load
});
</script>
@endsection
