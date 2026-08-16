@extends('admin.layouts.app')

@section('header', 'Create New Brand')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item"><a href="{{ route('admin.brands.index') }}">Brands</a></li>
    <li class="breadcrumb-item active" aria-current="page">Create</li>
@endsection

@section('actions')
    <a href="{{ route('admin.brands.index') }}" class="btn btn-outline-secondary rounded-pill px-3">
        <i class="bi bi-arrow-left me-1"></i> Back to Brands
    </a>
@endsection

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-9">
        <div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-4">
            <div class="card-header bg-white py-3 border-bottom">
                <h6 class="mb-0 fw-bold text-dark"><i class="bi bi-award-fill text-primary me-2"></i>Brand Specifications</h6>
            </div>
            <div class="card-body p-4">
                <form action="{{ route('admin.brands.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf

                    <div class="row g-4">
                        <!-- Left Details -->
                        <div class="col-md-7">
                            <div class="mb-3">
                                <label for="name" class="form-label fw-bold text-dark small">Brand Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name') }}" required placeholder="e.g. Sony, Nike, Apple">
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label for="slug" class="form-label fw-bold text-dark small">Slug (URL Identifier)</label>
                                <input type="text" class="form-control @error('slug') is-invalid @enderror" id="slug" name="slug" value="{{ old('slug') }}" placeholder="Auto-generated if left blank">
                                <div class="form-text text-muted" style="font-size: 0.72rem;">Unique URL slug for brand filter queries.</div>
                                @error('slug')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-0">
                                <label for="description" class="form-label fw-bold text-dark small">Brand Description (Optional)</label>
                                <textarea class="form-control @error('description') is-invalid @enderror" id="description" name="description" rows="4" placeholder="Brief background or tagline for this brand...">{{ old('description') }}</textarea>
                                @error('description')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- Right Settings & Logo -->
                        <div class="col-md-5">
                            <div class="mb-3">
                                <label for="category_id" class="form-label fw-bold text-dark small">Associated Category <span class="text-danger">*</span></label>
                                <select class="form-select @error('category_id') is-invalid @enderror" id="category_id" name="category_id" required>
                                    <option value="">Select Category</option>
                                    @foreach($categories as $category)
                                        <option value="{{ $category->id }}" {{ old('category_id') == $category->id ? 'selected' : '' }}>
                                            {{ $category->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('category_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label for="status" class="form-label fw-bold text-dark small">Status <span class="text-danger">*</span></label>
                                <select class="form-select @error('status') is-invalid @enderror" id="status" name="status" required>
                                    <option value="1" {{ old('status', '1') == '1' ? 'selected' : '' }}>Active</option>
                                    <option value="0" {{ old('status') == '0' ? 'selected' : '' }}>Inactive</option>
                                </select>
                                @error('status')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-0">
                                <label for="logo" class="form-label fw-bold text-dark small">Brand Logo</label>
                                <input class="form-control @error('logo') is-invalid @enderror" type="file" id="logo" name="logo" accept="image/*" onchange="previewImage(event)">
                                <div class="form-text text-muted" style="font-size: 0.72rem;">Square or transparent PNG recommended.</div>
                                @error('logo')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror

                                <div class="mt-3 text-center p-3 bg-light rounded-3 border">
                                    <img id="logoPreview" src="#" alt="Preview" class="img-fluid d-none" style="max-height: 100px; object-fit: contain;">
                                    <div id="placeholderPreview" class="text-muted small">
                                        <i class="bi bi-image fs-3 d-block mb-1"></i>
                                        <span>Logo Preview</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <hr class="my-4">

                    <div class="d-flex justify-content-end gap-2">
                        <a href="{{ route('admin.brands.index') }}" class="btn btn-light rounded-pill px-4">Cancel</a>
                        <button type="submit" class="btn btn-primary rounded-pill px-4 fw-bold">
                            <i class="bi bi-check-circle me-1"></i> Save Brand
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    function previewImage(event) {
        var input = event.target;
        var reader = new FileReader();
        reader.onload = function(){
            var dataURL = reader.result;
            var preview = document.getElementById('logoPreview');
            var placeholder = document.getElementById('placeholderPreview');
            preview.src = dataURL;
            preview.classList.remove('d-none');
            placeholder.classList.add('d-none');
        };
        if(input.files[0]) {
            reader.readAsDataURL(input.files[0]);
        }
    }
</script>
@endpush
