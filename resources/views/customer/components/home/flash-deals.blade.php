@if(!empty($flashProducts) && count($flashProducts) > 0)
<section class="flash-sale-section container my-5">
    <div class="card border-0 rounded-4 shadow-sm p-4 bg-gradient-danger text-white position-relative overflow-hidden" style="background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%);">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4 border-bottom border-secondary border-opacity-25 pb-3">
            <div class="d-flex align-items-center gap-3">
                <div class="bg-danger text-white p-3 rounded-circle d-flex align-items-center justify-content-center shadow" style="width: 50px; height: 50px;">
                    <i class="bi bi-lightning-charge-fill fs-4"></i>
                </div>
                <div>
                    <h3 class="fw-extrabold mb-0 text-white">{{ $activeFlashDeal->title ?? ($settings['flash_sale_title'] ?? '⚡ Limited Time Deals') }}</h3>
                    <p class="text-white-50 mb-0 small">{{ $activeFlashDeal->description ?? 'Grab top deals before the timer runs out!' }}</p>
                </div>
            </div>

            <!-- Countdown Timer -->
            <div class="d-flex align-items-center gap-2" id="flashCountdown" data-endtime="{{ isset($activeFlashDeal) && $activeFlashDeal->end_time ? $activeFlashDeal->end_time->toISOString() : ($settings['flash_sale_end_time'] ?? date('Y-m-d\TH:i:s', strtotime('+3 days'))) }}">
                <span class="text-white-50 me-2 small text-uppercase fw-bold">Ends In:</span>
                <div class="bg-danger text-white rounded-3 px-3 py-2 text-center fw-bold shadow-sm">
                    <span id="cd-hours" class="fs-5 d-block leading-none">00</span>
                    <small class="text-uppercase" style="font-size: 10px;">Hrs</small>
                </div>
                <span class="fs-4 text-white-50 fw-bold">:</span>
                <div class="bg-danger text-white rounded-3 px-3 py-2 text-center fw-bold shadow-sm">
                    <span id="cd-mins" class="fs-5 d-block leading-none">00</span>
                    <small class="text-uppercase" style="font-size: 10px;">Min</small>
                </div>
                <span class="fs-4 text-white-50 fw-bold">:</span>
                <div class="bg-danger text-white rounded-3 px-3 py-2 text-center fw-bold shadow-sm">
                    <span id="cd-secs" class="fs-5 d-block leading-none">00</span>
                    <small class="text-uppercase" style="font-size: 10px;">Sec</small>
                </div>
            </div>
        </div>

        <div class="row g-4">
            @foreach($flashProducts as $product)
                <div class="col-6 col-md-4 col-lg-3 d-flex">
                    @include('customer.components.product-card', ['product' => $product])
                </div>
            @endforeach
        </div>
    </div>
</section>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const timerContainer = document.getElementById('flashCountdown');
    if (!timerContainer) return;

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
});
</script>
@endif
