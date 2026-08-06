@extends('admin.layouts.app')

@section('header', 'Stock Details')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.stock.dashboard') }}" class="text-decoration-none">Inventory</a></li>
    <li class="breadcrumb-item active" aria-current="page">{{ $product->name }}</li>
@endsection

@section('content')
<div class="row g-4">
    <div class="col-md-4">
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body text-center py-4">
                <img src="{{ asset('storage/' . $product->main_image) }}" class="rounded shadow-sm mb-3 border" style="width: 150px; height: 150px; object-fit: cover;">
                <h5 class="fw-bold mb-1">{{ $product->name }}</h5>
                <p class="text-muted small mb-3">SKU: {{ $product->sku }}</p>
                <div class="d-flex justify-content-center gap-2 mb-4">
                    <span class="badge bg-light text-dark border">{{ $product->category->name }}</span>
                    <span class="badge bg-light text-dark border">{{ $product->brand->name }}</span>
                </div>

                <div class="row g-2">
                    <div class="col-6">
                        <div class="p-3 bg-light rounded-3">
                            <h4 class="fw-bold mb-0 text-primary">{{ $product->stock }}</h4>
                            <small class="text-muted text-uppercase fw-bold" style="font-size: 0.65rem;">Current Stock</small>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="p-3 bg-light rounded-3">
                            <h4 class="fw-bold mb-0 text-success">{{ $product->stock }}</h4>
                            <small class="text-muted text-uppercase fw-bold" style="font-size: 0.65rem;">Available</small>
                        </div>
                    </div>
                </div>

                <div class="d-grid gap-2 mt-4">
                    <a href="{{ route('admin.stock.add-form', $product) }}" class="btn btn-success"><i class="bi bi-plus-lg me-2"></i>Add Stock</a>
                    <a href="{{ route('admin.stock.adjust-form', $product) }}" class="btn btn-outline-warning"><i class="bi bi-sliders me-2"></i>Adjust Total Stock</a>
                </div>
            </div>
        </div>

        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white py-3">
                <h6 class="mb-0 fw-bold">Inventory Policy</h6>
            </div>
            <div class="card-body">
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-muted">Reorder Level:</span>
                    <span class="fw-bold">{{ $product->low_stock_alert }} units</span>
                </div>
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-muted">Reserved:</span>
                    <span class="fw-bold">0 units</span>
                </div>
                <div class="d-flex justify-content-between">
                    <span class="text-muted">Status:</span>
                    @if($product->stock <= 0)
                        <span class="badge bg-danger">Out of Stock</span>
                    @elseif($product->stock <= $product->low_stock_alert)
                        <span class="badge bg-warning text-dark">Low Stock</span>
                    @else
                        <span class="badge bg-success">Healthy</span>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-8">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">Stock Movement History</h5>
                <span class="badge bg-primary rounded-pill">Total Log: {{ $movements->total() }}</span>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-3 small">Date & Time</th>
                                <th class="small">Action Type</th>
                                <th class="small">Source</th>
                                <th class="small text-center">Qty</th>
                                <th class="small">Ledger (Before &rarr; After)</th>
                                <th class="small">Performed By</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($movements as $movement)
                            <tr>
                                <td class="ps-3">
                                    <div class="small fw-bold">{{ $movement->created_at->format('d M, Y') }}</div>
                                    <div class="text-muted" style="font-size: 0.7rem;">{{ $movement->created_at->format('h:i A') }}</div>
                                </td>
                                <td>
                                    <span class="badge bg-{{ $movement->movement_type->value == 'SALE' ? 'danger' : ($movement->movement_type->value == 'PURCHASE' ? 'success' : 'warning text-dark') }} small">
                                        {{ $movement->movement_type->value }}
                                    </span>
                                </td>
                                <td><span class="badge bg-light text-dark border small">{{ $movement->source->value }}</span></td>
                                <td class="text-center fw-bold">{{ $movement->quantity }}</td>
                                <td class="small">
                                    <span class="text-muted">{{ $movement->stock_before }}</span>
                                    <i class="bi bi-arrow-right mx-1 opacity-50"></i>
                                    <span class="fw-bold text-dark">{{ $movement->stock_after }}</span>
                                </td>
                                <td class="small">{{ $movement->createdBy->name ?? 'System' }}</td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="text-center py-5 text-muted">No stock movement history found for this product.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            @if($movements->hasPages())
                <div class="card-footer bg-white border-top-0 py-3">
                    {{ $movements->links('pagination::bootstrap-5') }}
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
