@if(isset($offers) && $offers->isNotEmpty())
<section class="py-5">
    <div class="container px-lg-4">
        <div class="row g-4 justify-content-center">
            @foreach($offers as $offer)
            <div class="col-lg-{{ $offers->count() == 1 ? '12' : ($offers->count() == 2 ? '6' : '4') }}">
                <div class="card border-0 shadow-lg rounded-5 overflow-hidden position-relative promo-bar-card transition-all"
                     style="background: linear-gradient(135deg, #1e3a8a 0%, #3b82f6 100%); min-height: 350px;">

                    {{-- Subtle Glassmorphism Layer --}}
                    <div class="position-absolute top-0 start-0 w-100 h-100 bg-white opacity-10" style="backdrop-filter: blur(8px);"></div>

                    @if($offer->banner_image)
                        <img src="{{ asset('storage/' . $offer->banner_image) }}" class="position-absolute top-0 end-0 h-100 object-fit-cover promo-img" style="width: 50%; mix-blend-mode: overlay; opacity: 0.7;" alt="{{ $offer->name }}">
                    @endif

                    <div class="card-body p-4 p-md-5 position-relative z-1 d-flex flex-column justify-content-center h-100 text-white">
                        <div class="d-flex align-items-center gap-2 mb-3">
                            <span class="badge bg-warning text-dark rounded-pill fw-bold text-uppercase px-3 py-2 shadow-sm animate-pulse">
                                <i class="bi bi-fire me-1"></i> MEGA SALE IS LIVE
                            </span>
                            @if($offer->end_date)
                                <div class="glass-pill px-3 py-1 rounded-pill small fw-medium">
                                    <i class="bi bi-alarm me-1"></i> Ends {{ $offer->end_date->diffForHumans() }}
                                </div>
                            @endif
                        </div>

                        <h2 class="promo-title fw-bolder mb-3 text-white">{{ $offer->name }}</h2>

                        @if($offer->description)
                            <p class="promo-subtitle lead opacity-90 mb-5 d-none d-md-block">{{ Str::limit($offer->description, 140) }}</p>
                        @endif

                        <div class="mt-auto d-flex align-items-center gap-4">
                            <a href="{{ route('collection.show', $offer->slug) }}" class="btn btn-white btn-lg rounded-pill px-5 py-3 fw-bold shadow hover-elevate transition-all group">
                                Grab Offer <i class="bi bi-arrow-right ms-2 transition-all group-hover-translate-x"></i>
                            </a>
                            <div class="d-none d-xl-flex align-items-center gap-2 text-white-50 small fw-bold">
                                <i class="bi bi-truck fs-4"></i> Free Express Delivery
                            </div>
                        </div>
                    </div>

                    <!-- Decorative background shapes -->
                    <div class="position-absolute rounded-circle bg-white opacity-10" style="width: 300px; height: 300px; bottom: -100px; right: -50px; pointer-events: none;"></div>
                    <div class="position-absolute rounded-circle bg-white opacity-10" style="width: 100px; height: 100px; top: 20px; left: 20%; pointer-events: none;"></div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</section>
