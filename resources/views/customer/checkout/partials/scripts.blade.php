@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" crossorigin=""></script>
<script>

// 1-Tap Delivery Preference Chips Handler (No Emojis)
let selectedDeliveryPreferences = [];

function toggleDeliveryChip(btn) {
    const pref = btn.getAttribute('data-pref');
    if (!pref) return;

    if (btn.classList.contains('active')) {
        btn.classList.remove('active');
        selectedDeliveryPreferences = selectedDeliveryPreferences.filter(p => p !== pref);
    } else {
        btn.classList.add('active');
        if (!selectedDeliveryPreferences.includes(pref)) {
            selectedDeliveryPreferences.push(pref);
        }
    }
    updateNotesField();
}

function toggleCustomNotesInput() {
    const wrapper = document.getElementById('custom-notes-wrapper');
    const toggleBtn = document.getElementById('btn-toggle-custom-note');
    if (wrapper) {
        wrapper.classList.toggle('d-none');
        if (!wrapper.classList.contains('d-none')) {
            const input = document.getElementById('custom_notes_input');
            if (input) input.focus();
            if (toggleBtn) toggleBtn.innerHTML = '<i class="bi bi-dash-circle me-1"></i> Hide notes input';
        } else {
            if (toggleBtn) toggleBtn.innerHTML = '<i class="bi bi-plus-circle me-1"></i> Add landmark or custom instructions';
        }
    }
}

function updateNotesField() {
    const customText = document.getElementById('custom_notes_input')?.value.trim() || '';
    let parts = [...selectedDeliveryPreferences];
    if (customText) {
        parts.push(`Note: ${customText}`);
    }
    const finalNotes = parts.join(' | ');
    const hiddenNotes = document.getElementById('notes');
    if (hiddenNotes) {
        hiddenNotes.value = finalNotes.slice(0, 150);
    }
}

// Payment Mode UI & Dynamic Buttons Handler
function updatePayButtonUI() {
    const isCod = document.getElementById('cod')?.checked;
    const isFullyWalletPaid = (window.isOrderFullyCoveredByWallet === true);
    const grandTotalRaw = document.getElementById('summary-grand-total')?.textContent.replace(/[^0-9.]/g, '') || '0';
    const grandTotalVal = parseFloat(grandTotalRaw);

    const onlineBtn = document.getElementById('btn-submit-online-pay');
    const codBtn = document.getElementById('btn-submit-cod-pay');
    const onlineBtnSidebar = document.getElementById('btn-submit-online-pay-sidebar');
    const codBtnSidebar = document.getElementById('btn-submit-cod-pay-sidebar');
    const mobileOnlineBtn = document.getElementById('btn-mobile-online-pay');
    const mobileCodBtn = document.getElementById('btn-mobile-cod-pay');
    const amountSpan = document.getElementById('btn-pay-amount');
    const mobileTotal = document.getElementById('mobile-sticky-grand-total');

    if (amountSpan) amountSpan.textContent = `₹${grandTotalVal.toFixed(2)}`;
    document.querySelectorAll('.btn-pay-amount-mirror').forEach(el => el.textContent = `₹${grandTotalVal.toFixed(2)}`);
    if (mobileTotal) mobileTotal.textContent = `₹${grandTotalVal.toFixed(2)}`;

    if (isCod && !isFullyWalletPaid && grandTotalVal > 0) {
        if (onlineBtn) onlineBtn.classList.add('d-none');
        if (codBtn) { codBtn.classList.remove('d-none'); codBtn.classList.add('d-flex'); }
        if (onlineBtnSidebar) onlineBtnSidebar.classList.add('d-none');
        if (codBtnSidebar) { codBtnSidebar.classList.remove('d-none'); codBtnSidebar.classList.add('d-flex'); }
        if (mobileOnlineBtn) mobileOnlineBtn.classList.add('d-none');
        if (mobileCodBtn) mobileCodBtn.classList.remove('d-none');
    } else {
        if (onlineBtn) { onlineBtn.classList.remove('d-none'); onlineBtn.classList.add('d-flex'); }
        if (codBtn) { codBtn.classList.add('d-none'); codBtn.classList.remove('d-flex'); }
        if (onlineBtnSidebar) { onlineBtnSidebar.classList.remove('d-none'); onlineBtnSidebar.classList.add('d-flex'); }
        if (codBtnSidebar) { codBtnSidebar.classList.add('d-none'); codBtnSidebar.classList.remove('d-flex'); }
        if (mobileOnlineBtn) mobileOnlineBtn.classList.remove('d-none');
        if (mobileCodBtn) mobileCodBtn.classList.add('d-none');
    }
}

// Direct COD Order Submission
function submitCodOrderWithConfirmation(e) {
    if (typeof handleOrderSubmission === 'function') {
        handleOrderSubmission(e);
    }
}
window.submitCodOrderWithConfirmation = submitCodOrderWithConfirmation;

let userAddresses = {!! json_encode($addresses) !!};
let currentSelectedAddress = userAddresses.find(a => a.is_default) || (userAddresses.length > 0 ? userAddresses[0] : null);
window.isCurrentAddressServiceable = true;
window.isCurrentAddressCodAvailable = true;
window.activeShippingCharge = parseFloat("{{ $shippingFee ?? 0 }}");
window.activeCodFee = parseFloat("{{ $resolvedCodFee ?? 40 }}");
window.currentCheckoutStep = 1;

window.activePendingOrder = null;

function showInPlacePaymentRecovery(msg) {
    const alertBox = document.getElementById('payment-recovery-inline-alert');
    const msgEl = document.getElementById('payment-recovery-inline-msg');
    if (alertBox) {
        if (msgEl && msg) {
            msgEl.textContent = msg;
        }
        alertBox.classList.remove('d-none');
        if (typeof goToStep === 'function') {
            goToStep(3);
        }
        const cardBody = document.getElementById('payment-card-body');
        if (cardBody) {
            cardBody.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }
    }
}

function selectCodAndSubmit() {
    const codRadio = document.getElementById('cod');
    if (codRadio && !codRadio.disabled) {
        codRadio.checked = true;
        codRadio.dispatchEvent(new Event('change'));
        if (typeof handleCodTileClick === 'function') {
            handleCodTileClick();
        }
        setTimeout(() => {
            const btnCod = document.getElementById('btn-submit-cod-pay-sidebar');
            if (btnCod && !btnCod.classList.contains('d-none')) {
                btnCod.click();
            } else {
                handleOrderSubmission(new Event('submit'));
            }
        }, 150);
    } else {
        Swal.fire({
            icon: 'info',
            title: 'COD Unavailable',
            text: 'Cash on Delivery is currently unavailable for this PIN code. Please retry with UPI, Cards, or NetBanking.',
            confirmButtonColor: '#4f46e5'
        });
    }
}

function showPendingOrderBlockModal() {
    return false;
}

function cancelPendingOrderOnCheckout(orderId, orderNumber) {
    Swal.fire({
        title: `Cancel Pending Order #${orderNumber}?`,
        text: 'Cancelling this pending order is 100% free and will allow you to place your new order immediately.',
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#ef4444',
        cancelButtonColor: '#64748b',
        confirmButtonText: 'Yes, Cancel Pending Order',
        cancelButtonText: 'Back'
    }).then((res) => {
        if (!res.isConfirmed) return;

        const bannerBtn = document.getElementById('btn-cancel-pending-banner');
        if (bannerBtn) {
            bannerBtn.disabled = true;
            bannerBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Cancelling...';
        }

        const cancelUrl = (window.activePendingOrder && window.activePendingOrder.cancel_url)
            ? window.activePendingOrder.cancel_url
            : `/account/orders/${orderId}/cancel`;

        fetch(cancelUrl, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                cancellation_reason: 'Cancelled pending order during checkout to place a new order',
                refund_method: 'original_source'
            })
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                window.activePendingOrder = null;
                const bannerEl = document.getElementById('pending-order-restriction-banner');
                if (bannerEl) bannerEl.remove();

                Swal.fire({
                    icon: 'success',
                    title: 'Pending Order Cancelled!',
                    text: data.message || `Order #${orderNumber} has been cancelled.`,
                    confirmButtonColor: '#10b981'
                });
            } else {
                if (bannerBtn) {
                    bannerBtn.disabled = false;
                    bannerBtn.innerHTML = '<i class="bi bi-x-circle me-1"></i> Cancel';
                }
                Swal.fire('Error', data.message || 'Could not cancel the pending order.', 'error');
            }
        })
        .catch(err => {
            console.error('Cancel pending order error:', err);
            if (bannerBtn) {
                bannerBtn.disabled = false;
                bannerBtn.innerHTML = '<i class="bi bi-x-circle me-1"></i> Cancel';
            }
            Swal.fire('Error', 'Network error while cancelling pending order.', 'error');
        });
    });
}

// -------------------------------------------------------------
// STEP NAVIGATION CONTROLLER (1: Address -> 2: Offers -> 3: Payment -> 4: Confirm)
// -------------------------------------------------------------
function goToStep(step) {
    if (step > 1 && (!currentSelectedAddress || !document.getElementById('hidden_shipping_address')?.value.trim())) {
        const errorBanner = document.getElementById('address-error-banner');
        if (errorBanner) {
            errorBanner.style.display = 'block';
            errorBanner.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }
        step = 1;
    }

    if (step > 1 && window.isCurrentAddressServiceable === false) {
        Swal.fire({
            icon: 'error',
            title: 'Delivery Unavailable',
            text: 'Delivery is currently unavailable to the selected PIN code. Please select or add a different delivery address.',
            confirmButtonColor: '#ef4444'
        });
        step = 1;
    }

    window.currentCheckoutStep = step;

    // Toggle Step Views
    document.getElementById('checkout-step-1-view')?.classList.toggle('d-none', step !== 1);
    document.getElementById('checkout-step-2-view')?.classList.toggle('d-none', step !== 2);
    document.getElementById('checkout-step-3-view')?.classList.toggle('d-none', step !== 3);

    // Toggle Sidebar & Mobile Action Buttons (Strictly Single Button per step)
    document.getElementById('step-1-sidebar-actions')?.classList.toggle('d-none', step !== 1);
    document.getElementById('step-2-sidebar-actions')?.classList.toggle('d-none', step !== 2);
    document.getElementById('step-3-sidebar-actions')?.classList.toggle('d-none', step !== 3);

    document.getElementById('step-1-mobile-actions')?.classList.toggle('d-none', step !== 1);
    document.getElementById('step-2-mobile-actions')?.classList.toggle('d-none', step !== 2);
    document.getElementById('step-3-mobile-actions')?.classList.toggle('d-none', step !== 3);

    // Update Top Stepper Progress Bar (3 Steps: 0%, 50%, 100%)
    const progressLine = document.getElementById('stepper-progress-line');
    const widths = { 1: '0%', 2: '50%', 3: '100%' };
    if (progressLine) progressLine.style.width = widths[step] || '0%';

    for (let s = 1; s <= 3; s++) {
        const badge = document.getElementById(`stepper-badge-${s}`);
        const text = document.getElementById(`stepper-text-${s}`);
        if (!badge || !text) continue;

        if (s < step) {
            badge.className = 'checkout-step-badge text-white shadow-xs';
            badge.style.background = '#10b981';
            badge.style.boxShadow = 'none';
            badge.innerHTML = '<i class="bi bi-check-lg"></i>';
            text.className = 'fw-semibold text-success mt-1.5 text-center';
        } else if (s === step) {
            badge.className = 'checkout-step-badge text-white shadow-xs';
            badge.style.background = '#4f46e5';
            badge.style.boxShadow = '0 0 0 3px rgba(79, 70, 229, 0.2) !important';
            badge.innerHTML = s;
            text.className = 'fw-bold text-primary mt-1.5 text-center';
        } else {
            badge.className = 'checkout-step-badge bg-light text-muted border';
            badge.style.background = '';
            badge.style.boxShadow = '';
            badge.innerHTML = s;
            text.className = 'fw-semibold text-muted mt-1.5 text-center';
        }
    }

    // Toggle Mobile Summary Cards: show on Step 3, hide on Step 1 & 2
    const summaryWrapper = document.getElementById('checkout-summary-cards-wrapper');
    if (summaryWrapper) {
        if (step === 3) {
            summaryWrapper.classList.remove('d-none');
        } else {
            summaryWrapper.classList.add('d-none');
        }
    }

    updatePayButtonUI();
    window.scrollTo({ top: 0, behavior: 'smooth' });
}

function proceedFromStep1() {
    if (window.activePendingOrder) {
        showPendingOrderBlockModal();
        return;
    }

    const hiddenAddress = document.getElementById('hidden_shipping_address');
    if (!currentSelectedAddress || !hiddenAddress || !hiddenAddress.value.trim()) {
        const errorBanner = document.getElementById('address-error-banner');
        if (errorBanner) {
            errorBanner.style.display = 'block';
            errorBanner.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }
        return;
    }

    if (window.isCurrentAddressServiceable === false) {
        Swal.fire({
            icon: 'error',
            title: 'Delivery Unavailable',
            text: 'Delivery is currently unavailable to the selected PIN code. Please select or add a different delivery address.',
            confirmButtonColor: '#ef4444'
        });
        return;
    }

    goToStep(2);
}

function proceedFromStep2() {
    goToStep(3);
}

function proceedFromStep3() {
    const grandTotalRaw = document.getElementById('summary-grand-total')?.textContent.replace(/[^0-9.]/g, '') || '0';
    const grandTotalVal = parseFloat(grandTotalRaw);
    const selectedPayMethod = document.querySelector('input[name="payment_method"]:checked')?.value || 'online';
    if (grandTotalVal > 0 && selectedPayMethod === 'cod' && window.isCurrentAddressCodAvailable === false) {
        Swal.fire({
            icon: 'error',
            title: 'COD Not Available',
            text: 'Cash on Delivery (COD) is not available for this delivery PIN code. Please select Online Payment.',
            confirmButtonColor: '#ef4444'
        });
        const onlineRadio = document.getElementById('pay_online');
        if (onlineRadio) {
            onlineRadio.checked = true;
            onlineRadio.dispatchEvent(new Event('change'));
        }
        return;
    }

    goToStep(4);
}

function syncTermsConsent(source) {
    const isChecked = source.checked;
    const topCb = document.getElementById('terms-consent-checkbox-top');
    const btmCb = document.getElementById('terms-consent-checkbox');
    if (topCb) topCb.checked = isChecked;
    if (btmCb) btmCb.checked = isChecked;

    ['confirm-terms-wrapper', 'confirm-terms-wrapper-top'].forEach(id => {
        const box = document.getElementById(id);
        if (box) {
            box.style.borderColor = isChecked ? '#cbd5e1' : '';
            box.style.background = isChecked ? '#f8fafc' : '';
        }
    });
}
window.syncTermsConsent = syncTermsConsent;

function resetPlaceOrderButton() {
    updatePayButtonUI();
    const actionBtnIds = [
        'btn-place-order', 'btn-place-order-top',
        'btn-submit-online-pay-sidebar', 'btn-submit-cod-pay-sidebar',
        'btn-mobile-online-pay', 'btn-mobile-cod-pay'
    ];
    actionBtnIds.forEach(id => {
        const btn = document.getElementById(id);
        if (btn) btn.disabled = false;
    });
}
window.resetPlaceOrderButton = resetPlaceOrderButton;

