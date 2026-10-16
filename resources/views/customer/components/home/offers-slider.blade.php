@if(isset($offers) && $offers->isNotEmpty())
<section class="offers-section container px-3 px-md-4 my-3 my-md-4">
    {{-- Section Header --}}
    <div class="d-flex justify-content-between align-items-center mb-3 mb-md-3">
        <div>
            <div class="mb-1 d-none d-md-block">
                <span class="badge rounded-pill px-3 py-1.5 fw-bold text-uppercase d-inline-flex align-items-center gap-1" style="background: rgba(239, 68, 68, 0.12); color: #ef4444; font-size: 0.72rem; letter-spacing: 0.05em;">
                    <i class="bi bi-tag-fill me-1" style="font-size: 0.75rem;"></i> Special Deals & Discounts
                </span>
            </div>
            <h4 class="fw-bolder mb-0 text-dark" style="letter-spacing: -0.02em; font-size: clamp(1.15rem, 2.5vw, 1.6rem);">Exclusive Offers</h4>
        </div>
        <a href="{{ route('offers.index') }}" class="btn btn-sm btn-light border rounded-pill px-3 py-1.5 fw-semibold text-primary" style="font-size: 0.8rem;">
            <span>View All Offers</span> <i class="bi bi-arrow-right ms-1"></i>
        </a>
    </div>

    {{-- Offers Carousel / Card --}}
    <div class="position-relative">
        <div id="offersCarousel" class="carousel slide shadow rounded-4 overflow-hidden" data-bs-ride="{{ $offers->count() > 1 ? 'carousel' : 'false' }}" data-bs-interval="4500" data-bs-pause="hover">

            {{-- Indicators (only if multiple offers) --}}
            @if($offers->count() > 1)
                <div class="carousel-indicators mb-2 mb-md-3 gap-1.5 z-3">
                    @foreach($offers as $index => $offer)
                        <button type="button" data-bs-target="#offersCarousel" data-bs-slide-to="{{ $index }}"
                                class="{{ $index === 0 ? 'active' : '' }} border-0 bg-white opacity-50 rounded-pill transition-all offer-indicator"
                                style="width: {{ $index === 0 ? '24px' : '8px' }}; height: 6px;"
                                aria-label="Offer Slide {{ $index + 1 }}"></button>
                    @endforeach
                </div>
            @endif

            <div class="carousel-inner rounded-4">
                @foreach($offers as $index => $offer)
                    @php
                        $hasImage = !empty($offer->banner_image);
                        $imgPath  = $hasImage ? (str_starts_with($offer->banner_image, 'http') ? $offer->banner_image : asset('storage/' . $offer->banner_image)) : null;

                        $themeColor = $offer->theme_color ?: '#6366f1';
                        // Rich gradient blending from theme_color into dark indigo / slate
                        $colorGradient = "linear-gradient(135deg, {$themeColor} 0%, #1e1b4b 60%, #0f172a 100%)";

                        $discountBadge = $offer->badge_text;
                        if (!$discountBadge) {
                            $discountBadge = $offer->discount_type === 'PERCENTAGE'
                                ? round($offer->discount_value) . '% OFF'
                                : 'Flat ₹' . round($offer->discount_value) . ' OFF';
                        }
                    @endphp

                    <div class="carousel-item {{ $index === 0 ? 'active' : '' }}">
                        <a href="{{ route('offers.index', ['offer_id' => $offer->id]) }}" class="text-decoration-none text-white d-block">
                            <div class="w-100 position-relative d-flex align-items-center offers-slide-item" style="background: {{ $colorGradient }}; cursor: pointer;">
                                @if($hasImage)
                                    <img src="{{ $imgPath }}" class="w-100 h-100 object-fit-cover opacity-75 position-absolute" alt="{{ $offer->title }}" style="inset: 0;">
                                    <div class="position-absolute top-0 start-0 w-100 h-100 z-1" style="background: linear-gradient(100deg, rgba(15,23,42,0.88) 0%, rgba(15,23,42,0.45) 60%, rgba(15,23,42,0.2) 100%);"></div>
                                @else
                                    {{-- Vibrant Color Thing with Decorative Watermark --}}
                                    <div class="position-absolute top-0 end-0 h-100 d-flex align-items-center pe-3 pe-md-5 opacity-15" style="font-size: 150px; line-height: 1; pointer-events: none; z-index: 0;">
                                        @if(($offer->type ?? '') === 'FLASH_DEAL') ⚡
                                        @elseif(($offer->type ?? '') === 'MEGA_SALE') 🔥
                                        @else 🏷️
                                        @endif
                                    </div>
                                    <div class="position-absolute w-100 h-100 top-0 start-0 pointer-events-none" style="background: radial-gradient(circle at 80% 20%, rgba(255,255,255,0.12) 0%, transparent 50%); z-index: 0;"></div>
                                @endif

                                <div class="w-100 py-3 py-md-4 z-2 px-3 px-md-4 px-lg-5">
                                    <div class="col-12 col-md-8 col-lg-7">
                                        {{-- Badge --}}
                                        <div class="d-flex align-items-center gap-2 mb-2">
                                            <span class="badge bg-warning text-dark rounded-pill px-3 py-1.5 fw-bolder text-uppercase shadow-sm" style="font-size: 11px; letter-spacing: 0.5px;">
                                                <i class="bi bi-lightning-charge-fill me-1"></i> {{ $discountBadge }}
                                            </span>
                                            @if(($offer->type ?? '') === 'FLASH_DEAL')
                                                <span class="badge bg-danger rounded-pill px-2.5 py-1 text-uppercase fw-semibold" style="font-size: 10px;">Flash Deal</span>
                                            @elseif(($offer->type ?? '') === 'MEGA_SALE')
                                                <span class="badge bg-primary rounded-pill px-2.5 py-1 text-uppercase fw-semibold" style="font-size: 10px;">Mega Sale</span>
                                            @endif
                                        </div>

                                        {{-- Title --}}
                                        <h3 class="fw-bolder text-white mb-2 lh-sm" style="font-size: clamp(1.25rem, 3.5vw, 2.3rem); text-shadow: 0 3px 15px rgba(0,0,0,0.45);">
                                            {{ $offer->title }}
                                        </h3>

                                        {{-- Description --}}
                                        @if($offer->description)
                                            <p class="text-white text-opacity-90 mb-3 small d-none d-sm-block lh-base" style="max-width: 550px; text-shadow: 0 2px 6px rgba(0,0,0,0.35);">
                                                {{ Str::limit($offer->description, 130) }}
                                            </p>
                                        @endif

                                        {{-- Action Buttons & Terms --}}
                                        <div class="d-flex align-items-center flex-wrap gap-3 mt-1">
                                            <span class="btn btn-primary px-3.5 px-md-4 py-1.5 py-md-2.5 rounded-pill shadow-lg fw-bold hover-elevate" style="font-size: 0.85rem;">
                                                View Deals <i class="bi bi-arrow-right ms-1"></i>
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </a>
                    </div>
                @endforeach
            </div>

            {{-- Prev / Next Nav Buttons (desktop only when multiple offers) --}}
            @if($offers->count() > 1)
                <button class="carousel-control-prev d-none d-md-flex align-items-center justify-content-center z-3 ms-2" type="button" data-bs-target="#offersCarousel" data-bs-slide="prev" style="width: 40px; height: 40px; top: 50%; transform: translateY(-50%); border-radius: 50%; background: rgba(0,0,0,0.35); border: 1px solid rgba(255,255,255,0.2);">
                    <i class="bi bi-chevron-left text-white fs-5"></i>
                    <span class="visually-hidden">Previous</span>
                </button>
                <button class="carousel-control-next d-none d-md-flex align-items-center justify-content-center z-3 me-2" type="button" data-bs-target="#offersCarousel" data-bs-slide="next" style="width: 40px; height: 40px; top: 50%; transform: translateY(-50%); border-radius: 50%; background: rgba(0,0,0,0.35); border: 1px solid rgba(255,255,255,0.2);">
                    <i class="bi bi-chevron-right text-white fs-5"></i>
                    <span class="visually-hidden">Next</span>
                </button>
            @endif

        </div>
    </div>
