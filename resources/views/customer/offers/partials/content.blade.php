<!-- 1. HERO CAMPAIGN BANNER (FULL IMAGE IF AVAILABLE, ELSE CLEAN TYPOGRAPHIC CARD) -->
@php
    $heroOffer = $selectedOffer ?: $liveMegaSale;
    $bannerImgUrl = null;
    if ($heroOffer && !empty($heroOffer->banner_image)) {
        $rawBanner = trim($heroOffer->banner_image);
        $bannerImgUrl = (str_starts_with($rawBanner, 'http://') || str_starts_with($rawBanner, 'https://'))
            ? $rawBanner
            : asset('storage/' . $rawBanner);
    }
@endphp

@if($heroOffer && $bannerImgUrl)
    {{-- ================================================================= --}}
    {{-- CASE 1: FULL HERO GRAPHIC BANNER (UNOBSTRUCTED, EDGE-TO-EDGE)    --}}
    {{-- ================================================================= --}}
    <div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-3 mb-md-4 position-relative bg-light offers-page-hero">
        <img src="{{ $bannerImgUrl }}" 
             alt="{{ $heroOffer->title }}" 
             class="w-100 d-block object-fit-cover offers-hero-img"
             loading="eager">

        {{-- Floating Countdown Timer Pill (Only if active deal end_time is set) --}}
        @if($heroOffer->end_time)
            <div class="position-absolute bottom-0 end-0 m-2.5 m-md-3.5 px-3 py-1.5 rounded-pill shadow-sm d-inline-flex align-items-center gap-2"
                 style="background: rgba(15, 23, 42, 0.88); backdrop-filter: blur(8px); border: 1px solid rgba(255, 255, 255, 0.22); z-index: 2;">
                <span class="text-warning small fw-bold text-uppercase d-inline-flex align-items-center gap-1" style="font-size: 0.72rem; letter-spacing: 0.5px;">
                    <i class="bi bi-clock-history"></i> Ends in:
                </span>
                <span id="dedicated-sale-timer" data-endtime="{{ $heroOffer->end_time->toISOString() }}" class="font-monospace text-white fw-bold small" style="font-size: 0.82rem;">
                    <span id="dt-days">00</span>d : <span id="dt-hours">00</span>h : <span id="dt-mins">00</span>m : <span id="dt-secs">00</span>s
                </span>
            </div>
        @endif
    </div>

