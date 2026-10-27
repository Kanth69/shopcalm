<div id="shopcalm-offline-banner" 
     class="position-fixed top-0 start-50 translate-middle-x px-3.5 py-2 shadow-lg rounded-pill d-none align-items-center gap-2 text-white" 
     style="z-index: 1099; margin-top: 14px; background: rgba(15, 23, 42, 0.95); backdrop-filter: blur(10px); font-size: 0.86rem; font-weight: 500; transition: all 0.3s ease; pointer-events: none;">
    <i id="shopcalm-offline-icon" class="bi bi-wifi-off text-warning fs-6"></i>
    <span id="shopcalm-offline-msg">You are offline. Please check your connection.</span>
</div>

<script>
(function() {
    const banner = document.getElementById('shopcalm-offline-banner');
    const icon = document.getElementById('shopcalm-offline-icon');
    const msg = document.getElementById('shopcalm-offline-msg');
    if (!banner) return;

    let hideTimer = null;

    function handleOnline() {
        if (hideTimer) clearTimeout(hideTimer);
        banner.classList.remove('d-none');
        banner.classList.add('d-flex');
        banner.style.background = 'rgba(16, 185, 129, 0.95)';
        icon.className = 'bi bi-check-circle-fill text-white fs-6';
        msg.textContent = 'Back online!';
        hideTimer = setTimeout(() => {
            banner.classList.remove('d-flex');
            banner.classList.add('d-none');
        }, 2400);
    }

    function handleOffline() {
        if (hideTimer) clearTimeout(hideTimer);
        banner.classList.remove('d-none');
        banner.classList.add('d-flex');
        banner.style.background = 'rgba(15, 23, 42, 0.95)';
        icon.className = 'bi bi-wifi-off text-warning fs-6';
        msg.textContent = 'You are offline. Please check your connection.';
    }

    window.addEventListener('online', handleOnline);
    window.addEventListener('offline', handleOffline);

    if (!navigator.onLine) {
        handleOffline();
    }
})();
</script>
