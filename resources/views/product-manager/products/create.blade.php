@extends('product-manager.layouts.app')

@section('title', 'Add New Product')
@section('header', 'Add New Product')
@section('subheader', 'Submit a new product for Admin review and publishing')

@section('content')
<form action="{{ route('product-manager.products.store') }}" method="POST" enctype="multipart/form-data">
    @csrf

    <div class="row g-4">
        <!-- Left Column: Details -->
        <div class="col-lg-8">
            <!-- Specifications -->
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
                        <input type="text" name="name" id="product_name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name') }}" required placeholder="e.g. Wireless Noise-Cancelling Headphones">
                        @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <label class="form-label fw-bold small text-dark mb-0">SKU Code <span class="text-danger">*</span></label>
                                <button type="button" class="btn btn-link btn-sm p-0 text-decoration-none" style="font-size: 0.75rem;" onclick="generateSku()">
                                    Auto Generate
                                </button>
                            </div>
                            <input type="text" name="sku" id="product_sku" class="form-control text-uppercase font-monospace @error('sku') is-invalid @enderror" value="{{ old('sku') }}" placeholder="e.g. AUD-001">
                            @error('sku') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-bold small text-dark">Slug (Optional)</label>
                            <input type="text" name="slug" class="form-control @error('slug') is-invalid @enderror" value="{{ old('slug') }}" placeholder="Auto-generated from title">
                            @error('slug') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold small text-dark">Short Summary</label>
                        <textarea name="short_description" class="form-control @error('short_description') is-invalid @enderror" rows="2" placeholder="Brief summary for listings...">{{ old('short_description') }}</textarea>
                        @error('short_description') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="mb-0">
                        <label class="form-label fw-bold small text-dark">Full Description</label>
                        <textarea name="description" class="form-control @error('description') is-invalid @enderror" rows="5" placeholder="Specifications, warranty, box contents...">{{ old('description') }}</textarea>
                        @error('description') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                </div>
            </div>

            <!-- Pricing & Inventory -->
            <div class="card bg-white border-0 shadow-sm rounded-4 mb-4">
                <div class="card-header bg-white py-3 border-bottom">
                    <h6 class="mb-0 fw-bold text-dark">Pricing & Initial Inventory</h6>
                </div>
                <div class="card-body p-4">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold small text-dark">Base Price (₹) <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text bg-light text-muted">₹</span>
                                <input type="number" name="price" step="0.01" min="0" class="form-control fw-bold @error('price') is-invalid @enderror" value="{{ old('price') }}" required placeholder="1999.00">
                            </div>
                            @error('price') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-bold small text-dark">Initial Stock (Units) <span class="text-danger">*</span></label>
                            <input type="number" name="stock" min="0" class="form-control fw-bold @error('stock') is-invalid @enderror" value="{{ old('stock', 10) }}" required placeholder="50">
                            @error('stock') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Column: Taxonomy & Media -->
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
                            <option value="">Select Category</option>
                            @foreach($categories as $cat)
                                <option value="{{ $cat->id }}" {{ old('category_id') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                            @endforeach
                        </select>
                        @error('category_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold small text-dark">Brand <span class="text-danger">*</span></label>
                        <select name="brand_id" class="form-select @error('brand_id') is-invalid @enderror" required>
                            <option value="">Select Brand</option>
                            @foreach($brands as $b)
                                <option value="{{ $b->id }}" {{ old('brand_id') == $b->id ? 'selected' : '' }}>{{ $b->name }}</option>
                            @endforeach
                        </select>
                        @error('brand_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="pt-2 border-top">
                        <div class="form-check mb-2">
                            <input type="checkbox" name="featured" value="1" class="form-check-input" id="feat" {{ old('featured') ? 'checked' : '' }}>
                            <label class="form-check-label small fw-semibold text-dark" for="feat">Featured Item</label>
                        </div>
                        <div class="form-check">
                            <input type="checkbox" name="trending" value="1" class="form-check-input" id="trend" {{ old('trending') ? 'checked' : '' }}>
                            <label class="form-check-label small fw-semibold text-dark" for="trend">Trending Item</label>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Media Uploads -->
            <div class="card bg-white border-0 shadow-sm rounded-4 mb-4">
                <div class="card-header bg-white py-3 border-bottom">
                    <h6 class="mb-0 fw-bold text-dark">Product Images</h6>
                </div>
                <div class="card-body p-4">
                    <div class="mb-3">
                        <label class="form-label fw-bold small text-dark">Main Hero Photo <span class="text-danger">*</span></label>
                        <input type="file" name="main_image" class="form-control @error('main_image') is-invalid @enderror" accept="image/*" required>
                        @error('main_image') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="mb-0">
                        <label class="form-label fw-bold small text-dark">Gallery Images</label>
                        <input type="file" name="gallery_images[]" multiple class="form-control" accept="image/*">
                        <small class="text-muted" style="font-size: 0.72rem;">Optional additional photos.</small>
                    </div>
                </div>
            </div>

            <!-- Submit Card -->
            <div class="card bg-white border-0 shadow-sm rounded-4 p-3">
                <button type="submit" class="btn btn-pm-primary w-100 py-2.5 fw-bold rounded-pill shadow-sm">
                    Submit for Approval
                </button>
            </div>
        </div>
    </div>
</form>

<script>
function generateSku() {
    const chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
    let sku = 'SKU-';
    for (let i = 0; i < 8; i++) {
        sku += chars.charAt(Math.floor(Math.random() * chars.length));
    }
    document.getElementById('product_sku').value = sku;
}
</script>
@endsection
