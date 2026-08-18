@extends('product-manager.layouts.app')

@section('title', 'Inventory & Stock')
@section('header', 'Inventory & Stock')
@section('subheader', 'Monitor on-hand inventory levels and manage stock adjustments')

@section('content')
<!-- Metric Cards -->
<div class="row g-3 mb-4">
    <div class="col-sm-6 col-xl-3">
        <div class="card p-3 bg-white border-0 shadow-sm rounded-4 h-100">
            <div class="text-muted small fw-semibold text-uppercase mb-1" style="font-size: 0.72rem;">Total On-Hand Units</div>
            <div class="fs-3 fw-bold text-dark">{{ number_format($totalUnits) }}</div>
        </div>
    </div>

    <div class="col-sm-6 col-xl-3">
        <div class="card p-3 bg-white border-0 shadow-sm rounded-4 h-100">
            <div class="text-muted small fw-semibold text-uppercase mb-1" style="font-size: 0.72rem;">Inventory Valuation</div>
            <div class="fs-3 fw-bold text-success">₹{{ number_format($totalValuation, 2) }}</div>
        </div>
    </div>

    <div class="col-sm-6 col-xl-3">
        <div class="card p-3 bg-white border-0 shadow-sm rounded-4 h-100 {{ $lowStockCount > 0 ? 'border-warning border-2' : '' }}">
            <div class="text-muted small fw-semibold text-uppercase mb-1" style="font-size: 0.72rem;">Low Stock Items</div>
            <div class="fs-3 fw-bold text-warning">{{ $lowStockCount }}</div>
        </div>
    </div>

    <div class="col-sm-6 col-xl-3">
        <div class="card p-3 bg-white border-0 shadow-sm rounded-4 h-100 {{ $outOfStockCount > 0 ? 'border-danger border-2' : '' }}">
            <div class="text-muted small fw-semibold text-uppercase mb-1" style="font-size: 0.72rem;">Out of Stock</div>
            <div class="fs-3 fw-bold text-danger">{{ $outOfStockCount }}</div>
        </div>
    </div>
</div>

<!-- Tabs Bar -->
<div class="d-flex flex-wrap gap-2 mb-3">
    <a href="{{ route('product-manager.stock.dashboard') }}" class="btn btn-sm rounded-pill px-3 py-1.5 fw-medium {{ !request('filter') ? 'btn-dark' : 'btn-outline-secondary' }}">
        All Stock ({{ $totalItems }})
    </a>
    <a href="{{ route('product-manager.stock.dashboard', ['filter' => 'low_stock']) }}" class="btn btn-sm rounded-pill px-3 py-1.5 fw-medium {{ request('filter') === 'low_stock' ? 'btn-warning text-dark' : 'btn-outline-warning text-dark' }}">
        Low Stock ({{ $lowStockCount }})
    </a>
    <a href="{{ route('product-manager.stock.dashboard', ['filter' => 'out_of_stock']) }}" class="btn btn-sm rounded-pill px-3 py-1.5 fw-medium {{ request('filter') === 'out_of_stock' ? 'btn-danger' : 'btn-outline-danger' }}">
        Out of Stock ({{ $outOfStockCount }})
    </a>
</div>

<!-- Stock Table -->
<div class="card bg-white border-0 shadow-sm rounded-4 overflow-hidden mb-4">
    <div class="card-header bg-white py-3 border-bottom d-flex align-items-center justify-content-between">
        <h6 class="mb-0 fw-bold text-dark">Stock Roster</h6>
        <a href="{{ route('product-manager.stock.history') }}" class="btn btn-sm btn-light border rounded-pill px-3">
            <i class="bi bi-clock-history me-1"></i> Movement Log
        </a>
    </div>

    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-4" style="font-size: 0.72rem;">Product</th>
                        <th style="font-size: 0.72rem;">Unit Price</th>
                        <th style="font-size: 0.72rem;">Stock Units</th>
                        <th style="font-size: 0.72rem;">Status</th>
                        <th class="pe-4 text-end" style="font-size: 0.72rem;">Quick Adjust</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($products as $prod)
                    <tr>
                        <td class="ps-4">
                            <div class="d-flex align-items-center gap-3">
                                @if($prod->main_image)
                                    <img src="{{ asset('storage/' . $prod->main_image) }}" class="rounded-3 border" style="width: 40px; height: 40px; object-fit: cover;">
                                @else
                                    <div class="rounded-3 bg-light d-flex align-items-center justify-content-center text-muted border" style="width: 40px; height: 40px;">
                                        <i class="bi bi-image"></i>
                                    </div>
                                @endif
                                <div>
                                    <div class="fw-bold text-dark">{{ $prod->name }}</div>
                                    <div class="text-muted small" style="font-size: 0.72rem;">SKU: {{ $prod->sku }}</div>
                                </div>
                            </div>
                        </td>
                        <td><div class="fw-bold text-dark">₹{{ number_format($prod->price, 2) }}</div></td>
                        <td>
                            <div class="fs-6 fw-bold {{ $prod->stock <= 0 ? 'text-danger' : ($prod->stock <= 10 ? 'text-warning' : 'text-dark') }}">
                                {{ $prod->stock }} units
                            </div>
                        </td>
                        <td>
                            @if($prod->stock <= 0)
                                <span class="badge bg-danger bg-opacity-10 text-danger rounded-pill px-2.5 py-1" style="font-size: 0.72rem;">Out of Stock</span>
                            @elseif($prod->stock <= 10)
                                <span class="badge bg-warning bg-opacity-10 text-warning text-dark border border-warning border-opacity-25 rounded-pill px-2.5 py-1" style="font-size: 0.72rem;">Low Stock</span>
                            @else
                                <span class="badge bg-success bg-opacity-10 text-success rounded-pill px-2.5 py-1" style="font-size: 0.72rem;">In Stock</span>
                            @endif
                        </td>
                        <td class="pe-4 text-end">
                            <div class="btn-group btn-group-sm">
                                <a href="{{ route('product-manager.stock.form', ['product' => $prod, 'action' => 'add']) }}" class="btn btn-outline-success" title="Add Inbound Stock">
                                    <i class="bi bi-plus-lg"></i> Add
                                </a>
                                <a href="{{ route('product-manager.stock.form', ['product' => $prod, 'action' => 'reduce']) }}" class="btn btn-outline-danger" title="Reduce Stock">
                                    <i class="bi bi-dash-lg"></i> Reduce
                                </a>
                                <a href="{{ route('product-manager.stock.form', ['product' => $prod, 'action' => 'adjust']) }}" class="btn btn-outline-primary" title="Adjust Stock">
                                    <i class="bi bi-sliders"></i> Adjust
                                </a>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="text-center py-5 text-muted">No stock items found.</td>
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