</section>

<style>
    .offers-slide-item { min-height: 200px; }
    @media (min-width: 768px) { .offers-slide-item { min-height: 300px; } }
    .offers-section .object-fit-cover { object-fit: cover; }
    .carousel-indicators .active.offer-indicator { background-color: var(--bs-primary) !important; opacity: 1 !important; }
</style>

@if($offers->count() > 1)
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const carouselEl = document.getElementById('offersCarousel');
        if (carouselEl && typeof bootstrap !== 'undefined') {
            const bsCarousel = bootstrap.Carousel.getOrCreateInstance(carouselEl, {
                interval: 4500,
                touch: true
            });

            // Indicator expansion on slide
            carouselEl.addEventListener('slide.bs.carousel', function (e) {
                const indicators = carouselEl.querySelectorAll('.offer-indicator');
                indicators.forEach(ind => ind.style.width = '8px');
                if (indicators[e.to]) indicators[e.to].style.width = '24px';
            });

            // Touch Swipe Handler for Mobile
            let touchStartX = 0;
            let touchEndX = 0;

            carouselEl.addEventListener('touchstart', function (e) {
                touchStartX = e.changedTouches[0].screenX;
            }, { passive: true });

            carouselEl.addEventListener('touchend', function (e) {
                touchEndX = e.changedTouches[0].screenX;
                const diff = touchStartX - touchEndX;
                if (Math.abs(diff) > 40) {
                    if (diff > 0) {
                        bsCarousel.next();
                    } else {
                        bsCarousel.prev();
                    }
                }
            }, { passive: true });
        }
    });
</script>
@endif
@endif
