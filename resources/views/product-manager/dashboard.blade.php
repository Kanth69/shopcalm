@extends('product-manager.layouts.app')

@section('title', 'Product Manager Dashboard')
@section('header', 'Catalog & Stock Overview')
@section('subheader', 'Manage catalog products, stock levels, and review approval statuses')

@section('content')
<!-- KPI Cards -->
<div class="row g-3 mb-4">
    <div class="col-sm-6 col-xl-3">
        <div class="card p-3 bg-white border-0 shadow-sm rounded-4 h-100">
            <div class="d-flex align-items-center justify-content-between mb-2">
                <span class="text-muted small fw-semibold text-uppercase" style="font-size: 0.72rem; letter-spacing: 0.5px;">Total Products</span>
                <div class="rounded-circle p-2 d-flex align-items-center justify-content-center" style="width: 36px; height: 36px; background: #f3e8ff; color: #7c3aed;">
                    <i class="bi bi-boxes"></i>
                </div>
            </div>
            <div class="fs-3 fw-bold text-dark mb-1">{{ number_format($totalProducts) }}</div>
            <div class="text-muted small" style="font-size: 0.75rem;">All catalog items</div>
        </div>
    </div>

    <div class="col-sm-6 col-xl-3">
        <div class="card p-3 bg-white border-0 shadow-sm rounded-4 h-100">
            <div class="d-flex align-items-center justify-content-between mb-2">
                <span class="text-muted small fw-semibold text-uppercase" style="font-size: 0.72rem; letter-spacing: 0.5px;">Live & Active</span>
                <div class="rounded-circle p-2 d-flex align-items-center justify-content-center" style="width: 36px; height: 36px; background: #d1fae5; color: #059669;">
                    <i class="bi bi-check-circle"></i>
                </div>
            </div>
            <div class="fs-3 fw-bold text-success mb-1">{{ number_format($activeProducts) }}</div>
            <div class="text-muted small" style="font-size: 0.75rem;">Visible on storefront</div>
        </div>
    </div>

    <div class="col-sm-6 col-xl-3">
        <div class="card p-3 bg-white border-0 shadow-sm rounded-4 h-100 {{ $pendingProducts > 0 ? 'border-warning border-2' : '' }}">
            <div class="d-flex align-items-center justify-content-between mb-2">
                <span class="text-muted small fw-semibold text-uppercase" style="font-size: 0.72rem; letter-spacing: 0.5px;">Pending Approval</span>
                <div class="rounded-circle p-2 d-flex align-items-center justify-content-center" style="width: 36px; height: 36px; background: #fef3c7; color: #d97706;">
                    <i class="bi bi-hourglass-split"></i>
                </div>
            </div>
            <div class="fs-3 fw-bold text-warning mb-1">{{ number_format($pendingProducts) }}</div>
            <div class="text-muted small" style="font-size: 0.75rem;">Awaiting Admin review</div>
        </div>
    </div>

    <div class="col-sm-6 col-xl-3">
        <div class="card p-3 bg-white border-0 shadow-sm rounded-4 h-100 {{ $rejectedProducts > 0 ? 'border-danger border-2' : '' }}">
            <div class="d-flex align-items-center justify-content-between mb-2">
                <span class="text-muted small fw-semibold text-uppercase" style="font-size: 0.72rem; letter-spacing: 0.5px;">Rejected Items</span>
                <div class="rounded-circle p-2 d-flex align-items-center justify-content-center" style="width: 36px; height: 36px; background: #fee2e2; color: #dc2626;">
                    <i class="bi bi-x-circle"></i>
                </div>
            </div>
            <div class="fs-3 fw-bold text-danger mb-1">{{ number_format($rejectedProducts) }}</div>
            <div class="text-muted small" style="font-size: 0.75rem;">Needs correction & resubmit</div>
        </div>
    </div>
</div>

<!-- Stock Quick Summary Bar -->
<div class="card bg-white border-0 shadow-sm rounded-4 p-3 mb-4">
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
        <div class="d-flex align-items-center gap-4">
            <div>
                <span class="text-muted small d-block">Low Stock (≤10):</span>
                <span class="fw-bold {{ $lowStockCount > 0 ? 'text-warning' : 'text-dark' }}">{{ $lowStockCount }} SKUs</span>
            </div>
            <div class="border-start ps-4">
                <span class="text-muted small d-block">Out of Stock (0):</span>
                <span class="fw-bold {{ $outOfStockCount > 0 ? 'text-danger' : 'text-dark' }}">{{ $outOfStockCount }} SKUs</span>
            </div>
            <div class="border-start ps-4 d-none d-md-block">
                <span class="text-muted small d-block">Categories & Brands:</span>
                <span class="fw-bold text-dark">{{ $totalCategories }} Cats / {{ $totalBrands }} Brands</span>
            </div>
        </div>

        <div class="d-flex gap-2">
            <a href="{{ route('product-manager.products.create') }}" class="btn btn-pm-primary rounded-pill px-3 py-1.5 fw-semibold" style="font-size: 0.85rem;">
                <i class="bi bi-plus-lg me-1"></i> Add Product
            </a>
            <a href="{{ route('product-manager.stock.dashboard') }}" class="btn btn-light border rounded-pill px-3 py-1.5 fw-semibold" style="font-size: 0.85rem;">
                <i class="bi bi-stack me-1"></i> Manage Stock
            </a>
        </div>
    </div>