function updateReviewSummaries() {
    const grandTotalRaw = document.getElementById('summary-grand-total')?.textContent.replace(/[^0-9.]/g, '') || '0';
    const grandTotalVal = parseFloat(grandTotalRaw);
    const selectedPayMethod = document.querySelector('input[name="payment_method"]:checked')?.value || 'online';
    const isOnline = (selectedPayMethod === 'online');

    // Step 4 Offers Summary
    const revOffers = document.getElementById('review-offers-summary-text');
    if (revOffers) {
        const discountRow = document.getElementById('summary-discount-row');
        const walletRow = document.getElementById('summary-wallet-row');
        const discountVal = discountRow && !discountRow.classList.contains('d-none') ? document.getElementById('summary-discount-val')?.textContent : null;
        const discountLabel = document.getElementById('summary-discount-label')?.textContent || 'Coupon';
        const walletVal = walletRow && !walletRow.classList.contains('d-none') ? document.getElementById('summary-wallet-val')?.textContent : null;

        let offersHtml = '';
        if (discountVal) {
            offersHtml += `<div class="d-flex align-items-center gap-2 mb-1.5 flex-wrap"><span class="badge text-white fw-bold px-2.5 py-1 rounded-pill" style="background: #059669; font-size: 0.78rem;"><i class="bi bi-tag-fill me-1"></i>${discountLabel}</span> <span class="font-monospace fw-bold text-success">${discountVal}</span></div>`;
        }
        if (walletVal) {
            offersHtml += `<div class="d-flex align-items-center gap-2 flex-wrap"><span class="badge bg-success text-white fw-bold px-2.5 py-1 rounded-pill" style="font-size: 0.78rem;"><i class="bi bi-wallet2 me-1"></i>ShopCalm Wallet</span> <span class="font-monospace fw-bold text-success">${walletVal}</span></div>`;
        }
        if (!discountVal && !walletVal) {
            offersHtml = '<span class="text-muted" style="font-size: 0.84rem;">No coupon code or wallet balance applied to this order.</span>';
        }
        revOffers.innerHTML = offersHtml;
    }

    // Step 4 Payment Mode Summary
    const revPay = document.getElementById('review-payment-mode-text');
    if (revPay) {
        if (grandTotalVal <= 0) {
            revPay.innerHTML = '<div class="d-flex align-items-center gap-2 flex-wrap mb-1.5"><span class="badge bg-success text-white px-3 py-1.5 rounded-pill fw-bold shadow-xs" style="font-size: 0.78rem; letter-spacing: 0.3px;"><i class="bi bi-shield-fill-check me-1"></i>100% COVERED</span> <span class="fw-bold text-dark fs-6">ShopCalm Wallet & Offers (₹0.00 Due)</span></div><small class="text-secondary d-block" style="font-size: 0.82rem; line-height: 1.45;">Your entire order amount is covered by wallet/offers. Payment methods are disabled as no gateway payment or cash collection is required.</small>';
        } else if (isOnline) {
            revPay.innerHTML = '<div class="d-flex align-items-center gap-2 flex-wrap mb-1.5"><span class="badge text-white px-3 py-1.5 rounded-pill fw-bold shadow-xs" style="background: #4f46e5; font-size: 0.78rem; letter-spacing: 0.4px;"><i class="bi bi-lightning-charge-fill me-1 text-warning"></i>ONLINE PAYMENT</span> <span class="fw-bold text-dark fs-6">UPI / Cards / NetBanking / PhonePe</span></div><small class="text-secondary d-block" style="font-size: 0.82rem; line-height: 1.45;">Instant payment via Razorpay with instant order confirmation.</small>';
        } else {
            revPay.innerHTML = '<div class="d-flex align-items-center gap-2 flex-wrap mb-1.5"><span class="badge bg-dark text-white px-3 py-1.5 rounded-pill fw-bold shadow-xs" style="font-size: 0.78rem; letter-spacing: 0.4px;"><i class="bi bi-cash-stack me-1 text-success"></i>CASH ON DELIVERY</span> <span class="fw-bold text-dark fs-6">Cash on Delivery (Pay at Doorstep)</span></div><small class="text-secondary d-block" style="font-size: 0.82rem; line-height: 1.45;">Pay via cash or UPI directly to our delivery executive when parcel arrives.</small>';
        }
    }

    // Step 4 Mobile Top Summary Updates
    const mobileTopTotal = document.getElementById('mobile-top-grand-total');
    if (mobileTopTotal) {
        const grandTotalFmt = document.getElementById('summary-grand-total')?.textContent?.trim() || '₹{{ number_format($grandTotal, 2) }}';
        mobileTopTotal.textContent = grandTotalFmt;
    }
    const mobileTopBadge = document.getElementById('mobile-top-paymode-badge');
    if (mobileTopBadge) {
        if (grandTotalVal <= 0) {
            mobileTopBadge.className = 'badge rounded-pill px-2.5 py-1 fw-bold text-white shadow-xs bg-success';
            mobileTopBadge.innerHTML = '<i class="bi bi-check-circle-fill me-1"></i> Covered by Wallet';
        } else if (isOnline) {
            mobileTopBadge.className = 'badge rounded-pill px-2.5 py-1 fw-bold text-white shadow-xs';
            mobileTopBadge.style.background = '#4f46e5';
            mobileTopBadge.innerHTML = '<i class="bi bi-lightning-charge-fill me-1 text-warning"></i> Online Payment';
        } else {
            mobileTopBadge.className = 'badge rounded-pill px-2.5 py-1 fw-bold text-white shadow-xs bg-dark';
            mobileTopBadge.innerHTML = '<i class="bi bi-cash-stack me-1 text-success"></i> Cash on Delivery';
        }
    }

    // Step 4 Final CTA button label & state
    resetPlaceOrderButton();
}