@elseif($heroOffer)
    {{-- ================================================================= --}}
    {{-- CASE 2: CLEAN COMPACT TYPOGRAPHIC CARD (WHEN NO BANNER IMAGE)    --}}
    {{-- ================================================================= --}}
    @php
        $discountFormatted = $heroOffer->discount_type === 'PERCENTAGE'
            ? intval($heroOffer->discount_value) . '% OFF'
            : 'FLAT ₹' . intval($heroOffer->discount_value) . ' OFF';
        $themeColor = $heroOffer->theme_color ?: '#4f46e5';
    @endphp
    <div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-3 mb-md-4 position-relative text-white offers-page-hero d-flex align-items-center" 
         style="background: linear-gradient(135deg, {{ $themeColor }} 0%, #1e1b4b 65%, #0f172a 100%);">
        
        {{-- Decorative Watermark Icon / Pattern --}}
        <div class="position-absolute top-0 end-0 h-100 d-flex align-items-center pe-3 pe-md-5 opacity-15" style="font-size: 150px; line-height: 1; pointer-events: none; z-index: 0;">
            @if(($heroOffer->type ?? '') === 'FLASH_DEAL') ⚡
            @elseif(($heroOffer->type ?? '') === 'MEGA_SALE') 🔥
            @else 🏷️
            @endif
        </div>
        <div class="position-absolute w-100 h-100 top-0 start-0 pointer-events-none" style="background: radial-gradient(circle at 80% 20%, rgba(255,255,255,0.12) 0%, transparent 50%); z-index: 0;"></div>

        <div class="card-body p-3.5 p-sm-4 p-md-5 position-relative z-1 w-100 d-flex align-items-center">
            <div class="d-flex flex-column flex-md-row align-items-start align-items-md-center justify-content-between gap-3 w-100">
                <div class="min-w-0">
                    <div class="d-flex align-items-center gap-2 mb-2 flex-wrap">
                        <span class="badge rounded-pill px-2.5 py-1 text-white fw-bold shadow-xs" 
                              style="background: rgba(255, 255, 255, 0.22); backdrop-filter: blur(4px); font-size: 0.76rem; letter-spacing: 0.5px;">
                            <i class="bi bi-tag-fill me-1 text-warning"></i> {{ $discountFormatted }}
                        </span>
                        @if($heroOffer->badge_text)
                            <span class="badge rounded-pill px-2.5 py-1 text-warning fw-bold border border-warning border-opacity-50" 
                                  style="background: rgba(245, 158, 11, 0.15); font-size: 0.74rem;">
                                {{ $heroOffer->badge_text }}
                            </span>
                        @endif
                    </div>
                    
                    <h2 class="fw-extrabold text-white mb-1.5" style="letter-spacing: -0.02em; font-size: clamp(1.25rem, 3.5vw, 2.1rem); line-height: 1.25;">
                        {{ $heroOffer->title }}
                    </h2>
                    
                    @if(!empty($heroOffer->description))
                        <p class="text-white text-opacity-80 mb-0 small" style="max-width: 620px; font-size: 0.88rem; line-height: 1.5;">
                            {{ $heroOffer->description }}
                        </p>
                    @endif
                </div>

                {{-- Live Countdown Timer --}}
                @if($heroOffer->end_time)
                <div class="flex-shrink-0 d-inline-flex align-items-center gap-1.5 p-2 px-3 rounded-4 shadow-sm" 
                     style="background: rgba(15, 23, 42, 0.8); border: 1px solid rgba(255, 255, 255, 0.18); backdrop-filter: blur(8px);">
                    <div class="text-start me-2">
                        <span class="text-white-50 small text-uppercase fw-bold d-block" style="font-size: 0.68rem;">Ends In</span>
                    </div>
                    <div class="d-flex align-items-center gap-1" id="dedicated-sale-timer" data-endtime="{{ $heroOffer->end_time->toISOString() }}">
                        <div class="bg-dark text-warning rounded-2 px-2 py-0.5 text-center fw-bold border border-secondary border-opacity-50" style="min-width: 36px;">
                            <span id="dt-days" class="fs-6 fw-bold d-block lh-1">00</span>
                            <small class="text-white-50 text-uppercase" style="font-size: 8px;">D</small>
                        </div>
                        <span class="text-warning fw-bold">:</span>
                        <div class="bg-dark text-warning rounded-2 px-2 py-0.5 text-center fw-bold border border-secondary border-opacity-50" style="min-width: 36px;">
                            <span id="dt-hours" class="fs-6 fw-bold d-block lh-1">00</span>
                            <small class="text-white-50 text-uppercase" style="font-size: 8px;">H</small>
                        </div>
                        <span class="text-warning fw-bold">:</span>
                        <div class="bg-dark text-warning rounded-2 px-2 py-0.5 text-center fw-bold border border-secondary border-opacity-50" style="min-width: 36px;">
                            <span id="dt-mins" class="fs-6 fw-bold d-block lh-1">00</span>
                            <small class="text-white-50 text-uppercase" style="font-size: 8px;">M</small>
                        </div>
                        <span class="text-warning fw-bold">:</span>
                        <div class="bg-dark text-warning rounded-2 px-2 py-0.5 text-center fw-bold border border-secondary border-opacity-50" style="min-width: 36px;">
                            <span id="dt-secs" class="fs-6 fw-bold d-block lh-1">00</span>
                            <small class="text-white-50 text-uppercase" style="font-size: 8px;">S</small>
                        </div>
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>

