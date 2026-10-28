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
                        $imgPath  = null;
                        if ($hasImage) {
                            $rawImg = trim($offer->banner_image);
                            $imgPath = (str_starts_with($rawImg, 'http://') || str_starts_with($rawImg, 'https://'))
                                ? $rawImg
                                : asset('storage/' . $rawImg);
                        }

                        $themeColor = $offer->theme_color ?: '#6366f1';
                        // Rich gradient blending from theme_color into dark indigo / slate
                        $colorGradient = "linear-gradient(135deg, {$themeColor} 0%, #1e1b4b 60%, #0f172a 100%)";
                    @endphp

                    <div class="carousel-item {{ $index === 0 ? 'active' : '' }}">
                        <a href="{{ route('offers.index', ['offer_id' => $offer->id]) }}" class="text-decoration-none text-white d-block">
                            <div class="w-100 position-relative d-flex align-items-center offers-slide-item" style="background: {{ $colorGradient }}; cursor: pointer;">
                                @if($hasImage)
                                    {{-- Full Graphic Image Only: Edge-to-edge, zero text clutter --}}
                                    <img src="{{ $imgPath }}" class="w-100 h-100 object-fit-cover position-absolute" alt="{{ $offer->title }}" style="inset: 0;">
                                @else
                                    {{-- Vibrant Color Gradient with Decorative Watermark --}}
                                    <div class="position-absolute top-0 end-0 h-100 d-flex align-items-center pe-3 pe-md-5 opacity-15" style="font-size: 150px; line-height: 1; pointer-events: none; z-index: 0;">
                                        @if(($offer->type ?? '') === 'FLASH_DEAL') ⚡
                                        @elseif(($offer->type ?? '') === 'MEGA_SALE') 🔥
                                        @else 🏷️
                                        @endif
                                    </div>
                                    <div class="position-absolute w-100 h-100 top-0 start-0 pointer-events-none" style="background: radial-gradient(circle at 80% 20%, rgba(255,255,255,0.12) 0%, transparent 50%); z-index: 0;"></div>

                                    <div class="w-100 py-4 py-md-5 z-2 px-3.5 px-md-5">
                                        <div class="col-12 col-md-8 col-lg-7">
                                            {{-- Title / Name Only --}}
                                            <h3 class="fw-bolder text-white mb-3 lh-sm" style="font-size: clamp(1.25rem, 3.5vw, 2.3rem); text-shadow: 0 3px 15px rgba(0,0,0,0.65);">
                                                {{ $offer->title }}
                                            </h3>

                                            {{-- Button --}}
                                            <div>
                                                <span class="btn btn-primary px-3.5 px-md-4 py-2 rounded-pill shadow-lg fw-bold hover-elevate d-inline-flex align-items-center gap-1.5" style="font-size: 0.85rem; background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%); border: none;">
                                                    View Deals <i class="bi bi-arrow-right"></i>
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                @endif

                                {{-- Floating Countdown Timer Pill (Only if active deal end_time is set) --}}
                                @if($offer->end_time)
                                    <div class="position-absolute bottom-0 end-0 m-2 m-md-3 px-2.5 px-md-3 py-1 py-md-1.5 rounded-pill shadow-sm d-inline-flex align-items-center gap-1.5 gap-sm-2"
                                         style="background: rgba(15, 23, 42, 0.88); backdrop-filter: blur(8px); border: 1px solid rgba(255, 255, 255, 0.22); z-index: 2;">
                                        <span class="text-warning small fw-bold text-uppercase d-inline-flex align-items-center gap-1" style="font-size: 0.68rem; letter-spacing: 0.5px;">
                                            <i class="bi bi-clock-history"></i> <span class="d-none d-sm-inline">Ends in:</span>
                                        </span>
                                        <span class="home-offer-timer font-monospace text-white fw-bold small" data-endtime="{{ $offer->end_time->toISOString() }}" style="font-size: 0.75rem;">
                                            <span class="ht-days">00</span>d : <span class="ht-hours">00</span>h : <span class="ht-mins">00</span>m : <span class="ht-secs">00</span>s
                                        </span>
                                    </div>
                                @endif
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
    .offers-slide-item {
        min-height: 200px !important;
    }
    @media (max-width: 767.98px) {
        .offers-slide-item {
            min-height: 200px !important;
            height: 200px !important;
            max-height: 200px !important;
        }
        .offers-section .carousel-indicators {
            justify-content: flex-start !important;
            margin-left: 1rem !important;
            margin-right: auto !important;
            margin-bottom: 0.65rem !important;
        }
    }
    @media (min-width: 768px) {
        .offers-slide-item {
            min-height: 300px !important;
            height: 300px !important;
        }
    }
    .offers-section .object-fit-cover { object-fit: cover; }
    .carousel-indicators .active.offer-indicator { background-color: var(--bs-primary) !important; opacity: 1 !important; }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        // Live countdown timer for home offers slider
        function updateHomeOfferTimers() {
            const timerContainers = document.querySelectorAll('.home-offer-timer');
            if (!timerContainers.length) return;
            const now = new Date().getTime();

            timerContainers.forEach(container => {
                const endTimeStr = container.dataset.endtime;
                if (!endTimeStr) return;
                const endTime = new Date(endTimeStr).getTime();
                const diff = endTime - now;

                const daysEl = container.querySelector('.ht-days');
                const hoursEl = container.querySelector('.ht-hours');
                const minsEl = container.querySelector('.ht-mins');
                const secsEl = container.querySelector('.ht-secs');

                if (diff <= 0) {
                    if (daysEl) daysEl.textContent = '00';
                    if (hoursEl) hoursEl.textContent = '00';
                    if (minsEl) minsEl.textContent = '00';
                    if (secsEl) secsEl.textContent = '00';
                    return;
                }

                const days = Math.floor(diff / (1000 * 60 * 60 * 24));
                const hours = Math.floor((diff % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
                const minutes = Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60));
                const seconds = Math.floor((diff % (1000 * 60)) / 1000);

                if (daysEl) daysEl.textContent = String(days).padStart(2, '0');
                if (hoursEl) hoursEl.textContent = String(hours).padStart(2, '0');
                if (minsEl) minsEl.textContent = String(minutes).padStart(2, '0');
                if (secsEl) secsEl.textContent = String(seconds).padStart(2, '0');
            });
        }

        updateHomeOfferTimers();
        setInterval(updateHomeOfferTimers, 1000);

        @if($offers->count() > 1)
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
        @endif
    });
</script>
@endif
