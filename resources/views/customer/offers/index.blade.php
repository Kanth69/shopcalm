@extends('layouts.customer')

@section('title', 'Exclusive Mega Sales & Bank Offers - ShopCalm')

@section('content')
<div class="container my-3.5 my-md-4 px-3 px-md-4 pb-5">

    <!-- 1. FULL HERO MEGA SALE CAMPAIGN BANNER (RESPONSIVE FOR ALL SCREENS) -->
    @if($liveMegaSale)
    <div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-4 mb-md-5 position-relative text-white" 
         style="background: linear-gradient(135deg, {{ $liveMegaSale->theme_color ?? '#4f46e5' }} 0%, #1e1b4b 60%, #0f172a 100%);">
        
        {{-- Decorative Pattern --}}
        <div class="position-absolute w-100 h-100"
             style="background-image: radial-gradient(circle, rgba(255,255,255,0.08) 1px, transparent 1px); background-size: 24px 24px; top: 0; left: 0; pointer-events: none;"></div>

        <div class="card-body p-3.5 p-sm-4 p-md-5 position-relative z-1">
            <div class="row align-items-center g-3 g-md-4">
                <div class="col-lg-8">
                    {{-- Badge --}}
                    <div class="d-inline-flex align-items-center gap-1.5 px-3 py-1.5 rounded-pill fw-bold text-uppercase mb-2.5 shadow-2xs" 
                         style="background: rgba(245, 158, 11, 0.22); color: #fbbf24; border: 1px solid rgba(251, 191, 36, 0.4); font-size: 0.74rem; letter-spacing: 0.05em;">
                        <i class="bi bi-fire text-warning"></i> {{ $liveMegaSale->badge_text ?? 'MEGA SALE EVENT' }}
                    </div>
                    
                    {{-- Title --}}
                    <h1 class="fw-extrabold text-white mb-2" style="letter-spacing: -0.02em; font-size: clamp(1.4rem, 4vw, 2.3rem); line-height: 1.2;">
                        {{ $liveMegaSale->title }}
                    </h1>
                    
                    {{-- Description --}}
                    <p class="text-white text-opacity-85 mb-3.5 mb-md-4" style="max-width: 620px; font-size: clamp(0.86rem, 2vw, 1rem); line-height: 1.55;">
                        {{ $liveMegaSale->description ?? 'Unbeatable discounts and instant bank offers across all top products! Grab your favorites before the timer runs out.' }}
                    </p>

                    {{-- Live Countdown Timer Blocks --}}
                    @if($liveMegaSale->end_time)
                    <div class="d-inline-flex align-items-center flex-wrap gap-2 p-2.5 p-sm-3 rounded-4 shadow-sm" 
                         style="background: rgba(15, 23, 42, 0.78); border: 1px solid rgba(255, 255, 255, 0.15); backdrop-filter: blur(8px);">
                        <span class="text-white-50 small text-uppercase fw-bold me-1 d-none d-sm-inline" style="font-size: 0.74rem;">
                            <i class="bi bi-clock-history text-warning me-0.5"></i> Offer Ends In:
                        </span>
                        <span class="text-white-50 small text-uppercase fw-bold me-1 d-inline d-sm-none" style="font-size: 0.7rem;">
                            <i class="bi bi-clock-history text-warning me-0.5"></i> Ends In:
                        </span>
                        <div class="d-flex align-items-center gap-1 gap-sm-1.5" id="dedicated-sale-timer" data-endtime="{{ $liveMegaSale->end_time->toISOString() }}">
                            <div class="bg-dark text-warning rounded-2 px-2 py-1 text-center fw-bold shadow-xs border border-secondary border-opacity-50" style="min-width: 40px;">
                                <span id="dt-days" class="fs-6 fw-extrabold d-block lh-1">00</span>
                                <small class="text-white-50 text-uppercase" style="font-size: 8px;">Days</small>
                            </div>
                            <span class="text-warning fw-bold">:</span>
                            <div class="bg-dark text-warning rounded-2 px-2 py-1 text-center fw-bold shadow-xs border border-secondary border-opacity-50" style="min-width: 40px;">
                                <span id="dt-hours" class="fs-6 fw-extrabold d-block lh-1">00</span>
                                <small class="text-white-50 text-uppercase" style="font-size: 8px;">Hrs</small>
                            </div>
                            <span class="text-warning fw-bold">:</span>
                            <div class="bg-dark text-warning rounded-2 px-2 py-1 text-center fw-bold shadow-xs border border-secondary border-opacity-50" style="min-width: 40px;">
                                <span id="dt-mins" class="fs-6 fw-extrabold d-block lh-1">00</span>
                                <small class="text-white-50 text-uppercase" style="font-size: 8px;">Min</small>
                            </div>
                            <span class="text-warning fw-bold">:</span>
                            <div class="bg-dark text-warning rounded-2 px-2 py-1 text-center fw-bold shadow-xs border border-secondary border-opacity-50" style="min-width: 40px;">
                                <span id="dt-secs" class="fs-6 fw-extrabold d-block lh-1">00</span>
                                <small class="text-white-50 text-uppercase" style="font-size: 8px;">Sec</small>
                            </div>
                        </div>
                    </div>
                    @endif
                </div>

                @if($liveMegaSale->banner_image)
                <div class="col-lg-4 text-center mt-3 mt-lg-0">
                    <img src="{{ asset('storage/' . $liveMegaSale->banner_image) }}" alt="{{ $liveMegaSale->title }}" class="img-fluid rounded-4 shadow-lg" style="max-height: 200px; object-fit: cover; border: 2px solid rgba(255,255,255,0.18);">
                </div>
                @endif
            </div>
        </div>
    </div>
    @else
    <div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-4 mb-md-5 position-relative text-white" 
         style="background: linear-gradient(135deg, #0f172a 0%, #1e293b 60%, #312e81 100%);">
        <div class="position-absolute w-100 h-100"
             style="background-image: radial-gradient(circle, rgba(255,255,255,0.06) 1px, transparent 1px); background-size: 24px 24px; top: 0; left: 0; pointer-events: none;"></div>
        
        <div class="card-body p-4 p-md-5 text-center position-relative z-1">
            <div class="d-inline-flex align-items-center justify-content-center mb-2.5 rounded-circle shadow-xs" 
                 style="width: 50px; height: 50px; background: rgba(99, 102, 241, 0.25); border: 2px solid rgba(99,102,241,0.5);">
                <i class="bi bi-tags-fill fs-4 text-warning"></i>
            </div>
            <h3 class="fw-bolder mb-1 text-white" style="letter-spacing: -0.02em;">Mega Deals & Offers Hub</h3>
            <p class="text-white-50 mb-0 mx-auto small" style="max-width: 540px; font-size: 0.88rem;">
                Discover ongoing discounts, seasonal sales, and special offers on all your favorite electronics, gadgets, and apparel!
            </p>
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
                <a href="{{ route('offers.index') }}" class="text-danger small fw-semibold text-decoration-none d-inline-flex align-items-center gap-1" style="font-size: 0.78rem;">
                    <i class="bi bi-arrow-counterclockwise"></i> Show All Deals
                </a>
            @endif
        </div>

        {{-- Swipeable Filter Chips --}}
        <div class="d-flex align-items-center gap-2 overflow-x-auto pb-2 text-nowrap no-scrollbar" style="scrollbar-width: none; -ms-overflow-style: none;">
            {{-- All Deals --}}
            <a href="{{ route('offers.index') }}" 
               class="btn btn-sm rounded-pill px-3.5 py-2 fw-semibold d-inline-flex align-items-center gap-1.5 text-decoration-none shadow-2xs {{ !request('offer_id') ? 'text-white' : 'bg-white border text-dark' }}" 
               style="font-size: 0.8rem; {{ !request('offer_id') ? 'background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%); border: none;' : 'border-color: #e2e8f0 !important;' }}">
                <i class="bi bi-stars {{ !request('offer_id') ? 'text-warning' : 'text-primary' }}"></i>
                <span>All Deals</span>
            </a>

            @foreach($allOffers as $offer)
            @php 
                $isSelected = request('offer_id') == $offer->id; 
                $themeColor = $offer->theme_color ?? '#6366f1';
                $discountFormatted = $offer->discount_type === 'PERCENTAGE' 
                    ? (floatval($offer->discount_value) == intval($offer->discount_value) ? intval($offer->discount_value) : number_format($offer->discount_value, 1)) . '% OFF'
                    : '₹' . number_format($offer->discount_value) . ' OFF';
            @endphp
            <a href="{{ $isSelected ? route('offers.index') : route('offers.index', ['offer_id' => $offer->id]) }}" 
               class="btn btn-sm rounded-pill px-3.5 py-2 fw-semibold d-inline-flex align-items-center gap-1.5 text-decoration-none shadow-2xs {{ $isSelected ? 'text-white' : 'bg-white border text-dark' }}" 
               style="font-size: 0.8rem; {{ $isSelected ? 'background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%); border: none; box-shadow: 0 4px 12px rgba(79, 70, 229, 0.3);' : 'border-color: #e2e8f0 !important;' }}">
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
    <div class="d-flex justify-content-between align-items-center mb-3 mb-md-3.5 flex-wrap gap-2">
        <div>
            <h4 class="fw-bolder mb-0 text-dark" style="letter-spacing: -0.02em; font-size: clamp(1.15rem, 3vw, 1.45rem);">
                @if($selectedOffer)
                    {{ $selectedOffer->title }}
                @else
                    All Discounted Deals
                @endif
            </h4>
        </div>
        
        <div class="d-flex align-items-center gap-2">
            @if($selectedOffer)
                <a href="{{ route('offers.index') }}" class="btn btn-sm btn-outline-danger rounded-pill px-3 py-1 fw-semibold d-inline-flex align-items-center gap-1" style="font-size: 0.76rem;">
                    <i class="bi bi-x-circle"></i> Reset
                </a>
            @endif
            <span class="badge rounded-pill bg-light text-secondary border px-3 py-1.5 fw-semibold" style="font-size: 0.78rem;">
                <strong class="text-dark">{{ number_format($products->total()) }}</strong> items
            </span>
        </div>
    </div>

    @if($products->count() > 0)
        <div class="row row-cols-2 row-cols-md-3 row-cols-lg-4 g-3 g-md-4">
            @foreach($products as $product)
                <div class="col">
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
            <a href="{{ route('offers.index') }}" class="btn btn-primary rounded-pill px-4 py-2.5 fw-bold shadow-sm" style="background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%); border: none;">
                View All Live Offers
            </a>
        </div>
    @endif

