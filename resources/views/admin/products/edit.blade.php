@extends('admin.layouts.app')

@section('header', 'Edit Product')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item"><a href="{{ route('admin.products.index') }}">Products</a></li>
    <li class="breadcrumb-item active" aria-current="page">Edit {{ $product->name }}</li>
@endsection

@section('actions')
    <div class="btn-group gap-2">
        <a href="{{ route('admin.products.index') }}" class="btn btn-outline-secondary rounded-pill px-3">
            <i class="bi bi-arrow-left me-1"></i> Back to Products
        </a>
        <a href="{{ route('admin.products.show', $product) }}" class="btn btn-outline-primary rounded-pill px-3">
            <i class="bi bi-eye me-1"></i> View Details
        </a>
    </div>
@endsection

@section('content')
<form action="{{ route('admin.products.update', $product) }}" method="POST" enctype="multipart/form-data" id="productForm">
    @csrf
    @method('PUT')

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
                        <input type="text" name="name" id="product_name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $product->name) }}" required>
                        @error('name') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold text-dark small">SKU Code <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text bg-light text-muted border-end-0 font-monospace">#</span>
                                <input type="text" name="sku" class="form-control font-monospace border-start-0 ps-0 text-uppercase @error('sku') is-invalid @enderror" value="{{ old('sku', $product->sku) }}" required>
                            </div>
                            @error('sku') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-bold text-dark small">Slug (URL)</label>
                            <input type="text" name="slug" class="form-control @error('slug') is-invalid @enderror" value="{{ old('slug', $product->slug) }}">
                            <div class="form-text text-muted" style="font-size: 0.72rem;">Custom URL slug for product page.</div>
                            @error('slug') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold text-dark small">Short Summary (Highlights)</label>
                        <textarea name="short_description" class="form-control @error('short_description') is-invalid @enderror" rows="2">{{ old('short_description', $product->short_description) }}</textarea>
                        @error('short_description') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                    </div>

                    <div class="mb-0">
                        <label class="form-label fw-bold text-dark small">Full Product Description</label>
                        <textarea name="description" class="form-control @error('description') is-invalid @enderror" rows="5">{{ old('description', $product->description) }}</textarea>
                        @error('description') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                    </div>
                </div>
            </div>

            <!-- 2. Pricing & Inventory -->
            <div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-4">
                <div class="card-header bg-white py-3 border-bottom">
                    <h6 class="mb-0 fw-bold text-dark">
                        <i class="bi bi-currency-rupee text-primary me-2"></i>2. Pricing & Inventory
                    </h6>
                </div>
                <div class="card-body p-4">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label fw-bold text-dark small">Cost Price (CP)</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light text-muted">₹</span>
                                <input type="number" name="cost_price" step="0.01" min="0" class="form-control @error('cost_price') is-invalid @enderror" value="{{ old('cost_price', $product->cost_price) }}" placeholder="e.g. 1800.00">
                            </div>
                            <div class="form-text text-muted" style="font-size: 0.72rem;">Procurement / wholesale cost (internal only).</div>
                            @error('cost_price') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-md-4">
                            <label class="form-label fw-bold text-dark small">Selling Price (SP) <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text bg-light text-muted">₹</span>
                                <input type="number" name="price" step="0.01" min="0" class="form-control fw-bold @error('price') is-invalid @enderror" value="{{ old('price', $product->price) }}" required>
                            </div>
                            <div class="form-text text-muted" style="font-size: 0.72rem;">Customer price before offers.</div>
                            @error('price') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-md-4">
                            <label class="form-label fw-bold text-dark small">Stock Inventory <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text bg-light text-muted"><i class="bi bi-boxes"></i></span>
                                <input type="number" name="stock" min="0" class="form-control fw-bold @error('stock') is-invalid @enderror" value="{{ old('stock', $product->stock) }}" required>
                                <span class="input-group-text bg-light text-muted small">Units</span>
                            </div>
                            @error('stock') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                        </div>
                    </div>
                </div>
            </div>

            <!-- 3. Product Options & Stock Matrix (Sizes / Colors) -->
            <div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-4">
                <div class="card-header bg-white py-3 border-bottom d-flex align-items-center justify-content-between">
                    <h6 class="mb-0 fw-bold text-dark">
                        <i class="bi bi-tag-fill text-primary me-2"></i>3. Product Sizes & Options Matrix
                    </h6>
                    <div class="form-check form-switch mb-0">
                        <input class="form-check-input" type="checkbox" name="has_options" id="has_options_switch" value="1" {{ old('has_options', $product->has_options) ? 'checked' : '' }} onchange="toggleOptionMatrix()">
                        <label class="form-check-label fw-bold small text-dark" for="has_options_switch">Enable Sizes / Colors</label>
                    </div>
                </div>
                <div class="card-body p-4" id="options_matrix_section" style="{{ old('has_options', $product->has_options) ? '' : 'display: none;' }}">
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold text-dark small">Option Category / Type</label>
                            <select name="option_type" id="option_type_select" class="form-select fw-semibold" onchange="applyPresetOptions()">
                                <option value="size" {{ old('option_type', $product->option_type ?? 'size') === 'size' ? 'selected' : '' }}>👕 Clothing Sizes (S, M, L, XL, XXL)</option>
                                <option value="waist" {{ old('option_type', $product->option_type) === 'waist' ? 'selected' : '' }}>👖 Waist Sizes (28, 30, 32, 34, 36)</option>
                                <option value="color" {{ old('option_type', $product->option_type) === 'color' ? 'selected' : '' }}>🎨 Colors (Black, Blue, Red, White)</option>
                                <option value="custom" {{ old('option_type', $product->option_type) === 'custom' ? 'selected' : '' }}>➕ Custom Options</option>
                            </select>
                        </div>
                        <div class="col-md-6 d-flex align-items-end">
                            <button type="button" class="btn btn-sm btn-outline-primary rounded-pill px-3" onclick="addOptionRow()">
                                <i class="bi bi-plus-lg me-1"></i> Add Option Row
                            </button>
                        </div>
                    </div>

                    <div class="table-responsive border rounded-3 overflow-hidden mb-2">
                        <table class="table table-hover align-middle mb-0" id="options_table">
                            <thead class="table-light">
                                <tr>
                                    <th class="ps-3" style="width: 50%;">Option Value (Size / Color)</th>
                                    <th style="width: 40%;">Stock Available</th>
                                    <th class="text-center" style="width: 10%;">Action</th>
                                </tr>
                            </thead>
                            <tbody id="options_rows">
                                @php
                                    $existingOptions = old('option_keys', []);
                                    $existingValues = old('option_values', []);
                                    if (empty($existingOptions) && $product->has_options && !empty($product->option_stocks)) {
                                        $stocksMap = is_array($product->option_stocks) ? $product->option_stocks : json_decode($product->option_stocks, true);
                                        $existingOptions = array_keys($stocksMap);
                                        $existingValues = array_values($stocksMap);
                                    }
                                @endphp
                                @forelse($existingOptions as $idx => $optKey)
                                    <tr>
                                        <td class="ps-3">
                                            <input type="text" name="option_keys[]" class="form-control form-control-sm fw-bold" value="{{ $optKey }}" placeholder="e.g. Size M" required>
                                        </td>
                                        <td>
                                            <input type="number" name="option_values[]" min="0" class="form-control form-control-sm fw-bold option-qty-input" value="{{ $existingValues[$idx] ?? 0 }}" required onchange="calculateTotalStockFromOptions()">
                                        </td>
                                        <td class="text-center">
                                            <button type="button" class="btn btn-sm btn-outline-danger border-0 rounded-circle p-1" onclick="removeOptionRow(this)"><i class="bi bi-trash-fill"></i></button>
                                        </td>
                                    </tr>
                                @empty
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <div class="form-text text-muted" style="font-size: 0.72rem;">Updating option stocks automatically updates the total product inventory quantity.</div>
                </div>
            </div>

            <!-- 4. Logistics Package & GST Tax Settings -->
            <div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-4">
                <div class="card-header bg-white py-3 border-bottom">
                    <h6 class="mb-0 fw-bold text-dark">
                        <i class="bi bi-truck text-primary me-2"></i>4. iThink Logistics & GST Tax Settings
                    </h6>
                </div>
                <div class="card-body p-4">
                    <div class="row g-3">
                        <div class="col-md-3">
                            <label class="form-label fw-bold text-dark small">Package Weight (kg)</label>
                            <div class="input-group input-group-sm">
                                <input type="number" name="weight" step="0.01" min="0" class="form-control fw-bold" value="{{ old('weight', $product->weight ?? 0.50) }}" placeholder="0.50">
                                <span class="input-group-text bg-light">kg</span>
                            </div>
                            <div class="form-text text-muted" style="font-size: 0.7rem;">Default fallback: 0.50 kg</div>
                        </div>

                        <div class="col-md-3">
                            <label class="form-label fw-bold text-dark small">Length (cm)</label>
                            <div class="input-group input-group-sm">
                                <input type="number" name="length" step="0.01" min="0" class="form-control fw-bold" value="{{ old('length', $product->length ?? 15.00) }}" placeholder="15.00">
                                <span class="input-group-text bg-light">cm</span>
                            </div>
                        </div>

                        <div class="col-md-3">
                            <label class="form-label fw-bold text-dark small">Width (cm)</label>
                            <div class="input-group input-group-sm">
                                <input type="number" name="width" step="0.01" min="0" class="form-control fw-bold" value="{{ old('width', $product->width ?? 10.00) }}" placeholder="10.00">
                                <span class="input-group-text bg-light">cm</span>
                            </div>
                        </div>

                        <div class="col-md-3">
                            <label class="form-label fw-bold text-dark small">Height (cm)</label>
                            <div class="input-group input-group-sm">
                                <input type="number" name="height" step="0.01" min="0" class="form-control fw-bold" value="{{ old('height', $product->height ?? 5.00) }}" placeholder="5.00">
                                <span class="input-group-text bg-light">cm</span>
                            </div>
                        </div>

                        <div class="col-md-6 mt-3">
                            <label class="form-label fw-bold text-dark small">HSN Code (GST Invoice)</label>
                            <input type="text" name="hsn_code" class="form-control form-control-sm fw-bold" value="{{ old('hsn_code', $product->hsn_code ?? '8518') }}" placeholder="e.g. 8518 / 6109">
                            <div class="form-text text-muted" style="font-size: 0.7rem;">Harmonized System Nomenclature code</div>
                        </div>

                        <div class="col-md-6 mt-3">
                            <label class="form-label fw-bold text-dark small">GST Tax Rate (%)</label>
                            <select name="tax_rate" class="form-select form-select-sm fw-semibold">
                                <option value="18.00" {{ old('tax_rate', $product->tax_rate ?? 18.00) == 18.00 ? 'selected' : '' }}>18% GST (Standard Goods & Electronics)</option>
                                <option value="12.00" {{ old('tax_rate', $product->tax_rate) == 12.00 ? 'selected' : '' }}>12% GST (Apparel > ₹1,000 / Handicrafts)</option>
                                <option value="5.00" {{ old('tax_rate', $product->tax_rate) == 5.00 ? 'selected' : '' }}>5% GST (Apparel < ₹1,000 / Essentials)</option>
                                <option value="28.00" {{ old('tax_rate', $product->tax_rate) == 28.00 ? 'selected' : '' }}>28% GST (Luxury Items)</option>
                                <option value="0.00" {{ old('tax_rate', $product->tax_rate) == 0.00 ? 'selected' : '' }}>0% Exempt</option>
                            </select>
                            <div class="form-text text-muted" style="font-size: 0.7rem;">Prices are GST inclusive on storefront</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 3. Product Gallery Images -->
            <div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-4">
                <div class="card-header bg-white py-3 border-bottom d-flex align-items-center justify-content-between">
                    <h6 class="mb-0 fw-bold text-dark">
                        <i class="bi bi-images text-primary me-2"></i>3. Product Photo Gallery
                    </h6>
                    <span class="badge bg-light text-secondary border px-2.5 py-1 rounded-pill small">
                        {{ $product->galleryImages->count() }} Attached
                    </span>
                </div>
                <div class="card-body p-4">
                    <label class="form-label fw-bold text-dark small">Upload More Gallery Images</label>
                    <input type="file" name="gallery_images[]" class="form-control @error('gallery_images.*') is-invalid @enderror" multiple accept="image/*">
                    <div class="form-text text-muted mb-3" style="font-size: 0.72rem;">Hold Ctrl/Cmd to add multiple photos to the gallery.</div>
                    @error('gallery_images.*') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror

                    @if($product->galleryImages->count() > 0)
                        <div class="row g-3 mt-1">
                            @foreach($product->galleryImages as $image)
                                <div class="col-6 col-sm-4 col-md-3" id="gallery-image-{{ $image->id }}">
                                    <div class="position-relative border rounded-3 overflow-hidden shadow-xs">
                                        <img src="{{ asset('storage/' . $image->image) }}" class="w-100" style="height: 120px; object-fit: cover;">
                                        <button type="button" class="btn btn-danger btn-sm position-absolute top-0 end-0 m-1 rounded-circle p-0 d-flex align-items-center justify-content-center shadow" style="width: 26px; height: 26px;" onclick="deleteGalleryImage({{ $image->id }})" title="Delete Image">
                                            <i class="bi bi-trash-fill" style="font-size: 0.7rem;"></i>
                                        </button>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
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
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}" {{ old('category_id', $product->category_id) == $category->id ? 'selected' : '' }}>
                                    {{ $category->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('category_id') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold text-dark small">Brand <span class="text-danger">*</span></label>
                        <select name="brand_id" id="brand_id" class="form-select @error('brand_id') is-invalid @enderror" required>
                            @foreach($brands as $brand)
                                <option value="{{ $brand->id }}" data-category-id="{{ $brand->category_id }}" {{ old('brand_id', $product->brand_id) == $brand->id ? 'selected' : '' }}>
                                    {{ $brand->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('brand_id') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold text-dark small">Publish Status <span class="text-danger">*</span></label>
                        <select name="status" class="form-select @error('status') is-invalid @enderror" required>
                            <option value="Active" {{ old('status', $product->status) === 'Active' ? 'selected' : '' }}>Active (Visible in Store)</option>
                            <option value="Inactive" {{ old('status', $product->status) === 'Inactive' ? 'selected' : '' }}>Inactive (Hidden)</option>
                        </select>
                        @error('status') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                    </div>

                    <hr class="my-3">

                    <div class="form-check form-switch mb-2">
                        <input class="form-check-input" type="checkbox" name="featured" id="featured" value="1" {{ old('featured', $product->featured) ? 'checked' : '' }}>
                        <label class="form-check-label fw-semibold text-dark small" for="featured">
                            <i class="bi bi-star-fill text-warning me-1"></i>Featured Product
                        </label>
                    </div>

                    <div class="form-check form-switch mb-0">
                        <input class="form-check-input" type="checkbox" name="trending" id="trending" value="1" {{ old('trending', $product->trending) ? 'checked' : '' }}>
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
                        <i class="bi bi-card-image text-primary me-2"></i>Primary Product Image
                    </h6>
                </div>
                <div class="card-body p-4 text-center">
                    <input type="file" name="main_image" class="form-control @error('main_image') is-invalid @enderror" accept="image/*" onchange="previewMainImage(this)">
                    <div class="form-text text-muted" style="font-size: 0.72rem;">Leave empty to keep existing main image.</div>
                    @error('main_image') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror

                    <div class="mt-3 p-3 bg-light rounded-3 border text-center">
                        <img id="mainImagePreview" src="{{ asset('storage/' . $product->main_image) }}" alt="Preview" class="img-fluid rounded-2" style="max-height: 160px; object-fit: contain;">
                    </div>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="d-grid gap-2">
                <button type="submit" class="btn btn-primary rounded-pill btn-lg fw-bold shadow-sm">
                    <i class="bi bi-check-circle me-1"></i> Update Product
                </button>
                <a href="{{ route('admin.products.index') }}" class="btn btn-light rounded-pill border">Cancel</a>
            </div>
        </div>
    </div>
</form>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    function previewMainImage(input) {
        if (input.files && input.files[0]) {
            var reader = new FileReader();
            reader.onload = function(e) {
                document.getElementById('mainImagePreview').src = e.target.result;
            }
            reader.readAsDataURL(input.files[0]);
        }
    }

    function deleteGalleryImage(id) {
        Swal.fire({
            title: 'Delete this image?',
            text: "Are you sure you want to remove this gallery image?",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#ef4444',
            cancelButtonColor: '#64748b',
            confirmButtonText: 'Yes, Delete',
            cancelButtonText: 'Cancel'
        }).then((result) => {
            if (result.isConfirmed) {
                fetch(`/admin/products/gallery/${id}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Accept': 'application/json'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    const el = document.getElementById(`gallery-image-${id}`);
                    if (el) {
                        el.style.transition = 'all 0.3s ease';
                        el.style.opacity = '0';
                        setTimeout(() => el.remove(), 300);
                    }
                    Swal.fire({
                        icon: 'success',
                        title: 'Deleted',
                        text: 'Gallery image removed successfully.',
                        toast: true,
                        position: 'top-end',
                        showConfirmButton: false,
                        timer: 2500
                    });
                })
                .catch(error => {
                    console.error('Error:', error);
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Failed to delete gallery image.',
                        toast: true,
                        position: 'top-end',
                        showConfirmButton: false,
                        timer: 3000
                    });
                });
            }
        });
    }

    function toggleOptionMatrix() {
        const sw = document.getElementById('has_options_switch');
        const sec = document.getElementById('options_matrix_section');
        if (sw && sec) {
            sec.style.display = sw.checked ? 'block' : 'none';
            if (sw.checked && document.querySelectorAll('#options_rows tr').length === 0) {
                applyPresetOptions();
            }
        }
    }

    function applyPresetOptions() {
        const type = document.getElementById('option_type_select').value;
        const tbody = document.getElementById('options_rows');
        if (!tbody) return;
        
        let presets = [];
        if (type === 'size') {
            presets = [
                { key: 'S', qty: 5 },
                { key: 'M', qty: 20 },
                { key: 'L', qty: 15 },
                { key: 'XL', qty: 10 }
            ];
        } else if (type === 'waist') {
            presets = [
                { key: '28', qty: 5 },
                { key: '30', qty: 10 },
                { key: '32', qty: 15 },
                { key: '34', qty: 10 },
                { key: '36', qty: 5 }
            ];
        } else if (type === 'color') {
            presets = [
                { key: 'Black', qty: 20 },
                { key: 'Blue', qty: 15 },
                { key: 'White', qty: 10 }
            ];
        } else {
            presets = [{ key: 'Option 1', qty: 10 }];
        }

        tbody.innerHTML = '';
        presets.forEach(p => {
            const tr = document.createElement('tr');
            tr.innerHTML = `
                <td class="ps-3">
                    <input type="text" name="option_keys[]" class="form-control form-control-sm fw-bold" value="${p.key}" required>
                </td>
                <td>
                    <input type="number" name="option_values[]" min="0" class="form-control form-control-sm fw-bold option-qty-input" value="${p.qty}" required onchange="calculateTotalStockFromOptions()">
                </td>
                <td class="text-center">
                    <button type="button" class="btn btn-sm btn-outline-danger border-0 rounded-circle p-1" onclick="removeOptionRow(this)"><i class="bi bi-trash-fill"></i></button>
                </td>
            `;
            tbody.appendChild(tr);
        });
        calculateTotalStockFromOptions();
    }

    function addOptionRow() {
        const tbody = document.getElementById('options_rows');
        if (!tbody) return;
        const tr = document.createElement('tr');
        tr.innerHTML = `
            <td class="ps-3">
                <input type="text" name="option_keys[]" class="form-control form-control-sm fw-bold" placeholder="e.g. Size XXL" required>
            </td>
            <td>
                <input type="number" name="option_values[]" min="0" class="form-control form-control-sm fw-bold option-qty-input" value="10" required onchange="calculateTotalStockFromOptions()">
            </td>
            <td class="text-center">
                <button type="button" class="btn btn-sm btn-outline-danger border-0 rounded-circle p-1" onclick="removeOptionRow(this)"><i class="bi bi-trash-fill"></i></button>
            </td>
        `;
        tbody.appendChild(tr);
        calculateTotalStockFromOptions();
    }

    function removeOptionRow(btn) {
        const tr = btn.closest('tr');
        if (tr) {
            tr.remove();
            calculateTotalStockFromOptions();
        }
    }

    function calculateTotalStockFromOptions() {
        const sw = document.getElementById('has_options_switch');
        if (sw && sw.checked) {
            let total = 0;
            document.querySelectorAll('.option-qty-input').forEach(inp => {
                total += parseInt(inp.value) || 0;
            });
            const stockInput = document.querySelector('input[name="stock"]');
            if (stockInput) {
                stockInput.value = total;
            }
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
