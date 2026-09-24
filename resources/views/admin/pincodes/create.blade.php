@extends('admin.layouts.app')

@section('header', 'Add Serviceable Pincode')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item"><a href="{{ route('admin.pincodes.index') }}">Pincodes</a></li>
    <li class="breadcrumb-item active" aria-current="page">Add</li>
@endsection

@section('actions')
    <a href="{{ route('admin.pincodes.index') }}" class="btn btn-outline-secondary rounded-pill px-3.5 py-1.5 d-inline-flex align-items-center" style="gap: 0.5rem !important;">
        <i class="bi bi-arrow-left"></i> Back to Pincodes
    </a>
@endsection

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-7">
        <div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-4">
            <div class="card-header bg-white py-3 px-4 border-bottom">
                <div class="d-flex align-items-center" style="gap: 0.75rem !important;">
                    <div class="rounded-3 d-flex align-items-center justify-content-center text-primary flex-shrink-0" style="width: 32px; height: 32px; background: #ede9fe;">
                        <i class="bi bi-geo-alt-fill fs-5"></i>
                    </div>
                    <h6 class="mb-0 fw-bold text-dark">New Delivery Location Details</h6>
                </div>
            </div>
            <div class="card-body p-4">
                <form action="{{ route('admin.pincodes.store') }}" method="POST">
                    @csrf

                    <div class="mb-3">
                        <label class="form-label fw-bold text-dark small">6-Digit PIN Code <span class="text-danger">*</span></label>
                        <input type="text" name="pincode" maxlength="6" class="form-control @error('pincode') is-invalid @enderror font-monospace fs-5 fw-bold" value="{{ old('pincode') }}" required placeholder="e.g. 500081">
                        @error('pincode') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold text-dark small">City / Zone Name <span class="text-danger">*</span></label>
                            <input type="text" name="city" class="form-control @error('city') is-invalid @enderror" value="{{ old('city') }}" required placeholder="e.g. Hyderabad (Hitec City)">
                            @error('city') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-bold text-dark small">State <span class="text-danger">*</span></label>
                            <input type="text" name="state" class="form-control @error('state') is-invalid @enderror" value="{{ old('state') }}" required placeholder="e.g. Telangana">
                            @error('state') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                        </div>
                    </div>

                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <label class="form-label fw-bold text-dark small">Estimated Transit (Days) <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text bg-light text-muted px-3"><i class="bi bi-clock"></i></span>
                                <input type="number" name="delivery_days" min="1" max="14" class="form-control @error('delivery_days') is-invalid @enderror" value="{{ old('delivery_days', 3) }}" required>
                            </div>
                            @error('delivery_days') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-bold text-dark small">Delivery Surcharge (₹) <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text bg-light text-muted px-3">₹</span>
                                <input type="number" step="0.01" min="0" name="delivery_charge" class="form-control @error('delivery_charge') is-invalid @enderror" value="{{ old('delivery_charge', '0.00') }}" required>
                            </div>
                            @error('delivery_charge') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                        </div>
                    </div>

                    <div class="row g-3 mb-4 p-3 bg-light rounded-3 border">
                        <div class="col-md-6">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" name="is_cod_available" value="1" id="codSwitch" {{ old('is_cod_available', true) ? 'checked' : '' }}>
                                <label class="form-check-label fw-bold text-dark small" for="codSwitch">
                                    Cash on Delivery (COD) Allowed
                                </label>
                            </div>
                            <small class="text-muted d-block mt-1">Allow customers to pay cash upon order arrival.</small>
                        </div>
                        <div class="col-md-6">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" name="is_serviceable" value="1" id="serviceableSwitch" {{ old('is_serviceable', true) ? 'checked' : '' }}>
                                <label class="form-check-label fw-bold text-dark small" for="serviceableSwitch">
                                    Location Serviceable
                                </label>
                            </div>
                            <small class="text-muted d-block mt-1">Enable delivery routing for this pincode.</small>
                        </div>
                        <div class="col-12 mt-3 pt-2 border-top">
                            <label class="form-label fw-bold text-dark small">COD Handling Fee (Optional Custom Override)</label>
                            <div class="input-group">
                                <span class="input-group-text bg-white text-muted px-3">₹</span>
                                <input type="number" step="0.01" min="0" name="cod_fee" class="form-control @error('cod_fee') is-invalid @enderror" value="{{ old('cod_fee') }}" placeholder="Leave blank to use Global Default (₹40.00)">
                            </div>
                            <small class="text-muted d-block mt-1">Leave blank to use the Global Admin Default fee (₹40.00).</small>
                            @error('cod_fee') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                        </div>
                    </div>

                    <hr class="my-4">

                    <div class="d-flex justify-content-end" style="gap: 0.75rem !important;">
                        <a href="{{ route('admin.pincodes.index') }}" class="btn btn-light border rounded-pill px-4">Cancel</a>
                        <button type="submit" class="btn btn-primary rounded-pill px-4 fw-bold d-inline-flex align-items-center" style="gap: 0.5rem !important;">
                            <i class="bi bi-check-circle"></i> Save Pincode
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
