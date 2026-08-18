@extends('product-manager.layouts.app')

@section('title', 'Edit Brand - ' . $brand->name)
@section('header', 'Edit Brand')
@section('subheader', 'Update brand profile and logo')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-6">
        <div class="card bg-white shadow-sm border-0 mb-4">
            <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
                <h6 class="mb-0 fw-bold text-dark"><i class="bi bi-pencil-square text-primary me-2"></i>Edit {{ $brand->name }}</h6>
                <a href="{{ route('product-manager.brands.index') }}" class="btn btn-sm btn-outline-secondary rounded-pill px-3">
                    <i class="bi bi-arrow-left me-1"></i> Back
                </a>
            </div>
            <div class="card-body p-4">
                <form action="{{ route('product-manager.brands.update', $brand) }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')

                    <div class="mb-3">
                        <label class="form-label fw-bold small text-dark">Brand Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $brand->name) }}" required>
                        @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold small text-dark">Brand Logo</label>
                        @if($brand->logo)
                            <div class="mb-2">
                                <img src="{{ asset('storage/' . $brand->logo) }}" class="rounded-3 border" style="width: 70px; height: 70px; object-fit: contain; background: #ffffff;">
                            </div>
                        @endif
                        <input type="file" name="logo" class="form-control @error('logo') is-invalid @enderror" accept="image/*">
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-bold small text-dark">Status <span class="text-danger">*</span></label>
                        <select name="status" class="form-select @error('status') is-invalid @enderror" required>
                            <option value="1" {{ old('status', $brand->status) == 1 ? 'selected' : '' }}>Active</option>
                            <option value="0" {{ old('status', $brand->status) == 0 ? 'selected' : '' }}>Inactive</option>
                        </select>
                    </div>

                    <div class="d-flex justify-content-end gap-2">
                        <a href="{{ route('product-manager.brands.index') }}" class="btn btn-light rounded-pill px-4 border">Cancel</a>
                        <button type="submit" class="btn btn-pm-primary px-4 fw-bold">
                            <i class="bi bi-check-circle me-1"></i> Update Brand
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
