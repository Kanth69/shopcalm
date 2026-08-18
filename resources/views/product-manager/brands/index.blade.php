@extends('product-manager.layouts.app')

@section('title', 'Brands')
@section('header', 'Brands')
@section('subheader', 'Manufacturer and brand partners')

@section('content')
<div class="card bg-white border-0 shadow-sm rounded-4 mb-4 p-3">
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
        <form method="GET" action="{{ route('product-manager.brands.index') }}" class="d-flex gap-2 flex-grow-1" style="max-width: 400px;">
            <div class="input-group">
                <span class="input-group-text bg-white border-end-0 text-muted"><i class="bi bi-search"></i></span>
                <input type="text" name="search" class="form-control border-start-0 ps-0" placeholder="Search brand..." value="{{ request('search') }}" style="font-size: 0.85rem;">
            </div>
            <button type="submit" class="btn btn-primary px-3" style="font-size: 0.85rem;">Search</button>
            @if(request()->filled('search'))
                <a href="{{ route('product-manager.brands.index') }}" class="btn btn-light border"><i class="bi bi-x-lg"></i></a>
            @endif
        </form>

        <a href="{{ route('product-manager.brands.create') }}" class="btn btn-pm-primary rounded-pill px-3" style="font-size: 0.85rem;">
            <i class="bi bi-plus-lg me-1"></i> Add Brand
        </a>
    </div>
</div>

<div class="card bg-white border-0 shadow-sm rounded-4 overflow-hidden mb-4">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-4" style="font-size: 0.72rem;">Brand</th>
                        <th style="font-size: 0.72rem;">Slug</th>
                        <th style="font-size: 0.72rem;">Products</th>
                        <th style="font-size: 0.72rem;">Status</th>
                        <th class="pe-4 text-end" style="font-size: 0.72rem;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($brands as $brand)
                    <tr>
                        <td class="ps-4">
                            <div class="d-flex align-items-center gap-3">
                                @if($brand->logo)
                                    <img src="{{ asset('storage/' . $brand->logo) }}" class="rounded-3 border" style="width: 38px; height: 38px; object-fit: contain; background: #ffffff;">
                                @else
                                    <div class="rounded-3 bg-light d-flex align-items-center justify-content-center text-muted border" style="width: 38px; height: 38px;">
                                        <i class="bi bi-tag"></i>
                                    </div>
                                @endif
                                <div class="fw-bold text-dark" style="font-size: 0.88rem;">{{ $brand->name }}</div>
                            </div>
                        </td>
                        <td><code class="text-muted small">{{ $brand->slug }}</code></td>
                        <td>
                            <span class="badge bg-light text-dark border rounded-pill px-2.5 py-1">
                                {{ $brand->products_count }} Products
                            </span>
                        </td>
                        <td>
                            @if($brand->status)
                                <span class="badge bg-success bg-opacity-10 text-success rounded-pill px-2.5 py-1" style="font-size: 0.72rem;">Active</span>
                            @else
                                <span class="badge bg-secondary bg-opacity-10 text-secondary rounded-pill px-2.5 py-1" style="font-size: 0.72rem;">Inactive</span>
                            @endif
                        </td>
                        <td class="pe-4 text-end">
                            <a href="{{ route('product-manager.brands.edit', $brand) }}" class="btn btn-sm btn-light border rounded-pill px-3" style="font-size: 0.78rem;">
                                Edit
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="text-center py-5 text-muted">No brands found.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($brands->hasPages())
            <div class="px-4 py-3 border-top bg-white">
                {{ $brands->links('pagination::bootstrap-5') }}
            </div>
        @endif
    </div>
</div>
@endsection
