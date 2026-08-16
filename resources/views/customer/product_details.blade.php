@extends('layouts.customer')

@section('title', $product->name . ' - Shopcalm')

@section('content')
<div class="container my-5">
    <div class="row g-5">
        <!-- Product Images -->
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-3">
                <div class="img-main-wrap p-4 bg-white text-center">
                    <img src="{{ asset('storage/' . $product->main_image) }}" id="main-product-img" class="img-fluid" alt="{{ $product->name }}" style="max-height: 500px; object-fit: contain;">
                </div>
            </div>

            @if($product->galleryImages->count() > 0)
            <div class="row g-2">
                <div class="col-3">
                    <div class="gallery-thumb active rounded-3 border overflow-hidden p-1 cursor-pointer" onclick="updateMainImage('{{ asset('storage/' . $product->main_image) }}', this)">
                        <img src="{{ asset('storage/' . $product->main_image) }}" class="img-fluid" style="aspect-ratio: 1; object-fit: cover;">
                    </div>
                </div>
                @foreach($product->galleryImages as $image)
                <div class="col-3">
                    <div class="gallery-thumb rounded-3 border overflow-hidden p-1 cursor-pointer" onclick="updateMainImage('{{ asset('storage/' . $image->image_path) }}', this)">
                        <img src="{{ asset('storage/' . $image->image_path) }}" class="img-fluid" style="aspect-ratio: 1; object-fit: cover;">
                    </div>
                </div>
                @endforeach
            </div>
            @endif
        </div>

        <!-- Product Info -->
        <div class="col-lg-6">
            <nav aria-label="breadcrumb" class="mb-3">
                <ol class="breadcrumb small">
                    <li class="breadcrumb-item"><a href="{{ route('home') }}" class="text-decoration-none">Home</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('category.products', $product->category->slug) }}" class="text-decoration-none">{{ $product->category->name }}</a></li>
                    <li class="breadcrumb-item active" aria-current="page">{{ $product->name }}</li>
                </ol>
            </nav>

            <h2 class="fw-bold mb-2">{{ $product->name }}</h2>
            <div class="d-flex align-items-center gap-3 mb-4">
                <div class="text-warning">
                    @for($i = 1; $i <= 5; $i++)
                        <i class="bi bi-star{{ $i <= round($product->averageRating()) ? '-fill' : '' }}"></i>
                    @endfor
                </div>
                <span class="text-muted small">({{ $product->approvedReviews->count() }} Reviews)</span>
                <span class="badge bg-light text-dark border">SKU: {{ $product->sku }}</span>
            </div>

            <div class="card border-0 bg-light rounded-4 mb-4">
                <div class="card-body p-4">
                    @if(isset($product->sale_price) && $product->sale_price < $product->price)
                        <div class="d-flex align-items-end gap-2 mb-1">
                            <h3 class="fw-bold text-dark mb-0">₹{{ number_format($product->sale_price, 2) }}</h3>
                            <span class="text-muted text-decoration-line-through fs-5">₹{{ number_format($product->price, 2) }}</span>
                            @php $perc = round((($product->price - $product->sale_price) / $product->price) * 100); @endphp
                            <span class="badge bg-primary rounded-pill px-2 py-1 ms-2" style="font-size: 0.8rem;">{{ $perc }}% OFF</span>
                        </div>
                        <p class="text-success small fw-bold mb-0">
                            <i class="bi bi-piggy-bank me-1"></i> You Save ₹{{ number_format($product->price - $product->sale_price, 2) }}
                        </p>
                    @else
                        <h3 class="fw-bold text-dark mb-0">₹{{ number_format($product->price, 2) }}</h3>
                    @endif
                </div>
            </div>

            <p class="text-muted mb-4">{{ $product->short_description }}</p>

            <div class="mb-4">
                <label class="form-label fw-bold small text-uppercase">Availability</label>
                <div>
                    @if($product->stock > 0)
                        <span class="text-success fw-bold"><i class="bi bi-check-circle-fill me-1"></i> In Stock ({{ $product->stock }} units)</span>
                    @else
                        <span class="text-danger fw-bold"><i class="bi bi-x-circle-fill me-1"></i> Out of Stock</span>
                    @endif
                </div>
            </div>

            @if($product->stock > 0)
            <div class="row g-3 mb-4">
                <div class="col-md-4">
                    <label class="form-label fw-bold small text-uppercase">Quantity</label>
                    <div class="input-group">
                        <button class="btn btn-outline-secondary rounded-start-pill px-3" type="button" onclick="changeQty(-1)"><i class="bi bi-dash"></i></button>
                        <input type="number" id="qty-input" class="form-control text-center" value="1" min="1" max="{{ $product->stock }}" readonly>
                        <button class="btn btn-outline-secondary rounded-end-pill px-3" type="button" onclick="changeQty(1)"><i class="bi bi-plus"></i></button>
                    </div>
                </div>
                <div class="col-md-8 d-flex align-items-end">
                    <div class="d-flex gap-2 w-100">
                        <form action="{{ route('cart.add') }}" method="POST" class="flex-grow-1 ajax-cart-form no-loader">
                            @csrf
                            <input type="hidden" name="product_id" value="{{ $product->id }}">
                            <input type="hidden" name="quantity" class="hidden-qty-input" value="1">
                            <button type="submit" class="btn btn-outline-primary btn-lg w-100 rounded-pill shadow-sm add-to-cart-btn no-loader">
                                <i class="bi bi-cart-plus me-2"></i> Add to Cart
                            </button>
                        </form>
                        <form action="{{ route('cart.add') }}" method="POST" class="flex-grow-1">
                            @csrf
                            <input type="hidden" name="product_id" value="{{ $product->id }}">
                            <input type="hidden" name="quantity" class="hidden-qty-input" value="1">
                            <input type="hidden" name="buy_now" value="1">
                            <button type="submit" class="btn btn-primary btn-lg w-100 rounded-pill shadow">
                                <i class="bi bi-lightning-fill me-2"></i> Buy Now
                            </button>
                        </form>
                    </div>
                </div>
            </div>
            @endif

            <div class="d-flex gap-2">
                <form action="{{ route('wishlist.add') }}" method="POST" class="flex-grow-1">
                    @csrf
                    <input type="hidden" name="product_id" value="{{ $product->id }}">
                    <button type="submit" class="btn btn-light border rounded-pill w-100">
                        <i class="bi {{ in_array($product->id, $wishlistedProductIds ?? []) ? 'bi-heart-fill text-danger' : 'bi-heart' }} me-2"></i> Add to Wishlist
                    </button>
                </form>
                <button class="btn btn-light border rounded-pill shadow-sm"><i class="bi bi-share"></i></button>
            </div>
        </div>
    </div>

    <!-- Product Details Tabs -->
    <div class="row mt-5 pt-5">
        <div class="col-12">
            <ul class="nav nav-pills mb-4 gap-2" id="productTab" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active rounded-pill px-4" data-bs-toggle="pill" data-bs-target="#desc" type="button">Description</button>
                </li>
                @if($product->specifications)
                <li class="nav-item" role="presentation">
                    <button class="nav-link rounded-pill px-4" data-bs-toggle="pill" data-bs-target="#spec" type="button">Specifications</button>
                </li>
                @endif
                <li class="nav-item" role="presentation">
                    <button class="nav-link rounded-pill px-4" data-bs-toggle="pill" data-bs-target="#rev" type="button">Reviews ({{ $product->approvedReviews->count() }})</button>
                </li>
            </ul>
            <div class="tab-content card border-0 shadow-sm rounded-4" id="productTabContent">
                <div class="tab-pane fade show active p-4 p-md-5" id="desc">
                    <div class="prose">
                        {!! $product->description !!}
                    </div>
                </div>
                @if($product->specifications)
                <div class="tab-pane fade p-4 p-md-5" id="spec">
                    <div class="table-responsive">
                        <table class="table table-bordered rounded-3 overflow-hidden">
                            <tbody>
                                @foreach(explode("\n", $product->specifications) as $spec)
                                    @php $parts = explode(":", $spec); @endphp
                                    @if(count($parts) == 2)
                                    <tr>
                                        <th class="bg-light w-25">{{ trim($parts[0]) }}</th>
                                        <td>{{ trim($parts[1]) }}</td>
                                    </tr>
                                    @endif
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
                @endif
                <div class="tab-pane fade p-4 p-md-5" id="rev">
                    <div class="row g-4">
                        <div class="col-md-4">
                            @include('customer.components.reviews.rating-summary', ['product' => $product])
                        </div>
                        <div class="col-md-8">
                            <div class="d-flex justify-content-between align-items-center mb-4">
                                <h5 class="fw-bold mb-0">Customer Reviews</h5>
                                @auth('customer')
                                    <button class="btn btn-outline-primary rounded-pill btn-sm" data-bs-toggle="collapse" data-bs-target="#reviewForm">
                                        Write a Review
                                    </button>
                                @else
                                    <a href="{{ route('login') }}" class="btn btn-outline-primary rounded-pill btn-sm">Login to Review</a>
                                @endauth
                            </div>

                            @auth('customer')
                            <div class="collapse mb-4" id="reviewForm">
                                @include('customer.components.reviews.write-review-form', ['product' => $product])
                            </div>
                            @endauth

                            <div class="review-list">
                                @forelse($product->approvedReviews as $review)
                                    @include('customer.components.reviews.review-card', ['review' => $review])
                                @empty
                                    <div class="text-center py-4 bg-light rounded-4">
                                        <p class="text-muted mb-0">No approved reviews yet. Be the first to review this product!</p>
                                    </div>
                                @endforelse
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function updateMainImage(src, thumb) {
    document.getElementById('main-product-img').src = src;
    document.querySelectorAll('.gallery-thumb').forEach(t => t.classList.remove('active'));
    thumb.classList.add('active');
}