document.addEventListener('DOMContentLoaded', function() {
    if (currentSelectedAddress) {
        applySelectedAddress(currentSelectedAddress);
    }

    const checkoutForm = document.getElementById('checkout-form');

    document.querySelectorAll('input[name="payment_method"]').forEach(radio => {
            radio.addEventListener('change', updatePayButtonUI);
        radio.addEventListener('change', function() {
            if (typeof updateShippingFeeAndTotal === 'function') {
                updateShippingFeeAndTotal(window.activeShippingCharge, 499);
            } else {
                updateReviewSummaries();
            }
            document.querySelectorAll('.payment-tile').forEach(tile => tile.classList.remove('active-tile'));
            const parentTile = this.closest('.payment-tile');
            if (parentTile) parentTile.classList.add('active-tile');
        });
    });

    const consentInput = document.getElementById('terms-consent-checkbox');
    if (consentInput) {
        consentInput.addEventListener('change', function() {
            const termsBox = document.getElementById('confirm-terms-wrapper');
            if (termsBox && this.checked) {
                termsBox.style.borderColor = '#cbd5e1';
                termsBox.style.background = '#f8fafc';
            }
        });
    }

    // Live Character Counter for Delivery Notes (Max 150 characters)
    const notesInput = document.getElementById('notes');
    const notesCounter = document.getElementById('notes-counter');
    const notesWarning = document.getElementById('notes-warning');
    if (notesInput && notesCounter) {
        function updateNotesCounter() {
            const currentLen = notesInput.value.length;
            notesCounter.textContent = `${currentLen} / 150`;
            if (currentLen >= 150) {
                notesCounter.className = 'badge rounded-pill bg-danger-subtle text-danger border border-danger font-monospace px-2.5 py-1';
                if (notesWarning) notesWarning.style.display = 'inline-block';
            } else if (currentLen >= 120) {
                notesCounter.className = 'badge rounded-pill bg-warning-subtle text-dark border border-warning font-monospace px-2.5 py-1';
                if (notesWarning) notesWarning.style.display = 'none';
            } else {
                notesCounter.className = 'badge rounded-pill bg-light text-secondary border font-monospace px-2.5 py-1';
                if (notesWarning) notesWarning.style.display = 'none';
            }
        }
        notesInput.addEventListener('input', updateNotesCounter);
        notesInput.addEventListener('paste', () => setTimeout(updateNotesCounter, 0));
        updateNotesCounter();
    }

    // ==============================================================
    // SMART CONTEXT-AWARE SINGLE-SELECT DELIVERY TIME SLOTS
    // ==============================================================
    const DELIVERY_TIME_SLOT_OPTIONS = {
        home: {
            badgeHtml: '<i class="bi bi-house-door-fill text-primary me-1"></i> Home',
            badgeClass: 'badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25',
            defaultSlot: 'Anytime (7 AM - 9 PM)',
            slots: [
                { id: 'home_anytime', text: 'Anytime (7 AM - 9 PM)', value: 'Anytime (7 AM - 9 PM)' },
                { id: 'home_morning', text: 'Morning (7 AM - 12 PM)', value: 'Morning (7 AM - 12 PM)' },
                { id: 'home_afternoon', text: 'Afternoon (12 PM - 5 PM)', value: 'Afternoon (12 PM - 5 PM)' },
                { id: 'home_evening', text: 'Evening (5 PM - 9 PM)', value: 'Evening (5 PM - 9 PM)' },
            ]
        },
        work: {
            badgeHtml: '<i class="bi bi-briefcase-fill text-warning me-1"></i> Office',
            badgeClass: 'badge bg-warning bg-opacity-20 text-dark border border-warning border-opacity-50',
            defaultSlot: 'Office Hours (10 AM - 6 PM)',
            slots: [
                { id: 'work_allday', text: 'Office Hours (10 AM - 6 PM)', value: 'Office Hours (10 AM - 6 PM)' },
                { id: 'work_morning', text: 'First Half (10 AM - 2 PM)', value: 'First Half (10 AM - 2 PM)' },
                { id: 'work_afternoon', text: 'Second Half (2 PM - 6 PM)', value: 'Second Half (2 PM - 6 PM)' },
            ]
        },
        office: {
            badgeHtml: '<i class="bi bi-briefcase-fill text-warning me-1"></i> Office',
            badgeClass: 'badge bg-warning bg-opacity-20 text-dark border border-warning border-opacity-50',
            defaultSlot: 'Office Hours (10 AM - 6 PM)',
            slots: [
                { id: 'work_allday', text: 'Office Hours (10 AM - 6 PM)', value: 'Office Hours (10 AM - 6 PM)' },
                { id: 'work_morning', text: 'First Half (10 AM - 2 PM)', value: 'First Half (10 AM - 2 PM)' },
                { id: 'work_afternoon', text: 'Second Half (2 PM - 6 PM)', value: 'Second Half (2 PM - 6 PM)' },
            ]
        },
        other: {
            badgeHtml: '<i class="bi bi-geo-alt-fill text-success me-1"></i> Other',
            badgeClass: 'badge bg-success bg-opacity-10 text-success border border-success border-opacity-25',
            defaultSlot: 'Anytime (9 AM - 8 PM)',
            slots: [
                { id: 'other_anytime', text: 'Anytime (9 AM - 8 PM)', value: 'Anytime (9 AM - 8 PM)' },
                { id: 'other_morning', text: 'Morning / Day (9 AM - 2 PM)', value: 'Morning / Day (9 AM - 2 PM)' },
                { id: 'other_afternoon', text: 'Afternoon / Evening (2 PM - 8 PM)', value: 'Afternoon / Evening (2 PM - 8 PM)' },
            ]
        }
    };

    window.selectedDeliveryTimeSlot = '';

    window.renderDeliveryTimeSlots = function(addrType) {
        const typeKey = (addrType || 'home').toLowerCase();
        const config = DELIVERY_TIME_SLOT_OPTIONS[typeKey] || DELIVERY_TIME_SLOT_OPTIONS.other;

        const badgeEl = document.getElementById('delivery-addr-badge');
        const timeSelect = document.getElementById('delivery_time_select');
        const hiddenSlotInput = document.getElementById('selected_time_slot');

        if (badgeEl) {
            badgeEl.className = `${config.badgeClass} rounded-pill px-2.5 py-0.5 fw-bold`;
            badgeEl.innerHTML = config.badgeHtml;
        }

        if (!timeSelect) return;
        timeSelect.innerHTML = '';

        // Check if current selected slot is valid for this address type, else reset to default
        const validValues = config.slots.map(s => s.value);
        if (!validValues.includes(window.selectedDeliveryTimeSlot)) {
            window.selectedDeliveryTimeSlot = config.defaultSlot;
        }
        if (hiddenSlotInput) {
            hiddenSlotInput.value = window.selectedDeliveryTimeSlot;
        }

        config.slots.forEach(slot => {
            const opt = document.createElement('option');
            opt.value = slot.value;
            opt.textContent = slot.text;
            if (slot.value === window.selectedDeliveryTimeSlot) {
                opt.selected = true;
            }
            timeSelect.appendChild(opt);
        });

        timeSelect.onchange = function() {
            window.selectedDeliveryTimeSlot = this.value;
            if (hiddenSlotInput) hiddenSlotInput.value = this.value;
        };
    };

    // Initialize delivery time slots on DOM ready
    const initialAddrType = @json(($activeAddr ?? null) ? ($activeAddr->type ?? 'home') : 'home');
    window.renderDeliveryTimeSlots(initialAddrType);

    window.handleOrderSubmission = function(e) { if (e && e.preventDefault) e.preventDefault(); executeOrderSubmission(); };
    function executeOrderSubmission() {

        if (window.activePendingOrder) {
            showPendingOrderBlockModal();
            return;
        }

        if (window.currentCheckoutStep === 1) {
            proceedFromStep1();
            return;
        }

        // Step 4: Final Place Order Validation
        const consentCb = document.getElementById('terms-consent-checkbox');
        const consentCbTop = document.getElementById('terms-consent-checkbox-top');
        const hasConsent = (consentCb && consentCb.checked) || (consentCbTop && consentCbTop.checked);

        if (!hasConsent) {
            Swal.fire({
                icon: 'warning',
                title: 'Agreement Required',
                text: 'Please agree to the Terms & Conditions and Return Policy before placing your order.',
                confirmButtonColor: '#4f46e5'
            });
            if (consentCb) {
                consentCb.scrollIntoView({ behavior: 'smooth', block: 'center' });
                consentCb.focus();
            }
            return;
        }

        // Keep form checkbox checked so server receives terms_consent=1
        if (consentCb) consentCb.checked = true;
        if (consentCbTop) consentCbTop.checked = true;

        const hiddenAddress = document.getElementById('hidden_shipping_address');
        if (!currentSelectedAddress || !hiddenAddress || !hiddenAddress.value.trim() || window.isCurrentAddressServiceable === false) {
            Swal.fire({
                icon: 'warning',
                title: 'Delivery Address Required',
                text: 'Please select or add a serviceable delivery address.',
                confirmButtonColor: '#4f46e5'
            });
            goToStep(1);
            return;
        }

        const grandTotalRaw = document.getElementById('summary-grand-total')?.textContent.replace(/[^0-9.]/g, '') || '0';
        const grandTotalVal = parseFloat(grandTotalRaw);
        const selectedPayMethod = document.querySelector('input[name="payment_method"]:checked')?.value || 'online';
        if (grandTotalVal > 0 && selectedPayMethod === 'cod' && window.isCurrentAddressCodAvailable === false) {
            Swal.fire({
                icon: 'error',
                title: 'COD Not Available',
                text: 'Cash on Delivery (COD) is not available for this delivery PIN code. Please select Online Payment.',
                confirmButtonColor: '#ef4444'
            });
            goToStep(3);
            return;
        }

        const actionBtnIds = [
            'btn-place-order', 'btn-place-order-top',
            'btn-submit-online-pay-sidebar', 'btn-submit-cod-pay-sidebar',
            'btn-mobile-online-pay', 'btn-mobile-cod-pay'
        ];
        actionBtnIds.forEach(id => {
            const btn = document.getElementById(id);
            if (btn) {
                btn.disabled = true;
                btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span> Processing...';
            }
        });

        // Format preferred delivery time slot + custom instructions into notes
        const notesInputEl = document.getElementById('notes');
        const chosenSlot = document.getElementById('delivery_time_select')?.value || window.selectedDeliveryTimeSlot || document.getElementById('selected_time_slot')?.value || '';
        const userNotes = notesInputEl ? notesInputEl.value.trim() : '';
        let finalNotes = '';
        if (chosenSlot) {
            finalNotes = `[Time: ${chosenSlot}]`;
        }
        if (userNotes && !userNotes.startsWith('[Time:')) {
            finalNotes = finalNotes ? `${finalNotes} ${userNotes}` : userNotes;
        } else if (userNotes && userNotes.startsWith('[Time:')) {
            finalNotes = userNotes;
        }
        if (notesInputEl) {
            notesInputEl.value = finalNotes.slice(0, 150);
        }

        const checkoutForm = document.getElementById('checkout-form');
        const formData = new FormData(checkoutForm);

        fetch('{{ route("checkout.place-order") }}', {
            method: 'POST',
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: formData
        })
        .then(async res => {
            const data = await res.json().catch(() => ({ success: false, message: 'Server communication error.' }));
            if (!res.ok || !data.success) {
                if (data.has_pending_order) {
                    window.activePendingOrder = {
                        id: data.pending_order_id,
                        order_number: data.pending_order_number,
                        total: data.pending_order_total || '0.00',
                        proceed_url: data.proceed_url,
                        order_url: data.order_url,
                        cancel_url: data.cancel_url
                    };
                    resetPlaceOrderButton();
                    showPendingOrderBlockModal();
                    return null;
                }
                const err = new Error(data.message || 'Could not place order. Please try again.');
                if (data.failed_url) {
                    err.failed_url = data.failed_url;
                }
                throw err;
            }
            return data;
        })
        .then(data => {
            if (!data) return;
            if (data.redirect_url) {
                window.location.href = data.redirect_url;
            } else if (data.gateway === 'razorpay' || data.razorpay_order_id) {
                const options = {
                    key: data.razorpay_key_id,
                    amount: data.amount_paise,
                    currency: data.currency || "INR",
                    name: "{{ config('app.name', 'ShopCalm') }}",
                    description: "Order #" + data.order_number,
                    order_id: data.razorpay_order_id,
                    prefill: data.prefill || {},
                    theme: { color: "#4f46e5" },
                    handler: function(response) {
                        ['btn-place-order', 'btn-place-order-top'].forEach(id => {
                            const btn = document.getElementById(id);
                            if (btn) btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span> Verifying Payment...';
                        });
                        fetch('{{ route("checkout.razorpay.verify") }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                'Accept': 'application/json'
                            },
                            body: JSON.stringify({
                                order_id: data.order_id,
                                razorpay_order_id: response.razorpay_order_id,
                                razorpay_payment_id: response.razorpay_payment_id,
                                razorpay_signature: response.razorpay_signature
                            })
                        })
                        .then(r => r.json())
                        .then(verifyData => {
                            if (verifyData.success && verifyData.redirect_url) {
                                window.location.href = verifyData.redirect_url;
                            } else {
                                resetPlaceOrderButton();
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Verification Failed',
                                    text: verifyData.message || 'Payment signature error.',
                                    confirmButtonColor: '#4f46e5',
                                    willClose: () => resetPlaceOrderButton(),
                                    didClose: () => resetPlaceOrderButton()
                                }).then(() => {
                                    resetPlaceOrderButton();
                                    if (verifyData.failed_url || data.failed_url) {
                                        window.location.href = verifyData.failed_url || data.failed_url;
                                    }
                                });
                            }
                        })
                        .catch(err => {
                            console.error("Verification error:", err);
                            resetPlaceOrderButton();
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: 'Payment verification error.',
                                confirmButtonColor: '#4f46e5',
                                willClose: () => resetPlaceOrderButton(),
                                didClose: () => resetPlaceOrderButton()
                            }).then(() => {
                                resetPlaceOrderButton();
                                if (data.failed_url) {
                                    window.location.href = data.failed_url;
                                }
                            });
                        });
                    },
                    modal: {
                        ondismiss: function() {
                            resetPlaceOrderButton();
                            showInPlacePaymentRecovery('Payment window was closed before completion. You can retry with UPI/Card or switch to Cash on Delivery.');
                        }
                    }
                };

                try {
                    const rzp = new Razorpay(options);
                    rzp.on('payment.failed', function(resp) {
                        resetPlaceOrderButton();
                        const errorMsg = (resp && resp.error && resp.error.description) 
                            ? resp.error.description 
                            : 'Payment authorization failed.';
                        showInPlacePaymentRecovery(errorMsg + ' You can retry with another method or pay via Cash on Delivery.');
                    });
                    rzp.open();
                } catch (rzpErr) {
                    console.error("Razorpay open error:", rzpErr);
                    resetPlaceOrderButton();
                    Swal.fire({
                        icon: 'error',
                        title: 'Gateway Notice',
                        text: rzpErr.message || 'Could not open Razorpay modal.',
                        confirmButtonColor: '#4f46e5',
                        confirmButtonText: 'OK',
                        allowOutsideClick: false,
                        willClose: () => resetPlaceOrderButton(),
                        didClose: () => resetPlaceOrderButton()
                    }).then(() => {
                        if (data && data.failed_url) {
                            window.location.href = data.failed_url;
                        }
                    });
                }
            }
        })
        .catch(err => {
            console.error("Place Order Error:", err);
            resetPlaceOrderButton();
            if (err.failed_url) {
                Swal.fire({
                    icon: 'error',
                    title: 'Payment Incomplete',
                    text: err.message || 'Payment could not be completed with the gateway.',
                    confirmButtonColor: '#4f46e5',
                    confirmButtonText: 'OK',
                    allowOutsideClick: false,
                    willClose: () => resetPlaceOrderButton(),
                    didClose: () => resetPlaceOrderButton()
                }).then(() => {
                    window.location.href = err.failed_url;
                });
                return;
            }
            Swal.fire({
                icon: 'error',
                title: 'Order Notice',
                text: err.message || 'Failed to initialize order.',
                confirmButtonColor: '#4f46e5',
                confirmButtonText: 'OK',
                willClose: () => resetPlaceOrderButton(),
                didClose: () => resetPlaceOrderButton()
            });
        });
    }

    if (checkoutForm) {
        checkoutForm.addEventListener('submit', handleOrderSubmission);
    }

    window.addEventListener('pageshow', function() {
        updateReviewSummaries();
    });

    const walletSwitch = document.getElementById('toggle-wallet-switch');
    if (walletSwitch) {
        walletSwitch.addEventListener('change', function() {
            const isChecked = this.checked;
            const hiddenWallet = document.getElementById('hidden_use_wallet');
            if (hiddenWallet) hiddenWallet.value = isChecked ? '1' : '0';

            fetch('{{ route("checkout.toggle-wallet") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ use_wallet: isChecked })
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    if (typeof updateShippingFeeAndTotal === 'function') {
                        updateShippingFeeAndTotal(window.activeShippingCharge, 499);
                    } else {
                        updateReviewSummaries();
                    }
                }
            })
            .catch(err => {
                console.error("Wallet toggle error:", err);
                this.checked = !isChecked;
                if (hiddenWallet) hiddenWallet.value = this.checked ? '1' : '0';
            });
        });
    }

    window.updateShippingFeeAndTotal = function(shippingCharge, freeShippingThreshold) {
        window.activeShippingCharge = shippingCharge || 0;
        const selectedPaymentMethod = document.querySelector('input[name="payment_method"]:checked')?.value || 'online';
        const isCod = (selectedPaymentMethod === 'cod');
        const isWalletChecked = document.getElementById('toggle-wallet-switch')?.checked || false;

        let rawSubtotal = parseFloat("{{ $subtotal }}");
        let discountAmt = document.getElementById('summary-discount-row')?.classList.contains('d-none') ? 0 : parseFloat(document.getElementById('summary-discount-val')?.textContent.replace(/[^0-9.]/g, '') || 0);

        let finalShippingFee = (rawSubtotal >= freeShippingThreshold) ? 0 : window.activeShippingCharge;
        let payableBeforeWallet = Math.max(0, (rawSubtotal - discountAmt) + finalShippingFee);

        let walletBalance = parseFloat("{{ isset($userWallet) ? $userWallet->balance : 0 }}");
        let walletDiscount = 0;

        if (isWalletChecked && walletBalance > 0) {
            walletDiscount = Math.min(walletBalance, payableBeforeWallet);
        }

        const walletRow = document.getElementById('summary-wallet-row');
        const walletVal = document.getElementById('summary-wallet-val');
        if (walletRow && walletVal) {
            if (walletDiscount > 0) {
                walletVal.textContent = `- ₹${walletDiscount.toFixed(2)}`;
                walletRow.classList.remove('d-none');
            } else {
                walletRow.classList.add('d-none');
            }
        }

        let isFullyCoveredByWallet = (isWalletChecked && walletDiscount >= payableBeforeWallet && payableBeforeWallet > 0);
        let codFee = (isCod && !isFullyCoveredByWallet) ? (typeof window.activeCodFee !== 'undefined' ? window.activeCodFee : parseFloat("{{ \App\Models\Setting::get('cod_flat_fee', 40) }}")) : 0;

        const shippingEl = document.getElementById('summary-shipping-fee');
        if (shippingEl) {
            if (finalShippingFee > 0) {
                shippingEl.innerHTML = `<span class="font-monospace text-dark fw-bold">+ ₹${finalShippingFee.toFixed(2)}</span>`;
            } else {
                shippingEl.innerHTML = `<span class="badge bg-success bg-opacity-10 text-success fw-bold rounded-pill px-2 py-0.5">FREE</span>`;
            }
        }

        const codRow = document.getElementById('summary-cod-fee-row');
        const codVal = document.getElementById('summary-cod-fee-val');
        if (codRow && codVal) {
            if (isFullyCoveredByWallet) {
                codVal.innerHTML = `<span class="badge bg-success bg-opacity-10 text-success fw-bold rounded-pill px-2 py-0.5">₹0.00 (Wallet Paid)</span>`;
            } else if (isCod) {
                codRow.classList.remove('d-none');
                codVal.innerHTML = `<span class="font-monospace text-dark fw-bold">+ ₹${codFee.toFixed(2)}</span>`;
            } else {
                codVal.innerHTML = `<span class="badge bg-success bg-opacity-10 text-success fw-bold rounded-pill px-2 py-0.5">₹0.00 (Prepaid Offer)</span>`;
            }
        }

        let calculatedGrandTotal = Math.max(0, (payableBeforeWallet + codFee) - walletDiscount);

        const summaryGrandTotal = document.getElementById('summary-grand-total');
        if (summaryGrandTotal) {
            summaryGrandTotal.textContent = `₹${calculatedGrandTotal.toFixed(2)}`;
        }

        // Live Total Savings Counter & Breakdown Update
        const cardSavingsEl = document.getElementById('card-total-savings');
        let totalSavedNow = parseFloat("{{ $totalDiscount ?? 0 }}") + discountAmt + walletDiscount + parseFloat("{{ $offerDiscount ?? 0 }}");
        if (cardSavingsEl) {
            cardSavingsEl.textContent = `₹${totalSavedNow.toFixed(2)}`;
        }
        document.querySelectorAll('.step2-total-savings-val').forEach(el => {
            el.textContent = `₹${totalSavedNow.toFixed(2)}`;
        });

        const savedBreakdownCoupon = document.getElementById('saved-breakdown-coupon');
        const savedBreakdownCouponRow = document.getElementById('saved-breakdown-coupon-row');
        if (savedBreakdownCoupon && savedBreakdownCouponRow) {
            savedBreakdownCoupon.textContent = `- ₹${discountAmt.toFixed(2)}`;
            savedBreakdownCouponRow.classList.toggle('d-none', discountAmt <= 0);
        }

        const savedBreakdownWallet = document.getElementById('saved-breakdown-wallet');
        const savedBreakdownWalletRow = document.getElementById('saved-breakdown-wallet-row');
        if (savedBreakdownWallet && savedBreakdownWalletRow) {
            savedBreakdownWallet.textContent = `- ₹${walletDiscount.toFixed(2)}`;
            savedBreakdownWalletRow.classList.toggle('d-none', walletDiscount <= 0);
        }

        const fullWalletNotice = document.getElementById('full-wallet-pay-notice');
        const step3WalletNotice = document.getElementById('step-3-wallet-full-notice');
        const paymentContainer = document.getElementById('payment-tiles-container');
        const paymentOverlay = document.getElementById('payment-wallet-disabled-overlay');
        const paymentCardBody = document.getElementById('payment-card-body');

        if (isFullyCoveredByWallet || calculatedGrandTotal <= 0) {
            if (fullWalletNotice) fullWalletNotice.classList.remove('d-none');
            if (step3WalletNotice) step3WalletNotice.classList.remove('d-none');
            if (paymentOverlay) paymentOverlay.classList.remove('d-none');
            if (paymentCardBody) paymentCardBody.classList.add('wallet-payment-blocked');
            if (paymentContainer) {
                paymentContainer.style.opacity = '0.45';
                paymentContainer.style.filter = 'grayscale(1)';
                paymentContainer.style.cursor = 'not-allowed';
            }
        } else {
            if (fullWalletNotice) fullWalletNotice.classList.add('d-none');
            if (step3WalletNotice) step3WalletNotice.classList.add('d-none');
            if (paymentOverlay) paymentOverlay.classList.add('d-none');
            if (paymentCardBody) paymentCardBody.classList.remove('wallet-payment-blocked');
            if (paymentContainer) {
                paymentContainer.style.opacity = '1';
                paymentContainer.style.filter = 'none';
                paymentContainer.style.cursor = 'default';
            }
        }

        updateReviewSummaries();
    };

    // Run initial check on page load to ensure zero-amount / covered state is synced
    window.updateShippingFeeAndTotal(window.activeShippingCharge, 499);
});

