@extends('product-manager.layouts.app')

@section('header', 'Edit Product')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('product-manager.dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item"><a href="{{ route('product-manager.products.index') }}">Products</a></li>
    <li class="breadcrumb-item active" aria-current="page">Edit {{ $product->name }}</li>
@endsection

@section('actions')
    <div class="btn-group gap-2">
        <a href="{{ route('product-manager.products.index') }}" class="btn btn-outline-secondary rounded-pill px-3">
            <i class="bi bi-arrow-left me-1"></i> Back to Products
        </a>
        <a href="{{ route('product-manager.products.show', $product) }}" class="btn btn-outline-primary rounded-pill px-3">
            <i class="bi bi-eye me-1"></i> View Details
        </a>
        <a href="{{ route('product-manager.stock.history', ['product_id' => $product->id]) }}" class="btn btn-outline-success rounded-pill px-3">
            <i class="bi bi-boxes me-1"></i> Stock History
        </a>
        @if($product->status === 'Active')
            <a href="{{ route('product.show', $product->slug) }}" target="_blank" class="btn btn-outline-info rounded-pill px-3">
                <i class="bi bi-box-arrow-up-right me-1"></i> Store View
            </a>
        @endif
    </div>
@endsection

@section('content')
@if($product->status === 'Rejected')
    <div class="alert alert-danger border-0 shadow-sm rounded-4 p-3 mb-4 d-flex align-items-start gap-3">
        <i class="bi bi-exclamation-octagon-fill text-danger fs-4 flex-shrink-0 mt-1"></i>
        <div>
            <div class="fw-bold text-danger">Admin Feedback / Rejection Reason:</div>
            <div class="text-dark small">{{ $product->active_rejection_reason ?? $product->rejection_reason ?? 'Please revise specifications or media.' }}</div>
        </div>
    </div>
@endif

<form action="{{ route('product-manager.products.update', $product) }}" method="POST" enctype="multipart/form-data" id="productForm">
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
                                <option value="size_color" {{ old('option_type', $product->option_type) === 'size_color' ? 'selected' : '' }}>🎨 + 👕 Double Variant: Color + Clothing Size (e.g. Blue - S, Blue - M)</option>
                                <option value="waist_color" {{ old('option_type', $product->option_type) === 'waist_color' ? 'selected' : '' }}>🎨 + 👖 Double Variant: Color + Waist Size (e.g. Black - 30, Black - 32)</option>
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
                                    <th class="ps-3" style="width: 55%;" id="option_col_header">Option Value (Size / Color)</th>
                                    <th style="width: 35%;">Stock Available</th>
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
                                    $currentOptType = old('option_type', $product->option_type ?? 'size');
                                    $isDoubleOpt = in_array($currentOptType, ['size_color', 'waist_color']);
                                    $isColorType = $currentOptType === 'color';

                                    $adminPalette = [
                                        'Black' => '#18181b', 'White' => '#ffffff', 'Blue' => '#2563eb', 'Navy Blue' => '#0f172a',
                                        'Sky Blue' => '#38bdf8', 'Royal Blue' => '#1d4ed8', 'Red' => '#ef4444', 'Maroon' => '#800000',
                                        'Crimson' => '#dc143c', 'Green' => '#16a34a', 'Dark Green' => '#14532d', 'Olive' => '#808000',
                                        'Teal' => '#0d9488', 'Lime' => '#84cc16', 'Mint' => '#10b981', 'Yellow' => '#eab308',
                                        'Mustard' => '#ca8a04', 'Orange' => '#ea580c', 'Coral' => '#fb7185', 'Peach' => '#fed7aa',
                                        'Pink' => '#ec4899', 'Rose' => '#f43f5e', 'Purple' => '#9333ea', 'Lavender' => '#c084fc',
                                        'Violet' => '#7c3aed', 'Brown' => '#78350f', 'Tan' => '#d2b48c', 'Khaki' => '#f0e68c',
                                        'Beige' => '#f5f5dc', 'Cream' => '#fef3c7', 'Grey' => '#64748b', 'Silver' => '#c0c0c0',
                                        'Gold' => '#ffd700', 'Burgundy' => '#800020'
                                    ];
                                    $getBladeHex = function($name) use ($adminPalette) {
                                        if (!$name) return '#cbd5e1';
                                        $clean = strtolower(trim($name));
                                        foreach ($adminPalette as $k => $h) {
                                            if (strtolower($k) === $clean) return $h;
                                        }
                                        return $name;
                                    };
                                @endphp
                                @forelse($existingOptions as $idx => $optKey)
                                    @php
                                        $parts = explode(' - ', $optKey, 2);
                                        $colorPart = $parts[0] ?? '';
                                        $sizePart = $parts[1] ?? '';
                                    @endphp
                                    <tr>
                                        <td class="ps-3">
                                            @if($isDoubleOpt || count($parts) === 2)
                                                <div class="d-flex gap-2 align-items-center">
                                                    <div style="flex: 1.2; min-width: 140px;">
                                                        <div class="d-inline-flex align-items-center gap-1.5 w-100">
                                                            <span class="opt-color-dot rounded-circle flex-shrink-0" 
                                                                  style="width: 18px; height: 18px; background-color: {{ $getBladeHex($colorPart) }}; border: 1px solid rgba(0,0,0,0.2) !important; box-shadow: inset 0 0 0 1px rgba(255,255,255,0.4);"></span>
                                                            <select class="form-select form-select-sm fw-bold opt-color-part" onchange="onColorSelectChange(this)" required style="font-size: 0.8rem;">
                                                                <option value="">-- Select Color --</option>
                                                                @php $found = false; @endphp
                                                                @foreach($adminPalette as $pColor => $pHex)
                                                                    @php $isSel = (strcasecmp($colorPart, $pColor) === 0); if($isSel) $found = true; @endphp
                                                                    <option value="{{ $pColor }}" {{ $isSel ? 'selected' : '' }}>{{ $pColor }}</option>
                                                                @endforeach
                                                                @if(!empty($colorPart) && !$found)
                                                                    <option value="{{ $colorPart }}" selected>{{ $colorPart }}</option>
                                                                @endif
                                                                <option value="__custom__">➕ Custom Color...</option>
                                                            </select>
                                                        </div>
                                                    </div>
                                                    <span class="text-muted fw-bold">-</span>
                                                    <div style="flex: 1; min-width: 90px;">
                                                        <input type="text" class="form-control form-control-sm fw-bold opt-size-part" value="{{ $sizePart }}" placeholder="Size (e.g. S)" oninput="syncDoubleOptionRow(this)" required>
                                                    </div>
                                                    <input type="hidden" name="option_keys[]" class="opt-combined-key" value="{{ $optKey }}">
                                                </div>
                                            @elseif($isColorType)
                                                <div style="max-width: 260px;">
                                                    <div class="d-inline-flex align-items-center gap-1.5 w-100">
                                                        <span class="opt-color-dot rounded-circle flex-shrink-0" 
                                                              style="width: 18px; height: 18px; background-color: {{ $getBladeHex($optKey) }}; border: 1px solid rgba(0,0,0,0.2) !important; box-shadow: inset 0 0 0 1px rgba(255,255,255,0.4);"></span>
                                                        <select name="option_keys[]" class="form-select form-select-sm fw-bold opt-color-select" onchange="onSingleColorSelectChange(this)" required style="font-size: 0.8rem;">
                                                            <option value="">-- Select Color --</option>
                                                            @php $found = false; @endphp
                                                            @foreach($adminPalette as $pColor => $pHex)
                                                                @php $isSel = (strcasecmp($optKey, $pColor) === 0); if($isSel) $found = true; @endphp
                                                                <option value="{{ $pColor }}" {{ $isSel ? 'selected' : '' }}>{{ $pColor }}</option>
                                                            @endforeach
                                                            @if(!empty($optKey) && !$found)
                                                                <option value="{{ $optKey }}" selected>{{ $optKey }}</option>
                                                            @endif
                                                            <option value="__custom__">➕ Custom Color...</option>
                                                        </select>
                                                    </div>
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

            <!-- 5. Product Gallery Images -->
            <div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-4">
                <div class="card-header bg-white py-3 border-bottom d-flex align-items-center justify-content-between flex-wrap gap-2">
                    <h6 class="mb-0 fw-bold text-dark">
                        <i class="bi bi-images text-primary me-2"></i>5. Product Photo Gallery
                    </h6>
                    <div class="d-flex align-items-center gap-2">
                        <span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25 rounded-pill px-2.5 py-1">
                            <i class="bi bi-aspect-ratio me-1"></i> Standard: 1000 × 1000 px (1:1)
                        </span>
                        <span class="badge bg-light text-secondary border px-2.5 py-1 rounded-pill small">
                            {{ $product->galleryImages->count() }} Attached
                        </span>
                    </div>
                </div>
                <div class="card-body p-4">
                    <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-2">
                        <label class="form-label fw-bold text-dark small mb-0">Upload More Gallery Images</label>
                        <select id="image_fit_mode" class="form-select form-select-sm w-auto fw-semibold" style="font-size: 0.75rem;">
                            <option value="contain" selected>Auto-Fit in 1000×1000 White Square (No Cut)</option>
                            <option value="cover">Auto-Crop 1000×1000 Square (Edge-to-Edge)</option>
                        </select>
                    </div>
                    <input type="file" name="gallery_images[]" class="form-control @error('gallery_images.*') is-invalid @enderror" multiple accept="image/*" onchange="previewGalleryImages(this)">
                    <div class="form-text text-muted mb-2" style="font-size: 0.72rem;">Recommended size: <strong>1000 × 1000 px (1:1 Square)</strong>. Any uploaded photo is automatically standardized to 1000 × 1000 px so all photos display in the exact same size.</div>
                    @error('gallery_images.*') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                    <div class="row g-2 mb-3" id="gallery_preview_row"></div>

                    @if($product->galleryImages->count() > 0)
                        <div class="row g-3 mt-1">
                            @foreach($product->galleryImages as $image)
                                <div class="col-6 col-sm-4 col-md-3" id="gallery-image-{{ $image->id }}">
                                    <div class="position-relative border rounded-3 overflow-hidden shadow-xs bg-white p-1">
                                        <img src="{{ asset('storage/' . $image->image) }}" class="w-100 rounded-2" style="height: 120px; aspect-ratio: 1/1; object-fit: contain;">
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
                        <label class="form-label fw-bold text-dark small">Brand <span class="text-muted fw-normal">(Optional)</span></label>
                        <select name="brand_id" id="brand_id" class="form-select @error('brand_id') is-invalid @enderror">
                            <option value="">-- No Brand / Generic (Optional) --</option>
                            @foreach($brands as $brand)
                                <option value="{{ $brand->id }}" data-category-id="{{ $brand->category_id }}" {{ old('brand_id', $product->brand_id) == $brand->id ? 'selected' : '' }}>
                                    {{ $brand->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('brand_id') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
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
                <div class="card-header bg-white py-3 border-bottom d-flex align-items-center justify-content-between">
                    <h6 class="mb-0 fw-bold text-dark">
                        <i class="bi bi-card-image text-primary me-2"></i>Primary Product Image
                    </h6>
                    <span class="badge bg-light text-dark border rounded-pill" style="font-size: 0.68rem;">1000 × 1000 px</span>
                </div>
                <div class="card-body p-4 text-center">
                    <input type="file" name="main_image" class="form-control @error('main_image') is-invalid @enderror" accept="image/*" onchange="previewMainImage(this)">
                    <div class="form-text text-muted" style="font-size: 0.72rem;">Standard size: <strong>1000 × 1000 px (1:1 Square)</strong>. Leave empty to keep existing main image.</div>
                    @error('main_image') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror

                    <div class="mt-3 p-3 bg-light rounded-3 border text-center">
                        <img id="mainImagePreview" src="{{ asset('storage/' . $product->main_image) }}" alt="Preview" class="img-fluid rounded-2" style="width: 160px; height: 160px; object-fit: contain; background: #fff;">
                    </div>
                </div>
            </div>

            <!-- Product Video (YouTube) -->
            <div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-4">
                <div class="card-header bg-white py-3 border-bottom d-flex align-items-center justify-content-between">
                    <h6 class="mb-0 fw-bold text-dark">
                        <i class="bi bi-youtube text-danger me-2"></i>Product Video (Link or Embed)
                    </h6>
                    <span class="badge bg-light text-muted border rounded-pill" style="font-size: 0.68rem;">Optional</span>
                </div>
                <div class="card-body p-4">
                    <div class="input-group">
                        <span class="input-group-text bg-light text-danger border align-items-start pt-2"><i class="bi bi-play-circle-fill"></i></span>
                        <textarea name="video_url" rows="2" class="form-control form-control-sm @error('video_url') is-invalid @enderror" 
                                  placeholder="Paste YouTube Link or Embed Code (e.g. https://youtu.be/... or <iframe ...>)">{{ old('video_url', $product->video_url) }}</textarea>
                    </div>
                    <div class="form-text text-muted" style="font-size: 0.72rem;">Paste a YouTube Link (watch/youtu.be/shorts) or full <strong>Embed Code</strong> (&lt;iframe...&gt;).</div>
                    @error('video_url') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="d-grid gap-2">
                <button type="submit" class="btn btn-primary rounded-pill btn-lg fw-bold shadow-sm">
                    <i class="bi bi-check-circle me-1"></i> {{ $product->status === 'Rejected' ? 'Save & Resubmit for Review' : ($product->status === 'Pending_Approval' ? 'Update Pending Product' : 'Save Changes') }}
                </button>
                <a href="{{ route('product-manager.products.index') }}" class="btn btn-light rounded-pill border">Cancel</a>
            </div>
        </div>
    </div>
</form>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
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
            if (preview && res.dataUrl) {
                preview.src = res.dataUrl;
            }
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
                col.className = 'col-4 col-sm-3';
                col.innerHTML = `
                    <div class="border rounded-3 p-1 bg-white text-center position-relative">
                        <img src="${res.dataUrl}" class="img-fluid rounded-2" style="height: 80px; width: 100%; aspect-ratio: 1/1; object-fit: contain;">
                        <span class="badge bg-primary position-absolute bottom-0 start-0 m-1" style="font-size: 0.62rem;">1000×1000</span>
                    </div>
                `;
                container.appendChild(col);
            }
            if (dt) input.files = dt.files;
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
                fetch(`/product-manager/products/gallery/${id}`, {
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

    const ADMIN_COLOR_PALETTE = [
        { name: 'Black', hex: '#18181b' },
        { name: 'White', hex: '#ffffff' },
        { name: 'Blue', hex: '#2563eb' },
        { name: 'Navy Blue', hex: '#0f172a' },
        { name: 'Sky Blue', hex: '#38bdf8' },
        { name: 'Royal Blue', hex: '#1d4ed8' },
        { name: 'Red', hex: '#ef4444' },
        { name: 'Maroon', hex: '#800000' },
        { name: 'Crimson', hex: '#dc143c' },
        { name: 'Green', hex: '#16a34a' },
        { name: 'Dark Green', hex: '#14532d' },
        { name: 'Olive', hex: '#808000' },
        { name: 'Teal', hex: '#0d9488' },
        { name: 'Lime', hex: '#84cc16' },
        { name: 'Mint', hex: '#10b981' },
        { name: 'Yellow', hex: '#eab308' },
        { name: 'Mustard', hex: '#ca8a04' },
        { name: 'Orange', hex: '#ea580c' },
        { name: 'Coral', hex: '#fb7185' },
        { name: 'Peach', hex: '#fed7aa' },
        { name: 'Pink', hex: '#ec4899' },
        { name: 'Rose', hex: '#f43f5e' },
        { name: 'Purple', hex: '#9333ea' },
        { name: 'Lavender', hex: '#c084fc' },
        { name: 'Violet', hex: '#7c3aed' },
        { name: 'Brown', hex: '#78350f' },
        { name: 'Tan', hex: '#d2b48c' },
        { name: 'Khaki', hex: '#f0e68c' },
        { name: 'Beige', hex: '#f5f5dc' },
        { name: 'Cream', hex: '#fef3c7' },
        { name: 'Grey', hex: '#64748b' },
        { name: 'Silver', hex: '#c0c0c0' },
        { name: 'Gold', hex: '#ffd700' },
        { name: 'Burgundy', hex: '#800020' }
    ];

    function getAdminColorHex(name) {
        if (!name) return '#cbd5e1';
        const clean = name.trim().toLowerCase();
        const found = ADMIN_COLOR_PALETTE.find(c => c.name.toLowerCase() === clean);
        if (found) return found.hex;
        return clean;
    }

    function buildColorSelectHtml(selectedColor, className, onChangeHandler, inputName = '') {
        const currentColor = (selectedColor || '').trim();
        let optionsHtml = `<option value="">-- Select Color --</option>`;
        let isFound = false;

        ADMIN_COLOR_PALETTE.forEach(c => {
            const isSel = (currentColor && c.name.toLowerCase() === currentColor.toLowerCase());
            if (isSel) isFound = true;
            optionsHtml += `<option value="${c.name}" ${isSel ? 'selected' : ''}>${c.name}</option>`;
        });

        if (currentColor && !isFound) {
            optionsHtml += `<option value="${currentColor}" selected>${currentColor}</option>`;
        }

        optionsHtml += `<option value="__custom__">➕ Custom Color...</option>`;

        const hex = getAdminColorHex(currentColor);
        const nameAttr = inputName ? `name="${inputName}"` : '';

        return `
            <div class="d-inline-flex align-items-center gap-1.5 w-100">
                <span class="opt-color-dot rounded-circle flex-shrink-0" 
                      style="width: 18px; height: 18px; background-color: ${hex}; border: 1px solid rgba(0,0,0,0.2) !important; box-shadow: inset 0 0 0 1px rgba(255,255,255,0.4);"></span>
                <select ${nameAttr} class="form-select form-select-sm fw-bold ${className}" onchange="${onChangeHandler}" required style="font-size: 0.8rem;">
                    ${optionsHtml}
                </select>
            </div>
        `;
    }

    function onColorSelectChange(selectEl) {
        if (selectEl.value === '__custom__') {
            const customName = prompt('Enter custom color name (e.g. Rose Gold, Charcoal):');
            if (customName && customName.trim()) {
                const trimmed = customName.trim();
                let opt = Array.from(selectEl.options).find(o => o.value.toLowerCase() === trimmed.toLowerCase());
                if (!opt) {
                    opt = new Option(trimmed, trimmed, true, true);
                    selectEl.insertBefore(opt, selectEl.options[selectEl.options.length - 1]);
                } else {
                    opt.selected = true;
                }
                selectEl.value = trimmed;
            } else {
                selectEl.value = '';
            }
        }

        const dot = selectEl.closest('.d-inline-flex')?.querySelector('.opt-color-dot');
        if (dot) {
            dot.style.backgroundColor = getAdminColorHex(selectEl.value);
        }
        syncDoubleOptionRow(selectEl);
    }

    function onSingleColorSelectChange(selectEl) {
        if (selectEl.value === '__custom__') {
            const customName = prompt('Enter custom color name (e.g. Rose Gold, Charcoal):');
            if (customName && customName.trim()) {
                const trimmed = customName.trim();
                let opt = Array.from(selectEl.options).find(o => o.value.toLowerCase() === trimmed.toLowerCase());
                if (!opt) {
                    opt = new Option(trimmed, trimmed, true, true);
                    selectEl.insertBefore(opt, selectEl.options[selectEl.options.length - 1]);
                } else {
                    opt.selected = true;
                }
                selectEl.value = trimmed;
            } else {
                selectEl.value = '';
            }
        }

        const dot = selectEl.closest('.d-inline-flex')?.querySelector('.opt-color-dot');
        if (dot) {
            dot.style.backgroundColor = getAdminColorHex(selectEl.value);
        }
    }

    function syncDoubleOptionRow(el) {
        const wrapper = el.closest('td');
        if (!wrapper) return;
        const colorInp = wrapper.querySelector('.opt-color-part');
        const sizeInp = wrapper.querySelector('.opt-size-part');
        const hiddenKey = wrapper.querySelector('.opt-combined-key');
        if (colorInp && sizeInp && hiddenKey) {
            const c = colorInp.value.trim();
            const s = sizeInp.value.trim();
            hiddenKey.value = (c && s) ? `${c} - ${s}` : (c || s);
        }
    }

    function buildOptionRowHtml(type, key, qty) {
        const isDouble = (type === 'size_color' || type === 'waist_color');
        if (isDouble) {
            const parts = (key || '').split(' - ');
            const colorVal = parts[0] || '';
            const sizeVal = parts[1] || '';
            const sizePlaceholder = type === 'waist_color' ? 'Waist (e.g. 32)' : 'Size (e.g. M)';
            const combined = (colorVal && sizeVal) ? `${colorVal} - ${sizeVal}` : (key || '');
            const colorSelectHtml = buildColorSelectHtml(colorVal, 'opt-color-part', 'onColorSelectChange(this)');
            return `
                <td class="ps-3">
                    <div class="d-flex gap-2 align-items-center">
                        <div style="flex: 1.2; min-width: 140px;">
                            ${colorSelectHtml}
                        </div>
                        <span class="text-muted fw-bold">-</span>
                        <div style="flex: 1; min-width: 90px;">
                            <input type="text" class="form-control form-control-sm fw-bold opt-size-part" value="${sizeVal}" placeholder="${sizePlaceholder}" oninput="syncDoubleOptionRow(this)" required>
                        </div>
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

        if (type === 'color') {
            const colorSelectHtml = buildColorSelectHtml(key, 'opt-color-select', 'onSingleColorSelectChange(this)', 'option_keys[]');
            return `
                <td class="ps-3">
                    <div style="max-width: 260px;">
                        ${colorSelectHtml}
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
            if (type === 'size_color') colHeader.textContent = 'Color & Clothing Size (Double Variant)';
            else if (type === 'waist_color') colHeader.textContent = 'Color & Waist Size (Double Variant)';
            else colHeader.textContent = 'Option Value (Size / Color)';
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
                { key: 'Blue - L', qty: 15 },
                { key: 'Black - S', qty: 10 },
                { key: 'Black - M', qty: 15 },
                { key: 'Black - L', qty: 15 }
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
        const typeSelect = document.getElementById('option_type_select');
        const type = typeSelect ? typeSelect.value : 'size';
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
                const brandCatId = option.getAttribute('data-category-id');
                if (!selectedCategoryId || !brandCatId || brandCatId === selectedCategoryId) {
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
