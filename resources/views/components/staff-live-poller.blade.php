@props([
    'endpoint' => route('order-manager.live-orders'),
    'portal' => 'order_manager'
])

<!-- Live Notification Audio Chime & Real-Time Poller -->
<div id="staffLivePollerConfig" 
     data-endpoint="{{ $endpoint }}" 
     data-portal="{{ $portal }}" 
     style="display: none;"></div>

<style>
    @keyframes pulse-live {
        0% { transform: scale(0.95); opacity: 0.8; box-shadow: 0 0 0 0 rgba(16, 185, 129, 0.7); }
        70% { transform: scale(1); opacity: 1; box-shadow: 0 0 0 8px rgba(16, 185, 129, 0); }
        100% { transform: scale(0.95); opacity: 0.8; box-shadow: 0 0 0 0 rgba(16, 185, 129, 0); }
    }
    .pulse-dot {
        width: 8px;
        height: 8px;
        background-color: #10b981;
        border-radius: 50%;
        display: inline-block;
        animation: pulse-live 2s infinite cubic-bezier(0.45, 0, 0.55, 1);
    }
    @keyframes highlightRow {
        0% { background-color: rgba(14, 165, 233, 0.25); transform: translateY(-4px); }
        100% { background-color: transparent; transform: translateY(0); }
    }
    .new-order-highlight {
        animation: highlightRow 3s ease-out;
    }
</style>

