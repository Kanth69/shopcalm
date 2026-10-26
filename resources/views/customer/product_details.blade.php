@extends('layouts.customer')

@section('title', ($product->meta_title ?: $product->name) . ' - ' . \App\Models\Setting::get('store_name', 'ShopCalm'))
@section('meta_description', $product->meta_description ?: ($product->short_description ?: 'Buy ' . $product->name . ' online at best price with fast delivery.'))

@section('og_tags')
    <meta property="og:title" content="{{ $product->name }}">
    <meta property="og:description" content="{{ $product->short_description ?: 'Buy ' . $product->name . ' online at best price.' }}">
    <meta property="og:image" content="{{ $product->main_image ? asset('storage/' . $product->main_image) : asset('images/logo.png') }}">
    <meta property="og:price:amount" content="{{ $product->sale_price ?: $product->price }}">
    <meta property="og:price:currency" content="INR">
    
    <!-- Schema.org JSON-LD for Google Search Rich Snippets -->
    <script type="application/ld+json">
    {
        "@@context": "https://schema.org/",
        "@@type": "Product",
        "name": "{{ $product->name }}",
        "image": "{{ $product->main_image ? asset('storage/' . $product->main_image) : asset('images/logo.png') }}",
        "description": "{{ addslashes(strip_tags($product->short_description ?: $product->name)) }}",
        "sku": "{{ $product->sku ?: 'SKU-' . $product->id }}",
        "brand": {
            "@@type": "Brand",
            "name": "{{ $product->brand->name ?? \App\Models\Setting::get('store_name', 'ShopCalm') }}"
        },
        "offers": {
            "@@type": "Offer",
            "url": "{{ url()->current() }}",
            "priceCurrency": "INR",
            "price": "{{ $product->sale_price ?: $product->price }}",
            "availability": "{{ $product->stock > 0 ? 'https://schema.org/InStock' : 'https://schema.org/OutOfStock' }}",
            "itemCondition": "https://schema.org/NewCondition"
        }
    }
    </script>
@endsection

