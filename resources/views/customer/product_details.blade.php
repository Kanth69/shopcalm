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
<div class="container my-3 my-md-4 px-3 px-md-4">
    
    {{-- 1. Breadcrumbs --}}
    <nav aria-label="breadcrumb" class="mb-3">
        <ol class="breadcrumb small mb-0" style="font-size: 0.82rem;">
            <li class="breadcrumb-item"><a href="{{ route('home') }}" class="text-decoration-none text-muted">Home</a></li>
            <li class="breadcrumb-item"><a href="{{ route('shop') }}" class="text-decoration-none text-muted">Shop</a></li>
            @if($product->category)
                <li class="breadcrumb-item"><a href="{{ route('category.products', $product->category->slug) }}" class="text-decoration-none text-muted">{{ $product->category->name }}</a></li>
            @endif
            <li class="breadcrumb-item active text-dark fw-medium text-truncate" aria-current="page" style="max-width: 320px;">{{ $product->name }}</li>
        </ol>
    </nav>

    {{-- 2. Main Product Details Row --}}
    <div class="row g-4 g-lg-5">
        
        {{-- Left Column: Sticky Image Gallery --}}
        <div class="col-lg-5 col-xl-5">
            <div class="product-gallery-sticky" style="position: sticky; top: 90px;">
                
                {{-- Main Product Canvas --}}
                <div class="card border-0 rounded-4 overflow-hidden shadow-xs position-relative mb-2.5" 
                     style="background: #ffffff; border: 1px solid #e2e8f0 !important;">
                    
                    {{-- Prominent Discount Badge (Top Left) --}}
                    @php
                        $hasDiscount = (isset($product->sale_price) && (float) $product->sale_price < (float) $product->price) || !empty($product->offer_discount_percentage);
                        $discountPct = $product->offer_discount_percentage ?? ($hasDiscount && (float) $product->price > 0 ? round((((float) $product->price - (float) $product->sale_price) / (float) $product->price) * 100) : 0);
                    @endphp
                    @if($hasDiscount && $discountPct > 0)
                        <div class="position-absolute top-0 start-0 m-3 z-2">
                            <span class="badge bg-danger text-white px-2.5 py-1.5 fw-bold shadow-xs rounded-3" style="font-size: 0.8rem; letter-spacing: 0.3px;">
                                {{ $discountPct }}% OFF
                            </span>
                        </div>
                    @elseif(isset($product->offer_badge))
                        <div class="position-absolute top-0 start-0 m-3 z-2">
                            <span class="badge bg-primary text-white px-2.5 py-1.5 fw-bold shadow-xs rounded-3" style="font-size: 0.8rem;">
                                {{ $product->offer_badge }}
                            </span>
                        </div>
                    @endif

                    {{-- Wishlist Button (Top Right) --}}
                    <div class="position-absolute top-0 end-0 m-3 z-2">
                        <form action="{{ route('wishlist.add') }}" method="POST">
                            @csrf
                            <input type="hidden" name="product_id" value="{{ $product->id }}">
                            <button type="submit" class="btn btn-light shadow-xs rounded-circle p-0 d-flex align-items-center justify-content-center" 
                                    title="Add to Wishlist" style="width: 38px; height: 38px; background: rgba(255,255,255,0.95); border: 1px solid #e2e8f0;">
                                <i class="bi {{ in_array($product->id, $wishlistedProductIds ?? []) ? 'bi-heart-fill text-danger' : 'bi-heart' }}" style="font-size: 1rem;"></i>
                            </button>
                        </form>
                    </div>

                    {{-- Main Image Area --}}
                    <div class="p-3 p-md-4 d-flex align-items-center justify-content-center" style="min-height: 380px; max-height: 480px;">
                        @if($product->main_image)
                            <img src="{{ asset('storage/' . $product->main_image) }}" id="main-product-img" 
                                 class="img-fluid main-pdp-image" alt="{{ $product->name }}" 
                                 style="max-height: 420px; width: auto; object-fit: contain; transition: transform 0.3s ease;">
                        @else
                            <div class="text-muted d-flex flex-column align-items-center justify-content-center py-5">
                                <i class="bi bi-image fs-1 opacity-25"></i>
                                <span class="small mt-2">No image available</span>
                            </div>
                        @endif
                    </div>
                </div>

                {{-- Thumbnail Gallery Strip --}}
                @if($product->galleryImages->count() > 0)
                <div class="d-flex align-items-center gap-2 overflow-x-auto pb-2 no-scrollbar">
                    {{-- Main image thumbnail --}}
                    <div class="gallery-thumb active rounded-3 border overflow-hidden p-1 cursor-pointer flex-shrink-0" 
                         onclick="updateMainImage('{{ asset('storage/' . $product->main_image) }}', this)"
                         style="width: 68px; height: 68px; background: #ffffff; border-color: #cbd5e1 !important;">
                        <img src="{{ asset('storage/' . $product->main_image) }}" class="w-100 h-100" style="object-fit: contain;">
                    </div>
                    @foreach($product->galleryImages as $image)
                    <div class="gallery-thumb rounded-3 border overflow-hidden p-1 cursor-pointer flex-shrink-0" 
                         onclick="updateMainImage('{{ asset('storage/' . $image->image_path) }}', this)"
                         style="width: 68px; height: 68px; background: #ffffff; border-color: #e2e8f0 !important;">
                        <img src="{{ asset('storage/' . $image->image_path) }}" class="w-100 h-100" style="object-fit: contain;">
                    </div>
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
                    $reviewCount = $product->approvedReviews->count();
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

            {{-- 6.5. Product Options (Sizes, Colors, Waist Sizes Matrix) --}}
            @if($product->has_options && !empty($product->option_stocks))
                @php
                    $optionStocks = is_array($product->option_stocks) ? $product->option_stocks : json_decode($product->option_stocks, true);
                    $optionLabel = match(strtolower($product->option_type ?? 'size')) {
                        'waist' => 'Select Waist Size',
                        'color' => 'Select Color',
                        default => 'Select Size',
                    };
                @endphp
                <div class="mb-4 p-3 rounded-4 border bg-white shadow-xs">
                    <div class="d-flex align-items-center justify-content-between mb-2.5">
                        <span class="fw-bold text-dark small" style="font-size: 0.88rem;"><i class="bi bi-tag-fill me-1 text-primary"></i> {{ $optionLabel }}:</span>
                        <span id="selected-option-display" class="badge rounded-pill bg-light text-secondary border px-2.5 py-1 font-monospace fw-bold" style="font-size: 0.72rem;">Please Select</span>
                    </div>
                    <div class="d-flex flex-wrap gap-2" id="option-pills-container">
                        @foreach($optionStocks as $optKey => $optQty)
                            @php
                                $isAvailable = $optQty > 0;
                            @endphp
                            <button type="button" 
                                    class="btn btn-sm rounded-pill px-3 py-1.5 fw-bold option-pill-btn {{ $isAvailable ? 'btn-outline-secondary text-dark border-secondary border-opacity-50' : 'btn-light border text-muted opacity-50' }}"
                                    data-option="{{ $optKey }}"
                                    data-stock="{{ $optQty }}"
                                    {{ !$isAvailable ? 'disabled' : '' }}
                                    onclick="selectProductOption('{{ $optKey }}', {{ $optQty }})">
                                {{ $optKey }}
                                @if($isAvailable)
                                    <span class="badge bg-light text-dark border ms-1 font-monospace" style="font-size: 0.65rem;">{{ $optQty }} left</span>
                                @else
                                    <span class="badge bg-danger text-white rounded-pill ms-1" style="font-size: 0.6rem;">Sold Out</span>
                                @endif
                            </button>
                        @endforeach
                    </div>
                </div>
            @endif

            {{-- 7. Quantity & CTA Purchase Actions (Flipkart / Amazon Iconic Dual Actions) --}}
            @if($product->stock > 0)
            <div class="mb-4">
                {{-- Quantity Stepper Row --}}
                <div class="d-flex align-items-center gap-3 mb-3">
                    <span class="text-muted small fw-semibold" style="font-size: 0.85rem;">Quantity:</span>
                    <div class="input-group shadow-xs rounded-pill border p-0.5" style="background: #ffffff; border-color: #cbd5e1 !important; width: 120px;">
                        <button class="btn btn-sm btn-link text-dark text-decoration-none px-3 py-1" type="button" onclick="changeQty(-1)"><i class="bi bi-dash-lg fw-bold"></i></button>
                        <input type="number" id="qty-input" class="form-control text-center border-0 p-0 fw-bold bg-transparent shadow-none" value="1" min="1" max="{{ $product->stock }}" readonly style="font-size: 0.95rem;">
                        <button class="btn btn-sm btn-link text-dark text-decoration-none px-3 py-1" type="button" onclick="changeQty(1)"><i class="bi bi-plus-lg fw-bold"></i></button>
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
                <div class="row g-2 text-center align-items-center">
                    <div class="col-3 border-end border-light-subtle">
                        <div class="d-flex flex-column align-items-center">
                            <div class="rounded-circle d-flex align-items-center justify-content-center mb-1 shadow-xs" style="width: 40px; height: 40px; background: #ffffff; color: #4f46e5;">
                                <i class="bi bi-lightning-charge-fill fs-5"></i>
                            </div>
                            <span class="fw-bold text-dark" style="font-size: 0.76rem; line-height: 1.2;">Fast Express<br>Dispatch</span>
                        </div>
                    </div>
                    <div class="col-3 border-end border-light-subtle">
                        <div class="d-flex flex-column align-items-center">
                            <div class="rounded-circle d-flex align-items-center justify-content-center mb-1 shadow-xs" style="width: 40px; height: 40px; background: #ffffff; color: #10b981;">
                                <i class="bi bi-truck fs-5"></i>
                            </div>
                            <span class="fw-bold text-dark" style="font-size: 0.76rem; line-height: 1.2;">Free Delivery<br>Orders ₹499+</span>
                        </div>
                    </div>
                    <div class="col-3 border-end border-light-subtle">
                        <div class="d-flex flex-column align-items-center">
                            <div class="rounded-circle d-flex align-items-center justify-content-center mb-1 shadow-xs" style="width: 40px; height: 40px; background: #ffffff; color: #f59e0b;">
                                <i class="bi bi-patch-check-fill fs-5"></i>
                            </div>
                            <span class="fw-bold text-dark" style="font-size: 0.76rem; line-height: 1.2;">100% Genuine<br>Direct Brands</span>
                        </div>
                    </div>
                    <div class="col-3">
                        <div class="d-flex flex-column align-items-center">
                            <div class="rounded-circle d-flex align-items-center justify-content-center mb-1 shadow-xs" style="width: 40px; height: 40px; background: #ffffff; color: #06b6d4;">
                                <i class="bi bi-cash-stack fs-5"></i>
                            </div>
                            <span class="fw-bold text-dark" style="font-size: 0.76rem; line-height: 1.2;">Pay on<br>Delivery</span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- 9. Delivery Options & Pincode Checker (Flipkart Clean Delivery Widget) --}}
            @php
                $activeLocation = app(\App\Services\DeliveryService::class)->getSessionLocation();
            @endphp
            <div class="mb-4 pt-1">
                <div class="d-flex align-items-center gap-2 mb-2">
                    <i class="bi bi-geo-alt-fill text-primary"></i>
                    <span class="fw-bold text-dark" style="font-size: 0.92rem;">Delivery Options</span>
                </div>

                {{-- Clean Inline Pincode Check Form --}}
                <div class="d-flex align-items-center gap-2 mb-2" style="max-width: 380px;">
                    <div class="input-group rounded-3 border p-1" style="background: #ffffff; border-color: #cbd5e1 !important;">
                        <input type="text" id="pdpPincodeInput" maxlength="6" 
                               class="form-control border-0 font-monospace fw-bold ps-2 text-dark shadow-none" 
                               placeholder="Enter 6-digit Pincode" value="{{ $activeLocation['pincode'] ?? '' }}"
                               style="font-size: 0.92rem; letter-spacing: 0.05em;">
                        <button class="btn btn-link text-primary fw-bold text-decoration-none px-3" 
                                style="font-size: 0.86rem;" type="button" id="pdpCheckPincodeBtn">
                            Check
                        </button>
                    </div>
                </div>

                {{-- Live Delivery Checklist Results --}}
                <div id="pdpDeliveryResults" class="{{ $activeLocation ? '' : 'd-none' }}">
                    @if($activeLocation && $activeLocation['is_serviceable'])
                        <div class="d-flex flex-column gap-1.5 mt-2" style="font-size: 0.84rem;">
                            <div class="d-flex align-items-center gap-2">
                                <i class="bi bi-truck text-success fs-6"></i>
                                <span class="text-dark">Delivery to <strong>{{ $activeLocation['location_text'] }}</strong> by <strong class="text-primary">{{ $activeLocation['estimated_delivery'] }}</strong> <span class="text-success fw-semibold">| FREE ₹499+</span></span>
                            </div>
                            <div class="d-flex align-items-center gap-2">
                                <i class="bi bi-cash-stack text-success fs-6"></i>
                                <span class="text-muted">{{ $activeLocation['is_cod_available'] ? 'Cash on Delivery available' : 'Prepaid payments only' }}</span>
                            </div>
                            <div class="d-flex align-items-center gap-2">
                                <i class="bi bi-shield-check text-success fs-6"></i>
                                <span class="text-muted">100% Verified Quality & Direct Brand Warranty</span>
                            </div>
                        </div>
                    @endif
                </div>
            </div>

        </div>
    </div>

    {{-- 10. Specifications & Description Section (Amazon / Flipkart Spec Table Style) --}}
    <div class="card border-0 shadow-xs rounded-4 mt-4 mt-md-5 overflow-hidden" style="background: #ffffff; border: 1px solid #e2e8f0 !important;">
        
        {{-- Section Nav Tabs --}}
        <div class="border-bottom bg-light px-2 px-md-4 pt-2 pt-md-3">
            <ul class="nav nav-tabs border-0 flex-nowrap overflow-x-auto gap-1 gap-md-2 no-scrollbar" id="productDetailTabs" role="tablist" style="-webkit-overflow-scrolling: touch; scrollbar-width: none; -ms-overflow-style: none;">
                <li class="nav-item flex-shrink-0" role="presentation">
                    <button class="nav-link active fw-bold px-3 py-2.5 border-0 bg-transparent text-dark position-relative pdp-tab-btn" 
                            id="tab-desc-btn" data-bs-toggle="tab" data-bs-target="#tab-desc" type="button" role="tab">
                        <i class="bi bi-file-text me-1 text-primary"></i> Product Overview
                    </button>
                </li>
                @if($product->specifications)
                <li class="nav-item flex-shrink-0" role="presentation">
                    <button class="nav-link fw-bold px-3 py-2.5 border-0 bg-transparent text-secondary position-relative pdp-tab-btn" 
                            id="tab-spec-btn" data-bs-toggle="tab" data-bs-target="#tab-spec" type="button" role="tab">
                        <i class="bi bi-list-check me-1 text-info"></i> Technical Specifications
                    </button>
                </li>
                @endif
                <li class="nav-item flex-shrink-0" role="presentation">
                    <button class="nav-link fw-bold px-3 py-2.5 border-0 bg-transparent text-secondary position-relative pdp-tab-btn" 
                            id="tab-rev-btn" data-bs-toggle="tab" data-bs-target="#tab-rev" type="button" role="tab">
                        <i class="bi bi-chat-heart me-1 text-warning"></i> Customer Reviews ({{ $product->approvedReviews->count() }})
                    </button>
                </li>
            </ul>
        </div>

        {{-- Tab Content Panes --}}
        <div class="tab-content p-3 p-md-4.5" id="productDetailTabsContent">
            
            {{-- Tab 1: Description --}}
            <div class="tab-pane fade show active" id="tab-desc" role="tabpanel">
                <div class="prose" style="font-size: 0.9rem; line-height: 1.8; color: #334155;">
                    {!! $product->description !!}
                </div>
            </div>

            {{-- Tab 2: Specifications Table (Clean 2-Column Responsive Table) --}}
            @if($product->specifications)
            <div class="tab-pane fade" id="tab-spec" role="tabpanel">
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
            @endif

            {{-- Tab 3: Customer Reviews (Flipkart Exact Ratings & Reviews Layout) --}}
            <div class="tab-pane fade" id="tab-rev" role="tabpanel">
                <div id="rev-section">
                    {{-- 1. Flipkart Header & Breakdown Banner --}}
                    <div class="p-3 p-md-4 rounded-3 border mb-4" style="background: #ffffff; border-color: #e2e8f0 !important;">
                        <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 pb-3 mb-3 border-bottom border-light-subtle">
                            <h5 class="fw-bold text-dark mb-0" style="font-size: 1.1rem; letter-spacing: -0.01em;">
                                Ratings & Reviews
                            </h5>
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
                                    {{ number_format($product->approvedReviews->count()) }} Ratings & {{ number_format($product->approvedReviews->count()) }} Reviews
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
                                        @php $pct = (int) round($product->ratingPercentage($i)); @endphp
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
                        <div class="p-3.5 rounded-3 border mb-3" style="background: #f8fafc; border-color: #e2e8f0 !important;">
                            @include('customer.components.reviews.write-review-form', ['product' => $product])
                        </div>
                    </div>
                    @endauth

                    {{-- 3. Verified Customer Reviews Stream (Flipkart Style List / Mobile Horizontal Carousel) --}}
                    @if($product->approvedReviews->count() > 1)
                        <div class="d-flex d-md-none align-items-center justify-content-between text-muted small mb-2 px-1">
                            <span style="font-size: 0.76rem;"><i class="bi bi-arrow-left-right me-1 text-primary"></i> Swipe horizontally for all reviews</span>
                            <span class="badge bg-light text-secondary border rounded-pill" style="font-size: 0.72rem;">{{ $product->approvedReviews->count() }} Reviews</span>
                        </div>
                    @endif
                    <div class="review-list bg-white rounded-3 border p-3 p-md-4" style="border-color: #e2e8f0 !important;">
                        @forelse($product->approvedReviews as $review)
                            @include('customer.components.reviews.review-card', ['review' => $review])
                        @empty
                            <div class="text-center py-5 w-100 bg-white rounded-3 border p-4">
                                <i class="bi bi-chat-square-heart display-4 text-muted opacity-25"></i>
                                <h6 class="mt-3 fw-bold text-dark">No Reviews Yet</h6>
                                <p class="text-muted small mb-0">Be the first verified customer to rate and review this product!</p>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>

        </div>
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