<script>
(function () {
    const configEl = document.getElementById('staffLivePollerConfig');
    if (!configEl) return;

    const endpoint = configEl.getAttribute('data-endpoint');
    const portal = configEl.getAttribute('data-portal') || 'order_manager';

    let lastKnownOrderId = 0;
    let isInitialLoad = true;
    let soundEnabled = localStorage.getItem('staff_sound_notifications') !== 'false';

    // Global AudioContext with automatic unlock on first user interaction
    let audioCtx = null;
    function getAudioContext() {
        if (!audioCtx) {
            const AudioCtxClass = window.AudioContext || window.webkitAudioContext;
            if (AudioCtxClass) {
                audioCtx = new AudioCtxClass();
            }
        }
        if (audioCtx && audioCtx.state === 'suspended') {
            audioCtx.resume();
        }
        return audioCtx;
    }

    function unlockAudio() {
        getAudioContext();
    }
    document.addEventListener('click', unlockAudio);
    document.addEventListener('keydown', unlockAudio);

    // 1. Synthesize Loud 4-Tone Alert Siren for New Orders (Strictly reserved for Order Manager portal)
    function playOrderChime() {
        if (!soundEnabled) return;
        if (portal !== 'order_manager') return;
        try {
            const ctx = getAudioContext();
            if (!ctx) return;
            if (ctx.state === 'suspended') ctx.resume();

            function playNote(freq, start, duration, vol = 0.5) {
                const osc = ctx.createOscillator();
                const gain = ctx.createGain();
                osc.type = 'triangle';
                osc.frequency.setValueAtTime(freq, ctx.currentTime + start);
                gain.gain.setValueAtTime(0.001, ctx.currentTime + start);
                gain.gain.exponentialRampToValueAtTime(vol, ctx.currentTime + start + 0.03);
                gain.gain.exponentialRampToValueAtTime(0.001, ctx.currentTime + start + duration);
                osc.connect(gain);
                gain.connect(ctx.destination);
                osc.start(ctx.currentTime + start);
                osc.stop(ctx.currentTime + start + duration);
            }

            // Loud 4-Tone High-Attention Order Bell (A5 -> D6 -> A5 -> D6)
            playNote(880.00, 0.00, 0.20, 0.6);
            playNote(1174.66, 0.15, 0.25, 0.7);
            playNote(880.00, 0.35, 0.20, 0.6);
            playNote(1174.66, 0.50, 0.40, 0.8);
        } catch (e) {
            console.warn('Audio chime could not be played:', e);
        }
    }

    // 2. Sound Toggle Helper in Navbar
    window.toggleStaffSound = function () {
        soundEnabled = !soundEnabled;
        localStorage.setItem('staff_sound_notifications', soundEnabled);
        const icon = document.getElementById('staffSoundIcon');
        const text = document.getElementById('staffSoundText');
        if (icon) {
            icon.className = soundEnabled ? 'bi bi-volume-up-fill text-success' : 'bi bi-volume-mute-fill text-danger';
        }
        if (text) {
            text.textContent = soundEnabled ? 'Sound ON' : 'Muted';
        }

        if (soundEnabled && portal === 'order_manager') playOrderChime();
    };

    // 3. Dynamic Row Generator for New Incoming Order
    function createOrderTableRow(order) {
        const tr = document.createElement('tr');
        tr.className = 'new-order-highlight';

        if (portal === 'order_manager') {
            tr.innerHTML = `
                <td class="ps-4">
                    <a href="${order.url}" class="fw-bold text-primary text-decoration-none font-monospace small">
                        #${order.order_number}
                    </a>
                    <div class="text-muted" style="font-size: 0.68rem;">
                        <i class="bi bi-clock me-0.5"></i>${order.created_at_human}
                        <span class="badge bg-success bg-opacity-15 text-success rounded-pill ms-1 fw-bold" style="font-size: 0.62rem;">NEW</span>
                    </div>
                </td>
                <td>
                    <div class="mb-1">${order.fulfillment_badge}</div>
                    <div class="fw-semibold text-dark small">${order.customer_name}</div>
                    <div class="text-muted small" style="font-size: 0.7rem;">
                        <i class="bi bi-geo-alt me-0.5"></i>${order.city} (${order.pincode})
                    </div>
                </td>
                <td>
                    <span class="badge bg-light text-secondary border px-2.5 py-1 rounded-pill small fw-bold">
                        ${order.items_count} unit${order.items_count > 1 ? 's' : ''}
                    </span>
                </td>
                <td>
                    <div class="fw-bold text-dark small">${order.total_amount_fmt}</div>
                    <div class="text-muted font-monospace" style="font-size: 0.65rem; text-transform: uppercase;">
                        ${order.payment_method} &bull; <span class="${order.payment_status === 'paid' ? 'text-success' : 'text-warning'}">${order.payment_status || 'pending'}</span>
                    </div>
                </td>
                <td>
                    <span class="badge rounded-pill px-2.5 py-1 fw-bold" style="background: #fef3c7; color: #92400e; font-size: 0.72rem;">
                        ${order.status_label}
                    </span>
                </td>
                <td class="pe-4 text-end">
                    <div class="d-flex align-items-center justify-content-end gap-1.5 flex-nowrap">
                        <a href="${order.url}" class="btn btn-sm btn-primary rounded-pill px-2.5 py-1 d-inline-flex align-items-center gap-1 shadow-xs fw-bold" style="font-size: 0.72rem; background: #0284c7; border-color: #0284c7;">
                            <span>Dispatch</span> <i class="bi bi-arrow-right"></i>
                        </a>
                    </div>
                </td>
            `;
        } else {
            // Admin Orders Table row
            tr.innerHTML = `
                <td class="ps-4">
                    <a href="${order.url}" class="fw-bold text-primary font-monospace small">#${order.order_number}</a>
                    <span class="badge bg-success bg-opacity-15 text-success rounded-pill ms-1 fw-bold" style="font-size: 0.62rem;">NEW</span>
                </td>
                <td><div class="fw-semibold text-dark small">${order.customer_name}</div></td>
                <td>${order.fulfillment_badge}</td>
                <td class="fw-bold text-dark small">${order.total_amount_fmt}</td>
                <td><span class="badge bg-warning text-dark">${order.status_label}</span></td>
                <td><div class="small text-muted">${order.created_at_fmt}</div></td>
                <td class="pe-4 text-end">
                    <a href="${order.url}" class="btn btn-sm btn-outline-primary rounded-pill px-2.5 py-1" style="font-size: 0.72rem;">View</a>
                </td>
            `;
        }

        return tr;
    }

    // 4. Update Live Counters in UI
    function updateStats(stats) {
        if (!stats) return;

        // Order Manager Dashboard KPIs
        const elNeeds = document.getElementById('kpiNeedsProcessing');
        if (elNeeds && stats.needs_processing !== undefined) elNeeds.textContent = stats.needs_processing;

        const elPack = document.getElementById('kpiPackingQueue');
        if (elPack && stats.packing_queue !== undefined) elPack.textContent = stats.packing_queue;

        const elTransit = document.getElementById('kpiInTransit');
        if (elTransit && stats.in_transit !== undefined) elTransit.textContent = stats.in_transit;

        const elTodayOrders = document.getElementById('kpiTodayOrders');
        if (elTodayOrders && stats.today_orders !== undefined) elTodayOrders.textContent = stats.today_orders;

        const elTodayRevenue = document.getElementById('kpiTodayRevenue');
        if (elTodayRevenue && stats.today_revenue_fmt) elTodayRevenue.textContent = stats.today_revenue_fmt;
    }

    // 5. Poll Function
    async function pollLiveOrders() {
        try {
            const url = `${endpoint}?since_id=${lastKnownOrderId}&portal=${portal}`;
            const res = await fetch(url, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                }
            });

            if (!res.ok) return;
            const data = await res.json();

            // Set high-water mark ID on initial load
            if (isInitialLoad) {
                lastKnownOrderId = data.latest_id || 0;
                isInitialLoad = false;
                return;
            }

            // If new orders detected
            if (data.has_new && data.new_orders && data.new_orders.length > 0) {
                lastKnownOrderId = data.latest_id;
                playOrderChime();

                // Find table body on page (Dashboard priority table or Orders list table)
                const tbody = document.querySelector('tbody.divide-y') || document.querySelector('tbody');
                const emptyRow = tbody ? tbody.querySelector('td[colspan]') : null;
                if (emptyRow && emptyRow.parentElement) {
                    emptyRow.parentElement.remove();
                }

                data.new_orders.forEach(order => {
                    if (tbody) {
                        const newTr = createOrderTableRow(order);
                        tbody.insertBefore(newTr, tbody.firstChild);
                    }

                    // Display SweetAlert2 Live Notification Toast
                    if (window.Swal) {
                        Swal.fire({
                            toast: true,
                            position: 'top-end',
                            icon: 'info',
                            title: `🔔 New Order #${order.order_number}!`,
                            html: `<strong>${order.customer_name}</strong> from <strong>${order.city}</strong> (${order.total_amount_fmt})<br><a href="${order.url}" class="btn btn-xs btn-primary rounded-pill px-2.5 py-0.5 mt-1 text-white fw-bold" style="font-size: 0.72rem; text-decoration: none;">View Order &rarr;</a>`,
                            showConfirmButton: false,
                            timer: 7000,
                            timerProgressBar: true
                        });
                    }
                });
            }

            updateStats(data.stats);

        } catch (e) {
            console.debug('Polling live-orders heartbeat:', e);
        }
    }

    // Start auto-polling every 10 seconds (slows to 30s when tab is inactive)
    let pollInterval = 10000;
    let timerId = null;

    function startTimer() {
        if (timerId) clearInterval(timerId);
        timerId = setInterval(pollLiveOrders, pollInterval);
    }

    document.addEventListener('visibilitychange', function () {
        pollInterval = document.hidden ? 30000 : 10000;
        startTimer();
    });

    // Run initial heartbeat immediately
    pollLiveOrders();
    startTimer();
})();
</script>
