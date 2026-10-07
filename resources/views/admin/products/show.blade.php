@extends('admin.layouts.app')

@section('header', 'Product Details')

@section('actions')
    <div class="d-flex align-items-center gap-2 flex-wrap">
        <a href="{{ route('admin.products.index') }}" class="btn btn-outline-secondary rounded-pill px-3">
            <i class="bi bi-arrow-left me-1"></i> Back to Products
        </a>

        @if($product->status === 'Pending_Approval')
            <form action="{{ route('admin.products.approve', $product) }}" method="POST" class="d-inline">
                @csrf
                <button type="submit" class="btn btn-success fw-bold rounded-pill px-3.5 shadow-sm">
                    <i class="bi bi-check-circle-fill me-1"></i> Approve & Publish
                </button>
            </form>
            <button type="button" class="btn btn-danger fw-bold rounded-pill px-3.5 shadow-sm" onclick="promptRejectProduct('{{ route('admin.products.reject', $product) }}', '{{ addslashes($product->name) }}')">
                <i class="bi bi-x-circle-fill me-1"></i> Reject Product
            </button>
        @elseif($product->status === 'Rejected')
            <form action="{{ route('admin.products.approve', $product) }}" method="POST" class="d-inline">
                @csrf
                <button type="submit" class="btn btn-success fw-bold rounded-pill px-3.5 shadow-sm">
                    <i class="bi bi-check-circle-fill me-1"></i> Override & Approve
                </button>
            </form>
        @endif

        <a href="{{ route('product.show', $product->slug) }}" target="_blank" class="btn btn-outline-primary rounded-pill px-3">
            <i class="bi bi-box-arrow-up-right me-1"></i> Store View
        </a>
        <a href="{{ route('admin.stock.show', $product) }}" class="btn btn-outline-success rounded-pill px-3">
            <i class="bi bi-boxes me-1"></i> Stock History
        </a>
        @if(auth()->user()->isSuperAdmin())
        <a href="{{ route('admin.products.edit', $product) }}" class="btn btn-primary rounded-pill px-3">
            <i class="bi bi-pencil-square me-1"></i> Edit Product
        </a>
        @endif
    </div>
@endsection

@section('content')

{{-- High Priority Alert for Pending Approval --}}
@if($product->status === 'Pending_Approval')
    <div class="alert border-0 shadow-sm rounded-4 p-4 mb-4 d-flex align-items-center justify-content-between flex-wrap gap-3"
        style="background: linear-gradient(135deg, #fffbeb 0%, #fef3c7 100%); border-left: 5px solid #d97706 !important;">
        <div class="d-flex align-items-center gap-3">
            <div style="width: 48px; height: 48px; border-radius: 14px; background: rgba(217, 119, 6, 0.15); display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                <i class="bi bi-hourglass-split text-warning fs-3" style="color: #b45309 !important;"></i>
            </div>
            <div>
                <h5 class="fw-bold mb-1" style="color: #92400e;">This Product is Awaiting Admin Approval</h5>
                <p class="mb-0 text-muted small">
                    Submitted by: <strong class="text-dark">{{ $product->submitter->name ?? 'Product Manager' }}</strong> ({{ $product->submitter->email ?? 'Staff' }}) &bull; {{ $product->created_at->format('d M Y, h:i A') }} ({{ $product->created_at->diffForHumans() }})
                </p>
            </div>
        </div>
        <div class="d-flex align-items-center gap-2">
            <form action="{{ route('admin.products.approve', $product) }}" method="POST" class="d-inline">
                @csrf
                <button type="submit" class="btn btn-success fw-bold rounded-pill px-4 py-2 shadow-sm">
                    <i class="bi bi-check-circle-fill me-1.5"></i> Approve & Publish Live
                </button>
            </form>
            <button type="button" class="btn btn-outline-danger fw-bold rounded-pill px-3.5 py-2 bg-white" onclick="promptRejectProduct('{{ route('admin.products.reject', $product) }}', '{{ addslashes($product->name) }}')">
                <i class="bi bi-x-circle-fill me-1.5"></i> Reject with Reason
            </button>
        </div>
    </div>
