<!-- 1. HERO CAMPAIGN BANNER (RESPONSIVE IMAGE BANNER OR TYPOGRAPHIC FALLBACK) -->
@php
    $heroOffer = $selectedOffer;
    if (!$heroOffer) {
        if ($liveMegaSale && !empty($liveMegaSale->banner_image)) {
            $heroOffer = $liveMegaSale;
        } else {
            $heroOffer = $allOffers->first(fn($o) => !empty($o->banner_image)) ?: $liveMegaSale ?: $allOffers->first();
        }
    }

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
    {{-- CASE 1: FULL HERO GRAPHIC BANNER (UNOBSTRUCTED, ZERO TEXT OVERLAY)--}}
    {{-- ================================================================= --}}
    <div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-3 mb-md-4 position-relative bg-light offers-page-hero">
        <img src="{{ $bannerImgUrl }}" 
             alt="{{ $heroOffer->title }}" 
             class="w-100 d-block object-fit-cover offers-hero-img"
             loading="eager">

        {{-- Floating Countdown Timer Pill (Only if active deal end_time is set) --}}
        @if($heroOffer->end_time)
            <div class="position-absolute bottom-0 end-0 m-2 m-sm-2.5 m-md-3.5 px-2.5 px-sm-3 py-1 py-sm-1.5 rounded-pill shadow-sm d-inline-flex align-items-center gap-1.5 gap-sm-2"
                 style="background: rgba(15, 23, 42, 0.88); backdrop-filter: blur(8px); border: 1px solid rgba(255, 255, 255, 0.22); z-index: 2;">
                <span class="text-warning small fw-bold text-uppercase d-inline-flex align-items-center gap-1" style="font-size: 0.68rem; letter-spacing: 0.5px;">
                    <i class="bi bi-clock-history"></i> <span class="d-none d-sm-inline">Ends in:</span>
                </span>
                <span id="dedicated-sale-timer" data-endtime="{{ $heroOffer->end_time->toISOString() }}" class="font-monospace text-white fw-bold small" style="font-size: 0.76rem;">
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
        <div class="position-absolute top-0 end-0 h-100 d-flex align-items-center pe-3 pe-md-5 opacity-15" style="font-size: 140px; line-height: 1; pointer-events: none; z-index: 0;">
            @if(($heroOffer->type ?? '') === 'FLASH_DEAL') ⚡
            @elseif(($heroOffer->type ?? '') === 'MEGA_SALE') 🔥
            @else 🏷️
            @endif
        </div>
        <div class="position-absolute w-100 h-100 top-0 start-0 pointer-events-none" style="background: radial-gradient(circle at 80% 20%, rgba(255,255,255,0.12) 0%, transparent 50%); z-index: 0;"></div>

        <div class="card-body p-3 p-sm-4 p-md-5 position-relative z-1 w-100 d-flex align-items-center">
            <div class="d-flex flex-column flex-md-row align-items-start align-items-md-center justify-content-between gap-2.5 gap-md-3 w-100">
                <div class="min-w-0">
                    <div class="d-flex align-items-center gap-1.5 gap-sm-2 mb-1.5 flex-wrap">
                        <span class="badge rounded-pill px-2.5 py-1 text-white fw-bold shadow-xs" 
                              style="background: rgba(255, 255, 255, 0.22); backdrop-filter: blur(4px); font-size: 0.72rem; letter-spacing: 0.5px;">
                            <i class="bi bi-tag-fill me-1 text-warning"></i> {{ $discountFormatted }}
                        </span>
                        @if($heroOffer->badge_text)
                            <span class="badge rounded-pill px-2.5 py-1 text-warning fw-bold border border-warning border-opacity-50" 
                                  style="background: rgba(245, 158, 11, 0.15); font-size: 0.7rem;">
                                {{ $heroOffer->badge_text }}
                            </span>
                        @endif
                    </div>
                    
                    <h2 class="fw-extrabold text-white mb-1" style="letter-spacing: -0.02em; font-size: clamp(1.15rem, 3.5vw, 2.1rem); line-height: 1.25;">
                        {{ $heroOffer->title }}
                    </h2>
                    
                    @if(!empty($heroOffer->description))
                        <p class="text-white text-opacity-80 mb-0 small text-truncate-2" style="max-width: 620px; font-size: 0.82rem; line-height: 1.45;">
                            {{ $heroOffer->description }}
                        </p>
                    @endif
                </div>

                {{-- Live Countdown Timer --}}
                @if($heroOffer->end_time)
                <div class="flex-shrink-0 d-inline-flex align-items-center gap-1.5 p-1.5 px-2.5 p-sm-2 px-sm-3 rounded-4 shadow-sm mt-1 mt-md-0" 
                     style="background: rgba(15, 23, 42, 0.8); border: 1px solid rgba(255, 255, 255, 0.18); backdrop-filter: blur(8px);">
                    <div class="text-start me-1.5">
                        <span class="text-white-50 small text-uppercase fw-bold d-block" style="font-size: 0.64rem;">Ends In</span>
                    </div>
                    <div class="d-flex align-items-center gap-1" id="dedicated-sale-timer" data-endtime="{{ $heroOffer->end_time->toISOString() }}">
                        <div class="bg-dark text-warning rounded-2 px-1.5 py-0.5 text-center fw-bold border border-secondary border-opacity-50" style="min-width: 32px;">
                            <span id="dt-days" class="small fw-bold d-block lh-1">00</span>
                            <small class="text-white-50 text-uppercase" style="font-size: 7px;">D</small>
                        </div>
                        <span class="text-warning fw-bold">:</span>
                        <div class="bg-dark text-warning rounded-2 px-1.5 py-0.5 text-center fw-bold border border-secondary border-opacity-50" style="min-width: 32px;">
                            <span id="dt-hours" class="small fw-bold d-block lh-1">00</span>
                            <small class="text-white-50 text-uppercase" style="font-size: 7px;">H</small>
                        </div>
                        <span class="text-warning fw-bold">:</span>
                        <div class="bg-dark text-warning rounded-2 px-1.5 py-0.5 text-center fw-bold border border-secondary border-opacity-50" style="min-width: 32px;">
                            <span id="dt-mins" class="small fw-bold d-block lh-1">00</span>
                            <small class="text-white-50 text-uppercase" style="font-size: 7px;">M</small>
                        </div>
                        <span class="text-warning fw-bold">:</span>
                        <div class="bg-dark text-warning rounded-2 px-1.5 py-0.5 text-center fw-bold border border-secondary border-opacity-50" style="min-width: 32px;">
                            <span id="dt-secs" class="small fw-bold d-block lh-1">00</span>
                            <small class="text-white-50 text-uppercase" style="font-size: 7px;">S</small>
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
        <div class="position-absolute top-0 end-0 h-100 d-flex align-items-center pe-3 pe-md-5 opacity-15" style="font-size: 120px; line-height: 1; pointer-events: none; z-index: 0;">
            ✨
        </div>
        <div class="card-body p-3 p-sm-4 p-md-5 text-center position-relative z-1 w-100 d-flex flex-column align-items-center justify-content-center">
            <span class="badge rounded-pill px-2.5 py-1 fw-bold text-uppercase d-inline-flex align-items-center gap-1 mb-1.5" style="background: rgba(255, 255, 255, 0.15); color: #facc15; font-size: 0.72rem; letter-spacing: 0.05em;">
                <i class="bi bi-tag-fill me-1"></i> Special Deals & Discounts
            </span>
            <h2 class="fw-extrabold mb-1 text-white" style="letter-spacing: -0.02em; font-size: clamp(1.2rem, 3.5vw, 2rem);">Live Deals & Exclusive Offers</h2>
            <p class="text-white text-opacity-80 mb-0 small" style="max-width: 580px; font-size: 0.84rem;">Handpicked discounts and limited-time price drops on top brands.</p>
        </div>
    </div>
