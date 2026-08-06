@extends('layouts.customer')

@section('title', $category->name . ' - Shopcalm')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-12">
            <div class="p-4 bg-white rounded shadow-sm mb-4">
                @include('customer.components.breadcrumb', ['currentPage' => $category->name])
                <h1 class="display-5">{{ $category->name }}</h1>
                <p class="lead text-muted">{{ $category->description }}</p>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-12">
            <div class="row">
                @forelse($products as $product)
                    <div class="col-lg-3 col-md-4 col-sm-6 d-flex mb-4">
                        @include('customer.components.product-card', ['product' => $product])
                    </div>
                @empty
                    <div class="col-12">
                        <div class="text-center my-5">
                            <i class="bi bi-exclamation-circle fs-1 text-muted"></i>
                            <h3 class="mt-3">No Products in this Category</h3>
                            <p class="text-muted">Check back later or explore our other categories.</p>
                            <a href="{{ route('shop') }}" class="btn btn-primary mt-3">Go to Shop</a>
                        </div>
                    </div>
                @endforelse
            </div>
            <hr>
            <footer class="d-flex mt-4">
                {{ $products->links('pagination::bootstrap-5') }}
            </footer>
        </div>
    </div>
</div>
@endsection
