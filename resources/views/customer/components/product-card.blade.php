<div class="card card-product-grid h-100 border-0 shadow-sm rounded-4 overflow-hidden d-flex flex-column transition-all hover-elevate">
    <div class="img-wrap position-relative">
        <a href="{{ route('product.show', $product->slug) }}">
            @if($product->main_image)
                <img loading="lazy" decoding="async" src="{{ asset('storage/' . $product->main_image) }}" class="card-img-top" alt="{{ $product->name }}">
            @else
                <div class="card-img-top bg-light d-flex align-items-center justify-content-center text-muted" style="height: 220px;">
                    <i class="bi bi-image fs-1 opacity-25"></i>
                </div>
            @endif
        </a>

        <!-- Wishlist Button -->
        <div class="wishlist-btn">
            <form action="{{ route('wishlist.add') }}" method="POST">
                @csrf
                <input type="hidden" name="product_id" value="{{ $product->id }}">
                <button type="submit" class="btn btn-light shadow-sm" title="Add to Wishlist">
                    <i class="bi {{ in_array($product->id, $wishlistedProductIds ?? []) ? 'bi-heart-fill text-danger' : 'bi-heart' }}"></i>
                </button>
            </form>
        </div>

        <!-- Dynamic Sale Campaign Badge -->
        @if(isset($product->offer_badge))
            <div class="position-absolute top-0 start-0 m-2 text-white rounded-pill px-3 py-1 fw-bolder z-2 shadow" style="font-size: 11px; background-color: #6d28d9 !important; border: 1px solid #a78bfa; letter-spacing: 0.5px;">
                <i class="bi bi-fire text-warning me-1"></i> <span class="text-white">{{ $product->offer_badge }}</span>
            </div>
        @endif

        @if($product->stock <= 0)
            <div class="position-absolute bottom-0 start-0 w-100 bg-dark bg-opacity-75 text-white text-center py-1 small fw-bold z-2">
                OUT OF STOCK
            </div>
        @endif
    </div>

    <div class="card-body p-3 d-flex flex-column flex-grow-1">
        <div class="d-flex justify-content-between align-items-center mb-1">
            <a href="{{ route('category.products', $product->category->slug) }}" class="text-muted small text-decoration-none hover-primary text-truncate pe-2" style="max-width: 70%;">
                {{ $product->category->name }}
            </a>
            <div class="text-warning small flex-shrink-0">
                <i class="bi bi-star-fill"></i>
                <span class="text-muted ms-1">{{ number_format($product->averageRating() ?? 0, 1) }}</span>
            </div>
        </div>

        <a href="{{ route('product.show', $product->slug) }}" class="title h6 mb-2 text-decoration-none flex-grow-1 d-block" style="display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; text-overflow: ellipsis;">
            {{ $product->name }}
        </a>

        <div class="price-wrap mt-auto pt-2 d-flex align-items-baseline gap-2">
            @if(isset($product->sale_price) && $product->sale_price < $product->price)
                <span class="price fw-bold text-primary fs-5">₹{{ number_format($product->sale_price, 2) }}</span>
                <span class="text-muted text-decoration-line-through small">₹{{ number_format($product->price, 2) }}</span>
            @else
                <span class="price fw-bold text-dark fs-5">₹{{ number_format($product->price, 2) }}</span>
            @endif
        </div>
    </div>

</div>