@else
    {{-- ================================================================= --}}
    {{-- CASE 3: DEFAULT COMPACT HUB HEADER (NO OFFER SELECTED, NO MEGA)  --}}
    {{-- ================================================================= --}}
    <div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-3 mb-md-4 position-relative text-white offers-page-hero d-flex align-items-center justify-content-center" 
         style="background: linear-gradient(135deg, #1e1b4b 0%, #312e81 60%, #4338ca 100%);">
        <div class="position-absolute top-0 end-0 h-100 d-flex align-items-center pe-3 pe-md-5 opacity-15" style="font-size: 150px; line-height: 1; pointer-events: none; z-index: 0;">
            ✨
        </div>
        <div class="card-body p-3.5 p-sm-4 p-md-5 text-center position-relative z-1 w-100 d-flex flex-column align-items-center justify-content-center">
            <span class="badge rounded-pill px-3 py-1.5 fw-bold text-uppercase d-inline-flex align-items-center gap-1 mb-2" style="background: rgba(255, 255, 255, 0.15); color: #facc15; font-size: 0.75rem; letter-spacing: 0.05em;">
                <i class="bi bi-tag-fill me-1"></i> Special Deals & Discounts
            </span>
            <h2 class="fw-extrabold mb-2 text-white" style="letter-spacing: -0.02em; font-size: clamp(1.3rem, 3.5vw, 2.1rem);">Live Deals & Exclusive Offers</h2>
            <p class="text-white text-opacity-80 mb-0 small" style="max-width: 580px; font-size: 0.9rem;">Handpicked discounts and limited-time price drops on top brands.</p>
        </div>
    </div>
@endif

<!-- 2. QUICK DEALS FILTER BAR (SIMPLE, FAST & INTUITIVE) -->
@if($allOffers->count() > 0)
<div class="mb-3 mb-md-4">
    <div class="d-flex align-items-center justify-content-between mb-2">
        <span class="text-muted small fw-bold text-uppercase" style="font-size: 0.72rem; letter-spacing: 0.05em;">
            <i class="bi bi-funnel-fill text-primary me-1"></i> Active Promotions:
        </span>
        @if(request('offer_id'))
            <a href="{{ route('offers.index') }}" class="offer-ajax-link text-danger small fw-semibold text-decoration-none d-inline-flex align-items-center gap-1" style="font-size: 0.78rem;">
                <i class="bi bi-x-circle"></i> Clear Filter
            </a>
        @endif
    </div>

    {{-- Swipeable Filter Chips --}}
    <div class="d-flex align-items-center gap-2 overflow-x-auto pb-1 text-nowrap no-scrollbar" style="scrollbar-width: none; -ms-overflow-style: none;">
        {{-- All Deals --}}
        <a href="{{ route('offers.index') }}" 
           class="offer-ajax-link btn btn-sm rounded-pill px-3 py-1.5 fw-semibold d-inline-flex align-items-center gap-1.5 text-decoration-none shadow-2xs {{ !request('offer_id') ? 'text-white' : 'bg-white border text-dark' }}" 
           style="font-size: 0.78rem; {{ !request('offer_id') ? 'background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%); border: none;' : 'border-color: #e2e8f0 !important;' }}"
           data-offer-id="">
            <i class="bi bi-stars {{ !request('offer_id') ? 'text-warning' : 'text-primary' }}"></i>
            <span>All Deals</span>
        </a>

        @foreach($allOffers as $offer)
        @php 
            $isSelected = request('offer_id') == $offer->id; 
            $discountFormatted = $offer->discount_type === 'PERCENTAGE' 
                ? (floatval($offer->discount_value) == intval($offer->discount_value) ? intval($offer->discount_value) : number_format($offer->discount_value, 1)) . '% OFF'
                : '₹' . number_format($offer->discount_value) . ' OFF';
        @endphp
        <a href="{{ $isSelected ? route('offers.index') : route('offers.index', ['offer_id' => $offer->id]) }}" 
           class="offer-ajax-link btn btn-sm rounded-pill px-3 py-1.5 fw-semibold d-inline-flex align-items-center gap-1.5 text-decoration-none shadow-2xs {{ $isSelected ? 'text-white' : 'bg-white border text-dark' }}" 
           style="font-size: 0.78rem; {{ $isSelected ? 'background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%); border: none; box-shadow: 0 4px 12px rgba(79, 70, 229, 0.25);' : 'border-color: #e2e8f0 !important;' }}"
           data-offer-id="{{ $offer->id }}">
            <span class="badge rounded-pill px-2 py-0.5 fw-extrabold" style="background: {{ $isSelected ? '#34d399' : '#dcfce7' }}; color: {{ $isSelected ? '#064e3b' : '#15803d' }}; font-size: 0.68rem;">
                {{ $discountFormatted }}
            </span>
            <span>{{ $offer->title }}</span>
            @if($isSelected)
                <i class="bi bi-check-circle-fill text-white ms-0.5" style="font-size: 0.75rem;"></i>
            @endif
        </a>
        @endforeach
    </div>
