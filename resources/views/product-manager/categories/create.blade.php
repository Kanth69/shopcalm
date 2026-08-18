@extends('product-manager.layouts.app')

@section('header', 'Create New Category')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('product-manager.dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item"><a href="{{ route('product-manager.categories.index') }}">Categories</a></li>
    <li class="breadcrumb-item active" aria-current="page">Create</li>
@endsection

@section('actions')
    <a href="{{ route('product-manager.categories.index') }}" class="btn btn-outline-secondary rounded-pill px-3">
        <i class="bi bi-arrow-left me-1"></i> Back to Categories
    </a>
@endsection

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-9">
        <div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-4">
            <div class="card-header bg-white py-3 border-bottom">
                <h6 class="mb-0 fw-bold text-dark">
                    <i class="bi bi-folder-plus text-primary me-2"></i>Category Specifications
                </h6>
            </div>
            <div class="card-body p-4">
                <form action="{{ route('product-manager.categories.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf

                    <div class="row g-4">
                        <!-- Left Details Column -->
                        <div class="col-md-7">
                            <div class="mb-3">
                                <label for="name" class="form-label fw-bold text-dark small">Category Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name') }}" required placeholder="e.g. Electronics, Fashion, Home Decor">
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label for="slug" class="form-label fw-bold text-dark small">Slug (URL Identifier)</label>
                                <input type="text" class="form-control @error('slug') is-invalid @enderror" id="slug" name="slug" value="{{ old('slug') }}" placeholder="Auto-generated if left blank">
                                <div class="form-text text-muted" style="font-size: 0.72rem;">Unique URL slug used for catalog filtering.</div>
                                @error('slug')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-0">
                                <label for="description" class="form-label fw-bold text-dark small">Description (Optional)</label>
                                <textarea class="form-control @error('description') is-invalid @enderror" id="description" name="description" rows="4" placeholder="Brief summary of items within this category...">{{ old('description') }}</textarea>
                                @error('description')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- Right Settings & Media Column -->
                        <div class="col-md-5">
                            <div class="mb-3">
                                <label for="status" class="form-label fw-bold text-dark small">Publish Status <span class="text-danger">*</span></label>
                                <select class="form-select @error('status') is-invalid @enderror" id="status" name="status" required>
                                    <option value="Active" {{ old('status', 'Active') === 'Active' ? 'selected' : '' }}>Active</option>
                                    <option value="Inactive" {{ old('status', 'Inactive') === 'Inactive' ? 'selected' : '' }}>Inactive</option>
                                </select>
                                @error('status')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-0">
                                <label for="image" class="form-label fw-bold text-dark small">Category Image / Thumbnail</label>
                                <input class="form-control @error('image') is-invalid @enderror" type="file" id="image" name="image" accept="image/*" onchange="previewImage(event)">
                                <div class="form-text text-muted" style="font-size: 0.72rem;">Recommended: Square PNG/JPG for catalog cards.</div>
                                @error('image')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror

                                <div class="mt-3 text-center p-3 bg-light rounded-3 border">
                                    <img id="imagePreview" src="#" alt="Preview" class="img-fluid d-none" style="max-height: 120px; object-fit: contain;">
                                    <div id="placeholderPreview" class="text-muted small">
                                        <i class="bi bi-image fs-3 d-block mb-1"></i>
                                        <span>Image Preview</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <hr class="my-4">

                    <div class="d-flex justify-content-end gap-2">
                        <a href="{{ route('product-manager.categories.index') }}" class="btn btn-light rounded-pill px-4">Cancel</a>
                        <button type="submit" class="btn btn-primary rounded-pill px-4 fw-bold">
                            <i class="bi bi-check-circle me-1"></i> Save Category
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    function previewImage(event) {
        const reader = new FileReader();
        reader.onload = function(){
            const output = document.getElementById('imagePreview');
            const placeholder = document.getElementById('placeholderPreview');
            output.src = reader.result;
            output.classList.remove('d-none');
            placeholder.classList.add('d-none');
        };
        if(event.target.files[0]) {
            reader.readAsDataURL(event.target.files[0]);
        }
    }
</script>
@endpush
@endsection
