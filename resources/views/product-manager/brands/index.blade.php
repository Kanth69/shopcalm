@extends('product-manager.layouts.app')

@section('header', 'Brand Management')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('product-manager.dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item active" aria-current="page">Brands</li>
@endsection

@section('actions')
    <a href="{{ route('product-manager.brands.create') }}" class="btn btn-primary rounded-pill px-3">
        <i class="bi bi-plus-lg me-1"></i> Add New Brand
    </a>
@endsection

@section('content')
<!-- Filter & Search Card -->
<div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-4">
    <div class="card-body p-4">
        <form method="GET" action="{{ route('product-manager.brands.index') }}">
            <div class="row g-3 align-items-center">
                <div class="col-md-7">
                    <div class="input-group">
                        <span class="input-group-text bg-white border-end-0 text-muted">
                            <i class="bi bi-search"></i>
                        </span>
                        <input type="text" name="search" class="form-control border-start-0 ps-0" 
                            placeholder="Search by brand name, slug, description..." 
                            value="{{ request('search') }}">
                    </div>
                </div>
                <div class="col-md-3">
                    <select name="status" class="form-select" onchange="this.form.submit()">
                        <option value="">All Statuses</option>
                        <option value="1" {{ request('status') === '1' ? 'selected' : '' }}>Active</option>
                        <option value="0" {{ request('status') === '0' ? 'selected' : '' }}>Inactive</option>
                    </select>
                </div>
                <div class="col-md-2 d-flex gap-2">
                    <button type="submit" class="btn btn-primary rounded-pill px-3 w-100 fw-bold">Filter</button>
                    @if(request()->hasAny(['search', 'status']))
                        <a href="{{ route('product-manager.brands.index') }}" class="btn btn-outline-secondary rounded-pill px-3" title="Clear filters">Clear</a>
                    @endif
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Brands Table Card -->
<div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-4">
    <div class="card-header bg-white py-3 border-bottom d-flex align-items-center justify-content-between">
        <div class="d-flex align-items-center gap-2">
            <i class="bi bi-award-fill text-primary fs-5"></i>
            <h6 class="mb-0 fw-bold text-dark">Brands Directory</h6>
        </div>
        <span class="badge bg-light text-dark border px-2.5 py-1.5 rounded-pill font-monospace small">
            Total: {{ $brands->total() }} Brands
        </span>
    </div>

    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-4" style="width: 70px;">ID</th>
                        <th style="width: 70px;">Logo</th>
                        <th>Brand Details</th>
                        <th>Slug</th>
                        <th class="text-center" style="width: 130px;">Products</th>
                        <th class="text-center" style="width: 110px;">Status</th>
                        <th class="pe-4 text-end" style="width: 110px;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($brands as $brand)
                    <tr id="brand-row-{{ $brand->id }}">
                        <td class="ps-4 fw-bold text-muted small">#{{ $brand->id }}</td>
                        <td>
                            @if($brand->logo)
                                <img src="{{ asset('storage/' . $brand->logo) }}" alt="{{ $brand->name }}" class="rounded-3 border" style="width: 44px; height: 44px; object-fit: contain; background: #fafafa;">
                            @else
                                <div class="bg-light rounded-3 d-flex align-items-center justify-content-center border text-muted" style="width: 44px; height: 44px;">
                                    <i class="bi bi-award text-secondary"></i>
                                </div>
                            @endif
                        </td>
                        <td>
                            <div class="fw-bold text-dark">{{ $brand->name }}</div>
                            @if($brand->description)
                                <div class="small text-muted text-truncate" style="max-width: 250px;">{{ $brand->description }}</div>
                            @endif
                        </td>
                        <td>
                            <code class="text-primary small bg-primary bg-opacity-10 px-2 py-1 rounded">{{ $brand->slug }}</code>
                        </td>
                        <td class="text-center">
                            <span class="badge bg-light text-dark border px-2.5 py-1 rounded-pill">
                                {{ $brand->products_count }} {{ Str::plural('item', $brand->products_count) }}
                            </span>
                        </td>
                        <td class="text-center">
                            @if($brand->status)
                                <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 rounded-pill px-2.5 py-1">Active</span>
                            @else
                                <span class="badge bg-secondary bg-opacity-10 text-secondary border border-secondary border-opacity-25 rounded-pill px-2.5 py-1">Inactive</span>
                            @endif
                        </td>
                        <td class="pe-4 text-end">
                            <a href="{{ route('product-manager.brands.edit', $brand) }}" class="btn btn-sm btn-outline-primary rounded-pill px-3" title="Edit Brand">
                                <i class="bi bi-pencil me-1"></i> Edit
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center py-5 text-muted">
                            <i class="bi bi-tag-fill fs-1 d-block mb-2 text-secondary opacity-50"></i>
                            <h6>No Brands Found</h6>
                            <p class="small mb-0">Try changing your search filters or add a new brand partner.</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($brands->hasPages())
            <div class="p-3 border-top">
                {{ $brands->links('pagination::bootstrap-5') }}
            </div>
        @endif
    </div>
</div>
@endsection
