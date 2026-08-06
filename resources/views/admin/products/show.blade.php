@extends('admin.layouts.app')

@section('header', 'Product Details')

@section('actions')
    <div class="btn-group">
        <a href="{{ route('admin.products.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left"></i> Back
        </a>
        <a href="{{ route('admin.products.edit', $product) }}" class="btn btn-primary">
            <i class="bi bi-pencil"></i> Edit Product
        </a>
    </div>
@endsection

@section('content')
<div class="row">
    <div class="col-md-4">
        <!-- Images Card -->
        <div class="card mb-4">
            <div class="card-body p-0 text-center">
                <img src="{{ asset('storage/' . $product->main_image) }}" alt="{{ $product->name }}" class="img-fluid rounded-top" style="width: 100%; height: 400px; object-fit: contain; background-color: #f8f9fa;">
            </div>
            @if($product->galleryImages->count() > 0)
            <div class="card-footer bg-white py-3">
                <div class="row g-2">
                    @foreach($product->galleryImages as $image)
                    <div class="col-3">
                        <img src="{{ asset('storage/' . $image->image) }}" class="img-thumbnail w-100" style="height: 60px; object-fit: cover; cursor: pointer;" onclick="document.querySelector('.img-fluid').src = this.src">
                    </div>
                    @endforeach
                </div>
            </div>
            @endif
        </div>

        <!-- Meta Info Card -->
        <div class="card">
            <div class="card-header bg-white py-3">
                <h5 class="card-title mb-0">System Info</h5>
            </div>
            <div class="card-body">
                <table class="table table-sm table-borderless mb-0">
                    <tr>
                        <th class="ps-0 text-muted" width="120">ID</th>
                        <td>#{{ $product->id }}</td>
                    </tr>
                    <tr>
                        <th class="ps-0 text-muted">Created At</th>
                        <td>{{ $product->created_at->format('d M Y, h:i A') }}</td>
                    </tr>
                    <tr>
                        <th class="ps-0 text-muted">Last Updated</th>
                        <td>{{ $product->updated_at->format('d M Y, h:i A') }}</td>
                    </tr>
                    <tr>
                        <th class="ps-0 text-muted">Status</th>
                        <td>
                            @if($product->status == 'Active')
                                <span class="badge bg-success">Active</span>
                            @else
                                <span class="badge bg-secondary">Inactive</span>
                            @endif
                        </td>
                    </tr>
                </table>
            </div>
        </div>
    </div>

    <div class="col-md-8">
        <!-- Main Info Card -->
        <div class="card mb-4">
            <div class="card-body">
                <div class="mb-4">
                    <span class="text-uppercase small fw-bold text-primary">{{ $product->category->name }}</span>
                    <h2 class="display-6 fw-bold text-dark mt-1">{{ $product->name }}</h2>
                    <div class="text-muted small">Brand: {{ $product->brand->name }} | SKU: {{ $product->sku }}</div>
                </div>

                <div class="row mb-4">
                    <div class="col-md-4">
                        <div class="p-3 bg-light rounded text-center">
                            <div class="small text-muted mb-1">Selling Price</div>
                            <div class="h3 fw-bold text-dark mb-0">₹{{ number_format($product->selling_price, 2) }}</div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="p-3 bg-success bg-opacity-10 rounded text-center">
                            <div class="small text-success mb-1 text-uppercase fw-bold">Offer Price</div>
                            <div class="h3 fw-bold text-success mb-0">{{ $product->offer_price ? '₹' . number_format($product->offer_price, 2) : '--' }}</div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="p-3 bg-info bg-opacity-10 rounded text-center">
                            <div class="small text-info mb-1 text-uppercase fw-bold">Current Stock</div>
                            <div class="h3 fw-bold text-info mb-0">{{ $product->stock }}</div>
                        </div>
                    </div>
                </div>

                <div class="mb-4">
                    <h5 class="fw-bold mb-2">Short Description</h5>
                    <p class="text-muted mb-0">{{ $product->short_description ?: 'No short description provided.' }}</p>
                </div>

                <div class="mb-0">
                    <h5 class="fw-bold mb-2">Full Description</h5>
                    <div class="text-muted">
                        {!! nl2br(e($product->description)) !!}
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-6">
                <div class="card mb-4">
                    <div class="card-header bg-white py-3">
                        <h5 class="card-title mb-0">Badges & Flags</h5>
                    </div>
                    <div class="card-body">
                        <div class="d-flex gap-2">
                            @if($product->featured)
                                <div class="badge-card p-3 bg-warning bg-opacity-10 border border-warning border-opacity-25 rounded w-100 text-center">
                                    <i class="bi bi-star-fill text-warning fs-3 d-block mb-2"></i>
                                    <span class="fw-bold">Featured</span>
                                </div>
                            @endif
                            @if($product->trending)
                                <div class="badge-card p-3 bg-primary bg-opacity-10 border border-primary border-opacity-25 rounded w-100 text-center">
                                    <i class="bi bi-graph-up-arrow text-primary fs-3 d-block mb-2"></i>
                                    <span class="fw-bold">Trending</span>
                                </div>
                            @endif
                            @if(!$product->featured && !$product->trending)
                                <span class="text-muted italic">No badges active.</span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                 <div class="card mb-4">
                    <div class="card-header bg-white py-3">
                        <h5 class="card-title mb-0">Financials</h5>
                    </div>
                    <div class="card-body">
                        <table class="table table-sm table-borderless mb-0">
                            <tr>
                                <th class="ps-0 text-muted" width="120">Cost Price</th>
                                <td>₹{{ $product->cost_price ? number_format($product->cost_price, 2) : '0.00' }}</td>
                            </tr>
                            <tr>
                                <th class="ps-0 text-muted">Profit margin</th>
                                <td>
                                    @if($product->cost_price)
                                        @php
                                            $profit = ($product->offer_price ?: $product->selling_price) - $product->cost_price;
                                            $percent = ($profit / $product->cost_price) * 100;
                                        @endphp
                                        <span class="text-success fw-bold">+₹{{ number_format($profit, 2) }} ({{ round($percent, 1) }}%)</span>
                                    @else
                                        --
                                    @endif
                                </td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
