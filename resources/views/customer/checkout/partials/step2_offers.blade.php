<!-- ====================================================== -->
<!-- STEP 2 VIEW: OFFERS, COUPONS & WALLET SAVINGS          -->
<!-- ====================================================== -->
<div id="checkout-step-2-view" class="d-none">

    <!-- 1. ShopCalm Wallet Card -->
    @if(isset($userWallet))
    <div class="checkout-card mb-3.5" style="border-color: #bbf7d0 !important;">
        <div class="checkout-card-header d-flex align-items-center justify-content-between flex-wrap gap-2" style="background: #f0fdf4;">
            <div class="d-flex align-items-center gap-2">
                <div class="rounded-circle text-success d-flex align-items-center justify-content-center flex-shrink-0" style="width: 30px; height: 30px; background: #dcfce7; font-size: 0.95rem;">
                    <i class="bi bi-wallet2"></i>
                </div>
                <h6 class="fw-bold mb-0 text-dark" style="font-size: 0.98rem;">ShopCalm Wallet</h6>
            </div>
            @if($userWallet->balance > 0)
                <span class="badge bg-success text-white rounded-pill px-2.5 py-1 fw-bold font-monospace" style="font-size: 0.78rem;">
                    ₹{{ number_format($userWallet->balance, 2) }} Available
                </span>
            @else
                <span class="badge bg-light text-secondary border rounded-pill px-2 py-0.5" style="font-size: 0.72rem;">
                    ₹0.00 Balance
                </span>
            @endif
        </div>

        <div class="checkout-card-body p-3 p-sm-3.5">
            <div class="d-flex align-items-center justify-content-between gap-3 flex-wrap">
                <div class="min-w-0 flex-grow-1">
                    <p class="text-secondary mb-0 small" style="font-size: 0.84rem; line-height: 1.45;">
                        @if($isWalletFrozen)
                            <span class="text-danger fw-semibold"><i class="bi bi-lock-fill me-1"></i>Wallet is currently locked.</span>
                        @elseif($userWallet->balance > 0)
                            <span>Use wallet balance to reduce payable amount.</span>
                        @else
                            <span>Balance is ₹0.00. Earn cashback &amp; credits on orders and returns.</span>
                        @endif
                    </p>
                </div>
                @if($userWallet->balance > 0 && !$isWalletFrozen)
                <div class="form-check form-switch mb-0 flex-shrink-0 ms-auto">
                    <input class="form-check-input custom-wallet-switch" type="checkbox" role="switch" id="toggle-wallet-switch" {{ $useWallet ? 'checked' : '' }} style="cursor: pointer; width: 2.6em; height: 1.35em;">
                </div>
                @endif
            </div>

            <!-- 100% Wallet Paid Notice Banner -->
            <div id="full-wallet-pay-notice" class="alert alert-success border-0 rounded-3 mt-3 mb-0 p-2.5 shadow-xs {{ ($useWallet && $grandTotal <= 0) ? '' : 'd-none' }}" style="background: #dcfce7; color: #166534;">
                <div class="d-flex align-items-center gap-2">
                    <i class="bi bi-shield-check fs-5 text-success flex-shrink-0"></i>
                    <div>
                        <strong class="small d-block mb-0.5" style="font-size: 0.88rem;">100% Covered by Wallet!</strong>
                        <span class="small" style="font-size: 0.78rem;">Your entire order is covered by wallet balance (₹0.00 due).</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- 2. Coupons & Promo Codes Card -->
    <div class="checkout-card mb-3.5">
        <div class="checkout-card-header d-flex align-items-center justify-content-between flex-wrap gap-2">
            <div class="d-flex align-items-center gap-2">
                <div class="rounded-circle text-primary d-flex align-items-center justify-content-center flex-shrink-0" style="width: 30px; height: 30px; background: rgba(79, 70, 229, 0.1); font-size: 0.9rem;">
                    <i class="bi bi-ticket-perforated-fill"></i>
                </div>
                <h6 class="fw-bold mb-0 text-dark" style="font-size: 0.98rem;">Apply Coupon</h6>
            </div>
            @if(isset($availableCoupons) && $availableCoupons->count() > 0)
                <button type="button" class="btn btn-sm btn-link text-primary text-decoration-none fw-semibold p-0" data-bs-toggle="modal" data-bs-target="#couponsModal" style="font-size: 0.8rem;">
                    <i class="bi bi-tag text-warning me-1"></i>View Coupons ({{ $availableCoupons->count() }})
                </button>
            @endif
        </div>

        <div class="checkout-card-body p-3 p-sm-3.5">
            <div id="coupon-status-wrapper">
                @if(!empty($couponCode))
                    <div class="p-2.5 rounded-3 d-flex align-items-center justify-content-between bg-white border" id="applied-coupon-box" style="border-color: #86efac !important; background: #f0fdf4 !important;">
                        <div class="d-flex align-items-center gap-2">
                            <i class="bi bi-check-circle-fill text-success fs-5"></i>
                            <div>
                                <span class="font-monospace fw-bold text-success px-2 py-0.5 rounded" style="background: #dcfce7; border: 1px dashed #22c55e; font-size: 0.8rem;">{{ $couponCode }}</span>
                                <div class="text-dark small fw-semibold mt-0.5" style="font-size: 0.8rem;">Saved ₹{{ number_format($discountAmount ?? 0, 2) }}!</div>
                            </div>
                        </div>
                        <button type="button" class="btn btn-sm btn-outline-danger rounded-pill px-2.5 py-0.5 fw-semibold" id="btn-remove-coupon-ajax" onclick="handleRemoveCoupon()" style="font-size: 0.74rem;">Remove</button>
                    </div>
                @else
                    <div class="input-group" id="coupon-input-group" style="max-width: 460px;">
                        <input type="text" id="coupon_code_input" class="form-control text-uppercase font-monospace" placeholder="Enter coupon code" style="font-size: 0.86rem; height: 42px;">
                        <button type="button" class="btn btn-primary px-3.5 fw-bold" id="btn-apply-coupon-ajax" onclick="handleApplyCoupon()" style="font-size: 0.84rem;">Apply</button>
                    </div>
                    <div id="coupon-error-msg" class="text-danger small mt-1.5" style="font-size: 0.78rem; display: none;"></div>
                @endif
            </div>
        </div>
    </div>

    <!-- 3. Total Savings Celebration Banner at Tab End -->
    <div class="checkout-card mb-3.5 border-0 shadow-xs" style="background: linear-gradient(135deg, #f0fdf4 0%, #dcfce7 100%); border: 1.5px solid #86efac !important;">
        <div class="checkout-card-body p-3 p-sm-3.5 text-center">
            <div class="d-flex align-items-center justify-content-center gap-2 text-success fw-bold mb-1" style="font-size: 0.95rem;">
                <i class="bi bi-piggy-bank-fill fs-5"></i>
                <span>You are saving <strong class="font-monospace fw-bolder step2-total-savings-val">₹{{ number_format($totalSavings, 2) }}</strong> on this order!</span>
            </div>
            <small class="text-secondary d-block" style="font-size: 0.78rem;">
                Includes product catalog discounts, applied coupons, and wallet credits.
            </small>
        </div>
    </div>
</div>
