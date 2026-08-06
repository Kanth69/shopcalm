@extends('admin.layouts.app')

@section('header', 'Brands')

@section('actions')
    <a href="{{ route('admin.brands.create') }}" class="btn btn-primary">
        <i class="bi bi-plus-lg"></i> Add Brand
    </a>
@endsection

@section('content')
<div class="card">
    <div class="card-body">
        <form method="GET" action="{{ route('admin.brands.index') }}" class="mb-4">
            <div class="row g-2 align-items-center">
                <div class="col-auto">
                    <input type="text" name="search" class="form-control" placeholder="Search brands..." value="{{ request('search') }}">
                </div>
                <div class="col-auto">
                    <select name="category_id" class="form-select">
                        <option value="">All Categories</option>
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}" {{ request('category_id') == $category->id ? 'selected' : '' }}>
                                {{ $category->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-auto">
                    <button class="btn btn-outline-secondary" type="submit">Filter</button>
                    @if(request('search') || request('category_id'))
                        <a href="{{ route('admin.brands.index') }}" class="btn btn-outline-danger">Clear</a>
                    @endif
                </div>
            </div>
        </form>

        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead>
                    <tr>
                        <th width="50">ID</th>
                        <th width="80">Logo</th>
                        <th>Name</th>
                        <th>Category</th>
                        <th>Status</th>
                        <th width="150" class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($brands as $brand)
                    <tr>
                        <td>{{ $brand->id }}</td>
                        <td>
                            @if($brand->logo)
                                <img src="{{ asset('storage/' . $brand->logo) }}" alt="{{ $brand->name }}" class="img-thumbnail" style="width: 50px; height: 50px; object-fit: contain;">
                            @else
                                <div class="bg-light d-flex align-items-center justify-content-center border" style="width: 50px; height: 50px;">
                                    <i class="bi bi-image text-muted"></i>
                                </div>
                            @endif
                        </td>
                        <td>
                            <div class="fw-bold">{{ $brand->name }}</div>
                            <div class="small text-muted">{{ $brand->slug }}</div>
                        </td>
                        <td>{{ $brand->category->name ?? 'N/A' }}</td>
                        <td>
                            @if($brand->status)
                                <span class="badge bg-success">Active</span>
                            @else
                                <span class="badge bg-danger">Inactive</span>
                            @endif
                        </td>
                        <td class="text-end">
                            <a href="{{ route('admin.brands.edit', $brand) }}" class="btn btn-sm btn-outline-primary">
                                <i class="bi bi-pencil"></i>
                            </a>
                            <button type="button" class="btn btn-sm btn-outline-danger" onclick="confirmDelete({{ $brand->id }})">
                                <i class="bi bi-trash"></i>
                            </button>
                            <form id="delete-form-{{ $brand->id }}" action="{{ route('admin.brands.destroy', $brand) }}" method="POST" class="d-none">
                                @csrf
                                @method('DELETE')
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center py-4">No brands found.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-3">
            {{ $brands->withQueryString()->links('pagination::bootstrap-5') }}
        </div>
    </div>
</div>
@endsection