function handleApplyCoupon() {
    const input = document.getElementById('coupon_code_input');
    const code = input ? input.value.trim() : '';

    if (!code) {
        Swal.fire({
            icon: 'warning',
            title: 'Coupon Required',
            text: 'Please enter a coupon code before clicking Apply.',
            confirmButtonColor: '#4f46e5'
        });
        if (input) input.focus();
        return;
    }

    const applyBtn = document.querySelector('#coupon-status-wrapper button[onclick="handleApplyCoupon()"]');
    const origBtnHtml = applyBtn ? applyBtn.innerHTML : 'APPLY';
    if (applyBtn) {
        applyBtn.disabled = true;
        applyBtn.innerHTML = '<span class="spinner-border spinner-border-sm"></span>';
    }

    const couponContainer = document.getElementById('coupon-status-wrapper');
    const summaryDiscountRow = document.getElementById('summary-discount-row');
    const summaryDiscountLabel = document.getElementById('summary-discount-label');
    const summaryDiscountVal = document.getElementById('summary-discount-val');
    const summaryGrandTotal = document.getElementById('summary-grand-total');

    fetch('{{ route("checkout.apply-coupon") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json'
        },
        body: JSON.stringify({ coupon_code: code })
    })
    .then(res => res.json())
    .then(data => {
        if (applyBtn) {
            applyBtn.disabled = false;
            applyBtn.innerHTML = origBtnHtml;
        }

        if (data.success) {
            const couponsModalEl = document.getElementById('couponsModal');
            if (couponsModalEl) {
                const modalInstance = bootstrap.Modal.getInstance(couponsModalEl);
                if (modalInstance) modalInstance.hide();
            }

            if (couponContainer) {
                couponContainer.innerHTML = `
                    <div class="p-3 rounded-3 shadow-xs d-flex align-items-center justify-content-between bg-white border" id="applied-coupon-box" style="border-color: #86efac !important;">
                        <div class="d-flex align-items-center gap-2">
                            <i class="bi bi-check-circle-fill text-success fs-4"></i>
                            <div>
                                <span class="font-monospace fw-bold text-success px-2 py-0.5 rounded" style="background: #dcfce7; border: 1px dashed #22c55e; font-size: 0.82rem;">${data.coupon_code}</span>
                                <small class="text-dark d-block mt-1 fw-semibold" style="font-size: 0.76rem;">Saved ₹${parseFloat(data.discount_amount).toFixed(2)} on your order</small>
                            </div>
                        </div>
                        <button type="button" class="btn btn-sm btn-outline-danger rounded-pill px-3 py-1 fw-bold" id="btn-remove-coupon-ajax" onclick="handleRemoveCoupon()" style="font-size: 0.74rem;">Remove</button>
                    </div>
                `;
            }

            if (summaryDiscountRow && summaryDiscountLabel && summaryDiscountVal) {
                summaryDiscountLabel.textContent = `Coupon (${data.coupon_code})`;
                summaryDiscountVal.textContent = `- ₹${parseFloat(data.discount_amount).toFixed(2)}`;
                summaryDiscountRow.classList.remove('d-none');
            }

            if (summaryGrandTotal) {
                summaryGrandTotal.textContent = `₹${parseFloat(data.grand_total).toFixed(2)}`;
            }

            if (typeof updateShippingFeeAndTotal === 'function') {
                updateShippingFeeAndTotal(window.activeShippingCharge, 499);
            } else {
                updateReviewSummaries();
            }

            Swal.fire({
                icon: 'success',
                title: 'Coupon Applied!',
                text: `Saved ₹${parseFloat(data.discount_amount).toFixed(2)} on your order!`,
                timer: 2000,
                showConfirmButton: false
            });

            try { confetti({ particleCount: 50, spread: 60, origin: { y: 0.8 } }); } catch(e) {}

        } else {
            Swal.fire({
                icon: 'error',
                title: 'Coupon Notice',
                text: data.message || 'Invalid coupon code.',
                confirmButtonColor: '#4f46e5'
            });
        }
    })
    .catch(err => {
        console.error("Coupon error:", err);
        if (applyBtn) {
            applyBtn.disabled = false;
            applyBtn.innerHTML = origBtnHtml;
        }
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'Could not apply coupon. Please try again.'
        });
    });
}

function applyCouponFromModal(code) {
    const input = document.getElementById('coupon_code_input');
    if (input) {
        input.value = code;
        handleApplyCoupon();
    } else {
        fetch('{{ route("checkout.apply-coupon") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            },
            body: JSON.stringify({ coupon_code: code })
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                const couponsModalEl = document.getElementById('couponsModal');
                if (couponsModalEl) {
                    const modalInstance = bootstrap.Modal.getInstance(couponsModalEl);
                    if (modalInstance) modalInstance.hide();
                }

                const couponContainer = document.getElementById('coupon-status-wrapper');
                if (couponContainer) {
                    couponContainer.innerHTML = `
                        <div class="p-3 rounded-3 shadow-xs d-flex align-items-center justify-content-between bg-white border" id="applied-coupon-box" style="border-color: #86efac !important;">
                            <div class="d-flex align-items-center gap-2">
                                <i class="bi bi-check-circle-fill text-success fs-4"></i>
                                <div>
                                    <span class="font-monospace fw-bold text-success px-2 py-0.5 rounded" style="background: #dcfce7; border: 1px dashed #22c55e; font-size: 0.82rem;">${data.coupon_code}</span>
                                    <small class="text-dark d-block mt-1 fw-semibold" style="font-size: 0.76rem;">Saved ₹${parseFloat(data.discount_amount).toFixed(2)} on your order</small>
                                </div>
                            </div>
                            <button type="button" class="btn btn-sm btn-outline-danger rounded-pill px-3 py-1 fw-bold" id="btn-remove-coupon-ajax" onclick="handleRemoveCoupon()" style="font-size: 0.74rem;">Remove</button>
                        </div>
                    `;
                }

                const summaryDiscountRow = document.getElementById('summary-discount-row');
                const summaryDiscountLabel = document.getElementById('summary-discount-label');
                const summaryDiscountVal = document.getElementById('summary-discount-val');
                if (summaryDiscountRow && summaryDiscountLabel && summaryDiscountVal) {
                    summaryDiscountLabel.textContent = `Coupon (${data.coupon_code})`;
                    summaryDiscountVal.textContent = `- ₹${parseFloat(data.discount_amount).toFixed(2)}`;
                    summaryDiscountRow.classList.remove('d-none');
                }

                if (typeof updateShippingFeeAndTotal === 'function') {
                    updateShippingFeeAndTotal(window.activeShippingCharge, 499);
                }

                Swal.fire({
                    icon: 'success',
                    title: 'Coupon Applied!',
                    text: `Saved ₹${parseFloat(data.discount_amount).toFixed(2)} on your order!`,
                    timer: 2000,
                    showConfirmButton: false
                });

                try { confetti({ particleCount: 50, spread: 60, origin: { y: 0.8 } }); } catch(e) {}
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Coupon Notice',
                    text: data.message || 'Could not apply coupon.',
                    confirmButtonColor: '#4f46e5'
                });
            }
        });
    }
}

function handleRemoveCoupon() {
    const btn = document.getElementById('btn-remove-coupon-ajax');
    if (btn) {
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>';
    }

    fetch('{{ route("checkout.remove-coupon") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json'
        }
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            const couponContainer = document.getElementById('coupon-status-wrapper');
            if (couponContainer) {
                couponContainer.innerHTML = `
                    <div class="d-flex gap-2">
                        <input type="text" id="coupon_code_input" class="form-control rounded-3 font-monospace fw-bold" placeholder="ENTER COUPON CODE" style="text-transform: uppercase; font-size: 0.88rem; height: 44px;">
                        <button type="button" class="btn btn-primary rounded-3 px-4 fw-bold flex-shrink-0" onclick="handleApplyCoupon()" style="background: #4f46e5; border: none; font-size: 0.84rem; height: 44px;">APPLY</button>
                    </div>
                    <div class="text-muted mt-2 small" style="font-size: 0.75rem;">
                        <i class="bi bi-info-circle text-primary me-1"></i>Apply valid promo code to get instant order discounts.
                    </div>
                `;
            }

            const summaryDiscountRow = document.getElementById('summary-discount-row');
            const summaryDiscountVal = document.getElementById('summary-discount-val');
            if (summaryDiscountRow) summaryDiscountRow.classList.add('d-none');
            if (summaryDiscountVal) summaryDiscountVal.textContent = '- ₹0.00';

            const summaryGrandTotal = document.getElementById('summary-grand-total');
            if (summaryGrandTotal) summaryGrandTotal.textContent = `₹${parseFloat(data.grand_total).toFixed(2)}`;

            if (typeof updateShippingFeeAndTotal === 'function') {
                updateShippingFeeAndTotal(window.activeShippingCharge, 499);
            } else {
                updateReviewSummaries();
            }
        } else {
            if (btn) btn.disabled = false;
        }
    })
    .catch(err => {
        console.error("Remove coupon error:", err);
        if (btn) btn.disabled = false;
    });
}

function applySelectedAddress(addr) {
    currentSelectedAddress = addr;
    
    document.getElementById('hidden_shipping_name').value = addr.name || '';
    document.getElementById('hidden_shipping_phone').value = addr.phone || '';
    const houseNoHidden = document.getElementById('hidden_shipping_house_no');
    if (houseNoHidden) houseNoHidden.value = addr.house_no || '';
    const streetHidden = document.getElementById('hidden_shipping_street');
    if (streetHidden) streetHidden.value = addr.street || '';
    document.getElementById('hidden_shipping_address').value = addr.address || '';
    document.getElementById('hidden_shipping_city').value = addr.city || '';
    document.getElementById('hidden_shipping_state').value = addr.state || '';
    document.getElementById('hidden_shipping_zip').value = addr.zip || '';
    const latField = document.getElementById('hidden_shipping_latitude');
    if (latField) latField.value = addr.latitude || '';
    const lngField = document.getElementById('hidden_shipping_longitude');
    if (lngField) lngField.value = addr.longitude || '';
    const landmarkField = document.getElementById('hidden_shipping_landmark');
    if (landmarkField) landmarkField.value = addr.landmark || '';

    const displayName = document.getElementById('display-name');
    const displayStreetCity = document.getElementById('display-street-city');
    const displayPhone = document.getElementById('display-phone');
    const activeDetails = document.getElementById('active-address-details');
    const noAddrMsg = document.getElementById('no-address-msg');

    if (displayName) {
        const typeBadge = addr.type ? `<span class="badge bg-light text-primary border rounded-pill px-2 py-0.5 text-uppercase fw-semibold ms-1" style="font-size:0.65rem;">${escapeAddressHtml(addr.type)}</span>` : '';
        displayName.innerHTML = `${escapeAddressHtml(addr.name)}${typeBadge}`;
    }
    if (displayStreetCity) {
        const houseStr = addr.house_no ? `<strong>${escapeAddressHtml(addr.house_no)}</strong>, ` : '';
        const streetStr = addr.street ? `${escapeAddressHtml(addr.street)}, ` : (addr.address ? `${escapeAddressHtml(addr.address)}, ` : '');
        const landmarkStr = addr.landmark ? `<span class="text-muted small d-block mt-0.5"><i class="bi bi-geo-alt"></i> Landmark: ${escapeAddressHtml(addr.landmark)}</span>` : '';
        displayStreetCity.innerHTML = `${houseStr}${streetStr}${escapeAddressHtml(addr.city)}, ${escapeAddressHtml(addr.state)} <span class="text-nowrap font-monospace fw-bold text-dark">- ${escapeAddressHtml(addr.zip)}</span>${landmarkStr}`;
    }
    if (displayPhone) displayPhone.innerHTML = `<i class="bi bi-telephone-fill text-muted me-1 small"></i> Mobile: <strong class="text-dark font-monospace">${escapeAddressHtml(addr.phone)}</strong>`;
    
    if (activeDetails) activeDetails.style.display = 'block';
    if (noAddrMsg) noAddrMsg.style.display = 'none';

    if (window.renderDeliveryTimeSlots) {
        window.renderDeliveryTimeSlots(addr.type || 'home');
    }

    return checkPincodeServiceability(addr.zip);
}

function selectModalAddress(addr) {
    applySelectedAddress(addr);
    const modalEl = document.getElementById('addressSelectorModal');
    if (modalEl) {
        const modalInstance = bootstrap.Modal.getInstance(modalEl);
        if (modalInstance) modalInstance.hide();
    }
}

function showAddressSelectorModal() {
    renderModalAddressList();
    const modalEl = document.getElementById('addressSelectorModal');
    if (modalEl) {
        const modalInstance = bootstrap.Modal.getOrCreateInstance(modalEl);
        modalInstance.show();
    }
}

function openNewAddressFromModal() {
    const modalEl = document.getElementById('addressSelectorModal');
    if (modalEl) {
        const modalInstance = bootstrap.Modal.getInstance(modalEl);
        if (modalInstance) modalInstance.hide();
    }
    setTimeout(() => {
        openCheckoutAddressModal();
    }, 250);
}

function openCheckoutAddressModal() {
    resetAddressModalToAddMode();
    const modalEl = document.getElementById('checkoutAddressModal');
    if (modalEl) {
        const modal = bootstrap.Modal.getOrCreateInstance(modalEl);
        modal.show();
        setTimeout(() => {
            const nameInput = document.getElementById('new_name');
            if (nameInput) nameInput.focus();
        }, 200);
    }
}


function fetchCheckoutModalGpsLocation() {
    const btn = document.getElementById('btn-checkout-modal-gps');
    if (!btn) return;
    const origHtml = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1" style="width:0.7rem;height:0.7rem;"></span> Locating...';

    if (!navigator.geolocation) {
        btn.disabled = false;
        btn.innerHTML = origHtml;
        Swal.fire('GPS Not Supported', 'Geolocation is not supported by your browser.', 'info');
        return;
    }

    navigator.geolocation.getCurrentPosition(
        async function (pos) {
            const lat = pos.coords.latitude;
            const lng = pos.coords.longitude;

            const latInput = document.getElementById('new_latitude');
            const lngInput = document.getElementById('new_longitude');
            if (latInput) latInput.value = lat;
            if (lngInput) lngInput.value = lng;

            let addrData = null;
            try {
                const res = await fetch(`https://nominatim.openstreetmap.org/reverse?format=jsonv2&lat=${lat}&lon=${lng}&accept-language=en`, {
                    headers: { 'User-Agent': 'ShopCalm-App/1.0 (contact@shopcalm.in)' }
                });
                if (res.ok) {
                    const data = await res.json();
                    if (data && data.address) {
                        addrData = extractAddressComponents(data.address, data.display_name);
                    }
                }
            } catch (err) {
                console.warn("Reverse geocode failed:", err);
            }

            btn.disabled = false;
            btn.innerHTML = origHtml;

            if (addrData) {
                const streetInput = document.getElementById('new_street');
                const cityInput = document.getElementById('new_city');
                const stateInput = document.getElementById('new_state');
                const zipInput = document.getElementById('new_zip');
                const houseNoInput = document.getElementById('new_house_no');

                if (streetInput && addrData.roadStr) streetInput.value = addrData.roadStr;
                if (cityInput && addrData.city) cityInput.value = addrData.city;
                if (stateInput && addrData.state) stateInput.value = addrData.state;
                if (zipInput && addrData.zip) {
                    zipInput.value = addrData.zip;
                    handleCheckoutPincodeInput(zipInput);
                } else if (addrData.city && addrData.state) {
                    isCheckoutPincodeValidated = true;
                    setCheckoutAddressSaveEnabled(true);
                }

                if (houseNoInput) houseNoInput.focus();

                Swal.fire({
                    icon: 'success',
                    title: 'Location Auto-Filled!',
                    text: 'Please enter your Flat / House No to complete your address.',
                    toast: true,
                    position: 'top-end',
                    showConfirmButton: false,
                    timer: 3000
                });
            } else {
                Swal.fire('Notice', 'Location detected, but unable to resolve street details automatically. Please enter your address details.', 'info');
            }
        },
        function (err) {
            btn.disabled = false;
            btn.innerHTML = origHtml;
            Swal.fire('GPS Notice', 'Unable to retrieve your current location. Please enter your address manually or select on map.', 'info');
        },
        { enableHighAccuracy: true, timeout: 8000, maximumAge: 0 }
    );
}

function openLocationPickerFromAddressModal() {
    const addrModalEl = document.getElementById('checkoutAddressModal');
    if (addrModalEl) {
        const m = bootstrap.Modal.getInstance(addrModalEl);
        if (m) m.hide();
    }
    openLocationPickerModal(false);
}