@section('content')
<div class="container my-3 my-md-4 px-3 px-md-4 pb-5 mb-4 pb-md-0 mb-md-0">
    
    {{-- Main Product Details Row --}}
    <div class="row g-4 g-lg-5">
        
        {{-- Left Column: Sticky Image Gallery --}}
        <div class="col-lg-5 col-xl-5">
            <div class="product-gallery-sticky" style="position: sticky; top: 90px;">
                
                {{-- Main Product Canvas --}}
                <div class="card border-0 rounded-4 overflow-hidden shadow-xs position-relative mb-2.5" 
                     style="background: #ffffff; border: 1px solid #e2e8f0 !important;">
                    
                    {{-- Calculation of Discount --}}
                    @php
                        $hasDiscount = (isset($product->sale_price) && (float) $product->sale_price < (float) $product->price) || !empty($product->offer_discount_percentage);
                        $discountPct = $product->offer_discount_percentage ?? ($hasDiscount && (float) $product->price > 0 ? round((((float) $product->price - (float) $product->sale_price) / (float) $product->price) * 100) : 0);
                    @endphp

                    {{-- Wishlist & Share Action Buttons (Top Right) --}}
                    @php
                        $isWishlisted = in_array($product->id, $wishlistedProductIds ?? []);
                    @endphp
                    <div class="position-absolute top-0 end-0 m-2.5 m-sm-3 z-2 d-flex align-items-center gap-2">
                        {{-- Share Button --}}
                        <button type="button" 
                                onclick="shareProduct()"
                                class="btn btn-light shadow-xs rounded-circle p-0 d-flex align-items-center justify-content-center" 
                                title="Share Product" 
                                aria-label="Share product"
                                style="width: 38px; height: 38px; background: rgba(255,255,255,0.95); border: 1px solid #e2e8f0; transition: transform 0.15s ease;">
                            <i class="bi bi-share text-dark" style="font-size: 0.95rem;"></i>
                        </button>

                        {{-- Wishlist Button --}}
                        <button type="button" 
                                class="btn btn-light shadow-xs rounded-circle p-0 d-flex align-items-center justify-content-center wishlist-toggle-btn pdp-wishlist-btn" 
                                data-product-id="{{ $product->id }}"
                                title="{{ $isWishlisted ? 'Remove from Wishlist' : 'Add to Wishlist' }}" 
                                aria-label="Wishlist"
                                style="width: 38px; height: 38px; background: rgba(255,255,255,0.95); border: 1px solid #e2e8f0; transition: transform 0.15s ease;">
                            <i class="bi {{ $isWishlisted ? 'bi-heart-fill text-danger' : 'bi-heart' }}" style="font-size: 1rem;"></i>
                        </button>
                    </div>

                    @php
                        $allProductImages = [];
                        if (!empty($product->main_image)) {
                            $allProductImages[] = str_starts_with($product->main_image, 'http') ? $product->main_image : asset('storage/' . $product->main_image);
                        }
                        if ($product->galleryImages && $product->galleryImages->count() > 0) {
                            foreach ($product->galleryImages as $gImg) {
                                $rawPath = $gImg->image ?? $gImg->image_path ?? null;
                                if (!empty($rawPath)) {
                                    $fullUrl = str_starts_with($rawPath, 'http') ? $rawPath : asset('storage/' . $rawPath);
                                    if (!in_array($fullUrl, $allProductImages, true)) {
                                        $allProductImages[] = $fullUrl;
                                    }
                                }
                            }
                        }
                    @endphp

                    {{-- Main Image Area (Fixed Uniform 1:1 Square Frame) --}}
                    <div class="p-3 p-md-4 d-flex align-items-center justify-content-center position-relative pdp-main-image-stage">
                        @if(count($allProductImages) > 0)
                            {{-- Expand / Fullscreen Hint Button (Top Left) --}}
                            <button type="button" class="btn btn-light bg-white bg-opacity-90 shadow-2xs rounded-circle position-absolute top-0 start-0 m-2.5 p-0 d-flex align-items-center justify-content-center border"
                                    style="width: 34px; height: 34px; z-index: 5;" 
                                    onclick="openProductLightbox()" 
                                    title="View full screen" aria-label="Expand image">
                                <i class="bi bi-arrows-fullscreen text-secondary" style="font-size: 0.82rem;"></i>
                            </button>

                            <div class="pdp-uniform-image-box d-flex align-items-center justify-content-center cursor-pointer"
                                 id="pdp-main-image-box"
                                 onclick="openProductLightbox()" 
                                 title="Click to view full screen" 
                                 style="cursor: zoom-in;">
                                <img src="{{ $allProductImages[0] }}" id="main-product-img" 
                                     class="main-pdp-image" alt="{{ $product->name }}">
                            </div>

                            @if(count($allProductImages) > 1)
                                {{-- Previous / Next Image Controls (Laptop / Desktop only, Hidden on Mobile) --}}
                                <button type="button" onclick="stepProductGallery(-1)" aria-label="Previous image"
                                        class="btn btn-light shadow-sm rounded-circle position-absolute start-0 ms-2.5 d-none d-md-flex align-items-center justify-content-center z-2 pdp-arrow-nav"
                                        style="width: 38px; height: 38px; top: 50%; transform: translateY(-50%); background: rgba(255,255,255,0.95); border: 1px solid #e2e8f0;">
                                    <i class="bi bi-chevron-left text-dark" style="font-size: 1rem;"></i>
                                </button>
                                <button type="button" onclick="stepProductGallery(1)" aria-label="Next image"
                                        class="btn btn-light shadow-sm rounded-circle position-absolute end-0 me-2.5 d-none d-md-flex align-items-center justify-content-center z-2 pdp-arrow-nav"
                                        style="width: 38px; height: 38px; top: 50%; transform: translateY(-50%); background: rgba(255,255,255,0.95); border: 1px solid #e2e8f0;">
                                    <i class="bi bi-chevron-right text-dark" style="font-size: 1rem;"></i>
                                </button>
                            @endif
                        @else
                            <div class="text-muted d-flex flex-column align-items-center justify-content-center py-5">
                                <i class="bi bi-image fs-1 opacity-25"></i>
                                <span class="small mt-2">No image available</span>
                            </div>
                        @endif
                    </div>

                    {{-- Watch Video Button (Floating Bottom-Left on Main Image) --}}
                    @if(!empty($product->getYoutubeEmbedUrl()))
                        <div class="position-absolute bottom-0 start-0 m-2.5 m-sm-3 pdp-video-btn-wrap">
                            <button type="button" 
                                    class="btn pdp-video-floating-btn shadow-sm d-inline-flex align-items-center gap-2" 
                                    onclick="openProductVideoModal(event)"
                                    title="Watch Product Video" 
                                    aria-label="Watch product video">
                                <span class="pdp-video-play-icon d-inline-flex align-items-center justify-content-center rounded-circle">
                                    <i class="bi bi-play-fill text-white"></i>
                                </span>
                                <span class="pdp-video-btn-text">Watch Video</span>
                            </button>
                        </div>
                    @endif
                </div>

                {{-- Slider Dots (Both Laptop & Mobile below main image) --}}
                @if(count($allProductImages) > 1)
                    <div class="d-flex justify-content-center align-items-center gap-1.5 py-2.5" id="pdp-slider-dots">
                        @foreach($allProductImages as $idx => $imgUrl)
                            <button type="button" 
                                    onclick="selectProductGalleryIndex({{ $idx }})"
                                    class="pdp-slider-dot {{ $idx === 0 ? 'active' : '' }}" 
                                    data-dot-idx="{{ $idx }}"
                                    title="View image {{ $idx + 1 }}"
                                    aria-label="Slide {{ $idx + 1 }}"></button>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>

        {{-- Right Column: Product Intelligence & Purchase Hub --}}
        <div class="col-lg-7 col-xl-7">
            
            {{-- Brand Link & Category --}}
            <div class="d-flex align-items-center gap-2 flex-wrap mb-1.5">
                @if($product->brand)
                    <a href="{{ route('brand.products', $product->brand->slug) }}" class="text-primary fw-bold text-decoration-none small text-uppercase" style="font-size: 0.8rem; letter-spacing: 0.05em;">
                        Visit {{ $product->brand->name }} Store
                    </a>
                @elseif($product->category)
                    <a href="{{ route('category.products', $product->category->slug) }}" class="text-muted fw-bold text-decoration-none small text-uppercase" style="font-size: 0.78rem; letter-spacing: 0.05em;">
                        {{ $product->category->name }}
                    </a>
                @endif
            </div>

            {{-- Title --}}
            <h1 class="fw-bolder text-dark mb-2" style="font-size: clamp(1.25rem, 2.2vw, 1.7rem); line-height: 1.35; letter-spacing: -0.02em;">
                {{ $product->name }}
            </h1>

            {{-- Ratings & Reviews Pill (Flipkart / Amazon Style) --}}
            <div class="d-flex align-items-center gap-2 flex-wrap mb-3">
                @php
                    $avgRating = $product->averageRating() ?? 0;
                    $reviewCount = $totalReviewsCount ?? $product->approvedReviews->count();
                @endphp
                <div class="badge bg-success text-white px-2 py-1 rounded-pill d-inline-flex align-items-center gap-1 fw-bold" style="font-size: 0.82rem;">
                    <span>{{ number_format($avgRating, 1) }}</span>
                    <i class="bi bi-star-fill" style="font-size: 0.75rem;"></i>
                </div>
                <a href="#rev-section" class="text-muted small text-decoration-none hover-primary" style="font-size: 0.84rem;">
                    {{ number_format($reviewCount) }} Verified Ratings & Reviews
                </a>
                <span class="text-muted small">•</span>
                <span class="badge bg-light text-secondary border rounded-pill px-2.5 py-1" style="font-size: 0.74rem;">
                    SKU: {{ $product->sku }}
                </span>
            </div>

            <hr class="my-3 text-secondary opacity-25">

            {{-- 3. Price Strip (Amazon / Flipkart Clean Typographic Style) --}}
            <div class="mb-3">
                <div class="d-flex align-items-baseline gap-2 flex-wrap mb-1">
                    @if($hasDiscount)
                        <span class="text-danger fw-bold fs-4">-{{ $discountPct }}%</span>
                        <span class="fw-bolder text-dark" style="font-size: clamp(1.6rem, 3vw, 2.1rem); letter-spacing: -0.03em;">
                            ₹{{ number_format($product->sale_price, 2) }}
                        </span>
                        <span class="text-muted text-decoration-line-through fs-6" style="font-size: 0.95rem;">
                            M.R.P.: ₹{{ number_format($product->price, 2) }}
                        </span>
                    @else
                        <span class="fw-bolder text-dark" style="font-size: clamp(1.6rem, 3vw, 2.1rem); letter-spacing: -0.03em;">
                            ₹{{ number_format($product->price, 2) }}
                        </span>
                    @endif
                </div>

                <div class="text-muted small d-flex align-items-center gap-2 flex-wrap" style="font-size: 0.8rem;">
                    <span>Inclusive of all taxes</span>
                    @if($hasDiscount)
                        <span>•</span>
                        <span class="text-success fw-bold">
                            <i class="bi bi-piggy-bank-fill me-1"></i> You Save ₹{{ number_format($product->price - $product->sale_price, 2) }}
                        </span>
                    @endif
                </div>
            </div>


            {{-- 5. Short Description --}}
            @if($product->short_description)
                <p class="text-secondary small mb-3" style="font-size: 0.88rem; line-height: 1.6;">
                    {{ $product->short_description }}
                </p>
            @endif

            {{-- 6. Stock Status --}}
            <div class="mb-3">
                @if($product->stock > 5)
                    <div class="d-flex align-items-center gap-2 text-success fw-bold" style="font-size: 0.95rem;">
                        <i class="bi bi-check-circle-fill fs-5"></i>
                        <span>In Stock</span>
                        <span class="badge bg-success bg-opacity-10 text-success rounded-pill px-2.5 py-0.5 small fw-semibold" style="font-size: 0.72rem;">Ready to Ship</span>
                    </div>
                @elseif($product->stock > 0)
                    <div class="d-flex align-items-center gap-2 text-warning fw-bold" style="font-size: 0.95rem;">
                        <i class="bi bi-exclamation-triangle-fill fs-5 text-warning"></i>
                        <span class="text-dark">Only <strong class="text-danger">{{ $product->stock }} left</strong> in stock - order soon!</span>
                    </div>
                @else
                    <div class="d-flex align-items-center gap-2 text-danger fw-bold" style="font-size: 0.95rem;">
                        <i class="bi bi-x-circle-fill fs-5"></i>
                        <span>Currently Out of Stock</span>
                    </div>
                @endif
            </div>

            {{-- 6.5. Product Options (Sizes, Colors, Waist Sizes, or Double Variant Color + Size Matrix) --}}
            @if($product->has_options && !empty($product->option_stocks))
                @php
                    $optionStocks = is_array($product->option_stocks) ? $product->option_stocks : json_decode($product->option_stocks, true);
                    $optType = strtolower($product->option_type ?? 'size');
                    $hasDoubleVariants = in_array($optType, ['size_color', 'waist_color']);
                    if (!$hasDoubleVariants && is_array($optionStocks)) {
                        foreach (array_keys($optionStocks) as $k) {
                            if (str_contains((string)$k, ' - ')) {
                                $hasDoubleVariants = true;
                                break;
                            }
                        }
                    }

                    $uniqueColors = [];
                    $colorTotalStock = [];
                    $uniqueSizes = [];
                    $doubleMatrix = [];
                    if ($hasDoubleVariants && is_array($optionStocks)) {
                        foreach ($optionStocks as $optKey => $optQty) {
                            $parts = explode(' - ', (string)$optKey, 2);
                            $c = trim($parts[0] ?? '');
                            $s = trim($parts[1] ?? '');
                            if ($c !== '' && $s !== '') {
                                if (!in_array($c, $uniqueColors, true)) $uniqueColors[] = $c;
                                if (!in_array($s, $uniqueSizes, true)) $uniqueSizes[] = $s;
                                $colorTotalStock[$c] = ($colorTotalStock[$c] ?? 0) + (int)$optQty;
                                $doubleMatrix[$c][$s] = (int)$optQty;
                            }
                        }
                    }

                    $optionLabel = match($optType) {
                        'waist' => 'Select Waist Size',
                        'color' => 'Select Color',
                        default => 'Select Size',
                    };
                    $secondDimLabel = ($optType === 'waist_color') ? 'Select Waist Size' : 'Select Size';

                    $colorHexMap = [
                        'red' => '#ef4444', 'dark red' => '#991b1b', 'light red' => '#f87171', 'crimson' => '#dc143c', 'maroon' => '#800000',
                        'blue' => '#2563eb', 'dark blue' => '#1e3a8a', 'light blue' => '#60a5fa', 'navy' => '#0f172a', 'navy blue' => '#0f172a',
                        'sky blue' => '#38bdf8', 'royal blue' => '#1d4ed8', 'cyan' => '#06b6d4', 'teal' => '#0d9488',
                        'green' => '#16a34a', 'dark green' => '#14532d', 'light green' => '#4ade80', 'lime' => '#84cc16', 'olive' => '#808000', 'mint' => '#10b981',
                        'yellow' => '#eab308', 'gold' => '#ffd700', 'mustard' => '#ca8a04',
                        'orange' => '#ea580c', 'dark orange' => '#c2410c', 'coral' => '#fb7185', 'peach' => '#fed7aa',
                        'purple' => '#9333ea', 'dark purple' => '#581c87', 'violet' => '#7c3aed', 'lavender' => '#c084fc', 'indigo' => '#4f46e5',
                        'pink' => '#ec4899', 'light pink' => '#fbcfe8', 'rose' => '#f43f5e', 'magenta' => '#d946ef',
                        'white' => '#ffffff', 'off white' => '#f8fafc', 'ivory' => '#fffff0', 'cream' => '#fef3c7', 'beige' => '#f5f5dc',
                        'black' => '#18181b', 'grey' => '#64748b', 'gray' => '#64748b', 'dark grey' => '#334155', 'dark gray' => '#334155',
                        'light grey' => '#cbd5e1', 'light gray' => '#cbd5e1', 'silver' => '#c0c0c0',
                        'brown' => '#78350f', 'coffee' => '#451a03', 'tan' => '#d2b48c', 'khaki' => '#f0e68c', 'burgundy' => '#800020'
                    ];
                    $getColorHex = function($color) use ($colorHexMap) {
                        $c = strtolower(trim((string)$color));
                        if (isset($colorHexMap[$c])) return $colorHexMap[$c];
                        if (str_starts_with($c, '#')) return $c;
                        return $c;
                    };
                @endphp

                @if($hasDoubleVariants && count($uniqueColors) > 0 && count($uniqueSizes) > 0)
                    <div class="mb-4 p-3 rounded-4 border bg-white shadow-xs" id="option-pills-container" data-double-variant="1">
                        <div class="d-flex align-items-center justify-content-between mb-3 pb-2 border-bottom">
                            <span class="fw-bold text-dark small" style="font-size: 0.88rem;"><i class="bi bi-layers-fill me-1 text-primary"></i> Selected Variant:</span>
                            <span id="selected-option-display" class="badge rounded-pill bg-light text-secondary border px-2.5 py-1 font-monospace fw-bold" style="font-size: 0.72rem;">Select Color & Size</span>
                        </div>

                        {{-- Step 1: Select Color --}}
                        <div class="mb-3">
                            <div class="fw-bold text-dark small mb-2" style="font-size: 0.82rem;">
                                <i class="bi bi-palette-fill me-1 text-primary"></i> 1. Select Color:
                                <span id="selected-color-label" class="text-primary fw-bold ms-1"></span>
                            </div>
                            <div class="d-flex flex-wrap gap-2">
                                @foreach($uniqueColors as $colorName)
                                    @php
                                        $cStock = $colorTotalStock[$colorName] ?? 0;
                                        $cAvail = $cStock > 0;
                                    @endphp
                                    <button type="button"
                                            class="btn btn-sm rounded-pill px-3 py-1.5 fw-bold double-color-pill d-inline-flex align-items-center {{ $cAvail ? 'btn-outline-secondary text-dark border-secondary border-opacity-50' : 'btn-light border text-muted opacity-50' }}"
                                            data-color="{{ $colorName }}"
                                            {{ !$cAvail ? 'disabled' : '' }}
                                            onclick="selectDoubleVariantColor('{{ addslashes($colorName) }}')">
                                        <span class="d-inline-block rounded-circle" 
                                              style="width: 15px; height: 15px; background-color: {{ $getColorHex($colorName) }}; border: 1px solid rgba(0,0,0,0.25); box-shadow: inset 0 0 0 1px rgba(255,255,255,0.4); flex-shrink: 0; margin-right: 8px;"></span>
                                        <span>{{ $colorName }}</span>
                                        @if(!$cAvail)
                                            <span class="badge bg-danger text-white rounded-pill ms-2" style="font-size: 0.6rem;">Sold Out</span>
                                        @endif
                                    </button>
                                @endforeach
                            </div>
                        </div>

                        {{-- Step 2: Select Size / Waist --}}
                        <div>
                            <div class="fw-bold text-dark small mb-2" style="font-size: 0.82rem;">
                                <i class="bi bi-rulers me-1 text-primary"></i> 2. {{ $secondDimLabel }}:
                                <span id="selected-size-label" class="text-primary fw-bold ms-1"></span>
                            </div>
                            <div class="d-flex flex-wrap gap-2">
                                @foreach($uniqueSizes as $sizeName)
                                    <button type="button"
                                            class="btn btn-sm rounded-pill px-3 py-1.5 fw-bold double-size-pill d-inline-flex align-items-center btn-outline-secondary text-dark border-secondary border-opacity-50"
                                            data-size="{{ $sizeName }}"
                                            onclick="selectDoubleVariantSize('{{ addslashes($sizeName) }}')">
                                        <span>{{ $sizeName }}</span>
                                        <span class="badge bg-danger text-white rounded-pill ms-1.5 double-size-stock-badge" data-size-badge="{{ $sizeName }}" style="font-size: 0.6rem; display: none;">Sold Out</span>
                                    </button>
                                @endforeach
                            </div>
                        </div>
                        <script>
                            window.doubleVariantMatrix = @json($doubleMatrix);
                        </script>
                    </div>
                @else
                    <div class="mb-4 p-3 rounded-4 border bg-white shadow-xs">
                        <div class="d-flex align-items-center justify-content-between mb-2.5">
                            <span class="fw-bold text-dark small" style="font-size: 0.88rem;"><i class="bi bi-tag-fill me-1 text-primary"></i> {{ $optionLabel }}:</span>
                            <span id="selected-option-display" class="badge rounded-pill bg-light text-secondary border px-2.5 py-1 font-monospace fw-bold" style="font-size: 0.72rem;">Please Select</span>
                        </div>
                        <div class="d-flex flex-wrap gap-2" id="option-pills-container">
                            @foreach($optionStocks as $optKey => $optQty)
                                @php
                                    $isAvailable = $optQty > 0;
                                    $isColorLike = ($optType === 'color') || isset($colorHexMap[strtolower(trim($optKey))]);
                                @endphp
                                <button type="button" 
                                        class="btn btn-sm rounded-pill px-3 py-1.5 fw-bold option-pill-btn d-inline-flex align-items-center {{ $isAvailable ? 'btn-outline-secondary text-dark border-secondary border-opacity-50' : 'btn-light border text-muted opacity-50' }}"
                                        data-option="{{ $optKey }}"
                                        data-stock="{{ $optQty }}"
                                        {{ !$isAvailable ? 'disabled' : '' }}
                                        onclick="selectProductOption('{{ addslashes($optKey) }}', {{ $optQty }})">
                                    @if($isColorLike)
                                        <span class="d-inline-block rounded-circle" 
                                              style="width: 15px; height: 15px; background-color: {{ $getColorHex($optKey) }}; border: 1px solid rgba(0,0,0,0.25); box-shadow: inset 0 0 0 1px rgba(255,255,255,0.4); flex-shrink: 0; margin-right: 8px;"></span>
                                    @endif
                                    <span>{{ $optKey }}</span>
                                    @if(!$isAvailable)
                                        <span class="badge bg-danger text-white rounded-pill ms-2" style="font-size: 0.6rem;">Sold Out</span>
                                    @endif
                                </button>
                            @endforeach
                        </div>
                    </div>
                @endif
            @endif

            {{-- 7. Quantity & CTA Purchase Actions (Flipkart / Amazon Iconic Dual Actions) --}}
            @if($product->stock > 0)
            <div class="mb-4">
                {{-- Quantity Stepper Row --}}
                <div class="d-flex align-items-center gap-3 mb-3">
                    <span class="text-muted small fw-semibold" style="font-size: 0.85rem;">Quantity:</span>
                    <div class="d-inline-flex align-items-center border rounded-pill shadow-xs bg-white p-1" style="border-color: #cbd5e1 !important; height: 38px;">
                        <button class="btn btn-sm btn-light border-0 rounded-circle text-dark d-flex align-items-center justify-content-center p-0 flex-shrink-0" type="button" onclick="changeQty(-1)" style="width: 28px; height: 28px; background: #f1f5f9;" aria-label="Decrease quantity">
                            <i class="bi bi-dash fw-bold fs-6"></i>
                        </button>
                        <input type="text" inputmode="numeric" id="qty-input" class="form-control text-center border-0 p-0 fw-bold bg-white shadow-none text-dark" value="1" min="1" max="{{ $product->stock }}" readonly style="font-size: 1.05rem; font-weight: 800 !important; color: #0f172a !important; width: 48px; min-width: 48px; pointer-events: none; -webkit-text-fill-color: #0f172a !important; opacity: 1 !important;">
                        <button class="btn btn-sm btn-light border-0 rounded-circle text-dark d-flex align-items-center justify-content-center p-0 flex-shrink-0" type="button" onclick="changeQty(1)" style="width: 28px; height: 28px; background: #f1f5f9;" aria-label="Increase quantity">
                            <i class="bi bi-plus fw-bold fs-6"></i>
                        </button>
                    </div>
                </div>

                {{-- Action Buttons (Flipkart Amber Cart & Orange Buy Now) --}}
                <div class="row g-2.5">
                    {{-- Add to Cart --}}
                    <div class="col-6">
                        <form action="{{ route('cart.add') }}" method="POST" class="ajax-cart-form no-loader w-100" onsubmit="return validateOptionSelected(this)">
                            @csrf
                            <input type="hidden" name="product_id" value="{{ $product->id }}">
                            <input type="hidden" name="quantity" class="hidden-qty-input" value="1">
                            <input type="hidden" name="selected_option" class="hidden-selected-option" value="">
                            <button type="submit" class="btn w-100 rounded-3 text-white fw-bold py-3 text-uppercase shadow-sm add-to-cart-btn no-loader d-flex align-items-center justify-content-center gap-2 pdp-cart-btn" 
                                    style="background: #ff9f00; font-size: 0.92rem; letter-spacing: 0.5px; border: none; height: 50px;">
                                <i class="bi bi-cart-plus-fill fs-5"></i> <span>Add to Cart</span>
                            </button>
                        </form>
                    </div>

                    {{-- Buy Now --}}
                    <div class="col-6">
                        <form action="{{ route('cart.add') }}" method="POST" class="w-100" id="buy-now-form" onsubmit="return validateOptionSelected(this)">
                            @csrf
                            <input type="hidden" name="product_id" value="{{ $product->id }}">
                            <input type="hidden" name="quantity" class="hidden-qty-input" value="1">
                            <input type="hidden" name="selected_option" class="hidden-selected-option" value="">
                            <input type="hidden" name="buy_now" value="1">
                            <button type="submit" id="buy-now-btn" class="btn w-100 rounded-3 text-white fw-bold py-3 text-uppercase shadow-sm d-flex align-items-center justify-content-center gap-2 pdp-buy-btn" 
                                    style="background: #fb641b; font-size: 0.92rem; letter-spacing: 0.5px; border: none; height: 50px;">
                                <i class="bi bi-lightning-fill fs-5"></i> <span>Buy Now</span>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
            @endif

            {{-- 8. 4 Buyer Guarantees Strip (Amazon Trust Bar) --}}
            <div class="p-3 rounded-4 mb-4" style="background: #f8fafc; border: 1px solid #e2e8f0;">
                <div class="row g-2 g-sm-3 text-center align-items-center">
                    <div class="col-6 col-sm-3 border-end border-light-subtle pb-2 pb-sm-0">
                        <div class="d-flex flex-column align-items-center">
                            <div class="rounded-circle d-flex align-items-center justify-content-center mb-1 shadow-xs" style="width: 38px; height: 38px; background: #ffffff; color: #4f46e5;">
                                <i class="bi bi-lightning-charge-fill fs-5"></i>
                            </div>
                            <span class="fw-bold text-dark" style="font-size: 0.74rem; line-height: 1.25;">Fast Express<br class="d-none d-sm-inline"> Dispatch</span>
                        </div>
                    </div>
                    <div class="col-6 col-sm-3 border-end-0 border-sm-end border-light-subtle pb-2 pb-sm-0">
                        <div class="d-flex flex-column align-items-center">
                            <div class="rounded-circle d-flex align-items-center justify-content-center mb-1 shadow-xs" style="width: 38px; height: 38px; background: #ffffff; color: #10b981;">
                                <i class="bi bi-truck fs-5"></i>
                            </div>
                            <span class="fw-bold text-dark" style="font-size: 0.74rem; line-height: 1.25;">Free Delivery<br class="d-none d-sm-inline"> Orders ₹499+</span>
                        </div>
                    </div>
                    <div class="col-6 col-sm-3 border-end border-light-subtle pt-2 pt-sm-0">
                        <div class="d-flex flex-column align-items-center">
                            <div class="rounded-circle d-flex align-items-center justify-content-center mb-1 shadow-xs" style="width: 38px; height: 38px; background: #ffffff; color: #f59e0b;">
                                <i class="bi bi-patch-check-fill fs-5"></i>
                            </div>
                            <span class="fw-bold text-dark" style="font-size: 0.74rem; line-height: 1.25;">100% Genuine<br class="d-none d-sm-inline"> Direct Brands</span>
                        </div>
                    </div>
                    <div class="col-6 col-sm-3 pt-2 pt-sm-0">
                        <div class="d-flex flex-column align-items-center">
                            <div class="rounded-circle d-flex align-items-center justify-content-center mb-1 shadow-xs" style="width: 38px; height: 38px; background: #ffffff; color: #06b6d4;">
                                <i class="bi bi-cash-stack fs-5"></i>
                            </div>
                            <span class="fw-bold text-dark" style="font-size: 0.74rem; line-height: 1.25;">Pay on<br class="d-none d-sm-inline"> Delivery</span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- 9. Delivery Options & Pincode Checker (Flipkart Clean Delivery Widget) --}}
            @php
                $activeLocation = app(\App\Services\DeliveryService::class)->getSessionLocation();
            @endphp
            <div class="mb-4 pt-2">
                <div class="d-flex align-items-center gap-2 mb-2.5">
                    <i class="bi bi-geo-alt-fill text-primary"></i>
                    <span class="fw-bold text-dark" style="font-size: 0.94rem;">Delivery Options</span>
                </div>

                {{-- Clean Inline Pincode Check Form --}}
                <div class="d-flex align-items-center gap-2 mb-3" style="max-width: 400px;">
                    <div class="input-group rounded-3 border p-1" style="background: #ffffff; border-color: #cbd5e1 !important;">
                        <input type="text" id="pdpPincodeInput" maxlength="6" 
                               class="form-control border-0 font-monospace fw-bold ps-3 text-dark shadow-none" 
                               placeholder="Enter 6-digit Pincode" value="{{ $activeLocation['pincode'] ?? '' }}"
                               style="font-size: 0.95rem; letter-spacing: 0.05em;">
                        <button class="btn btn-primary rounded-2 px-3.5 py-1.5 fw-semibold shadow-2xs" 
                                style="font-size: 0.86rem;" type="button" id="pdpCheckPincodeBtn">
                            Check
                        </button>
                    </div>
                </div>

                {{-- Live Delivery Checklist Results --}}
                <div id="pdpDeliveryResults" class="{{ $activeLocation ? '' : 'd-none' }}">
                    @if($activeLocation && $activeLocation['is_serviceable'])
                        <div class="d-flex flex-column gap-2 mt-3 p-3 rounded-3 bg-light border border-slate-200" style="font-size: 0.85rem;">
                            <div class="d-flex align-items-center gap-2">
                                <i class="bi bi-truck text-success fs-6 flex-shrink-0"></i>
                                <span class="text-dark">Delivery to <strong>{{ $activeLocation['location_text'] }}</strong> by <strong class="text-primary">{{ $activeLocation['estimated_delivery'] }}</strong> <span class="text-success fw-semibold">| {{ ($activeLocation['delivery_charge'] ?? 0) == 0 ? 'FREE Delivery' : '₹' . number_format($activeLocation['delivery_charge'], 0) . ' (FREE ₹' . number_format($activeLocation['free_shipping_min'] ?? 499, 0) . '+)' }}</span></span>
                            </div>
                            <div class="d-flex align-items-center gap-2">
                                <i class="bi bi-cash-stack text-success fs-6 flex-shrink-0"></i>
                                <span class="text-muted">{{ $activeLocation['is_cod_available'] ? 'Cash on Delivery available' : 'Prepaid payments only' }}</span>
                            </div>
                            <div class="d-flex align-items-center gap-2">
                                <i class="bi bi-shield-check text-success fs-6 flex-shrink-0"></i>
                                <span class="text-muted">100% Verified Quality & Direct Brand Warranty</span>
                            </div>
                        </div>
                    @endif
                </div>
            </div>

        </div>
    </div>

    {{-- 10. Specifications & Description Accordion (Collapsed / Closed by Default) --}}
    <div class="accordion pdp-details-accordion mt-4 mt-md-5 d-flex flex-column gap-3" id="pdpAccordion">
        
        {{-- Item 1: Product Overview (Closed by default) --}}
        <div class="accordion-item border rounded-3 overflow-hidden shadow-xs" style="background: #ffffff; border: 1px solid #e2e8f0 !important;">
            <h2 class="accordion-header" id="headingOverview">
                <button class="accordion-button collapsed fw-bold text-dark py-3 px-3 px-md-4 shadow-none" 
                        type="button" data-bs-toggle="collapse" data-bs-target="#collapseOverview" 
                        aria-expanded="false" aria-controls="collapseOverview" 
                        style="font-size: 0.95rem; background: #ffffff;">
                    <div class="d-flex align-items-center gap-2">
                        <i class="bi bi-file-text text-primary fs-5"></i>
                        <span>Product Overview</span>
                    </div>
                </button>
            </h2>
            <div id="collapseOverview" class="accordion-collapse collapse" aria-labelledby="headingOverview">
                <div class="accordion-body p-3 p-md-4 border-top" style="border-color: #f1f5f9 !important; font-size: 0.9rem; line-height: 1.8; color: #334155;">
                    <div class="prose">
                        {!! $product->description !!}
                    </div>
                </div>
            </div>
        </div>

        {{-- Item 2: Technical Specifications (Closed by default) --}}
        @if($product->specifications)
        <div class="accordion-item border rounded-3 overflow-hidden shadow-xs" style="background: #ffffff; border: 1px solid #e2e8f0 !important;">
            <h2 class="accordion-header" id="headingSpecs">
                <button class="accordion-button collapsed fw-bold text-dark py-3 px-3 px-md-4 shadow-none" 
                        type="button" data-bs-toggle="collapse" data-bs-target="#collapseSpecs" 
                        aria-expanded="false" aria-controls="collapseSpecs" 
                        style="font-size: 0.95rem; background: #ffffff;">
                    <div class="d-flex align-items-center gap-2">
                        <i class="bi bi-list-check text-info fs-5"></i>
                        <span>Technical Specifications</span>
                    </div>
                </button>
            </h2>
            <div id="collapseSpecs" class="accordion-collapse collapse" aria-labelledby="headingSpecs">
                <div class="accordion-body p-3 p-md-4 border-top" style="border-color: #f1f5f9 !important;">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover mb-0 rounded-3 overflow-hidden border pdp-specs-table" style="font-size: 0.86rem; border-color: #e2e8f0 !important;">
                            <tbody>
                                @foreach(explode("\n", $product->specifications) as $spec)
                                    @php $parts = explode(":", $spec, 2); @endphp
                                    @if(count($parts) == 2)
                                    <tr>
                                        <th class="text-secondary fw-semibold py-2.5 px-3 bg-light" style="width: 35%; border-color: #f1f5f9;">
                                            {{ trim($parts[0]) }}
                                        </th>
                                        <td class="text-dark fw-medium py-2.5 px-3" style="border-color: #f1f5f9;">
                                            {{ trim($parts[1]) }}
                                        </td>
                                    </tr>
                                    @endif
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        @endif
    </div>

    {{-- 11. Similar / Related Products Section (Amazon Style Recommendations) --}}
    @if(isset($relatedProducts) && $relatedProducts->isNotEmpty())
    <div class="mt-5 pt-3">
        <div class="d-flex justify-content-between align-items-center mb-3 mb-md-4">
            <div>
                <span class="badge rounded-pill px-3 py-1 fw-bold text-uppercase d-inline-flex align-items-center gap-1" style="background: rgba(99,102,241,0.12); color: #6366f1; font-size: 0.72rem; letter-spacing: 0.05em;">
                    <i class="bi bi-stars"></i> Recommendations
                </span>
                <h4 class="fw-bolder mb-0 text-dark mt-1" style="letter-spacing: -0.02em; font-size: clamp(1.15rem, 2.5vw, 1.5rem);">
                    Similar Products You Might Like
                </h4>
            </div>
            <a href="{{ route('category.products', $product->category->slug) }}" class="btn btn-sm btn-light border rounded-pill px-3 py-1.5 fw-semibold text-primary" style="font-size: 0.8rem;">
                <span>View All</span> <i class="bi bi-arrow-right ms-1"></i>
            </a>
        </div>

        <div class="row row-cols-2 row-cols-md-3 row-cols-lg-4 g-2 g-md-4">
            @foreach($relatedProducts as $relatedProduct)
                <div class="col d-flex">
                    @include('customer.components.product-card', ['product' => $relatedProduct])
                </div>
            @endforeach
        </div>
    </div>
    @endif

    {{-- 12. Customer Reviews Section (At End of Page, After Recommendations) --}}
    <div class="card border-0 shadow-xs rounded-4 mt-5 overflow-hidden mb-4" id="rev-section" style="background: #ffffff; border: 1px solid #e2e8f0 !important;">
        {{-- Section Header --}}
        <div class="border-bottom bg-light px-3 px-md-4 py-3 d-flex align-items-center justify-content-between flex-wrap gap-2">
            <div class="d-flex align-items-center gap-2">
                <i class="bi bi-chat-heart text-warning fs-5"></i>
                <h5 class="fw-bold text-dark mb-0" style="font-size: 1.1rem; letter-spacing: -0.01em;">
                    Ratings & Reviews
                </h5>
                <span class="badge bg-secondary-subtle text-secondary rounded-pill px-2.5 py-1" id="pdp-review-count-badge" style="font-size: 0.76rem;">
                    {{ $totalReviewsCount ?? $product->approvedReviews->count() }}
                </span>
            </div>
            @auth('customer')
                <button class="btn btn-outline-secondary rounded-2 px-3 py-1.5 fw-semibold shadow-xs" 
                        data-bs-toggle="collapse" data-bs-target="#reviewForm" style="font-size: 0.85rem;">
                    <i class="bi bi-star-fill text-warning me-1"></i> Rate Product
                </button>
            @else
                <a href="{{ route('login') }}" class="btn btn-outline-secondary rounded-2 px-3 py-1.5 fw-semibold shadow-xs" style="font-size: 0.85rem;">
                    <i class="bi bi-box-arrow-in-right me-1"></i> Sign In to Rate
                </a>
            @endauth
        </div>

        <div class="p-3 p-md-4.5">
            {{-- 1. Flipkart Header & Breakdown Banner --}}
            <div class="p-3 p-md-4 rounded-3 border mb-4" style="background: #ffffff; border-color: #e2e8f0 !important;">
                {{-- Breakdown Row (Score on Left + Bars on Right) --}}
                <div class="row g-4 align-items-center">
                    {{-- Overall Score --}}
                    <div class="col-md-4 text-center text-md-start border-end-md border-light-subtle">
                        <div class="d-flex align-items-baseline justify-content-center justify-content-md-start gap-2 mb-1">
                            <span class="fw-bolder text-dark" style="font-size: 2.8rem; line-height: 1;">
                                {{ number_format($avgRating, 1) }}
                            </span>
                            <i class="bi bi-star-fill text-success fs-3"></i>
                        </div>
                        <div class="text-muted small" style="font-size: 0.82rem;">
                            {{ number_format($totalReviewsCount ?? $product->approvedReviews->count()) }} Ratings & {{ number_format($totalReviewsCount ?? $product->approvedReviews->count()) }} Reviews
                        </div>
                    </div>

                    {{-- Star Distribution Progress Bars (Flipkart Green/Amber/Red) --}}
                    <div class="col-md-8">
                        <div class="d-flex flex-column gap-1.5" style="max-width: 480px;">
                            @php
                                $barColors = [
                                    5 => '#388e3c',
                                    4 => '#388e3c',
                                    3 => '#388e3c',
                                    2 => '#ff9f00',
                                    1 => '#ff6161'
                                ];
                            @endphp
                            @for($i = 5; $i >= 1; $i--)
                                @php $pct = isset($ratingPercentages[$i]) ? (int) $ratingPercentages[$i] : (int) round($product->ratingPercentage($i)); @endphp
                                <div class="d-flex align-items-center gap-2" style="font-size: 0.78rem;">
                                    <span class="text-secondary fw-semibold text-nowrap" style="width: 32px;">{{ $i }} ★</span>
                                    <div class="progress flex-grow-1 rounded-pill" style="height: 6px; background: #e2e8f0;">
                                        <div class="progress-bar rounded-pill" role="progressbar" 
                                             style="width: {{ $pct }}%; background-color: {{ $barColors[$i] }};"></div>
                                    </div>
                                    <span class="text-muted fw-semibold text-end text-nowrap" style="width: 38px;">{{ $pct }}%</span>
                                </div>
                            @endfor
                        </div>
                    </div>
                </div>
            </div>

            {{-- 2. Write Review Form Collapse --}}
            @auth('customer')
            <div class="collapse mb-4" id="reviewForm">
                <div class="p-3 p-sm-4 rounded-4 border mb-3 shadow-xs" style="background: #f8fafc; border-color: #e2e8f0 !important;">
                    @include('customer.components.reviews.write-review-form', ['product' => $product])
                </div>
            </div>
            @endauth

            {{-- 3. Verified Customer Reviews Stream (Flipkart Style List / Mobile Horizontal Carousel) --}}
            @php
                $revCount = $totalReviewsCount ?? $product->approvedReviews->count();
                $reviewsList = $initialReviews ?? $product->approvedReviews;
            @endphp
            @if($revCount > 1)
                <div class="d-flex d-md-none align-items-center justify-content-between text-muted small mb-2 px-1">
                    <span style="font-size: 0.76rem;"><i class="bi bi-arrow-left-right me-1 text-primary"></i> Swipe horizontally for all reviews</span>
                    <span class="badge bg-light text-secondary border rounded-pill" style="font-size: 0.72rem;">{{ $revCount }} Reviews</span>
                </div>
            @endif
            <div class="review-list bg-white rounded-3 border p-3 p-md-4" style="border-color: #e2e8f0 !important;">
                <div id="pdp-reviews-container">
                    @forelse($reviewsList as $review)
                        @include('customer.components.reviews.review-card', ['review' => $review])
                    @empty
                        <div class="text-center py-5 w-100 bg-white rounded-3 border p-4">
                            <i class="bi bi-chat-square-heart display-4 text-muted opacity-25"></i>
                            <h6 class="mt-3 fw-bold text-dark">No Reviews Yet</h6>
                            <p class="text-muted small mb-0">Be the first verified customer to rate and review this product!</p>
                        </div>
                    @endforelse
                </div>

                @if($revCount > $reviewsList->count())
                    <div class="text-center pt-3 mt-2 border-top" id="loadMoreReviewsWrap" style="border-color: #f1f5f9 !important;">
                        <button type="button" id="loadMoreReviewsBtn" data-page="2" 
                                data-url="{{ route('product.reviews.load-more', $product->slug) }}"
                                class="btn btn-outline-dark rounded-pill px-4 py-2 fw-semibold d-inline-flex align-items-center gap-2 shadow-xs transition-all hover-elevate"
                                style="font-size: 0.84rem; border-color: #cbd5e1; background: #ffffff;">
                            <span class="btn-text">Load More Reviews (<span id="reviewsRemainingCount">{{ $revCount - $reviewsList->count() }}</span> remaining)</span>
                            <i class="bi bi-chevron-down" style="font-size: 0.75rem;"></i>
                        </button>
                    </div>
                @endif
            </div>
        </div>
    </div>

