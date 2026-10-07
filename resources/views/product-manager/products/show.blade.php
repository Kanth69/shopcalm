@extends('product-manager.layouts.app')

@section('header', $product->name)

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('product-manager.dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item"><a href="{{ route('product-manager.products.index') }}">Products</a></li>
    <li class="breadcrumb-item active" aria-current="page">{{ Str::limit($product->name, 25) }}</li>
@endsection

@section('actions')
    <div class="btn-group gap-2">
        <a href="{{ route('product-manager.products.index') }}" class="btn btn-outline-secondary rounded-pill px-3">
            <i class="bi bi-arrow-left me-1"></i> Back to Products
        </a>
        <a href="{{ route('product-manager.stock.history', ['product_id' => $product->id]) }}" class="btn btn-outline-success rounded-pill px-3">
            <i class="bi bi-boxes me-1"></i> Stock History
        </a>
        @if($product->status === 'Active')
            <a href="{{ route('product.show', $product->slug) }}" target="_blank" class="btn btn-outline-primary rounded-pill px-3">
                <i class="bi bi-box-arrow-up-right me-1"></i> View in Store
            </a>
        @endif
        <a href="{{ route('product-manager.products.edit', $product) }}" class="btn btn-primary rounded-pill px-3 fw-bold">
            <i class="bi bi-pencil-square me-1"></i> Edit Product
        </a>
    </div>
@endsection

@section('content')

@if($product->status === 'Rejected')
    <div class="alert alert-danger border-0 shadow-sm rounded-4 p-3 mb-4 d-flex align-items-start justify-content-between gap-3">
        <div class="d-flex align-items-start gap-3">
            <i class="bi bi-exclamation-octagon-fill text-danger fs-3 flex-shrink-0 mt-1"></i>
            <div>
                <div class="fw-bold text-danger">Product Requires Revision (Rejected by Admin)</div>
                <div class="text-dark small mt-1">
                    {{ $product->active_rejection_reason ?? ($product->latestRejectionReason->reason ?? 'Please update specifications and media.') }}
                </div>
                @if($product->latestRejectionReason && $product->latestRejectionReason->rejector)
                    <div class="text-muted small mt-1" style="font-size: 0.72rem;">
                        Feedback by <strong>{{ $product->latestRejectionReason->rejector->name }}</strong> &bull; {{ $product->latestRejectionReason->created_at->diffForHumans() }}
                    </div>
                @endif
            </div>
        </div>
        <div class="d-flex gap-2 align-items-center">
            <a href="{{ route('product-manager.products.edit', $product) }}" class="btn btn-sm btn-primary rounded-pill px-3">
                <i class="bi bi-pencil me-1"></i> Fix Details
            </a>
            <form action="{{ route('product-manager.products.resubmit', $product) }}" method="POST">
                @csrf
                <button type="submit" class="btn btn-sm btn-warning text-dark rounded-pill px-3 fw-bold">
                    <i class="bi bi-arrow-repeat me-1"></i> Resubmit for Review
                </button>
            </form>
        </div>
    </div>
@endif

<div class="row g-4">
    <!-- Left Column: Visuals & Meta -->
    <div class="col-lg-4">
        <!-- Main Image Card -->
        <div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-4">
            <div class="card-body p-0 bg-light text-center position-relative">
                @if($product->main_image)
                    <img id="mainProductView" src="{{ asset('storage/' . $product->main_image) }}" alt="{{ $product->name }}" class="img-fluid" style="width: 100%; height: 320px; object-fit: contain; background: #fafafa;">
                @else
                    <div class="d-flex flex-column align-items-center justify-content-center text-muted" style="height: 320px;">
                        <i class="bi bi-image fs-1 mb-2"></i>
                        <span class="small fw-semibold">No Image Uploaded</span>
                    </div>
                @endif

                <div class="position-absolute top-0 start-0 m-3 d-flex flex-column gap-1">
                    @if($product->status === 'Active')
                        <span class="badge bg-success rounded-pill px-2.5 py-1"><i class="bi bi-check-circle me-1"></i>Live on Store</span>
                    @elseif($product->status === 'Pending_Approval')
                        <span class="badge bg-warning text-dark rounded-pill px-2.5 py-1"><i class="bi bi-hourglass-split me-1"></i>Pending Admin Approval</span>
                    @elseif($product->status === 'Rejected')
                        <span class="badge bg-danger rounded-pill px-2.5 py-1"><i class="bi bi-x-circle me-1"></i>Rejected</span>
                    @else
                        <span class="badge bg-secondary rounded-pill px-2.5 py-1">Inactive</span>
                    @endif

                    @if($product->featured)
                        <span class="badge bg-warning text-dark rounded-pill px-2.5 py-1"><i class="bi bi-star-fill me-1"></i>Featured</span>
                    @endif

                    @if($product->trending)
                        <span class="badge bg-info text-dark rounded-pill px-2.5 py-1"><i class="bi bi-graph-up-arrow me-1"></i>Trending</span>
                    @endif
                </div>
            </div>

            @if($product->galleryImages && $product->galleryImages->count() > 0)
                <div class="card-footer bg-white border-top p-3">
                    <div class="small fw-bold text-secondary text-uppercase mb-2" style="font-size: 0.72rem; letter-spacing: 0.5px;">Gallery Photos ({{ $product->galleryImages->count() }})</div>
                    <div class="d-flex gap-2 flex-wrap">
                        @if($product->main_image)
                            <img src="{{ asset('storage/' . $product->main_image) }}" class="rounded border p-1" style="width: 50px; height: 50px; object-fit: cover; cursor: pointer;" onclick="document.getElementById('mainProductView').src = this.src">
                        @endif
                        @foreach($product->galleryImages as $image)
                            <img src="{{ asset('storage/' . $image->image) }}" class="rounded border p-1" style="width: 50px; height: 50px; object-fit: cover; cursor: pointer;" onclick="document.getElementById('mainProductView').src = this.src">
                        @endforeach
                    </div>
                </div>
            @endif
        </div>

        <!-- Catalog Details -->
        <div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-4">
            <div class="card-header bg-white py-3 border-bottom">
                <h6 class="mb-0 fw-bold text-dark"><i class="bi bi-info-circle text-primary me-2"></i>Catalog Details</h6>
            </div>
            <div class="card-body p-0">
                <table class="table table-sm mb-0">
                    <tbody>
                        <tr>
                            <th class="ps-4 py-2.5 text-muted fw-normal small" style="width: 40%;">Product ID</th>
                            <td class="pe-4 py-2.5 fw-semibold text-dark small">#{{ $product->id }}</td>
                        </tr>
                        <tr>
                            <th class="ps-4 py-2.5 text-muted fw-normal small">SKU Code</th>
                            <td class="pe-4 py-2.5 fw-bold text-dark font-monospace small">{{ $product->sku }}</td>
                        </tr>
                        <tr>
                            <th class="ps-4 py-2.5 text-muted fw-normal small">Category</th>
                            <td class="pe-4 py-2.5 text-dark small">
                                @if($product->category)
                                    <span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25">{{ $product->category->name }}</span>
                                @else
                                    <span class="text-muted italic">—</span>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <th class="ps-4 py-2.5 text-muted fw-normal small">Brand</th>
                            <td class="pe-4 py-2.5 text-dark small">
                                @if($product->brand)
                                    <span class="fw-semibold">{{ $product->brand->name }}</span>
                                @else
                                    <span class="text-muted italic">—</span>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <th class="ps-4 py-2.5 text-muted fw-normal small">Last Updated</th>
                            <td class="pe-4 py-2.5 text-muted small">{{ $product->updated_at->format('d M Y, h:i A') }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Rejection History Log -->
        @if($product->rejectionReasons->count() > 0)
            <div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-4">
                <div class="card-header bg-white py-3 border-bottom d-flex align-items-center justify-content-between">
                    <h6 class="mb-0 fw-bold text-dark">
                        <i class="bi bi-clock-history text-danger me-2"></i>Rejection History Log
                    </h6>
                    <span class="badge bg-light text-danger border px-2 py-0.5 rounded-pill small">
                        {{ $product->rejectionReasons->count() }} {{ Str::plural('event', $product->rejectionReasons->count()) }}
                    </span>
                </div>
                <div class="card-body p-0">
                    <div class="list-group list-group-flush">
                        @foreach($product->rejectionReasons as $rej)
                            <div class="list-group-item p-3 border-bottom">
                                <div class="d-flex align-items-center justify-content-between mb-1">
                                    <span class="badge {{ $rej->status === 'active' ? 'bg-danger' : 'bg-secondary' }} rounded-pill" style="font-size: 0.65rem;">
                                        {{ ucfirst($rej->status) }}
                                    </span>
                                    <span class="text-muted" style="font-size: 0.7rem;">{{ $rej->created_at->format('d M Y, h:i A') }}</span>
                                </div>
                                <div class="text-dark small mb-1" style="font-size: 0.8rem;">{{ $rej->reason }}</div>
                                @if($rej->rejector)
                                    <div class="text-muted" style="font-size: 0.68rem;">By: {{ $rej->rejector->name }}</div>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        @endif
    </div>

    <!-- Right Column: Specs, Pricing & Stock Movements -->
    <div class="col-lg-8">
        <!-- Overview & Key Metrics -->
        <div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-4">
            <div class="card-body p-4">
                <h3 class="fw-bold text-dark mb-3">{{ $product->name }}</h3>

                <!-- Key Metrics Cards -->
                <div class="row g-3 mb-4">
                    <div class="col-sm-3">
                        <div class="p-3 bg-light rounded-4 border text-center">
                            <div class="small text-muted fw-bold text-uppercase" style="font-size: 0.72rem;">Cost Price (CP)</div>
                            <div class="h4 fw-bolder text-secondary mb-0 mt-1">
                                {{ $product->cost_price ? '₹' . number_format($product->cost_price, 2) : '—' }}
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-3">
                        <div class="p-3 bg-light rounded-4 border text-center">
                            <div class="small text-muted fw-bold text-uppercase" style="font-size: 0.72rem;">Selling Price</div>
                            <div class="h4 fw-bolder text-dark mb-0 mt-1">₹{{ number_format($product->price, 2) }}</div>
                        </div>
                    </div>
                    <div class="col-sm-3">
                        @php
                            $stockBg = $product->stock > 5 ? '#d1fae5' : ($product->stock > 0 ? '#fef3c7' : '#fee2e2');
                            $stockColor = $product->stock > 5 ? '#065f46' : ($product->stock > 0 ? '#92400e' : '#991b1b');
                            $stockBorder = $product->stock > 5 ? '#a7f3d0' : ($product->stock > 0 ? '#fde68a' : '#fca5a5');
                            $stockLabel = $product->stock > 5 ? 'In Stock' : ($product->stock > 0 ? 'Low Stock' : 'Out of Stock');
                        @endphp
                        <div class="p-3 rounded-4 text-center" style="background: {{ $stockBg }}; border: 1px solid {{ $stockBorder }}; color: {{ $stockColor }};">
                            <div class="small fw-bold text-uppercase" style="font-size: 0.72rem;">Current Inventory</div>
                            <div class="h3 fw-bolder mb-0 mt-1">{{ $product->stock }} Units</div>
                            <div class="small fw-bold" style="font-size: 0.75rem;">{{ $stockLabel }}</div>
                        </div>
                    </div>
                    <div class="col-sm-4">
                        <div class="p-3 bg-light rounded-4 border text-center">
                            <div class="small text-muted fw-bold text-uppercase" style="font-size: 0.72rem;">Stock Operations</div>
                            <div class="d-flex gap-1 justify-content-center mt-2">
                                <a href="{{ route('product-manager.stock.form', ['product' => $product, 'action' => 'add']) }}" class="btn btn-sm btn-outline-success rounded-pill px-2.5" title="Add Inbound Stock">
                                    <i class="bi bi-plus-lg"></i> Add
                                </a>
                                <a href="{{ route('product-manager.stock.form', ['product' => $product, 'action' => 'reduce']) }}" class="btn btn-sm btn-outline-danger rounded-pill px-2.5" title="Reduce Stock">
                                    <i class="bi bi-dash-lg"></i> Reduce
                                </a>
                                <a href="{{ route('product-manager.stock.form', ['product' => $product, 'action' => 'adjust']) }}" class="btn btn-sm btn-outline-warning text-dark rounded-pill px-2.5" title="Direct Count Adjust">
                                    <i class="bi bi-sliders"></i> Adjust
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Short Description -->
                @if($product->short_description)
                    <div class="mb-4 p-3 bg-light rounded-3 border-start border-3 border-primary">
                        <h6 class="fw-bold text-dark mb-1" style="font-size: 0.85rem;">Summary</h6>
                        <p class="text-secondary small mb-0">{{ $product->short_description }}</p>
                    </div>
                @endif

                <!-- Full Description -->
                <div class="mb-2">
                    <h6 class="fw-bold text-dark mb-2" style="font-size: 0.9rem;">Product Description</h6>
                    <div class="text-secondary small line-height-lg" style="line-height: 1.7;">
                        @if($product->description)
                            {!! nl2br(e($product->description)) !!}
                        @else
                            <span class="text-muted italic">No full description provided.</span>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        @php
            $optStocks = is_array($product->option_stocks) ? $product->option_stocks : (json_decode($product->option_stocks ?? '{}', true) ?: []);
            $optTypeLabels = [
                'size'        => 'Clothing Size',
                'waist'       => 'Waist Size',
                'color'       => 'Color',
                'size_color'  => 'Color + Clothing Size (Double Variant)',
                'waist_color' => 'Color + Waist Size (Double Variant)',
                'custom'      => 'Custom Variant',
            ];
            $optTypeLabel = $optTypeLabels[strtolower($product->option_type ?? 'size')] ?? ucfirst($product->option_type ?? 'Variant');
        @endphp
        @if($product->has_options && !empty($optStocks))
            <div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-4">
                <div class="card-header bg-white py-3 border-bottom d-flex align-items-center justify-content-between">
                    <h6 class="mb-0 fw-bold text-dark">
                        <i class="bi bi-sliders text-primary me-2"></i>Product Variants & Stock Breakdown
                    </h6>
                    <span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25 rounded-pill px-3 py-1">
                        {{ $optTypeLabel }} ({{ count($optStocks) }})
                    </span>
                </div>
                <div class="card-body p-3">
                    <div class="d-flex flex-wrap gap-2">
                        @foreach($optStocks as $optKey => $optQty)
                            <div class="border rounded-3 px-3 py-2 bg-light d-inline-flex align-items-center gap-2">
                                <span class="fw-bold text-dark small">{{ $optKey }}</span>
                                <span class="badge rounded-pill {{ (int)$optQty > 5 ? 'bg-success' : ((int)$optQty > 0 ? 'bg-warning text-dark' : 'bg-danger') }}">
                                    {{ (int)$optQty }} in stock
                                </span>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        @endif

        <!-- Product Stock Movement Audit Trail -->
        <div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-4">
            <div class="card-header bg-white py-3 border-bottom d-flex align-items-center justify-content-between">
                <h6 class="mb-0 fw-bold text-dark">
                    <i class="bi bi-boxes text-primary me-2"></i>Recent Stock Movements (This Product)
                </h6>
                <a href="{{ route('product-manager.stock.history', ['product_id' => $product->id]) }}" class="btn btn-sm btn-outline-primary rounded-pill px-3">
                    Full Stock Trail
                </a>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-3" style="font-size: 0.72rem;">Date</th>
                                <th style="font-size: 0.72rem;">Type</th>
                                <th style="font-size: 0.72rem;">Change</th>
                                <th style="font-size: 0.72rem;">Trail</th>
                                <th style="font-size: 0.72rem;">Notes</th>
                                <th class="pe-3 text-end" style="font-size: 0.72rem;">Recorded By</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($product->stockMovements as $sm)
                            <tr>
                                <td class="ps-3 text-muted small" style="white-space: nowrap;">
                                    {{ $sm->created_at->format('d M, Y') }}<br>
                                    <span style="font-size: 0.68rem;">{{ $sm->created_at->format('h:i A') }}</span>
                                </td>
                                <td>
                                    @php
                                        $typeVal = is_object($sm->movement_type) ? $sm->movement_type->value : $sm->movement_type;
                                        $typeClass = match($typeVal) {
                                            'PURCHASE' => 'success',
                                            'SALE' => 'danger',
                                            'ADJUSTMENT' => 'warning text-dark',
                                            default => 'secondary'
                                        };
                                    @endphp
                                    <span class="badge bg-{{ $typeClass }} rounded-pill">{{ $typeVal }}</span>
                                </td>
                                <td>
                                    @if($sm->quantity > 0)
                                        <span class="text-success fw-bold">+{{ $sm->quantity }}</span>
                                    @elseif($sm->quantity < 0)
                                        <span class="text-danger fw-bold">{{ $sm->quantity }}</span>
                                    @else
                                        <span class="text-muted fw-bold">0</span>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge bg-light text-muted border font-monospace">
                                        {{ $sm->stock_before }} &rarr; <b class="text-dark">{{ $sm->stock_after }}</b>
                                    </span>
                                </td>
                                <td style="max-width: 180px;">
                                    <span class="small text-muted text-truncate d-block" title="{{ $sm->notes }}">
                                        {{ $sm->notes ?? '—' }}
                                    </span>
                                </td>
                                <td class="pe-3 text-end small">
                                    @if(!$sm->isSystemMovement() && $sm->createdBy)
                                        <span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25 rounded-pill px-2.5 py-1" style="font-size: 0.72rem;">
                                            <i class="bi bi-person-fill me-1"></i> {{ $sm->createdBy->name }} ({{ $sm->createdBy->role_name ?? 'Staff' }})
                                        </span>
                                    @else
                                        <span class="badge bg-secondary bg-opacity-10 text-secondary border border-secondary border-opacity-25 rounded-pill px-2.5 py-1" style="font-size: 0.72rem;">
                                            <i class="bi bi-robot me-1"></i> System
                                        </span>
                                    @endif
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="text-center py-4 text-muted small">No stock movements recorded for this product yet.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