function resetAddressModalToAddMode() {
    const editIdInput = document.getElementById('edit_address_id');
    if (editIdInput) editIdInput.value = '';

    const iconBox = document.getElementById('checkoutAddressModalIconBox');
    if (iconBox) iconBox.style.background = 'linear-gradient(135deg, #4f46e5 0%, #3730a3 100%)';

    const icon = document.getElementById('checkoutAddressModalIcon');
    if (icon) icon.className = 'bi bi-house-add-fill fs-5';

    const title = document.getElementById('checkoutAddressModalLabel');
    if (title) title.textContent = 'Add New Address';

    const subtitle = document.getElementById('checkoutAddressModalSubtitle');
    if (subtitle) subtitle.textContent = 'Enter your delivery destination details';

    const saveBtn = document.getElementById('btn-save-address');
    if (saveBtn) {
        saveBtn.innerHTML = '<i class="bi bi-bookmark-check me-1"></i> Save Address';
    }

    const nameEl = document.getElementById('new_name');
    const phoneEl = document.getElementById('new_phone');
    const zipEl = document.getElementById('new_zip');
    const houseNoEl = document.getElementById('new_house_no');
    const streetEl = document.getElementById('new_street');
    const landmarkEl = document.getElementById('new_landmark');
    const cityEl = document.getElementById('new_city');
    const stateEl = document.getElementById('new_state');
    const latEl = document.getElementById('new_latitude');
    const lngEl = document.getElementById('new_longitude');

    if (nameEl) nameEl.value = '{{ auth()->user()->name ?? "" }}';
    if (phoneEl) phoneEl.value = '{{ auth()->user()->mobile_number ?? "" }}';
    if (zipEl) zipEl.value = '';
    if (houseNoEl) houseNoEl.value = '';
    if (streetEl) streetEl.value = '';
    if (landmarkEl) landmarkEl.value = '';
    if (cityEl) cityEl.value = '';
    if (stateEl) stateEl.value = '';
    if (latEl) latEl.value = '';
    if (lngEl) lngEl.value = '';

    const homeRadio = document.getElementById('checkout_addr_type_home');
    if (homeRadio) homeRadio.checked = true;

    clearCheckoutPincodeError();
    lockCheckoutCityState(false);
    setCheckoutAddressSaveEnabled(false);
}

function clearActiveAddress() {
    currentSelectedAddress = null;
    ['hidden_shipping_name', 'hidden_shipping_phone', 'hidden_shipping_address', 'hidden_shipping_city', 'hidden_shipping_state', 'hidden_shipping_zip', 'hidden_shipping_latitude', 'hidden_shipping_longitude', 'hidden_shipping_landmark'].forEach(id => {
        const el = document.getElementById(id);
        if (el) el.value = '';
    });
    const activeDetails = document.getElementById('active-address-details');
    const noAddrMsg = document.getElementById('no-address-msg');
    const badgeEl = document.getElementById('active-address-serviceability-badge');
    if (activeDetails) activeDetails.style.display = 'none';
    if (noAddrMsg) noAddrMsg.style.display = 'block';
    if (badgeEl) badgeEl.innerHTML = '';
    window.isCurrentAddressServiceable = false;

    // Open popup modal for user to add new address
    openCheckoutAddressModal();
}

function editActiveCheckoutAddress() {
    const addrId = currentSelectedAddress?.id || (userAddresses.length > 0 ? userAddresses[0].id : null);
    if (!addrId) return;
    editCheckoutAddress(null, addrId);
}

function editCheckoutAddress(e, addressId) {
    if (e) {
        e.stopPropagation();
        e.preventDefault();
    }
    if (!addressId) return;

    // Close address selector modal if open
    const selectorModalEl = document.getElementById('addressSelectorModal');
    if (selectorModalEl) {
        const modalInstance = bootstrap.Modal.getInstance(selectorModalEl);
        if (modalInstance) modalInstance.hide();
    }

    const addr = userAddresses.find(a => Number(a.id) === Number(addressId));
    if (!addr) {
        Swal.fire('Error', 'Address details not found.', 'error');
        return;
    }

    // Set Edit Mode in Modal
    const editIdInput = document.getElementById('edit_address_id');
    if (editIdInput) editIdInput.value = addr.id;

    const iconBox = document.getElementById('checkoutAddressModalIconBox');
    if (iconBox) iconBox.style.background = 'linear-gradient(135deg, #0d9488 0%, #0f766e 100%)';

    const icon = document.getElementById('checkoutAddressModalIcon');
    if (icon) icon.className = 'bi bi-pencil-square fs-5';

    const title = document.getElementById('checkoutAddressModalLabel');
    if (title) title.textContent = 'Edit Delivery Address';

    const subtitle = document.getElementById('checkoutAddressModalSubtitle');
    if (subtitle) subtitle.textContent = 'Update your delivery destination details';

    const saveBtn = document.getElementById('btn-save-address');
    if (saveBtn) {
        saveBtn.innerHTML = '<i class="bi bi-check2-circle me-1"></i> Update Address';
    }

    // Populate Fields
    const nameEl = document.getElementById('new_name');
    const phoneEl = document.getElementById('new_phone');
    const zipEl = document.getElementById('new_zip');
    const houseNoEl = document.getElementById('new_house_no');
    const streetEl = document.getElementById('new_street');
    const landmarkEl = document.getElementById('new_landmark');
    const cityEl = document.getElementById('new_city');
    const stateEl = document.getElementById('new_state');
    const latEl = document.getElementById('new_latitude');
    const lngEl = document.getElementById('new_longitude');

    if (nameEl) nameEl.value = addr.name || '';
    if (phoneEl) phoneEl.value = addr.phone || '';
    if (zipEl) zipEl.value = addr.zip || '';
    if (houseNoEl) houseNoEl.value = addr.house_no || '';
    if (streetEl) streetEl.value = addr.street || addr.address || '';
    if (landmarkEl) landmarkEl.value = addr.landmark || '';
    if (cityEl) cityEl.value = addr.city || '';
    if (stateEl) stateEl.value = addr.state || '';
    if (latEl) latEl.value = addr.latitude || '';
    if (lngEl) lngEl.value = addr.longitude || '';

    const typeVal = (addr.type || 'home').toLowerCase();
    const typeRadio = document.querySelector(`input[name="new_type"][value="${typeVal}"]`);
    if (typeRadio) typeRadio.checked = true;

    // Set validated & lock city/state
    if (addr.zip && addr.city && addr.state) {
        isCheckoutPincodeValidated = true;
        lockCheckoutCityState(true);
        setCheckoutAddressSaveEnabled(true);
        const hintEl = document.getElementById('new_zip_lookup_hint');
        if (hintEl) {
            hintEl.className = 'text-success fw-semibold d-block mt-0.5';
            hintEl.innerHTML = `<i class="bi bi-check-circle-fill me-1"></i>${addr.city}, ${addr.state}`;
        }
    } else {
        isCheckoutPincodeValidated = false;
        lockCheckoutCityState(false);
        setCheckoutAddressSaveEnabled(false);
    }

    setTimeout(() => {
        const addrModalEl = document.getElementById('checkoutAddressModal');
        if (addrModalEl) {
            const modal = bootstrap.Modal.getOrCreateInstance(addrModalEl);
            modal.show();
            setTimeout(() => {
                if (addrEl) addrEl.focus();
            }, 200);
        }
    }, 200);
}

function deleteActiveCheckoutAddress(btn) {
    const addrId = currentSelectedAddress?.id || (userAddresses.length > 0 ? userAddresses[0].id : null);
    if (!addrId) return;
    deleteCheckoutAddress(null, addrId, btn);
}

function deleteCheckoutAddress(e, addressId, btn) {
    if (e) {
        e.stopPropagation();
        e.preventDefault();
    }
    if (!addressId) return;

    Swal.fire({
        title: 'Delete Address?',
        text: 'Are you sure you want to delete this address?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#ef4444',
        cancelButtonColor: '#64748b',
        confirmButtonText: 'Yes, Delete',
        cancelButtonText: 'Cancel'
    }).then(result => {
        if (!result.isConfirmed) return;

        if (btn) {
            btn.disabled = true;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span>';
        }

        fetch(`/account/addresses/${addressId}`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'X-HTTP-Method-Override': 'DELETE',
                'Accept': 'application/json'
            }
        })
        .then(async res => {
            const data = await res.json();
            if (!res.ok || !data.success) throw new Error(data.message || 'Failed to delete address.');
            return data;
        })
        .then(data => {
            const wasActive = (currentSelectedAddress && currentSelectedAddress.id === addressId);
            userAddresses = userAddresses.filter(a => a.id !== addressId);

            if (wasActive) {
                if (userAddresses.length > 0) {
                    applySelectedAddress(userAddresses[0]);
                } else {
                    clearActiveAddress();
                }
            }

            renderModalAddressList();

            if (userAddresses.length === 0) {
                const modalEl = document.getElementById('addressSelectorModal');
                if (modalEl) {
                    const modalInstance = bootstrap.Modal.getInstance(modalEl);
                    if (modalInstance) modalInstance.hide();
                }
            }

            Swal.fire({
                icon: 'success',
                title: 'Deleted!',
                text: data.message || 'Address deleted successfully.',
                toast: true,
                position: 'top-end',
                showConfirmButton: false,
                timer: 3000,
                timerProgressBar: true
            });
        })
        .catch(err => {
            if (btn) {
                btn.disabled = false;
                btn.innerHTML = '<i class="bi bi-trash"></i>';
            }
            Swal.fire({ icon: 'error', title: 'Error', text: err.message || 'Failed to delete address.' });
        });
    });
}

// Track if checkout PIN code is 6-digit validated
let isCheckoutPincodeValidated = false;
let checkoutPincodeErrorTimer = null;
let newPincodeLookupTimer = null;

// Enable or disable Save button for checkout manual address
function setCheckoutAddressSaveEnabled(enabled) {
    const btn = document.getElementById('btn-save-address');
    if (!btn) return;
    isCheckoutPincodeValidated = Boolean(enabled);
    btn.disabled = !enabled;
    if (enabled) {
        btn.style.opacity = '1';
        btn.style.cursor = 'pointer';
    } else {
        btn.style.opacity = '0.6';
        btn.style.cursor = 'not-allowed';
    }
}

// Lock / Unlock City & State inputs once PIN code is validated
function lockCheckoutCityState(locked) {
    const cityInput = document.getElementById('new_city');
    const stateInput = document.getElementById('new_state');
    const cityIcon = document.getElementById('new_city_lock_icon');
    const stateIcon = document.getElementById('new_state_lock_icon');
    const cityHint = document.getElementById('new_city_hint');
    const stateHint = document.getElementById('new_state_hint');

    if (cityInput) {
        cityInput.readOnly = locked;
        if (locked) {
            cityInput.style.backgroundColor = '#f8fafc';
            cityInput.style.cursor = 'default';
            cityInput.style.fontWeight = '600';
            cityInput.style.color = '#1e293b';
        } else {
            cityInput.style.backgroundColor = '';
            cityInput.style.cursor = '';
            cityInput.style.fontWeight = '';
            cityInput.style.color = '';
        }
    }
    if (stateInput) {
        stateInput.readOnly = locked;
        if (locked) {
            stateInput.style.backgroundColor = '#f8fafc';
            stateInput.style.cursor = 'default';
            stateInput.style.fontWeight = '600';
            stateInput.style.color = '#1e293b';
        } else {
            stateInput.style.backgroundColor = '';
            stateInput.style.cursor = '';
            stateInput.style.fontWeight = '';
            stateInput.style.color = '';
        }
    }
    if (cityIcon) cityIcon.classList.toggle('d-none', !locked);
    if (stateIcon) stateIcon.classList.toggle('d-none', !locked);
    if (cityHint) {
        cityHint.innerHTML = locked ? '<span class="text-success fw-medium"><i class="bi bi-shield-check me-0.5"></i>Auto-filled</span>' : '';
    }
    if (stateHint) {
        stateHint.innerHTML = locked ? '<span class="text-success fw-medium"><i class="bi bi-shield-check me-0.5"></i>Auto-filled</span>' : '';
    }
}

// Clear PIN error and cancel auto-dismiss timer
function clearCheckoutPincodeError() {
    if (checkoutPincodeErrorTimer) {
        clearTimeout(checkoutPincodeErrorTimer);
        checkoutPincodeErrorTimer = null;
    }
    const hintEl = document.getElementById('new_zip_lookup_hint');
    if (hintEl) {
        hintEl.style.opacity = '1';
        hintEl.innerHTML = '';
    }
    const cityHint = document.getElementById('new_city_hint');
    if (cityHint) cityHint.innerHTML = '';
    const stateHint = document.getElementById('new_state_hint');
    if (stateHint) stateHint.innerHTML = '';
}

// Show validation error for wrong PIN that auto-dismisses after 4 seconds
function showDismissableCheckoutPincodeError(msg) {
    clearCheckoutPincodeError();
    const hintEl = document.getElementById('new_zip_lookup_hint');
    if (!hintEl) return;
    hintEl.className = 'text-danger fw-semibold d-block mt-0.5';
    hintEl.style.transition = 'opacity 0.35s ease';
    hintEl.style.opacity = '1';
    hintEl.innerHTML = `<i class="bi bi-x-circle-fill me-1"></i>${msg}`;

    checkoutPincodeErrorTimer = setTimeout(() => {
        hintEl.style.opacity = '0';
        setTimeout(() => {
            hintEl.innerHTML = '';
            hintEl.style.opacity = '1';
        }, 350);
    }, 4500);
}

function openNewAddressForm() {
    openCheckoutAddressModal();
}

function closeNewAddressForm() {
    const modalEl = document.getElementById('checkoutAddressModal');
    if (modalEl) {
        const modalInstance = bootstrap.Modal.getInstance(modalEl);
        if (modalInstance) modalInstance.hide();
    }
    resetAddressModalToAddMode();
}

// Real-time Pincode typing handler: numbers only, clears previous errors, manages locks
function handleCheckoutPincodeInput(input) {
    const pin = (input.value || '').replace(/\D/g, '').slice(0, 6);
    if (input.value !== pin) {
        input.value = pin;
    }

    // Immediately clear error when typing
    clearCheckoutPincodeError();

    if (pin.length < 6) {
        lockCheckoutCityState(false);
        setCheckoutAddressSaveEnabled(false);
        if (pin.length === 0) {
            document.getElementById('new_city').value = '';
            document.getElementById('new_state').value = '';
        }
        return;
    }

    handleNewPincodeLookup(pin);
}

