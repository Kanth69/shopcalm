@extends('admin.layouts.app')

@section('header', 'Inventory Management')

@section('actions')
    <a href="{{ route('admin.stock.history') }}" class="btn btn-outline-primary">
        <i class="bi bi-clock-history"></i> Global Stock History
    </a>
@endsection

@section('content')
<div class="row g-4 mb-4">
    <div class="col-md-2">
        <div class="card h-100 border-0 shadow-sm border-start border-primary border-4">
            <div class="card-body">
                <h6 class="text-muted small text-uppercase mb-1">Total SKUs</h6>
                <h3 class="mb-0 fw-bold">{{ number_format($stats['total_products']) }}</h3>
            </div>
        </div>
    </div>
    <div class="col-md-2">
        <div class="card h-100 border-0 shadow-sm border-start border-info border-4">
            <div class="card-body">
                <h6 class="text-muted small text-uppercase mb-1">In Stock Units</h6>
                <h3 class="mb-0 fw-bold">{{ number_format($stats['total_stock']) }}</h3>
            </div>
        </div>
    </div>
    <div class="col-md-2">
        <div class="card h-100 border-0 shadow-sm border-start border-warning border-4">
            <div class="card-body">
                <h6 class="text-muted small text-uppercase mb-1">Low Stock</h6>
                <h3 class="mb-0 fw-bold">{{ number_format($stats['low_stock']) }}</h3>
            </div>
        </div>
    </div>
    <div class="col-md-2">
        <div class="card h-100 border-0 shadow-sm border-start border-danger border-4">
            <div class="card-body">
                <h6 class="text-muted small text-uppercase mb-1">Out of Stock</h6>
                <h3 class="mb-0 fw-bold">{{ number_format($stats['out_of_stock']) }}</h3>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card h-100 border-0 shadow-sm border-start border-success border-4">
            <div class="card-body">
                <h6 class="text-muted small text-uppercase mb-1">Total Inventory Value</h6>
                <h3 class="mb-0 fw-bold">₹{{ number_format($stats['total_value'], 2) }}</h3>
            </div>
        </div>
    </div>
</div>

<div class="card border-0 shadow-sm mb-4">
    <div class="card-body">
        <form method="GET" action="{{ route('admin.stock.dashboard') }}">
            <div class="row g-3">
                <div class="col-md-4">
                    <input type="text" name="search" class="form-control" placeholder="Search by name or SKU..." value="{{ request('search') }}">
                </div>
                <div class="col-md-2">
                    <select name="stock_status" class="form-select">
                        <option value="">All Stock Levels</option>
                        <option value="low" {{ request('stock_status') == 'low' ? 'selected' : '' }}>Low Stock</option>
                        <option value="out" {{ request('stock_status') == 'out' ? 'selected' : '' }}>Out of Stock</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <select name="category_id" class="form-select">
                        <option value="">All Categories</option>
                        @foreach(\App\Models\Category::all() as $category)
                            <option value="{{ $category->id }}" {{ request('category_id') == $category->id ? 'selected' : '' }}>{{ $category->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary w-100">Filter Results</button>
                </div>
                <div class="col-md-2">
                    <a href="{{ route('admin.stock.dashboard') }}" class="btn btn-light w-100">Reset</a>
                </div>
            </div>
        </form>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-3">Product</th>
                        <th>SKU</th>
                        <th class="text-center">Current Stock</th>
                        <th class="text-center">Reserved</th>
                        <th class="text-center">Available</th>
                        <th class="text-center">Reorder Level</th>
                        <th class="text-center">Status</th>
                        <th>Last Updated</th>
                        <th class="text-end pe-3">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($products as $product)
                    <tr>
                        <td class="ps-3">
                            <div class="d-flex align-items-center">
                                <img src="{{ asset('storage/' . $product->main_image) }}" class="rounded me-2 border shadow-sm" style="width: 42px; height: 42px; object-fit: cover;">
                                <div>
                                    <a href="{{ route('admin.stock.show', $product) }}" class="fw-bold text-dark text-decoration-none hover-primary">{{ Str::limit($product->name, 35) }}</a>
                                    <div class="small text-muted">{{ $product->category->name }}</div>
                                </div>
                            </div>
                        </td>
                        <td><span class="text-muted small fw-medium">{{ $product->sku }}</span></td>
                        <td class="text-center fw-bold">{{ $product->stock }}</td>
                        <td class="text-center text-muted">0</td>
                        <td class="text-center text-primary fw-bold">{{ $product->stock }}</td>
                        <td class="text-center text-muted small">5</td>
                        <td class="text-center">
                            @if($product->stock <= 0)
                                <span class="badge bg-danger rounded-pill">Out of Stock</span>
                            @elseif($product->stock <= 5)
                                <span class="badge bg-warning text-dark rounded-pill">Low Stock</span>
                            @else
                                <span class="badge bg-success rounded-pill">Optimal</span>
                            @endif
                        </td>
                        <td class="small text-muted">{{ $product->updated_at->diffForHumans() }}</td>
                        <td class="text-end pe-3">
                            <div class="btn-group shadow-sm">
                                <a href="{{ route('admin.stock.add-form', $product) }}" class="btn btn-sm btn-outline-success" title="Add Stock">
                                    <i class="bi bi-plus-lg"></i>
                                </a>
                                <a href="{{ route('admin.stock.reduce-form', $product) }}" class="btn btn-sm btn-outline-danger" title="Reduce Stock">
                                    <i class="bi bi-dash-lg"></i>
                                </a>
                                <a href="{{ route('admin.stock.adjust-form', $product) }}" class="btn btn-sm btn-outline-warning" title="Adjust Stock">
                                    <i class="bi bi-sliders"></i>
                                </a>
                                <a href="{{ route('admin.stock.show', $product) }}" class="btn btn-sm btn-outline-secondary" title="View History">
                                    <i class="bi bi-clock-history"></i>
                                </a>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="text-center py-5 text-muted">No products found for inventory management.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($products->hasPages())
            <div class="card-footer bg-white py-3">
                {{ $products->links('pagination::bootstrap-5') }}
            </div>
        @endif
    </div>
</div>

<style>
    .hover-primary:hover { color: var(--bs-primary) !important; text-decoration: underline !important; }
</style>
@endsection
