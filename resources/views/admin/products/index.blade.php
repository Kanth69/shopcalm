@extends('admin.layouts.app')

@section('header', 'Products')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item active" aria-current="page">Products</li>
@endsection

@section('actions')
    @if(auth()->user()->isSuperAdmin())
    <a href="{{ route('admin.products.create') }}" class="btn btn-primary fw-semibold px-3" style="border-radius: 10px; font-size:0.875rem;">
        <i class="bi bi-plus-lg me-1"></i> Add Product
    </a>
    @endif
@endsection

@push('styles')
<style>
    .action-btn-circle {
        width: 32px;
        height: 32px;
        border-radius: 8px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border: 1px solid transparent;
        transition: all 0.2s cubic-bezier(.4,0,.2,1);
        text-decoration: none;
        font-size: 0.85rem;
    }
    .action-btn-circle:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.08);
    }
    .action-btn-view {
        background: #f1f5f9;
        color: #475569;
        border-color: #e2e8f0;
    }
    .action-btn-view:hover {
        background: #e2e8f0;
        color: #0f172a;
    }
    .action-btn-edit {
        background: #ede9fe;
        color: #6366f1;
        border-color: #ddd6fe;
    }
    .action-btn-edit:hover {
        background: #ddd6fe;
        color: #4f46e5;
    }
    .action-btn-delete {
        background: #fee2e2;
        color: #ef4444;
        border-color: #fecaca;
    }
    .action-btn-delete:hover {
        background: #fecaca;
        color: #dc2626;
    }
</style>
@endpush

@section('content')

@if($pendingCount > 0)
    <div class="alert border-0 shadow-sm rounded-4 p-3.5 mb-4 d-flex align-items-center justify-content-between flex-wrap gap-3" 
        style="background: linear-gradient(135deg, #fffbeb 0%, #fef3c7 100%); border-left: 5px solid #d97706 !important;">
        <div class="d-flex align-items-center gap-3">
            <div style="width: 44px; height: 44px; border-radius: 12px; background: rgba(217, 119, 6, 0.15); display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                <i class="bi bi-hourglass-split text-warning fs-4" style="color: #b45309 !important;"></i>
            </div>
            <div>
                <h6 class="fw-bold mb-0.5" style="color: #92400e; font-size: 0.95rem;">
                    {{ $pendingCount }} {{ Str::plural('Product Submission', $pendingCount) }} Awaiting Your Review & Approval
                </h6>
                <p class="mb-0 text-muted small" style="font-size: 0.78rem;">
                    Product Managers have submitted new or revised products for live catalog publication.
                </p>
            </div>
        </div>
        <div class="d-flex align-items-center gap-2">
            <a href="{{ route('admin.products.index', ['status' => 'Pending_Approval']) }}" class="btn btn-warning text-dark fw-bold rounded-pill px-3.5 py-1.5 shadow-sm" style="font-size: 0.82rem;">
                <i class="bi bi-eye me-1"></i> Filter Pending Items ({{ $pendingCount }})
            </a>
        </div>
    </div>
@endif

{{-- Quick Status Filter Tabs --}}
<div class="d-flex flex-wrap gap-2 mb-3">
    <a href="{{ route('admin.products.index') }}" class="btn btn-sm rounded-pill px-3 py-1.5 fw-bold shadow-xs {{ !request('status') ? 'btn-dark text-white' : 'btn-outline-secondary bg-white text-dark' }}">
        All Products <span class="badge {{ !request('status') ? 'bg-white text-dark' : 'bg-secondary text-white' }} ms-1">{{ $totalCount }}</span>
    </a>
    <a href="{{ route('admin.products.index', ['status' => 'Pending_Approval']) }}" class="btn btn-sm rounded-pill px-3 py-1.5 fw-bold shadow-xs {{ request('status') === 'Pending_Approval' ? 'btn-warning text-dark' : 'btn-outline-warning text-dark bg-white' }}">
        <i class="bi bi-hourglass-split me-1 text-warning"></i> Pending Approvals 
        @if($pendingCount > 0)
            <span class="badge bg-danger text-white ms-1">{{ $pendingCount }}</span>
        @else
            <span class="badge bg-light text-muted ms-1">0</span>
        @endif
    </a>
    <a href="{{ route('admin.products.index', ['status' => 'Active']) }}" class="btn btn-sm rounded-pill px-3 py-1.5 fw-bold shadow-xs {{ request('status') === 'Active' ? 'btn-success text-white' : 'btn-outline-success text-success bg-white' }}">
        <i class="bi bi-check-circle me-1"></i> Live & Active <span class="badge {{ request('status') === 'Active' ? 'bg-white text-success' : 'bg-success text-white' }} ms-1">{{ $activeCount }}</span>
    </a>
    <a href="{{ route('admin.products.index', ['status' => 'Rejected']) }}" class="btn btn-sm rounded-pill px-3 py-1.5 fw-bold shadow-xs {{ request('status') === 'Rejected' ? 'btn-danger text-white' : 'btn-outline-danger text-danger bg-white' }}">
        <i class="bi bi-x-circle me-1"></i> Rejected <span class="badge {{ request('status') === 'Rejected' ? 'bg-white text-danger' : 'bg-danger text-white' }} ms-1">{{ $rejectedCount }}</span>
    </a>
    <a href="{{ route('admin.products.index', ['status' => 'Inactive']) }}" class="btn btn-sm rounded-pill px-3 py-1.5 fw-bold shadow-xs {{ request('status') === 'Inactive' ? 'btn-secondary text-white' : 'btn-outline-secondary bg-white text-dark' }}">
        Inactive <span class="badge {{ request('status') === 'Inactive' ? 'bg-white text-dark' : 'bg-secondary text-white' }} ms-1">{{ $inactiveCount }}</span>
    </a>