</div>
@endif

<!-- 3. DEAL PRODUCTS GRID -->
<div id="offers-products-heading" class="d-flex justify-content-between align-items-center mb-3 mb-md-4 flex-wrap gap-2">
    <div class="d-flex align-items-center gap-2">
        <h4 class="fw-bold mb-0 text-dark" style="letter-spacing: -0.02em; font-size: clamp(1.1rem, 2.5vw, 1.35rem);">
            @if($selectedOffer)
                Products on Offer
            @else
                All Discounted Products
            @endif
        </h4>
        <span class="badge rounded-pill bg-light text-secondary border px-2.5 py-1 fw-semibold" style="font-size: 0.74rem;">
            <strong class="text-dark">{{ number_format($products->total()) }}</strong> items
        </span>
    </div>
    
    @if($selectedOffer)
        <div>
            <a href="{{ route('offers.index') }}" class="offer-ajax-link btn btn-sm btn-outline-secondary rounded-pill px-3 py-1 fw-semibold d-inline-flex align-items-center gap-1" style="font-size: 0.76rem;">
                <i class="bi bi-arrow-left"></i> View All Deals
            </a>
        </div>
    @endif
</div>

@if($products->count() > 0)
    <div class="row row-cols-2 row-cols-md-3 row-cols-lg-4 g-3 g-md-4">
        @foreach($products as $product)
            <div class="col d-flex align-items-stretch">
                @include('customer.components.product-card', ['product' => $product])
            </div>
        @endforeach
    </div>

    <div class="mt-4 mt-md-5 d-flex justify-content-center">
        {{ $products->links('pagination::bootstrap-5') }}
    </div>
@else
    <div class="text-center py-5 bg-white rounded-4 shadow-sm my-4 border" style="border-color: #e2e8f0 !important;">
        <div class="rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 64px; height: 64px; background: #f8fafc;">
            <i class="bi bi-bag-x fs-1 text-muted"></i>
        </div>
        <h5 class="fw-bold text-dark mb-1">No products available under this offer right now</h5>
        <p class="text-muted small mb-4">Try selecting another offer card above or explore our entire live catalog!</p>
        <a href="{{ route('offers.index') }}" class="offer-ajax-link btn btn-primary rounded-pill px-4 py-2.5 fw-bold shadow-sm" style="background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%); border: none;">
            View All Live Offers
        </a>
    </div>
@endif

<style>
.offers-page-hero {
    height: 300px !important;
    min-height: 300px !important;
    max-height: 300px !important;
}
.offers-hero-img {
    height: 300px !important;
    min-height: 300px !important;
    max-height: 300px !important;
    width: 100% !important;
    object-fit: cover !important;
}
@media (max-width: 767.98px) {
    .offers-page-hero {
        height: 200px !important;
        min-height: 200px !important;
        max-height: 200px !important;
    }
    .offers-hero-img {
        height: 200px !important;
        min-height: 200px !important;
        max-height: 200px !important;
    }
}
</style>