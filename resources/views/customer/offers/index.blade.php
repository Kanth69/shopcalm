@extends('layouts.customer')

@section('title', 'Exclusive Mega Sales & Bank Offers - ShopCalm')

@section('content')
<div class="container my-4">

    <!-- HERO FESTIVE CAMPAIGN SECTION -->
    @if($liveMegaSale)
    <div class="card border-0 shadow-lg rounded-4 overflow-hidden mb-5 position-relative text-white" style="background: linear-gradient(135deg, {{ $liveMegaSale->theme_color ?? '#6d28d9' }} 0%, #0f172a 100%);">
        <div class="card-body p-4 p-md-5">
            <div class="row align-items-center g-4">
                <div class="col-lg-8">
                    <div class="d-inline-flex align-items-center gap-2 bg-warning text-dark px-3 py-1 rounded-pill fw-bold small text-uppercase mb-3 shadow-sm">
                        <i class="bi bi-fire"></i> {{ $liveMegaSale->badge_text ?? 'MEGA SALE EVENT' }}
                    </div>
                    <h1 class="display-4 fw-bolder text-white mb-2">{{ $liveMegaSale->title }}</h1>
                    <p class="fs-5 text-white-50 mb-4" style="max-width: 650px;">
                        {{ $liveMegaSale->description ?? 'Unbeatable discounts and instant bank offers across all top products!' }}
                    </p>

                    @if($liveMegaSale->end_time)
                    <div class="d-inline-flex align-items-center gap-3 bg-dark bg-opacity-60 border border-warning border-opacity-50 p-3 rounded-4 shadow-sm" style="background-color: rgba(15, 23, 42, 0.8) !important;">
                        <span class="text-warning-emphasis small text-uppercase fw-bold"><i class="bi bi-clock-history text-warning me-1"></i> Offer Ends In:</span>
                        <div class="fs-4 fw-bolder text-warning font-monospace" id="dedicated-sale-timer" data-endtime="{{ $liveMegaSale->end_time->toISOString() }}">
                            Loading Timer...
                        </div>
                    </div>
                    @endif
                </div>

                @if($liveMegaSale->banner_image)
                <div class="col-lg-4 text-center">
                    <img src="{{ asset('storage/' . $liveMegaSale->banner_image) }}" alt="{{ $liveMegaSale->title }}" class="img-fluid rounded-3 shadow-lg" style="max-height: 240px; object-fit: cover;">
                </div>
                @endif
            </div>
        </div>
    </div>
    @else
    <div class="bg-gradient text-white p-5 rounded-4 shadow-sm mb-5 text-center" style="background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%);">
        <h2 class="fw-bold mb-2"><i class="bi bi-tags-fill text-warning me-2"></i> ShopCalm Sales & Offers Hub</h2>
        <p class="text-white-50 mb-0">Discover ongoing discounts, seasonal sales, and special offers on your favorite products!</p>
    </div>
    @endif

    <!-- ACTIVE OFFERS LIST & CLICKABLE FILTERS -->
    @if($allOffers->count() > 0)
    <div class="mb-5">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h4 class="fw-bold text-dark mb-0"><i class="bi bi-ticket-detailed text-primary me-2"></i> Select an Offer to View Products</h4>
            @if(request('offer_id'))
                <a href="{{ route('offers.index') }}" class="btn btn-sm btn-outline-secondary rounded-pill px-3">
                    <i class="bi bi-x-circle me-1"></i> Show All Offers
                </a>
            @endif
        </div>

        <div class="row g-3">
            @foreach($allOffers as $offer)
            @php $isSelected = request('offer_id') == $offer->id; @endphp
            <div class="col-md-6 col-lg-4">
                <a href="{{ $isSelected ? route('offers.index') : route('offers.index', ['offer_id' => $offer->id]) }}" class="text-decoration-none d-block h-100">
                    <div class="card h-100 border-0 shadow-sm rounded-4 overflow-hidden transition-all hover-elevate {{ $isSelected ? 'ring-2 ring-primary shadow-lg border-2 border-primary' : '' }}" style="border-left: 6px solid {{ $offer->theme_color ?? '#2563eb' }} !important; background-color: {{ $isSelected ? '#f8fafc' : '#ffffff' }};">
                        <div class="card-body p-3.5">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <span class="badge text-white px-2.5 py-1 rounded-pill small fw-bold" style="background-color: {{ $offer->theme_color ?? '#2563eb' }};">
                                    {{ $offer->badge_text ?? 'OFFER' }}
                                </span>
                                <span class="fw-bold text-success fs-5">
                                    {{ $offer->discount_type === 'PERCENTAGE' ? $offer->discount_value.'%' : '₹'.number_format($offer->discount_value) }} OFF
                                </span>
                            </div>
                            <h6 class="fw-bold text-dark mb-1">{{ $offer->title }}</h6>
                            @if($offer->description)
                                <p class="text-secondary small mb-3 text-truncate">{{ $offer->description }}</p>
                            @endif
                            <div class="d-flex justify-content-between align-items-center pt-2 border-top small">
                                <span class="text-muted"><i class="bi bi-calendar-event me-1"></i> {{ $offer->end_time ? $offer->end_time->format('d M Y') : 'Active' }}</span>
                                <span class="fw-bold {{ $isSelected ? 'text-primary' : 'text-secondary' }}">
                                    @if($isSelected)
                                        <i class="bi bi-check-circle-fill me-1"></i> Active Filter
                                    @else
                                        Click to View <i class="bi bi-arrow-right small"></i>
                                    @endif
                                </span>
                            </div>
                        </div>
                    </div>
                </a>
            </div>
            @endforeach
        </div>
    </div>
    @endif

    <!-- DEAL PRODUCTS GRID -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="fw-bold text-dark mb-0">
            <i class="bi bi-fire text-warning me-2"></i> 
            @if($selectedOffer)
                Products for <span class="text-primary">{{ $selectedOffer->title }}</span> ({{ number_format($products->total()) }})
            @else
                Products on Sale ({{ number_format($products->total()) }})
            @endif
        </h4>
    </div>

    @if($products->count() > 0)
        <div class="row row-cols-2 row-cols-md-3 row-cols-lg-4 g-3 g-md-4">
            @foreach($products as $product)
                <div class="col">
                    @include('customer.components.product-card', ['product' => $product])
                </div>
            @endforeach
        </div>

        <div class="mt-4">
            {{ $products->links('pagination::bootstrap-5') }}
        </div>
    @else
        <div class="text-center py-5 bg-white rounded-4 shadow-sm my-4">
            <i class="bi bi-bag-x fs-1 text-muted d-block mb-3"></i>
            <h5 class="fw-bold text-dark">No products available under this offer right now</h5>
            <p class="text-muted small">Try selecting another offer card above or view all deals!</p>
            <a href="{{ route('offers.index') }}" class="btn btn-primary rounded-pill px-4 fw-bold">View All Live Offers</a>
        </div>
    @endif

</div>

@if($liveMegaSale && $liveMegaSale->end_time)
<script>
document.addEventListener('DOMContentLoaded', function() {
    const timerEl = document.getElementById('dedicated-sale-timer');
    if (!timerEl) return;

    const endTime = new Date(timerEl.dataset.endtime).getTime();

    function updateTimer() {
        const now = new Date().getTime();
        const diff = endTime - now;

        if (diff <= 0) {
            timerEl.textContent = "Ends Soon";
            return;
        }

        const days = Math.floor(diff / (1000 * 60 * 60 * 24));
        const hours = Math.floor((diff % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
        const minutes = Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60));
        const seconds = Math.floor((diff % (1000 * 60)) / 1000);

        let str = "";
        if (days > 0) str += `${days}d : `;
        str += `${String(hours).padStart(2, '0')}h : ${String(minutes).padStart(2, '0')}m : ${String(seconds).padStart(2, '0')}s`;
        timerEl.textContent = str;
    }

    updateTimer();
    setInterval(updateTimer, 1000);
});
</script>
@endif
@endsection
