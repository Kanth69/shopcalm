@extends('admin.layouts.app')

@section('header', 'Adjust Stock - ' . $product->name)

@section('actions')
    <a href="{{ route('admin.products.index') }}" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left"></i> Back to Products
    </a>
@endsection

@section('content')
<div class="row">
    <div class="col-md-6">
        <div class="card mb-4 border-0 shadow-sm">
            <div class="card-header bg-white py-3 border-bottom border-warning">
                <h5 class="card-title mb-0 text-warning-emphasis"><i class="bi bi-exclamation-triangle me-2"></i>Manual Stock Adjustment</h5>
            </div>
            <div class="card-body">
                <form action="{{ route('admin.stock.adjust', $product) }}" method="POST">
                    @csrf

                    <div class="mb-4 d-flex align-items-center bg-light p-3 rounded">
                        @if($product->main_image)
                            <img src="{{ asset('storage/' . $product->main_image) }}" alt="{{ $product->name }}" class="rounded me-3" style="width: 60px; height: 60px; object-fit: cover;">
                        @endif
                        <div>
                            <h6 class="mb-1">{{ $product->name }}</h6>
                            <span class="badge bg-secondary">Current Stock: <span id="current_stock_display">{{ $product->stock }}</span></span>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="new_stock" class="form-label fw-bold">New Final Stock Quantity <span class="text-danger">*</span></label>
                        <input type="number" class="form-control form-control-lg @error('new_stock') is-invalid @enderror" id="new_stock" name="new_stock" value="{{ old('new_stock', $product->stock) }}" min="0" required oninput="calculateDifference()">
                        <div class="form-text mt-2" id="difference_text">
                            Difference: <span class="badge bg-light text-dark border">0</span>
                        </div>
                        @error('new_stock')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="notes" class="form-label fw-bold">Reason for Adjustment <span class="text-danger">*</span></label>
                        <textarea class="form-control @error('notes') is-invalid @enderror" id="notes" name="notes" rows="3" required placeholder="e.g., Inventory recount, damaged goods written off, lost in transit.">{{ old('notes') }}</textarea>
                        <div class="form-text">A reason is mandatory for manual adjustments to maintain an accurate audit trail.</div>
                        @error('notes')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="d-grid mt-4">
                        <button type="submit" class="btn btn-warning btn-lg">
                            <i class="bi bi-sliders me-1"></i> Confirm Adjustment
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="col-md-6">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white py-3">
                <h5 class="card-title mb-0">Recent History for this Product</h5>
            </div>
            <div class="card-body p-0">
                @php
                    $productHistory = $product->stockMovements()->with('createdBy')->latest()->take(5)->get();
                @endphp

                @if($productHistory->isNotEmpty())
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="ps-3">Date</th>
                                    <th>Change</th>
                                    <th>Reason</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($productHistory as $history)
                                    <tr>
                                        <td class="ps-3 text-muted small">{{ $history->created_at->format('d M y') }}</td>
                                        <td>
                                            @if($history->stock_after > $history->stock_before)
                                                <span class="text-success fw-bold">+{{ $history->quantity }}</span>
                                            @elseif($history->stock_after < $history->stock_before)
                                                <span class="text-danger fw-bold">-{{ $history->quantity }}</span>
                                            @else
                                                <span class="text-muted fw-bold">0</span>
                                            @endif
                                            <small class="text-muted d-block">{{ $history->source->value }}</small>
                                        </td>
                                        <td class="small text-muted">{{ Str::limit($history->notes, 30) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="p-4 text-center text-muted">No history found.</div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    function calculateDifference() {
        const currentStock = parseInt(document.getElementById('current_stock_display').innerText);
        const newStockInput = document.getElementById('new_stock').value;
        const differenceText = document.getElementById('difference_text');

        if (newStockInput === '') {
            differenceText.innerHTML = 'Difference: <span class="badge bg-light text-dark border">0</span>';
            return;
        }

        const newStock = parseInt(newStockInput);
        const difference = newStock - currentStock;

        if (difference > 0) {
            differenceText.innerHTML = `Difference: <span class="badge bg-success">+${difference}</span> (Adding stock)`;
        } else if (difference < 0) {
            differenceText.innerHTML = `Difference: <span class="badge bg-danger">${difference}</span> (Removing stock)`;
        } else {
            differenceText.innerHTML = 'Difference: <span class="badge bg-light text-dark border">0</span> (No change)';
        }
    }
</script>
@endpush