</div>

{{-- 📱 12. App-Like Sticky Bottom Bar on Mobile (Flipkart/Amazon Mobile Experience) --}}
@if($product->stock > 0)
<div class="d-block d-md-none position-fixed bottom-0 start-0 end-0 bg-white border-top shadow-lg p-2.5 z-3 pdp-mobile-sticky-bar" 
     style="backdrop-filter: blur(10px); background: rgba(255, 255, 255, 0.98) !important; z-index: 1045;">
    <div class="container-fluid px-2">
        <div class="row g-2 align-items-center">
            {{-- Price Summary on Left --}}
            <div class="col-4">
                <div class="d-flex flex-column">
                    <span class="text-muted" style="font-size: 0.68rem; line-height: 1;">Total Price:</span>
                    <span class="fw-bolder text-dark" style="font-size: 1rem; letter-spacing: -0.3px;">
                        ₹{{ number_format($product->sale_price ?? $product->price, 0) }}
                    </span>
                </div>
            </div>

            {{-- 2 Action Buttons on Right --}}
            <div class="col-8">
                <div class="d-flex gap-1.5">
                    {{-- Mobile Add to Cart --}}
                    <form action="{{ route('cart.add') }}" method="POST" class="flex-fill ajax-cart-form no-loader">
                        @csrf
                        <input type="hidden" name="product_id" value="{{ $product->id }}">
                        <input type="hidden" name="quantity" class="hidden-qty-input" value="1">
                        <button type="submit" class="btn text-white w-100 rounded-3 py-2 fw-bold text-nowrap add-to-cart-btn no-loader shadow-xs" 
                                style="font-size: 0.8rem; background: #ff9f00; border: none;">
                            <i class="bi bi-cart-plus-fill me-1"></i> Cart
                        </button>
                    </form>

                    {{-- Mobile Instant Buy Now --}}
                    <form action="{{ route('cart.add') }}" method="POST" class="flex-fill" id="mobile-buy-now-form">
                        @csrf
                        <input type="hidden" name="product_id" value="{{ $product->id }}">
                        <input type="hidden" name="quantity" class="hidden-qty-input" value="1">
                        <input type="hidden" name="buy_now" value="1">
                        <button type="submit" id="mobile-buy-now-btn" class="btn text-white w-100 rounded-3 py-2 fw-bold text-nowrap shadow-xs" 
                                style="font-size: 0.8rem; background: #fb641b; border: none;">
                            <i class="bi bi-lightning-fill me-1"></i> Buy Now
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endif

