@php
    $hasDiscount = (isset($product->sale_price) && (float) $product->sale_price < (float) $product->price) || !empty($product->offer_discount_percentage);
    $discountPct = $product->offer_discount_percentage ?? ($hasDiscount && (float) $product->price > 0 ? round((((float) $product->price - (float) $product->sale_price) / (float) $product->price) * 100) : 0);
    $rating = (float)($product->averageRating() ?? 0);
@endphp

<div class="card card-product-grid w-100 h-100 border-0 rounded-3 overflow-hidden d-flex flex-column transition-all hover-elevate shadow-xs" 
     style="background: #ffffff; border: 1px solid #e2e8f0 !important; border-radius: 12px !important;">
    
    {{-- Image Container: 1:1 Square Canvas (Matches standard 800x800 / 1000x1000 uploads) --}}
    <div class="img-wrap position-relative d-flex align-items-center justify-content-center" 
         style="background: #ffffff; width: 100%; aspect-ratio: 1 / 1; overflow: hidden; padding: 6px;">
        
        <a href="{{ route('product.show', $product->slug) }}" class="d-flex align-items-center justify-content-center w-100 h-100 text-decoration-none">
            @if($product->main_image)
                <img loading="lazy" decoding="async" src="{{ asset('storage/' . $product->main_image) }}" 
                     class="card-img-top product-img-contain" alt="{{ $product->name }}" 
                     style="width: 100%; height: 100%; object-fit: contain; object-position: center; transition: transform 0.3s ease;">
            @else
                <div class="d-flex align-items-center justify-content-center text-muted w-100 h-100">
                    <i class="bi bi-image fs-3 opacity-25"></i>
                </div>
            @endif
        </a>

        <!-- Top-Right: Wishlist Button -->
        @php
            $isWishlisted = in_array($product->id, $wishlistedProductIds ?? []);
        @endphp
        <div class="wishlist-btn position-absolute top-0 end-0 m-1.5 z-2">
            <button type="button" 
                    class="btn btn-light shadow-2xs rounded-circle p-0 d-flex align-items-center justify-content-center wishlist-toggle-btn" 
                    data-product-id="{{ $product->id }}"
                    title="{{ $isWishlisted ? 'Remove from Wishlist' : 'Add to Wishlist' }}" 
                    style="width: 28px; height: 28px; background: rgba(255,255,255,0.92); border: 1px solid #e2e8f0;">
                <i class="bi {{ $isWishlisted ? 'bi-heart-fill text-danger' : 'bi-heart text-secondary' }}" style="font-size: 0.78rem;"></i>
            </button>
        </div>

        <!-- Out of Stock Overlay -->
        @if($product->stock <= 0)
            <div class="position-absolute bottom-0 start-0 w-100 bg-dark bg-opacity-75 text-white text-center py-0.5 small fw-bold z-2" style="font-size: 0.68rem; letter-spacing: 0.04em;">
                OUT OF STOCK
            </div>
        @endif
    </div>

    {{-- Card Body: Compact & App-like --}}
    <div class="card-body p-2 p-sm-3 d-flex flex-column flex-grow-1">
        <!-- Product Name Title -->
        <a href="{{ route('product.show', $product->slug) }}" class="title mb-1 text-decoration-none text-dark fw-semibold" 
           style="display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; text-overflow: ellipsis; line-height: 1.25; font-size: 0.82rem; min-height: 2.05em;" 
           title="{{ $product->name }}">
            {{ $product->name }}
        </a>

        <!-- App-Style Ratings Row after Title -->
        <div class="d-flex align-items-center gap-1.5 mb-1.5">
            @if($rating > 0)
                <span class="badge bg-success text-white d-inline-flex align-items-center gap-1 px-1.5 py-0.5" style="font-size: 0.65rem; font-weight: 700; border-radius: 4px;">
                    <span>{{ number_format($rating, 1) }}</span>
                    <i class="bi bi-star-fill" style="font-size: 0.55rem;"></i>
                </span>
                <div class="d-inline-flex align-items-center text-warning" style="font-size: 0.62rem; gap: 1px;">
                    @for($i = 1; $i <= 5; $i++)
                        @if($rating >= $i)
                            <i class="bi bi-star-fill"></i>
                        @elseif($rating >= $i - 0.5)
                            <i class="bi bi-star-half"></i>
                        @else
                            <i class="bi bi-star text-muted opacity-30"></i>
                        @endif
                    @endfor
                </div>
            @else
                <div class="d-inline-flex align-items-center text-warning" style="font-size: 0.62rem; gap: 1px;">
                    @for($i = 1; $i <= 5; $i++)
                        <i class="bi bi-star-fill text-warning opacity-50"></i>
                    @endfor
                </div>
                <span class="text-muted" style="font-size: 0.68rem;">(5.0)</span>
            @endif
        </div>

        <!-- Clear Pricing & Action Row -->
        <div class="price-wrap mt-auto pt-1 d-flex align-items-center justify-content-between">
            <div class="min-w-0 pe-1">
                <div class="d-flex align-items-baseline gap-1.5 flex-wrap">
                    <span class="price fw-bolder text-dark font-monospace" style="font-size: 0.94rem; letter-spacing: -0.3px;">
                        ₹{{ number_format($product->sale_price ?? $product->price, 2) }}
                    </span>
                    @if($hasDiscount)
                        <span class="text-muted text-decoration-line-through small" style="font-size: 0.7rem;">
                            ₹{{ number_format($product->price, 2) }}
                        </span>
                        @if($discountPct > 0)
                            <span class="text-success fw-bold small text-nowrap" style="font-size: 0.72rem;">
                                {{ $discountPct }}% off
                            </span>
                        @endif
                    @endif
                </div>
            </div>

            <!-- Quick View/Add Arrow Button -->
            @if($product->stock > 0)
                <a href="{{ route('product.show', $product->slug) }}" 
                   class="btn btn-sm btn-light border text-primary rounded-circle p-0 d-flex align-items-center justify-content-center shadow-2xs flex-shrink-0" 
                   title="View Product" style="width: 26px; height: 26px; background: #eff6ff; border-color: #dbeafe !important;">
                    <i class="bi bi-chevron-right" style="font-size: 0.72rem; font-weight: 800;"></i>
                </a>
            @endif
        </div>
    </div>
</div>
