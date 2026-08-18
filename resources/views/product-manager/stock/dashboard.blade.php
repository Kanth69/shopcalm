@extends('product-manager.layouts.app')

@section('header', 'Stock & Inventory Management')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('product-manager.dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item active" aria-current="page">Stock Management</li>
@endsection

@section('actions')
    <a href="{{ route('product-manager.stock.history') }}" class="btn btn-outline-primary rounded-pill px-3">
        <i class="bi bi-clock-history me-1"></i> Stock Movement History
    </a>
@endsection

@section('content')
<div class="row g-4 mb-4">
    <div class="col-md-2">
        <div class="card h-100 border-0 shadow-sm border-start border-primary border-4 rounded-4">
            <div class="card-body">
                <h6 class="text-muted small text-uppercase mb-1" style="font-size: 0.72rem;">Total SKUs</h6>
                <h3 class="mb-0 fw-bold">{{ number_format($totalItems) }}</h3>
            </div>
        </div>
    </div>
    <div class="col-md-2">
        <div class="card h-100 border-0 shadow-sm border-start border-info border-4 rounded-4">
            <div class="card-body">
                <h6 class="text-muted small text-uppercase mb-1" style="font-size: 0.72rem;">In Stock Units</h6>
                <h3 class="mb-0 fw-bold">{{ number_format($totalUnits) }}</h3>
            </div>
        </div>
    </div>
    <div class="col-md-2">
        <div class="card h-100 border-0 shadow-sm border-start border-warning border-4 rounded-4">
            <div class="card-body">
                <h6 class="text-muted small text-uppercase mb-1" style="font-size: 0.72rem;">Low Stock</h6>
                <h3 class="mb-0 fw-bold">{{ number_format($lowStockCount) }}</h3>
            </div>
        </div>
    </div>
    <div class="col-md-2">
        <div class="card h-100 border-0 shadow-sm border-start border-danger border-4 rounded-4">
            <div class="card-body">
                <h6 class="text-muted small text-uppercase mb-1" style="font-size: 0.72rem;">Out of Stock</h6>
                <h3 class="mb-0 fw-bold">{{ number_format($outOfStockCount) }}</h3>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card h-100 border-0 shadow-sm border-start border-success border-4 rounded-4">
            <div class="card-body">
                <h6 class="text-muted small text-uppercase mb-1" style="font-size: 0.72rem;">Total Inventory Value</h6>
                <h3 class="mb-0 fw-bold text-success">₹{{ number_format($totalValuation, 2) }}</h3>
            </div>
        </div>
    </div>
</div>

<div class="card border-0 shadow-sm rounded-4 mb-4">
    <div class="card-body">
        <form method="GET" action="{{ route('product-manager.stock.dashboard') }}">
            <div class="row g-3">
                <div class="col-md-4">
                    <input type="text" name="search" class="form-control" placeholder="Search by name or SKU..." value="{{ request('search') }}">
                </div>
                <div class="col-md-3">
                    <select name="filter" class="form-select">
                        <option value="">All Stock Levels</option>
                        <option value="low_stock" {{ request('filter') == 'low_stock' ? 'selected' : '' }}>Low Stock (≤10)</option>
                        <option value="out_of_stock" {{ request('filter') == 'out_of_stock' ? 'selected' : '' }}>Out of Stock (0)</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <button type="submit" class="btn btn-primary w-100 rounded-pill">Filter Results</button>
                </div>
                <div class="col-md-2">
                    <a href="{{ route('product-manager.stock.dashboard') }}" class="btn btn-light border w-100 rounded-pill">Reset</a>
                </div>
            </div>
        </form>
    </div>
</div>

<div class="card border-0 shadow-sm rounded-4 overflow-hidden">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-3" style="font-size: 0.72rem;">Product</th>
                        <th style="font-size: 0.72rem;">SKU</th>
                        <th class="text-center" style="font-size: 0.72rem;">Current Stock</th>
                        <th class="text-center" style="font-size: 0.72rem;">Available</th>
                        <th class="text-center" style="font-size: 0.72rem;">Status</th>
                        <th style="font-size: 0.72rem;">Last Updated</th>
                        <th class="text-end pe-3" style="font-size: 0.72rem;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($products as $product)
                    <tr>
                        <td class="ps-3">
                            <div class="d-flex align-items-center">
                                @if($product->main_image)
                                    <img src="{{ asset('storage/' . $product->main_image) }}" class="rounded me-2 border shadow-xs" style="width: 42px; height: 42px; object-fit: cover;">
                                @else
                                    <div class="bg-light rounded d-flex align-items-center justify-content-center me-2 text-muted border" style="width: 42px; height: 42px;">
                                        <i class="bi bi-box-seam"></i>
                                    </div>
                                @endif
                                <div>
                                    <div class="fw-bold text-dark">{{ Str::limit($product->name, 35) }}</div>
                                    <div class="small text-muted">{{ $product->category->name ?? 'No Category' }}</div>
                                </div>
                            </div>
                        </td>
                        <td><span class="text-muted small fw-medium">{{ $product->sku }}</span></td>
                        <td class="text-center fw-bold fs-6">{{ $product->stock }}</td>
                        <td class="text-center text-primary fw-bold">{{ $product->stock }}</td>
                        <td class="text-center">
                            @if($product->stock <= 0)
                                <span class="badge bg-danger rounded-pill px-2.5 py-1" style="font-size: 0.72rem;">Out of Stock</span>
                            @elseif($product->stock <= 10)
                                <span class="badge bg-warning text-dark rounded-pill px-2.5 py-1" style="font-size: 0.72rem;">Low Stock</span>
                            @else
                                <span class="badge bg-success rounded-pill px-2.5 py-1" style="font-size: 0.72rem;">In Stock</span>
                            @endif
                        </td>
                        <td class="small text-muted">{{ $product->updated_at->diffForHumans() }}</td>
                        <td class="text-end pe-3">
                            <div class="btn-group btn-group-sm shadow-xs">
                                <a href="{{ route('product-manager.stock.form', ['product' => $product, 'action' => 'add']) }}" class="btn btn-outline-success" title="Add Inbound Stock">
                                    <i class="bi bi-plus-lg"></i>
                                </a>
                                <a href="{{ route('product-manager.stock.form', ['product' => $product, 'action' => 'reduce']) }}" class="btn btn-outline-danger" title="Reduce Stock">
                                    <i class="bi bi-dash-lg"></i>
                                </a>
                                <a href="{{ route('product-manager.stock.form', ['product' => $product, 'action' => 'adjust']) }}" class="btn btn-outline-warning text-dark" title="Adjust Stock">
                                    <i class="bi bi-sliders"></i>
                                </a>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center py-5 text-muted">No stock items found.</td>
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
