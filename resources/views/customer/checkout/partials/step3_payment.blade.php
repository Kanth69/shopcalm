<!-- ====================================================== -->
<!-- STEP 3 VIEW: PAYMENT METHOD SELECTION                 -->
<!-- ====================================================== -->
<div id="checkout-step-3-view" class="d-none">

    <!-- Payment Methods Card -->
    <div class="checkout-card mb-3.5">
        <div class="checkout-card-header d-flex align-items-center justify-content-between flex-wrap gap-2">
            <div class="d-flex align-items-center gap-2">
                <div class="rounded-circle text-white d-flex align-items-center justify-content-center flex-shrink-0" style="width: 30px; height: 30px; background: #4f46e5; font-size: 0.9rem;">
                    <i class="bi bi-credit-card-2-front-fill"></i>
                </div>
                <h6 class="fw-bold mb-0 text-dark" style="font-size: 0.98rem;">Payment Method</h6>
            </div>
            <span class="badge bg-success bg-opacity-10 text-success rounded-pill px-2.5 py-1 fw-bold d-inline-flex align-items-center gap-1" style="font-size: 0.72rem;">
                <i class="bi bi-shield-check"></i> 100% Secure
            </span>
        </div>

        <div class="checkout-card-body p-3 p-sm-3.5 position-relative" id="payment-card-body">
            <!-- In-Place Payment Recovery Alert (Global Best Practice: 0 Page Jumps) -->
            <div id="payment-recovery-inline-alert" class="alert alert-warning border-0 rounded-3 p-3 mb-3 d-none shadow-xs" style="background: #fffbeb; border: 1.5px solid #fde68a !important;">
                <div class="d-flex align-items-start gap-2.5">
                    <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0" style="width: 32px; height: 32px; background: #fef3c7; color: #d97706; font-size: 0.95rem;">
                        <i class="bi bi-arrow-repeat"></i>
                    </div>
                    <div class="flex-grow-1 min-w-0">
                        <strong class="d-block text-dark small mb-1" style="font-size: 0.88rem;">Payment Not Completed</strong>
                        <p class="text-secondary small mb-2.5" id="payment-recovery-inline-msg" style="font-size: 0.8rem; line-height: 1.45;">
                            Your payment was not completed. You can retry with UPI/Card or place your order with Cash on Delivery.
                        </p>
                        <div class="d-flex align-items-center gap-2 flex-wrap">
                            <button type="button" class="btn btn-sm btn-primary rounded-pill px-3 py-1.5 fw-bold shadow-xs d-inline-flex align-items-center gap-1" onclick="handleOrderSubmission(event)" style="font-size: 0.78rem; background: #4f46e5; border-color: #4f46e5;">
                                <i class="bi bi-arrow-clockwise"></i>
                                <span>Retry Online</span>
                            </button>
                            <button type="button" class="btn btn-sm btn-success rounded-pill px-3 py-1.5 fw-bold shadow-xs d-inline-flex align-items-center gap-1" onclick="selectCodAndSubmit()" style="font-size: 0.78rem; background: #16a34a; border-color: #16a34a;">
                                <i class="bi bi-cash"></i>
                                <span>Pay via COD</span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Disabled Overlay for 100% Wallet Payment -->
            <div id="payment-wallet-disabled-overlay" 
                 class="position-absolute top-0 start-0 w-100 h-100 rounded-4 {{ ($useWallet && $grandTotal <= 0) ? '' : 'd-none' }}"
                 style="cursor: not-allowed !important; z-index: 10; background: rgba(248, 250, 252, 0.75); backdrop-filter: blur(1px); display: flex; align-items: center; justify-content: center;"
                 title="100% covered by ShopCalm Wallet. Additional payment method not required.">
                <div class="badge bg-white text-dark border shadow-xs rounded-pill px-3 py-1.5 fw-semibold d-inline-flex align-items-center gap-1.5" style="pointer-events: none; font-size: 0.8rem;">
                    <i class="bi bi-shield-check text-success fs-6"></i>
                    <span>100% Covered by Wallet (₹0.00 Due)</span>
                </div>
            </div>

            <!-- Payment Tiles Container -->
            <div id="payment-tiles-container" class="d-flex flex-column gap-2.5" style="{{ ($useWallet && $grandTotal <= 0) ? 'opacity: 0.45; filter: grayscale(1); cursor: not-allowed !important;' : '' }}">
                
                <!-- Option 1: Razorpay Online Payment (Default) -->
                <div class="payment-tile active-tile" onclick="document.getElementById('pay_online').checked = true; document.getElementById('pay_online').dispatchEvent(new Event('change'));">
                    <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 w-100">
                        <div class="d-flex align-items-center gap-2.5 min-w-0">
                            <input class="form-check-input flex-shrink-0 cursor-pointer m-0" type="radio" name="payment_method" id="pay_online" value="online" checked form="checkout-form" style="width: 1.25rem; height: 1.25rem; accent-color: #4f46e5;">
                            <label class="form-check-label fw-bold text-dark mb-0 cursor-pointer d-inline-flex align-items-center gap-2" for="pay_online" style="font-size: 0.92rem;">
                                <i class="bi bi-credit-card text-primary fs-5"></i>
                                <span>Online Payment</span>
                            </label>
                        </div>
                        <span class="badge bg-light text-secondary border rounded-pill px-2.5 py-1 fw-semibold flex-shrink-0" style="font-size: 0.72rem;">UPI, Cards, NetBanking</span>
                    </div>
                </div>

                @php
                    $isGlobalCodEnabled = (\App\Models\Setting::get('enable_cod', '1') === '1');
                @endphp
                @if($isGlobalCodEnabled)
                <!-- Option 2: Cash On Delivery (COD) -->
                <div class="payment-tile" id="payment-tile-cod" onclick="handleCodTileClick()">
                    <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 w-100">
                        <div class="d-flex align-items-center gap-2.5 min-w-0">
                            <input class="form-check-input flex-shrink-0 cursor-pointer m-0" type="radio" name="payment_method" id="cod" value="cod" form="checkout-form" style="width: 1.25rem; height: 1.25rem; accent-color: #4f46e5;">
                            <label class="form-check-label fw-bold text-dark mb-0 cursor-pointer d-inline-flex align-items-center gap-2" for="cod" id="cod-label" style="font-size: 0.92rem;">
                                <i class="bi bi-cash text-success fs-5"></i>
                                <span>Cash on Delivery (COD)</span>
                            </label>
                        </div>
                        <span id="cod-badge" class="badge bg-light text-secondary border rounded-pill px-2.5 py-1 fw-semibold flex-shrink-0" style="font-size: 0.72rem;">PAY AT DOORSTEP</span>
                    </div>
                    <div id="cod-unavailable-notice" class="alert alert-danger py-1.5 px-2.5 small mt-2 rounded-3 d-none mb-0" style="font-size: 0.78rem;">
                        <i class="bi bi-slash-circle me-1"></i> Cash on Delivery is unavailable for this PIN code.
                    </div>
                </div>
                @endif
            </div>

            <!-- Terms & Conditions Consent (Clean, Clear Spacing, Never Overlapped) -->
            <div class="d-flex align-items-start gap-2.5 pt-3 mt-1" id="confirm-terms-wrapper">
                <input class="form-check-input m-0 mt-0.5 flex-shrink-0 cursor-pointer" type="checkbox" name="terms_consent" value="1" id="terms-consent-checkbox" style="width: 1.15rem; height: 1.15rem; accent-color: #4f46e5;">
                <label class="form-check-label text-muted small cursor-pointer ms-1" for="terms-consent-checkbox" style="font-size: 0.82rem; line-height: 1.45;">
                    I agree to the <a href="{{ route('page.terms') }}" target="_blank" class="fw-semibold text-primary text-decoration-none">Terms &amp; Conditions</a> and acknowledge the <a href="{{ route('page.return') }}" target="_blank" class="fw-semibold text-primary text-decoration-none">Return Policy</a>.
                </label>
            </div>
        </div>
    </div>
</div>
