@extends('product-manager.layouts.app')

@section('header', 'Category Management')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('product-manager.dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item active" aria-current="page">Categories</li>
@endsection

@section('actions')
    <a href="{{ route('product-manager.categories.create') }}" class="btn btn-primary rounded-pill px-3">
        <i class="bi bi-plus-lg me-1"></i> Add New Category
    </a>
@endsection

@section('content')
<div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-4">
    <div class="card-body p-4">
        <!-- Search and Filter Bar -->
        <form method="GET" action="{{ route('product-manager.categories.index') }}" class="mb-4">
            <div class="row g-3 align-items-center">
                <div class="col-md-5">
                    <div class="input-group">
                        <span class="input-group-text bg-white border-end-0 text-muted">
                            <i class="bi bi-search"></i>
                        </span>
                        <input type="text" name="search" class="form-control border-start-0 ps-0" placeholder="Search by name, slug, description..." value="{{ request('search') }}">
                    </div>
                </div>
                <div class="col-md-3">
                    <select name="status" class="form-select" onchange="this.form.submit()">
                        <option value="">All Statuses</option>
                        <option value="Active" {{ request('status') === 'Active' ? 'selected' : '' }}>Active</option>
                        <option value="Inactive" {{ request('status') === 'Inactive' ? 'selected' : '' }}>Inactive</option>
                    </select>
                </div>
                <div class="col-md-4 d-flex gap-2">
                    <button class="btn btn-primary rounded-pill px-3" type="submit">Filter</button>
                    @if(request('search') || request('status'))
                        <a href="{{ route('product-manager.categories.index') }}" class="btn btn-outline-secondary rounded-pill px-3">Clear Filters</a>
                    @endif
                </div>
            </div>
        </form>

        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-3" style="width: 70px;">ID</th>
                        <th style="width: 70px;">Image</th>
                        <th>Category Name</th>
                        <th>Slug</th>
                        <th class="text-center" style="width: 130px;">Products</th>
                        <th class="text-center" style="width: 110px;">Status</th>
                        <th class="text-end pe-3" style="width: 120px;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($categories as $category)
                    <tr id="category-row-{{ $category->id }}">
                        <td class="ps-3 fw-bold text-muted small">#{{ $category->id }}</td>
                        <td>
                            @if($category->image)
                                <img src="{{ asset('storage/' . $category->image) }}" alt="{{ $category->name }}" class="rounded-3 border" style="width: 44px; height: 44px; object-fit: cover;">
                            @else
                                <div class="bg-light rounded-3 d-flex align-items-center justify-content-center border text-muted" style="width: 44px; height: 44px;">
                                    <i class="bi bi-folder2 text-secondary"></i>
                                </div>
                            @endif
                        </td>
                        <td>
                            <div class="fw-bold text-dark">{{ $category->name }}</div>
                            @if($category->description)
                                <div class="small text-muted text-truncate" style="max-width: 280px;">{{ $category->description }}</div>
                            @endif
                        </td>
                        <td>
                            <code class="text-primary small bg-primary bg-opacity-10 px-2 py-1 rounded">{{ $category->slug }}</code>
                        </td>
                        <td class="text-center">
                            <span class="badge bg-light text-dark border px-2.5 py-1 rounded-pill">
                                {{ $category->products_count }} {{ Str::plural('item', $category->products_count) }}
                            </span>
                        </td>
                        <td class="text-center">
                            @if($category->status === 'Active')
                                <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 rounded-pill px-2.5 py-1">Active</span>
                            @else
                                <span class="badge bg-secondary bg-opacity-10 text-secondary border border-secondary border-opacity-25 rounded-pill px-2.5 py-1">Inactive</span>
                            @endif
                        </td>
                        <td class="text-end pe-3">
                            <a href="{{ route('product-manager.categories.edit', $category) }}" class="btn btn-sm btn-outline-primary rounded-pill px-3" title="Edit Category">
                                <i class="bi bi-pencil me-1"></i> Edit
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center py-5 text-muted">
                            <i class="bi bi-folder-x fs-1 d-block mb-2 text-secondary opacity-50"></i>
                            <h6>No Categories Found</h6>
                            <p class="small mb-0">Try changing your search filters or add a new category.</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($categories->hasPages())
            <div class="mt-4">
                {{ $categories->links('pagination::bootstrap-5') }}
            </div>
        @endif
    </div>
</div>
@endsection
