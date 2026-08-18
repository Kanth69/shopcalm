@extends('product-manager.layouts.app')

@section('title', 'Stock Movement History')
@section('header', 'Stock Movement History')
@section('subheader', 'Complete chronological audit log of all inbound purchases, reductions, and manual adjustments')

@section('content')
<div class="card bg-white shadow-sm border-0 mb-4 p-3">
    <form method="GET" action="{{ route('product-manager.stock.history') }}">
        <div class="row g-2 align-items-center">
            <div class="col-md-4">
                <select name="product_id" class="form-select" onchange="this.form.submit()">
                    <option value="">All Products</option>
                    @foreach($products as $p)
                        <option value="{{ $p->id }}" {{ request('product_id') == $p->id ? 'selected' : '' }}>{{ $p->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <select name="movement_type" class="form-select" onchange="this.form.submit()">
                    <option value="">All Movement Types</option>
                    <option value="PURCHASE" {{ request('movement_type') === 'PURCHASE' ? 'selected' : '' }}>PURCHASE (Inbound Stock)</option>
                    <option value="ADJUSTMENT" {{ request('movement_type') === 'ADJUSTMENT' ? 'selected' : '' }}>ADJUSTMENT (Manual / Write-off)</option>
                    <option value="SALE" {{ request('movement_type') === 'SALE' ? 'selected' : '' }}>SALE (Customer Orders)</option>
                </select>
            </div>
            <div class="col-md-3">
                <select name="source" class="form-select" onchange="this.form.submit()">
                    <option value="">All Sources</option>
                    <option value="PURCHASE" {{ request('source') === 'PURCHASE' ? 'selected' : '' }}>Purchase</option>
                    <option value="MANUAL" {{ request('source') === 'MANUAL' ? 'selected' : '' }}>Manual</option>
                    <option value="ORDER" {{ request('source') === 'ORDER' ? 'selected' : '' }}>Order</option>
                </select>
            </div>
            <div class="col-md-2 d-flex gap-1">
                <button type="submit" class="btn btn-primary w-100"><i class="bi bi-funnel me-1"></i> Filter</button>
                @if(request()->hasAny(['product_id', 'movement_type', 'source']))
                    <a href="{{ route('product-manager.stock.history') }}" class="btn btn-light border"><i class="bi bi-x-lg"></i></a>
                @endif
            </div>
        </div>
    </form>
</div>

<div class="card bg-white shadow-sm border-0 overflow-hidden mb-4">
    <div class="card-header bg-white py-3 border-bottom d-flex align-items-center justify-content-between">
        <h6 class="mb-0 fw-bold text-dark"><i class="bi bi-clock-history text-primary me-2"></i>Audit Log Entries ({{ $movements->total() }})</h6>
        <a href="{{ route('product-manager.stock.dashboard') }}" class="btn btn-sm btn-outline-secondary rounded-pill px-3">
            <i class="bi bi-arrow-left me-1"></i> Back to Inventory
        </a>
    </div>

    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-4 text-uppercase text-muted fw-bold" style="font-size: 0.7rem;">Timestamp</th>
                        <th class="text-uppercase text-muted fw-bold" style="font-size: 0.7rem;">Product</th>
                        <th class="text-uppercase text-muted fw-bold" style="font-size: 0.7rem;">Type & Source</th>
                        <th class="text-uppercase text-muted fw-bold" style="font-size: 0.7rem;">Quantity Delta</th>
                        <th class="text-uppercase text-muted fw-bold" style="font-size: 0.7rem;">Stock Trail</th>
                        <th class="text-uppercase text-muted fw-bold" style="font-size: 0.7rem;">Notes</th>
                        <th class="pe-4 text-end text-uppercase text-muted fw-bold" style="font-size: 0.7rem;">Logged By</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($movements as $m)
                    <tr>
                        <td class="ps-4 text-muted small">
                            <div class="fw-bold text-dark">{{ $m->created_at->format('M d, Y') }}</div>
                            <div style="font-size: 0.7rem;">{{ $m->created_at->format('h:i A') }}</div>
                        </td>
                        <td>
                            <div class="fw-bold text-dark small">{{ $m->product->name ?? 'Deleted Product' }}</div>
                            <div class="text-muted" style="font-size: 0.7rem;">SKU: {{ $m->product->sku ?? '—' }}</div>
                        </td>
                        <td>
                            @php
                                $typeVal = is_object($m->movement_type) ? $m->movement_type->value : $m->movement_type;
                                $sourceVal = is_object($m->source) ? $m->source->value : $m->source;
                            @endphp
                            <span class="badge bg-light text-dark border rounded-pill px-2.5 py-1">
                                {{ $typeVal }}
                            </span>
                            <div class="text-muted" style="font-size: 0.7rem;">Src: {{ $sourceVal }}</div>
                        </td>
                        <td>
                            <span class="fs-6 fw-bold {{ $m->quantity > 0 ? 'text-success' : 'text-danger' }}">
                                {{ $m->quantity > 0 ? '+' . $m->quantity : $m->quantity }}
                            </span>
                        </td>
                        <td>
                            <div class="small text-muted font-monospace">
                                {{ $m->stock_before }} &rarr; <span class="fw-bold text-dark">{{ $m->stock_after }}</span>
                            </div>
                        </td>
                        <td style="max-width: 250px;">
                            <span class="small text-muted text-truncate d-block" title="{{ $m->notes }}">{{ $m->notes ?? '—' }}</span>
                        </td>
                        <td class="pe-4 text-end text-muted small">
                            <i class="bi bi-person me-1"></i>{{ $m->createdBy->name ?? 'System' }}
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center py-5 text-muted">No stock movement history recorded yet.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($movements->hasPages())
            <div class="px-4 py-3 border-top bg-white">
                {{ $movements->links('pagination::bootstrap-5') }}
            </div>
        @endif
    </div>
</div>
@endsection