</div>



<style>
    /* Floating Watch Video Button */
    .pdp-video-btn-wrap {
        z-index: 25 !important;
        pointer-events: auto !important;
    }
    .pdp-video-floating-btn {
        background: rgba(15, 23, 42, 0.88) !important;
        backdrop-filter: blur(8px);
        -webkit-backdrop-filter: blur(8px);
        border: 1px solid rgba(255, 255, 255, 0.22) !important;
        color: #ffffff !important;
        padding: 5px 13px 5px 6px !important;
        border-radius: 9999px !important;
        font-size: 0.78rem !important;
        line-height: 1 !important;
        transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
        box-shadow: 0 4px 14px rgba(0, 0, 0, 0.25) !important;
        cursor: pointer !important;
        touch-action: manipulation;
        user-select: none;
        -webkit-tap-highlight-color: transparent;
    }
    .pdp-video-floating-btn:hover,
    .pdp-video-floating-btn:focus,
    .pdp-video-floating-btn:active {
        background: #0f172a !important;
        border-color: rgba(239, 68, 68, 0.8) !important;
        color: #ffffff !important;
        transform: translateY(-1px) scale(1.03);
        box-shadow: 0 6px 18px rgba(239, 68, 68, 0.35) !important;
    }
    .pdp-video-play-icon {
        width: 22px;
        height: 22px;
        background: #ef4444;
        box-shadow: 0 2px 6px rgba(239, 68, 68, 0.5);
        font-size: 0.8rem;
        padding-left: 1.5px;
        flex-shrink: 0;
    }
    .pdp-video-btn-text {
        font-weight: 600;
        letter-spacing: 0.02em;
        white-space: nowrap;
    }

    @media (max-width: 767.98px) {
        .pdp-video-btn-wrap {
            margin: 8px !important;
        }
        .pdp-video-floating-btn {
            padding: 4px 10px 4px 5px !important;
            font-size: 0.72rem !important;
            background: rgba(15, 23, 42, 0.92) !important;
        }
        .pdp-video-play-icon {
            width: 20px;
            height: 20px;
            font-size: 0.75rem;
        }
    }

    .pdp-main-image-stage {
        width: 100%;
        max-width: 480px;
        aspect-ratio: 1 / 1;
        background: #ffffff;
        overflow: hidden;
        margin: 0 auto;
    }
    .pdp-uniform-image-box {
        width: 100%;
        height: 100%;
        max-width: 100%;
        max-height: 100%;
        aspect-ratio: 1 / 1;
        overflow: hidden;
        border-radius: 12px;
        background: #ffffff;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 8px;
    }
    .main-pdp-image {
        width: 100% !important;
        height: 100% !important;
        object-fit: contain !important;
        object-position: center center !important;
        transition: opacity 0.2s ease, transform 0.3s ease;
    }
    .gallery-thumb { 
        transition: all 0.2s ease; 
        opacity: 0.75; 
    }
    .gallery-thumb.active { 
        opacity: 1; 
        border-color: #4f46e5 !important; 
        box-shadow: 0 0 0 2px rgba(79, 70, 229, 0.25);
    }
    .gallery-thumb:hover { 
        opacity: 1; 
    }
    .main-pdp-image:hover {
        transform: scale(1.04);
    }
    .pdp-slider-dot {
        width: 8px;
        height: 8px;
        border-radius: 999px;
        background: #cbd5e1;
        border: none;
        padding: 0;
        transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
        cursor: pointer;
    }
    .pdp-slider-dot.active {
        width: 24px;
        background: #4f46e5;
    }
    .pdp-arrow-nav {
        transition: transform 0.2s ease, background-color 0.2s ease, box-shadow 0.2s ease;
    }
    .pdp-arrow-nav:hover {
        background: #ffffff !important;
        transform: translateY(-50%) scale(1.08) !important;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.12) !important;
    }
    .pdp-tab-btn {
        font-size: 0.88rem;
        white-space: nowrap;
        border-radius: 8px 8px 0 0;
        transition: all 0.2s ease;
        padding-bottom: 10px !important;
    }
    .pdp-tab-btn.active {
        color: #4f46e5 !important;
        background: #ffffff !important;
        border-bottom: 2.5px solid #4f46e5 !important;
        font-weight: 700 !important;
    }
    .pdp-tab-btn:not(.active):hover {
        color: #1e293b !important;
        background: rgba(0, 0, 0, 0.03);
    }
    #productDetailTabs::-webkit-scrollbar {
        display: none;
    }
    .hover-primary:hover {
        color: #4f46e5 !important;
    }
    .cursor-pointer { cursor: pointer; }
    
    @media (max-width: 767.98px) {
        .product-gallery-sticky {
            position: static !important;
        }
        .pdp-main-image-stage {
            width: 100%;
            aspect-ratio: 1 / 1 !important;
            height: auto !important;
            min-height: auto !important;
            max-height: none !important;
        }
        .pdp-uniform-image-box {
            width: 100% !important;
            height: 100% !important;
            aspect-ratio: 1 / 1 !important;
            padding: 6px !important;
        }
        body {
            padding-bottom: 60px;
        }
        .review-list {
            display: flex !important;
            flex-direction: row !important;
            flex-wrap: nowrap !important;
            overflow-x: auto !important;
            -webkit-overflow-scrolling: touch;
            scrollbar-width: none;
            gap: 12px;
            padding: 4px 2px 14px 2px !important;
            border: none !important;
            background: transparent !important;
        }
        .review-list::-webkit-scrollbar {
            display: none;
        }
        .review-card-item {
            flex: 0 0 280px !important;
            width: 280px !important;
            max-width: 82vw !important;
            background: #ffffff !important;
            border: 1px solid #e2e8f0 !important;
            border-radius: 14px !important;
            padding: 14px !important;
            box-shadow: 0 2px 8px rgba(0,0,0,0.04) !important;
            display: flex !important;
            flex-direction: column !important;
            justify-content: space-between !important;
        }
        .review-comment-text {
            display: -webkit-box;
            -webkit-line-clamp: 4;
            -webkit-box-orient: vertical;
            overflow: hidden;
            font-size: 0.84rem !important;
        }
    }

    @media (max-width: 575.98px) {
        .pdp-tab-btn {
            font-size: 0.8rem !important;
            padding: 0.55rem 0.85rem !important;
        }
        .pdp-specs-table th {
            width: 40% !important;
            font-size: 0.78rem !important;
            padding: 0.5rem 0.65rem !important;
        }
        .pdp-specs-table td {
            font-size: 0.8rem !important;
            padding: 0.5rem 0.65rem !important;
        }
    }

    /* Amazon-style Clean Fullscreen Lightbox */
    #productLightboxModal {
        background: #ffffff !important;
        padding: 0 !important;
    }
    #productLightboxModal .modal-dialog {
        max-width: 100% !important;
        width: 100% !important;
        height: 100% !important;
        margin: 0 !important;
    }
    #productLightboxModal .modal-content {
        background: #ffffff !important;
        border: none !important;
        border-radius: 0 !important;
        box-shadow: none !important;
        height: 100dvh !important;
        min-height: 100dvh !important;
        max-height: 100dvh !important;
        display: flex !important;
        flex-direction: column !important;
    }
    #productLightboxModal .modal-header {
        display: flex !important;
        flex-direction: row !important;
        align-items: center !important;
        justify-content: space-between !important;
        flex-shrink: 0 !important;
        border: none !important;
        background: #ffffff !important;
        padding: 0.75rem 1rem !important;
    }
    #productLightboxModal .modal-body {
        flex: 1 1 auto !important;
        min-height: 0 !important;
        overflow: hidden !important;
        display: flex !important;
        align-items: center !important;
        justify-content: center !important;
        padding: 0.5rem !important;
        background: #ffffff !important;
    }
    #productLightboxModal .modal-footer {
        flex-shrink: 0 !important;
        background: #ffffff !important;
        border-top: 1px solid #e2e8f0 !important;
        padding: 0.6rem 0.8rem !important;
        padding-bottom: calc(0.6rem + env(safe-area-inset-bottom, 0px)) !important;
        position: static !important;
        box-shadow: none !important;
    }
    .lightbox-thumb-item {
        background: #ffffff;
        border: 1px solid #d5d9d9;
        outline: none;
        transition: border-color 0.2s ease, box-shadow 0.2s ease, transform 0.15s ease;
    }
    .lightbox-thumb-item.active {
        border: 2px solid #007185 !important;
        box-shadow: 0 0 0 1px #007185 !important;
        transform: scale(1.05);
        opacity: 1 !important;
    }
    #lightbox-thumb-container::-webkit-scrollbar,
    .lightbox-thumb-scroll::-webkit-scrollbar {
        display: none;
    }