function changeQty(amt) {
    const input = document.getElementById('qty-input');
    const hiddens = document.querySelectorAll('.hidden-qty-input');
    let val = parseInt(input.value) + amt;
    const max = parseInt(input.max);
    const min = parseInt(input.min);

    if (val >= min && val <= max) {
        input.value = val;
        hiddens.forEach(h => h.value = val);
    }
}
</script>

<style>
.gallery-thumb { transition: all 0.2s; opacity: 0.7; }
.gallery-thumb.active { opacity: 1; border-color: var(--bs-primary) !important; }
.gallery-thumb:hover { opacity: 1; }
.prose { line-height: 1.8; color: #475569; }
.cursor-pointer { cursor: pointer; }
</style>
@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
function confirmDeleteUserReview(id, deleteUrl, btnElement) {
    if (typeof Swal === 'undefined') {
        if (!confirm('Are you sure you want to delete your review?')) return;
        executeUserReviewDelete(id, deleteUrl, btnElement);
    } else {
        Swal.fire({
            title: 'Delete Your Review?',
            text: 'This action cannot be undone.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#ef4444',
            cancelButtonColor: '#64748b',
            confirmButtonText: 'Yes, Delete',
            cancelButtonText: 'Cancel'
        }).then((result) => {
            if (result.isConfirmed) {
                executeUserReviewDelete(id, deleteUrl, btnElement);
            }
        });
    }
}

function executeUserReviewDelete(id, deleteUrl, btnElement) {
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
        if (!res.ok || !data.success) throw new Error(data.message || 'Failed to delete review.');
        return data;
    })
    .then(data => {
        const card = document.getElementById('user-review-card-' + id);
        if (card) {
            card.style.transition = 'all 0.3s ease';
            card.style.opacity = '0';
            setTimeout(() => card.remove(), 300);
        }
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                icon: 'success',
                title: 'Deleted!',
                text: data.message || 'Review deleted successfully.',
                toast: true,
                position: 'top-end',
                showConfirmButton: false,
                timer: 2500
            });
        }
    })
    .catch(err => {
        btnElement.disabled = false;
        if (typeof Swal !== 'undefined') {
            Swal.fire({ icon: 'error', title: 'Error', text: err.message });
        } else {
            alert(err.message);
        }
    });
}

@if(session('review_submitted_swal'))
document.addEventListener('DOMContentLoaded', function() {
    Swal.fire({
        icon: 'success',
        title: 'Review Submitted!',
        text: '{{ session("review_submitted_swal") }}',
        confirmButtonColor: '#6366f1',
        confirmButtonText: 'OK'
    });
});
@endif
</script>
@endpush

@endsection
