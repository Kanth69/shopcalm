<!-- Global Centered Alert Modal (Reusable for Contact, Tickets, & all actions) -->
<div class="modal fade" id="globalCenterAlertModal" tabindex="-1" aria-labelledby="globalCenterAlertTitle" aria-hidden="true" style="z-index: 109999;">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 rounded-4 shadow-lg overflow-hidden text-center p-4 p-md-5" style="background: linear-gradient(135deg, #ffffff 0%, #faf5ff 100%);">
            <div class="modal-body p-0 position-relative">
                <!-- Close Button in Corner -->
                <button type="button" class="btn-close position-absolute top-0 end-0 p-1 text-muted" data-bs-dismiss="modal" aria-label="Close" style="font-size: 0.85rem; top: -8px; right: -8px;"></button>

                <!-- Glowing Icon Circle -->
                <div class="mb-3.5 d-inline-flex align-items-center justify-content-center p-2 rounded-circle" id="globalCenterAlertIconWrap" style="background: rgba(16, 185, 129, 0.12); border: 2px dashed rgba(16, 185, 129, 0.35);">
                    <div class="d-flex align-items-center justify-content-center rounded-circle text-white" id="globalCenterAlertIconInner" style="width: 68px; height: 68px; background: linear-gradient(135deg, #10b981 0%, #059669 100%); box-shadow: 0 10px 25px rgba(16, 185, 129, 0.35);">
                        <i class="bi bi-check-lg fs-1 fw-bold" id="globalCenterAlertIcon"></i>
                    </div>
                </div>

                <!-- Title -->
                <h3 class="fw-bolder mb-2 text-dark" id="globalCenterAlertTitle" style="letter-spacing: -0.02em; color: #0f172a;">
                    Success!
                </h3>

                <!-- Message -->
                <p class="text-muted mb-4 mx-auto" id="globalCenterAlertMessage" style="font-size: 0.95rem; line-height: 1.65; max-width: 360px;">
                </p>

                <!-- Optional Badge Pill -->
                <div id="globalCenterAlertBadgeWrap" class="mb-4" style="display: none;">
                    <div class="d-inline-flex align-items-center gap-2 bg-light px-3.5 py-1.5 rounded-pill border small fw-semibold text-dark" style="font-size: 0.8rem;" id="globalCenterAlertBadge">
                        <i class="bi bi-headset text-primary" id="globalCenterAlertBadgeIcon"></i> 
                        <span id="globalCenterAlertBadgeText">Helpdesk Ticket Logged</span>
                    </div>
                </div>

                <!-- Action Button -->
                <div>
                    <button type="button" class="btn btn-primary rounded-pill px-5 py-2.5 fw-bold shadow-sm" id="globalCenterAlertBtn" data-bs-dismiss="modal" style="font-size: 0.9rem;">
                        Got it, Thanks!
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
window.showCenterAlert = function(options) {
    const opts = Object.assign({
        type: 'success', // 'success', 'danger', 'error', 'warning', 'info'
        title: 'Success!',
        message: '',
        badge: '',
        buttonText: 'Got it, Thanks!',
        onClose: null,
        autoCloseMs: null
    }, options);

    const modalEl = document.getElementById('globalCenterAlertModal');
    if (!modalEl) return;

    const iconWrap = document.getElementById('globalCenterAlertIconWrap');
    const iconInner = document.getElementById('globalCenterAlertIconInner');
    const icon = document.getElementById('globalCenterAlertIcon');
    const titleEl = document.getElementById('globalCenterAlertTitle');
    const messageEl = document.getElementById('globalCenterAlertMessage');
    const badgeWrap = document.getElementById('globalCenterAlertBadgeWrap');
    const badgeEl = document.getElementById('globalCenterAlertBadge');
    const btn = document.getElementById('globalCenterAlertBtn');

    const configs = {
        success: {
            wrapBg: 'rgba(16, 185, 129, 0.12)',
            wrapBorder: 'rgba(16, 185, 129, 0.35)',
            innerBg: 'linear-gradient(135deg, #10b981 0%, #059669 100%)',
            innerShadow: '0 10px 25px rgba(16, 185, 129, 0.35)',
            icon: 'bi-check-lg'
        },
        danger: {
            wrapBg: 'rgba(239, 68, 68, 0.12)',
            wrapBorder: 'rgba(239, 68, 68, 0.35)',
            innerBg: 'linear-gradient(135deg, #ef4444 0%, #dc2626 100%)',
            innerShadow: '0 10px 25px rgba(239, 68, 68, 0.35)',
            icon: 'bi-x-lg'
        },
        error: {
            wrapBg: 'rgba(239, 68, 68, 0.12)',
            wrapBorder: 'rgba(239, 68, 68, 0.35)',
            innerBg: 'linear-gradient(135deg, #ef4444 0%, #dc2626 100%)',
            innerShadow: '0 10px 25px rgba(239, 68, 68, 0.35)',
            icon: 'bi-x-lg'
        },
        warning: {
            wrapBg: 'rgba(245, 158, 11, 0.12)',
            wrapBorder: 'rgba(245, 158, 11, 0.35)',
            innerBg: 'linear-gradient(135deg, #f59e0b 0%, #d97706 100%)',
            innerShadow: '0 10px 25px rgba(245, 158, 11, 0.35)',
            icon: 'bi-exclamation-lg'
        },
        info: {
            wrapBg: 'rgba(99, 102, 241, 0.12)',
            wrapBorder: 'rgba(99, 102, 241, 0.35)',
            innerBg: 'linear-gradient(135deg, #6366f1 0%, #4f46e5 100%)',
            innerShadow: '0 10px 25px rgba(99, 102, 241, 0.35)',
            icon: 'bi-info-lg'
        }
    };

    const cfg = configs[opts.type] || configs.success;
    if (iconWrap) {
        iconWrap.style.background = cfg.wrapBg;
        iconWrap.style.borderColor = cfg.wrapBorder;
    }
    if (iconInner) {
        iconInner.style.background = cfg.innerBg;
        iconInner.style.boxShadow = cfg.innerShadow;
    }
    if (icon) {
        icon.className = `bi ${opts.icon || cfg.icon} fs-1 fw-bold`;
    }

    if (titleEl) titleEl.textContent = opts.title;
    if (messageEl) messageEl.textContent = opts.message;
    if (btn) btn.textContent = opts.buttonText;

    if (badgeWrap && badgeEl) {
        if (opts.badge) {
            badgeWrap.style.display = 'block';
            if (typeof opts.badge === 'string') {
                badgeEl.innerHTML = opts.badge;
            } else if (opts.badge.text) {
                badgeEl.innerHTML = (opts.badge.icon ? `<i class="${opts.badge.icon} me-1.5"></i>` : '') + `<span>${opts.badge.text}</span>`;
            }
        } else {
            badgeWrap.style.display = 'none';
        }
    }

    const bsModal = bootstrap.Modal.getInstance(modalEl) || new bootstrap.Modal(modalEl);

    // One-time close handler
    const onCloseWrapper = function() {
        modalEl.removeEventListener('hidden.bs.modal', onCloseWrapper);
        if (typeof opts.onClose === 'function') {
            opts.onClose();
        }
    };
    modalEl.addEventListener('hidden.bs.modal', onCloseWrapper);

    bsModal.show();

    if (opts.autoCloseMs && Number(opts.autoCloseMs) > 0) {
        setTimeout(() => {
            bsModal.hide();
        }, Number(opts.autoCloseMs));
    }
};
</script>