</style>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
let currentSelectedOption = '';
let selectedDoubleColor = '';
let selectedDoubleSize = '';

function selectProductOption(optKey, optQty) {
    currentSelectedOption = optKey;
    document.querySelectorAll('.option-pill-btn').forEach(btn => {
        btn.classList.remove('btn-primary', 'text-white');
        btn.classList.add('btn-outline-secondary', 'text-dark');
    });
    const clickedBtn = document.querySelector(`.option-pill-btn[data-option="${CSS.escape(optKey)}"]`);
    if (clickedBtn) {
        clickedBtn.classList.remove('btn-outline-secondary', 'text-dark');
        clickedBtn.classList.add('btn-primary', 'text-white');
    }
    const displaySpan = document.getElementById('selected-option-display');
    if (displaySpan) {
        displaySpan.textContent = optKey;
        displaySpan.className = 'badge rounded-pill bg-primary text-white border px-2.5 py-1 font-monospace fw-bold';
    }
    document.querySelectorAll('.hidden-selected-option').forEach(inp => {
        inp.value = optKey;
    });
    const qtyInput = document.getElementById('qty-input');
    if (qtyInput && optQty > 0) {
        qtyInput.max = optQty;
        if (parseInt(qtyInput.value) > optQty) {
            qtyInput.value = optQty;
            document.querySelectorAll('.hidden-qty').forEach(el => el.value = optQty);
        }
    }
    // Switch main image if variant image exists
    switchVariantImage(optKey);
}

