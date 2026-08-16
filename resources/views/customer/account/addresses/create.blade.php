@extends('customer.account.layout')

@section('title', 'Add New Address')

@section('account_content')

{{-- Hero Banner --}}
<div class="rounded-4 mb-4 p-4 d-flex align-items-center justify-content-between flex-wrap gap-3"
     style="background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%); min-height: 100px;">
    <div class="d-flex align-items-center gap-3">
        <div class="rounded-3 d-flex align-items-center justify-content-center"
             style="width:52px; height:52px; background: linear-gradient(135deg, #10b981 0%, #059669 100%);">
            <i class="bi bi-plus-circle-fill text-white fs-4"></i>
        </div>
        <div>
            <h5 class="fw-bold text-white mb-0">Add New Address</h5>
            <p class="text-white-50 small mb-0">Save your delivery details for faster checkout</p>
        </div>
    </div>
    <a href="{{ route('account.addresses.index') }}" class="btn btn-outline-light rounded-pill px-4 py-2 fw-semibold btn-sm">
        <i class="bi bi-arrow-left me-1"></i> Back to Addresses
    </a>
</div>

{{-- Form Card --}}
<div class="card border-0 shadow-sm rounded-4 overflow-hidden">
    <div class="card-body p-4 p-md-5">
        <form action="{{ route('account.addresses.store') }}" method="POST">
            @csrf

            {{-- Full Name --}}
            <div class="mb-4">
                <label for="name" class="form-label fw-semibold text-dark mb-1" style="font-size:0.875rem;">
                    <i class="bi bi-person me-1 text-primary"></i>Full Name
                </label>
                <input type="text" id="name" name="name"
                       value="{{ old('name') }}"
                       class="form-control rounded-3 @error('name') is-invalid @enderror"
                       placeholder="Enter recipient full name"
                       style="height:46px;"
                       required>
                @error('name')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            {{-- Phone --}}
            <div class="mb-4">
                <label for="phone" class="form-label fw-semibold text-dark mb-1" style="font-size:0.875rem;">
                    <i class="bi bi-telephone me-1 text-primary"></i>Phone Number
                </label>
                <input type="tel" id="phone" name="phone"
                       value="{{ old('phone') }}"
                       class="form-control rounded-3 @error('phone') is-invalid @enderror"
                       placeholder="e.g. 9876543210"
                       style="height:46px;"
                       required>
                @error('phone')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            {{-- Address --}}
            <div class="mb-4">
                <label for="address" class="form-label fw-semibold text-dark mb-1" style="font-size:0.875rem;">
                    <i class="bi bi-house me-1 text-primary"></i>Street Address
                </label>
                <textarea id="address" name="address" rows="3"
                          class="form-control rounded-3 @error('address') is-invalid @enderror"
                          placeholder="House No., Street, Area..."
                          required>{{ old('address') }}</textarea>
                @error('address')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            {{-- City / State / ZIP in 3 cols --}}
            <div class="row g-3 mb-4">
                <div class="col-md-4">
                    <label for="city" class="form-label fw-semibold text-dark mb-1" style="font-size:0.875rem;">
                        <i class="bi bi-building me-1 text-primary"></i>City
                    </label>
                    <input type="text" id="city" name="city"
                           value="{{ old('city') }}"
                           class="form-control rounded-3 @error('city') is-invalid @enderror"
                           placeholder="City"
                           style="height:46px;"
                           required>
                    @error('city')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-md-4">
                    <label for="state" class="form-label fw-semibold text-dark mb-1" style="font-size:0.875rem;">
                        <i class="bi bi-map me-1 text-primary"></i>State
                    </label>
                    <input type="text" id="state" name="state"
                           value="{{ old('state') }}"
                           class="form-control rounded-3 @error('state') is-invalid @enderror"
                           placeholder="State"
                           style="height:46px;"
                           required>
                    @error('state')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-md-4">
                    <label for="zip" class="form-label fw-semibold text-dark mb-1" style="font-size:0.875rem;">
                        <i class="bi bi-mailbox me-1 text-primary"></i>PIN / ZIP Code
                    </label>
                    <input type="text" id="zip" name="zip"
                           value="{{ old('zip') }}"
                           class="form-control rounded-3 @error('zip') is-invalid @enderror"
                           placeholder="560001"
                           style="height:46px;"
                           required>
                    @error('zip')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            {{-- Country --}}
            <div class="mb-5">
                <label for="country" class="form-label fw-semibold text-dark mb-1" style="font-size:0.875rem;">
                    <i class="bi bi-globe me-1 text-primary"></i>Country
                </label>
                <input type="text" id="country" name="country"
                       value="{{ old('country', 'India') }}"
                       class="form-control rounded-3 @error('country') is-invalid @enderror"
                       placeholder="Country"
                       style="height:46px;"
                       required>
                @error('country')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            {{-- Actions --}}
            <div class="d-flex gap-3 flex-wrap">
                <button type="submit" class="btn fw-bold rounded-pill px-5 py-2"
                        style="background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%); color:#fff; border:none; font-size:0.9rem; min-width:170px;">
                    <i class="bi bi-geo-alt-fill me-2"></i>Save Address
                </button>
                <a href="{{ route('account.addresses.index') }}"
                   class="btn btn-outline-secondary rounded-pill px-5 py-2 fw-semibold"
                   style="font-size:0.9rem;">
                    Cancel
                </a>
            </div>

        </form>
    </div>
</div>

@endsection
