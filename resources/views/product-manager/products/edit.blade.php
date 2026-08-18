@extends('product-manager.layouts.app')

@section('title', 'Edit Product')
@section('header', 'Edit Product')
@section('subheader', 'Update product details and specifications')

@section('content')
@if($product->status === 'Rejected')
    <div class="alert alert-danger border-0 rounded-4 p-3 mb-4 d-flex align-items-start gap-3">
        <i class="bi bi-x-circle text-danger fs-4 flex-shrink-0 mt-1"></i>
        <div>
            <div class="fw-bold text-danger">Admin Feedback:</div>
            <div class="text-dark small">{{ $product->rejection_reason ?? 'Please revise product details before resubmitting.' }}</div>
        </div>
    </div>
@endif

<form action="{{ route('product-manager.products.update', $product) }}" method="POST" enctype="multipart/form-data">
    @csrf
    @method('PUT')

    <div class="row g-4">
        <!-- Left Column -->
        <div class="col-lg-8">
            <div class="card bg-white border-0 shadow-sm rounded-4 mb-4">
                <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
                    <h6 class="mb-0 fw-bold text-dark">Product Details</h6>
                    <a href="{{ route('product-manager.products.index') }}" class="btn btn-sm btn-light border rounded-pill px-3">
                        <i class="bi bi-arrow-left me-1"></i> Back
                    </a>
                </div>
                <div class="card-body p-4">
                    <div class="mb-3">
                        <label class="form-label fw-bold small text-dark">Product Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $product->name) }}" required>
                        @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold small text-dark">SKU Code <span class="text-danger">*</span></label>
                            <input type="text" name="sku" class="form-control text-uppercase font-monospace @error('sku') is-invalid @enderror" value="{{ old('sku', $product->sku) }}" required>
                            @error('sku') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-bold small text-dark">Slug</label>
                            <input type="text" name="slug" class="form-control @error('slug') is-invalid @enderror" value="{{ old('slug', $product->slug) }}">
                            @error('slug') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold small text-dark">Short Summary</label>
                        <textarea name="short_description" class="form-control @error('short_description') is-invalid @enderror" rows="2">{{ old('short_description', $product->short_description) }}</textarea>
                        @error('short_description') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="mb-0">
                        <label class="form-label fw-bold small text-dark">Full Description</label>
                        <textarea name="description" class="form-control @error('description') is-invalid @enderror" rows="5">{{ old('description', $product->description) }}</textarea>
                        @error('description') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                </div>
            </div>

            <!-- Pricing & Inventory -->
            <div class="card bg-white border-0 shadow-sm rounded-4 mb-4">
                <div class="card-header bg-white py-3 border-bottom">
                    <h6 class="mb-0 fw-bold text-dark">Pricing & Stock</h6>
                </div>
                <div class="card-body p-4">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold small text-dark">Base Price (₹) <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text bg-light text-muted">₹</span>
                                <input type="number" name="price" step="0.01" min="0" class="form-control fw-bold @error('price') is-invalid @enderror" value="{{ old('price', $product->price) }}" required>
                            </div>
                            @error('price') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-bold small text-dark">Current Stock (Units) <span class="text-danger">*</span></label>
                            <input type="number" name="stock" min="0" class="form-control fw-bold @error('stock') is-invalid @enderror" value="{{ old('stock', $product->stock) }}" required>
                            @error('stock') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Column -->
        <div class="col-lg-4">
            <!-- Taxonomy -->
            <div class="card bg-white border-0 shadow-sm rounded-4 mb-4">
                <div class="card-header bg-white py-3 border-bottom">
                    <h6 class="mb-0 fw-bold text-dark">Category & Brand</h6>
                </div>
                <div class="card-body p-4">
                    <div class="mb-3">
                        <label class="form-label fw-bold small text-dark">Category <span class="text-danger">*</span></label>
                        <select name="category_id" class="form-select @error('category_id') is-invalid @enderror" required>
                            @foreach($categories as $cat)
                                <option value="{{ $cat->id }}" {{ old('category_id', $product->category_id) == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                            @endforeach
                        </select>
                        @error('category_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold small text-dark">Brand <span class="text-danger">*</span></label>
                        <select name="brand_id" class="form-select @error('brand_id') is-invalid @enderror" required>
                            @foreach($brands as $b)
                                <option value="{{ $b->id }}" {{ old('brand_id', $product->brand_id) == $b->id ? 'selected' : '' }}>{{ $b->name }}</option>
                            @endforeach
                        </select>
                        @error('brand_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="pt-2 border-top">
                        <div class="form-check mb-2">
                            <input type="checkbox" name="featured" value="1" class="form-check-input" id="feat" {{ old('featured', $product->featured) ? 'checked' : '' }}>
                            <label class="form-check-label small fw-semibold text-dark" for="feat">Featured Item</label>
                        </div>
                        <div class="form-check">
                            <input type="checkbox" name="trending" value="1" class="form-check-input" id="trend" {{ old('trending', $product->trending) ? 'checked' : '' }}>
                            <label class="form-check-label small fw-semibold text-dark" for="trend">Trending Item</label>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Media -->
            <div class="card bg-white border-0 shadow-sm rounded-4 mb-4">
                <div class="card-header bg-white py-3 border-bottom">
                    <h6 class="mb-0 fw-bold text-dark">Images</h6>
                </div>
                <div class="card-body p-4">
                    <div class="mb-3">
                        <label class="form-label fw-bold small text-dark">Main Hero Photo</label>
                        @if($product->main_image)
                            <div class="mb-2">
                                <img src="{{ asset('storage/' . $product->main_image) }}" class="rounded-3 border" style="width: 70px; height: 70px; object-fit: cover;">
                            </div>
                        @endif
                        <input type="file" name="main_image" class="form-control" accept="image/*">
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold small text-dark">Add Gallery Photos</label>
                        <input type="file" name="gallery_images[]" multiple class="form-control" accept="image/*">
                    </div>

                    @if($product->galleryImages->isNotEmpty())
                        <div class="pt-2 border-top">
                            <label class="form-label fw-bold small text-dark mb-2">Gallery</label>
                            <div class="d-flex flex-wrap gap-2">
                                @foreach($product->galleryImages as $gal)
                                <div class="position-relative">
                                    <img src="{{ asset('storage/' . $gal->image) }}" class="rounded-3 border" style="width: 48px; height: 48px; object-fit: cover;">
                                    <form action="{{ route('product-manager.products.gallery.destroy', $gal->id) }}" method="POST" class="position-absolute top-0 end-0 m-0.5">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-danger btn-sm p-0 rounded-circle d-flex align-items-center justify-content-center" style="width: 16px; height: 16px;">
                                            <i class="bi bi-x" style="font-size: 10px;"></i>
                                        </button>
                                    </form>
                                </div>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Submit -->
            <div class="card bg-white border-0 shadow-sm rounded-4 p-3">
                <button type="submit" class="btn btn-pm-primary w-100 py-2.5 fw-bold rounded-pill shadow-sm">
                    Save & Submit
                </button>
            </div>
        </div>
    </div>
</form>
@endsection
