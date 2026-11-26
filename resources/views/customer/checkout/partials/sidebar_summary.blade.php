<!-- ============================================================== -->
<!-- RIGHT COLUMN: STICKY ORDER SUMMARY & SINGLE ACTION BUTTON      -->
<!-- ============================================================== -->
<div class="checkout-sticky-sidebar">
    
    <!-- Collapsible Summary Container (Always visible on Desktop, visible on Step 3 on Mobile) -->
    <div id="checkout-summary-cards-wrapper" class="d-none d-lg-block">
        <!-- Order Items Summary Card (Collapsible) -->
        <div class="checkout-card mb-3" id="checkout-items-summary-card">
        <div class="checkout-card-header d-flex align-items-center justify-content-between">
            <div class="d-flex align-items-center gap-2">
                <div class="rounded-circle d-flex align-items-center justify-content-center text-primary" style="width: 28px; height: 28px; background: rgba(79, 70, 229, 0.1); font-size: 0.85rem;">
                    <i class="bi bi-bag-check-fill"></i>
                </div>
                <h6 class="fw-bold mb-0 text-dark" style="font-size: 0.92rem;">Items in Order</h6>
            </div>
            <span class="badge bg-light text-secondary border rounded-pill px-2.5 py-0.5 fw-semibold" style="font-size: 0.74rem;">
                {{ $cart->items->sum('quantity') }} {{ Str::plural('item', $cart->items->sum('quantity')) }}
            </span>
        </div>

        <div class="checkout-card-body p-2.5 p-sm-3" style="max-height: 220px; overflow-y: auto;">
            <div class="d-flex flex-column gap-2.5">
                @foreach($cart->items as $item)
                @php
                    $unitPrice = (float) ($item->product->selling_price ?? $item->unit_price);
                    $itemTotal = $unitPrice * $item->quantity;
                    $itemThumb = $item->display_image ?? $item->product?->main_image;
                    $itemImage = $itemThumb ? (str_starts_with($itemThumb, 'http') ? $itemThumb : asset('storage/' . $itemThumb)) : null;
                    $itemName  = $item->product->name ?? 'Product';
                @endphp
                <div class="d-flex align-items-center gap-2.5 pb-2 border-bottom border-slate-100">
                    <div class="rounded-3 border overflow-hidden flex-shrink-0 d-flex align-items-center justify-content-center" style="width: 48px; height: 48px; background: #ffffff;">
                        @if($itemImage)
                            <img src="{{ $itemImage }}" alt="{{ $itemName }}" class="w-100 h-100 object-fit-contain" loading="lazy">
                        @else
                            <div class="w-100 h-100 d-flex align-items-center justify-content-center bg-light text-muted">
                                <i class="bi bi-image fs-5 opacity-50"></i>
                            </div>
                        @endif
                    </div>
                    <div class="flex-grow-1 min-w-0">
                        <h6 class="mb-0.5 fw-bold text-dark text-truncate" style="font-size: 0.86rem;" title="{{ $itemName }}">
                            {{ $itemName }}
                        </h6>
                        @if($item->selected_option)
                        <span class="badge bg-light text-secondary border rounded-pill px-2 py-0.5 mb-1" style="font-size: 0.68rem;">
                            {{ $item->selected_option }}
                        </span>
                        @endif
                        <div class="d-flex align-items-center gap-2 small text-muted" style="font-size: 0.76rem;">
                            <span>Qty: <strong class="text-dark">{{ $item->quantity }}</strong></span>
                            <span>&bull;</span>
                            <span class="font-monospace text-dark fw-semibold">₹{{ number_format($itemTotal, 2) }}</span>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>

    <!-- Price Details Card -->
    <div class="checkout-card mb-3">
        <div class="checkout-card-header d-flex align-items-center justify-content-between">
            <h6 class="fw-bold mb-0 text-dark d-flex align-items-center" style="font-size: 0.95rem;">
                <i class="bi bi-receipt text-primary me-2"></i> Price Details
            </h6>
            <span class="badge bg-success bg-opacity-10 text-success rounded-pill px-2.5 py-0.5 fw-bold" style="font-size: 0.72rem;">
                Safe &amp; Verified
            </span>
        </div>

        <div class="checkout-card-body">
            <!-- Items MRP Total -->
            <div class="d-flex justify-content-between align-items-center mb-2.5 small text-secondary" style="font-size: 0.86rem;">
                <span>Total MRP:</span>
                <span class="font-monospace fw-semibold text-dark">₹{{ number_format($totalMrp, 2) }}</span>
            </div>

            <!-- Discount on MRP -->
            @if($totalDiscount > 0)
            <div class="d-flex justify-content-between align-items-center mb-2.5 small text-success" id="summary-discount-row" style="font-size: 0.86rem;">
                <span>Catalog Discount:</span>
                <span class="font-monospace fw-semibold" id="summary-discount-val">- ₹{{ number_format($totalDiscount, 2) }}</span>
            </div>
            @endif

            <!-- Items Subtotal -->
            <div class="d-flex justify-content-between align-items-center mb-2.5 small text-secondary" style="font-size: 0.86rem;">
                <span>Items Subtotal:</span>
                <span class="font-monospace fw-semibold text-dark" id="summary-subtotal">₹{{ number_format($subtotal, 2) }}</span>
            </div>

            <!-- Coupon Discount -->
            <div class="d-flex justify-content-between align-items-center mb-2.5 small text-success {{ ($discountAmount ?? 0) > 0 ? '' : 'd-none' }}" id="summary-coupon-discount-row" style="font-size: 0.86rem;">
                <span>Coupon Savings:</span>
                <span class="font-monospace fw-semibold" id="summary-coupon-discount-val">- ₹{{ number_format($discountAmount ?? 0, 2) }}</span>
            </div>

            <!-- Offer Discount -->
            @if(isset($offerDiscount) && $offerDiscount > 0)
            <div class="d-flex justify-content-between align-items-center mb-2.5 small text-success" id="summary-offer-discount-row" style="font-size: 0.86rem;">
                <span>Offer Savings:</span>
                <span class="font-monospace fw-semibold" id="summary-offer-discount-val">- ₹{{ number_format($offerDiscount, 2) }}</span>
            </div>
            @endif

            <!-- Wallet Deduction -->
            <div class="d-flex justify-content-between align-items-center mb-2.5 small text-success {{ ($walletDiscount ?? 0) > 0 ? '' : 'd-none' }}" id="summary-wallet-row" style="font-size: 0.86rem;">
                <span>Wallet Credits:</span>
                <span class="font-monospace fw-semibold" id="summary-wallet-val">- ₹{{ number_format($walletDiscount ?? 0, 2) }}</span>
            </div>

            <!-- Delivery Fee -->
            <div class="d-flex justify-content-between align-items-center mb-2.5 small text-secondary" style="font-size: 0.86rem;">
                <div class="d-flex align-items-center gap-1">
                    <span>Delivery Fee:</span>
                    <i class="bi bi-info-circle text-muted" style="font-size: 0.74rem;" title="Free delivery on orders above ₹499"></i>
                </div>
                <span id="summary-shipping-fee">
                    @if(($shippingFee ?? 0) > 0)
                        <span class="font-monospace text-dark fw-bold">+ ₹{{ number_format($shippingFee ?? 0, 2) }}</span>
                    @else
                        <span class="badge bg-success bg-opacity-10 text-success fw-bold rounded-pill px-2 py-0.5">FREE</span>
                    @endif
                </span>
            </div>

            <!-- COD Handling Fee -->
            <div class="d-flex justify-content-between align-items-center mb-2.5 small text-secondary d-none" id="summary-cod-fee-row" style="font-size: 0.86rem;">
                <span>COD Convenience Fee:</span>
                <span class="font-monospace text-dark fw-bold" id="summary-cod-fee-val">+ ₹{{ number_format($resolvedCodFee, 2) }}</span>
            </div>

            <hr class="my-3 text-muted opacity-25">

            <!-- Grand Total -->
            <div class="d-flex justify-content-between align-items-baseline mb-2.5">
                <div>
                    <span class="fw-bold text-dark fs-6">Grand Total:</span>
                    <small class="text-muted d-block" style="font-size: 0.74rem;">(Inclusive of all taxes)</small>
                </div>
                <span class="fs-5 fw-bolder text-dark font-monospace text-nowrap" id="summary-grand-total">
                    ₹{{ number_format($grandTotal, 2) }}
                </span>
            </div>

            <!-- Total Savings Badge -->
            <div class="p-2.5 rounded-3 text-center" style="background: #dcfce7; color: #166534; font-size: 0.8rem;">
                <i class="bi bi-piggy-bank-fill me-1"></i> You are saving <strong class="font-monospace" id="card-total-savings">₹{{ number_format($totalSavings, 2) }}</strong> on this order!
            </div>
        </div>
    </div>
    </div>

    <!-- ============================================================== -->
    <!-- STRICTLY SINGLE ACTION BUTTON CONTAINER (DESKTOP)             -->
    <!-- ============================================================== -->
    <div class="d-none d-lg-block">
        <!-- Step 1 Button: Continue to Offers -->
        <div id="step-1-sidebar-actions">
            <button type="button" class="btn btn-primary rounded-pill w-100 py-3 fw-bold shadow-xs d-flex align-items-center justify-content-center gap-2" onclick="proceedFromStep1()" style="font-size: 0.98rem; background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%); border: none;">
                <span>Continue to Offers</span>
                <i class="bi bi-arrow-right fs-6"></i>
            </button>
        </div>

        <!-- Step 2 Button: Continue to Payment -->
        <div id="step-2-sidebar-actions" class="d-none">
            <button type="button" class="btn btn-primary rounded-pill w-100 py-3 fw-bold shadow-xs d-flex align-items-center justify-content-center gap-2" onclick="proceedFromStep2()" style="font-size: 0.98rem; background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%); border: none;">
                <span>Continue to Payment</span>
                <i class="bi bi-arrow-right fs-6"></i>
            </button>
            <button type="button" class="btn btn-link text-muted text-decoration-none small mt-2 w-100 text-center" onclick="goToStep(1)" style="font-size: 0.82rem;">
                <i class="bi bi-arrow-left me-1"></i> Back to Delivery Address
            </button>
        </div>

        <!-- Step 3 Buttons: Place Order (Single Active Button based on selected payment) -->
        <div id="step-3-sidebar-actions" class="d-none">
            <button type="button" id="btn-submit-online-pay-sidebar" class="btn btn-primary rounded-pill w-100 py-3 fw-bold shadow-xs d-flex align-items-center justify-content-center gap-2" onclick="handleOrderSubmission(event)" style="font-size: 0.98rem; background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%); border: none;">
                <i class="bi bi-shield-lock-fill"></i>
                <span>Pay <span class="btn-pay-amount-mirror">₹{{ number_format($grandTotal, 2) }}</span> Securely</span>
            </button>
            <button type="button" id="btn-submit-cod-pay-sidebar" class="btn btn-success rounded-pill w-100 py-3 fw-bold shadow-xs d-none align-items-center justify-content-center gap-2" onclick="handleOrderSubmission(event)" style="font-size: 0.98rem; background: linear-gradient(135deg, #16a34a 0%, #15803d 100%); border: none;">
                <i class="bi bi-check2-circle"></i>
                <span>Place Cash on Delivery Order</span>
            </button>
            <button type="button" class="btn btn-link text-muted text-decoration-none small mt-2 w-100 text-center" onclick="goToStep(2)" style="font-size: 0.82rem;">
                <i class="bi bi-arrow-left me-1"></i> Back to Offers &amp; Wallet
            </button>
        </div>
    </div>