@else
{{-- Fallback Promo Banner if no offers are managed in the admin panel --}}
<section class="py-5">
    <div class="container px-lg-4">
        <div class="card border-0 shadow-lg rounded-5 overflow-hidden position-relative promo-bar-card transition-all"
             style="background: linear-gradient(135deg, #0f172a 0%, #1e40af 100%); min-height: 350px;">

            <div class="card-body p-4 p-md-5 position-relative z-1 d-flex flex-column flex-lg-row align-items-center justify-content-between gap-5">
                <div class="text-white text-center text-lg-start">
                    <div class="d-flex align-items-center gap-2 mb-3 justify-content-center justify-content-lg-start">
                        <span class="badge bg-danger text-white px-3 py-2 rounded-pill fw-bold text-uppercase shadow-sm animate-pulse">
                            <i class="bi bi-lightning-charge-fill me-1"></i> LIMITED TIME OFFER
                        </span>
                        <div class="glass-pill px-3 py-1 rounded-pill small fw-bold text-white">
                             <i class="bi bi-stars me-1 text-warning"></i> PREMIUM ACCESS
                        </div>
                    </div>

                    <h2 class="promo-title fw-bolder display-3 mb-3 text-white tracking-tight" style="letter-spacing: -2px;">Big Sale! Up to 70% Off</h2>

                    <div class="promo-subtitle lead opacity-90 mb-5">
                        <div class="d-flex flex-wrap gap-3 justify-content-center justify-content-lg-start">
                            <span><i class="bi bi-truck me-1"></i> Free Delivery</span>
                            <span class="opacity-50">|</span>
                            <span><i class="bi bi-credit-card me-1"></i> Extra 10% Bank Off</span>
                            <span class="opacity-50">|</span>
                            <span><i class="bi bi-shield-check me-1"></i> 1 Year Warranty</span>
                        </div>
                    </div>

                    <a href="{{ route('shop') }}" class="btn btn-white btn-lg rounded-pill px-5 py-3 fw-bold shadow hover-elevate transition-all group">
                        Explore Collection <i class="bi bi-arrow-right ms-2 transition-all group-hover-translate-x"></i>
                    </a>
                </div>

                <div class="d-none d-lg-flex position-relative align-items-center justify-content-center">
                    <div class="glass-card p-4 rounded-5 shadow-lg text-center d-flex flex-column align-items-center justify-content-center" style="width: 260px; height: 260px; border: 1px solid rgba(255,255,255,0.2);">
                        <i class="bi bi-gift-fill text-white mb-3" style="font-size: 6rem; filter: drop-shadow(0 0 15px rgba(255,255,255,0.3));"></i>
                        <div class="fw-bold text-white h5 mb-0">Unbox Happiness</div>
                        <div class="text-white-50 small">Premium Picks Only</div>
                    </div>

                    <!-- Decorative glowing orb -->
                    <div class="position-absolute bg-primary rounded-circle opacity-25 blur-30" style="width: 150px; height: 150px; filter: blur(40px);"></div>
                </div>
            </div>

            <!-- Dynamic decorative background particles -->
            <div class="position-absolute rounded-circle bg-white opacity-05" style="width: 400px; height: 400px; top: -150px; left: -100px;"></div>
            <div class="position-absolute rounded-circle bg-white opacity-05" style="width: 300px; height: 300px; bottom: -100px; right: 10%;"></div>
        </div>
    </div>
</section>
@endif

<style>
    .promo-bar-card {
        border: 1px solid rgba(255,255,255,0.1);
        box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25) !important;
    }
    .promo-title {
        font-size: clamp(32px, 6vw, 56px);
        letter-spacing: -2px;
        line-height: 1;
        font-weight: 800 !important;
    }
    .promo-subtitle {
        font-size: clamp(16px, 2.5vw, 20px);
    }
    .glass-pill {
        background: rgba(255, 255, 255, 0.12);
        backdrop-filter: blur(12px);
        border: 1px solid rgba(255,255,255,0.15);
    }
    .glass-card {
        background: rgba(255, 255, 255, 0.08);
        backdrop-filter: blur(20px);
    }
    .btn-white {
        background: #fff;
        color: #1e3a8a;
        border: none;
    }
    .btn-white:hover {
        background: #f8fafc;
        color: #1e40af;
        transform: translateY(-3px) scale(1.02);
    }
    .hover-elevate { transition: all 0.4s cubic-bezier(0.165, 0.84, 0.44, 1); }
    .hover-elevate:hover { transform: translateY(-8px); }
    .group:hover .group-hover-translate-x { transform: translateX(10px); }

    .animate-pulse {
        animation: pulse-glow 2.5s infinite;
    }

    @keyframes pulse-glow {
        0% { transform: scale(1); filter: brightness(1); }
        50% { transform: scale(1.03); filter: brightness(1.2); }
        100% { transform: scale(1); filter: brightness(1); }
    }

    .promo-img {
        mask-image: linear-gradient(to left, black, transparent);
        -webkit-mask-image: linear-gradient(to left, black, transparent);
    }

    .opacity-05 { opacity: 0.05; }
</style>
