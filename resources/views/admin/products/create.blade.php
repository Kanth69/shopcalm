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
                        <div class="col-md-4">
                            <label class="form-label fw-bold text-dark small">Cost Price (CP)</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light text-muted">₹</span>
                                <input type="number" name="cost_price" step="0.01" min="0" class="form-control @error('cost_price') is-invalid @enderror" value="{{ old('cost_price') }}" placeholder="e.g. 1800.00">
                            </div>
                            <div class="form-text text-muted" style="font-size: 0.72rem;">Procurement / wholesale cost (internal only).</div>
                            @error('cost_price') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-md-4">
                            <label class="form-label fw-bold text-dark small">Selling Price (SP) <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text bg-light text-muted">₹</span>
                                <input type="number" name="price" step="0.01" min="0" class="form-control fw-bold @error('price') is-invalid @enderror" value="{{ old('price') }}" required placeholder="e.g. 2499.00">
                            </div>
                            <div class="form-text text-muted" style="font-size: 0.72rem;">Customer price before offers.</div>
                            @error('price') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-md-4">
                            <label class="form-label fw-bold text-dark small">Stock Inventory <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text bg-light text-muted"><i class="bi bi-boxes"></i></span>
                                <input type="number" name="stock" min="0" class="form-control fw-bold @error('stock') is-invalid @enderror" value="{{ old('stock', 10) }}" required placeholder="e.g. 50">
                                <span class="input-group-text bg-light text-muted small">Units</span>
                            </div>
                            <div class="form-text text-muted" style="font-size: 0.72rem;">Available quantity for dispatch.</div>
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
                        <input class="form-check-input" type="checkbox" name="has_options" id="has_options_switch" value="1" {{ old('has_options') ? 'checked' : '' }} onchange="toggleOptionMatrix()">
                        <label class="form-check-label fw-bold small text-dark" for="has_options_switch">Enable Sizes / Colors</label>
                    </div>
                </div>
                <div class="card-body p-4" id="options_matrix_section" style="{{ old('has_options') ? '' : 'display: none;' }}">
                    <div class="row g-3 mb-3">
                        <div class="col-md-7">
                            <label class="form-label fw-bold text-dark small">Option Category / Type</label>
                            <select name="option_type" id="option_type_select" class="form-select fw-semibold" onchange="applyPresetOptions()">
                                <option value="size" {{ old('option_type', 'size') === 'size' ? 'selected' : '' }}>👕 Clothing Sizes Only (S, M, L, XL, XXL)</option>
                                <option value="waist" {{ old('option_type') === 'waist' ? 'selected' : '' }}>👖 Waist Sizes Only (28, 30, 32, 34, 36)</option>
                                <option value="color" {{ old('option_type') === 'color' ? 'selected' : '' }}>🎨 Colors Only (Black, Blue, Red, White)</option>
                                <option value="size_color" {{ old('option_type') === 'size_color' ? 'selected' : '' }}>👕🎨 Color + Clothing Size (e.g. Blue - S, Blue - M)</option>
                                <option value="waist_color" {{ old('option_type') === 'waist_color' ? 'selected' : '' }}>👖🎨 Color + Waist Size (e.g. Blue - 30, Black - 32)</option>
                                <option value="custom" {{ old('option_type') === 'custom' ? 'selected' : '' }}>➕ Custom Options</option>
                            </select>
                        </div>
                        <div class="col-md-5 d-flex align-items-end">
                            <button type="button" class="btn btn-sm btn-outline-primary rounded-pill px-3" onclick="addOptionRow()">
                                <i class="bi bi-plus-lg me-1"></i> Add Variant Row
                            </button>
                        </div>
                    </div>

                    <div class="table-responsive border rounded-3 overflow-hidden mb-2">
                        <table class="table table-hover align-middle mb-0" id="options_table">
                            <thead class="table-light">
                                <tr>
                                    <th class="ps-3" id="option_col_header" style="width: 55%;">Variant Value (Size / Color)</th>
                                    <th style="width: 35%;">Stock Available</th>
                                    <th class="text-center" style="width: 10%;">Action</th>
                                </tr>
                            </thead>
                            <tbody id="options_rows">
                                @php
                                    $existingOptions = old('option_keys', []);
                                    $existingValues = old('option_values', []);
                                    $isDoubleOpt = in_array(old('option_type'), ['size_color', 'waist_color']);
                                @endphp
                                @forelse($existingOptions as $idx => $optKey)
                                    @php
                                        $parts = explode(' - ', $optKey, 2);
                                        $cPart = $parts[0] ?? '';
                                        $sPart = $parts[1] ?? '';
                                    @endphp
                                    <tr>
                                        <td class="ps-3">
                                            @if($isDoubleOpt || str_contains($optKey, ' - '))
                                                <div class="d-flex align-items-center gap-2">
                                                    <input type="text" class="form-control form-control-sm fw-bold opt-color-part" value="{{ $cPart }}" placeholder="Color (e.g. Blue)" required oninput="syncDoubleOptionRow(this)">
                                                    <span class="text-muted fw-bold">-</span>
                                                    <input type="text" class="form-control form-control-sm fw-bold opt-size-part" value="{{ $sPart }}" placeholder="Size (e.g. S)" required oninput="syncDoubleOptionRow(this)">
                                                    <input type="hidden" name="option_keys[]" class="opt-combined-key" value="{{ $optKey }}">
                                                </div>
                                            @else
                                                <input type="text" name="option_keys[]" class="form-control form-control-sm fw-bold" value="{{ $optKey }}" placeholder="e.g. Size M" required>
                                            @endif
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
                                <input type="number" name="weight" step="0.01" min="0" class="form-control fw-bold" value="{{ old('weight', 0.50) }}" placeholder="0.50">
                                <span class="input-group-text bg-light">kg</span>
                            </div>
                            <div class="form-text text-muted" style="font-size: 0.7rem;">Default fallback: 0.50 kg</div>
                        </div>

                        <div class="col-md-3">
                            <label class="form-label fw-bold text-dark small">Length (cm)</label>
                            <div class="input-group input-group-sm">
                                <input type="number" name="length" step="0.01" min="0" class="form-control fw-bold" value="{{ old('length', 15.00) }}" placeholder="15.00">
                                <span class="input-group-text bg-light">cm</span>
                            </div>
                        </div>

                        <div class="col-md-3">
                            <label class="form-label fw-bold text-dark small">Width (cm)</label>
                            <div class="input-group input-group-sm">
                                <input type="number" name="width" step="0.01" min="0" class="form-control fw-bold" value="{{ old('width', 10.00) }}" placeholder="10.00">
                                <span class="input-group-text bg-light">cm</span>
                            </div>
                        </div>

                        <div class="col-md-3">
                            <label class="form-label fw-bold text-dark small">Height (cm)</label>
                            <div class="input-group input-group-sm">
                                <input type="number" name="height" step="0.01" min="0" class="form-control fw-bold" value="{{ old('height', 5.00) }}" placeholder="5.00">
                                <span class="input-group-text bg-light">cm</span>
                            </div>
                        </div>

                        <div class="col-md-6 mt-3">
                            <label class="form-label fw-bold text-dark small">HSN Code (GST Invoice)</label>
                            <input type="text" name="hsn_code" class="form-control form-control-sm fw-bold" value="{{ old('hsn_code', '8518') }}" placeholder="e.g. 8518 / 6109">
                            <div class="form-text text-muted" style="font-size: 0.7rem;">Harmonized System Nomenclature code</div>
                        </div>

                        <div class="col-md-6 mt-3">
                            <label class="form-label fw-bold text-dark small">GST Tax Rate (%)</label>
                            <select name="tax_rate" class="form-select form-select-sm fw-semibold">
                                <option value="18.00" {{ old('tax_rate', 18.00) == 18.00 ? 'selected' : '' }}>18% GST (Standard Goods & Electronics)</option>
                                <option value="12.00" {{ old('tax_rate') == 12.00 ? 'selected' : '' }}>12% GST (Apparel > ₹1,000 / Handicrafts)</option>
                                <option value="5.00" {{ old('tax_rate') == 5.00 ? 'selected' : '' }}>5% GST (Apparel < ₹1,000 / Essentials)</option>
                                <option value="28.00" {{ old('tax_rate') == 28.00 ? 'selected' : '' }}>28% GST (Luxury Items)</option>
                                <option value="0.00" {{ old('tax_rate') == 0.00 ? 'selected' : '' }}>0% Exempt</option>
                            </select>
                            <div class="form-text text-muted" style="font-size: 0.7rem;">Prices are GST inclusive on storefront</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 3. Product Gallery Images -->
            <div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-4">
                <div class="card-header bg-white py-3 border-bottom d-flex align-items-center justify-content-between flex-wrap gap-2">
                    <h6 class="mb-0 fw-bold text-dark">
                        <i class="bi bi-images text-primary me-2"></i>3. Product Photo Gallery
                    </h6>
                    <span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25 rounded-pill px-2.5 py-1">
                        <i class="bi bi-aspect-ratio me-1"></i> Standard Size: 1000 × 1000 px (1:1 Square)
                    </span>
                </div>
                <div class="card-body p-4">
                    <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-2">
                        <label class="form-label fw-bold text-dark small mb-0">Upload Additional Angles & Lifestyle Images</label>
                        <select id="image_fit_mode" class="form-select form-select-sm w-auto fw-semibold" style="font-size: 0.75rem;">
                            <option value="contain" selected>Auto-Fit in 1000×1000 White Square (No Cut)</option>
                            <option value="cover">Auto-Crop 1000×1000 Square (Edge-to-Edge)</option>
                        </select>
                    </div>
                    <input type="file" name="gallery_images[]" id="gallery_input" class="form-control @error('gallery_images.*') is-invalid @enderror" multiple accept="image/*" onchange="previewGalleryImages(this)">
                    <div class="form-text text-muted mb-3" style="font-size: 0.72rem;">Recommended size: <strong>1000 × 1000 px (1:1 Square)</strong>. Any uploaded photo is automatically standardized to 1000 × 1000 px so all photos display in the exact same size.</div>
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
                <div class="card-header bg-white py-3 border-bottom d-flex align-items-center justify-content-between">
                    <h6 class="mb-0 fw-bold text-dark">
                        <i class="bi bi-card-image text-primary me-2"></i>Primary Product Image <span class="text-danger">*</span>
                    </h6>
                    <span class="badge bg-light text-dark border rounded-pill" style="font-size: 0.68rem;">1000 × 1000 px</span>
                </div>
                <div class="card-body p-4 text-center">
                    <input type="file" name="main_image" class="form-control @error('main_image') is-invalid @enderror" accept="image/*" onchange="previewMainImage(this)" required>
                    <div class="form-text text-muted" style="font-size: 0.72rem;">Standard size: <strong>1000 × 1000 px (1:1 Square)</strong>. Auto-formatted on upload.</div>
                    @error('main_image') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror

                    <div class="mt-3 p-3 bg-light rounded-3 border text-center">
                        <img id="mainImagePreview" src="#" alt="Preview" class="img-fluid rounded-2 d-none" style="width: 160px; height: 160px; object-fit: contain; background: #fff;">
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

    function standardizeImageTo1000Square(file) {
        return new Promise((resolve) => {
            if (!file || !file.type.startsWith('image/')) {
                resolve({ file, dataUrl: '' });
                return;
            }
            const mode = document.getElementById('image_fit_mode')?.value || 'contain';
            const reader = new FileReader();
            reader.onload = function(evt) {
                const img = new Image();
                img.onload = function() {
                    const TARGET = 1000;
                    const canvas = document.createElement('canvas');
                    canvas.width = TARGET;
                    canvas.height = TARGET;
                    const ctx = canvas.getContext('2d');
                    ctx.fillStyle = '#FFFFFF';
                    ctx.fillRect(0, 0, TARGET, TARGET);

                    if (mode === 'cover') {
                        const scale = Math.max(TARGET / img.width, TARGET / img.height);
                        const w = img.width * scale;
                        const h = img.height * scale;
                        const x = (TARGET - w) / 2;
                        const y = (TARGET - h) / 2;
                        ctx.drawImage(img, x, y, w, h);
                    } else {
                        const pad = 20;
                        const avail = TARGET - (pad * 2);
                        const scale = Math.min(avail / img.width, avail / img.height);
                        const w = img.width * scale;
                        const h = img.height * scale;
                        const x = (TARGET - w) / 2;
                        const y = (TARGET - h) / 2;
                        ctx.drawImage(img, x, y, w, h);
                    }

                    const dataUrl = canvas.toDataURL('image/jpeg', 0.92);
                    canvas.toBlob((blob) => {
                        if (!blob) {
                            resolve({ file, dataUrl });
                            return;
                        }
                        const cleanName = file.name.replace(/\.[^/.]+$/, '') + '_1000x1000.jpg';
                        const newFile = new File([blob], cleanName, { type: 'image/jpeg', lastModified: Date.now() });
                        resolve({ file: newFile, dataUrl });
                    }, 'image/jpeg', 0.92);
                };
                img.onerror = () => resolve({ file, dataUrl: evt.target.result });
                img.src = evt.target.result;
            };
            reader.readAsDataURL(file);
        });
    }

    async function previewMainImage(input) {
        if (input.files && input.files[0]) {
            const res = await standardizeImageTo1000Square(input.files[0]);
            if (typeof DataTransfer !== 'undefined' && res.file) {
                const dt = new DataTransfer();
                dt.items.add(res.file);
                input.files = dt.files;
            }
            const preview = document.getElementById('mainImagePreview');
            const placeholder = document.getElementById('mainImagePlaceholder');
            if (preview && res.dataUrl) {
                preview.src = res.dataUrl;
                preview.classList.remove('d-none');
            }
            if (placeholder) placeholder.classList.add('d-none');
        }
    }

    async function previewGalleryImages(input) {
        const container = document.getElementById('gallery_preview_row');
        if (!container) return;
        container.innerHTML = '';
        if (input.files && input.files.length > 0) {
            const filesArr = Array.from(input.files);
            const dt = typeof DataTransfer !== 'undefined' ? new DataTransfer() : null;
            for (const f of filesArr) {
                const res = await standardizeImageTo1000Square(f);
                if (dt && res.file) dt.items.add(res.file);
                const col = document.createElement('div');
                col.className = 'col-3';
                col.innerHTML = `<img src="${res.dataUrl}" class="rounded-2 border w-100 bg-white" style="height: 68px; aspect-ratio: 1/1; object-fit: contain;">`;
                container.appendChild(col);
            }
            if (dt) input.files = dt.files;
        }
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

    function isDoubleVariantType(type) {
        return type === 'size_color' || type === 'waist_color';
    }

    function syncDoubleOptionRow(el) {
        const tr = el.closest('tr');
        if (!tr) return;
        const c = (tr.querySelector('.opt-color-part')?.value || '').trim();
        const s = (tr.querySelector('.opt-size-part')?.value || '').trim();
        const hidden = tr.querySelector('.opt-combined-key');
        if (hidden) {
            hidden.value = (c && s) ? `${c} - ${s}` : (c || s);
        }
    }

    function buildOptionRowHtml(type, key, qty) {
        if (isDoubleVariantType(type)) {
            const parts = (key || '').split(' - ');
            const colorVal = parts[0] || '';
            const sizeVal = parts[1] || '';
            const sizePlaceholder = type === 'waist_color' ? 'Waist (e.g. 32)' : 'Size (e.g. M)';
            const combined = (colorVal && sizeVal) ? `${colorVal} - ${sizeVal}` : key;
            return `
                <td class="ps-3">
                    <div class="d-flex align-items-center gap-2">
                        <input type="text" class="form-control form-control-sm fw-bold opt-color-part" value="${colorVal}" placeholder="Color (e.g. Blue)" required oninput="syncDoubleOptionRow(this)">
                        <span class="text-muted fw-bold">-</span>
                        <input type="text" class="form-control form-control-sm fw-bold opt-size-part" value="${sizeVal}" placeholder="${sizePlaceholder}" required oninput="syncDoubleOptionRow(this)">
                        <input type="hidden" name="option_keys[]" class="opt-combined-key" value="${combined}">
                    </div>
                </td>
                <td>
                    <input type="number" name="option_values[]" min="0" class="form-control form-control-sm fw-bold option-qty-input" value="${qty}" required onchange="calculateTotalStockFromOptions()">
                </td>
                <td class="text-center">
                    <button type="button" class="btn btn-sm btn-outline-danger border-0 rounded-circle p-1" onclick="removeOptionRow(this)"><i class="bi bi-trash-fill"></i></button>
                </td>
            `;
        }
        return `
            <td class="ps-3">
                <input type="text" name="option_keys[]" class="form-control form-control-sm fw-bold" value="${key}" placeholder="e.g. Size M" required>
            </td>
            <td>
                <input type="number" name="option_values[]" min="0" class="form-control form-control-sm fw-bold option-qty-input" value="${qty}" required onchange="calculateTotalStockFromOptions()">
            </td>
            <td class="text-center">
                <button type="button" class="btn btn-sm btn-outline-danger border-0 rounded-circle p-1" onclick="removeOptionRow(this)"><i class="bi bi-trash-fill"></i></button>
            </td>
        `;
    }

    function applyPresetOptions() {
        const type = document.getElementById('option_type_select').value;
        const tbody = document.getElementById('options_rows');
        const colHeader = document.getElementById('option_col_header');
        if (!tbody) return;
        if (colHeader) {
            colHeader.textContent = isDoubleVariantType(type) ? 'Color & Size Combination' : 'Variant Value (Size / Color)';
        }
        
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
        } else if (type === 'size_color') {
            presets = [
                { key: 'Blue - S', qty: 10 },
                { key: 'Blue - M', qty: 15 },
                { key: 'Blue - L', qty: 12 },
                { key: 'Blue - XL', qty: 8 },
                { key: 'Black - S', qty: 10 },
                { key: 'Black - M', qty: 15 },
                { key: 'Black - L', qty: 12 },
                { key: 'Black - XL', qty: 8 }
            ];
        } else if (type === 'waist_color') {
            presets = [
                { key: 'Blue - 30', qty: 10 },
                { key: 'Blue - 32', qty: 15 },
                { key: 'Blue - 34', qty: 10 },
                { key: 'Black - 30', qty: 10 },
                { key: 'Black - 32', qty: 15 },
                { key: 'Black - 34', qty: 10 }
            ];
        } else {
            presets = [{ key: 'Option 1', qty: 10 }];
        }

        tbody.innerHTML = '';
        presets.forEach(p => {
            const tr = document.createElement('tr');
            tr.innerHTML = buildOptionRowHtml(type, p.key, p.qty);
            tbody.appendChild(tr);
        });
        calculateTotalStockFromOptions();
    }

    function addOptionRow() {
        const tbody = document.getElementById('options_rows');
        const type = document.getElementById('option_type_select')?.value || 'size';
        if (!tbody) return;
        const tr = document.createElement('tr');
        tr.innerHTML = buildOptionRowHtml(type, '', 10);
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
