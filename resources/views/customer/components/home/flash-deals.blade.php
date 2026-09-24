@if(!empty($flashProducts) && count($flashProducts) > 0)
@php
    $themeColor = $activeFlashDeal->theme_color ?? '#ef4444';
    $hasBannerImg = !empty($activeFlashDeal->banner_image);
    $bannerUrl = $hasBannerImg ? asset('storage/' . $activeFlashDeal->banner_image) : null;
@endphp
<section class="flash-sale-section container px-2 px-sm-3 px-md-4 my-3 my-md-5">
    <div class="card border-0 rounded-4 shadow-lg p-3 p-sm-4 text-white position-relative overflow-hidden" 
         style="background: {{ $hasBannerImg ? "linear-gradient(135deg, rgba(15, 23, 42, 0.92) 0%, rgba(30, 41, 59, 0.96) 100%), url('{$bannerUrl}') center/cover no-repeat" : "linear-gradient(135deg, {$themeColor}dd 0%, #0f172a 100%)" }}; border: 1.5px solid rgba(255,255,255,0.12) !important;">
        
        {{-- Section Header --}}
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-3 mb-md-4 border-bottom border-white border-opacity-15 pb-3">
            <div class="d-flex align-items-center gap-2.5">
                <div class="text-white rounded-circle d-flex align-items-center justify-content-center shadow-sm flex-shrink-0" 
                     style="width: 42px; height: 42px; background: {{ $themeColor }};">
                    <i class="bi bi-lightning-charge-fill fs-5"></i>
                </div>
                <div class="overflow-hidden">
                    <div class="d-flex align-items-center gap-2 flex-wrap">
                        <h4 class="fw-extrabold mb-0 text-white text-truncate" style="font-size: clamp(1.05rem, 3vw, 1.45rem); letter-spacing: -0.3px;">
                            {{ $activeFlashDeal->title }}
                        </h4>
                        @if($activeFlashDeal->badge_text)
                            <span class="badge rounded-pill px-2.5 py-1 fw-bold text-white shadow-xs" style="background: {{ $themeColor }}; font-size: 0.7rem; letter-spacing: 0.3px;">
                                {{ $activeFlashDeal->badge_text }}
                            </span>
                        @endif
                    </div>
                    @if($activeFlashDeal->description)
                        <p class="text-white-50 mb-0 small text-truncate d-none d-sm-block mt-0.5" style="font-size: 0.78rem; max-width: 500px;">
                            {{ $activeFlashDeal->description }}
                        </p>
                    @endif
                </div>
            </div>

            <div class="d-flex align-items-center justify-content-between justify-content-md-end gap-2.5">
                <!-- Countdown Timer -->
                <div class="d-inline-flex align-items-center gap-1 bg-black bg-opacity-40 px-3 py-1.5 rounded-pill border border-white border-opacity-15 shadow-xs" id="flashCountdown" data-endtime="{{ $activeFlashDeal->end_time ? $activeFlashDeal->end_time->toISOString() : now()->addHours(24)->toISOString() }}">
                    <span class="text-white-50 me-1 small text-uppercase fw-bold d-flex align-items-center" style="font-size: 0.7rem; letter-spacing: 0.4px;">
                        <i class="bi bi-clock-history me-1 text-warning"></i>
                        <span>Ends In:</span>
                    </span>
                    <div class="text-white rounded-2 px-1.5 py-0.5 text-center fw-bold font-monospace shadow-2xs" style="background: rgba(255,255,255,0.18); min-width: 28px;">
                        <span id="cd-hours" class="d-block leading-none fw-extrabold" style="font-size: 0.88rem !important;">00</span>
                    </div>
                    <span class="text-white-50 fw-bold" style="font-size: 0.8rem;">:</span>
                    <div class="text-white rounded-2 px-1.5 py-0.5 text-center fw-bold font-monospace shadow-2xs" style="background: rgba(255,255,255,0.18); min-width: 28px;">
                        <span id="cd-mins" class="d-block leading-none fw-extrabold" style="font-size: 0.88rem !important;">00</span>
                    </div>
                    <span class="text-white-50 fw-bold" style="font-size: 0.8rem;">:</span>
                    <div class="text-white rounded-2 px-1.5 py-0.5 text-center fw-bold font-monospace shadow-2xs" style="background: rgba(255,255,255,0.18); min-width: 28px;">
                        <span id="cd-secs" class="d-block leading-none fw-extrabold" style="font-size: 0.88rem !important;">00</span>
                    </div>
                </div>

                <!-- Navigation Arrow Buttons for Horizontal Scroll -->
                <div class="d-flex align-items-center gap-1 ms-1">
                    <button type="button" id="flashScrollPrev" class="btn btn-sm btn-outline-light rounded-circle p-0 d-flex align-items-center justify-content-center border-opacity-25" 
                            style="width: 32px; height: 32px; backdrop-filter: blur(4px);" title="Previous deals">
                        <i class="bi bi-chevron-left" style="font-size: 0.82rem;"></i>
                    </button>
                    <button type="button" id="flashScrollNext" class="btn btn-sm btn-outline-light rounded-circle p-0 d-flex align-items-center justify-content-center border-opacity-25" 
                            style="width: 32px; height: 32px; backdrop-filter: blur(4px);" title="Next deals">
                        <i class="bi bi-chevron-right" style="font-size: 0.82rem;"></i>
                    </button>
                </div>
            </div>
        </div>

        {{-- Horizontal Scrollable Deals Track --}}
        <div class="flash-deals-track d-flex gap-3 overflow-x-auto pb-2 pt-1" id="flashDealsTrack" 
             style="-webkit-overflow-scrolling: touch; scroll-snap-type: x mandatory; scrollbar-width: thin; scrollbar-color: rgba(255,255,255,0.3) transparent;">
            @foreach($flashProducts as $product)
                <div class="flash-product-item flex-shrink-0" style="scroll-snap-align: start;">
                    @include('customer.components.product-card', ['product' => $product])
                </div>
            @endforeach
        </div>
    </div>
</section>

<style>
    .flash-product-item {
        width: 220px;
        max-width: 220px;
        transition: transform 0.2s ease;
    }
    .flash-product-item:hover {
        transform: translateY(-4px);
    }
    .flash-deals-track::-webkit-scrollbar {
        height: 6px;
    }
    .flash-deals-track::-webkit-scrollbar-track {
        background: rgba(255, 255, 255, 0.08);
        border-radius: 10px;
    }
    .flash-deals-track::-webkit-scrollbar-thumb {
        background: rgba(255, 255, 255, 0.35);
        border-radius: 10px;
    }
    .flash-deals-track::-webkit-scrollbar-thumb:hover {
        background: rgba(255, 255, 255, 0.6);
    }

    @media (max-width: 575.98px) {
        .flash-product-item {
            width: 165px;
            max-width: 165px;
        }
        .flash-deals-track {
            gap: 0.75rem !important;
            padding-bottom: 6px;
        }
    }
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Countdown Timer
    const timerContainer = document.getElementById('flashCountdown');
    if (timerContainer) {
        const endTimeStr = timerContainer.getAttribute('data-endtime');
        const endTime = new Date(endTimeStr).getTime();

        function updateTimer() {
            const now = new Date().getTime();
            const distance = endTime - now;

            if (distance < 0) {
                document.getElementById('cd-hours').textContent = '00';
                document.getElementById('cd-mins').textContent = '00';
                document.getElementById('cd-secs').textContent = '00';
                return;
            }

            const hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
            const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
            const seconds = Math.floor((distance % (1000 * 60)) / 1000);

            document.getElementById('cd-hours').textContent = hours < 10 ? '0' + hours : hours;
            document.getElementById('cd-mins').textContent = minutes < 10 ? '0' + minutes : minutes;
            document.getElementById('cd-secs').textContent = seconds < 10 ? '0' + seconds : seconds;
        }

        updateTimer();
        setInterval(updateTimer, 1000);
    }

    // Horizontal Scroll Buttons
    const track = document.getElementById('flashDealsTrack');
    const prevBtn = document.getElementById('flashScrollPrev');
    const nextBtn = document.getElementById('flashScrollNext');

    if (track && prevBtn && nextBtn) {
        const scrollAmount = 240;
        prevBtn.addEventListener('click', () => {
            track.scrollBy({ left: -scrollAmount, behavior: 'smooth' });
        });
        nextBtn.addEventListener('click', () => {
            track.scrollBy({ left: scrollAmount, behavior: 'smooth' });
        });
    }
});
</script>
@endif