function handleNewPincodeLookup(val) {
    const pin = (val || '').replace(/\D/g, '').slice(0, 6);
    const zipInput = document.getElementById('new_zip');
    if (zipInput && zipInput.value !== pin) {
        zipInput.value = pin;
    }
    const hintEl = document.getElementById('new_zip_lookup_hint');
    if (newPincodeLookupTimer) {
        clearTimeout(newPincodeLookupTimer);
    }
    if (pin.length < 6) {
        if (hintEl) hintEl.textContent = '';
        lockCheckoutCityState(false);
        setCheckoutAddressSaveEnabled(false);
        return;
    }

    if (hintEl) {
        hintEl.className = 'text-muted d-block mt-0.5';
        hintEl.innerHTML = '<span class="spinner-border spinner-border-sm me-1" style="width:0.6rem;height:0.6rem;"></span> Checking pincode...';
    }

    newPincodeLookupTimer = setTimeout(async () => {
        let city = '';
        let state = '';
        let isServiceable = false;
        let deliveryMessage = '';

        // 1. Check ShopCalm store delivery serviceability first
        try {
            const res = await fetch('{{ route("delivery.check") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ pincode: pin })
            });
            const data = await res.json();
            if (data.city && !data.city.startsWith('PIN ')) city = data.city;
            if (data.state && data.state !== 'India') state = data.state;
            if (data.is_serviceable) {
                isServiceable = true;
                deliveryMessage = data.estimated_delivery ? `Est. Delivery: ${data.estimated_delivery}` : 'Delivery Available';
            }
        } catch (err) {
            console.warn('ShopCalm delivery service check failed:', err);
        }

        // 2. Fallback to Postal Pincode Directory if city/state not in local DB or generic
        if (!city || !state) {
            try {
                const postalRes = await fetch(`https://api.postalpincode.in/pincode/${pin}`);
                const postalData = await postalRes.json();
                if (Array.isArray(postalData) && postalData[0]?.Status === 'Success' && postalData[0].PostOffice?.length > 0) {
                    const po = postalData[0].PostOffice[0];
                    if (!city) city = po.District || po.Division || po.Block || '';
                    if (!state) state = po.State || '';
                }
            } catch (postalErr) {
                console.warn('Postal API lookup fallback error:', postalErr);
            }
        }

        const cityInput = document.getElementById('new_city');
        const stateInput = document.getElementById('new_state');

        // 3. Validation result handling: only allow saving if city & state are verified
        if (city && state) {
            if (cityInput) {
                cityInput.value = city;
                cityInput.style.transition = 'background-color 0.4s ease';
                cityInput.style.backgroundColor = '#ecfdf5';
                setTimeout(() => { if (cityInput) cityInput.style.backgroundColor = ''; }, 1200);
            }
            if (stateInput) {
                stateInput.value = state;
                stateInput.style.transition = 'background-color 0.4s ease';
                stateInput.style.backgroundColor = '#ecfdf5';
                setTimeout(() => { if (stateInput) stateInput.style.backgroundColor = ''; }, 1200);
            }

            // Lock City & State (Readonly) and Enable Save Button
            lockCheckoutCityState(true);
            setCheckoutAddressSaveEnabled(true);

            if (hintEl) {
                if (isServiceable) {
                    hintEl.className = 'text-success fw-semibold d-block mt-0.5';
                    hintEl.innerHTML = `<i class="bi bi-check-circle-fill me-1"></i>${city}, ${state} &bull; ${deliveryMessage}`;
                } else {
                    hintEl.className = 'text-primary fw-semibold d-block mt-0.5';
                    hintEl.innerHTML = `<i class="bi bi-geo-alt-fill me-1"></i>${city}, ${state}`;
                }
            }
        } else {
            // Wrong / Non-existent PIN code: Keep unlocked, Disable Save, Show Auto-Dismiss Error
            lockCheckoutCityState(false);
            setCheckoutAddressSaveEnabled(false);
            showDismissableCheckoutPincodeError(`Invalid PIN code (${pin}). Please check and enter a valid PIN.`);
        }
    }, 280);
}

function escapeAddressHtml(str) {
    if (!str) return '';
    return String(str)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#039;');
}

function renderModalAddressList() {
    const listEl = document.getElementById('modal-address-list');
    if (!listEl) return;

    listEl.innerHTML = '';
    const currentId = currentSelectedAddress?.id;

    if (userAddresses.length === 0) {
        listEl.innerHTML = `
            <div class="col-12 text-center py-4 text-muted">
                <i class="bi bi-geo-alt fs-2 d-block mb-2 text-secondary"></i>
                <span class="small fw-semibold">No saved addresses found.</span>
            </div>
        `;
    } else {
        userAddresses.forEach((addr) => {
            const isSelected = (currentId && addr.id === currentId) || (!currentId && currentSelectedAddress?.zip === addr.zip);
            const col = document.createElement('div');
            col.className = 'col-12';
            const card = document.createElement('div');
            card.className = `card border rounded-4 p-3 p-sm-3.5 address-modal-option cursor-pointer position-relative shadow-xs ${isSelected ? 'is-selected' : ''}`;
            card.onclick = () => selectModalAddress(addr);

            const safeName = addr.name || '';
            const safePhone = addr.phone || '';
            const safeZip = addr.zip || '';

            card.innerHTML = `
                <div class="d-flex flex-column flex-sm-row justify-content-between align-items-start align-items-sm-center gap-2.5">
                    <div class="min-w-0 flex-grow-1 w-100">
                        <div class="d-flex align-items-center gap-2 mb-1 flex-wrap">
                            <strong class="text-dark fs-6">${escapeAddressHtml(safeName)}</strong>
                            ${addr.type ? `<span class="badge bg-light text-primary border rounded-pill px-2 py-0.5 text-uppercase fw-semibold" style="font-size: 0.65rem;">${escapeAddressHtml(addr.type)}</span>` : ''}
                            ${isSelected ? '<span class="badge bg-success text-white rounded-pill px-2.5 py-0.5 fw-bold" style="font-size: 0.68rem;"><i class="bi bi-check-lg me-1"></i>Selected</span>' : (addr.is_default ? '<span class="badge bg-light text-secondary border rounded-pill px-2 py-0.5" style="font-size: 0.65rem;">Default</span>' : '')}
                        </div>
                        <div class="text-secondary mb-1.5" style="font-size: 0.86rem; line-height: 1.45;">
                            ${addr.house_no ? `<strong>${escapeAddressHtml(addr.house_no)}</strong>, ` : ''}${escapeAddressHtml(addr.street || addr.address || '')}, ${escapeAddressHtml(addr.city || '')}, ${escapeAddressHtml(addr.state || '')} <span class="text-nowrap font-monospace text-dark fw-bold">- ${escapeAddressHtml(safeZip)}</span>
                            ${addr.landmark ? `<span class="text-muted small d-block mt-0.5"><i class="bi bi-geo-alt"></i> Landmark: ${escapeAddressHtml(addr.landmark)}</span>` : ''}
                        </div>
                        <div class="small text-muted text-nowrap" style="font-size: 0.82rem; white-space: nowrap !important;">
                            <i class="bi bi-telephone-fill text-muted me-1 small"></i> Mobile: <strong class="text-dark font-monospace">${escapeAddressHtml(safePhone)}</strong>
                        </div>
                    </div>
                    <div class="flex-shrink-0 align-self-end align-self-sm-center d-flex align-items-center gap-1.5 mt-2 mt-sm-0">
                        ${isSelected ? `
                            <span class="btn btn-sm btn-success rounded-pill px-3 py-1.5 fw-semibold shadow-xs text-nowrap" style="font-size: 0.78rem; pointer-events: none;">
                                <i class="bi bi-check2"></i> Selected
                            </span>
                        ` : `
                            <button type="button" class="btn btn-sm btn-outline-primary rounded-pill px-3 py-1.5 fw-semibold text-nowrap" style="font-size: 0.78rem;">
                                Select
                            </button>
                        `}
                        <button type="button" class="btn btn-sm btn-outline-secondary rounded-circle p-0 d-inline-flex align-items-center justify-content-center" style="width: 34px; height: 34px;" onclick="editCheckoutAddress(event, ${addr.id})" title="Edit Address">
                            <i class="bi bi-pencil small"></i>
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-danger rounded-circle p-0 d-inline-flex align-items-center justify-content-center" style="width: 34px; height: 34px;" onclick="deleteCheckoutAddress(event, ${addr.id}, this)" title="Delete Address">
                            <i class="bi bi-trash small"></i>
                        </button>
                    </div>
                </div>
            `;
            col.appendChild(card);
            listEl.appendChild(col);
        });
    }

    const headerBtn = document.getElementById('btn-saved-addresses-header');
    const countText = document.getElementById('saved-addresses-count-text');
    const switchBtn = document.getElementById('btn-switch-address');
    const closeHeaderBtn = document.getElementById('btn-close-new-address-header');
    const cancelBtn = document.getElementById('btn-cancel-new-address');

    if (countText) countText.textContent = `Saved Addresses (${userAddresses.length})`;
    if (headerBtn) headerBtn.style.display = userAddresses.length > 0 ? 'inline-block' : 'none';
    if (switchBtn) switchBtn.style.display = userAddresses.length > 1 ? 'inline-block' : 'none';
    if (closeHeaderBtn) closeHeaderBtn.style.display = userAddresses.length > 0 ? 'inline-block' : 'none';
    if (cancelBtn) cancelBtn.style.display = userAddresses.length > 0 ? 'inline-block' : 'none';
}

function handleSaveAddress(e) {
    if (e) e.preventDefault();

    const editAddressId = (document.getElementById('edit_address_id')?.value || '').trim();
    const isEdit = Boolean(editAddressId);

    const nameVal = (document.getElementById('new_name')?.value || '').trim();
    const phoneVal = (document.getElementById('new_phone')?.value || '').trim();
    const houseNoVal = (document.getElementById('new_house_no')?.value || '').trim();
    const streetVal = (document.getElementById('new_street')?.value || '').trim();
    const landmarkVal = (document.getElementById('new_landmark')?.value || '').trim();
    const zipVal = (document.getElementById('new_zip')?.value || '').trim();
    const cityVal = (document.getElementById('new_city')?.value || '').trim();
    const stateVal = (document.getElementById('new_state')?.value || '').trim();
    const latVal = (document.getElementById('new_latitude')?.value || '').trim();
    const lngVal = (document.getElementById('new_longitude')?.value || '').trim();
    const typeVal = document.querySelector('input[name="new_type"]:checked')?.value || 'home';

    if (!nameVal || !phoneVal || !houseNoVal || !streetVal || !zipVal || !cityVal || !stateVal) {
        Swal.fire({
            icon: 'warning',
            title: 'Incomplete Address',
            text: 'Please fill in Full Name, Mobile Number, Flat/House No, Street/Road/Area, 6-digit PIN Code, City, and State.',
            confirmButtonColor: '#4f46e5'
        });
        return;
    }

    if (phoneVal.length !== 10) {
        Swal.fire({
            icon: 'warning',
            title: 'Invalid Mobile Number',
            text: 'Please enter a valid 10-digit mobile number.',
            confirmButtonColor: '#4f46e5'
        });
        return;
    }

    if (zipVal.length !== 6) {
        Swal.fire({
            icon: 'warning',
            title: 'Invalid PIN Code',
            text: 'Please enter a valid 6-digit PIN code.',
            confirmButtonColor: '#4f46e5'
        });
        return;
    }

    if (!isCheckoutPincodeValidated && !isEdit) {
        Swal.fire({
            icon: 'warning',
            title: 'PIN Code Not Validated',
            text: 'Please enter a valid 6-digit PIN code and allow it to detect your City & State before saving.',
            confirmButtonColor: '#4f46e5'
        });
        showDismissableCheckoutPincodeError('PIN code must be verified before saving.');
        return;
    }

    const btn = document.getElementById('btn-save-address');
    const origText = btn ? btn.innerHTML : (isEdit ? '<i class="bi bi-check2-circle me-1"></i> Update Address' : '<i class="bi bi-bookmark-check me-1"></i> Save Address');
    if (btn) {
        btn.disabled = true;
        btn.innerHTML = `<span class="spinner-border spinner-border-sm me-1"></span> ${isEdit ? 'Updating...' : 'Saving...'}`;
    }

    const fullStreetAddress = [houseNoVal, streetVal, landmarkVal].filter(Boolean).join(', ');

    const payload = {
        name: nameVal,
        phone: phoneVal,
        house_no: houseNoVal,
        street: streetVal,
        landmark: landmarkVal || null,
        address: fullStreetAddress,
        city: cityVal,
        state: stateVal,
        zip: zipVal,
        latitude: latVal ? parseFloat(latVal) : null,
        longitude: lngVal ? parseFloat(lngVal) : null,
        type: typeVal,
        country: 'India',
        is_default: true
    };

    const targetUrl = isEdit ? `/account/addresses/${editAddressId}` : '{{ route("checkout.save-address") }}';
    const requestHeaders = {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': '{{ csrf_token() }}',
        'Accept': 'application/json'
    };
    if (isEdit) {
        requestHeaders['X-HTTP-Method-Override'] = 'PUT';
    }

    fetch(targetUrl, {
        method: 'POST',
        headers: requestHeaders,
        body: JSON.stringify(payload)
    })
    .then(async res => {
        const data = await res.json();
        if (!res.ok) {
            throw new Error(data.message || (isEdit ? 'Could not update address.' : 'Could not save address.'));
        }
        return data;
    })
    .then(async (data) => {
        if (btn) {
            btn.disabled = false;
            btn.innerHTML = origText;
        }
        if (data.success || data.address) {
            const savedAddr = data.address || payload;
            if (isEdit && !savedAddr.id) savedAddr.id = Number(editAddressId);

            if (isEdit) {
                const idx = userAddresses.findIndex(a => Number(a.id) === Number(editAddressId));
                if (idx !== -1) {
                    userAddresses[idx] = Object.assign({}, userAddresses[idx], savedAddr);
                } else {
                    userAddresses.unshift(savedAddr);
                }
            } else {
                if (Array.isArray(data.all_addresses)) {
                    userAddresses = data.all_addresses;
                } else {
                    userAddresses.unshift(savedAddr);
                }
            }

            renderModalAddressList();
            const servicePromise = applySelectedAddress(savedAddr);
            closeNewAddressForm();

            const isServiceable = await servicePromise;

            if (isServiceable) {
                const successBanner = document.getElementById('address-success-banner');
                if (successBanner) {
                    const spanEl = successBanner.querySelector('span');
                    if (spanEl) spanEl.textContent = isEdit ? 'Address updated successfully!' : 'Address saved successfully!';
                    successBanner.style.display = 'block';
                    setTimeout(() => { successBanner.style.display = 'none'; }, 3000);
                }

                Swal.fire({
                    icon: 'success',
                    title: isEdit ? 'Address Updated!' : 'Address Saved!',
                    text: data.message || (isEdit ? 'Address updated successfully.' : 'Address saved successfully.'),
                    toast: true,
                    position: 'top-end',
                    showConfirmButton: false,
                    timer: 3000,
                    timerProgressBar: true
                });

                if (!isEdit && !window.activePendingOrder) {
                    goToStep(2);
                }
            } else {
                goToStep(1);
                Swal.fire({
                    icon: 'warning',
                    title: 'Delivery Unavailable',
                    text: `Address ${isEdit ? 'updated' : 'saved'}, but ShopCalm currently does not deliver to PIN code ${savedAddr.zip}. Please select or add a serviceable delivery address.`,
                    confirmButtonColor: '#4f46e5'
                });
            }
        } else {
            Swal.fire('Error', data.message || (isEdit ? 'Could not update address.' : 'Could not save address.'), 'error');
        }
    })
    .catch(err => {
        console.error("Save address error:", err);
        if (btn) {
            btn.disabled = false;
            btn.innerHTML = origText;
        }
        Swal.fire('Error', err.message || 'Could not save address.', 'error');
    });
}

