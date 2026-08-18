@extends('product-manager.layouts.app')

@section('title', 'Pending Approvals')
@section('header', 'Pending Approvals')
@section('subheader', 'Products submitted for Admin review')

@section('content')
<div class="card bg-white border-0 shadow-sm rounded-4 overflow-hidden mb-4">
    <div class="card-header bg-white py-3 border-bottom d-flex align-items-center justify-content-between">
        <div>
            <h6 class="mb-0 fw-bold text-dark">Review Queue ({{ $products->total() }})</h6>
            <small class="text-muted" style="font-size: 0.75rem;">These products remain hidden from the storefront until approved.</small>
        </div>
        <a href="{{ route('product-manager.products.create') }}" class="btn btn-sm btn-pm-primary rounded-pill px-3">
            <i class="bi bi-plus-lg me-1"></i> Add Product
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
                        <th style="font-size: 0.72rem;">Stock</th>
                        <th style="font-size: 0.72rem;">Submitted</th>
                        <th class="pe-4 text-end" style="font-size: 0.72rem;">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($products as $prod)
                    <tr>
                        <td class="ps-4">
                            <div class="d-flex align-items-center gap-3">
                                @if($prod->main_image)
                                    <img src="{{ asset('storage/' . $prod->main_image) }}" class="rounded-3 border" style="width: 42px; height: 42px; object-fit: cover;">
                                @else
                                    <div class="rounded-3 bg-light d-flex align-items-center justify-content-center text-muted border" style="width: 42px; height: 42px;">
                                        <i class="bi bi-image"></i>
                                    </div>
                                @endif
                                <div>
                                    <div class="fw-bold text-dark">{{ $prod->name }}</div>
                                    <div class="text-muted small" style="font-size: 0.72rem;">SKU: {{ $prod->sku }}</div>
                                </div>
                            </div>
                        </td>
                        <td>
                            <div class="fw-medium text-dark small">{{ $prod->category->name ?? '—' }}</div>
                            <div class="text-muted" style="font-size: 0.72rem;">{{ $prod->brand->name ?? '—' }}</div>
                        </td>
                        <td>
                            <div class="fw-bold text-dark">₹{{ number_format($prod->price, 2) }}</div>
                        </td>
                        <td>
                            <span class="small fw-semibold text-dark">{{ $prod->stock }} units</span>
                        </td>
                        <td>
                            <span class="badge bg-warning bg-opacity-10 text-warning text-dark border border-warning border-opacity-25 rounded-pill px-2.5 py-1" style="font-size: 0.7rem;">
                                <i class="bi bi-hourglass-split me-1 text-warning"></i> In Review
                            </span>
                            <div class="text-muted" style="font-size: 0.7rem;">{{ $prod->created_at->diffForHumans() }}</div>
                        </td>
                        <td class="pe-4 text-end">
                            <a href="{{ route('product-manager.products.edit', $prod) }}" class="btn btn-sm btn-outline-primary rounded-pill px-3" title="Edit Specifications">
                                <i class="bi bi-pencil me-1"></i> Edit
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center py-5 text-muted">
                            <i class="bi bi-check2-circle text-success fs-2 mb-2 d-block opacity-50"></i>
                            <div class="fw-bold text-dark mb-1">Queue is Empty</div>
                            <small class="text-muted">No pending submissions awaiting review.</small>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($products->hasPages())
            <div class="px-4 py-3 border-top bg-white">
                {{ $products->links('pagination::bootstrap-5') }}
            </div>
        @endif
    </div>
</div>
@endsection
