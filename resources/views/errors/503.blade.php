@extends('errors.layout')

@section('title', 'Maintenance - ShopCalm')

@section('content')
<div class="error-card">
    <div class="error-code-badge bg-warning bg-opacity-10 text-warning mx-auto">
        <i class="bi bi-tools text-dark"></i>
    </div>
    
    <h1 class="h3 fw-bold mb-2">We're Upgrading ShopCalm</h1>
    <p class="text-muted mb-4" style="font-size: 0.95rem; line-height: 1.6;">
        We are performing brief scheduled system enhancements to bring you a faster and smoother shopping experience. We'll be back online momentarily!
    </p>

    <!-- Auto Refresh Indicator -->
    <div class="d-inline-flex align-items-center gap-2 bg-light border rounded-pill px-3 py-1.5 mb-4 text-secondary small">
        <span class="spinner-grow spinner-grow-sm text-primary" role="status" aria-hidden="true"></span>
        <span id="countdown-text">Auto-refreshing in <strong id="timer-sec">15</strong>s...</span>
    </div>

    <!-- Manual Action -->
    <div>
        <button type="button" onclick="window.location.reload();" class="btn-primary-custom" style="cursor: pointer;">
            <i class="bi bi-arrow-clockwise"></i>
            <span>Check Now</span>
        </button>
    </div>
</div>
@endsection

@section('scripts')
<script>
    (function() {
        let sec = 15;
        const timerEl = document.getElementById('timer-sec');
        const interval = setInterval(function() {
            sec--;
            if (timerEl) timerEl.textContent = sec;
            if (sec <= 0) {
                clearInterval(interval);
                window.location.reload();
            }
        }, 1000);
    })();
</script>
@endsection