function selectDoubleVariantColor(colorName) {
    selectedDoubleColor = colorName;
    const colorLabel = document.getElementById('selected-color-label');
    if (colorLabel) colorLabel.textContent = colorName;

    // Switch main image if variant image exists for this color
    switchVariantImage(colorName);

    document.querySelectorAll('.double-color-pill').forEach(btn => {
        if (btn.getAttribute('data-color') === colorName) {
            btn.classList.remove('btn-outline-secondary', 'text-dark');
            btn.classList.add('btn-primary', 'text-white');
        } else if (!btn.disabled) {
            btn.classList.remove('btn-primary', 'text-white');
            btn.classList.add('btn-outline-secondary', 'text-dark');
        }
    });

    const matrix = window.doubleVariantMatrix || {};
    const sizesForColor = matrix[colorName] || {};

    document.querySelectorAll('.double-size-pill').forEach(btn => {
        const sizeName = btn.getAttribute('data-size');
        const stock = sizesForColor[sizeName] !== undefined ? parseInt(sizesForColor[sizeName]) : 0;
        const badge = btn.querySelector('.double-size-stock-badge');

        if (stock > 0) {
            btn.disabled = false;
            btn.classList.remove('btn-light', 'text-muted', 'opacity-50');
            if (selectedDoubleSize === sizeName) {
                btn.classList.remove('btn-outline-secondary', 'text-dark');
                btn.classList.add('btn-primary', 'text-white');
            } else {
                btn.classList.remove('btn-primary', 'text-white');
                btn.classList.add('btn-outline-secondary', 'text-dark');
            }
            if (badge) {
                badge.style.display = 'none';
                badge.textContent = '';
            }
        } else {
            btn.disabled = true;
            btn.classList.remove('btn-primary', 'text-white', 'btn-outline-secondary', 'text-dark');
            btn.classList.add('btn-light', 'border', 'text-muted', 'opacity-50');
            if (badge) {
                badge.style.display = 'inline-block';
                badge.textContent = 'Sold Out';
                badge.className = 'badge bg-danger text-white rounded-pill ms-1.5 double-size-stock-badge';
            }
            if (selectedDoubleSize === sizeName) {
                selectedDoubleSize = '';
                const sizeLabel = document.getElementById('selected-size-label');
                if (sizeLabel) sizeLabel.textContent = '';
            }
        }
    });

    syncDoubleVariantSelection();
}

