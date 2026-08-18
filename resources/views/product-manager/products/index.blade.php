@extends('product-manager.layouts.app')

@section('title', 'All Products')
@section('header', 'Products')
@section('subheader', 'Catalog products and specifications')

@section('content')
<!-- Status Tabs -->
<div class="d-flex flex-wrap gap-2 mb-3">
    <a href="{{ route('product-manager.products.index') }}" class="btn btn-sm rounded-pill px-3 py-1.5 fw-medium {{ !request('status') ? 'btn-dark' : 'btn-outline-secondary' }}">
        All Products <span class="badge bg-secondary ms-1">{{ $totalCount }}</span>
    </a>
    <a href="{{ route('product-manager.products.pending') }}" class="btn btn-sm rounded-pill px-3 py-1.5 fw-medium {{ request('status') === 'Pending_Approval' ? 'btn-warning text-dark' : 'btn-outline-warning text-dark' }}">
        Pending Approval <span class="badge bg-warning text-dark ms-1">{{ $pendingCount }}</span>
    </a>
    <a href="{{ route('product-manager.products.rejected') }}" class="btn btn-sm rounded-pill px-3 py-1.5 fw-medium {{ request('status') === 'Rejected' ? 'btn-danger' : 'btn-outline-danger' }}">
        Rejected <span class="badge bg-danger ms-1">{{ $rejectedCount }}</span>
    </a>
    <a href="{{ route('product-manager.products.index', ['status' => 'Active']) }}" class="btn btn-sm rounded-pill px-3 py-1.5 fw-medium {{ request('status') === 'Active' ? 'btn-success' : 'btn-outline-success' }}">
        Active <span class="badge bg-success bg-opacity-25 text-success ms-1">{{ $activeCount }}</span>
    </a>
</div>