@endif

<!-- 2. HIGH-IMPACT MOBILE & DESKTOP OFFERS SHOWCASE (ALL LIVE DEALS DECK) -->
@if($allOffers->count() > 0)
<div class="mb-3 mb-md-4">
    <div class="d-flex align-items-center justify-content-between mb-2 px-0.5">
        <div class="d-flex align-items-center gap-1.5">
            <span class="d-inline-flex align-items-center justify-content-center rounded-circle text-primary" style="width: 22px; height: 22px; background: rgba(99, 102, 241, 0.12); font-size: 0.78rem;">
                <i class="bi bi-lightning-charge-fill"></i>
            </span>
            <h6 class="fw-bold mb-0 text-dark" style="font-size: 0.9rem; letter-spacing: -0.01em;">
                Exclusive Deals Zone
            </h6>
            <span class="badge bg-primary bg-opacity-10 text-primary rounded-pill px-2 py-0.5 fw-bold" style="font-size: 0.65rem;">
                {{ $allOffers->count() }} Live
            </span>
        </div>
        @if(request('offer_id'))
            <a href="{{ route('offers.index') }}" class="offer-ajax-link text-danger small fw-bold text-decoration-none d-inline-flex align-items-center gap-1" style="font-size: 0.75rem;">
                <i class="bi bi-x-circle-fill"></i> <span>Reset Filter</span>
            </a>
        @endif
    </div>

    {{-- Horizontal Scrollable Offer Cards on Mobile, Responsive Grid on Tablet/Desktop --}}
    <div class="d-flex gap-2.5 overflow-x-auto pb-2 pt-0.5 scroll-snap-x no-scrollbar" style="scroll-snap-type: x mandatory; -webkit-overflow-scrolling: touch;">
        {{-- Card 0: "All Deals" Reset Card --}}
        @php $isAllActive = !request('offer_id'); @endphp
        <a href="{{ route('offers.index') }}" 
           class="offer-ajax-link card border-0 rounded-4 text-decoration-none flex-shrink-0 scroll-snap-align-start transition-all hover-elevate p-2.5 d-flex flex-column justify-content-between position-relative overflow-hidden shadow-xs" 
           style="width: 135px; min-width: 135px; height: 105px; background: {{ $isAllActive ? 'linear-gradient(135deg, #1e1b4b 0%, #312e81 60%, #4338ca 100%)' : 'linear-gradient(135deg, #0f172a 0%, #1e293b 100%)' }}; border: {{ $isAllActive ? '2.5px solid #34d399' : '1px solid rgba(255, 255, 255, 0.18)' }} !important; box-shadow: {{ $isAllActive ? '0 4px 15px rgba(52, 211, 153, 0.35)' : 'none' }}; color: #ffffff;"
           data-offer-id=""
           title="Show all offers and products">
            <div class="d-flex align-items-center justify-content-between">
                <span class="badge rounded-circle p-1.5 d-inline-flex align-items-center justify-content-center {{ $isAllActive ? 'bg-warning text-dark' : 'bg-white bg-opacity-10 text-white' }}" style="width: 26px; height: 26px;">
                    <i class="bi bi-stars fs-6"></i>
                </span>
                @if($isAllActive)
                    <span class="badge bg-success text-white rounded-pill px-1.5 py-0.5 fw-bold" style="font-size: 0.6rem;">
                        <i class="bi bi-check2"></i> ACTIVE
                    </span>
                @else
                    <span class="badge rounded-pill px-1.5 py-0.5 fw-semibold" style="background: rgba(255, 255, 255, 0.12); color: #94a3b8; font-size: 0.58rem;">
                        <i class="bi bi-arrow-repeat"></i> ALL
                    </span>
                @endif
            </div>

            <div>
                <div class="fw-bold mb-0 text-truncate text-white" style="font-size: 0.85rem;">
                    All Deals
                </div>
                <div class="small text-white text-opacity-70" style="font-size: 0.68rem;">
                    {{ $isAllActive ? 'Entire Catalog' : 'Show All Items' }}
                </div>
            </div>
        </a>

        {{-- Loop through All Active Offers --}}
        @foreach($allOffers as $offer)
        @php
            $isSelected = request('offer_id') == $offer->id;
            $hasCardImage = !empty($offer->banner_image);
            $cardImgUrl = null;
            if ($hasCardImage) {
                $rawCard = trim($offer->banner_image);
                $cardImgUrl = (str_starts_with($rawCard, 'http://') || str_starts_with($rawCard, 'https://'))
                    ? $rawCard
                    : asset('storage/' . $rawCard);
            }
            $cardTheme = $offer->theme_color ?: '#4f46e5';
        @endphp

        @if($hasCardImage)
            {{-- CASE A: OFFER HAS IMAGE - DISPLAY CLEAN GRAPHIC BANNER CARD WITH ZERO TEXT CLUTTER --}}
            <a href="{{ $isSelected ? route('offers.index') : route('offers.index', ['offer_id' => $offer->id]) }}" 
               class="offer-ajax-link card border-0 rounded-4 text-decoration-none flex-shrink-0 scroll-snap-align-start transition-all hover-elevate position-relative overflow-hidden shadow-xs bg-light" 
               style="width: 200px; min-width: 200px; height: 105px; border: {{ $isSelected ? '3px solid #10b981' : '1px solid #e2e8f0' }} !important; box-shadow: {{ $isSelected ? '0 8px 20px rgba(16, 185, 129, 0.4)' : 'none' }};"
               data-offer-id="{{ $offer->id }}"
               title="{{ $offer->title }}">
                
                <img src="{{ $cardImgUrl }}" alt="{{ $offer->title }}" class="w-100 h-100 object-fit-cover d-block" style="border-radius: inherit;">
                
                @if($isSelected)
                    <div class="position-absolute top-0 end-0 m-1.5 z-2">
                        <span class="badge bg-success text-white rounded-pill px-2 py-0.5 shadow-sm fw-bold d-inline-flex align-items-center gap-1" style="font-size: 0.62rem; backdrop-filter: blur(4px);">
                            <i class="bi bi-check-circle-fill"></i> ACTIVE
                        </span>
                    </div>
                @endif
            </a>

        @else
            {{-- CASE B: OFFER HAS NO IMAGE - CLEAN TYPOGRAPHIC GRADIENT CARD FALLBACK --}}
            @php
                $discountFormatted = $offer->discount_type === 'PERCENTAGE' 
                    ? (floatval($offer->discount_value) == intval($offer->discount_value) ? intval($offer->discount_value) : number_format($offer->discount_value, 1)) . '% OFF'
                    : 'FLAT ₹' . number_format($offer->discount_value) . ' OFF';
                $badgeIcon = match($offer->type) {
                    'FLASH_DEAL' => '⚡',
                    'MEGA_SALE'  => '🔥',
                    default      => '🏷️'
                };
            @endphp
            <a href="{{ $isSelected ? route('offers.index') : route('offers.index', ['offer_id' => $offer->id]) }}" 
               class="offer-ajax-link card border-0 rounded-4 text-decoration-none flex-shrink-0 scroll-snap-align-start transition-all hover-elevate p-2.5 d-flex flex-column justify-content-between position-relative overflow-hidden shadow-xs" 
               style="width: 200px; min-width: 200px; height: 105px; background: linear-gradient(135deg, {{ $cardTheme }} 0%, #1e1b4b 100%); color: #ffffff; border: {{ $isSelected ? '3px solid #10b981' : '1px solid rgba(255,255,255,0.15)' }} !important; box-shadow: {{ $isSelected ? '0 8px 20px rgba(16, 185, 129, 0.35)' : 'none' }};"
               data-offer-id="{{ $offer->id }}"
               title="{{ $offer->title }}">
                
                <div class="d-flex align-items-center justify-content-between position-relative z-1">
                    <span class="badge rounded-pill px-2 py-0.5 fw-bold" style="background: rgba(255, 255, 255, 0.2); backdrop-filter: blur(4px); font-size: 0.62rem;">
                        {{ $badgeIcon }} {{ $offer->badge_text ?: 'SPECIAL DEAL' }}
                    </span>
                    @if($isSelected)
                        <span class="badge rounded-pill px-1.5 py-0.5 fw-bold text-white shadow-xs" style="background: #10b981; font-size: 0.6rem;">
                            <i class="bi bi-check-circle-fill me-0.5"></i> ACTIVE
                        </span>
                    @else
                        <span class="badge bg-warning text-dark rounded-pill px-1.5 py-0.5 fw-bolder font-monospace" style="font-size: 0.65rem;">
                            {{ $discountFormatted }}
                        </span>
                    @endif
                </div>

                <div class="position-relative z-1">
                    <div class="fw-bold text-white text-truncate" style="font-size: 0.86rem;">
                        {{ $offer->title }}
                    </div>
                </div>

                <div class="d-flex align-items-center justify-content-between pt-1 border-top border-white border-opacity-15 position-relative z-1">
                    <span class="small fw-semibold text-white text-opacity-90" style="font-size: 0.68rem;">
                        {{ $isSelected ? 'Viewing Deals' : 'Explore Offer' }} &rarr;
                    </span>
                    <i class="bi {{ $isSelected ? 'bi-check-circle-fill text-success' : 'bi-arrow-right-circle text-white' }}" style="font-size: 0.75rem;"></i>
                </div>
            </a>
        @endif
        @endforeach
    </div>