function selectDoubleVariantSize(sizeName) {
    if (!selectedDoubleColor) {
        // Auto-select the first available color that has this size in stock
        const matrix = window.doubleVariantMatrix || {};
        for (const c of Object.keys(matrix)) {
            if ((matrix[c][sizeName] || 0) > 0) {
                selectDoubleVariantColor(c);
                break;
            }
        }
    }

    const matrix = window.doubleVariantMatrix || {};
    const stock = (selectedDoubleColor && matrix[selectedDoubleColor] && matrix[selectedDoubleColor][sizeName])
        ? parseInt(matrix[selectedDoubleColor][sizeName])
        : 0;
    if (selectedDoubleColor && stock <= 0) return;

    selectedDoubleSize = sizeName;
    const sizeLabel = document.getElementById('selected-size-label');
    if (sizeLabel) sizeLabel.textContent = sizeName;

    document.querySelectorAll('.double-size-pill').forEach(btn => {
        if (btn.getAttribute('data-size') === sizeName && !btn.disabled) {
            btn.classList.remove('btn-outline-secondary', 'text-dark');
            btn.classList.add('btn-primary', 'text-white');
        } else if (!btn.disabled) {
            btn.classList.remove('btn-primary', 'text-white');
            btn.classList.add('btn-outline-secondary', 'text-dark');
        }
    });

    syncDoubleVariantSelection();
}

function syncDoubleVariantSelection() {
    const displaySpan = document.getElementById('selected-option-display');
    const matrix = window.doubleVariantMatrix || {};

    if (selectedDoubleColor && selectedDoubleSize) {
        const stock = (matrix[selectedDoubleColor] && matrix[selectedDoubleColor][selectedDoubleSize])
            ? parseInt(matrix[selectedDoubleColor][selectedDoubleSize])
            : 0;
        if (stock > 0) {
            currentSelectedOption = `${selectedDoubleColor} - ${selectedDoubleSize}`;
            if (displaySpan) {
                displaySpan.textContent = currentSelectedOption;
                displaySpan.className = 'badge rounded-pill bg-primary text-white border px-2.5 py-1 font-monospace fw-bold';
            }
            document.querySelectorAll('.hidden-selected-option').forEach(inp => {
                inp.value = currentSelectedOption;
            });
            const qtyInput = document.getElementById('qty-input');
            if (qtyInput) {
                qtyInput.max = stock;
                if (parseInt(qtyInput.value) > stock) {
                    qtyInput.value = stock;
                    document.querySelectorAll('.hidden-qty').forEach(el => el.value = stock);
                }
            }
            return;
        }
    }

    currentSelectedOption = '';
    document.querySelectorAll('.hidden-selected-option').forEach(inp => {
        inp.value = '';
    });
    if (displaySpan) {
        if (selectedDoubleColor && !selectedDoubleSize) {
            displaySpan.textContent = `${selectedDoubleColor} — Now Select Size`;
        } else if (!selectedDoubleColor && selectedDoubleSize) {
            displaySpan.textContent = `Size ${selectedDoubleSize} — Now Select Color`;
        } else {
            displaySpan.textContent = 'Select Color & Size';
        }
        displaySpan.className = 'badge rounded-pill bg-light text-secondary border px-2.5 py-1 font-monospace fw-bold';
    }
}

function validateOptionSelected(formEl) {
    const hasOptionsContainer = document.getElementById('option-pills-container');
    if (hasOptionsContainer && !currentSelectedOption) {
        const isDouble = hasOptionsContainer.getAttribute('data-double-variant') === '1';
        const isBuyNow = formEl && (formEl.querySelector('input[name="buy_now"]') || (formEl.id && formEl.id.includes('buy-now')));
        const actionText = isBuyNow ? 'proceeding to checkout' : 'adding to your bag';

        let title = 'Select Option';
        let msg = `Please select a size or color option before ${actionText}.`;

        if (isDouble) {
            title = 'Select Color & Size';
            if (!selectedDoubleColor && !selectedDoubleSize) {
                msg = `Please select both a Color and a Size before ${actionText}.`;
            } else if (!selectedDoubleColor) {
                msg = 'Please select a Color option.';
            } else if (!selectedDoubleSize) {
                msg = `Please select an available Size for ${selectedDoubleColor}.`;
            }
        }

        if (typeof Swal !== 'undefined') {
            Swal.fire({
                icon: 'warning',
                title: title,
                text: msg,
                confirmButtonColor: isBuyNow ? '#fb641b' : '#ff9f00',
                customClass: { popup: 'rounded-4 shadow-lg border-0' }
            });
        } else {
            alert(msg);
        }

        // Smooth scroll to variant selection container and glow outline
        hasOptionsContainer.scrollIntoView({ behavior: 'smooth', block: 'center' });
        hasOptionsContainer.style.transition = 'all 0.3s ease';
        hasOptionsContainer.style.outline = '2px solid #ef4444';
        hasOptionsContainer.style.boxShadow = '0 0 16px rgba(239, 68, 68, 0.35)';
        setTimeout(() => {
            hasOptionsContainer.style.outline = '';
            hasOptionsContainer.style.boxShadow = '';
        }, 3000);

        return false;
    }
    return true;
}

let currentProductGalleryIdx = 0;
const pdpGalleryImages = @json($allProductImages);
const pdpVariantImages = @json($product->option_images ?? []);

function switchVariantImage(key) {
    if (!key || !pdpVariantImages || typeof pdpVariantImages !== 'object') return;
    
    let path = pdpVariantImages[key];
    if (!path && key.includes(' - ')) {
        const color = key.split(' - ')[0].trim();
        path = pdpVariantImages[color];
    }
    
    if (path) {
        const fullUrl = path.startsWith('http') ? path : '/storage/' + path;
        const mainImg = document.getElementById('main-product-img');
        if (mainImg) {
            const idx = pdpGalleryImages ? pdpGalleryImages.indexOf(fullUrl) : -1;
            if (idx !== -1) {
                selectProductGalleryIndex(idx);
            } else {
                mainImg.style.opacity = '0.35';
                setTimeout(() => {
                    mainImg.src = fullUrl;
                    mainImg.style.opacity = '1';
                }, 90);
            }
        }
    }
}

function selectProductGalleryIndex(idx) {
    if (!pdpGalleryImages || !pdpGalleryImages.length) return;
    if (idx < 0) idx = pdpGalleryImages.length - 1;
    if (idx >= pdpGalleryImages.length) idx = 0;

    currentProductGalleryIdx = idx;
    const src = pdpGalleryImages[idx];
    const mainImg = document.getElementById('main-product-img');

    if (mainImg && src && mainImg.src !== src) {
        mainImg.style.opacity = '0.35';
        setTimeout(() => {
            mainImg.src = src;
            mainImg.style.opacity = '1';
        }, 90);
    }

    // Update slider dots
    const dots = document.querySelectorAll('.pdp-slider-dot');
    dots.forEach((dot, i) => {
        if (i === idx) {
            dot.classList.add('active');
        } else {
            dot.classList.remove('active');
        }
    });
}

function stepProductGallery(delta) {
    selectProductGalleryIndex(currentProductGalleryIdx + delta);
}

// Touch swipe & tap support on main image for mobile
document.addEventListener('DOMContentLoaded', function() {
    const stage = document.querySelector('.pdp-main-image-stage');
    if (stage) {
        let startX = 0;
        let startY = 0;
        let isMoving = false;
        stage.addEventListener('touchstart', function(e) {
            if (e.touches && e.touches.length === 1) {
                startX = e.touches[0].clientX;
                startY = e.touches[0].clientY;
                isMoving = false;
            }
        }, { passive: true });

        stage.addEventListener('touchmove', function(e) {
            isMoving = true;
        }, { passive: true });

        stage.addEventListener('touchend', function(e) {
            if (e.changedTouches && e.changedTouches.length === 1) {
                const diffX = e.changedTouches[0].clientX - startX;
                const diffY = e.changedTouches[0].clientY - startY;
                if (Math.abs(diffX) > 40) {
                    if (diffX < 0) {
                        stepProductGallery(1); // Swipe left -> Next
                    } else {
                        stepProductGallery(-1); // Swipe right -> Prev
                    }
                } else if (!isMoving || (Math.abs(diffX) < 10 && Math.abs(diffY) < 10)) {
                    // Tap on image without drag -> Open full screen lightbox!
                    if (!e.target.closest('.pdp-video-btn-wrap') && !e.target.closest('button')) {
                        openProductLightbox();
                    }
                }
            }
        }, { passive: true });
    }
});

// Share Product Function
function shareProduct() {
    const title = @json($product->name);
    const url = window.location.href;

    if (navigator.share) {
        navigator.share({
            title: title,
            text: 'Check out ' + title + ' on ShopCalm!',
            url: url
        }).catch(() => {});
    } else {
        if (navigator.clipboard && navigator.clipboard.writeText) {
            navigator.clipboard.writeText(url).then(() => {
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        icon: 'success',
                        title: 'Link Copied!',
                        text: 'Product link copied to clipboard.',
                        toast: true,
                        position: 'top-end',
                        showConfirmButton: false,
                        timer: 2500
                    });
                } else {
                    alert('Product link copied to clipboard!');
                }
            }).catch(() => {
                alert('Share URL: ' + url);
            });
        } else {
            alert('Share URL: ' + url);
        }
    }
}

function updateMainImage(src, thumb) {
    const idx = thumb && thumb.hasAttribute('data-index') ? parseInt(thumb.getAttribute('data-index'), 10) : 0;
    selectProductGalleryIndex(idx);
}

function changeQty(amt) {
    const input = document.getElementById('qty-input');
    const hiddens = document.querySelectorAll('.hidden-qty-input');
    if (!input) return;
    let val = parseInt(input.value) + amt;
    const max = parseInt(input.max) || 999;
    const min = parseInt(input.min) || 1;

    if (val >= min && val <= max) {
        input.value = val;
        hiddens.forEach(h => h.value = val);
    }
}

// Reset Buy Now Button on page load/restore
function resetBuyNowBtns() {
    const desktopBtn = document.getElementById('buy-now-btn');
    if (desktopBtn) {
        desktopBtn.disabled = false;
        desktopBtn.innerHTML = '<i class="bi bi-lightning-fill fs-5"></i> <span>Buy Now</span>';
    }
}

window.addEventListener('pageshow', function() {
    resetBuyNowBtns();
});

window.addEventListener('focus', function() {
    resetBuyNowBtns();
});

