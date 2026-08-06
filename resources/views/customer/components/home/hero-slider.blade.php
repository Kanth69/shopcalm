<section class="hero-section container-fluid px-3 px-md-4 my-4">
    <div class="row g-3 align-items-stretch" style="min-height: 460px;">

        {{-- Left: Main Carousel --}}
        <div class="col-12 col-lg-8">
            <div id="heroCarousel" class="carousel slide carousel-fade h-100 shadow rounded-4 overflow-hidden" data-bs-ride="carousel" data-bs-interval="4500" data-bs-pause="hover" style="min-height: 340px;">

                {{-- Indicators --}}
                <div class="carousel-indicators mb-3 gap-2 z-3">
                    @foreach($banners as $index => $banner)
                        <button type="button" data-bs-target="#heroCarousel" data-bs-slide-to="{{ $index }}"
                                class="{{ $index === 0 ? 'active' : '' }} border-0 bg-white opacity-50 rounded-pill transition-all hero-indicator"
                                style="width: {{ $index === 0 ? '30px' : '10px' }}; height: 8px;"
                                aria-label="Slide {{ $index + 1 }}"></button>
                    @endforeach
                </div>

                <div class="carousel-inner h-100 rounded-4">
                    @foreach($banners as $index => $banner)
                        <div class="carousel-item h-100 {{ $index === 0 ? 'active' : '' }}" style="min-height: 340px;">
                            @php
                                $hasImage = !empty($banner->desktop_image);
                                $imgPath  = $hasImage ? (str_starts_with($banner->desktop_image, 'http') ? $banner->desktop_image : asset('storage/' . $banner->desktop_image)) : null;

                                // Pick gradient: custom > type-defaults
                                $typeGradients = [
                                    'CAMPAIGN_OFFER'  => 'linear-gradient(135deg, #6d28d9 0%, #0f172a 100%)',
                                    'CATEGORY_HEADER' => 'linear-gradient(135deg, #1e40af 0%, #0f172a 100%)',
                                    'BRAND_PROMO'     => 'linear-gradient(135deg, #065f46 0%, #0f172a 100%)',
                                    'GENERAL_PROMO'   => 'linear-gradient(135deg, #0f172a 0%, #1e293b 100%)',
                                ];
                                $bg = $banner->bg_gradient ?: ($typeGradients[$banner->banner_type ?? 'GENERAL_PROMO'] ?? 'linear-gradient(135deg, #0f172a 0%, #1e293b 100%)');
                            @endphp

                            <div class="w-100 h-100 position-relative" style="min-height: 340px; background: {{ $bg }};">
                                @if($hasImage)
                                    <img src="{{ $imgPath }}" class="w-100 h-100 object-fit-cover opacity-75" alt="{{ $banner->title }}" style="position: absolute; inset: 0;">
                                    <div class="position-absolute top-0 start-0 w-100 h-100 z-1" style="background: linear-gradient(100deg, rgba(15,23,42,0.82) 0%, rgba(15,23,42,0.28) 65%, transparent 100%);"></div>
                                @else
                                    {{-- No image: decorative pattern --}}
                                    <div class="position-absolute top-0 end-0 h-100 d-flex align-items-center pe-5 opacity-10" style="font-size: 220px; line-height: 1; pointer-events: none; z-index: 0;">
                                        @if(($banner->banner_type ?? '') === 'CAMPAIGN_OFFER') 🔥
                                        @elseif($banner->banner_type === 'CATEGORY_HEADER') 🗂️
                                        @elseif($banner->banner_type === 'BRAND_PROMO') 🏅
                                        @else 🛍️
                                        @endif
                                    </div>
                                @endif

                                <div class="position-absolute top-0 start-0 w-100 h-100 d-flex align-items-center z-2 px-4 px-lg-5">
                                    <div class="col-md-7 col-lg-6">
                                        @if($banner->subtitle)
                                            <span class="badge bg-primary bg-opacity-90 text-white rounded-pill px-3 py-2 mb-3 fw-semibold text-uppercase" style="font-size: 11px; letter-spacing: 1px;">{{ $banner->subtitle }}</span>
                                        @endif
                                        <h1 class="display-4 fw-bolder text-white mb-4 lh-sm" style="text-shadow: 0 4px 24px rgba(0,0,0,0.4);">
                                            {{ $banner->title }}
                                        </h1>
                                        @if($banner->primary_button_text)
                                            @php
                                                if (($banner->banner_type ?? '') === 'CAMPAIGN_OFFER' && $banner->offer_id) {
                                                    $bannerLink = route('offers.index') . '?offer_id=' . $banner->offer_id;
                                                } else {
                                                    $bannerLink = $banner->primary_button_link ?? route('shop');
                                                }
                                            @endphp
                                            <a href="{{ $bannerLink }}" class="btn btn-primary btn-lg px-5 py-3 rounded-pill shadow-lg fw-bold me-2 hover-elevate">
                                                {{ $banner->primary_button_text }} <i class="bi bi-arrow-right ms-1"></i>
                                            </a>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                @if($banners->count() > 1)
                    <button class="carousel-control-prev hero-ctrl" type="button" data-bs-target="#heroCarousel" data-bs-slide="prev">
                        <div class="hero-ctrl-btn shadow"><i class="bi bi-chevron-left"></i></div>
                    </button>
                    <button class="carousel-control-next hero-ctrl" type="button" data-bs-target="#heroCarousel" data-bs-slide="next">
                        <div class="hero-ctrl-btn shadow"><i class="bi bi-chevron-right"></i></div>
                    </button>
                @endif
            </div>
        </div>

        {{-- Right: Promo Side Cards --}}
        <div class="col-12 col-lg-4 d-none d-lg-flex flex-column gap-3">
            {{-- Card 1: Upcoming Deals --}}
            @php
                $bgStyle = "background: linear-gradient(135deg, #1e40af 0%, #3b82f6 100%);";
                if (isset($upcomingDeal) && $upcomingDeal && $upcomingDeal->banner_image) {
                    $bgUrl = asset('storage/' . $upcomingDeal->banner_image);
                    $bgStyle = "background: linear-gradient(rgba(30, 64, 175, 0.7), rgba(59, 130, 246, 0.8)), url('{$bgUrl}') center/cover no-repeat;";
                }
            @endphp
            <div class="flex-fill rounded-4 overflow-hidden position-relative shadow-sm d-flex flex-column p-4" style="{{ $bgStyle }} min-height: 200px;">
                <div class="position-absolute top-0 end-0 opacity-10" style="font-size: 120px; line-height: 1; transform: translate(15px, -15px);">🛍️</div>
                
                {{-- Top Left Header --}}
                <div class="z-2 position-relative mb-auto">
                    <span class="badge bg-white text-primary fw-bold mb-2 px-2 py-1 rounded-pill" style="font-size: 11px; letter-spacing: 0.5px;">UPCOMING DEALS</span>
                </div>

                {{-- Bottom Content --}}
                <div class="z-2 position-relative w-100">
                    @if(isset($upcomingDeal) && $upcomingDeal)
                        <h3 class="fw-bolder text-white mb-1" style="text-shadow: 0 2px 4px rgba(0,0,0,0.2);">{{ $upcomingDeal->title }}</h3>
                        
                        @php
                            $discountText = $upcomingDeal->discount_type === 'PERCENTAGE' 
                                ? "Up to " . round($upcomingDeal->discount_value) . "% OFF" 
                                : "Flat ₹" . round($upcomingDeal->discount_value) . " OFF";
                        @endphp
                        <span class="badge bg-warning text-dark fw-bolder mb-2 px-3 py-1 rounded-pill" style="font-size: 12px; letter-spacing: 0.5px;">
                            {{ $discountText }}
                        </span>
                        
                        <div class="text-white text-opacity-90 small fw-semibold d-flex align-items-center">
                            <i class="bi bi-calendar-event me-2 fs-6"></i>
                            Starts: {{ \Carbon\Carbon::parse($upcomingDeal->start_time)->format('M d, h:i A') }}
                        </div>
                    @else
                        <h4 class="fw-bolder text-white mb-2">More Deals Coming</h4>
                        <p class="text-white text-opacity-75 small mb-0">Stay tuned for exciting new offers curated just for you.</p>
                    @endif
                </div>
            </div>

            {{-- Card 2: New Arrivals --}}
            <div class="flex-fill rounded-4 overflow-hidden position-relative shadow-sm d-flex align-items-end" style="background: linear-gradient(135deg, #065f46 0%, #10b981 100%); min-height: 200px;">
                <div class="position-absolute top-0 end-0 opacity-10" style="font-size: 120px; line-height: 1; transform: translate(15px, -15px);">✨</div>
                <div class="p-4 z-2 position-relative">
                    <span class="badge bg-light text-dark fw-bold mb-2 px-2 py-1 rounded-pill" style="font-size: 11px;">NEW</span>
                    <h5 class="fw-bolder text-white mb-1">Fresh Arrivals</h5>
                    <p class="text-white text-opacity-75 small mb-3">Just dropped this week</p>
                    <a href="{{ route('shop', ['sort' => 'newest']) }}" class="btn btn-sm btn-light rounded-pill px-3 fw-semibold hover-elevate">
                        Shop New <i class="bi bi-arrow-right ms-1"></i>
                    </a>
                </div>
            </div>
        </div>

    </div>
</section>

<style>
    .hero-section .object-fit-cover { object-fit: cover; }
    .hero-ctrl { width: 72px; opacity: 0; transition: opacity 0.3s ease; z-index: 5; }
    #heroCarousel:hover .hero-ctrl { opacity: 1; }
    .hero-ctrl-btn {
        width: 44px; height: 44px; background: rgba(255,255,255,0.88);
        color: #1e293b; border-radius: 50%; display: flex; align-items: center;
        justify-content: center; font-size: 1.3rem; transition: all 0.2s ease; backdrop-filter: blur(6px);
    }
    .hero-ctrl-btn:hover { background: #fff; color: var(--bs-primary); transform: scale(1.1); }
    .carousel-indicators .active.hero-indicator { background-color: var(--bs-primary) !important; opacity: 1 !important; }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const carousel = document.getElementById('heroCarousel');
        if (carousel) {
            carousel.addEventListener('slide.bs.carousel', function (e) {
                const indicators = carousel.querySelectorAll('.hero-indicator');
                indicators.forEach(ind => ind.style.width = '10px');
                if (indicators[e.to]) indicators[e.to].style.width = '30px';
            });
        }
    });
</script>
