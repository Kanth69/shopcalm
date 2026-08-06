@extends('admin.layouts.app')

@section('header', 'Stock Movement History')

@section('actions')
    <a href="{{ route('admin.stock.dashboard') }}" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left"></i> Back to Dashboard
    </a>
@endsection

@section('content')
<div class="card mb-4 border-0 shadow-sm">
    <div class="card-body">
        <form method="GET" action="{{ route('admin.stock.history') }}">
            <div class="row g-3">
                <div class="col-md-4">
                    <input type="text" name="search" class="form-control" placeholder="Search by Product Name or SKU..." value="{{ request('search') }}">
                </div>
                <div class="col-md-3">
                    <select name="type" class="form-select">
                        <option value="">All Movement Types</option>
                        <option value="PURCHASE" {{ request('type') == 'PURCHASE' ? 'selected' : '' }}>Purchase</option>
                        <option value="SALE" {{ request('type') == 'SALE' ? 'selected' : '' }}>Sale</option>
                        <option value="ADJUSTMENT" {{ request('type') == 'ADJUSTMENT' ? 'selected' : '' }}>Adjustment</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <select name="source" class="form-select">
                        <option value="">All Sources</option>
                        <option value="PURCHASE" {{ request('source') == 'PURCHASE' ? 'selected' : '' }}>Purchase (Admin)</option>
                        <option value="ORDER" {{ request('source') == 'ORDER' ? 'selected' : '' }}>Order (Customer)</option>
                        <option value="MANUAL" {{ request('source') == 'MANUAL' ? 'selected' : '' }}>Manual (Admin)</option>
                    </select>
                </div>
                <div class="col-md-2 d-flex gap-2">
                    <button type="submit" class="btn btn-primary w-100">Filter</button>
                    @if(request()->anyFilled(['search', 'type', 'source']))
                        <a href="{{ route('admin.stock.history') }}" class="btn btn-light"><i class="bi bi-x-lg"></i></a>
                    @endif
                </div>
            </div>
        </form>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        @if($movements->isNotEmpty())
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-3">Date</th>
                            <th>Product</th>
                            <th>Movement Details</th>
                            <th>Reference</th>
                            <th>Notes</th>
                            <th class="text-end pe-3">User</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($movements as $movement)
                            <tr>
                                <td class="ps-3 text-muted small" style="white-space: nowrap;">
                                    {{ $movement->created_at->format('d M, Y') }}<br>
                                    {{ $movement->created_at->format('H:i:s') }}
                                </td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        @if($movement->product->main_image)
                                            <img src="{{ asset('storage/' . $movement->product->main_image) }}" class="rounded me-2" style="width: 40px; height: 40px; object-fit: cover;">
                                        @endif
                                        <div>
                                            <a href="{{ route('admin.products.show', $movement->product) }}" class="text-decoration-none fw-bold text-dark">
                                                {{ Str::limit($movement->product->name, 30) }}
                                            </a>
                                            <div class="small text-muted">SKU: {{ $movement->product->sku }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div class="mb-1">
                                        @php
                                            $typeClass = match($movement->movement_type->value) {
                                                'PURCHASE' => 'success',
                                                'SALE' => 'danger',
                                                'ADJUSTMENT' => 'warning text-dark',
                                                default => 'secondary'
                                            };
                                        @endphp
                                        <span class="badge bg-{{ $typeClass }}">{{ $movement->movement_type->value }}</span>
                                        <span class="badge bg-light text-dark border ms-1">{{ $movement->source->value }}</span>
                                    </div>
                                    <div>
                                        @if($movement->stock_after > $movement->stock_before)
                                            <span class="text-success fw-bold">+{{ $movement->quantity }}</span>
                                        @elseif($movement->stock_after < $movement->stock_before)
                                            <span class="text-danger fw-bold">-{{ $movement->quantity }}</span>
                                        @else
                                            <span class="text-muted fw-bold">0</span>
                                        @endif
                                        <span class="text-muted small ms-2">({{ $movement->stock_before }} &rarr; {{ $movement->stock_after }})</span>
                                    </div>
                                </td>
                                <td>
                                    @if($movement->reference_type === \App\Models\Order::class)
                                        <a href="{{ route('admin.orders.show', $movement->reference_id) }}" class="badge bg-primary text-decoration-none">
                                            Order #{{ \App\Models\Order::find($movement->reference_id)->order_number ?? $movement->reference_id }}
                                        </a>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td class="small">
                                    {{ Str::limit($movement->notes, 50) }}
                                </td>
                                <td class="text-end pe-3">
                                    @if($movement->createdBy)
                                        <span class="badge bg-light text-dark border">
                                            <i class="bi bi-person"></i> {{ $movement->createdBy->name }}
                                        </span>
                                    @else
                                        <span class="badge bg-secondary text-white">
                                            <i class="bi bi-robot"></i> System
                                        </span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="text-center py-5 text-muted">
                <i class="bi bi-journal-x fs-1 d-block mb-3"></i>
                <h5>No Stock Movements Found</h5>
                <p>Try adjusting your search or filters.</p>
            </div>
        @endif
    </div>
    @if($movements->hasPages())
        <div class="card-footer bg-white py-3">
            {{ $movements->links('pagination::bootstrap-5') }}
        </div>
    @endif
</div>
@endsection