// PDP Delivery Checker
document.addEventListener('DOMContentLoaded', function() {
    resetBuyNowBtns();

    const pdpCheckBtn = document.getElementById('pdpCheckPincodeBtn');
    const pdpInput = document.getElementById('pdpPincodeInput');
    const pdpResults = document.getElementById('pdpDeliveryResults');

    function checkPDPPincode(pincode) {
        if (!pincode || pincode.length !== 6) {
            if (pdpResults) {
                pdpResults.classList.remove('d-none');
                pdpResults.innerHTML = `
                    <div class="d-flex align-items-center gap-2 mt-2 p-2 rounded-3 bg-danger bg-opacity-10 text-danger border border-danger border-opacity-25" style="font-size: 0.82rem;">
                        <i class="bi bi-exclamation-circle-fill fs-6 flex-shrink-0"></i>
                        <span>Please enter a valid 6-digit PIN code.</span>
                    </div>`;
            }
            return;
        }

        if (pdpCheckBtn) {
            pdpCheckBtn.disabled = true;
            pdpCheckBtn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status"></span>';
        }

        fetch('{{ route("delivery.check") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json'
            },
            body: JSON.stringify({ pincode: pincode, save_location: true })
        })
        .then(r => r.json())
        .then(data => {
            if (pdpCheckBtn) {
                pdpCheckBtn.disabled = false;
                pdpCheckBtn.innerHTML = 'Check';
            }
            if (pdpResults) {
                pdpResults.classList.remove('d-none');
                if (data.is_serviceable) {
                    // Update header location text immediately across all pages
                    const headerText = document.getElementById('header-delivery-text');
                    if (headerText && data.location_text) {
                        headerText.textContent = data.location_text;
                    }
                    const mobileHeaderText = document.getElementById('header-mobile-delivery-text');
                    if (mobileHeaderText) {
                        mobileHeaderText.textContent = data.city || data.pincode;
                    }
                    const modalInput = document.getElementById('modalPincodeInput');
                    if (modalInput) {
                        modalInput.value = pincode;
                    }

                    const chargeLabel = (parseFloat(data.delivery_charge || 0) === 0)
                        ? 'FREE Delivery'
                        : `₹${parseFloat(data.delivery_charge).toFixed(0)} (FREE ₹${parseFloat(data.free_shipping_min || 499).toFixed(0)}+)`;
                    pdpResults.innerHTML = `
                        <div class="d-flex flex-column gap-1.5 mt-2" style="font-size: 0.84rem;">
                            <div class="d-flex align-items-center gap-2">
                                <i class="bi bi-truck text-success fs-6"></i>
                                <span class="text-dark">Delivery to <strong>${data.location_text}</strong> by <strong class="text-primary">${data.estimated_delivery}</strong> <span class="text-success fw-semibold">| ${chargeLabel}</span></span>
                            </div>
                            <div class="d-flex align-items-center gap-2">
                                <i class="bi bi-cash-stack text-success fs-6"></i>
                                <span class="text-muted">${data.is_cod_available ? 'Cash on Delivery available' : 'Prepaid payments only'}</span>
                            </div>
                            <div class="d-flex align-items-center gap-2">
                                <i class="bi bi-shield-check text-success fs-6"></i>
                                <span class="text-muted">100% Verified Quality & Direct Brand Warranty</span>
                            </div>
                        </div>`;
                } else {
                    pdpResults.innerHTML = `
                        <div class="d-flex align-items-center gap-2 mt-3 p-3 rounded-3 bg-danger bg-opacity-10 text-danger border border-danger border-opacity-25" style="font-size: 0.85rem;">
                            <i class="bi bi-x-circle-fill fs-6 flex-shrink-0"></i>
                            <span>${data.message || 'Currently not deliverable to this PIN code.'}</span>
                        </div>`;
                }
            }
        })
        .catch(() => {
            if (pdpCheckBtn) {
                pdpCheckBtn.disabled = false;
                pdpCheckBtn.innerHTML = 'Check';
            }
            if (pdpResults) {
                pdpResults.classList.remove('d-none');
                pdpResults.innerHTML = `
                    <div class="d-flex align-items-center gap-2 mt-3 p-3 rounded-3 bg-danger bg-opacity-10 text-danger border border-danger border-opacity-25" style="font-size: 0.85rem;">
                        <i class="bi bi-exclamation-circle-fill fs-6 flex-shrink-0"></i>
                        <span>Network error. Please try again.</span>
                    </div>`;
            }
        });
    }

    // Bidirectional listener when header modal updates pincode
    window.onDeliveryPincodeUpdated = function(location) {
        if (location && location.pincode) {
            if (pdpInput) pdpInput.value = location.pincode;
            checkPDPPincode(location.pincode);
        }
    };

    if (pdpCheckBtn && pdpInput) {
        pdpCheckBtn.addEventListener('click', () => checkPDPPincode(pdpInput.value.trim()));
        pdpInput.addEventListener('keydown', (e) => {
            if (e.key === 'Enter') {
                e.preventDefault();
                checkPDPPincode(pdpInput.value.trim());
            }
        });
    }

    // Instant Buy Now Checkout Handler
    function bindBuyNow(formId, btnId) {
        const form = document.getElementById(formId);
        const btn = document.getElementById(btnId);
        if (form && btn) {
            form.addEventListener('submit', function(e) {
                e.preventDefault();

                if (!validateOptionSelected(form)) {
                    return false;
                }

                // Sync current selected option to form's hidden input
                const optInput = form.querySelector('.hidden-selected-option');
                if (optInput && currentSelectedOption) {
                    optInput.value = currentSelectedOption;
                }

                btn.disabled = true;
                btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1" role="status"></span> Processing...';

                const formData = new FormData(form);
                fetch('{{ route("cart.add") }}', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: formData
                })
                .then(async r => {
                    const data = await r.json().catch(() => ({}));
                    if (!r.ok || !data.success) {
                        const errMsg = data.message || (data.errors ? Object.values(data.errors).flat()[0] : 'Could not proceed to checkout.');
                        throw new Error(errMsg);
                    }
                    return data;
                })
                .then(data => {
                    window.location.href = data.redirect_url || '{{ route("checkout.index") }}';
                })
                .catch(err => {
                    resetBuyNowBtns();
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({
                            icon: 'warning',
                            title: 'Option Notice',
                            text: err.message,
                            confirmButtonColor: '#fb641b',
                            customClass: { popup: 'rounded-4 shadow-lg border-0' }
                        });
                    } else {
                        alert(err.message);
                    }
                });
            });
        }
    }

    bindBuyNow('buy-now-form', 'buy-now-btn');
});

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
    if (btnElement) {
        btnElement.disabled = true;
        btnElement.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Deleting...';
    }

    const formData = new FormData();
    formData.append('_token', '{{ csrf_token() }}');
    formData.append('_method', 'DELETE');

    fetch(deleteUrl, {
        method: 'POST',
        headers: {
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: formData
    })
    .then(async res => {
        const data = await res.json();
        if (!res.ok || !data.success) throw new Error(data.message || 'Failed to delete review.');
        return data;
    })
    .then(data => {
        // 1. Remove all instances of this review card across the page
        const cards = document.querySelectorAll(`[data-review-id="${id}"], .review-card-item-${id}, #user-review-card-${id}`);
        cards.forEach(card => {
            card.style.transition = 'all 0.35s ease';
            card.style.opacity = '0';
            card.style.transform = 'translateY(-10px)';
            setTimeout(() => card.remove(), 350);
        });

        // 2. If the user review was shown inside #write-review-container, restore the write review form!
        const statusWrapper = document.getElementById('user-review-status-wrapper');
        const formWrapper = document.getElementById('write-review-form-wrapper');
        if (statusWrapper) {
            statusWrapper.style.transition = 'all 0.3s ease';
            statusWrapper.style.opacity = '0';
            setTimeout(() => {
                statusWrapper.style.display = 'none';
                if (formWrapper) {
                    formWrapper.style.display = 'block';
                    formWrapper.style.opacity = '0';
                    formWrapper.style.transition = 'opacity 0.3s ease';
                    setTimeout(() => formWrapper.style.opacity = '1', 50);
                }
            }, 300);
        }

        // 3. Decrement review count badge
        const countBadge = document.getElementById('pdp-review-count-badge');
        if (countBadge) {
            let current = parseInt(countBadge.textContent.trim(), 10) || 0;
            if (current > 0) countBadge.textContent = current - 1;
        }
        const reviewsRemaining = document.getElementById('reviewsRemainingCount');
        if (reviewsRemaining) {
            let current = parseInt(reviewsRemaining.textContent.trim(), 10) || 0;
            if (current > 0) reviewsRemaining.textContent = current - 1;
        }

        // 4. If reviews container has no items remaining, show empty state
        setTimeout(() => {
            const listContainer = document.getElementById('pdp-reviews-container');
            if (listContainer && listContainer.querySelectorAll('.review-card-item').length === 0) {
                listContainer.innerHTML = `
                    <div class="text-center py-5 w-100 bg-white rounded-3 border p-4">
                        <i class="bi bi-chat-square-heart display-4 text-muted opacity-25"></i>
                        <h6 class="mt-3 fw-bold text-dark">No Reviews Yet</h6>
                        <p class="text-muted small mb-0">Be the first verified customer to rate and review this product!</p>
                    </div>
                `;
            }
        }, 400);

        // 5. Toast notification (no page reload)
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                icon: 'success',
                title: 'Review Deleted',
                text: data.message || 'Your review has been deleted successfully.',
                toast: true,
                position: 'top-end',
                showConfirmButton: false,
                timer: 2500
            });
        }
    })
    .catch(err => {
        if (btnElement) {
            btnElement.disabled = false;
            btnElement.innerHTML = '<i class="bi bi-trash3"></i> Delete';
        }
        if (typeof Swal !== 'undefined') {
            Swal.fire({ icon: 'error', title: 'Error', text: err.message });
        } else {
            alert(err.message);
        }
    });
}

// --- AJAX Load More Reviews ---
const loadMoreReviewsBtn = document.getElementById('loadMoreReviewsBtn');
if (loadMoreReviewsBtn) {
    loadMoreReviewsBtn.addEventListener('click', function() {
        const btn = this;
        const page = parseInt(btn.getAttribute('data-page') || '2', 10);
        const url = btn.getAttribute('data-url');
        const originalHtml = btn.innerHTML;

        btn.disabled = true;
        btn.innerHTML = `<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> <span>Loading reviews...</span>`;

        fetch(`${url}?page=${page}`, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        })
        .then(res => {
            if (!res.ok) throw new Error('Failed to load reviews');
            return res.json();
        })
        .then(data => {
            const container = document.getElementById('pdp-reviews-container');
            if (container && data.html) {
                const tempDiv = document.createElement('div');
                tempDiv.innerHTML = data.html;
                const newCards = Array.from(tempDiv.children);
                newCards.forEach(card => {
                    card.style.opacity = '0';
                    card.style.transform = 'translateY(10px)';
                    card.style.transition = 'all 0.35s ease';
                    container.appendChild(card);
                    requestAnimationFrame(() => {
                        card.style.opacity = '1';
                        card.style.transform = 'translateY(0)';
                    });
                });
            }

            if (data.hasMore) {
                btn.setAttribute('data-page', data.nextPage);
                btn.disabled = false;
                btn.innerHTML = `<span class="btn-text">Load More Reviews (<span id="reviewsRemainingCount">${data.remaining}</span> remaining)</span> <i class="bi bi-chevron-down" style="font-size: 0.75rem;"></i>`;
            } else {
                const wrap = document.getElementById('loadMoreReviewsWrap');
                if (wrap) {
                    wrap.innerHTML = `<span class="badge bg-light text-muted border px-3 py-1.5 rounded-pill" style="font-size: 0.78rem;"><i class="bi bi-check2-all text-success me-1"></i> All ${data.total} reviews loaded</span>`;
                }
            }
        })
        .catch(err => {
            console.error('Error loading reviews:', err);
            btn.disabled = false;
            btn.innerHTML = originalHtml;
        });
    });
}

// Product Video Modal Controls
const pdpVideoEmbedUrl = @json($product->getYoutubeEmbedUrl());
function openProductVideoModal(e) {
    if (e) {
        if (typeof e.preventDefault === 'function') e.preventDefault();
        if (typeof e.stopPropagation === 'function') e.stopPropagation();
    }
    const iframe = document.getElementById('productVideoIframe');
    if (iframe && pdpVideoEmbedUrl) {
        if (!iframe.src || iframe.src === '' || iframe.src === 'about:blank' || iframe.src.indexOf('youtube') === -1) {
            iframe.src = pdpVideoEmbedUrl;
        }
    }
    const modalEl = document.getElementById('productVideoModal');
    if (modalEl) {
        if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
            const modal = bootstrap.Modal.getOrCreateInstance(modalEl);
            modal.show();
        } else {
            modalEl.classList.add('show');
            modalEl.style.display = 'block';
            document.body.classList.add('modal-open');
        }
    }
}
function closeProductVideoModal() {
    const iframe = document.getElementById('productVideoIframe');
    if (iframe) {
        iframe.src = '';
    }
}
document.addEventListener('DOMContentLoaded', function() {
    const modalEl = document.getElementById('productVideoModal');
    if (modalEl) {
        modalEl.addEventListener('hidden.bs.modal', function() {
            closeProductVideoModal();
        });
    }
});

