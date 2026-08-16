@extends('admin.layouts.app')

@section('header', 'Product Details')

@section('actions')
    <div class="btn-group gap-2">
        <a href="{{ route('admin.products.index') }}" class="btn btn-outline-secondary rounded-pill px-3">
            <i class="bi bi-arrow-left me-1"></i> Back to Products
        </a>
        <a href="{{ route('product.show', $product->slug) }}" target="_blank" class="btn btn-outline-primary rounded-pill px-3">
            <i class="bi bi-box-arrow-up-right me-1"></i> View in Store
        </a>
        <a href="{{ route('admin.stock.show', $product) }}" class="btn btn-outline-success rounded-pill px-3">
            <i class="bi bi-boxes me-1"></i> Stock History
        </a>
        <a href="{{ route('admin.products.edit', $product) }}" class="btn btn-primary rounded-pill px-3">
            <i class="bi bi-pencil-square me-1"></i> Edit Product
        </a>
    </div>
@endsection

@section('content')
<div class="row g-4">
    <!-- Left Column: Visuals & Meta -->
    <div class="col-lg-4">
        <!-- Main Image Card -->
        <div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-4">
            <div class="card-body p-0 bg-light text-center position-relative">
                @if($product->main_image)
                    <img id="mainProductView" src="{{ asset('storage/' . $product->main_image) }}" alt="{{ $product->name }}" class="img-fluid" style="width: 100%; height: 360px; object-fit: contain; background: #fafafa;">
                @else
                    <div class="d-flex flex-column align-items-center justify-content-center text-muted" style="height: 360px;">
                        <i class="bi bi-image fs-1 mb-2"></i>
                        <span class="small fw-semibold">No Image Uploaded</span>
                    </div>
                @endif

                <div class="position-absolute top-0 start-0 m-3 d-flex flex-column gap-1">
                    @if($product->status === 'Active')
                        <span class="badge bg-success rounded-pill px-2.5 py-1">Active</span>
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
                    <div class="small fw-bold text-secondary text-uppercase mb-2" style="font-size: 0.72rem; letter-spacing: 0.5px;">Gallery Images</div>
                    <div class="d-flex gap-2 flex-wrap">
                        @if($product->main_image)
                            <img src="{{ asset('storage/' . $product->main_image) }}" class="rounded border p-1" style="width: 54px; height: 54px; object-fit: cover; cursor: pointer;" onclick="document.getElementById('mainProductView').src = this.src">
                        @endif
                        @foreach($product->galleryImages as $image)
                            <img src="{{ asset('storage/' . $image->image) }}" class="rounded border p-1" style="width: 54px; height: 54px; object-fit: cover; cursor: pointer;" onclick="document.getElementById('mainProductView').src = this.src">
                        @endforeach
                    </div>
                </div>
            @endif
        </div>

        <!-- System & Catalog Metadata -->
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
                                    <span class="text-muted italic">Uncategorized</span>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <th class="ps-4 py-2.5 text-muted fw-normal small">Brand</th>
                            <td class="pe-4 py-2.5 text-dark small">
                                @if($product->brand)
                                    <span class="fw-semibold">{{ $product->brand->name }}</span>
                                @else
                                    <span class="text-muted italic">No Brand</span>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <th class="ps-4 py-2.5 text-muted fw-normal small">Product Type</th>
                            <td class="pe-4 py-2.5 text-dark small">{{ ucfirst($product->product_type ?? 'Single Item') }}</td>
                        </tr>
                        <tr>
                            <th class="ps-4 py-2.5 text-muted fw-normal small">Created At</th>
                            <td class="pe-4 py-2.5 text-muted small">{{ $product->created_at->format('d M Y, h:i A') }}</td>
                        </tr>
                        <tr>
                            <th class="ps-4 py-2.5 text-muted fw-normal small">Last Updated</th>
                            <td class="pe-4 py-2.5 text-muted small">{{ $product->updated_at->format('d M Y, h:i A') }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Right Column: Product Specs, Pricing, & Description -->
    <div class="col-lg-8">
        <!-- Overview Card -->
        <div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-4">
            <div class="card-body p-4">
                <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-2">
                    <span class="badge bg-light text-secondary border px-2.5 py-1 small fw-bold">
                        <i class="bi bi-tag me-1"></i>{{ $product->category?->name ?? 'Store Product' }}
                    </span>
                    <div class="d-flex align-items-center gap-1 text-warning small">
                        <i class="bi bi-star-fill"></i>
                        <span class="fw-bold text-dark">{{ number_format($product->averageRating(), 1) }}</span>
                        <span class="text-muted">({{ $product->reviews->count() }} reviews)</span>
                    </div>
                </div>

                <h3 class="fw-bold text-dark mb-3">{{ $product->name }}</h3>

                <!-- Key Metrics Cards -->
                <div class="row g-3 mb-4">
                    <div class="col-sm-4">
                        <div class="p-3 bg-light rounded-4 border text-center">
                            <div class="small text-muted fw-bold text-uppercase" style="font-size: 0.72rem;">Base Price</div>
                            <div class="h3 fw-bolder text-dark mb-0 mt-1">₹{{ number_format($product->price, 2) }}</div>
                        </div>
                    </div>
                    <div class="col-sm-4">
                        <div class="p-3 rounded-4 border text-center" style="background: rgba(99, 102, 241, 0.05); border-color: rgba(99, 102, 241, 0.2) !important;">
                            <div class="small text-primary fw-bold text-uppercase" style="font-size: 0.72rem;">Effective Sale Price</div>
                            <div class="h3 fw-bolder text-primary mb-0 mt-1">
                                @if(isset($product->sale_price))
                                    ₹{{ number_format($product->sale_price, 2) }}
                                    <div class="small text-success fw-bold" style="font-size: 0.75rem;">
                                        {{ $product->offer_badge ?? 'Offer Applied' }} ({{ $product->offer_discount_percentage }}% OFF)
                                    </div>
                                @else
                                    ₹{{ number_format($product->price, 2) }}
                                    <div class="small text-muted" style="font-size: 0.75rem;">No Active Campaign</div>
                                @endif
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-4">
                        @php
                            $stockBg = $product->stock > 5 ? '#d1fae5' : ($product->stock > 0 ? '#fef3c7' : '#fee2e2');
                            $stockColor = $product->stock > 5 ? '#065f46' : ($product->stock > 0 ? '#92400e' : '#991b1b');
                            $stockBorder = $product->stock > 5 ? '#a7f3d0' : ($product->stock > 0 ? '#fde68a' : '#fca5a5');
                            $stockLabel = $product->stock > 5 ? 'In Stock' : ($product->stock > 0 ? 'Low Stock' : 'Out of Stock');
                        @endphp
                        <div class="p-3 rounded-4 text-center" style="background: {{ $stockBg }}; border: 1px solid {{ $stockBorder }}; color: {{ $stockColor }};">
                            <div class="small fw-bold text-uppercase" style="font-size: 0.72rem;">Available Inventory</div>
                            <div class="h3 fw-bolder mb-0 mt-1">{{ $product->stock }} Units</div>
                            <div class="small fw-bold" style="font-size: 0.75rem;">{{ $stockLabel }}</div>
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
                            <span class="text-muted italic">No full description provided for this product.</span>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Reviews Card -->
        <div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-4">
            <div class="card-header bg-white py-3 border-bottom d-flex align-items-center justify-content-between">
                <h6 class="mb-0 fw-bold text-dark">
                    <i class="bi bi-chat-quote-fill text-primary me-2"></i>Customer Reviews ({{ $product->reviews->count() }})
                </h6>
                <a href="{{ route('admin.reviews.index', ['search' => $product->name]) }}" class="btn btn-sm btn-outline-primary rounded-pill px-3">
                    Manage All Reviews
                </a>
            </div>
            <div class="card-body p-0">
                @if($product->reviews && $product->reviews->count() > 0)
                    <div class="list-group list-group-flush">
                        @foreach($product->reviews->take(5) as $review)
                            <div class="list-group-item p-3 border-bottom">
                                <div class="d-flex align-items-center justify-content-between mb-1">
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="rounded-circle bg-primary bg-opacity-10 text-primary fw-bold d-flex align-items-center justify-content-center" style="width: 32px; height: 32px; font-size: 0.75rem;">
                                            {{ strtoupper(substr($review->user?->name ?? 'U', 0, 1)) }}
                                        </div>
                                        <div>
                                            <span class="fw-bold text-dark small">{{ $review->user?->name ?? 'Customer' }}</span>
                                            @if($review->is_verified_purchase)
                                                <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 ms-1" style="font-size: 0.65rem;">Verified Purchase</span>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="text-warning small">
                                        @for($i = 1; $i <= 5; $i++)
                                            <i class="bi {{ $i <= $review->rating ? 'bi-star-fill' : 'bi-star' }}"></i>
                                        @endfor
                                    </div>
                                </div>
                                <div class="fw-semibold text-dark small">{{ $review->title }}</div>
                                <p class="text-secondary small mb-1">{{ $review->review }}</p>
                                <span class="text-muted" style="font-size: 0.7rem;">{{ $review->created_at->format('d M Y') }}</span>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-4 text-muted">
                        <i class="bi bi-star text-muted fs-3 d-block mb-1"></i>
                        <span class="small">No customer reviews yet for this product.</span>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
