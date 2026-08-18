@extends('product-manager.layouts.app')

@section('title', 'Edit Category - ' . $category->name)
@section('header', 'Edit Category')
@section('subheader', 'Update category details and storefront visibility')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-7">
        <div class="card bg-white shadow-sm border-0 mb-4">
            <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
                <h6 class="mb-0 fw-bold text-dark"><i class="bi bi-pencil-square text-primary me-2"></i>Edit {{ $category->name }}</h6>
                <a href="{{ route('product-manager.categories.index') }}" class="btn btn-sm btn-outline-secondary rounded-pill px-3">
                    <i class="bi bi-arrow-left me-1"></i> Back
                </a>
            </div>
            <div class="card-body p-4">
                <form action="{{ route('product-manager.categories.update', $category) }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')

                    <div class="mb-3">
                        <label class="form-label fw-bold small text-dark">Category Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $category->name) }}" required>
                        @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold small text-dark">Description</label>
                        <textarea name="description" class="form-control @error('description') is-invalid @enderror" rows="3">{{ old('description', $category->description) }}</textarea>
                        @error('description') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <label class="form-label fw-bold small text-dark">Category Photo</label>
                            @if($category->image)
                                <div class="mb-2">
                                    <img src="{{ asset('storage/' . $category->image) }}" class="rounded-3 border" style="width: 60px; height: 60px; object-fit: cover;">
                                </div>
                            @endif
                            <input type="file" name="image" class="form-control @error('image') is-invalid @enderror" accept="image/*">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-bold small text-dark">Status <span class="text-danger">*</span></label>
                            <select name="status" class="form-select @error('status') is-invalid @enderror" required>
                                <option value="Active" {{ old('status', $category->status) === 'Active' ? 'selected' : '' }}>Active</option>
                                <option value="Inactive" {{ old('status', $category->status) === 'Inactive' ? 'selected' : '' }}>Inactive</option>
                            </select>
                        </div>
                    </div>

                    <div class="d-flex justify-content-end gap-2">
                        <a href="{{ route('product-manager.categories.index') }}" class="btn btn-light rounded-pill px-4 border">Cancel</a>
                        <button type="submit" class="btn btn-pm-primary px-4 fw-bold">
                            <i class="bi bi-check-circle me-1"></i> Update Category
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
