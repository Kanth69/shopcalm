@extends('product-manager.layouts.app')

@section('header', 'Stock Movement History')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('product-manager.dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item"><a href="{{ route('product-manager.stock.dashboard') }}">Stock Management</a></li>
    <li class="breadcrumb-item active" aria-current="page">History</li>
@endsection

@section('actions')
    <a href="{{ route('product-manager.stock.dashboard') }}" class="btn btn-outline-secondary rounded-pill px-3">
        <i class="bi bi-arrow-left me-1"></i> Back to Inventory
    </a>
@endsection

@section('content')
<div class="card mb-4 border-0 shadow-sm rounded-4">
    <div class="card-body">
        <form method="GET" action="{{ route('product-manager.stock.history') }}">
            <div class="row g-3">
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
                        <option value="PURCHASE" {{ request('movement_type') == 'PURCHASE' ? 'selected' : '' }}>Purchase</option>
                        <option value="SALE" {{ request('movement_type') == 'SALE' ? 'selected' : '' }}>Sale</option>
                        <option value="ADJUSTMENT" {{ request('movement_type') == 'ADJUSTMENT' ? 'selected' : '' }}>Adjustment</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <select name="source" class="form-select" onchange="this.form.submit()">
                        <option value="">All Sources</option>
                        <option value="PURCHASE" {{ request('source') == 'PURCHASE' ? 'selected' : '' }}>Purchase</option>
                        <option value="ORDER" {{ request('source') == 'ORDER' ? 'selected' : '' }}>Order</option>
                        <option value="MANUAL" {{ request('source') == 'MANUAL' ? 'selected' : '' }}>Manual</option>
                    </select>
                </div>
                <div class="col-md-2 d-flex gap-2">
                    <button type="submit" class="btn btn-primary w-100 rounded-pill">Filter</button>
                    @if(request()->anyFilled(['product_id', 'movement_type', 'source']))
                        <a href="{{ route('product-manager.stock.history') }}" class="btn btn-light border rounded-pill"><i class="bi bi-x-lg"></i></a>
                    @endif
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
                        <th class="ps-3" style="font-size: 0.72rem;">Date</th>
                        <th style="font-size: 0.72rem;">Product</th>
                        <th style="font-size: 0.72rem;">Movement Details</th>
                        <th style="font-size: 0.72rem;">Stock Trail</th>
                        <th style="font-size: 0.72rem;">Notes</th>
                        <th class="text-end pe-3" style="font-size: 0.72rem;">User</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($movements as $movement)
                        <tr>
                            <td class="ps-3 text-muted small" style="white-space: nowrap;">
                                {{ $movement->created_at->format('d M, Y') }}<br>
                                <span style="font-size: 0.7rem;">{{ $movement->created_at->format('h:i A') }}</span>
                            </td>
                            <td>
                                @if($movement->product)
                                    <div class="d-flex align-items-center">
                                        @if($movement->product->main_image)
                                            <img src="{{ asset('storage/' . $movement->product->main_image) }}" class="rounded me-2 border shadow-xs" style="width: 40px; height: 40px; object-fit: cover;">
                                        @else
                                            <div class="bg-light rounded d-flex align-items-center justify-content-center me-2 text-muted border" style="width: 40px; height: 40px;">
                                                <i class="bi bi-box-seam"></i>
                                            </div>
                                        @endif
                                        <div>
                                            <a href="{{ route('product-manager.products.edit', $movement->product) }}" class="text-decoration-none fw-bold text-dark">
                                                {{ Str::limit($movement->product->name, 30) }}
                                            </a>
                                            <div class="small text-muted">SKU: {{ $movement->product->sku ?? 'N/A' }}</div>
                                        </div>
                                    </div>
                                @else
                                    <div class="d-flex align-items-center text-muted">
                                        <div class="bg-light rounded d-flex align-items-center justify-content-center me-2 text-muted" style="width: 40px; height: 40px;">
                                            <i class="bi bi-trash"></i>
                                        </div>
                                        <div>
                                            <span class="fw-semibold text-secondary">Deleted Product</span>
                                            <div class="small text-muted">ID #{{ $movement->product_id }}</div>
                                        </div>
                                    </div>
                                @endif
                            </td>
                            <td>
                                <div class="mb-1">
                                    @php
                                        $typeVal = is_object($movement->movement_type) ? $movement->movement_type->value : $movement->movement_type;
                                        $sourceVal = is_object($movement->source) ? $movement->source->value : $movement->source;
                                        $typeClass = match($typeVal) {
                                            'PURCHASE' => 'success',
                                            'SALE' => 'danger',
                                            'ADJUSTMENT' => 'warning text-dark',
                                            default => 'secondary'
                                        };
                                    @endphp
                                    <span class="badge bg-{{ $typeClass }} rounded-pill">{{ $typeVal }}</span>
                                    <span class="badge bg-light text-dark border rounded-pill ms-1">{{ $sourceVal }}</span>
                                </div>
                                <div>
                                    @if($movement->quantity > 0)
                                        <span class="text-success fw-bold">+{{ $movement->quantity }}</span>
                                    @elseif($movement->quantity < 0)
                                        <span class="text-danger fw-bold">{{ $movement->quantity }}</span>
                                    @else
                                        <span class="text-muted fw-bold">0</span>
                                    @endif
                                </div>
                            </td>
                            <td>
                                <span class="badge bg-light text-muted border font-monospace">
                                    {{ $movement->stock_before }} &rarr; <b class="text-dark">{{ $movement->stock_after }}</b>
                                </span>
                            </td>
                            <td style="max-width: 220px;">
                                <span class="small text-muted text-truncate d-block" title="{{ $movement->notes }}">
                                    {{ $movement->notes ?? '—' }}
                                </span>
                            </td>
                            <td class="text-end pe-3 text-muted small">
                                <i class="bi bi-person me-1"></i>{{ $movement->createdBy->name ?? 'System' }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-5 text-muted">
                                No stock movements recorded yet.
                            </td>
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