<style>
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
</style>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
let currentSelectedOption = '';

function selectProductOption(optKey, optQty) {
    currentSelectedOption = optKey;
    document.querySelectorAll('.option-pill-btn').forEach(btn => {
        btn.classList.remove('btn-primary', 'text-white');
        btn.classList.add('btn-outline-secondary', 'text-dark');
    });
    const clickedBtn = document.querySelector(`.option-pill-btn[data-option="${optKey}"]`);
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
}

function validateOptionSelected(formEl) {
    const hasOptionsContainer = document.getElementById('option-pills-container');
    if (hasOptionsContainer && !currentSelectedOption) {
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                icon: 'info',
                title: 'Please Select an Option',
                text: 'Please select a size or color option before adding to your bag.',
                confirmButtonColor: '#0284c7'
            });
        } else {
            alert('Please select a size or color option before adding to your bag.');
        }
        return false;
    }
    return true;
}

function updateMainImage(src, thumb) {
    const mainImg = document.getElementById('main-product-img');
    if (mainImg) {
        mainImg.style.opacity = '0.4';
        setTimeout(() => {
            mainImg.src = src;
            mainImg.style.opacity = '1';
        }, 120);
    }
    document.querySelectorAll('.gallery-thumb').forEach(t => t.classList.remove('active'));
    thumb.classList.add('active');
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

// Reset Buy Now Buttons on page load/restore
function resetBuyNowBtns() {
    const desktopBtn = document.getElementById('buy-now-btn');
    if (desktopBtn) {
        desktopBtn.disabled = false;
        desktopBtn.innerHTML = '<i class="bi bi-lightning-fill fs-5"></i> <span>Buy Now</span>';
    }
    const mobileBtn = document.getElementById('mobile-buy-now-btn');
    if (mobileBtn) {
        mobileBtn.disabled = false;
        mobileBtn.innerHTML = '<i class="bi bi-lightning-fill me-1"></i> Buy Now';
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
            body: JSON.stringify({ pincode: pincode, save_location: false })
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
                    pdpResults.innerHTML = `
                        <div class="d-flex flex-column gap-1.5 mt-2" style="font-size: 0.84rem;">
                            <div class="d-flex align-items-center gap-2">
                                <i class="bi bi-truck text-success fs-6"></i>
                                <span class="text-dark">Delivery to <strong>${data.location_text}</strong> by <strong class="text-primary">${data.estimated_delivery}</strong> <span class="text-success fw-semibold">| FREE ₹499+</span></span>
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
                        <div class="d-flex align-items-center gap-2 mt-2 p-2 rounded-3 bg-danger bg-opacity-10 text-danger border border-danger border-opacity-25" style="font-size: 0.82rem;">
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
                    <div class="d-flex align-items-center gap-2 mt-2 p-2 rounded-3 bg-danger bg-opacity-10 text-danger border border-danger border-opacity-25" style="font-size: 0.82rem;">
                        <i class="bi bi-exclamation-circle-fill fs-6 flex-shrink-0"></i>
                        <span>Network error. Please try again.</span>
                    </div>`;
            }
        });
    }

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
                .then(r => r.json())
                .then(data => {
                    if (data.success) {
                        window.location.href = data.redirect_url || '{{ route("checkout.index") }}';
                    } else {
                        throw new Error(data.message || 'Could not proceed.');
                    }
                })
                .catch(err => {
                    resetBuyNowBtns();
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({ icon: 'error', title: 'Notice', text: err.message });
                    } else {
                        alert(err.message);
                    }
                });
            });
        }
    }

    bindBuyNow('buy-now-form', 'buy-now-btn');
    bindBuyNow('mobile-buy-now-form', 'mobile-buy-now-btn');
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
</script>
@endpush
@endsection
