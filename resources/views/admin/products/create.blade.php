@extends('admin.layouts.app')

@section('header', 'Add New Product')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item"><a href="{{ route('admin.products.index') }}">Products</a></li>
    <li class="breadcrumb-item active" aria-current="page">Create</li>
@endsection

@section('actions')
    <a href="{{ route('admin.products.index') }}" class="btn btn-outline-secondary rounded-pill px-3">
        <i class="bi bi-arrow-left me-1"></i> Back to Products
    </a>
@endsection

@section('content')
<form action="{{ route('admin.products.store') }}" method="POST" enctype="multipart/form-data" id="productForm">
    @csrf

    <div class="row g-4">
        <!-- Left Column: Product Info & Pricing -->
        <div class="col-lg-8">
            <!-- 1. Basic Information -->
            <div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-4">
                <div class="card-header bg-white py-3 border-bottom">
                    <h6 class="mb-0 fw-bold text-dark">
                        <i class="bi bi-box-seam text-primary me-2"></i>1. Basic Information
                    </h6>
                </div>
                <div class="card-body p-4">
                    <div class="mb-3">
                        <label class="form-label fw-bold text-dark small">Product Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" id="product_name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name') }}" required placeholder="e.g. Wireless Noise-Cancelling Headphones Pro">
                        @error('name') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <label class="form-label fw-bold text-dark small mb-0">SKU Code <span class="text-danger">*</span></label>
                                <button type="button" class="btn btn-link btn-sm text-decoration-none p-0" style="font-size: 0.75rem;" onclick="generateSku()">
                                    <i class="bi bi-shuffle me-1"></i>Auto SKU
                                </button>
                            </div>
                            <div class="input-group">
                                <span class="input-group-text bg-light text-muted border-end-0 font-monospace">#</span>
                                <input type="text" name="sku" id="product_sku" class="form-control font-monospace border-start-0 ps-0 text-uppercase @error('sku') is-invalid @enderror" value="{{ old('sku') }}" required placeholder="e.g. AUD-WNC-001">
                            </div>
                            @error('sku') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-bold text-dark small">Slug (Optional)</label>
                            <input type="text" name="slug" class="form-control @error('slug') is-invalid @enderror" value="{{ old('slug') }}" placeholder="Auto-generated if left empty">
                            <div class="form-text text-muted" style="font-size: 0.72rem;">Custom URL slug for product page.</div>
                            @error('slug') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold text-dark small">Short Summary (Highlights)</label>
                        <textarea name="short_description" class="form-control @error('short_description') is-invalid @enderror" rows="2" placeholder="Brief 1-2 sentence overview shown in product search & listings...">{{ old('short_description') }}</textarea>
                        @error('short_description') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                    </div>

                    <div class="mb-0">
                        <label class="form-label fw-bold text-dark small">Full Product Description</label>
                        <textarea name="description" class="form-control @error('description') is-invalid @enderror" rows="5" placeholder="Detailed product specifications, box contents, warranty details...">{{ old('description') }}</textarea>
                        @error('description') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                    </div>
                </div>
            </div>

            <!-- 2. Pricing & Inventory -->
            <div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-4">
                <div class="card-header bg-white py-3 border-bottom">
                    <h6 class="mb-0 fw-bold text-dark">
                        <i class="bi bi-currency-rupee text-primary me-2"></i>2. Pricing & Initial Inventory
                    </h6>
                </div>
                <div class="card-body p-4">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold text-dark small">Base Price <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text bg-light text-muted">₹</span>
                                <input type="number" name="price" step="0.01" min="0" class="form-control fw-bold @error('price') is-invalid @enderror" value="{{ old('price') }}" required placeholder="e.g. 2499.00">
                            </div>
                            <div class="form-text text-muted" style="font-size: 0.72rem;">Customer price before dynamic promotional offers.</div>
                            @error('price') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-bold text-dark small">Stock Inventory <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text bg-light text-muted"><i class="bi bi-boxes"></i></span>
                                <input type="number" name="stock" min="0" class="form-control fw-bold @error('stock') is-invalid @enderror" value="{{ old('stock', 10) }}" required placeholder="e.g. 50">
                                <span class="input-group-text bg-light text-muted small">Units</span>
                            </div>
                            <div class="form-text text-muted" style="font-size: 0.72rem;">Available quantity for immediate dispatch.</div>
                            @error('stock') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                        </div>
                    </div>
                </div>
            </div>

            <!-- 3. Product Gallery Images -->
            <div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-4">
                <div class="card-header bg-white py-3 border-bottom">
                    <h6 class="mb-0 fw-bold text-dark">
                        <i class="bi bi-images text-primary me-2"></i>3. Product Photo Gallery
                    </h6>
                </div>
                <div class="card-body p-4">
                    <label class="form-label fw-bold text-dark small">Upload Additional Angles & Lifestyle Images</label>
                    <input type="file" name="gallery_images[]" id="gallery_input" class="form-control @error('gallery_images.*') is-invalid @enderror" multiple accept="image/*" onchange="previewGalleryImages(this)">
                    <div class="form-text text-muted mb-3" style="font-size: 0.72rem;">Hold Ctrl/Cmd to select multiple images (JPG, PNG, WebP up to 2MB).</div>
                    @error('gallery_images.*') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror

                    <div class="row g-2 mt-2" id="gallery_preview_row"></div>
                </div>
            </div>
        </div>

        <!-- Right Column: Categorization, Media & Flags -->
        <div class="col-lg-4">
            <!-- Organization & Status -->
            <div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-4">
                <div class="card-header bg-white py-3 border-bottom">
                    <h6 class="mb-0 fw-bold text-dark">
                        <i class="bi bi-tags text-primary me-2"></i>Catalog & Flags
                    </h6>
                </div>
                <div class="card-body p-4">
                    <div class="mb-3">
                        <label class="form-label fw-bold text-dark small">Category <span class="text-danger">*</span></label>
                        <select name="category_id" id="category_id" class="form-select @error('category_id') is-invalid @enderror" required>
                            <option value="">Select Category</option>
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}" {{ old('category_id') == $category->id ? 'selected' : '' }}>
                                    {{ $category->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('category_id') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold text-dark small">Brand <span class="text-danger">*</span></label>
                        <select name="brand_id" id="brand_id" class="form-select @error('brand_id') is-invalid @enderror" required>
                            <option value="">Select Brand</option>
                            @foreach($brands as $brand)
                                <option value="{{ $brand->id }}" data-category-id="{{ $brand->category_id }}" {{ old('brand_id') == $brand->id ? 'selected' : '' }}>
                                    {{ $brand->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('brand_id') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold text-dark small">Publish Status <span class="text-danger">*</span></label>
                        <select name="status" class="form-select @error('status') is-invalid @enderror" required>
                            <option value="Active" {{ old('status', 'Active') === 'Active' ? 'selected' : '' }}>Active (Visible in Store)</option>
                            <option value="Inactive" {{ old('status') === 'Inactive' ? 'selected' : '' }}>Inactive (Hidden)</option>
                        </select>
                        @error('status') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                    </div>

                    <hr class="my-3">

                    <div class="form-check form-switch mb-2">
                        <input class="form-check-input" type="checkbox" name="featured" id="featured" value="1" {{ old('featured') ? 'checked' : '' }}>
                        <label class="form-check-label fw-semibold text-dark small" for="featured">
                            <i class="bi bi-star-fill text-warning me-1"></i>Featured Product
                        </label>
                    </div>

                    <div class="form-check form-switch mb-0">
                        <input class="form-check-input" type="checkbox" name="trending" id="trending" value="1" {{ old('trending') ? 'checked' : '' }}>
                        <label class="form-check-label fw-semibold text-dark small" for="trending">
                            <i class="bi bi-graph-up-arrow text-primary me-1"></i>Trending Product
                        </label>
                    </div>
                </div>
            </div>

            <!-- Primary Featured Image -->
            <div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-4">
                <div class="card-header bg-white py-3 border-bottom">
                    <h6 class="mb-0 fw-bold text-dark">
                        <i class="bi bi-card-image text-primary me-2"></i>Primary Product Image <span class="text-danger">*</span>
                    </h6>
                </div>
                <div class="card-body p-4 text-center">
                    <input type="file" name="main_image" class="form-control @error('main_image') is-invalid @enderror" accept="image/*" onchange="previewMainImage(this)" required>
                    <div class="form-text text-muted" style="font-size: 0.72rem;">Square or portrait image on plain/white background.</div>
                    @error('main_image') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror

                    <div class="mt-3 p-3 bg-light rounded-3 border text-center">
                        <img id="mainImagePreview" src="#" alt="Preview" class="img-fluid rounded-2 d-none" style="max-height: 160px; object-fit: contain;">
                        <div id="mainImagePlaceholder" class="text-muted small py-2">
                            <i class="bi bi-image fs-2 d-block mb-1 text-secondary"></i>
                            <span>No Main Image Selected</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="d-grid gap-2">
                <button type="submit" class="btn btn-primary rounded-pill btn-lg fw-bold shadow-sm">
                    <i class="bi bi-check-circle me-1"></i> Save & Publish Product
                </button>
                <a href="{{ route('admin.products.index') }}" class="btn btn-light rounded-pill border">Cancel</a>
            </div>
        </div>
    </div>
</form>

@push('scripts')
<script>
    function generateSku() {
        const name = document.getElementById('product_name').value.trim();
        const prefix = name ? name.substring(0, 3).toUpperCase().replace(/[^A-Z]/g, 'PRD') : 'PRD';
        const randomNum = Math.floor(100 + Math.random() * 900);
        document.getElementById('product_sku').value = `${prefix}-${randomNum}`;
    }

    function previewMainImage(input) {
        if (input.files && input.files[0]) {
            const reader = new FileReader();
            reader.onload = function(e) {
                const preview = document.getElementById('mainImagePreview');
                const placeholder = document.getElementById('mainImagePlaceholder');
                preview.src = e.target.result;
                preview.classList.remove('d-none');
                placeholder.classList.add('d-none');
            };
            reader.readAsDataURL(input.files[0]);
        }
    }

    function previewGalleryImages(input) {
        const container = document.getElementById('gallery_preview_row');
        container.innerHTML = '';
        if (input.files) {
            Array.from(input.files).forEach(file => {
                const reader = new FileReader();
                reader.onload = function(e) {
                    const col = document.createElement('div');
                    col.className = 'col-3';
                    col.innerHTML = `<img src="${e.target.result}" class="rounded-2 border w-100" style="height: 60px; object-fit: cover;">`;
                    container.appendChild(col);
                };
                reader.readAsDataURL(file);
            });
        }
    }

    document.addEventListener('DOMContentLoaded', function() {
        const categorySelect = document.getElementById('category_id');
        const brandSelect = document.getElementById('brand_id');
        const allBrandOptions = Array.from(brandSelect.options).filter(opt => opt.value !== "");

        function updateBrands() {
            const selectedCategoryId = categorySelect.value;
            allBrandOptions.forEach(option => {
                if (!selectedCategoryId || option.getAttribute('data-category-id') === selectedCategoryId) {
                    option.style.display = '';
                } else {
                    option.style.display = 'none';
                    if (brandSelect.value === option.value) {
                        brandSelect.value = '';
                    }
                }
            });
        }

        categorySelect.addEventListener('change', updateBrands);
        updateBrands();
    });
</script>
@endpush
@endsection
