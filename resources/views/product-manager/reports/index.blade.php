@extends('product-manager.layouts.app')

@section('header', 'Product & Inventory Reports')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('product-manager.dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item active" aria-current="page">Reports & Analytics</li>
@endsection

@section('content')

<!-- Financial & Inventory High-Level KPIs -->
<div class="row g-3 mb-4">
    <div class="col-sm-6 col-xl-4">
        <div class="card border-0 shadow-sm rounded-4 p-3 bg-white h-100 border-start border-success border-4">
            <div class="d-flex align-items-center gap-3">
                <div class="rounded-4 p-3 bg-success bg-opacity-10 text-success d-flex align-items-center justify-content-center" style="width: 52px; height: 52px;">
                    <i class="bi bi-wallet2 fs-4"></i>
                </div>
                <div>
                    <div class="text-muted small fw-semibold text-uppercase" style="font-size: 0.72rem; letter-spacing: 0.5px;">Warehouse Valuation</div>
                    <div class="fs-4 fw-bold text-success">₹{{ number_format($totalInventoryValue, 2) }}</div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-sm-6 col-xl-4">
        <div class="card border-0 shadow-sm rounded-4 p-3 bg-white h-100 border-start border-primary border-4">
            <div class="d-flex align-items-center gap-3">
                <div class="rounded-4 p-3 bg-primary bg-opacity-10 text-primary d-flex align-items-center justify-content-center" style="width: 52px; height: 52px;">
                    <i class="bi bi-boxes fs-4"></i>
                </div>
                <div>
                    <div class="text-muted small fw-semibold text-uppercase" style="font-size: 0.72rem; letter-spacing: 0.5px;">Total Units On-Hand</div>
                    <div class="fs-4 fw-bold text-dark">{{ number_format($totalUnitsInStock) }} units</div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-sm-6 col-xl-4">
        <div class="card border-0 shadow-sm rounded-4 p-3 bg-white h-100 border-start border-danger border-4">
            <div class="d-flex align-items-center gap-3">
                <div class="rounded-4 p-3 bg-danger bg-opacity-10 text-danger d-flex align-items-center justify-content-center" style="width: 52px; height: 52px;">
                    <i class="bi bi-exclamation-triangle fs-4"></i>
                </div>
                <div>
                    <div class="text-muted small fw-semibold text-uppercase" style="font-size: 0.72rem; letter-spacing: 0.5px;">Stock Depletion Risks</div>
                    <div class="fs-4 fw-bold text-danger">{{ $riskProducts->count() }} SKUs At Risk</div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row g-4 mb-4">
    <!-- Top Selling Products Velocity -->
    <div class="col-lg-7">
        <div class="card border-0 shadow-sm rounded-4 h-100 overflow-hidden">
            <div class="card-header bg-white py-3 border-bottom d-flex align-items-center justify-content-between">
                <h6 class="mb-0 fw-bold text-dark">
                    <i class="bi bi-fire text-warning me-2"></i>Product Sales Velocity (Top 10)
                </h6>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-3" style="font-size: 0.72rem;">Product</th>
                                <th style="font-size: 0.72rem;">Price</th>
                                <th style="font-size: 0.72rem;">Units Sold</th>
                                <th class="pe-3 text-end" style="font-size: 0.72rem;">Gross Revenue</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($topSelling as $tp)
                            <tr>
                                <td class="ps-3">
                                    <div class="fw-bold text-dark small text-truncate" style="max-width: 220px;">{{ $tp->name }}</div>
                                    <div class="text-muted" style="font-size: 0.7rem;">SKU: {{ $tp->sku }}</div>
                                </td>
                                <td><div class="small fw-semibold text-dark">₹{{ number_format($tp->price, 2) }}</div></td>
                                <td>
                                    <span class="badge bg-primary bg-opacity-10 text-primary rounded-pill px-2.5 py-1">
                                        {{ $tp->total_units_sold }} units
                                    </span>
                                </td>
                                <td class="pe-3 text-end fw-bold text-dark">₹{{ number_format($tp->total_revenue, 2) }}</td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="4" class="text-center py-4 text-muted small">No sales recorded yet.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Category Valuation Breakdown -->
    <div class="col-lg-5">
        <div class="card border-0 shadow-sm rounded-4 h-100 overflow-hidden">
            <div class="card-header bg-white py-3 border-bottom d-flex align-items-center justify-content-between">
                <h6 class="mb-0 fw-bold text-dark">
                    <i class="bi bi-pie-chart text-primary me-2"></i>Category Valuations
                </h6>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-3" style="font-size: 0.72rem;">Category</th>
                                <th style="font-size: 0.72rem;">Stock Units</th>
                                <th class="pe-3 text-end" style="font-size: 0.72rem;">Total Value</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($categoryBreakdown as $cat)
                            <tr>
                                <td class="ps-3">
                                    <div class="fw-bold text-dark small">{{ $cat->name }}</div>
                                    <div class="text-muted" style="font-size: 0.7rem;">{{ $cat->product_count }} SKUs</div>
                                </td>
                                <td><span class="small fw-semibold text-dark">{{ number_format($cat->total_stock) }}</span></td>
                                <td class="pe-3 text-end fw-bold text-success">₹{{ number_format($cat->total_value, 2) }}</td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="3" class="text-center py-4 text-muted small">No category data.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Stock Depletion Risks Watchlist -->
<div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-4">
    <div class="card-header bg-white py-3 border-bottom d-flex align-items-center justify-content-between">
        <h6 class="mb-0 fw-bold text-danger">
            <i class="bi bi-slash-circle me-2"></i>Stockout Depletion Watchlist
        </h6>
        <a href="{{ route('product-manager.stock.dashboard', ['filter' => 'low_stock']) }}" class="btn btn-sm btn-outline-danger rounded-pill px-3">
            Manage All Risk SKUs
        </a>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-4 text-uppercase text-muted fw-bold" style="font-size: 0.7rem;">Product</th>
                        <th class="text-uppercase text-muted fw-bold" style="font-size: 0.7rem;">Category & Brand</th>
                        <th class="text-uppercase text-muted fw-bold" style="font-size: 0.7rem;">Unit Price</th>
                        <th class="text-uppercase text-muted fw-bold" style="font-size: 0.7rem;">Stock Level</th>
                        <th class="pe-4 text-end text-uppercase text-muted fw-bold" style="font-size: 0.7rem;">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($riskProducts as $rp)
                    <tr>
                        <td class="ps-4">
                            <div class="fw-bold text-dark">{{ $rp->name }}</div>
                            <div class="text-muted small" style="font-size: 0.72rem;">SKU: {{ $rp->sku }}</div>
                        </td>
                        <td>
                            <div class="small text-dark">{{ $rp->category->name ?? '—' }}</div>
                            <div class="text-muted" style="font-size: 0.7rem;">{{ $rp->brand->name ?? '—' }}</div>
                        </td>
                        <td><div class="fw-bold text-dark">₹{{ number_format($rp->price, 2) }}</div></td>
                        <td>
                            <span class="badge {{ $rp->stock <= 0 ? 'bg-danger' : 'bg-warning text-dark' }} rounded-pill px-2.5 py-1">
                                {{ $rp->stock }} units
                            </span>
                        </td>
                        <td class="pe-4 text-end">
                            <a href="{{ route('product-manager.stock.form', ['product' => $rp, 'action' => 'add']) }}" class="btn btn-sm btn-outline-success rounded-pill px-3">
                                <i class="bi bi-plus-lg me-1"></i> Restock
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="text-center py-4 text-muted small">No stockout risks detected.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

@endsection