@elseif($product->status === 'Rejected')
    <div class="alert border-0 shadow-sm rounded-4 p-4 mb-4 d-flex align-items-center justify-content-between flex-wrap gap-3"
        style="background: linear-gradient(135deg, #fef2f2 0%, #fee2e2 100%); border-left: 5px solid #ef4444 !important;">
        <div class="d-flex align-items-center gap-3">
            <div style="width: 48px; height: 48px; border-radius: 14px; background: rgba(239, 68, 68, 0.15); display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                <i class="bi bi-exclamation-octagon-fill text-danger fs-3"></i>
            </div>
            <div>
                <h5 class="fw-bold mb-1 text-danger">Product Rejected</h5>
                <p class="mb-0 text-dark small">
                    <strong>Reason:</strong> {{ $product->active_rejection_reason ?? $product->rejection_reason ?? 'Awaiting corrections from Product Manager.' }}
                </p>
            </div>
        </div>
        <div class="d-flex align-items-center gap-2">
            <form action="{{ route('admin.products.approve', $product) }}" method="POST" class="d-inline" onsubmit="return confirm('Override rejection and approve this product now?');">
                @csrf
                <button type="submit" class="btn btn-success fw-bold rounded-pill px-3.5 py-2 shadow-sm">
                    <i class="bi bi-check-circle-fill me-1.5"></i> Override & Approve
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
                    <img id="mainProductView" src="{{ asset('storage/' . $product->main_image) }}" alt="{{ $product->name }}" class="img-fluid" style="width: 100%; height: 360px; object-fit: contain; background: #fafafa;">
                @else
                    <div class="d-flex flex-column align-items-center justify-content-center text-muted" style="height: 360px;">
                        <i class="bi bi-image fs-1 mb-2"></i>
                        <span class="small fw-semibold">No Image Uploaded</span>
                    </div>
                @endif

                <div class="position-absolute top-0 start-0 m-3 d-flex flex-column gap-1">
                    @if($product->status === 'Active')
                        <span class="badge bg-success rounded-pill px-2.5 py-1"><i class="bi bi-check-circle me-1"></i>Active</span>
                    @elseif($product->status === 'Pending_Approval')
                        <span class="badge bg-warning text-dark rounded-pill px-2.5 py-1"><i class="bi bi-hourglass-split me-1"></i>Pending Approval</span>
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

        <!-- Rejection History Log -->
        @if($product->rejectionReasons && $product->rejectionReasons->count() > 0)
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
                            <div class="small text-muted fw-bold text-uppercase" style="font-size: 0.72rem;">Selling Price (SP)</div>
                            <div class="h4 fw-bolder text-dark mb-0 mt-1">₹{{ number_format($product->price, 2) }}</div>
                        </div>
                    </div>
                    <div class="col-sm-3">
                        <div class="p-3 rounded-4 border text-center" style="background: rgba(99, 102, 241, 0.05); border-color: rgba(99, 102, 241, 0.2) !important;">
                            <div class="small text-primary fw-bold text-uppercase" style="font-size: 0.72rem;">Effective Price</div>
                            <div class="h4 fw-bolder text-primary mb-0 mt-1">
                                @if(isset($product->sale_price))
                                    ₹{{ number_format($product->sale_price, 2) }}
                                    <div class="small text-success fw-bold" style="font-size: 0.7rem;">
                                        {{ $product->offer_discount_percentage }}% OFF
                                    </div>
                                @else
                                    ₹{{ number_format($product->price, 2) }}
                                    <div class="small text-muted" style="font-size: 0.7rem;">Standard</div>
                                @endif
                            </div>
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

@push('scripts')
<script>
function promptRejectProduct(rejectUrl, productName) {
    Swal.fire({
        title: 'Reject Product?',
        html: `Provide reason for rejecting <b>${productName}</b>:`,
        input: 'textarea',
        inputPlaceholder: 'e.g. Please update product description, price, and upload higher quality image...',
        inputAttributes: {
            'aria-label': 'Rejection reason',
            'rows': 3
        },
        showCancelButton: true,
        confirmButtonColor: '#ef4444',
        cancelButtonColor: '#64748b',
        confirmButtonText: 'Reject Product',
        cancelButtonText: 'Cancel',
        inputValidator: (value) => {
            if (!value || value.trim().length < 5) {
                return 'Please enter a valid rejection reason (min 5 characters).';
            }
        }
    }).then((result) => {
        if (result.isConfirmed) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = rejectUrl;

            const csrfInput = document.createElement('input');
            csrfInput.type = 'hidden';
            csrfInput.name = '_token';
            csrfInput.value = '{{ csrf_token() }}';
            form.appendChild(csrfInput);

            const reasonInput = document.createElement('input');
            reasonInput.type = 'hidden';
            reasonInput.name = 'rejection_reason';
            reasonInput.value = result.value;
            form.appendChild(reasonInput);

            document.body.appendChild(form);
            form.submit();
        }
    });
}
</script>
@endpush
@endsection