function checkPincodeServiceability(pincode) {
    const badgeEl = document.getElementById('active-address-serviceability-badge');
    const errorBanner = document.getElementById('address-error-banner');

    if (!pincode) {
        window.isCurrentAddressServiceable = false;
        return Promise.resolve(false);
    }

    if (badgeEl) {
        badgeEl.innerHTML = `<span class="badge bg-light text-muted border font-monospace"><span class="spinner-border spinner-border-sm me-1" style="width:0.6rem;height:0.6rem;"></span> Checking PIN ${pincode}...</span>`;
    }

    return fetch('{{ route("delivery.check") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json'
        },
        body: JSON.stringify({ pincode: pincode })
    })
    .then(res => res.json())
    .then(data => {

            if (data.is_serviceable) {
                window.isCurrentAddressServiceable = true;
                window.isCurrentAddressCodAvailable = (data.is_cod_available === true || data.is_cod_available === 1);
                window.activeCodFee = (data.cod_fee !== undefined) ? parseFloat(data.cod_fee) : parseFloat("{{ \App\Models\Setting::get('cod_flat_fee', 40) }}");
                updateCodAvailabilityUI(window.isCurrentAddressCodAvailable, pincode);
                if (errorBanner) errorBanner.style.display = 'none';

                // Update Amazon-Style Estimated Arrival Card & Strips
                const estText = data.estimated_delivery ? `Arriving by ${data.estimated_delivery}` : 'Standard Delivery by ShopCalm Logistics';
                const estDateEl = document.getElementById('delivery-estimated-date');
                if (estDateEl) estDateEl.textContent = estText;

                const locTextEl = document.getElementById('delivery-location-text');
                if (locTextEl) locTextEl.textContent = `${data.city || 'Your Location'}, ${pincode}`;

                const step2DateEl = document.getElementById('step2-strip-estimated-date');
                if (step2DateEl) step2DateEl.textContent = data.estimated_delivery ? `Arriving by ${data.estimated_delivery}` : 'Standard Delivery';
                const step3DateEl = document.getElementById('step3-strip-estimated-date');
                if (step3DateEl) step3DateEl.textContent = data.estimated_delivery ? `Arriving by ${data.estimated_delivery}` : 'Standard Delivery';

                const codModalArrival = document.getElementById('cod-modal-arrival-display');
                if (codModalArrival) codModalArrival.textContent = data.estimated_delivery || 'Standard Delivery (2-4 Days)';

                const codModalAddr = document.getElementById('cod-modal-address-display');
                if (codModalAddr) codModalAddr.textContent = `${data.city || ''} (${pincode})`;


            if (badgeEl) {
                badgeEl.innerHTML = `
                    <div class="d-flex align-items-center gap-2 flex-wrap text-break-word">
                        <span class="badge bg-success bg-opacity-10 text-success fw-bold rounded-pill px-2.5 py-0.5" style="font-size: 0.72rem;">
                            <i class="bi bi-truck me-1"></i> Delivery Available
                        </span>
                        ${data.estimated_delivery ? `
                            <span class="badge bg-light text-dark border rounded-pill px-2.5 py-0.5 fw-semibold" style="font-size: 0.72rem;">
                                <i class="bi bi-clock-history text-primary me-1"></i> Est. Delivery: ${escapeAddressHtml(data.estimated_delivery)}
                            </span>
                        ` : ''}
                    </div>
                `;

                const revDateTitle = document.getElementById('review-delivery-date-title');
                const revDateText = document.getElementById('review-delivery-date-text');
                if (revDateTitle) revDateTitle.textContent = `Estimated Delivery: ${data.estimated_delivery}`;
                if (revDateText) revDateText.textContent = `Delivering to ${data.city} (${data.pincode})`;
            }

            if (typeof updateShippingFeeAndTotal === 'function') {
                updateShippingFeeAndTotal(data.delivery_charge || 0, data.free_shipping_min || 499);
            }
            return true;
        } else {
            window.isCurrentAddressServiceable = false;
            window.isCurrentAddressCodAvailable = false;
            updateCodAvailabilityUI(false, pincode);
            
            if (badgeEl) {
                badgeEl.innerHTML = `
                    <span class="badge bg-danger bg-opacity-10 text-danger border border-danger rounded-pill px-2.5 py-0.5" style="font-size: 0.72rem;">
                        <i class="bi bi-x-circle-fill text-danger me-1"></i> Delivery Unavailable (${pincode})
                    </span>
                `;
            }

            if (errorBanner) {
                errorBanner.className = 'alert alert-danger small mb-3 rounded-3 border-0 bg-danger-subtle text-danger';
                errorBanner.innerHTML = `<i class="bi bi-exclamation-octagon-fill me-2"></i><strong>Delivery Unavailable:</strong> We do not deliver to PIN code <strong>${pincode}</strong>. Please choose a different address.`;
                errorBanner.style.display = 'block';
            }
            return false;
        }
    })
    .catch(err => {
        console.error("Serviceability check error:", err);
        window.isCurrentAddressServiceable = false;
        return false;
    });
}

function handleCodTileClick() {
    if (window.isCurrentAddressCodAvailable === false) {
        const pin = currentSelectedAddress?.zip || '';
        Swal.fire({
            icon: 'info',
            title: 'COD Not Available',
            text: pin ? `Cash on Delivery (COD) is not available for PIN ${pin}. Please pay securely online.` : 'Cash on Delivery (COD) is not available for this delivery address. Please pay securely online.',
            confirmButtonColor: '#4f46e5'
        });
        return;
    }
    const codRadio = document.getElementById('cod');
    if (codRadio && !codRadio.disabled) {
        codRadio.checked = true;
        codRadio.dispatchEvent(new Event('change'));
    }
}

function updateCodAvailabilityUI(isAvailable, pincode) {
    const codTile = document.getElementById('payment-tile-cod');
    const codRadio = document.getElementById('cod');
    const codBadge = document.getElementById('cod-badge');
    const codNotice = document.getElementById('cod-unavailable-notice');
    const codLabel = document.getElementById('cod-label');

    if (!isAvailable) {
        if (codTile) {
            codTile.classList.add('disabled-tile');
            codTile.classList.remove('active-tile');
        }
        if (codRadio) {
            codRadio.disabled = true;
            if (codRadio.checked) {
                const onlineRadio = document.getElementById('pay_online');
                if (onlineRadio) {
                    onlineRadio.checked = true;
                    onlineRadio.dispatchEvent(new Event('change'));
                }
            }
        }
        if (codBadge) {
            codBadge.className = 'badge bg-danger text-white rounded-pill px-2 py-0.5 fw-bold';
            codBadge.innerHTML = '<i class="bi bi-slash-circle me-1"></i>NOT AVAILABLE';
        }
        if (codLabel) {
            codLabel.classList.add('text-muted');
        }
        if (codNotice) {
            codNotice.classList.remove('d-none');
            if (pincode) {
                codNotice.innerHTML = `<i class="bi bi-slash-circle me-1"></i>COD is not available for PIN <strong>${pincode}</strong>. Please select Online Payment.`;
            }
        }
    } else {
        if (codTile) {
            codTile.classList.remove('disabled-tile');
        }
        if (codRadio) {
            codRadio.disabled = false;
        }
        if (codBadge) {
            codBadge.className = 'badge bg-light text-secondary border rounded-pill px-2 py-0.5';
            codBadge.textContent = 'PAY AT DOORSTEP';
        }
        if (codLabel) {
            codLabel.classList.remove('text-muted');
        }
        if (codNotice) {
            codNotice.classList.add('d-none');
        }
    }
}

// ==============================================================
// LEAFLET INTERACTIVE MAP & DUAL LOCATION PROVIDER (FREE OSM + MULTI-KEY LOCATIONIQ POOL)
// ==============================================================
const LOCATIONIQ_KEYS = @json(\App\Services\LocationiqService::getActiveKeys());
const LOCATIONIQ_KEY = (Array.isArray(LOCATIONIQ_KEYS) && LOCATIONIQ_KEYS.length > 0) ? LOCATIONIQ_KEYS[0] : "{{ \App\Models\Setting::get('locationiq_api_key') ?: (config('services.locationiq.key') ?: 'pk.8fcbbfbdafcf2d840b41ffd3c60e443f') }}";
let deliveryMap = null;
let deliveryMarker = null;
let currentMapCoords = { lat: 12.971599, lng: 77.594566 }; // Default Bengaluru (MG Road / Central)
let isGeocoding = false;
let searchDebounceTimer = null;

function openLocationPickerModal(forceGps = false) {
    try {
        const modalEl = document.getElementById('locationPickerModal');
        if (!modalEl) {
            console.error('locationPickerModal element not found');
            return;
        }
        let modal = bootstrap.Modal.getInstance(modalEl);
        if (!modal) {
            modal = new bootstrap.Modal(modalEl);
        }
        modal.show();
        setTimeout(() => {
            if (deliveryMap) {
                deliveryMap.invalidateSize();
                if (forceGps) {
                    recenterToGpsLocation();
                }
            }
        }, 200);
    } catch (e) {
        console.error('Error opening locationPickerModal:', e);
    }
}

document.addEventListener('DOMContentLoaded', function () {
    const modalEl = document.getElementById('locationPickerModal');
    if (modalEl) {
        modalEl.addEventListener('shown.bs.modal', function () {
            initOrRefreshMap();
            setTimeout(() => {
                if (deliveryMap) {
                    deliveryMap.invalidateSize();
                    if (deliveryMarker) {
                        deliveryMap.setView(deliveryMarker.getLatLng(), deliveryMap.getZoom());
                    }
                }
            }, 250);
        });
    }

    // Place Search setup
    const searchInput = document.getElementById('map-search-input');
    const clearBtn = document.getElementById('btn-clear-map-search');
    const resultsContainer = document.getElementById('map-search-results');

    if (searchInput) {
        searchInput.addEventListener('input', function () {
            const q = this.value.trim();
            if (clearBtn) clearBtn.classList.toggle('d-none', q.length === 0);

            clearTimeout(searchDebounceTimer);
            if (q.length < 3) {
                if (resultsContainer) {
                    resultsContainer.innerHTML = '';
                    resultsContainer.classList.add('d-none');
                }
                return;
            }

            searchDebounceTimer = setTimeout(() => {
                searchPlaces(q);
            }, 350);
        });
    }
});

function initOrRefreshMap() {
    if (typeof L === 'undefined') {
        console.warn('Leaflet library not loaded yet');
        return;
    }

    if (!deliveryMap) {
        deliveryMap = L.map('delivery-map-container', {
            zoomControl: false
        }).setView([currentMapCoords.lat, currentMapCoords.lng], 15);

        L.control.zoom({ position: 'bottomright' }).addTo(deliveryMap);

        // 100% Free OpenStreetMap Standard Tiles (No API key needed, reliable global edge CDN)
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            subdomains: ['a', 'b', 'c'],
            maxZoom: 19,
            attribution: '&copy; <a href="https://www.openstreetmap.org/copyright" target="_blank">OpenStreetMap</a>'
        }).addTo(deliveryMap);

        // Sharp Pin Icon: Tip (17, 44) aligns directly to exact (lat, lng) without extra transform offsets
        const pinIcon = L.divIcon({
            className: 'custom-map-pin',
            html: '<div style="pointer-events: none; width: 34px; height: 44px; position: relative; margin: 0; padding: 0;">' +
                  '<svg width="34" height="44" viewBox="0 0 384 512" style="display: block; filter: drop-shadow(0 3px 5px rgba(0,0,0,0.35));">' +
                  '<path fill="#dc2626" d="M172.268 501.67C26.97 291.031 0 269.413 0 192 0 85.961 85.961 0 192 0s192 85.961 192 192c0 77.413-26.97 99.031-172.268 309.67-9.535 13.774-29.93 13.773-39.464 0zM192 272c44.183 0 80-35.817 80-80s-35.817-80-80-80-80 35.817-80 80 35.817 80 80 80z"/>' +
                  '</svg>' +
                  '<div style="width: 8px; height: 8px; background: rgba(0,0,0,0.3); border-radius: 50%; position: absolute; left: 13px; bottom: -3px;"></div>' +
                  '</div>',
            iconSize: [34, 44],
            iconAnchor: [17, 44]
        });

        deliveryMarker = L.marker([currentMapCoords.lat, currentMapCoords.lng], {
            draggable: true,
            icon: pinIcon
        }).addTo(deliveryMap);

        deliveryMarker.on('dragend', function () {
            const pos = deliveryMarker.getLatLng();
            currentMapCoords = { lat: pos.lat, lng: pos.lng };
            reverseGeocode(pos.lat, pos.lng);
        });

        deliveryMap.on('click', function (e) {
            deliveryMarker.setLatLng(e.latlng);
            currentMapCoords = { lat: e.latlng.lat, lng: e.latlng.lng };
            reverseGeocode(e.latlng.lat, e.latlng.lng);
        });

        setTimeout(() => {
            if (deliveryMap) deliveryMap.invalidateSize();
        }, 150);

        recenterToGpsLocation();
    } else {
        deliveryMap.invalidateSize();
        if (deliveryMarker) {
            deliveryMap.setView(deliveryMarker.getLatLng(), deliveryMap.getZoom());
        }
    }
}

function recenterToGpsLocation() {
    const locateBtn = document.getElementById('btn-locate-me');
    if (locateBtn) locateBtn.innerHTML = '<span class="spinner-border spinner-border-sm" style="width:0.9rem;height:0.9rem;"></span>';

    const permHint = document.getElementById('map-perm-hint');
    if (permHint) permHint.classList.add('d-none');

    if (!navigator.geolocation) {
        console.warn("navigator.geolocation not supported on this device/browser");
        fetchNetworkIpLocation();
        return;
    }

    // Phase 1: High-accuracy hardware GPS (Mobile GPS / Location sensor)
    navigator.geolocation.getCurrentPosition(
        function (pos) {
            applyTargetPosition(pos.coords.latitude, pos.coords.longitude, pos.coords.accuracy);
        },
        function (err) {
            console.warn("High-accuracy GPS attempt failed (code " + err.code + "), trying standard triangulation...");
            // Phase 2: Standard Wi-Fi / Cell tower triangulation
            navigator.geolocation.getCurrentPosition(
                function (pos2) {
                    applyTargetPosition(pos2.coords.latitude, pos2.coords.longitude, pos2.coords.accuracy);
                },
                function (err2) {
                    console.warn("Standard geolocation failed (code " + err2.code + "), falling back to network IP location...");
                    if (err2.code === 1 && permHint) {
                        permHint.classList.remove('d-none');
                    }
                    fetchNetworkIpLocation();
                },
                { enableHighAccuracy: false, timeout: 6000, maximumAge: 60000 }
            );
        },
        { enableHighAccuracy: true, timeout: 6000, maximumAge: 0 }
    );
}

function applyTargetPosition(lat, lng, accuracy) {
    const locateBtn = document.getElementById('btn-locate-me');
    if (locateBtn) locateBtn.innerHTML = '<i class="bi bi-crosshair fs-5"></i>';

    currentMapCoords = { lat, lng };
    const zoomLevel = (accuracy && accuracy <= 150) ? 18 : 16;

    if (deliveryMap) {
        deliveryMap.setView([lat, lng], zoomLevel);
        if (deliveryMarker) {
            deliveryMarker.setLatLng([lat, lng]);
        }
        setTimeout(() => deliveryMap.invalidateSize(), 150);
    }
    reverseGeocode(lat, lng);
}

async function fetchNetworkIpLocation() {
    const locateBtn = document.getElementById('btn-locate-me');
    try {
        const res = await fetch('https://ipwho.is/');
        const data = await res.json();
        if (data && data.success && data.latitude && data.longitude) {
            applyTargetPosition(data.latitude, data.longitude, 2000);
            return;
        }
    } catch (e) {
        console.warn("ipwho.is failed, trying ipapi.co fallback:", e);
    }

    try {
        const res2 = await fetch('https://ipapi.co/json/');
        const data2 = await res2.json();
        if (data2 && data2.latitude && data2.longitude) {
            applyTargetPosition(data2.latitude, data2.longitude, 2000);
            return;
        }
    } catch (e2) {
        console.warn("ipapi.co failed:", e2);
    }

    if (locateBtn) locateBtn.innerHTML = '<i class="bi bi-crosshair fs-5"></i>';
    reverseGeocode(currentMapCoords.lat, currentMapCoords.lng);
}

