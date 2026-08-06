@extends('layouts.customer')

@section('title', 'All Categories - Shopcalm')

@section('content')
<div class="container my-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold mb-1"><i class="bi bi-grid-fill text-primary me-2"></i>Product Categories</h2>
            <p class="text-muted mb-0">Browse products by category</p>
        </div>
        <a href="{{ route('shop') }}" class="btn btn-outline-primary rounded-pill px-4">Browse All Shop</a>
    </div>

    <div class="row g-4">
        @forelse($categories as $category)
        <div class="col-6 col-md-4 col-lg-3">
            <a href="{{ route('category.products', $category->slug) }}" class="text-decoration-none text-dark">
                <div class="card h-100 border-0 shadow-sm rounded-4 overflow-hidden text-center p-4 category-card transition-all">
                    @if($category->image)
                        <div class="mb-3">
                            <img src="{{ asset('storage/' . $category->image) }}" alt="{{ $category->name }}" class="img-fluid rounded-circle" style="width: 80px; height: 80px; object-fit: cover;">
                        </div>
                    @else
                        <div class="bg-primary-subtle text-primary rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 80px; height: 80px;">
                            <i class="bi bi-tag-fill fs-2"></i>
                        </div>
                    @endif
                    <h5 class="fw-bold mb-1">{{ $category->name }}</h5>
                    <span class="badge bg-light text-secondary border rounded-pill">{{ $category->products_count }} Products</span>
                </div>
            </a>
        </div>
        @empty
        <div class="col-12 text-center py-5">
            <i class="bi bi-folder-x fs-1 text-muted"></i>
            <h4 class="mt-3">No Categories Found</h4>
        </div>
        @endforelse
    </div>
</div>

<style>
.category-card { transition: all 0.25s ease-in-out; }
.category-card:hover { transform: translateY(-5px); box-shadow: 0 10px 25px rgba(0,0,0,0.08) !important; }
</style>
@endsection
