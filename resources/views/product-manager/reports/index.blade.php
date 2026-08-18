@extends('product-manager.layouts.app')

@section('title', 'Reports')
@section('header', 'Reports & Intelligence')
@section('subheader', 'Sales velocity, inventory valuations, and stock risk detection')

@section('content')
<!-- Financial KPIs -->
<div class="row g-3 mb-4">
    <div class="col-sm-6 col-xl-4">
        <div class="card p-3 bg-white border-0 shadow-sm rounded-4 h-100">
            <div class="text-muted small fw-semibold text-uppercase mb-1" style="font-size: 0.72rem;">Total Warehouse Valuation</div>
            <div class="fs-3 fw-bold text-success">₹{{ number_format($totalInventoryValue, 2) }}</div>
        </div>
    </div>

    <div class="col-sm-6 col-xl-4">
        <div class="card p-3 bg-white border-0 shadow-sm rounded-4 h-100">
            <div class="text-muted small fw-semibold text-uppercase mb-1" style="font-size: 0.72rem;">Total Stock On-Hand</div>
            <div class="fs-3 fw-bold text-dark">{{ number_format($totalUnitsInStock) }} units</div>
        </div>
    </div>

    <div class="col-sm-6 col-xl-4">
        <div class="card p-3 bg-white border-0 shadow-sm rounded-4 h-100 {{ $riskProducts->count() > 0 ? 'border-warning border-2' : '' }}">
            <div class="text-muted small fw-semibold text-uppercase mb-1" style="font-size: 0.72rem;">Stock Risk Items</div>
            <div class="fs-3 fw-bold text-danger">{{ $riskProducts->count() }} SKUs</div>
        </div>
    </div>
</div>

<div class="row g-4 mb-4">
    <!-- Top Selling Products Velocity -->
    <div class="col-lg-7">
        <div class="card bg-white border-0 shadow-sm rounded-4 h-100 overflow-hidden">
            <div class="card-header bg-white py-3 border-bottom d-flex align-items-center justify-content-between">
                <h6 class="mb-0 fw-bold text-dark">Top Moving Products</h6>
                <span class="badge bg-light text-muted border">Top 10</span>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-4" style="font-size: 0.72rem;">Product</th>
                                <th style="font-size: 0.72rem;">Unit Price</th>
                                <th style="font-size: 0.72rem;">Units Sold</th>
                                <th class="pe-4 text-end" style="font-size: 0.72rem;">Revenue</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($topSelling as $tp)
                            <tr>
                                <td class="ps-4">
                                    <div class="fw-bold text-dark small text-truncate" style="max-width: 220px;">{{ $tp->name }}</div>
                                    <div class="text-muted" style="font-size: 0.7rem;">SKU: {{ $tp->sku }}</div>
                                </td>
                                <td><div class="small fw-semibold text-dark">₹{{ number_format($tp->price, 2) }}</div></td>
                                <td>
                                    <span class="badge bg-primary bg-opacity-10 text-primary rounded-pill px-2.5 py-1">
                                        {{ $tp->total_units_sold }} units
                                    </span>
                                </td>
                                <td class="pe-4 text-end fw-bold text-dark">₹{{ number_format($tp->total_revenue, 2) }}</td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="4" class="text-center py-4 text-muted small">No sales data recorded yet.</td>
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
        <div class="card bg-white border-0 shadow-sm rounded-4 h-100 overflow-hidden">
            <div class="card-header bg-white py-3 border-bottom d-flex align-items-center justify-content-between">
                <h6 class="mb-0 fw-bold text-dark">Category Valuations</h6>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-4" style="font-size: 0.72rem;">Category</th>
                                <th style="font-size: 0.72rem;">Units</th>
                                <th class="pe-4 text-end" style="font-size: 0.72rem;">Total Value</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($categoryBreakdown as $cat)
                            <tr>
                                <td class="ps-4">
                                    <div class="fw-bold text-dark small">{{ $cat->name }}</div>
                                    <div class="text-muted" style="font-size: 0.7rem;">{{ $cat->product_count }} SKUs</div>
                                </td>
                                <td><span class="small fw-semibold text-dark">{{ number_format($cat->total_stock) }}</span></td>
                                <td class="pe-4 text-end fw-bold text-success">₹{{ number_format($cat->total_value, 2) }}</td>
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

<!-- Stock Depletion Risks -->
<div class="card bg-white border-0 shadow-sm rounded-4 overflow-hidden mb-4">
    <div class="card-header bg-white py-3 border-bottom d-flex align-items-center justify-content-between">
        <h6 class="mb-0 fw-bold text-danger">Stock Depletion Risks</h6>
        <a href="{{ route('product-manager.stock.dashboard', ['filter' => 'low_stock']) }}" class="btn btn-sm btn-light border rounded-pill px-3" style="font-size: 0.78rem;">
            Manage Risks
        </a>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-4" style="font-size: 0.72rem;">Product</th>
                        <th style="font-size: 0.72rem;">Category & Brand</th>
                        <th style="font-size: 0.72rem;">Price</th>
                        <th style="font-size: 0.72rem;">Remaining Stock</th>
                        <th class="pe-4 text-end" style="font-size: 0.72rem;">Action</th>
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
                            <span class="badge {{ $rp->stock <= 0 ? 'bg-danger' : 'bg-warning text-dark' }} rounded-pill px-2.5 py-1" style="font-size: 0.72rem;">
                                {{ $rp->stock }} units
                            </span>
                        </td>
                        <td class="pe-4 text-end">
                            <a href="{{ route('product-manager.stock.form', ['product' => $rp, 'action' => 'add']) }}" class="btn btn-sm btn-outline-success rounded-pill px-3" style="font-size: 0.78rem;">
                                Restock
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