<!-- Filters Card -->
<div class="card bg-white border-0 shadow-sm rounded-4 p-3 mb-4">
    <form method="GET" action="{{ route('product-manager.products.index') }}">
        <div class="row g-2 align-items-center">
            <div class="col-md-3">
                <div class="input-group">
                    <span class="input-group-text bg-white border-end-0 text-muted"><i class="bi bi-search"></i></span>
                    <input type="text" name="search" class="form-control border-start-0 ps-0" placeholder="Search name or SKU..." value="{{ request('search') }}" style="font-size: 0.85rem;">
                </div>
            </div>
            <div class="col-md-2">
                <select name="category_id" class="form-select" onchange="this.form.submit()" style="font-size: 0.85rem;">
                    <option value="">All Categories</option>
                    @foreach($categories as $cat)
                        <option value="{{ $cat->id }}" {{ request('category_id') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <select name="brand_id" class="form-select" onchange="this.form.submit()" style="font-size: 0.85rem;">
                    <option value="">All Brands</option>
                    @foreach($brands as $b)
                        <option value="{{ $b->id }}" {{ request('brand_id') == $b->id ? 'selected' : '' }}>{{ $b->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <select name="stock_status" class="form-select" onchange="this.form.submit()" style="font-size: 0.85rem;">
                    <option value="">All Stock Levels</option>
                    <option value="in_stock" {{ request('stock_status') === 'in_stock' ? 'selected' : '' }}>In Stock (>10)</option>
                    <option value="low_stock" {{ request('stock_status') === 'low_stock' ? 'selected' : '' }}>Low Stock (≤10)</option>
                    <option value="out_of_stock" {{ request('stock_status') === 'out_of_stock' ? 'selected' : '' }}>Out of Stock (0)</option>
                </select>
            </div>
            <div class="col-md-2">
                <select name="sort" class="form-select" onchange="this.form.submit()" style="font-size: 0.85rem;">
                    <option value="latest" {{ request('sort') === 'latest' ? 'selected' : '' }}>Sort: Newest</option>
                    <option value="price_asc" {{ request('sort') === 'price_asc' ? 'selected' : '' }}>Price: Low to High</option>
                    <option value="price_desc" {{ request('sort') === 'price_desc' ? 'selected' : '' }}>Price: High to Low</option>
                    <option value="stock_asc" {{ request('sort') === 'stock_asc' ? 'selected' : '' }}>Stock: Low to High</option>
                </select>
            </div>
            <div class="col-md-1 d-flex gap-1">
                <button type="submit" class="btn btn-primary w-100" title="Apply Filter"><i class="bi bi-funnel"></i></button>
                @if(request()->hasAny(['search', 'category_id', 'brand_id', 'stock_status', 'sort', 'status']))
                    <a href="{{ route('product-manager.products.index') }}" class="btn btn-light border" title="Reset Filters"><i class="bi bi-x-lg"></i></a>
                @endif
            </div>
        </div>
    </form>
</div>

<!-- Products Table -->
<div class="card bg-white border-0 shadow-sm rounded-4 overflow-hidden mb-4">
    <div class="card-header bg-white py-3 border-bottom d-flex align-items-center justify-content-between">
        <h6 class="mb-0 fw-bold text-dark">Catalog List</h6>
        <a href="{{ route('product-manager.products.create') }}" class="btn btn-sm btn-pm-primary rounded-pill px-3">
            <i class="bi bi-plus-lg me-1"></i> Add Product
        </a>
    </div>

    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-4" style="font-size: 0.72rem;">Product</th>
                        <th style="font-size: 0.72rem;">Category & Brand</th>
                        <th style="font-size: 0.72rem;">Price & Stock</th>
                        <th style="font-size: 0.72rem;">Status</th>
                        <th class="pe-4 text-end" style="font-size: 0.72rem;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($products as $prod)
                    <tr>
                        <td class="ps-4">
                            <div class="d-flex align-items-center gap-3">
                                @if($prod->main_image)
                                    <img src="{{ asset('storage/' . $prod->main_image) }}" alt="{{ $prod->name }}" class="rounded-3 border" style="width: 42px; height: 42px; object-fit: cover;">
                                @else
                                    <div class="rounded-3 bg-light d-flex align-items-center justify-content-center text-muted border" style="width: 42px; height: 42px;">
                                        <i class="bi bi-image fs-5"></i>
                                    </div>
                                @endif
                                <div>
                                    <div class="fw-bold text-dark" style="font-size: 0.88rem;">{{ $prod->name }}</div>
                                    <div class="text-muted small" style="font-size: 0.72rem;">SKU: {{ $prod->sku }}</div>
                                </div>
                            </div>
                        </td>
                        <td>
                            <div class="fw-medium text-dark small">{{ $prod->category->name ?? '—' }}</div>
                            <div class="text-muted" style="font-size: 0.72rem;">{{ $prod->brand->name ?? '—' }}</div>
                        </td>
                        <td>
                            <div class="fw-bold text-dark">₹{{ number_format($prod->price, 2) }}</div>
                            <div class="small {{ $prod->stock <= 5 ? 'text-danger fw-bold' : 'text-muted' }}" style="font-size: 0.72rem;">
                                Stock: {{ $prod->stock }} units
                            </div>
                        </td>
                        <td>
                            @if($prod->status === 'Active')
                                <span class="badge bg-success bg-opacity-10 text-success rounded-pill px-2.5 py-1" style="font-size: 0.72rem;">
                                    <i class="bi bi-check-circle me-1"></i> Active
                                </span>
                            @elseif($prod->status === 'Pending_Approval')
                                <span class="badge bg-warning bg-opacity-10 text-warning text-dark border border-warning border-opacity-25 rounded-pill px-2.5 py-1" style="font-size: 0.72rem;">
                                    <i class="bi bi-hourglass-split me-1 text-warning"></i> Pending Approval
                                </span>
                            @elseif($prod->status === 'Rejected')
                                <span class="badge bg-danger bg-opacity-10 text-danger rounded-pill px-2.5 py-1" style="font-size: 0.72rem;">
                                    <i class="bi bi-x-circle me-1"></i> Rejected
                                </span>
                            @else
                                <span class="badge bg-secondary bg-opacity-10 text-secondary rounded-pill px-2.5 py-1" style="font-size: 0.72rem;">
                                    Inactive
                                </span>
                            @endif
                        </td>
                        <td class="pe-4 text-end">
                            <div class="btn-group btn-group-sm">
                                <a href="{{ route('product-manager.products.edit', $prod) }}" class="btn btn-outline-primary" title="Edit Product">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                @if($prod->status === 'Rejected')
                                    <form action="{{ route('product-manager.products.resubmit', $prod) }}" method="POST" class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn btn-outline-warning" title="Resubmit for Approval">
                                            <i class="bi bi-arrow-repeat"></i>
                                        </button>
                                    </form>
                                @endif
                                <a href="{{ route('product-manager.stock.form', ['product' => $prod, 'action' => 'adjust']) }}" class="btn btn-outline-secondary" title="Adjust Stock">
                                    <i class="bi bi-stack"></i>
                                </a>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="text-center py-5 text-muted">
                            <i class="bi bi-box-seam display-6 d-block mb-2 opacity-50"></i>
                            <div class="fw-bold text-dark mb-1">No Products Found</div>
                            <small>Add your first product to start catalog submissions.</small>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($products->hasPages())
            <div class="px-4 py-3 border-top bg-white">
                {{ $products->links('pagination::bootstrap-5') }}
            </div>
        @endif
    </div>
</div>
@endsection