</div>
@endif

<!-- 3. DEAL PRODUCTS GRID -->
<div id="offers-products-heading" class="d-flex justify-content-between align-items-center mb-2.5 mb-md-3.5 flex-wrap gap-2">
    <div class="d-flex align-items-center gap-2">
        <h4 class="fw-bold mb-0 text-dark" style="letter-spacing: -0.02em; font-size: clamp(1.05rem, 2.5vw, 1.3rem);">
            @if($selectedOffer)
                Products on Offer
            @else
                All Discounted Products
            @endif
        </h4>
        <span class="badge rounded-pill bg-light text-secondary border px-2 py-0.5 fw-semibold" style="font-size: 0.72rem;">
            <strong class="text-dark">{{ number_format($products->total()) }}</strong> items
        </span>
    </div>
    
    @if($selectedOffer)
        <div>
            <a href="{{ route('offers.index') }}" class="offer-ajax-link btn btn-sm btn-outline-secondary rounded-pill px-2.5 py-1 fw-semibold d-inline-flex align-items-center gap-1" style="font-size: 0.74rem;">
                <i class="bi bi-arrow-left"></i> View All Deals
            </a>
        </div>
    @endif
</div>

@if($products->count() > 0)
    <div class="row row-cols-2 row-cols-md-3 row-cols-lg-4 g-2 g-md-3">
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
    <div class="text-center py-5 bg-white rounded-4 shadow-sm my-3 border p-4" style="border-color: #e2e8f0 !important;">
        <div class="rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 56px; height: 56px; background: #f8fafc;">
            <i class="bi bi-bag-x fs-2 text-muted"></i>
        </div>
        <h5 class="fw-bold text-dark mb-1">No products available under this offer right now</h5>
        <p class="text-muted small mb-3">Try selecting another offer card above or explore our entire live catalog!</p>
        <a href="{{ route('offers.index') }}" class="offer-ajax-link btn btn-primary rounded-pill px-4 py-2 fw-bold shadow-sm btn-sm" style="background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%); border: none;">
            View All Live Offers
        </a>
    </div>
@endif

<style>
.offers-page-hero {
    min-height: 240px;
    max-height: 300px;
}
.offers-hero-img {
    height: 280px !important;
    min-height: 240px !important;
    max-height: 300px !important;
    width: 100% !important;
    object-fit: cover !important;
}
.no-scrollbar::-webkit-scrollbar {
    display: none;
}
.no-scrollbar {
    -ms-overflow-style: none;
    scrollbar-width: none;
}
.scroll-snap-x {
    scroll-snap-type: x mandatory;
    -webkit-overflow-scrolling: touch;
}
.scroll-snap-align-start {
    scroll-snap-align: start;
}
.text-truncate-2 {
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}
@media (max-width: 767.98px) {
    .offers-page-hero {
        min-height: 140px;
        max-height: 200px;
    }
    .offers-hero-img {
        height: auto !important;
        min-height: 140px !important;
        max-height: 200px !important;
        aspect-ratio: 16 / 7;
        object-fit: cover !important;
    }
}
</style>
