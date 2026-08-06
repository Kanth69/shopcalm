@extends('layouts.customer')

@section('title', $product->name . ' - Shopcalm')

@section('content')
<div class="container my-5">
    <div class="card shadow-sm">
        <div class="card-body">
            <div class="row">
                <aside class="col-lg-6">
                    <div class="border rounded-4 mb-3 d-flex justify-content-center bg-light">
                        <a href="{{ asset('storage/' . $product->main_image) }}" data-bs-toggle="modal" data-bs-target="#imageModal">
                            <img id="main-product-image" style="max-width: 100%; max-height: 500px; margin: auto; object-fit: contain;" class="rounded-4" src="{{ asset('storage/' . $product->main_image) }}" />
                        </a>
                    </div>
                    @if($product->galleryImages->isNotEmpty())
                    <div class="d-flex justify-content-center mb-3">
                        <a href="#" class="border mx-1 rounded-2 item-thumb" onclick="event.preventDefault(); document.getElementById('main-product-image').src='{{ asset('storage/' . $product->main_image) }}'">
                            <img width="60" height="60" class="rounded-2" src="{{ asset('storage/' . $product->main_image) }}" />
                        </a>
                        @foreach($product->galleryImages as $image)
                            <a href="#" class="border mx-1 rounded-2 item-thumb" onclick="event.preventDefault(); document.getElementById('main-product-image').src='{{ asset('storage/' . $image->image) }}'">
                                <img width="60" height="60" class="rounded-2" src="{{ asset('storage/' . $image->image) }}" />
                            </a>
                        @endforeach
                    </div>
                    @endif
                </aside>
                <main class="col-lg-6">
                    <div class="ps-lg-3">
                        <h1 class="title text-dark h3">
                            {{ $product->name }}
                        </h1>
                        <div class="d-flex flex-row my-3">
                            <div class="text-warning mb-1 me-2">
                                <i class="bi bi-star-fill"></i>
                                <i class="bi bi-star-fill"></i>
                                <i class="bi bi-star-fill"></i>
                                <i class="bi bi-star-fill"></i>
                                <i class="bi bi-star-half"></i>
                                <span class="ms-1">4.5</span>
                            </div>
                            <span class="text-muted"><i class="bi bi-check-circle-fill me-1 text-success"></i> In stock</span>
                        </div>

                        <div class="mb-3">
                            @if($product->offer_price)
                                <span class="price h2 fw-bold text-danger">₹{{ number_format($product->offer_price, 2) }}</span>
                                <del class="text-muted ms-2">₹{{ number_format($product->selling_price, 2) }}</del>
                            @else
                                <span class="price h2 fw-bold">₹{{ number_format($product->selling_price, 2) }}</span>
                            @endif
                        </div>

                        <p class="text-muted">{{ $product->short_description }}</p>

                        <div class="row">
                            <dt class="col-3">Category:</dt>
                            <dd class="col-9"><a href="{{ route('category.products', $product->category->slug) }}">{{ $product->category->name }}</a></dd>

                            <dt class="col-3">Brand:</dt>
                            <dd class="col-9"><a href="{{ route('brand.products', $product->brand->slug) }}">{{ $product->brand->name }}</a></dd>

                            <dt class="col-3">SKU:</dt>
                            <dd class="col-9">{{ $product->sku }}</dd>
                        </div>

                        <hr />

                        <form action="{{ route('cart.add') }}" method="POST">
                            @csrf
                            <input type="hidden" name="product_id" value="{{ $product->id }}">
                            <div class="row mb-4">
                                <div class="col-md-4 col-6 mb-3">
                                    <label class="mb-2 d-block">Quantity</label>
                                    <div class="input-group" style="width: 170px;">
                                        <input type="number" name="quantity" class="form-control text-center border border-secondary" value="1" min="1" max="{{ $product->stock }}">
                                    </div>
                                </div>
                            </div>
                            <button type="submit" class="btn btn-primary shadow-0 btn-lg"> <i class="me-1 bi bi-cart-plus"></i> Add to cart </button>
                        </form>
                    </div>
                </main>
            </div>
        </div>
    </div>

    <!-- content -->
    <div class="card mt-4">
        <div class="card-body">
            <h5 class="card-title">Full Description</h5>
            <p>
                {!! nl2br(e($product->description)) !!}
            </p>
        </div>
    </div>

    @if($relatedProducts->isNotEmpty())
    <section class="py-5">
        <h3 class="mb-4">Related Products</h3>
        <div class="row">
            @foreach($relatedProducts as $related)
                <div class="col-lg-3 col-md-6 col-sm-6">
                    @include('customer.components.product-card', ['product' => $related])
                </div>
            @endforeach
        </div>
    </section>
    @endif
</div>
@endsection