</div>

{{-- Filters Card --}}
<div class="card mb-4" style="border-radius: 14px !important;">
    <div class="card-body p-3">
        <form method="GET" action="{{ route('admin.products.index') }}">
            <div class="row g-2 align-items-center">
                <div class="col-md-3">
                    <div class="input-group">
                        <span class="input-group-text bg-white border-end-0 text-muted" style="border-radius: 10px 0 0 10px; border-color: #cbd5e1;">
                            <i class="bi bi-search"></i>
                        </span>
                        <input type="text" name="search" class="form-control border-start-0 ps-0" 
                            placeholder="Search product name or SKU..." 
                            value="{{ request('search') }}"
                            style="border-radius: 0 10px 10px 0; border-color: #cbd5e1; font-size: 0.85rem;">
                    </div>
                </div>
                <div class="col-md-2">
                    <select name="category_id" class="form-select" style="border-radius: 10px; border-color: #cbd5e1; font-size: 0.85rem;" onchange="this.form.submit()">
                        <option value="">All Categories</option>
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}" {{ request('category_id') == $category->id ? 'selected' : '' }}>
                                {{ $category->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <select name="brand_id" class="form-select" style="border-radius: 10px; border-color: #cbd5e1; font-size: 0.85rem;" onchange="this.form.submit()">
                        <option value="">All Brands</option>
                        @foreach($brands as $brand)
                            <option value="{{ $brand->id }}" {{ request('brand_id') == $brand->id ? 'selected' : '' }}>
                                {{ $brand->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <select name="status" class="form-select" style="border-radius: 10px; border-color: #cbd5e1; font-size: 0.85rem;" onchange="this.form.submit()">
                        <option value="">All Statuses</option>
                        <option value="Active" {{ request('status') == 'Active' ? 'selected' : '' }}>Active</option>
                        <option value="Pending_Approval" {{ request('status') == 'Pending_Approval' ? 'selected' : '' }}>Pending Approval</option>
                        <option value="Rejected" {{ request('status') == 'Rejected' ? 'selected' : '' }}>Rejected</option>
                        <option value="Inactive" {{ request('status') == 'Inactive' ? 'selected' : '' }}>Inactive</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <select name="sort" class="form-select" style="border-radius: 10px; border-color: #cbd5e1; font-size: 0.85rem;" onchange="this.form.submit()">
                        <option value="latest" {{ request('sort') == 'latest' ? 'selected' : '' }}>Sort: Latest</option>
                        <option value="oldest" {{ request('sort') == 'oldest' ? 'selected' : '' }}>Sort: Oldest</option>
                        <option value="price_low" {{ request('sort') == 'price_low' ? 'selected' : '' }}>Price: Low to High</option>
                        <option value="price_high" {{ request('sort') == 'price_high' ? 'selected' : '' }}>Price: High to Low</option>
                    </select>
                </div>
                <div class="col-md-1 d-flex gap-1">
                    <button type="submit" class="btn btn-primary w-100 fw-semibold" style="border-radius: 10px; font-size: 0.82rem;" title="Apply Filter">
                        <i class="bi bi-funnel"></i>
                    </button>
                    @if(request()->hasAny(['search', 'category_id', 'brand_id', 'status', 'sort']))
                        <a href="{{ route('admin.products.index') }}" class="btn btn-light" style="border-radius: 10px; border: 1px solid #e2e8f0; font-size: 0.82rem;" title="Reset Filters">
                            <i class="bi bi-x-lg"></i>
                        </a>
                    @endif
                </div>
            </div>
        </form>
    </div>
</div>

{{-- Products Table Card --}}
<div class="card" style="border-radius: 14px !important;">
    <div class="card-header d-flex align-items-center justify-content-between py-3" style="background:#fff; border-bottom: 1px solid #f1f5f9; border-radius: 14px 14px 0 0;">
        <h6 class="mb-0 fw-bold text-dark" style="font-size:0.95rem;">
            <i class="bi bi-box-seam text-primary me-2"></i>Product Catalog
        </h6>
        <span class="badge bg-light text-dark border fw-normal px-2.5 py-1.5" style="font-size:0.75rem; border-radius: 8px;">
            Total: {{ $products->total() }} products
        </span>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead style="background:#f8fafc; border-bottom: 1px solid #e2e8f0;">
                    <tr>
                        <th class="ps-4 text-uppercase text-muted fw-bold" style="font-size: 0.7rem; letter-spacing: 0.05em;">Product</th>
                        <th class="text-uppercase text-muted fw-bold" style="font-size: 0.7rem; letter-spacing: 0.05em;">Category & Brand</th>
                        <th class="text-uppercase text-muted fw-bold" style="font-size: 0.7rem; letter-spacing: 0.05em;">Price & Stock</th>
                        <th class="text-uppercase text-muted fw-bold" style="font-size: 0.7rem; letter-spacing: 0.05em;">Approval Status</th>
                        <th class="text-uppercase text-muted fw-bold" style="font-size: 0.7rem; letter-spacing: 0.05em;">Badges</th>
                        <th class="pe-4 text-end text-uppercase text-muted fw-bold" style="font-size: 0.7rem; letter-spacing: 0.05em;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($products as $product)
                    <tr id="product-row-{{ $product->id }}">
                        <td class="ps-4">
                            <div class="d-flex align-items-center">
                                @if($product->main_image)
                                    <img loading="lazy" src="{{ asset('storage/' . $product->main_image) }}" alt="{{ $product->name }}" 
                                        style="width: 44px; height: 44px; object-fit: cover; border-radius: 10px; border: 1px solid #e2e8f0;" class="me-3">
                                @else
                                    <div class="bg-light d-flex align-items-center justify-content-center me-3" 
                                        style="width: 44px; height: 44px; border-radius: 10px; border: 1px solid #e2e8f0; color:#94a3b8;">
                                        <i class="bi bi-image" style="font-size:1.1rem;"></i>
                                    </div>
                                @endif
                                <div>
                                    <a href="{{ route('admin.products.show', $product) }}" class="fw-bold text-dark text-decoration-none" style="font-size:0.875rem;">
                                        {{ Str::limit($product->name, 35) }}
                                    </a>
                                    <div style="font-size:0.72rem; color:#94a3b8;">SKU: {{ $product->sku }}</div>
                                </div>
                            </div>
                        </td>
                        <td>
                            <div class="fw-medium text-dark" style="font-size:0.83rem;">{{ $product->category->name ?? '—' }}</div>
                            <div style="font-size:0.72rem; color:#94a3b8;">{{ $product->brand->name ?? '—' }}</div>
                        </td>
                        <td>
                            <div class="fw-bold text-dark" style="font-size:0.88rem;">₹{{ number_format($product->price, 2) }}</div>
                            <div class="small {{ $product->stock <= 5 ? 'text-danger fw-bold' : 'text-muted' }}" style="font-size: 0.72rem;">Stock: {{ $product->stock }}</div>
                        </td>
                        <td>
                            @if($product->status == 'Active')
                                <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 rounded-pill px-2.5 py-1">
                                    <i class="bi bi-check-circle me-1"></i> Live & Active
                                </span>
                            @elseif($product->status == 'Pending_Approval')
                                <span class="badge bg-warning bg-opacity-10 text-warning border border-warning border-opacity-25 rounded-pill px-2.5 py-1 text-dark">
                                    <i class="bi bi-hourglass-split me-1 text-warning"></i> Pending Approval
                                </span>
                            @elseif($product->status == 'Rejected')
                                <div>
                                    <span class="badge bg-danger bg-opacity-10 text-danger border border-danger border-opacity-25 rounded-pill px-2.5 py-1">
                                        <i class="bi bi-x-circle me-1"></i> Rejected
                                    </span>
                                    @if($product->rejection_reason)
                                        <button type="button" class="btn btn-link btn-sm p-0 d-block text-danger small text-decoration-underline mt-1" 
                                            onclick="showRejectionReason('{{ addslashes($product->rejection_reason) }}')" style="font-size: 0.7rem;">
                                            View Reason
                                        </button>
                                    @endif
                                </div>
                            @else
                                <span class="badge bg-secondary bg-opacity-10 text-secondary border border-secondary border-opacity-25 rounded-pill px-2.5 py-1">
                                    Inactive
                                </span>
                            @endif
                        </td>
                        <td>
                            <div class="d-flex gap-1 flex-wrap">
                                @if($product->featured)
                                    <span class="badge" style="background:#fef3c7; color:#92400e; border-radius:6px; font-size:0.68rem; padding:0.25em 0.5em;"><i class="bi bi-star-fill me-1"></i>Featured</span>
                                @endif
                                @if($product->trending)
                                    <span class="badge" style="background:#e0e7ff; color:#3730a3; border-radius:6px; font-size:0.68rem; padding:0.25em 0.5em;"><i class="bi bi-graph-up-arrow me-1"></i>Trending</span>
                                @endif
                            </div>
                        </td>
                        <td class="pe-4 text-end">
                            <div class="d-flex align-items-center justify-content-end gap-1.5 flex-nowrap">
                                @if($product->status === 'Pending_Approval')
                                    <form action="{{ route('admin.products.approve', $product) }}" method="POST" class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-success rounded-pill px-2.5 py-1 fw-bold shadow-xs d-inline-flex align-items-center gap-1" title="Approve & Publish Live" style="font-size: 0.72rem;">
                                            <i class="bi bi-check-circle-fill"></i> Approve
                                        </button>
                                    </form>
                                    <button type="button" class="btn btn-sm btn-outline-danger rounded-pill px-2.5 py-1 shadow-xs d-inline-flex align-items-center gap-1 bg-white" title="Reject with Feedback" 
                                        onclick="promptRejectProduct('{{ route('admin.products.reject', $product) }}', '{{ addslashes($product->name) }}')" style="font-size: 0.72rem;">
                                        <i class="bi bi-x-circle-fill"></i> Reject
                                    </button>
                                @endif

                                <a href="{{ route('admin.products.show', $product) }}" class="action-btn-circle action-btn-view" title="Inspect & Review">
                                    <i class="bi bi-eye-fill"></i>
                                </a>
                                @if(auth()->user()->isSuperAdmin())
                                <a href="{{ route('admin.products.edit', $product) }}" class="action-btn-circle action-btn-edit" title="Edit Product">
                                    <i class="bi bi-pencil-fill"></i>
                                </a>
                                @endif
                                <button type="button" class="action-btn-circle action-btn-delete" 
                                    onclick="confirmDelete({{ $product->id }}, '{{ route('admin.products.destroy', $product) }}', this)" title="Delete Product">
                                    <i class="bi bi-trash3-fill"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center py-5">
                            <div class="py-4">
                                <i class="bi bi-box-seam text-muted opacity-50 display-6 mb-3 d-block"></i>
                                <h6 class="fw-bold text-dark mb-1">No Products Found</h6>
                                <p class="text-muted mb-0" style="font-size:0.82rem;">Try adjusting your filters or add a new product.</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($products->hasPages())
            <div class="px-4 py-3 border-top" style="background:#fff; border-radius: 0 0 14px 14px;">
                {{ $products->links('pagination::bootstrap-5') }}
            </div>
        @endif
    </div>
</div>

@push('scripts')
<script>
function confirmDelete(id, deleteUrl, btnElement) {
    Swal.fire({
        title: 'Delete Product?',
        text: "This action cannot be undone!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#ef4444',
        cancelButtonColor: '#64748b',
        confirmButtonText: 'Yes, Delete',
        cancelButtonText: 'Cancel'
    }).then((result) => {
        if (result.isConfirmed) {
            btnElement.disabled = true;
            fetch(deleteUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'X-HTTP-Method-Override': 'DELETE',
                    'Accept': 'application/json'
                }
            })
            .then(async res => {
                const data = await res.json();
                if (!res.ok || !data.success) {
                    throw new Error(data.message || 'Failed to delete product.');
                }
                return data;
            })
            .then(data => {
                const row = document.getElementById('product-row-' + id);
                if (row) {
                    row.style.transition = 'all 0.3s ease';
                    row.style.opacity = '0';
                    setTimeout(() => row.remove(), 300);
                }
                Swal.fire({
                    icon: 'success',
                    title: 'Deleted!',
                    text: data.message || 'Product deleted successfully.',
                    toast: true,
                    position: 'top-end',
                    showConfirmButton: false,
                    timer: 3000,
                    timerProgressBar: true
                });
            })
            .catch(err => {
                btnElement.disabled = false;
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: err.message || 'Failed to delete product.',
                    toast: true,
                    position: 'top-end',
                    showConfirmButton: false,
                    timer: 3000
                });
            });
        }
    });
}

function promptRejectProduct(rejectUrl, productName) {
    Swal.fire({
        title: 'Reject Product?',
        html: `Provide reason for rejecting <b>${productName}</b>:`,
        input: 'textarea',
        inputPlaceholder: 'e.g. Please update product description and upload higher quality image...',
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

function showRejectionReason(reason) {
    Swal.fire({
        title: 'Rejection Feedback',
        text: reason,
        icon: 'info',
        confirmButtonColor: '#3b82f6',
        confirmButtonText: 'Close'
    });
}
</script>
@endpush

@endsection

