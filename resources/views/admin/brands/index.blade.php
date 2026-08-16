@extends('admin.layouts.app')

@section('header', 'Brand Management')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item active" aria-current="page">Brands</li>
@endsection

@section('actions')
    <a href="{{ route('admin.brands.create') }}" class="btn btn-primary rounded-pill px-3">
        <i class="bi bi-plus-lg me-1"></i> Add New Brand
    </a>
@endsection

@section('content')
<!-- Filter & Search Card -->
<div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-4">
    <div class="card-body p-4">
        <form method="GET" action="{{ route('admin.brands.index') }}">
            <div class="row g-3 align-items-center">
                <div class="col-md-5">
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
                    <select name="category_id" class="form-select" onchange="this.form.submit()">
                        <option value="">All Categories</option>
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}" {{ request('category_id') == $category->id ? 'selected' : '' }}>
                                {{ $category->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <select name="status" class="form-select" onchange="this.form.submit()">
                        <option value="">All Statuses</option>
                        <option value="1" {{ request('status') === '1' ? 'selected' : '' }}>Active</option>
                        <option value="0" {{ request('status') === '0' ? 'selected' : '' }}>Inactive</option>
                    </select>
                </div>
                <div class="col-md-2 d-flex gap-2">
                    <button type="submit" class="btn btn-primary rounded-pill px-3 w-100 fw-bold">Filter</button>
                    @if(request()->hasAny(['search', 'category_id', 'status']))
                        <a href="{{ route('admin.brands.index') }}" class="btn btn-outline-secondary rounded-pill px-3" title="Clear filters">Clear</a>
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
                        <th>Category</th>
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
                            @if($brand->category)
                                <span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25 rounded-pill px-2.5 py-1 small">
                                    {{ $brand->category->name }}
                                </span>
                            @else
                                <span class="text-muted small italic">Unassigned</span>
                            @endif
                        </td>
                        <td>
                            <code class="text-secondary small bg-light px-2 py-1 rounded">{{ $brand->slug }}</code>
                        </td>
                        <td class="text-center">
                            <span class="badge bg-light text-dark border rounded-pill px-2.5 py-1">
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
                            <div class="btn-group btn-group-sm">
                                <a href="{{ route('admin.brands.edit', $brand) }}" class="btn btn-outline-primary" title="Edit Brand">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <button type="button" class="btn btn-outline-danger" onclick="confirmDelete({{ $brand->id }}, '{{ route('admin.brands.destroy', $brand) }}', this)" title="Delete Brand">
                                    <i class="bi bi-trash3"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="text-center py-5 text-muted">
                            <i class="bi bi-award text-muted opacity-50 display-6 d-block mb-2"></i>
                            <h6 class="fw-bold text-dark">No Brands Found</h6>
                            <p class="small text-muted mb-0">Try changing your search terms or category filter.</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($brands->hasPages())
            <div class="p-4 border-top bg-white d-flex justify-content-between align-items-center flex-wrap gap-2">
                <div class="small text-muted">
                    Showing <strong>{{ $brands->firstItem() }}</strong> to <strong>{{ $brands->lastItem() }}</strong> of <strong>{{ $brands->total() }}</strong> brands
                </div>
                <div>
                    {{ $brands->withQueryString()->links('pagination::bootstrap-5') }}
                </div>
            </div>
        @endif
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
function confirmDelete(id, deleteUrl, btnElement) {
    Swal.fire({
        title: 'Delete Brand?',
        text: "Are you sure you want to delete this brand? Assigned products will not be deleted.",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#ef4444',
        cancelButtonColor: '#64748b',
        confirmButtonText: 'Yes, Delete',
        cancelButtonText: 'Cancel'
    }).then((result) => {
        if (result.isConfirmed) {
            btnElement.disabled = true;
            fetch(deleteUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'X-HTTP-Method-Override': 'DELETE',
                    'Accept': 'application/json'
                }
            })
            .then(async res => {
                const data = await res.json();
                if (!res.ok || !data.success) {
                    throw new Error(data.message || 'Failed to delete brand.');
                }
                return data;
            })
            .then(data => {
                const row = document.getElementById('brand-row-' + id);
                if (row) {
                    row.style.transition = 'all 0.3s ease';
                    row.style.opacity = '0';
                    setTimeout(() => row.remove(), 300);
                }
                Swal.fire({
                    icon: 'success',
                    title: 'Deleted',
                    text: data.message || 'Brand deleted successfully.',
                    toast: true,
                    position: 'top-end',
                    showConfirmButton: false,
                    timer: 2500
                });
            })
            .catch(err => {
                btnElement.disabled = false;
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: err.message || 'Failed to delete brand.',
                    toast: true,
                    position: 'top-end',
                    showConfirmButton: false,
                    timer: 3000
                });
            });
        }
    });
}
</script>
@endpush
@endsection