</div>

<style>
.offer-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 10px 25px rgba(15, 23, 42, 0.08) !important;
}
</style>

@if($liveMegaSale && $liveMegaSale->end_time)
<script>
document.addEventListener('DOMContentLoaded', function() {
    const timerContainer = document.getElementById('dedicated-sale-timer');
    if (!timerContainer) return;

    const endTime = new Date(timerContainer.dataset.endtime).getTime();

    function updateTimer() {
        const now = new Date().getTime();
        const diff = endTime - now;

        if (diff <= 0) {
            if(document.getElementById('dt-days')) document.getElementById('dt-days').textContent = '00';
            if(document.getElementById('dt-hours')) document.getElementById('dt-hours').textContent = '00';
            if(document.getElementById('dt-mins')) document.getElementById('dt-mins').textContent = '00';
            if(document.getElementById('dt-secs')) document.getElementById('dt-secs').textContent = '00';
            if(document.getElementById('dt-mobile-timer')) document.getElementById('dt-mobile-timer').textContent = '00d 00h 00m';
            return;
        }

        const days = Math.floor(diff / (1000 * 60 * 60 * 24));
        const hours = Math.floor((diff % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
        const minutes = Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60));
        const seconds = Math.floor((diff % (1000 * 60)) / 1000);

        if(document.getElementById('dt-days')) document.getElementById('dt-days').textContent = String(days).padStart(2, '0');
        if(document.getElementById('dt-hours')) document.getElementById('dt-hours').textContent = String(hours).padStart(2, '0');
        if(document.getElementById('dt-mins')) document.getElementById('dt-mins').textContent = String(minutes).padStart(2, '0');
        if(document.getElementById('dt-secs')) document.getElementById('dt-secs').textContent = String(seconds).padStart(2, '0');
        if(document.getElementById('dt-mobile-timer')) {
            document.getElementById('dt-mobile-timer').textContent = `${days}d ${hours}h ${minutes}m ${seconds}s`;
        }
    }

    updateTimer();
    setInterval(updateTimer, 1000);
});
</script>
@endif
@endsection