function sanitizeToEnglish(str) {
    if (!str || typeof str !== 'string') return '';
    // If dual language e.g. 'Road / ರಸ್ತೆ' or 'ರಸ್ತೆ (Road)' or 'Road (ರಸ್ತೆ)'
    if (str.includes('/') || str.includes('(')) {
        const parts = str.split(/[/()]/).map(s => s.trim()).filter(Boolean);
        const latinPart = parts.find(p => /[A-Za-z0-9]/.test(p));
        if (latinPart) str = latinPart;
    }
    // If text contains English/Latin characters along with Indic script, strip Indic characters
    if (/[A-Za-z]/.test(str)) {
        return str.replace(/[\u0900-\u0D7F]/g, '').replace(/\s{2,}/g, ' ').trim();
    }
    // If entirely Indic script (no Latin letters)
    if (/^[\u0900-\u0D7F\s,.-]+$/.test(str)) {
        return '';
    }
    return str.trim();
}

function extractAddressComponents(addr, displayName) {
    if (!addr && !displayName) return null;
    addr = addr || {};

    const rawBuilding = addr.building || addr.house_name || addr.house_number || '';
    const rawRoad = addr.road || addr.street || addr.footway || addr.path || '';
    const rawColony = addr.colony || addr.neighbourhood || addr.subdivision || addr.quarter || '';
    const rawSuburb = addr.suburb || addr.residential || addr.commercial || addr.industrial || '';
    const rawVillage = addr.village || addr.hamlet || addr.locality || '';
    
    const building = sanitizeToEnglish(rawBuilding);
    const road = sanitizeToEnglish(rawRoad);
    const colony = sanitizeToEnglish(rawColony);
    const suburb = sanitizeToEnglish(rawSuburb);
    const village = sanitizeToEnglish(rawVillage);

    const roadParts = [building, road, colony, suburb, village].filter(Boolean);
    const uniqueRoadParts = [...new Set(roadParts)];
    
    let roadStr = uniqueRoadParts.join(', ');
    if (!roadStr && displayName) {
        const latinDisplayName = sanitizeToEnglish(displayName);
        roadStr = latinDisplayName ? latinDisplayName.split(',').slice(0, 3).join(',').trim() : '';
    }

    let city = sanitizeToEnglish(addr.city || addr.town || addr.municipality || addr.village || addr.county || addr.state_district || '');
    let state = sanitizeToEnglish(addr.state || '');
    const zip = (addr.postcode || '').replace(/\D/g, '').slice(0, 6);

    // If city or state were stripped because they were in regional script, extract from English displayName
    if ((!city || !state) && displayName) {
        const parts = displayName.split(',').map(s => sanitizeToEnglish(s)).filter(Boolean);
        if (!city && parts.length >= 2) city = parts[parts.length - 3] || parts[parts.length - 2];
        if (!state && parts.length >= 1) state = parts[parts.length - 2] || parts[parts.length - 1];
    }

    return { roadStr, city, state, zip };
}

async function reverseGeocode(lat, lng) {
    if (isGeocoding) return;
    isGeocoding = true;

    const areaEl = document.getElementById('map-detected-area');
    const zipBadge = document.getElementById('map-detected-zip');
    const cityStateEl = document.getElementById('map-detected-city-state');

    if (areaEl) areaEl.innerHTML = '<span class="spinner-border spinner-border-sm me-1 text-primary" style="width:0.7rem;height:0.7rem;"></span> Detecting address...';
    if (zipBadge) zipBadge.textContent = '...';

    let addressData = null;

    // 1. Try LocationIQ key pool with automatic failover on 429 / network error
    const candidateKeys = (Array.isArray(LOCATIONIQ_KEYS) && LOCATIONIQ_KEYS.length > 0) ? LOCATIONIQ_KEYS : (LOCATIONIQ_KEY ? [LOCATIONIQ_KEY] : []);
    for (const key of candidateKeys) {
        if (!key || key === 'pk.your_api_key_here') continue;
        try {
            const url = 'https://us1.locationiq.com/v1/reverse?key=' + encodeURIComponent(key) + '&lat=' + lat + '&lon=' + lng + '&format=json&accept-language=en&addressdetails=1&normalizeaddress=1';
            const res = await fetch(url, {
                headers: { 'Accept-Language': 'en' }
            });
            if (res.status === 429) {
                console.warn('LocationIQ key quota reached, trying next pooled key...');
                continue;
            }
            if (res.ok) {
                const data = await res.json();
                if (data && (data.address || data.display_name)) {
                    addressData = extractAddressComponents(data.address, data.display_name);
                    break;
                }
            }
        } catch (e) {
            console.warn('LocationIQ reverse failed, trying next pooled key:', e);
        }
    }

    // 2. Free OpenStreetMap Nominatim Fallback with STRICT English output (&accept-language=en)
    if (!addressData) {
        try {
            const nomUrl = 'https://nominatim.openstreetmap.org/reverse?format=jsonv2&lat=' + lat + '&lon=' + lng + '&accept-language=en';
            const res = await fetch(nomUrl, {
                headers: {
                    'User-Agent': 'ShopCalm-App/1.0 (contact@shopcalm.in)',
                    'Accept-Language': 'en'
                }
            });
            if (res.ok) {
                const data = await res.json();
                if (data && (data.address || data.display_name)) {
                    addressData = extractAddressComponents(data.address, data.display_name);
                }
            }
        } catch (e) {
            console.warn('Nominatim reverse failed:', e);
        }
    }

    if (addressData) {
        if (areaEl) areaEl.textContent = addressData.roadStr || addressData.city || 'Location Pin Dropped';
        if (zipBadge) zipBadge.textContent = addressData.zip ? addressData.zip : 'PIN --';
        if (cityStateEl) cityStateEl.textContent = [addressData.city, addressData.state].filter(Boolean).join(', ');

        const roadInput = document.getElementById('map_road_area');
        const cityInput = document.getElementById('map_city');
        const stateInput = document.getElementById('map_state');
        const zipInput = document.getElementById('map_zip_code');

        if (roadInput) roadInput.value = addressData.roadStr;
        if (cityInput) cityInput.value = addressData.city;
        if (stateInput) stateInput.value = addressData.state;
        if (zipInput && addressData.zip) zipInput.value = addressData.zip;
    } else {
        if (areaEl) areaEl.textContent = 'Pin Location Selected';
        if (cityStateEl) cityStateEl.textContent = 'Please confirm your house no and PIN.';
    }

    isGeocoding = false;
}

async function searchPlaces(query) {
    const resultsContainer = document.getElementById('map-search-results');
    if (!resultsContainer) return;

    let list = [];

    // 1. Try LocationIQ key pool with automatic failover
    const candidateSearchKeys = (Array.isArray(LOCATIONIQ_KEYS) && LOCATIONIQ_KEYS.length > 0) ? LOCATIONIQ_KEYS : (LOCATIONIQ_KEY ? [LOCATIONIQ_KEY] : []);
    for (const key of candidateSearchKeys) {
        if (!key || key === 'pk.your_api_key_here') continue;
        try {
            const url = 'https://api.locationiq.com/v1/autocomplete?key=' + encodeURIComponent(key) + '&q=' + encodeURIComponent(query) + '&countrycodes=in&accept-language=en&limit=5&format=json';
            const res = await fetch(url);
            if (res.status === 429) {
                console.warn('LocationIQ autocomplete quota reached, trying next pooled key...');
                continue;
            }
            if (res.ok) {
                const data = await res.json();
                if (Array.isArray(data) && data.length > 0) {
                    list = data;
                    break;
                }
            }
        } catch (e) {
            console.warn('LocationIQ search failed, trying next pooled key:', e);
        }
    }

    // 2. Free OpenStreetMap Nominatim Search Fallback (100% Free, No Key Required)
    if (list.length === 0) {
        try {
            const nomUrl = 'https://nominatim.openstreetmap.org/search?format=jsonv2&q=' + encodeURIComponent(query) + '&countrycodes=in&accept-language=en&limit=5';
            const res = await fetch(nomUrl, { headers: { 'User-Agent': 'ShopCalm-App/1.0 (contact@shopcalm.in)' } });
            if (res.ok) {
                const data = await res.json();
                if (Array.isArray(data)) {
                    list = data;
                }
            }
        } catch (e) {
            console.warn('Nominatim search failed:', e);
        }
    }

    if (list.length > 0) {
        resultsContainer.innerHTML = list.map(item => {
            const cleanName = sanitizeToEnglish(item.display_name || '');
            const escapedName = cleanName.replace(/'/g, "\\'").replace(/"/g, '&quot;');
            const lat = item.lat;
            const lon = item.lon || item.lng;
            return '<button type="button" class="list-group-item list-group-item-action py-2 px-3 d-flex align-items-center gap-2 border-0 border-bottom" onclick="selectSearchResult(' + lat + ', ' + lon + ', \'' + escapedName + '\')">' +
                '<i class="bi bi-geo-alt text-primary flex-shrink-0"></i>' +
                '<span class="text-truncate">' + cleanName + '</span>' +
            '</button>';
        }).join('');
        resultsContainer.classList.remove('d-none');
    } else {
        resultsContainer.classList.add('d-none');
    }
}

function selectSearchResult(lat, lon, displayName) {
    const searchInput = document.getElementById('map-search-input');
    const resultsContainer = document.getElementById('map-search-results');
    const cleanName = sanitizeToEnglish(displayName);
    if (searchInput) searchInput.value = cleanName.split(',')[0] || cleanName;
    if (resultsContainer) resultsContainer.classList.add('d-none');

    currentMapCoords = { lat: parseFloat(lat), lng: parseFloat(lon) };
    if (deliveryMap) {
        deliveryMap.flyTo([lat, lon], 16);
        if (deliveryMarker) {
            deliveryMarker.setLatLng([lat, lon]);
        }
        setTimeout(() => deliveryMap.invalidateSize(), 150);
    }
    reverseGeocode(lat, lon);
}

function clearMapSearch() {
    const searchInput = document.getElementById('map-search-input');
    const clearBtn = document.getElementById('btn-clear-map-search');
    const resultsContainer = document.getElementById('map-search-results');
    if (searchInput) searchInput.value = '';
    if (clearBtn) clearBtn.classList.add('d-none');
    if (resultsContainer) {
        resultsContainer.innerHTML = '';
        resultsContainer.classList.add('d-none');
    }
}

async function saveMapAddressToCheckout() {
    const btn = document.getElementById('btn-save-map-address');
    const origHtml = btn.innerHTML;

    const houseNo = document.getElementById('map_house_no')?.value.trim();
    const roadArea = document.getElementById('map_road_area')?.value.trim();
    const landmark = document.getElementById('map_landmark')?.value.trim();
    const city = document.getElementById('map_city')?.value.trim();
    const state = document.getElementById('map_state')?.value.trim();
    const zip = document.getElementById('map_zip_code')?.value.trim();
    const name = document.getElementById('map_name')?.value.trim();
    const phone = document.getElementById('map_phone')?.value.trim();
    const typeEl = document.querySelector('input[name="map_address_type"]:checked');
    const type = typeEl ? typeEl.value : 'home';

    if (!houseNo) {
        Swal.fire({
            icon: 'warning',
            title: 'House / Flat No Required',
            text: 'Please enter your Flat / House or Building Number to help the delivery partner find your doorstep.',
            confirmButtonColor: '#4f46e5'
        });
        document.getElementById('map_house_no')?.focus();
        return;
    }

    if (!zip || zip.length !== 6 || !/^\d{6}$/.test(zip)) {
        Swal.fire({
            icon: 'warning',
            title: 'Valid 6-Digit PIN Required',
            text: 'Please ensure a valid 6-digit Indian PIN code is entered.',
            confirmButtonColor: '#4f46e5'
        });
        document.getElementById('map_zip_code')?.focus();
        return;
    }

    if (!name) {
        Swal.fire('Error', 'Please enter receiver full name.', 'error');
        document.getElementById('map_name')?.focus();
        return;
    }

    if (!phone || phone.length !== 10 || !/^\d{10}$/.test(phone)) {
        Swal.fire({
            icon: 'warning',
            title: '10-Digit Mobile Required',
            text: 'Please enter a valid 10-digit mobile number for delivery updates.',
            confirmButtonColor: '#4f46e5'
        });
        document.getElementById('map_phone')?.focus();
        return;
    }

    const fullStreetAddress = [houseNo, roadArea, landmark].filter(Boolean).join(', ');

    const payload = {
        name: name,
        phone: phone,
        house_no: houseNo,
        street: roadArea,
        landmark: landmark || null,
        address: fullStreetAddress,
        city: city || 'City',
        state: state || 'State',
        zip: zip,
        country: 'India',
        latitude: currentMapCoords.lat,
        longitude: currentMapCoords.lng,
        type: type,
        is_default: true
    };

    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Saving Location...';

    try {
        const res = await fetch('{{ route("checkout.save-address") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            },
            body: JSON.stringify(payload)
        });

        const data = await res.json();
        if (!res.ok) {
            throw new Error(data.message || 'Could not save address.');
        }

        btn.disabled = false;
        btn.innerHTML = origHtml;

        const modalEl = document.getElementById('locationPickerModal');
        const modalInstance = bootstrap.Modal.getInstance(modalEl);
        if (modalInstance) modalInstance.hide();

        const savedAddr = data.address || payload;
        if (Array.isArray(data.all_addresses)) {
            userAddresses = data.all_addresses;
        } else {
            userAddresses.unshift(savedAddr);
        }
        renderModalAddressList();
        const isServiceable = await applySelectedAddress(savedAddr);

        if (isServiceable) {
            const successBanner = document.getElementById('address-success-banner');
            if (successBanner) {
                successBanner.style.display = 'block';
                setTimeout(() => { successBanner.style.display = 'none'; }, 3000);
            }
            if (!window.activePendingOrder) {
                goToStep(2);
            }
        } else {
            goToStep(1);
            Swal.fire({
                icon: 'warning',
                title: 'Delivery Unavailable',
                text: 'Location saved, but ShopCalm currently does not deliver to PIN code ' + savedAddr.zip + '. Please select or add a serviceable delivery address.',
                confirmButtonColor: '#4f46e5'
            });
        }
    } catch (err) {
        console.error("Save map address error:", err);
        btn.disabled = false;
        btn.innerHTML = origHtml;
        Swal.fire('Error', err.message || 'Could not save address.', 'error');
    }
}

// Auto-clear placeholder on focus / click so example text disappears immediately
document.addEventListener('focus', function(e) {
    if (e.target && (e.target.tagName === 'INPUT' || e.target.tagName === 'TEXTAREA') && e.target.placeholder) {
        e.target.dataset.originalPlaceholder = e.target.placeholder;
        e.target.placeholder = '';
    }
}, true);

document.addEventListener('blur', function(e) {
    if (e.target && (e.target.tagName === 'INPUT' || e.target.tagName === 'TEXTAREA') && e.target.dataset.originalPlaceholder) {
        if (!e.target.value) {
            e.target.placeholder = e.target.dataset.originalPlaceholder;
        }
    }
}, true);

</script>
@endpush