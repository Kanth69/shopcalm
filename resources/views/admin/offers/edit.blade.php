@extends('admin.layouts.app')

@section('header', 'Edit Sales Offer Campaign')

@section('actions')
    <a href="{{ route('admin.offers.index') }}" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left"></i> Back to List
    </a>
@endsection

@section('content')
<div class="row">
    <div class="col-md-12">
        <form action="{{ route('admin.offers.update', $offer) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            <div class="row">
                <div class="col-md-8">
                    <!-- Basic Info -->
                    <div class="card mb-4 border-0 shadow-sm">
                        <div class="card-header bg-white py-3">
                            <h5 class="card-title mb-0 fw-bold">1. Campaign Details</h5>
                        </div>
                        <div class="card-body">
                            <div class="row mb-3">
                                <div class="col-md-8">
                                    <label class="form-label fw-bold">Campaign Title <span class="text-danger">*</span></label>
                                    <input type="text" name="title" class="form-control @error('title') is-invalid @enderror" value="{{ old('title', $offer->title) }}" required>
                                    @error('title') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-bold">Campaign Type <span class="text-danger">*</span></label>
                                    <select name="type" class="form-select" required>
                                        <option value="MEGA_SALE" {{ old('type', $offer->type) == 'MEGA_SALE' ? 'selected' : '' }}>Mega Sale Event</option>
                                        <option value="FLASH_DEAL" {{ old('type', $offer->type) == 'FLASH_DEAL' ? 'selected' : '' }}>Flash Lightning Deal</option>
                                        <option value="BANK_OFFER" {{ old('type', $offer->type) == 'BANK_OFFER' ? 'selected' : '' }}>Bank Instant Offer</option>
                                        <option value="CATEGORY_SALE" {{ old('type', $offer->type) == 'CATEGORY_SALE' ? 'selected' : '' }}>Category Sale</option>
                                        <option value="DIRECT_DISCOUNT" {{ old('type', $offer->type) == 'DIRECT_DISCOUNT' ? 'selected' : '' }}>Direct Storefront Discount</option>
                                    </select>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label class="form-label fw-bold">Offer Badge Text</label>
                                    <input type="text" name="badge_text" class="form-control" value="{{ old('badge_text', $offer->badge_text) }}" placeholder="e.g. 🔥 BIG BILLION DEAL">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-bold">Theme Accent Color</label>
                                    <input type="color" name="theme_color" class="form-control form-control-color w-100" value="{{ old('theme_color', $offer->theme_color ?? '#2563eb') }}">
                                </div>
                            </div>

                            <div class="mb-0">
                                <label class="form-label fw-bold">Campaign Description</label>
                                <textarea name="description" class="form-control" rows="3">{{ old('description', $offer->description) }}</textarea>
                            </div>
                        </div>
                    </div>

                    <!-- Discount & Rules -->
                    <div class="card mb-4 border-0 shadow-sm">
                        <div class="card-header bg-white py-3">
                            <h5 class="card-title mb-0 fw-bold">2. Discount & Applicability Rules</h5>
                        </div>
                        <div class="card-body">
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label class="form-label fw-bold">Discount Type <span class="text-danger">*</span></label>
                                    <select name="discount_type" class="form-select" required>
                                        <option value="PERCENTAGE" {{ old('discount_type', $offer->discount_type) == 'PERCENTAGE' ? 'selected' : '' }}>Percentage (%)</option>
                                        <option value="FLAT" {{ old('discount_type', $offer->discount_type) == 'FLAT' ? 'selected' : '' }}>Fixed Flat Amount (₹)</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-bold">Discount Value <span class="text-danger">*</span></label>
                                    <input type="number" name="discount_value" step="0.01" class="form-control" value="{{ old('discount_value', $offer->discount_value) }}" required>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label class="form-label fw-bold">Minimum Order Amount (₹)</label>
                                    <input type="number" name="min_purchase_amount" step="0.01" class="form-control" value="{{ old('min_purchase_amount', $offer->min_purchase_amount) }}">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-bold">Maximum Discount Cap (₹)</label>
                                    <input type="number" name="max_discount_amount" step="0.01" class="form-control" value="{{ old('max_discount_amount', $offer->max_discount_amount) }}">
                                </div>
                            </div>

                            <hr>

                            @php $currentTarget = $offer->targets->first(); @endphp
                            <div class="row">
                                <div class="col-md-6">
                                    <label class="form-label fw-bold">Target Restriction</label>
                                    <select name="target_type" class="form-select" id="target_type">
                                        <option value="" {{ !$currentTarget ? 'selected' : '' }}>All Products (Storewide)</option>
                                        <option value="CATEGORY" {{ (old('target_type', $currentTarget?->target_type) == 'CATEGORY') ? 'selected' : '' }}>Specific Category</option>
                                        <option value="BRAND" {{ (old('target_type', $currentTarget?->target_type) == 'BRAND') ? 'selected' : '' }}>Specific Brand</option>
                                        <option value="PRODUCT" {{ (old('target_type', $currentTarget?->target_type) == 'PRODUCT') ? 'selected' : '' }}>Specific Product</option>
                                    </select>
                                </div>
                                <div class="col-md-6 {{ !$currentTarget ? 'd-none' : '' }}" id="target_id_container">
                                    <label class="form-label fw-bold" id="target_id_label">Select Target</label>
                                    <select name="target_id" class="form-select" id="target_id_select">
                                        <option value="">Choose...</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Banners & Schedule Sidebar -->
                <div class="col-md-4">
                    <div class="card mb-4 border-0 shadow-sm">
                        <div class="card-header bg-white py-3">
                            <h5 class="card-title mb-0 fw-bold">3. Campaign Banner & Timers</h5>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <label class="form-label fw-bold">Campaign Banner Image</label>
                                @if($offer->banner_image)
                                    <div class="mb-2">
                                        <img src="{{ asset('storage/' . $offer->banner_image) }}" class="img-fluid rounded border">
                                    </div>
                                @endif
                                <input type="file" name="banner_image" class="form-control" accept="image/*">
                            </div>

                            <hr>

                            <div class="mb-3">
                                <label class="form-label fw-bold">Start Time</label>
                                <input type="datetime-local" name="start_time" class="form-control" value="{{ old('start_time', $offer->start_time ? $offer->start_time->format('Y-m-d\TH:i') : '') }}">
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-bold">End Time</label>
                                <input type="datetime-local" name="end_time" class="form-control" value="{{ old('end_time', $offer->end_time ? $offer->end_time->format('Y-m-d\TH:i') : '') }}">
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-bold">Priority Order</label>
                                <input type="number" name="priority" class="form-control" value="{{ old('priority', $offer->priority) }}">
                            </div>

                            <hr>

                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" name="is_active" id="is_active" value="1" {{ old('is_active', $offer->is_active) ? 'checked' : '' }}>
                                <label class="form-check-label fw-bold" for="is_active">Campaign Active</label>
                            </div>
                        </div>
                    </div>

                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-primary btn-lg fw-bold">Update Offer Campaign</button>
                        <a href="{{ route('admin.offers.index') }}" class="btn btn-light">Cancel</a>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const targetType = document.getElementById('target_type');
    const container = document.getElementById('target_id_container');
    const select = document.getElementById('target_id_select');
    const label = document.getElementById('target_id_label');
    const selectedId = "{{ old('target_id', $currentTarget?->target_id) }}";

    const categories = @json($categories ?? []);
    const brands = @json($brands ?? []);
    const products = @json($products ?? []);

    function populateTargets() {
        const val = targetType.value;
        select.innerHTML = '<option value="">Choose...</option>';

        if (!val) {
            container.classList.add('d-none');
            return;
        }

        container.classList.remove('d-none');

        if (val === 'CATEGORY') {
            label.innerText = 'Select Target Category';
            categories.forEach(c => {
                const sel = (c.id == selectedId) ? 'selected' : '';
                select.innerHTML += `<option value="${c.id}" ${sel}>${c.name}</option>`;
            });
        } else if (val === 'BRAND') {
            label.innerText = 'Select Target Brand';
            brands.forEach(b => {
                const sel = (b.id == selectedId) ? 'selected' : '';
                select.innerHTML += `<option value="${b.id}" ${sel}>${b.name}</option>`;
            });
        } else if (val === 'PRODUCT') {
            label.innerText = 'Select Target Product';
            products.forEach(p => {
                const sel = (p.id == selectedId) ? 'selected' : '';
                select.innerHTML += `<option value="${p.id}" ${sel}>${p.name}</option>`;
            });
        }
    }

    targetType.addEventListener('change', populateTargets);
    populateTargets();
});
</script>
@endsection
