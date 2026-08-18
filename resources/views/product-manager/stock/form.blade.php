@extends('product-manager.layouts.app')

@php
    $actionTitle = match($action) {
        'add' => 'Add Inbound Stock',
        'reduce' => 'Reduce Stock',
        'adjust' => 'Direct Stock Adjustment',
        default => 'Stock Operation'
    };
    $route = match($action) {
        'add' => route('product-manager.stock.add', $product),
        'reduce' => route('product-manager.stock.reduce', $product),
        'adjust' => route('product-manager.stock.adjust', $product),
        default => route('product-manager.stock.dashboard')
    };
@endphp

@section('title', $actionTitle)
@section('header', $actionTitle)
@section('subheader', 'Update inventory stock count')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-5">
        <div class="card bg-white border-0 shadow-sm rounded-4 mb-4">
            <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
                <h6 class="mb-0 fw-bold text-dark">{{ $actionTitle }}</h6>
                <a href="{{ route('product-manager.stock.dashboard') }}" class="btn btn-sm btn-light border rounded-pill px-3">
                    <i class="bi bi-arrow-left me-1"></i> Back
                </a>
            </div>

            <div class="card-body p-4">
                <div class="p-3 bg-light rounded-3 mb-4 d-flex align-items-center gap-3">
                    @if($product->main_image)
                        <img src="{{ asset('storage/' . $product->main_image) }}" class="rounded-3 border" style="width: 48px; height: 48px; object-fit: cover;">
                    @else
                        <div class="rounded-3 bg-white d-flex align-items-center justify-content-center border" style="width: 48px; height: 48px;">
                            <i class="bi bi-box-seam text-muted"></i>
                        </div>
                    @endif
                    <div>
                        <div class="fw-bold text-dark">{{ $product->name }}</div>
                        <div class="text-muted small">Current Stock: <b class="text-dark">{{ $product->stock }} units</b></div>
                    </div>
                </div>

                <form action="{{ $route }}" method="POST">
                    @csrf

                    @if($action === 'add')
                        <div class="mb-3">
                            <label class="form-label fw-bold small text-dark">Quantity to Add <span class="text-danger">*</span></label>
                            <input type="number" name="quantity" min="1" class="form-control fw-bold" placeholder="e.g. 25" required autofocus>
                        </div>
                        <div class="mb-4">
                            <label class="form-label fw-bold small text-dark">Notes (Optional)</label>
                            <input type="text" name="notes" class="form-control" placeholder="e.g. Inbound shipment #PO-102">
                        </div>
                    @elseif($action === 'reduce')
                        <div class="mb-3">
                            <label class="form-label fw-bold small text-dark">Quantity to Remove <span class="text-danger">*</span></label>
                            <input type="number" name="quantity" min="1" max="{{ $product->stock }}" class="form-control fw-bold" placeholder="e.g. 5" required autofocus>
                        </div>
                        <div class="mb-4">
                            <label class="form-label fw-bold small text-dark">Reason <span class="text-danger">*</span></label>
                            <input type="text" name="notes" class="form-control" placeholder="e.g. Damaged / write-off" required>
                        </div>
                    @elseif($action === 'adjust')
                        <div class="mb-3">
                            <label class="form-label fw-bold small text-dark">Target Physical Stock <span class="text-danger">*</span></label>
                            <input type="number" name="target_stock" min="0" value="{{ $product->stock }}" class="form-control fw-bold" required autofocus>
                        </div>
                        <div class="mb-4">
                            <label class="form-label fw-bold small text-dark">Reason <span class="text-danger">*</span></label>
                            <input type="text" name="notes" class="form-control" placeholder="e.g. Inventory audit reconciliation" required>
                        </div>
                    @endif

                    <div class="d-flex justify-content-end gap-2 pt-2 border-top">
                        <a href="{{ route('product-manager.stock.dashboard') }}" class="btn btn-light rounded-pill px-4 border">Cancel</a>
                        <button type="submit" class="btn btn-pm-primary rounded-pill px-4 fw-bold">
                            Save
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
