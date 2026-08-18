@extends('product-manager.layouts.app')

@php
    $actionTitle = match($action) {
        'add' => 'Add Inbound Stock',
        'reduce' => 'Reduce Stock',
        'adjust' => 'Direct Physical Count Adjustment',
        default => 'Stock Operation'
    };
    $cardTitle = match($action) {
        'add' => 'Record Stock Purchase',
        'reduce' => 'Record Stock Reduction / Write-Off',
        'adjust' => 'Record Physical Stock Adjustment',
        default => 'Stock Operation'
    };
    $colorClass = match($action) {
        'add' => 'success',
        'reduce' => 'danger',
        'adjust' => 'warning text-dark',
        default => 'primary'
    };
    $route = match($action) {
        'add' => route('product-manager.stock.add', $product),
        'reduce' => route('product-manager.stock.reduce', $product),
        'adjust' => route('product-manager.stock.adjust', $product),
        default => route('product-manager.stock.dashboard')
    };
@endphp

@section('header', $actionTitle . ' - ' . $product->name)

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('product-manager.dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item"><a href="{{ route('product-manager.stock.dashboard') }}">Stock Management</a></li>
    <li class="breadcrumb-item active" aria-current="page">{{ $actionTitle }}</li>
@endsection

@section('actions')
    <a href="{{ route('product-manager.stock.dashboard') }}" class="btn btn-outline-secondary rounded-pill px-3">
        <i class="bi bi-arrow-left me-1"></i> Back to Inventory
    </a>
@endsection

@section('content')
<div class="row justify-content-center">
    <div class="col-md-6">
        <div class="card mb-4 border-0 shadow-sm rounded-4 overflow-hidden">
            <div class="card-header bg-white py-3 border-bottom">
                <h5 class="card-title mb-0 fw-bold text-{{ $colorClass }}">{{ $cardTitle }}</h5>
            </div>
            <div class="card-body p-4">
                <form action="{{ $route }}" method="POST">
                    @csrf

                    <div class="mb-4 text-center">
                        @if($product->main_image)
                            <img src="{{ asset('storage/' . $product->main_image) }}" alt="{{ $product->name }}" class="img-thumbnail rounded-3 mb-3 border shadow-xs" style="max-height: 120px; object-fit: contain;">
                        @endif
                        <h5 class="fw-bold mb-1 text-dark">{{ $product->name }}</h5>
                        <p class="text-muted small mb-3">SKU: {{ $product->sku }}</p>
                        <div class="d-inline-block px-4 py-2 bg-light rounded-pill border">
                            <span class="text-muted small">Current Level:</span>
                            <span class="fw-bold ms-1 text-dark">{{ $product->stock }} units</span>
                        </div>
                    </div>

                    <hr class="my-4 opacity-10">

                    @if($action === 'add')
                        <div class="mb-3">
                            <label for="quantity" class="form-label fw-bold small">Quantity to Add <span class="text-danger">*</span></label>
                            <input type="number" class="form-control form-control-lg @error('quantity') is-invalid @enderror" id="quantity" name="quantity" value="{{ old('quantity', 1) }}" min="1" required autofocus>
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

                        <div class="d-grid mt-4">
                            <button type="submit" class="btn btn-success btn-lg py-3 rounded-pill shadow-sm fw-bold">
                                <i class="bi bi-plus-circle-fill me-2"></i> Update Inventory
                            </button>
                        </div>
                    @elseif($action === 'reduce')
                        <div class="mb-3">
                            <label for="quantity" class="form-label fw-bold small">Quantity to Reduce <span class="text-danger">*</span></label>
                            <input type="number" class="form-control form-control-lg @error('quantity') is-invalid @enderror" id="quantity" name="quantity" value="{{ old('quantity', 1) }}" min="1" max="{{ $product->stock }}" required autofocus>
                            <div class="form-text small text-muted">Cannot exceed current stock level ({{ $product->stock }} units).</div>
                            @error('quantity')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="notes" class="form-label fw-bold small">Reason for Reduction <span class="text-danger">*</span></label>
                            <textarea class="form-control @error('notes') is-invalid @enderror" id="notes" name="notes" rows="3" placeholder="e.g., Damaged in transit, Expired batch #402" required>{{ old('notes') }}</textarea>
                            <div class="form-text small text-muted">Specify reason for reduction (e.g., damage, loss, internal use).</div>
                            @error('notes')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="d-grid mt-4">
                            <button type="submit" class="btn btn-danger btn-lg py-3 rounded-pill shadow-sm fw-bold">
                                <i class="bi bi-dash-circle-fill me-2"></i> Reduce Stock
                            </button>
                        </div>
                    @elseif($action === 'adjust')
                        <div class="mb-3">
                            <label for="target_stock" class="form-label fw-bold small">New Physical Stock Count <span class="text-danger">*</span></label>
                            <input type="number" class="form-control form-control-lg @error('target_stock') is-invalid @enderror" id="target_stock" name="target_stock" value="{{ old('target_stock', $product->stock) }}" min="0" required autofocus>
                            <div class="form-text small text-muted">Directly override stock level following a physical count audit.</div>
                            @error('target_stock')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="notes" class="form-label fw-bold small">Audit Reason <span class="text-danger">*</span></label>
                            <textarea class="form-control @error('notes') is-invalid @enderror" id="notes" name="notes" rows="3" placeholder="e.g., Monthly physical inventory reconciliation audit" required>{{ old('notes') }}</textarea>
                            <div class="form-text small text-muted">Reason for discrepancy vs recorded balance.</div>
                            @error('notes')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="d-grid mt-4">
                            <button type="submit" class="btn btn-warning text-dark btn-lg py-3 rounded-pill shadow-sm fw-bold">
                                <i class="bi bi-sliders me-2"></i> Save Adjustment
                            </button>
                        </div>
                    @endif
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
