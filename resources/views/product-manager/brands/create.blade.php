@extends('product-manager.layouts.app')

@section('title', 'Add Brand')
@section('header', 'Add Brand')
@section('subheader', 'Create a new manufacturer brand profile')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-6">
        <div class="card bg-white shadow-sm border-0 mb-4">
            <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
                <h6 class="mb-0 fw-bold text-dark"><i class="bi bi-tag text-primary me-2"></i>New Brand</h6>
                <a href="{{ route('product-manager.brands.index') }}" class="btn btn-sm btn-outline-secondary rounded-pill px-3">
                    <i class="bi bi-arrow-left me-1"></i> Back
                </a>
            </div>
            <div class="card-body p-4">
                <form action="{{ route('product-manager.brands.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf

                    <div class="mb-3">
                        <label class="form-label fw-bold small text-dark">Brand Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name') }}" required placeholder="e.g. Sony, Logitech, Nike">
                        @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold small text-dark">Brand Logo</label>
                        <input type="file" name="logo" class="form-control @error('logo') is-invalid @enderror" accept="image/*">
                        @error('logo') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-bold small text-dark">Status <span class="text-danger">*</span></label>
                        <select name="status" class="form-select @error('status') is-invalid @enderror" required>
                            <option value="1" {{ old('status', '1') == '1' ? 'selected' : '' }}>Active</option>
                            <option value="0" {{ old('status') == '0' ? 'selected' : '' }}>Inactive</option>
                        </select>
                    </div>

                    <div class="d-flex justify-content-end gap-2">
                        <a href="{{ route('product-manager.brands.index') }}" class="btn btn-light rounded-pill px-4 border">Cancel</a>
                        <button type="submit" class="btn btn-pm-primary px-4 fw-bold">
                            <i class="bi bi-check-circle me-1"></i> Save Brand
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
