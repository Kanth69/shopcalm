<section class="hero-section container px-3 px-md-4 my-2 my-md-4">
    <div class="position-relative">
        <div id="heroCarousel" class="carousel slide carousel-fade shadow rounded-4 overflow-hidden" data-bs-ride="{{ (isset($banners) && $banners->count() > 1) ? 'carousel' : 'false' }}" data-bs-interval="4500" data-bs-pause="hover">

            {{-- Indicators --}}
            @if(isset($banners) && $banners->count() > 1)
                <div class="carousel-indicators mb-2 mb-md-3 gap-1.5 z-3">
                    @foreach($banners as $index => $banner)
                        <button type="button" data-bs-target="#heroCarousel" data-bs-slide-to="{{ $index }}"
                                class="{{ $index === 0 ? 'active' : '' }} border-0 bg-white opacity-50 rounded-pill transition-all hero-indicator"
                                style="width: {{ $index === 0 ? '24px' : '8px' }}; height: 6px;"
                                aria-label="Slide {{ $index + 1 }}"></button>
                    @endforeach
                </div>
            @endif

            <div class="carousel-inner h-100 rounded-4">
                @foreach($banners as $index => $banner)
                    @php
                        $hasDesktop = !empty($banner->desktop_image);
                        $hasMobile  = !empty($banner->mobile_image);
                        $hasImage   = $hasDesktop || $hasMobile;
                        $desktopPath = $hasDesktop ? (str_starts_with($banner->desktop_image, 'http') ? $banner->desktop_image : asset('storage/' . $banner->desktop_image)) : null;
                        $mobilePath  = $hasMobile ? (str_starts_with($banner->mobile_image, 'http') ? $banner->mobile_image : asset('storage/' . $banner->mobile_image)) : $desktopPath;

                        // Pick gradient: custom > type-defaults
                        $typeGradients = [
                            'CAMPAIGN_OFFER'  => 'linear-gradient(135deg, #6d28d9 0%, #0f172a 100%)',
                            'CATEGORY_HEADER' => 'linear-gradient(135deg, #1e40af 0%, #0f172a 100%)',
                            'BRAND_PROMO'     => 'linear-gradient(135deg, #065f46 0%, #0f172a 100%)',
                            'GENERAL_PROMO'   => 'linear-gradient(135deg, #0f172a 0%, #1e293b 100%)',
                        ];
                        $bg = $banner->bg_gradient ?: ($typeGradients[$banner->banner_type ?? 'GENERAL_PROMO'] ?? 'linear-gradient(135deg, #0f172a 0%, #1e293b 100%)');

                        if (($banner->banner_type ?? '') === 'CAMPAIGN_OFFER' && $banner->offer_id) {
                            $bannerLink = route('offers.index') . '?offer_id=' . $banner->offer_id;
                        } else {
                            $bannerLink = $banner->primary_button_link ?? route('shop');
                        }
                    @endphp

                    <div class="carousel-item h-100 {{ $index === 0 ? 'active' : '' }}">
                        <a href="{{ $bannerLink }}" class="text-decoration-none text-white d-block h-100">
                            <div class="w-100 h-100 position-relative d-flex align-items-center hero-slide-item" style="background: {{ $bg }}; cursor: pointer;">
                                @if($hasImage)
                                    {{-- Full Graphic Image Only: Edge-to-edge, zero clutter --}}
                                    <picture class="w-100 h-100 position-absolute" style="inset: 0;">
                                        @if($hasMobile)
                                            <source media="(max-width: 767.98px)" srcset="{{ $mobilePath }}">
                                        @endif
                                        <img src="{{ $desktopPath ?: $mobilePath }}" class="w-100 h-100 object-fit-cover" alt="{{ $banner->title }}">
                                    </picture>
                                @else
                                    {{-- No Image: Vibrant Gradient Card with Title & Button --}}
                                    <div class="position-absolute top-0 end-0 h-100 d-flex align-items-center pe-3 pe-md-5 opacity-10" style="font-size: 140px; line-height: 1; pointer-events: none; z-index: 0;">
                                        @if(($banner->banner_type ?? '') === 'CAMPAIGN_OFFER') 🔥
                                        @elseif($banner->banner_type === 'CATEGORY_HEADER') 🗂️
                                        @elseif($banner->banner_type === 'BRAND_PROMO') 🏅
                                        @else 🛍️
                                        @endif
                                    </div>

                                    <div class="w-100 py-3 py-md-4 z-2 px-3.5 px-md-5">
                                        <div class="col-12 col-md-8 col-lg-7">
                                            @if($banner->subtitle)
                                                <span class="badge bg-primary bg-opacity-90 text-white rounded-pill px-2.5 py-1 mb-2 fw-semibold text-uppercase d-none d-md-inline-block" style="font-size: 10px; letter-spacing: 0.8px;">{{ $banner->subtitle }}</span>
                                            @endif
                                            <h2 class="fw-bolder text-white mb-2 mb-md-3 lh-sm" style="font-size: clamp(1.2rem, 3.5vw, 2.3rem); text-shadow: 0 4px 20px rgba(0,0,0,0.5);">
                                                {{ $banner->title }}
                                            </h2>
                                            @if($banner->primary_button_text)
                                                <div>
                                                    <span class="btn btn-primary px-3.5 px-md-4 py-1.5 py-md-2 rounded-pill shadow-lg fw-bold hover-elevate d-inline-flex align-items-center gap-1.5" style="font-size: 0.85rem;">
                                                        {{ $banner->primary_button_text }} <i class="bi bi-arrow-right"></i>
                                                    </span>
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </a>
                    </div>
                @endforeach
            </div>

            {{-- Prev / Next Nav Buttons (desktop only when multiple banners) --}}
            @if(isset($banners) && $banners->count() > 1)
                <button class="carousel-control-prev d-none d-md-flex align-items-center justify-content-center z-3 ms-2" type="button" data-bs-target="#heroCarousel" data-bs-slide="prev" style="width: 40px; height: 40px; top: 50%; transform: translateY(-50%); border-radius: 50%; background: rgba(0,0,0,0.35); border: 1px solid rgba(255,255,255,0.2);">
                    <i class="bi bi-chevron-left text-white fs-5"></i>
                    <span class="visually-hidden">Previous</span>
                </button>
                <button class="carousel-control-next d-none d-md-flex align-items-center justify-content-center z-3 me-2" type="button" data-bs-target="#heroCarousel" data-bs-slide="next" style="width: 40px; height: 40px; top: 50%; transform: translateY(-50%); border-radius: 50%; background: rgba(0,0,0,0.35); border: 1px solid rgba(255,255,255,0.2);">
                    <i class="bi bi-chevron-right text-white fs-5"></i>
                    <span class="visually-hidden">Next</span>
                </button>
            @endif

        </div>
    </div>
</section>

<style>
    .hero-slide-item { min-height: 200px !important; }
    @media (min-width: 768px) { .hero-slide-item { min-height: 300px !important; } }
    .hero-section .object-fit-cover { object-fit: cover; }
    .carousel-indicators .active.hero-indicator { background-color: var(--bs-primary) !important; opacity: 1 !important; }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const carouselEl = document.getElementById('heroCarousel');
        if (carouselEl && typeof bootstrap !== 'undefined') {
            const bsCarousel = bootstrap.Carousel.getOrCreateInstance(carouselEl, {
                interval: 4500,
                touch: true
            });

            // Smooth indicator expansion on slide
            carouselEl.addEventListener('slide.bs.carousel', function (e) {
                const indicators = carouselEl.querySelectorAll('.hero-indicator');
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
                handleSwipeGesture();
            }, { passive: true });

            function handleSwipeGesture() {
                const diff = touchStartX - touchEndX;
                if (Math.abs(diff) > 40) { // minimum swipe distance threshold
                    if (diff > 0) {
                        bsCarousel.next();
                    } else {
                        bsCarousel.prev();
                    }
                }
            }
        }
    });
</script>