</div>

<div class="row g-4">
    <!-- Recent Product Submissions -->
    <div class="col-lg-8">
        <div class="card bg-white border-0 shadow-sm rounded-4 overflow-hidden h-100">
            <div class="card-header bg-white py-3 border-bottom d-flex align-items-center justify-content-between">
                <h6 class="mb-0 fw-bold text-dark">Recent Submissions</h6>
                <a href="{{ route('product-manager.products.index') }}" class="small text-decoration-none fw-bold" style="color: #7c3aed;">View All &rarr;</a>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-4" style="font-size: 0.72rem;">Product</th>
                                <th style="font-size: 0.72rem;">Price</th>
                                <th style="font-size: 0.72rem;">Stock</th>
                                <th style="font-size: 0.72rem;">Status</th>
                                <th class="pe-4 text-end" style="font-size: 0.72rem;">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($recentProducts as $prod)
                            <tr>
                                <td class="ps-4">
                                    <div class="d-flex align-items-center gap-3">
                                        @if($prod->main_image)
                                            <img src="{{ asset('storage/' . $prod->main_image) }}" class="rounded-3 border" style="width: 38px; height: 38px; object-fit: cover;">
                                        @else
                                            <div class="rounded-3 bg-light d-flex align-items-center justify-content-center text-muted border" style="width: 38px; height: 38px;">
                                                <i class="bi bi-image"></i>
                                            </div>
                                        @endif
                                        <div>
                                            <div class="fw-bold text-dark text-truncate" style="max-width: 220px; font-size: 0.85rem;">{{ $prod->name }}</div>
                                            <div class="text-muted small" style="font-size: 0.7rem;">SKU: {{ $prod->sku }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div class="fw-bold text-dark" style="font-size: 0.85rem;">₹{{ number_format($prod->price, 2) }}</div>
                                </td>
                                <td>
                                    <span class="small fw-semibold {{ $prod->stock <= 5 ? 'text-danger' : 'text-dark' }}">{{ $prod->stock }}</span>
                                </td>
                                <td>
                                    @if($prod->status === 'Active')
                                        <span class="badge bg-success bg-opacity-10 text-success rounded-pill px-2.5 py-1" style="font-size: 0.7rem;">Active</span>
                                    @elseif($prod->status === 'Pending_Approval')
                                        <span class="badge bg-warning bg-opacity-10 text-warning text-dark border border-warning border-opacity-25 rounded-pill px-2.5 py-1" style="font-size: 0.7rem;">Pending</span>
                                    @elseif($prod->status === 'Rejected')
                                        <span class="badge bg-danger bg-opacity-10 text-danger rounded-pill px-2.5 py-1" style="font-size: 0.7rem;">Rejected</span>
                                    @else
                                        <span class="badge bg-secondary bg-opacity-10 text-secondary rounded-pill px-2.5 py-1" style="font-size: 0.7rem;">Inactive</span>
                                    @endif
                                </td>
                                <td class="pe-4 text-end">
                                    <a href="{{ route('product-manager.products.edit', $prod) }}" class="btn btn-sm btn-light border rounded-pill px-2.5" title="Edit">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="text-center py-4 text-muted small">No products found.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Rejections Queue -->
    <div class="col-lg-4">
        <div class="card bg-white border-0 shadow-sm rounded-4 overflow-hidden h-100">
            <div class="card-header bg-white py-3 border-bottom d-flex align-items-center justify-content-between">
                <h6 class="mb-0 fw-bold text-danger">Rejections Queue</h6>
                <span class="badge bg-danger bg-opacity-10 text-danger rounded-pill">{{ $rejectedProducts }}</span>
            </div>
            <div class="card-body p-3">
                @if($recentRejected->isEmpty())
                    <div class="text-center py-5 text-muted">
                        <i class="bi bi-check-circle text-success fs-2 mb-2 d-block opacity-50"></i>
                        <div class="fw-bold text-dark small mb-1">No Rejections</div>
                        <small class="text-muted">All submissions are active or in review.</small>
                    </div>
                @else
                    <div class="d-flex flex-column gap-2.5">
                        @foreach($recentRejected as $rej)
                        <div class="p-3 bg-light rounded-3 border">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <div class="fw-bold text-dark small text-truncate" style="max-width: 170px;">{{ $rej->name }}</div>
                                <span class="badge bg-danger text-white" style="font-size: 0.65rem;">Needs Edit</span>
                            </div>
                            <div class="text-muted small mb-2" style="font-size: 0.75rem;">
                                {{ Str::limit($rej->rejection_reason ?? 'Admin requested changes.', 60) }}
                            </div>
                            <a href="{{ route('product-manager.products.edit', $rej) }}" class="btn btn-sm btn-primary rounded-pill w-100 py-1 fw-bold" style="font-size: 0.75rem;">
                                Edit & Resubmit
                            </a>
                        </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
