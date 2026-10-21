@extends('layouts.customer')

@section('title', ($selectedOffer ? $selectedOffer->title . ' - ' : '') . 'Exclusive Mega Sales & Bank Offers - ShopCalm')

@section('content')
<div class="container my-3.5 my-md-4 px-3 px-md-4 pb-5 position-relative">

    {{-- Seamless Dynamic Offers Container --}}
    <div id="offers-dynamic-container" class="position-relative" style="transition: opacity 0.2s ease, filter 0.2s ease;">
        @include('customer.offers.partials.content')
    </div>

</div>

<style>
.offer-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 10px 25px rgba(15, 23, 42, 0.08) !important;
}

/* Smooth transition when offers switch without reloading */
.offers-loading-state {
    opacity: 0.55 !important;
    pointer-events: none !important;
    filter: grayscale(15%);
    cursor: wait;
}

/* Top progress bar for offers tab change */
#offers-top-progress {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    height: 3px;
    z-index: 99999;
    background: linear-gradient(90deg, #6366f1, #a855f7, #ec4899);
    background-size: 200% 100%;
    animation: offersProgressAnimation 1.2s infinite ease-in-out;
    display: none;
}

@keyframes offersProgressAnimation {
    0% { background-position: 200% 0; }
    100% { background-position: -200% 0; }
}
</style>

<div id="offers-top-progress"></div>

<script>
(function() {
    let currentAbortController = null;
    window.offerTimerInterval = null;

    /**
     * Initializes or restarts the live countdown timer on the hero banner
     */
    function initOfferTimer() {
        if (window.offerTimerInterval) {
            clearInterval(window.offerTimerInterval);
            window.offerTimerInterval = null;
        }

        const timerContainer = document.getElementById('dedicated-sale-timer');
        if (!timerContainer) return;

        const endTimeStr = timerContainer.dataset.endtime;
        if (!endTimeStr) return;

        const endTime = new Date(endTimeStr).getTime();

        function updateTimer() {
            const now = new Date().getTime();
            const diff = endTime - now;

            if (diff <= 0) {
                if (document.getElementById('dt-days')) document.getElementById('dt-days').textContent = '00';
                if (document.getElementById('dt-hours')) document.getElementById('dt-hours').textContent = '00';
                if (document.getElementById('dt-mins')) document.getElementById('dt-mins').textContent = '00';
                if (document.getElementById('dt-secs')) document.getElementById('dt-secs').textContent = '00';
                if (document.getElementById('dt-mobile-timer')) document.getElementById('dt-mobile-timer').textContent = '00d 00h 00m';
                if (window.offerTimerInterval) {
                    clearInterval(window.offerTimerInterval);
                    window.offerTimerInterval = null;
                }
                return;
            }

            const days = Math.floor(diff / (1000 * 60 * 60 * 24));
            const hours = Math.floor((diff % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
            const minutes = Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60));
            const seconds = Math.floor((diff % (1000 * 60)) / 1000);

            if (document.getElementById('dt-days')) document.getElementById('dt-days').textContent = String(days).padStart(2, '0');
            if (document.getElementById('dt-hours')) document.getElementById('dt-hours').textContent = String(hours).padStart(2, '0');
            if (document.getElementById('dt-mins')) document.getElementById('dt-mins').textContent = String(minutes).padStart(2, '0');
            if (document.getElementById('dt-secs')) document.getElementById('dt-secs').textContent = String(seconds).padStart(2, '0');
            if (document.getElementById('dt-mobile-timer')) {
                document.getElementById('dt-mobile-timer').textContent = `${days}d ${hours}h ${minutes}m ${seconds}s`;
            }
        }

        updateTimer();
        window.offerTimerInterval = setInterval(updateTimer, 1000);
    }

    /**
     * Seamlessly fetches offer content via AJAX without reloading the page
     */
    function loadOfferPage(url, pushToHistory = true, scrollOnPagination = false) {
        const container = document.getElementById('offers-dynamic-container');
        const progressBar = document.getElementById('offers-top-progress');

        if (!container) {
            window.location.href = url;
            return;
        }

        // Cancel previous request if user clicks quickly between offers
        if (currentAbortController) {
            currentAbortController.abort();
        }
        currentAbortController = new AbortController();

        // Visual loading feedback
        container.classList.add('offers-loading-state');
        if (progressBar) progressBar.style.display = 'block';

        fetch(url, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            },
            signal: currentAbortController.signal
        })
        .then(response => {
            if (!response.ok) {
                throw new Error('Server returned ' + response.status);
            }
            return response.json();
        })
        .then(data => {
            if (data && data.html) {
                container.innerHTML = data.html;

                if (data.title) {
                    document.title = data.title;
                }

                // Update URL in browser bar without page reload
                if (pushToHistory) {
                    history.pushState({ url: url }, data.title || document.title, url);
                }

                // Restart timer if the new offer has a live end_time
                initOfferTimer();

                // Smooth scroll to product grid if pagination was clicked
                if (scrollOnPagination) {
                    const heading = document.getElementById('offers-products-heading');
                    if (heading) {
                        const yOffset = -80;
                        const y = heading.getBoundingClientRect().top + window.pageYOffset + yOffset;
                        window.scrollTo({ top: y, behavior: 'smooth' });
                    }
                }
            }
        })
        .catch(err => {
            if (err.name === 'AbortError') return;
            console.warn('Offers AJAX fetch failed, falling back to full reload:', err);
            window.location.href = url;
        })
        .finally(() => {
            container.classList.remove('offers-loading-state');
            if (progressBar) progressBar.style.display = 'none';
        });
    }

    // Intercept clicks on offer filter chips, reset buttons, and pagination
    document.addEventListener('click', function(e) {
        // Ignore modified clicks (cmd/ctrl/shift/middle click) so user can still open in new tab
        if (e.button !== 0 || e.ctrlKey || e.metaKey || e.shiftKey || e.altKey) return;

        const offerLink = e.target.closest('.offer-ajax-link');
        const paginationLink = e.target.closest('#offers-dynamic-container .pagination a');

        if (offerLink) {
            const url = offerLink.getAttribute('href');
            if (url && url !== '#' && !url.startsWith('javascript:')) {
                e.preventDefault();
                loadOfferPage(url, true, false);
            }
        } else if (paginationLink) {
            const url = paginationLink.getAttribute('href');
            if (url && url !== '#' && !url.startsWith('javascript:')) {
                e.preventDefault();
                loadOfferPage(url, true, true);
            }
        }
    });

    // Handle browser Back / Forward buttons seamlessly
    window.addEventListener('popstate', function(e) {
        loadOfferPage(window.location.href, false, false);
    });

    // Initialize timer on first load
    document.addEventListener('DOMContentLoaded', function() {
        initOfferTimer();
    });
})();
</script>
@endsection