</div>

<!-- ============================================================== -->
<!-- MOBILE STICKY BOTTOM ACTION BAR (STRICTLY SINGLE BUTTON)       -->
<!-- ============================================================== -->
<div id="mobile-checkout-sticky-bar" class="d-block d-lg-none">
    <div class="d-flex align-items-center justify-content-between gap-3">
        <div>
            <small class="text-muted d-block" style="font-size: 0.72rem;">TOTAL PAYABLE</small>
            <strong class="font-monospace text-dark fs-6" id="mobile-sticky-grand-total">₹{{ number_format($grandTotal, 2) }}</strong>
        </div>
        <div class="flex-grow-1" style="max-width: 220px;">
            <!-- Mobile Step 1 Button -->
            <div id="step-1-mobile-actions">
                <button type="button" class="btn btn-primary rounded-pill w-100 py-2.5 fw-bold shadow-xs" onclick="proceedFromStep1()" style="font-size: 0.9rem; background: #4f46e5; border-color: #4f46e5;">
                    <span>Continue</span> <i class="bi bi-arrow-right ms-1"></i>
                </button>
            </div>
            <!-- Mobile Step 2 Button -->
            <div id="step-2-mobile-actions" class="d-none">
                <button type="button" class="btn btn-primary rounded-pill w-100 py-2.5 fw-bold shadow-xs" onclick="proceedFromStep2()" style="font-size: 0.9rem; background: #4f46e5; border-color: #4f46e5;">
                    <span>To Payment</span> <i class="bi bi-arrow-right ms-1"></i>
                </button>
            </div>
            <!-- Mobile Step 3 Button -->
            <div id="step-3-mobile-actions" class="d-none">
                <button type="button" id="btn-mobile-online-pay" class="btn btn-primary rounded-pill w-100 py-2.5 fw-bold shadow-xs" onclick="handleOrderSubmission(event)" style="font-size: 0.9rem; background: #4f46e5; border-color: #4f46e5;">
                    <i class="bi bi-shield-lock-fill me-1"></i> Pay Now
                </button>
                <button type="button" id="btn-mobile-cod-pay" class="btn btn-success rounded-pill w-100 py-2.5 fw-bold shadow-xs d-none" onclick="handleOrderSubmission(event)" style="font-size: 0.9rem; background: #16a34a; border-color: #16a34a;">
                    <i class="bi bi-check2-circle me-1"></i> Place COD
                </button>
            </div>
        </div>
    </div>
</div>
