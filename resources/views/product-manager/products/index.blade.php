@extends('product-manager.layouts.app')

@section('header', 'Products')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('product-manager.dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item active" aria-current="page">Products</li>
@endsection

@section('actions')
    <a href="{{ route('product-manager.products.create') }}" class="btn btn-primary fw-semibold px-3" style="border-radius: 10px; font-size:0.875rem;">
        <i class="bi bi-plus-lg me-1"></i> Add Product
    </a>
@endsection

@section('content')

{{-- Quick Status Filter Tabs --}}
<div class="d-flex flex-wrap gap-2 mb-3">
    <a href="{{ route('product-manager.products.index') }}" class="btn btn-sm rounded-pill px-3 py-1.5 fw-medium {{ !request('status') ? 'btn-dark' : 'btn-outline-secondary' }}">
        All Products <span class="badge bg-secondary ms-1">{{ $totalCount }}</span>
    </a>
    <a href="{{ route('product-manager.products.index', array_merge(request()->query(), ['status' => 'Pending_Approval'])) }}" class="btn btn-sm rounded-pill px-3 py-1.5 fw-medium {{ request('status') === 'Pending_Approval' ? 'btn-warning text-dark' : 'btn-outline-warning text-dark' }}">
        <i class="bi bi-hourglass-split me-1"></i> Pending Approvals 
        @if($pendingCount > 0)
            <span class="badge bg-danger text-white ms-1">{{ $pendingCount }}</span>
        @else
            <span class="badge bg-light text-muted ms-1">0</span>
        @endif
    </a>
    <a href="{{ route('product-manager.products.index', array_merge(request()->query(), ['status' => 'Active'])) }}" class="btn btn-sm rounded-pill px-3 py-1.5 fw-medium {{ request('status') === 'Active' ? 'btn-success' : 'btn-outline-success' }}">
        <i class="bi bi-check-circle me-1"></i> Live & Active <span class="badge bg-success bg-opacity-25 text-success ms-1">{{ $activeCount }}</span>
    </a>
    <a href="{{ route('product-manager.products.index', array_merge(request()->query(), ['status' => 'Rejected'])) }}" class="btn btn-sm rounded-pill px-3 py-1.5 fw-medium {{ request('status') === 'Rejected' ? 'btn-danger' : 'btn-outline-danger' }}">
        <i class="bi bi-x-circle me-1"></i> Rejected <span class="badge bg-danger bg-opacity-25 text-danger ms-1">{{ $rejectedCount }}</span>
    </a>
    <a href="{{ route('product-manager.products.index', array_merge(request()->query(), ['status' => 'Inactive'])) }}" class="btn btn-sm rounded-pill px-3 py-1.5 fw-medium {{ request('status') === 'Inactive' ? 'btn-secondary' : 'btn-outline-secondary' }}">
        Inactive <span class="badge bg-light text-dark ms-1">{{ $inactiveCount }}</span>
    </a>
</div>

{{-- Filters Card --}}
<div class="card mb-4" style="border-radius: 14px !important;">
    <div class="card-body p-3">
        <form method="GET" action="{{ route('product-manager.products.index') }}">
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
                        <a href="{{ route('product-manager.products.index') }}" class="btn btn-light" style="border-radius: 10px; border: 1px solid #e2e8f0; font-size: 0.82rem;" title="Reset Filters">
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
                                    <a href="{{ route('product-manager.products.edit', $product) }}" class="fw-bold text-dark text-decoration-none" style="font-size:0.875rem;">
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
                            <div class="d-flex flex-wrap gap-1">
                                @if($product->featured)
                                    <span class="badge bg-warning bg-opacity-10 text-warning text-dark border border-warning border-opacity-25" style="border-radius: 6px; font-size: 0.68rem;">Featured</span>
                                @endif
                                @if($product->trending)
                                    <span class="badge bg-info bg-opacity-10 text-info text-dark border border-info border-opacity-25" style="border-radius: 6px; font-size: 0.68rem;">Trending</span>
                                @endif
                            </div>
                        </td>
                        <td class="pe-4 text-end">
                            <div class="btn-group" role="group">
                                <a href="{{ route('product-manager.products.edit', $product) }}" class="btn btn-sm btn-light text-primary" style="border: 1px solid #cbd5e1; font-size:0.78rem;" title="Edit Product">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                @if($product->status === 'Rejected')
                                    <form action="{{ route('product-manager.products.resubmit', $product) }}" method="POST" class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-light text-warning" style="border: 1px solid #cbd5e1; font-size:0.78rem;" title="Resubmit for Approval">
                                            <i class="bi bi-arrow-repeat"></i>
                                        </button>
                                    </form>
                                @endif
                                <a href="{{ route('product-manager.stock.form', ['product' => $product, 'action' => 'adjust']) }}" class="btn btn-sm btn-light text-secondary" style="border: 1px solid #cbd5e1; font-size:0.78rem;" title="Adjust Stock">
                                    <i class="bi bi-stack"></i>
                                </a>
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
function showRejectionReason(reason) {
    Swal.fire({
        title: 'Rejection Feedback',
        text: reason,
        icon: 'info',
        confirmButtonColor: '#6366f1',
        confirmButtonText: 'Close'
    });
}
</script>
@endpush

@endsection