// Product Fullscreen Lightbox Controls
let currentLightboxIdx = 0;
function openProductLightbox(idx) {
    if (typeof idx !== 'number') {
        idx = (typeof currentProductGalleryIdx === 'number') ? currentProductGalleryIdx : 0;
    }
    if (!pdpGalleryImages || !pdpGalleryImages.length) return;
    setLightboxIndex(idx);
    
    const modalEl = document.getElementById('productLightboxModal');
    if (modalEl && typeof bootstrap !== 'undefined') {
        const modal = bootstrap.Modal.getOrCreateInstance(modalEl);
        modal.show();
    }
}

function setLightboxIndex(idx) {
    if (!pdpGalleryImages || !pdpGalleryImages.length) return;
    if (idx < 0) idx = pdpGalleryImages.length - 1;
    if (idx >= pdpGalleryImages.length) idx = 0;

    currentLightboxIdx = idx;
    const imgEl = document.getElementById('lightbox-img');
    
    if (imgEl) {
        imgEl.style.opacity = '0.35';
        imgEl.src = pdpGalleryImages[idx];
        setTimeout(() => { imgEl.style.opacity = '1'; }, 70);
    }

    // Keep page gallery in sync
    selectProductGalleryIndex(idx);

    // Update Amazon-style gallery thumbnails
    const thumbs = document.querySelectorAll('.lightbox-thumb-item');
    thumbs.forEach((thumb, i) => {
        if (i === idx) {
            thumb.classList.add('active');
            thumb.style.borderColor = '#007185';
            thumb.style.boxShadow = '0 0 0 1.5px #007185';
            thumb.style.opacity = '1';
            thumb.scrollIntoView({ behavior: 'smooth', block: 'nearest', inline: 'center' });
        } else {
            thumb.classList.remove('active');
            thumb.style.borderColor = '#d5d9d9';
            thumb.style.boxShadow = 'none';
            thumb.style.opacity = '0.75';
        }
    });
}

function stepLightboxGallery(delta) {
    setLightboxIndex(currentLightboxIdx + delta);
}

document.addEventListener('DOMContentLoaded', function() {
    // Lightbox Touch Swipe on mobile
    const lbStage = document.getElementById('lightbox-stage');
    if (lbStage) {
        let lbStartX = 0;
        lbStage.addEventListener('touchstart', function(e) {
            if (e.touches && e.touches.length === 1) {
                lbStartX = e.touches[0].clientX;
            }
        }, { passive: true });

        lbStage.addEventListener('touchend', function(e) {
            if (e.changedTouches && e.changedTouches.length === 1) {
                const diffX = e.changedTouches[0].clientX - lbStartX;
                if (Math.abs(diffX) > 40) {
                    if (diffX < 0) {
                        stepLightboxGallery(1); // Swipe left -> Next
                    } else {
                        stepLightboxGallery(-1); // Swipe right -> Prev
                    }
                }
            }
        }, { passive: true });
    }

    // Keyboard navigation (Arrow keys & Esc)
    document.addEventListener('keydown', function(e) {
        const lbModal = document.getElementById('productLightboxModal');
        if (lbModal && lbModal.classList.contains('show')) {
            if (e.key === 'ArrowRight') {
                stepLightboxGallery(1);
            } else if (e.key === 'ArrowLeft') {
                stepLightboxGallery(-1);
            }
        }
    });

    // Double-tap on mobile touch to open lightbox
    const mainImgBox = document.getElementById('pdp-main-image-box');
    if (mainImgBox) {
        let lastTap = 0;
        mainImgBox.addEventListener('touchend', function(e) {
            const currentTime = new Date().getTime();
            const tapGap = currentTime - lastTap;
            if (tapGap > 0 && tapGap < 350) {
                // Double tap!
                e.preventDefault();
                openProductLightbox();
                lastTap = 0;
            } else {
                lastTap = currentTime;
            }
        });
    }

    // White transparent frosted backdrop when lightbox opens
    const lbModalEl = document.getElementById('productLightboxModal');
    if (lbModalEl) {
        lbModalEl.addEventListener('show.bs.modal', function() {
            setTimeout(() => {
                document.querySelectorAll('.modal-backdrop').forEach(b => {
                    b.classList.add('lightbox-white-backdrop');
                });
            }, 10);
        });
    }
});
</script>

{{-- YouTube Video Responsive Modal --}}
@if(!empty($product->getYoutubeEmbedUrl()))
<div class="modal fade" id="productVideoModal" tabindex="-1" aria-labelledby="productVideoModalLabel" aria-hidden="true" style="backdrop-filter: blur(5px);">
    <div class="modal-dialog modal-dialog-centered modal-lg mx-2 mx-sm-auto">
        <div class="modal-content text-white border-0 rounded-4 overflow-hidden shadow-lg" style="background-color: #0f172a !important;">
            <div class="modal-header border-0 py-2.5 px-3 d-flex justify-content-between align-items-center" style="background-color: #1e293b;">
                <h6 class="modal-title fw-bold text-white small mb-0 d-flex align-items-center gap-2" id="productVideoModalLabel">
                    <i class="bi bi-youtube text-danger fs-5"></i>
                    <span class="text-truncate" style="max-width: 220px;">{{ $product->name }}</span>
                </h6>
                <div class="d-flex align-items-center gap-2">
                    <a href="{{ $product->getYoutubeWatchUrl() }}" target="_blank" rel="noopener noreferrer" 
                       class="btn btn-sm btn-outline-danger text-white rounded-pill px-2.5 py-1 text-decoration-none d-inline-flex align-items-center gap-1.5" 
                       style="font-size: 0.72rem; border-color: rgba(239, 68, 68, 0.45); background: rgba(239, 68, 68, 0.12);"
                       title="Open directly on YouTube">
                        <i class="bi bi-box-arrow-up-right text-danger"></i>
                        <span class="d-none d-sm-inline">Open on YouTube</span>
                    </a>
                    <button type="button" class="btn btn-sm btn-outline-light rounded-circle p-0 d-flex align-items-center justify-content-center" 
                            style="width: 32px; height: 32px;" 
                            data-bs-dismiss="modal" 
                            onclick="closeProductVideoModal()" 
                            aria-label="Close">
                        <i class="bi bi-x-lg" style="font-size: 0.85rem;"></i>
                    </button>
                </div>
            </div>
            <div class="modal-body p-2 p-sm-3 bg-black">
                <div class="ratio ratio-16x9 rounded-3 overflow-hidden">
                    <iframe id="productVideoIframe" 
                            src="" 
                            title="{{ $product->name }} Video" 
                            allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" 
                            allowfullscreen 
                            style="border: 0; width: 100%; height: 100%;"></iframe>
                </div>
            </div>
        </div>
    </div>
</div>
@endif

{{-- Amazon-Style Clean Fullscreen Product Image Gallery Modal --}}
@if(count($allProductImages) > 0)
<div class="modal fade" id="productLightboxModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-fullscreen m-0 p-0 border-0">
        <div class="modal-content border-0 rounded-0 bg-white">
            
            {{-- Top Header: Perfectly Aligned "Back" Button (Left) and "✕" Close Button (Right) --}}
            <div class="modal-header border-0 bg-white px-3 py-2.5 d-flex align-items-center justify-content-between" style="z-index: 30;">
                <button type="button" 
                        class="btn btn-light bg-white border rounded-pill px-3 py-1 fw-medium text-dark shadow-2xs d-inline-flex align-items-center gap-1.5" 
                        data-bs-dismiss="modal" 
                        aria-label="Back"
                        style="border-color: #d5d9d9 !important; font-size: 0.88rem; min-width: 68px; background: #ffffff !important; box-shadow: 0 1px 2px rgba(0,0,0,0.06);">
                    <i class="bi bi-chevron-left" style="font-size: 0.82rem;"></i>
                    <span>Back</span>
                </button>
                <button type="button" 
                        class="btn btn-light rounded-circle border shadow-2xs d-inline-flex align-items-center justify-content-center p-0" 
                        data-bs-dismiss="modal" 
                        aria-label="Close" 
                        style="width: 34px; height: 34px; border-color: #d5d9d9 !important; background: #ffffff !important;">
                    <i class="bi bi-x-lg text-dark" style="font-size: 0.9rem;"></i>
                </button>
            </div>

            {{-- Centered Main Image Stage --}}
            <div class="modal-body p-2 p-sm-4 d-flex align-items-center justify-content-center position-relative overflow-hidden bg-white" 
                 id="lightbox-stage" 
                 style="touch-action: pan-y pinch-zoom;">
                
                {{-- Previous Arrow (Desktop Only) --}}
                @if(count($allProductImages) > 1)
                <button type="button" onclick="stepLightboxGallery(-1)" aria-label="Previous image"
                        class="btn btn-light shadow-sm rounded-circle position-absolute start-0 ms-3 d-none d-md-flex align-items-center justify-content-center border"
                        style="width: 42px; height: 42px; top: 50%; transform: translateY(-50%); z-index: 20; background: rgba(255, 255, 255, 0.95); border-color: #d5d9d9 !important;">
                    <i class="bi bi-chevron-left text-dark fs-5"></i>
                </button>
                @endif

                {{-- The Product Image --}}
                <div class="d-flex align-items-center justify-content-center w-100 h-100">
                    <img id="lightbox-img" src="{{ $allProductImages[0] ?? '' }}" alt="{{ $product->name }}" 
                         class="img-fluid" 
                         style="max-height: 72vh; max-width: 100%; object-fit: contain; transition: opacity 0.15s ease;">
                </div>

                {{-- Next Arrow (Desktop Only) --}}
                @if(count($allProductImages) > 1)
                <button type="button" onclick="stepLightboxGallery(1)" aria-label="Next image"
                        class="btn btn-light shadow-sm rounded-circle position-absolute end-0 me-3 d-none d-md-flex align-items-center justify-content-center border"
                        style="width: 42px; height: 42px; top: 50%; transform: translateY(-50%); z-index: 20; background: rgba(255, 255, 255, 0.95); border-color: #d5d9d9 !important;">
                    <i class="bi bi-chevron-right text-dark fs-5"></i>
                </button>
                @endif
            </div>

            {{-- Bottom Horizontal Thumbnail Carousel (Amazon-Style) --}}
            @if(count($allProductImages) > 1)
            <div class="modal-footer p-2 bg-white border-top border-light-subtle d-block w-100" 
                 id="lightbox-thumb-container">
                <div class="d-flex align-items-center justify-content-start justify-content-sm-center gap-2 flex-nowrap lightbox-thumb-scroll" 
                     style="overflow-x: auto; -webkit-overflow-scrolling: touch; scrollbar-width: none; padding: 2px 4px;">
                    @foreach($allProductImages as $lIdx => $lUrl)
                        <button type="button" onclick="setLightboxIndex({{ $lIdx }})"
                                class="lightbox-thumb-item rounded-2 p-1 bg-white {{ $lIdx === 0 ? 'active' : '' }}"
                                data-lightbox-thumb="{{ $lIdx }}"
                                aria-label="View thumbnail {{ $lIdx + 1 }}"
                                style="width: 56px; height: 56px; flex-shrink: 0; cursor: pointer; transition: all 0.2s ease; border: {{ $lIdx === 0 ? '2px solid #007185' : '1px solid #d5d9d9' }};">
                            <img src="{{ $lUrl }}" alt="Thumb {{ $lIdx + 1 }}" style="width: 100%; height: 100%; object-fit: contain; border-radius: 2px;">
                        </button>
                    @endforeach
                </div>
            </div>
            @endif

        </div>
    </div>
</div>
@endif
@endpush
@endsection
