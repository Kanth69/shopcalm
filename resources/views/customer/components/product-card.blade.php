@php
    $hasDiscount = (isset($product->sale_price) && (float) $product->sale_price < (float) $product->price) || !empty($product->offer_discount_percentage);
    $discountPct = $product->offer_discount_percentage ?? ($hasDiscount && (float) $product->price > 0 ? round((((float) $product->price - (float) $product->sale_price) / (float) $product->price) * 100) : 0);
@endphp

<div class="card card-product-grid w-100 h-100 border-0 rounded-4 overflow-hidden d-flex flex-column transition-all hover-elevate shadow-xs" style="background: #ffffff; border: 1px solid #e2e8f0 !important;">
    {{-- Image Container (Clean White Uniform Canvas) --}}
    <div class="img-wrap position-relative d-flex align-items-center justify-content-center p-2" style="background: #ffffff; overflow: hidden; height: 200px; min-height: 200px; max-height: 200px;">
        <a href="{{ route('product.show', $product->slug) }}" class="d-flex align-items-center justify-content-center w-100 h-100 text-decoration-none">
            @if($product->main_image)
                <img loading="lazy" decoding="async" src="{{ asset('storage/' . $product->main_image) }}" class="card-img-top product-img-contain" alt="{{ $product->name }}" style="width: 100%; height: 100%; max-height: 176px; max-width: 176px; object-fit: contain; object-position: center;">
            @else
                <div class="d-flex align-items-center justify-content-center text-muted w-100 h-100">
                    <i class="bi bi-image fs-2 opacity-25"></i>
                </div>
            @endif
        </a>

        <!-- Wishlist Button -->
        <div class="wishlist-btn position-absolute top-0 end-0 m-2 z-2">
            <form action="{{ route('wishlist.add') }}" method="POST">
                @csrf
                <input type="hidden" name="product_id" value="{{ $product->id }}">
                <button type="submit" class="btn btn-light shadow-xs rounded-circle p-0 d-flex align-items-center justify-content-center" 
                        title="Add to Wishlist" style="width: 32px; height: 32px; background: rgba(255,255,255,0.92); border: 1px solid #f1f5f9;">
                    <i class="bi {{ in_array($product->id, $wishlistedProductIds ?? []) ? 'bi-heart-fill text-danger' : 'bi-heart' }}" style="font-size: 0.85rem;"></i>
                </button>
            </form>
        </div>

        <!-- Prominent Top-Left Discount Badge -->
        @if($hasDiscount && $discountPct > 0)
            <div class="position-absolute top-0 start-0 m-2 z-2">
                <span class="badge bg-danger text-white px-2 py-1 fw-bold shadow-xs" 
                      style="font-size: 0.72rem; border-radius: 6px; letter-spacing: 0.3px;">
                    {{ $discountPct }}% OFF
                </span>
            </div>
        @elseif(isset($product->offer_badge))
            <div class="position-absolute top-0 start-0 m-2 z-2">
                <span class="badge bg-primary text-white px-2 py-1 fw-bold shadow-xs" 
                      style="font-size: 0.72rem; border-radius: 6px;">
                    {{ $product->offer_badge }}
                </span>
            </div>
        @endif

        @if($product->stock <= 0)
            <div class="position-absolute bottom-0 start-0 w-100 bg-dark bg-opacity-75 text-white text-center py-1 small fw-bold z-2" style="font-size: 0.72rem;">
                OUT OF STOCK
            </div>
        @endif
    </div>

    {{-- Card Body --}}
    <div class="card-body p-2.5 p-md-3 d-flex flex-column flex-grow-1">
        <!-- Category & Rating Row -->
        <div class="d-flex justify-content-between align-items-center mb-1">
            <a href="{{ route('category.products', $product->category->slug) }}" class="text-muted text-uppercase text-decoration-none text-truncate pe-1" style="font-size: 0.7rem; font-weight: 600; letter-spacing: 0.3px; max-width: 65%;">
                {{ $product->category->name }}
            </a>
            <div class="badge bg-warning bg-opacity-10 text-dark border border-warning border-opacity-25 rounded-pill px-1.5 py-0.5 d-flex align-items-center gap-1" style="font-size: 0.68rem; font-weight: 700;">
                <i class="bi bi-star-fill text-warning"></i>
                <span>{{ number_format($product->averageRating() ?? 0, 1) }}</span>
            </div>
        </div>

        <!-- Product Name Title -->
        <a href="{{ route('product.show', $product->slug) }}" class="title mb-2 text-decoration-none text-dark fw-semibold" style="display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; text-overflow: ellipsis; line-height: 1.35; font-size: 0.86rem; min-height: 2.3em;">
            {{ $product->name }}
        </a>

        <!-- Clear Pricing Row -->
        <div class="price-wrap mt-auto pt-1 d-flex align-items-center justify-content-between">
            <div>
                <div class="d-flex align-items-baseline gap-1.5 flex-wrap">
                    <span class="price fw-bolder text-dark" style="font-size: 0.98rem; letter-spacing: -0.2px;">
                        ₹{{ number_format($product->sale_price ?? $product->price, 2) }}
                    </span>
                    @if($hasDiscount)
                        <span class="text-muted text-decoration-line-through small" style="font-size: 0.72rem;">
                            ₹{{ number_format($product->price, 2) }}
                        </span>
                        <span class="badge bg-success bg-opacity-10 text-success fw-bold px-1.5 py-0.5" style="font-size: 0.65rem;">
                            -{{ $discountPct }}%
                        </span>
                    @endif
                </div>
            </div>

            <!-- Quick Add Button -->
            @if($product->stock > 0)
                <a href="{{ route('product.show', $product->slug) }}" class="btn btn-sm btn-light border text-primary rounded-circle p-0 d-flex align-items-center justify-content-center shadow-xs flex-shrink-0" title="View Product" style="width: 30px; height: 30px; background: #eff6ff; border-color: #dbeafe !important;">
                    <i class="bi bi-arrow-right-short fs-5"></i>
                </a>
            @endif
        </div>
    </div>
</div>
