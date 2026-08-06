@extends('admin.layouts.app')

@section('header', 'Add Stock - ' . $product->name)

@section('actions')
    <a href="{{ route('admin.stock.dashboard') }}" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left"></i> Back to Inventory
    </a>
@endsection

@section('content')
<div class="row justify-content-center">
    <div class="col-md-6">
        <div class="card mb-4 border-0 shadow-sm">
            <div class="card-header bg-white py-3">
                <h5 class="card-title mb-0 fw-bold text-success">Record Stock Purchase</h5>
            </div>
            <div class="card-body p-4">
                <form action="{{ route('admin.stock.add', $product) }}" method="POST">
                    @csrf

                    <div class="mb-4 text-center">
                        @if($product->main_image)
                            <img src="{{ asset('storage/' . $product->main_image) }}" alt="{{ $product->name }}" class="img-thumbnail rounded-3 mb-3" style="max-height: 120px; object-fit: contain;">
                        @endif
                        <h5 class="fw-bold mb-1">{{ $product->name }}</h5>
                        <p class="text-muted small mb-3">SKU: {{ $product->sku }}</p>
                        <div class="d-inline-block px-4 py-2 bg-light rounded-pill">
                            <span class="text-muted small">Current Level:</span>
                            <span class="fw-bold ms-1 text-dark">{{ $product->stock }} units</span>
                        </div>
                    </div>

                    <hr class="my-4 opacity-10">

                    <div class="mb-3">
                        <label for="quantity" class="form-label fw-bold small">Quantity to Add <span class="text-danger">*</span></label>
                        <input type="number" class="form-control form-control-lg @error('quantity') is-invalid @enderror" id="quantity" name="quantity" value="{{ old('quantity', 1) }}" min="1" required>
                        <div class="form-text small text-muted">Enter the number of units received from the supplier.</div>
                        @error('quantity')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="notes" class="form-label fw-bold small">Audit Notes (Optional)</label>
                        <textarea class="form-control @error('notes') is-invalid @enderror" id="notes" name="notes" rows="3" placeholder="e.g., Purchased from supplier, Invoice #998877">{{ old('notes') }}</textarea>
                        <div class="form-text small text-muted">Include invoice numbers or supplier names for tracking.</div>
                        @error('notes')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="d-grid mt-5">
                        <button type="submit" class="btn btn-success btn-lg py-3 rounded-3 shadow-sm fw-bold">
                            <i class="bi bi-plus-circle-fill me-2"></i> Update Inventory
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
