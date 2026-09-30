@extends('layouts.customer')

@section('title', 'Secure Checkout — ' . \App\Models\Setting::get('store_name', 'WiseKart'))

@push('styles')
<script src="https://cdn.jsdelivr.net/npm/canvas-confetti@1.9.2/dist/confetti.browser.min.js"></script>
<style>
    /* Checkout Stepper */
    .checkout-step-badge {
        width: 34px;
        height: 34px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 700;
        font-size: 0.85rem;
    }
    
    /* Payment Tiles */
    .payment-tile {
        border: 2px solid #e2e8f0;
        border-radius: 1rem;
        padding: 1.15rem 1.25rem;
        cursor: pointer;
        transition: all 0.2s ease;
        background: #ffffff;
    }
    .payment-tile:hover {
        border-color: #93c5fd;
        background: #f8fafc;
    }
    .payment-tile.active-tile {
        border-color: #4f46e5 !important;
        background: #f5f3ff !important;
        box-shadow: 0 4px 16px rgba(79, 70, 229, 0.12);
    }
    
    /* Promo Input */
    #coupon_code_input {
        color: #0f172a !important;
        font-weight: 800 !important;
        letter-spacing: 1px !important;
        background-color: #ffffff !important;
        border: 1.5px solid #cbd5e1 !important;
    }
    #coupon_code_input:focus {
        border-color: #4f46e5 !important;
        box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.18) !important;
    }

    /* Checkout CTA */
    .btn-checkout-cta {
        background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%) !important;
        color: #ffffff !important;
        border: none !important;
        box-shadow: 0 6px 20px rgba(79, 70, 229, 0.35) !important;
        transition: all 0.2s ease;
    }
    .btn-checkout-cta:hover {
        transform: translateY(-1px);
        box-shadow: 0 8px 24px rgba(79, 70, 229, 0.45) !important;
    }
    
    /* Custom Wallet Switch */
    .custom-wallet-switch:checked {
        background-color: #10b981 !important;
        border-color: #10b981 !important;
    }
    
    /* Price Breakdown Rows */
    .price-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        font-size: 0.88rem;
        margin-bottom: 0.75rem;
    }
    
    .address-card-selected {
        border: 2px solid #10b981 !important;
        background: linear-gradient(135deg, #f0fdf4 0%, #ffffff 100%) !important;
    }
    
    .cursor-pointer { cursor: pointer; }
    .address-modal-option { transition: all 0.2s; }
    .address-modal-option:hover { border-color: #4f46e5 !important; background-color: #f5f3ff; }

    @media (max-width: 767.98px) {
        .checkout-step-badge {
            width: 28px;
            height: 28px;
            font-size: 0.75rem;
        }
        body {
            padding-bottom: 86px !important;
        }
        .payment-tile {
            padding: 0.85rem 1rem !important;
            border-radius: 0.85rem !important;
        }
        .card-body {
            padding: 0.95rem !important;
        }
        .card-header {
            padding: 0.75rem 0.95rem !important;
        }
    }
</style>
@endpush

@section('content')
<div class="container py-2 py-md-4 px-2 px-sm-3 px-md-4">
    
    <!-- Top Stepper Header -->
    <div class="row justify-content-center mb-3 mb-md-4">
        <div class="col-lg-8">
            <div class="d-flex align-items-center justify-content-between position-relative px-2 px-md-3">
                <!-- Line Behind -->
                <div class="position-absolute" style="top: 14px; left: 15%; right: 15%; height: 2px; background: #e2e8f0; z-index: 0;"></div>
                <div class="position-absolute" style="top: 14px; left: 15%; width: 35%; height: 2px; background: #4f46e5; z-index: 1;"></div>

                <!-- Step 1: Cart (Done) -->
                <div class="d-flex flex-column align-items-center position-relative" style="z-index: 2;">
                    <div class="checkout-step-badge text-white shadow-xs" style="background: #4f46e5;">
                        <i class="bi bi-check-lg"></i>
                    </div>
                    <span class="fw-bold text-dark mt-1" style="font-size: 0.74rem;">Bag</span>
                </div>

                <!-- Step 2: Checkout (Active) -->
                <div class="d-flex flex-column align-items-center position-relative" style="z-index: 2;">
                    <div class="checkout-step-badge text-white shadow-xs" style="background: #4f46e5; box-shadow: 0 0 0 5px rgba(79, 70, 229, 0.2) !important;">
                        2
                    </div>
                    <span class="fw-bolder text-primary mt-1" style="font-size: 0.74rem;">Delivery & Pay</span>
                </div>

                <!-- Step 3: Confirmation -->
                <div class="d-flex flex-column align-items-center position-relative" style="z-index: 2;">
                    <div class="checkout-step-badge bg-light text-muted border">
                        3
                    </div>
                    <span class="fw-semibold text-muted mt-1" style="font-size: 0.74rem;">Confirmation</span>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3 g-lg-4">
        <!-- ========================================== -->
        <!-- LEFT COLUMN: Delivery Address & Payment -->
        <!-- ========================================== -->
        <div class="col-lg-7">
            <!-- 1. Delivery Address Card -->
            <div class="card border-0 shadow-xs rounded-4 overflow-hidden mb-3.5" id="delivery-address-card" style="background: #ffffff; border: 1px solid #e2e8f0 !important;">
                <div class="card-header bg-white py-3 px-3 px-md-4 border-bottom d-flex align-items-center justify-content-between flex-wrap gap-2">
                    <div class="d-flex align-items-center gap-2.5">
                        <div class="rounded-circle text-primary d-flex align-items-center justify-content-center" style="width: 34px; height: 34px; background: #eef2ff;">
                            <i class="bi bi-geo-alt-fill fs-6"></i>
                        </div>
                        <div>
                            <h6 class="fw-bold mb-0 text-dark" style="font-size: 0.95rem;">1. Delivery Address</h6>
                            <small class="text-muted d-none d-sm-inline" style="font-size: 0.74rem;">Where should we deliver your order?</small>
                        </div>
                    </div>
                    <div class="d-flex gap-2 ms-auto">
                        <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill px-3 py-1 fw-semibold" id="btn-change-address" onclick="showAddressSelectorModal()" style="display: {{ $addresses->count() > 0 ? 'inline-block' : 'none' }}; font-size: 0.78rem;">
                            <i class="bi bi-arrow-repeat me-1"></i> Change
                        </button>
                        <button type="button" class="btn btn-sm btn-primary rounded-pill px-3 py-1 fw-semibold shadow-xs" onclick="openNewAddressForm()" style="font-size: 0.78rem; background: #4f46e5; border: none;">
                            <i class="bi bi-plus-lg me-1"></i> Add New
                        </button>
                    </div>
                </div>

                <div class="card-body p-3 p-md-4">
                    <!-- Error Banner for Missing Address -->
                    <div id="address-error-banner" class="alert alert-danger small mb-3 rounded-3 border-0 bg-danger-subtle text-danger" style="display: none;">
                        <i class="bi bi-exclamation-triangle-fill me-2"></i><span>Please select or add a valid delivery address before proceeding.</span>
                    </div>

                    <!-- Success Banner for Saved Address -->
                    <div id="address-success-banner" class="alert alert-success small mb-3 rounded-3 border-0 bg-success-subtle text-success" style="display: none;">
                        <i class="bi bi-check-circle-fill me-2"></i><span>Address saved successfully!</span>
                    </div>

                    <!-- Active Selected Shipping Display -->
                    <div id="active-address-display" class="p-3 p-md-3.5 rounded-3 border address-card-selected">
                        <div id="no-address-msg" style="display: {{ $addresses->count() > 0 ? 'none' : 'block' }};" class="text-center py-4 text-muted">
                            <i class="bi bi-building-add fs-2 text-primary d-block mb-2"></i>
                            No saved address found. Click <strong>"Add New"</strong> to enter your delivery location.
                        </div>

                        @php $activeAddr = $addresses->count() > 0 ? ($addresses->firstWhere('is_default', true) ?? $addresses->first()) : null; @endphp
                        <div id="active-address-details" style="display: {{ $addresses->count() > 0 ? 'block' : 'none' }};">
                            <div class="d-flex justify-content-between align-items-center mb-1.5">
                                <div class="d-flex align-items-center gap-2">
                                    <h6 class="fw-bold mb-0 text-dark" style="font-size: 0.92rem;" id="display-name">{{ $activeAddr ? $activeAddr->name : '' }}</h6>
                                    <span class="badge bg-success text-white rounded-pill px-2 py-0.5 fw-bold" style="font-size: 0.68rem;">✓ Deliver Here</span>
                                </div>
                            </div>
                            <p class="text-secondary mb-1.5" id="display-street-city" style="line-height: 1.5; font-size: 0.85rem;">
                                {{ $activeAddr ? "{$activeAddr->address}, {$activeAddr->city}, {$activeAddr->state} - {$activeAddr->zip}" : '' }}
                            </p>
                            <div class="small text-muted" id="display-phone" style="font-size: 0.8rem;">
                                <i class="bi bi-telephone-fill text-muted me-1 small"></i> Mobile: <strong class="text-dark">{{ $activeAddr ? $activeAddr->phone : '' }}</strong>
                            </div>
                            <div id="active-address-serviceability-badge" class="mt-2"></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 2. Add New Address Form Modal/Collapse -->
            <div class="card border-0 shadow-xs rounded-4 mb-3.5 overflow-hidden" id="new-address-card" style="display: {{ $addresses->count() === 0 ? 'block' : 'none' }}; background: #ffffff; border: 1.5px solid #4f46e5 !important;">
                <div class="card-header text-white py-3 px-3 px-md-4 d-flex justify-content-between align-items-center" style="background: #4f46e5;">
                    <h6 class="fw-bold mb-0 text-white" style="font-size: 0.92rem;"><i class="bi bi-house-add me-2"></i>Add New Delivery Address</h6>
                    @if($addresses->count() > 0)
                        <button type="button" class="btn-close btn-close-white" onclick="closeNewAddressForm()"></button>
                    @endif
                </div>
                <div class="card-body p-3 p-md-4">
                    <form id="new-address-form" onsubmit="handleSaveAddress(event)">
                        @csrf
                        <div class="row g-2.5">
                            <div class="col-12">
                                <label for="new_name" class="form-label fw-semibold small mb-1" style="font-size: 0.8rem;">Full Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control rounded-3" id="new_name" required value="{{ auth()->user()->name ?? '' }}" style="font-size: 0.88rem;">
                            </div>
                            <div class="col-6">
                                <label for="new_phone" class="form-label fw-semibold small mb-1" style="font-size: 0.8rem;">Mobile Number <span class="text-danger">*</span></label>
                                <input type="tel" class="form-control rounded-3" id="new_phone" required value="{{ auth()->user()->mobile_number ?? '' }}" placeholder="10-digit mobile" style="font-size: 0.88rem;">
                            </div>
                            <div class="col-6">
                                <label for="new_zip" class="form-label fw-semibold small mb-1" style="font-size: 0.8rem;">Pincode <span class="text-danger">*</span></label>
                                <input type="text" class="form-control rounded-3 font-monospace fw-bold" id="new_zip" required placeholder="6-digit PIN" style="font-size: 0.88rem;">
                            </div>
                            <div class="col-12">
                                <label for="new_address" class="form-label fw-semibold small mb-1" style="font-size: 0.8rem;">Flat / House / Building / Street Address <span class="text-danger">*</span></label>
                                <textarea class="form-control rounded-3" id="new_address" rows="2" required placeholder="Complete address" style="font-size: 0.88rem;"></textarea>
                            </div>
                            <div class="col-6">
                                <label for="new_city" class="form-label fw-semibold small mb-1" style="font-size: 0.8rem;">City <span class="text-danger">*</span></label>
                                <input type="text" class="form-control rounded-3" id="new_city" required style="font-size: 0.88rem;">
                            </div>
                            <div class="col-6">
                                <label for="new_state" class="form-label fw-semibold small mb-1" style="font-size: 0.8rem;">State <span class="text-danger">*</span></label>
                                <input type="text" class="form-control rounded-3" id="new_state" required style="font-size: 0.88rem;">
                            </div>
                        </div>

                        <div class="d-flex justify-content-end gap-2 mt-3">
                            @if($addresses->count() > 0)
                                <button type="button" class="btn btn-light rounded-pill px-3.5 btn-sm" onclick="closeNewAddressForm()">Cancel</button>
                            @endif
                            <button type="submit" class="btn btn-primary rounded-pill px-4 btn-sm shadow-xs fw-semibold" id="btn-save-address" style="background: #4f46e5; border: none;">
                                <i class="bi bi-bookmark-check me-1"></i> Save Address
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Hidden Actual Checkout Form -->
            <form action="{{ route('checkout.place-order') }}" method="POST" id="checkout-form">
                @csrf
                <input type="hidden" name="shipping_name" id="hidden_shipping_name">
                <input type="hidden" name="shipping_email" id="hidden_shipping_email" value="{{ auth()->user()->email }}">
                <input type="hidden" name="shipping_phone" id="hidden_shipping_phone">
                <input type="hidden" name="shipping_address" id="hidden_shipping_address">
                <input type="hidden" name="shipping_city" id="hidden_shipping_city">
                <input type="hidden" name="shipping_state" id="hidden_shipping_state">
                <input type="hidden" name="shipping_zip" id="hidden_shipping_zip">
                <input type="hidden" name="shipping_country" id="hidden_shipping_country" value="India">

                <!-- 2. Select Payment Method Card -->
                <div class="card border-0 shadow-xs rounded-4 overflow-hidden mb-3.5" style="background: #ffffff; border: 1px solid #e2e8f0 !important;">
                    <div class="card-header bg-white py-3 px-3 px-md-4 border-bottom d-flex align-items-center justify-content-between">
                        <div class="d-flex align-items-center gap-2.5">
                            <div class="rounded-circle text-success d-flex align-items-center justify-content-center" style="width: 34px; height: 34px; background: #ecfdf5;">
                                <i class="bi bi-credit-card-2-front-fill fs-6"></i>
                            </div>
                            <div>
                                <h6 class="fw-bold mb-0 text-dark" style="font-size: 0.95rem;">2. Payment Method</h6>
                                <small class="text-muted d-none d-sm-inline" style="font-size: 0.74rem;">Choose your payment mode</small>
                            </div>
                        </div>
                        <span class="badge bg-success bg-opacity-10 text-success fw-bold rounded-pill px-2.5 py-1" style="font-size: 0.7rem;">
                            <i class="bi bi-shield-check me-1"></i>100% SECURE
                        </span>
                    </div>

                    <div class="card-body p-3 p-md-4">
                        <!-- Option 1: Cashfree Online Payment (Active) -->
                        <div class="payment-tile active-tile mb-2.5" onclick="document.getElementById('pay_online').checked = true; document.getElementById('pay_online').dispatchEvent(new Event('change'));">
                            <div class="d-flex align-items-start gap-2.5">
                                <input class="form-check-input mt-1 flex-shrink-0" type="radio" name="payment_method" id="pay_online" value="online" checked form="checkout-form">
                                <div class="flex-grow-1">
                                    <div class="d-flex align-items-center justify-content-between flex-wrap gap-1 mb-1">
                                        <label class="form-check-label fw-bold text-dark mb-0 cursor-pointer" style="font-size: 0.92rem;" for="pay_online">
                                            ⚡ UPI / Cards / NetBanking
                                        </label>
                                        <span class="badge bg-success text-white rounded-pill px-2 py-0.5 fw-bold" style="font-size: 0.68rem;">FASTEST</span>
                                    </div>
                                    <p class="text-secondary small mb-2" style="font-size: 0.78rem; line-height: 1.4;">
                                        Google Pay, PhonePe, Paytm, BHIM UPI, Visa, Mastercard, RuPay & All Banks.
                                    </p>
                                    <div class="d-flex align-items-center gap-1.5 flex-wrap">
                                        <span class="badge bg-white border text-dark px-2 py-0.5 rounded" style="font-size: 0.7rem;"><i class="bi bi-lightning-charge-fill text-warning me-1"></i>Instant Dispatch</span>
                                        <span class="badge bg-white border text-dark px-2 py-0.5 rounded" style="font-size: 0.7rem;"><i class="bi bi-arrow-repeat text-success me-1"></i>Auto Refund</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Option 2: Cash On Delivery -->
                        <div class="payment-tile" onclick="document.getElementById('cod').checked = true; document.getElementById('cod').dispatchEvent(new Event('change'));">
                            <div class="d-flex align-items-start gap-2.5">
                                <input class="form-check-input mt-1 flex-shrink-0" type="radio" name="payment_method" id="cod" value="cod" form="checkout-form">
                                <div class="flex-grow-1">
                                    <div class="d-flex align-items-center justify-content-between flex-wrap gap-1 mb-1">
                                        <label class="form-check-label fw-bold text-dark mb-0 cursor-pointer" style="font-size: 0.92rem;" for="cod">
                                            💵 Cash on Delivery (COD)
                                        </label>
                                        <span class="badge bg-light text-secondary border rounded-pill px-2 py-0.5" style="font-size: 0.68rem;">PAY AT DOORSTEP</span>
                                    </div>
                                    <p class="text-secondary small mb-0" style="font-size: 0.78rem;">
                                        Pay via cash / UPI to our delivery executive when your parcel arrives.
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 3. Delivery Instructions / Notes Card -->
                <div class="card border-0 shadow-xs rounded-4 mb-3.5" style="background: #ffffff; border: 1px solid #e2e8f0 !important;">
                    <div class="card-body p-3 p-md-4">
                        <h6 class="fw-bold mb-1.5 text-dark" style="font-size: 0.88rem;">
                            <i class="bi bi-chat-left-text text-primary me-1.5"></i>Delivery Notes (Optional)
                        </h6>
                        <textarea class="form-control rounded-3" id="notes" name="notes" rows="2" placeholder="e.g. Leave with security / Call before ringing the bell." style="font-size: 0.84rem;"></textarea>
                    </div>
                </div>
            </form>
        </div>

        <!-- ========================================== -->
        <!-- RIGHT COLUMN: Order Summary & Coupons -->
        <!-- ========================================== -->
        <div class="col-lg-5">
            <!-- 1. Apply Promo Coupon Section -->
            <div class="card border-0 shadow-xs rounded-4 mb-3.5" style="background: #ffffff; border: 1px solid #e2e8f0 !important;">
                <div class="card-body p-3 p-md-4" id="coupon-card-body">
                    <div class="d-flex align-items-center justify-content-between mb-2.5">
                        <div class="d-flex align-items-center gap-2">
                            <i class="bi bi-tag-fill text-primary fs-6"></i>
                            <h6 class="fw-bold mb-0 text-dark" style="font-size: 0.92rem;">Coupons & Offers</h6>
                        </div>
                    </div>
                    
                    <div id="coupon-status-wrapper">
                        @if($couponCode)
                            <!-- Applied Coupon Banner -->
                            <div class="p-2.5 rounded-3 shadow-xs d-flex align-items-center justify-content-between" id="applied-coupon-box" style="background: #f0fdf4; border: 1.5px solid #86efac;">
                                <div class="d-flex align-items-center gap-2">
                                    <i class="bi bi-check-circle-fill text-success fs-5"></i>
                                    <div>
                                        <div class="d-flex align-items-center gap-1.5">
                                            <span class="font-monospace fw-bold text-success px-2 py-0.5 rounded" style="background: #dcfce7; border: 1px dashed #22c55e; font-size: 0.8rem;">{{ $couponCode }}</span>
                                            <span class="badge bg-success text-white rounded-pill px-2 py-0.5 fw-bold" style="font-size: 0.65rem;">APPLIED</span>
                                        </div>
                                        <div class="small fw-semibold text-dark mt-0.5" style="font-size: 0.78rem;">
                                            Saved <span class="text-success fw-bold">₹{{ number_format($discountAmount, 2) }}</span> with coupon!
                                        </div>
                                    </div>
                                </div>
                                <button type="button" class="btn btn-sm btn-outline-danger rounded-pill px-2.5 py-0.5 fw-bold" id="btn-remove-coupon-ajax" style="font-size: 0.74rem;">
                                    Remove
                                </button>
                            </div>
                        @else
                            <!-- Clean Input Form -->
                            <form id="ajax-coupon-form" action="{{ route('checkout.apply-coupon') }}">
                                @csrf
                                <div class="d-flex gap-2">
                                    <div class="position-relative flex-grow-1">
                                        <input type="text" name="coupon_code" id="coupon_code_input" 
                                               class="form-control rounded-3 font-monospace fw-bold" 
                                               placeholder="ENTER COUPON CODE" required 
                                               style="text-transform: uppercase; font-size: 0.88rem; height: 42px;">
                                    </div>
                                    <button type="submit" class="btn btn-primary rounded-3 px-3.5 fw-bold flex-shrink-0" id="btn-apply-coupon" style="height: 42px; font-size: 0.84rem; background: #4f46e5; border: none;">
                                        APPLY
                                    </button>
                                </div>
                            </form>

                            @if(isset($availableCoupons) && $availableCoupons->count() > 0)
                                <div class="mt-2.5 pt-2 border-top text-center" id="view-available-coupons-btn-container">
                                    <button type="button" class="btn btn-sm btn-light border text-primary rounded-pill px-3 py-1.5 fw-bold w-100 d-flex align-items-center justify-content-center gap-1.5 shadow-xs" data-bs-toggle="modal" data-bs-target="#couponsModal" style="font-size: 0.82rem;">
                                        <i class="bi bi-ticket-perforated-fill text-warning"></i>
                                        <span>View Available Coupons ({{ $availableCoupons->count() }})</span>
                                        <i class="bi bi-chevron-right ms-auto small"></i>
                                    </button>
                                </div>
                            @endif
                        @endif
                    </div>

                    <div id="coupon-error-banner" class="alert alert-danger small mt-2 mb-0 rounded-3 border-0 bg-danger-subtle text-danger" style="{{ $couponError ? 'display: block;' : 'display: none;' }}">
                        <i class="bi bi-exclamation-triangle-fill me-2"></i><span id="coupon-error-text">{{ $couponError ?? '' }}</span>
                    </div>
                </div>
            </div>

            <!-- 2. Sticky Order Summary & Price Breakdown -->
            <div class="card border-0 shadow-xs rounded-4 overflow-hidden" style="background: #ffffff; border: 1px solid #e2e8f0 !important;">
                <div class="card-header bg-white py-3 px-3 px-md-4 border-bottom">
                    <h6 class="fw-bold mb-0 text-dark" style="font-size: 0.95rem;">
                        <i class="bi bi-bag-check-fill text-primary me-1.5"></i>Order Summary ({{ $cart->items->sum('quantity') }} items)
                    </h6>
                </div>

                <div class="card-body p-3 p-md-4">
                    <!-- Items List -->
                    <div class="checkout-items-wrapper mb-3" style="max-height: 240px; overflow-y: auto;">
                        @foreach($cart->items as $item)
                        <div class="p-2.5 mb-2 bg-white rounded-3 border shadow-xs d-flex align-items-center justify-content-between gap-2.5" style="border-color: #f1f5f9 !important;">
                            <div class="d-flex align-items-center gap-2.5 min-w-0">
                                @if($item->product->main_image)
                                    <img src="{{ asset('storage/' . $item->product->main_image) }}" alt="{{ $item->product->name }}" class="rounded-2 border flex-shrink-0" style="width: 48px; height: 48px; object-fit: contain;">
                                @else
                                    <div class="rounded-2 bg-light border d-flex align-items-center justify-content-center flex-shrink-0 text-muted" style="width: 48px; height: 48px;">
                                        <i class="bi bi-box-seam fs-5"></i>
                                    </div>
                                @endif
                                <div class="min-w-0">
                                    <h6 class="mb-0.5 fw-bold text-dark text-truncate" style="font-size: 0.85rem;" title="{{ $item->product->name }}">
                                        {{ $item->product->name }}
                                    </h6>
                                    <div class="d-flex align-items-center gap-2 flex-wrap">
                                        <span class="badge bg-light text-dark border px-2 py-0.5 fw-semibold" style="font-size: 0.68rem;">
                                            Qty: {{ $item->quantity }}
                                        </span>
                                        <span class="small text-muted" style="font-size: 0.75rem;">
                                            ₹{{ number_format($item->unit_price, 2) }} each
                                        </span>
                                    </div>
                                </div>
                            </div>
                            <div class="text-end flex-shrink-0">
                                <span class="fw-bold text-dark font-monospace" style="font-size: 0.88rem;">
                                    ₹{{ number_format($item->quantity * $item->unit_price, 2) }}
                                </span>
                            </div>
                        </div>
                        @endforeach
                    </div>

                    <!-- WiseKart Wallet Box -->
                    @if(isset($isWalletFrozen) && $isWalletFrozen)
                    <div class="card border-0 rounded-3 p-3 mb-3" style="background: #fff1f2; border: 1.5px dashed #f87171 !important;">
                        <div class="d-flex align-items-center gap-2.5">
                            <i class="bi bi-shield-lock-fill fs-5 text-danger flex-shrink-0"></i>
                            <div>
                                <span class="fw-bold text-danger small d-block" style="font-size: 0.85rem;">WiseKart Wallet (Locked)</span>
                                <small class="text-secondary" style="font-size: 0.75rem;">Balance (₹{{ number_format($userWallet->balance, 2) }}) temporarily locked.</small>
                            </div>
                        </div>
                    </div>
                    @elseif(isset($userWallet) && $userWallet->balance > 0)
                    <div class="card border-0 rounded-3 p-3 mb-3 shadow-xs position-relative overflow-hidden wallet-card-container transition-all" 
                         id="wallet-card-box"
                         style="background: {{ $useWallet ? 'linear-gradient(135deg, #ecfdf5 0%, #f0fdf4 100%)' : '#ffffff' }}; border: 1.5px solid {{ $useWallet ? '#10b981' : '#cbd5e1' }} !important;">
                        
                        <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
                            <div class="d-flex align-items-center gap-2.5">
                                <div class="rounded-circle d-flex align-items-center justify-content-center text-white flex-shrink-0" 
                                     style="width: 36px; height: 36px; background: #10b981;">
                                    <i class="bi bi-wallet2 fs-6"></i>
                                </div>
                                <div>
                                    <div class="d-flex align-items-center gap-1.5 mb-0.5">
                                        <span class="fw-bold text-dark" style="font-size: 0.88rem;">WiseKart Wallet</span>
                                        <span class="badge bg-success text-white rounded-pill px-2 py-0.5 fw-bold" style="font-size: 0.68rem;">
                                            ₹{{ number_format($userWallet->balance, 2) }}
                                        </span>
                                    </div>
                                    <div class="text-secondary small" style="font-size: 0.74rem;">
                                        Apply wallet balance for instant discount
                                    </div>
                                </div>
                            </div>
                            <div class="form-check form-switch mb-0">
                                <input class="form-check-input custom-wallet-switch" type="checkbox" role="switch" id="toggle-wallet-switch" {{ $useWallet ? 'checked' : '' }} 
                                       style="cursor: pointer; width: 2.8em; height: 1.5em;">
                            </div>
                        </div>

                        <!-- Dynamic Applied Banner -->
                        <div id="wallet-applied-badge" class="mt-2.5 pt-2 border-top d-flex align-items-center justify-content-between small text-success fw-semibold {{ $useWallet && $walletDiscount > 0 ? '' : 'd-none' }}" style="border-color: rgba(16, 185, 129, 0.25) !important;">
                            <span class="d-flex align-items-center gap-1" style="font-size: 0.78rem;">
                                <i class="bi bi-check-circle-fill text-success"></i> Wallet applied:
                            </span>
                            <span class="font-monospace fw-bold" id="wallet-applied-val">- ₹{{ number_format($walletDiscount, 2) }}</span>
                        </div>
                    </div>
                    @endif

                    <!-- Price Breakdown Box -->
                    <div class="p-3 rounded-3 bg-light border mb-3" style="border-color: #e2e8f0 !important;">
                        <!-- Total MRP -->
                        <div class="price-row text-secondary">
                            <span>Total MRP:</span>
                            <span class="font-monospace text-dark fw-semibold">₹{{ number_format($totalMrp ?? $subtotal, 2) }}</span>
                        </div>

                        <!-- Product Discount -->
                        @if(isset($totalDiscount) && $totalDiscount > 0)
                        <div class="price-row text-success">
                            <span>Discount on MRP:</span>
                            <span class="font-monospace fw-bold">- ₹{{ number_format($totalDiscount, 2) }}</span>
                        </div>
                        @endif

                        <!-- Subtotal -->
                        <div class="price-row text-secondary">
                            <span>Cart Subtotal:</span>
                            <span class="font-monospace fw-semibold text-dark" id="summary-subtotal">₹{{ number_format($subtotal, 2) }}</span>
                        </div>

                        <!-- Coupon Savings -->
                        <div class="price-row text-success {{ $discountAmount > 0 ? '' : 'd-none' }}" id="summary-discount-row">
                            <span class="d-flex align-items-center gap-1 fw-semibold">
                                <i class="bi bi-tag-fill"></i> <span id="summary-discount-label">Coupon ({{ $couponCode }})</span>
                            </span>
                            <span class="font-monospace fw-bold" id="summary-discount-val">- ₹{{ number_format($discountAmount, 2) }}</span>
                        </div>

                        <!-- Wallet Credits -->
                        <div class="price-row text-success {{ $walletDiscount > 0 ? '' : 'd-none' }}" id="summary-wallet-row">
                            <span class="d-flex align-items-center gap-1 fw-semibold">
                                <i class="bi bi-wallet2"></i> <span>Wallet Credits</span>
                            </span>
                            <span class="font-monospace fw-bold" id="summary-wallet-val">- ₹{{ number_format($walletDiscount, 2) }}</span>
                        </div>

                        <!-- Shipping / Delivery Charges -->
                        <div class="price-row text-secondary mb-1">
                            <span>Delivery Fee:</span>
                            <span id="summary-shipping-fee">
                                @if(isset($shippingFee) && $shippingFee > 0)
                                    <span class="font-monospace text-dark fw-bold">+ ₹{{ number_format($shippingFee, 2) }}</span>
                                @else
                                    <span class="badge bg-success bg-opacity-10 text-success fw-bold rounded-pill px-2 py-0.5">FREE</span>
                                @endif
                            </span>
                        </div>

                        <!-- COD Handling Fee -->
                        <div class="price-row text-secondary mb-1" id="summary-cod-fee-row">
                            <span>COD Handling Fee:</span>
                            <span id="summary-cod-fee-val">
                                <span class="badge bg-success bg-opacity-10 text-success fw-bold rounded-pill px-2 py-0.5">₹0.00 (Prepaid Offer)</span>
                            </span>
                        </div>

                        <hr class="my-2.5" style="border-color: #cbd5e1;">

                        <!-- Grand Total -->
                        <div class="d-flex justify-content-between align-items-center pt-1">
                            <div>
                                <span class="fw-bold text-dark fs-6 d-block">Grand Total:</span>
                                <small class="text-muted" style="font-size: 0.7rem;"><i class="bi bi-shield-check text-success me-1"></i>Inclusive of all taxes</small>
                            </div>
                            <span class="fs-4 fw-bolder text-dark font-monospace" id="summary-grand-total">₹{{ number_format($grandTotal, 2) }}</span>
                        </div>
                    <!-- Terms & No Return Consent Checkbox -->
                    <div class="mb-3 p-2.5 p-md-3 rounded-3 border" style="background: #f8fafc; border-color: #cbd5e1 !important;">
                        <div class="form-check mb-0 d-flex align-items-start gap-2">
                            <input class="form-check-input cursor-pointer flex-shrink-0 mt-1" type="checkbox" id="terms-consent-checkbox" name="terms_consent" required form="checkout-form" style="cursor: pointer;">
                            <label class="form-check-label text-dark small mb-0" for="terms-consent-checkbox" style="font-size: 0.78rem; line-height: 1.45; cursor: pointer;">
                                I have reviewed my order and agree to {{ \App\Models\Setting::get('store_name', 'ShopCalm') }}'s 
                                <a href="{{ route('page.return') }}" target="_blank" class="text-primary text-decoration-none fw-semibold">No Return Policy</a> 
                                and 
                                <a href="{{ route('page.terms') }}" target="_blank" class="text-primary text-decoration-none fw-semibold">Terms & Conditions</a>.
                            </label>
                        </div>
                    </div>

                    <!-- Place Order Desktop Button -->
                    <div class="d-none d-md-grid mb-3">
                        <button type="submit" form="checkout-form" class="btn btn-primary btn-lg rounded-pill btn-checkout-cta d-flex align-items-center justify-content-center gap-2 py-3 shadow-xs text-white" id="btn-place-order">
                            <span id="btn-place-order-text" class="fw-bold text-white fs-6">
                                @if($grandTotal <= 0)
                                    Confirm & Place Order (Wallet Paid)
                                @else
                                    Pay Online ₹{{ number_format($grandTotal, 2) }}
                                @endif
                            </span>
                            <i class="bi bi-arrow-right fs-5 text-white"></i>
                        </button>
                    </div>

                    <!-- Security Badges -->
                    <div class="d-flex align-items-center justify-content-around text-center pt-2 border-top text-muted small" style="font-size: 0.72rem;">
                        <div><i class="bi bi-shield-lock-fill text-success me-1"></i>SSL Encrypted</div>
                        <div><i class="bi bi-patch-check-fill text-primary me-1"></i>100% Genuine</div>
                        <div><i class="bi bi-truck text-info me-1"></i>Fast Delivery</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- 📱 Mobile Sticky Bottom Checkout Bar --}}
<div class="d-block d-md-none position-fixed bottom-0 start-0 end-0 bg-white border-top shadow-lg p-2.5 z-3" 
     style="backdrop-filter: blur(10px); background: rgba(255, 255, 255, 0.98) !important; z-index: 1045;">
    <div class="container-fluid px-2">
        <div class="row g-2 align-items-center">
            <div class="col-5">
                <div class="d-flex flex-column">
                    <span class="text-muted" style="font-size: 0.68rem; line-height: 1;">Grand Total:</span>
                    <span class="fw-bolder text-dark font-monospace" id="mobile-summary-grand-total" style="font-size: 1.05rem; letter-spacing: -0.3px;">
                        ₹{{ number_format($grandTotal, 2) }}
                    </span>
                </div>
            </div>
            <div class="col-7">
                <button type="submit" form="checkout-form" class="btn btn-primary w-100 rounded-pill py-2.5 fw-bold text-nowrap shadow-xs btn-checkout-cta" id="mobile-btn-place-order" style="font-size: 0.84rem;">
                    <span id="mobile-btn-place-order-text">Place Order</span> <i class="bi bi-arrow-right ms-1"></i>
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Coupons Modal -->
<div class="modal fade" id="couponsModal" tabindex="-1" aria-labelledby="couponsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
            <div class="modal-header bg-white py-3 px-3.5 px-md-4 border-bottom">
                <div class="d-flex align-items-center gap-2">
                    <div class="rounded-circle d-flex align-items-center justify-content-center" style="width: 32px; height: 32px; background: rgba(99, 102, 241, 0.12); color: #6366f1;">
                        <i class="bi bi-tag-fill"></i>
                    </div>
                    <h5 class="modal-title fw-bolder text-dark mb-0" id="couponsModalLabel" style="font-size: 1rem;">
                        Available Coupons
                    </h5>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-3 p-md-3.5 bg-light">
                @if(isset($availableCoupons) && $availableCoupons->count() > 0)
                    @php
                        $eligibleCoupons = $availableCoupons->where('is_eligible', true);
                        $ineligibleCoupons = $availableCoupons->where('is_eligible', false);
                    @endphp

                    {{-- 1. Applicable / Eligible Coupons --}}
                    @if($eligibleCoupons->count() > 0)
                        <div class="mb-3">
                            <span class="text-muted small fw-bold text-uppercase d-block mb-2" style="font-size: 0.72rem; letter-spacing: 0.05em;">
                                <i class="bi bi-check-circle-fill text-success me-1"></i> Applicable for your order ({{ $eligibleCoupons->count() }})
                            </span>
                            <div class="d-flex flex-column gap-2.5">
                                @foreach($eligibleCoupons as $c)
                                    @php
                                        $discType = is_object($c->discount_type) ? $c->discount_type->value : (string)$c->discount_type;
                                    @endphp
                                    <div class="card border-0 shadow-xs rounded-3 p-3 bg-white position-relative overflow-hidden" 
                                         style="border: 1.5px dashed #3b82f6 !important; background: linear-gradient(135deg, #ffffff 0%, #f0fdf4 100%);">
                                        <div class="d-flex justify-content-between align-items-start mb-1.5">
                                            <span class="font-monospace fw-extrabold px-2.5 py-1 rounded shadow-2xs" style="background: #eff6ff; color: #1d4ed8; border: 1px solid #93c5fd; font-size: 0.85rem; letter-spacing: 0.05em;">
                                                {{ $c->code }}
                                            </span>
                                            <button type="button" class="btn btn-sm btn-primary rounded-pill px-3 py-1 fw-bold shadow-2xs" onclick="applyCouponFromModal('{{ $c->code }}')" style="font-size: 0.76rem; background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%); border: none;">
                                                APPLY
                                            </button>
                                        </div>
                                        <div class="fw-bolder text-success mt-1" style="font-size: 0.92rem;">
                                            @if($discType === 'PERCENTAGE')
                                                Save {{ round($c->discount_value) }}% @if(isset($c->calculated_discount) && $c->calculated_discount > 0) (₹{{ number_format($c->calculated_discount, 2) }}) @endif
                                            @else
                                                Save ₹{{ number_format($c->discount_value) }} FLAT
                                            @endif
                                        </div>
                                        @if($c->description)
                                            <p class="text-secondary small mb-1.5 mt-0.5" style="font-size: 0.78rem; line-height: 1.35;">{{ $c->description }}</p>
                                        @endif
                                        <div class="d-flex align-items-center gap-3 pt-1 text-muted small" style="font-size: 0.72rem;">
                                            @if($c->minimum_order_amount > 0)
                                                <span><i class="bi bi-bag-check me-0.5"></i> Min spend: ₹{{ number_format($c->minimum_order_amount) }}</span>
                                            @endif
                                            @if($c->valid_until)
                                                <span><i class="bi bi-clock-history me-0.5"></i> Expires {{ $c->valid_until->format('d M, Y') }}</span>
                                            @endif
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    {{-- 2. Ineligible / Unlockable Coupons --}}
                    @if($ineligibleCoupons->count() > 0)
                        <div class="mt-3">
                            <span class="text-muted small fw-bold text-uppercase d-block mb-2" style="font-size: 0.72rem; letter-spacing: 0.05em;">
                                <i class="bi bi-lock-fill text-secondary me-1"></i> More Coupons (Add items to unlock)
                            </span>
                            <div class="d-flex flex-column gap-2.5">
                                @foreach($ineligibleCoupons as $c)
                                    @php
                                        $discType = is_object($c->discount_type) ? $c->discount_type->value : (string)$c->discount_type;
                                    @endphp
                                    <div class="card border-0 shadow-xs rounded-3 p-3 bg-white opacity-90" style="border: 1px dashed #cbd5e1 !important;">
                                        <div class="d-flex justify-content-between align-items-start mb-1">
                                            <span class="font-monospace fw-bold px-2 py-0.5 rounded text-secondary" style="background: #f1f5f9; border: 1px solid #e2e8f0; font-size: 0.82rem;">
                                                {{ $c->code }}
                                            </span>
                                            <span class="badge bg-light text-secondary border rounded-pill px-2 py-1 small" style="font-size: 0.7rem;">
                                                <i class="bi bi-lock me-0.5"></i> Locked
                                            </span>
                                        </div>
                                        <div class="fw-bold text-dark mt-1" style="font-size: 0.86rem;">
                                            @if($discType === 'PERCENTAGE')
                                                Save {{ round($c->discount_value) }}%
                                            @else
                                                Save ₹{{ number_format($c->discount_value) }} FLAT
                                            @endif
                                        </div>
                                        @if($c->ineligibility_reason)
                                            <div class="text-danger small mt-1 d-flex align-items-center gap-1" style="font-size: 0.74rem;">
                                                <i class="bi bi-exclamation-circle"></i> {{ $c->ineligibility_reason }}
                                            </div>
                                        @elseif($c->minimum_order_amount > 0 && isset($subtotal) && $subtotal < $c->minimum_order_amount)
                                            <div class="text-primary small mt-1" style="font-size: 0.74rem;">
                                                Add ₹{{ number_format($c->minimum_order_amount - $subtotal, 2) }} more to unlock!
                                            </div>
                                        @endif
                                        @if($c->description)
                                            <p class="text-muted small mb-0 mt-1" style="font-size: 0.76rem;">{{ $c->description }}</p>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif
                @else
                    <div class="text-center py-4 text-muted">
                        <div class="rounded-circle d-inline-flex align-items-center justify-content-center mb-2" style="width: 44px; height: 44px; background: #e2e8f0;">
                            <i class="bi bi-tag text-secondary fs-5"></i>
                        </div>
                        <h6 class="fw-bold text-dark mb-1" style="font-size: 0.9rem;">No Active Coupons Found</h6>
                        <p class="small text-muted mb-0" style="font-size: 0.78rem;">Check back later for seasonal promo codes and discount vouchers!</p>
                    </div>
                @endif
            </div>
            <div class="modal-footer bg-white py-2 px-3 border-top">
                <button type="button" class="btn btn-light rounded-pill px-3.5 btn-sm fw-semibold" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal for selecting/changing saved address -->
<div class="modal fade" id="addressSelectorModal" tabindex="-1" aria-labelledby="addressModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content rounded-4 border-0 shadow">
            <div class="modal-header border-0 pb-0 px-3 px-md-4 pt-3">
                <h5 class="modal-title fw-bold" id="addressModalLabel" style="font-size: 1rem;"><i class="bi bi-geo-alt-fill text-primary me-2"></i>Select Saved Address</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-3 p-md-4">
                <div class="row g-2.5" id="modal-address-list">
                    @foreach($addresses as $addr)
                    <div class="col-12">
                        <div class="card border rounded-3 p-3 address-modal-option cursor-pointer" onclick="selectModalAddress({{ json_encode($addr) }})">
                            <div class="d-flex justify-content-between align-items-start gap-2">
                                <div>
                                    <strong class="text-dark" style="font-size: 0.9rem;">{{ $addr->name }}</strong>
                                    <p class="text-secondary mb-1 mt-0.5" style="font-size: 0.82rem;">{{ $addr->address }}, {{ $addr->city }}, {{ $addr->state }} - {{ $addr->zip }}</p>
                                    <div class="small text-muted" style="font-size: 0.78rem;"><i class="bi bi-telephone me-1"></i> {{ $addr->phone }}</div>
                                </div>
                                <button type="button" class="btn btn-sm btn-outline-primary rounded-pill px-3 py-0.5 flex-shrink-0" style="font-size: 0.76rem;">Deliver Here</button>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Cashfree DropJS SDK v3 -->
<script src="https://sdk.cashfree.com/js/v3/cashfree.js"></script>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
let userAddresses = {!! json_encode($addresses) !!};
let currentSelectedAddress = userAddresses.length > 0 ? userAddresses[0] : null;

document.addEventListener('DOMContentLoaded', function() {
    if (currentSelectedAddress) {
        applySelectedAddress(currentSelectedAddress);
    }

    const checkoutForm = document.getElementById('checkout-form');
    const placeOrderBtn = document.getElementById('btn-place-order');
    const placeOrderBtnText = document.getElementById('btn-place-order-text');
    const mobPlaceOrderBtn = document.getElementById('mobile-btn-place-order');
    const mobPlaceOrderBtnText = document.getElementById('mobile-btn-place-order-text');

    window.updatePaymentButtonLabel = function() {
        const selectedVal = document.querySelector('input[name="payment_method"]:checked')?.value;
        const isOnline = (selectedVal === 'online');
        const grandTotalRaw = document.getElementById('summary-grand-total')?.textContent.replace(/[^0-9.]/g, '') || '0';
        const grandTotalVal = parseFloat(grandTotalRaw);
        const grandTotalFmt = document.getElementById('summary-grand-total')?.textContent || '₹{{ number_format($grandTotal, 2) }}';

        const mobGrandEl = document.getElementById('mobile-summary-grand-total');
        if (mobGrandEl) mobGrandEl.textContent = grandTotalFmt;

        if (placeOrderBtnText) {
            if (grandTotalVal <= 0) {
                placeOrderBtnText.textContent = `Confirm & Place Order (Wallet Paid)`;
            } else if (isOnline) {
                placeOrderBtnText.textContent = `Pay Online ${grandTotalFmt}`;
            } else {
                placeOrderBtnText.textContent = `Place Order for ${grandTotalFmt} (COD)`;
            }
        }

        if (mobPlaceOrderBtnText) {
            if (grandTotalVal <= 0) {
                mobPlaceOrderBtnText.textContent = `Place Order (Wallet)`;
            } else if (isOnline) {
                mobPlaceOrderBtnText.textContent = `Pay Online (${grandTotalFmt})`;
            } else {
                mobPlaceOrderBtnText.textContent = `Place COD Order (${grandTotalFmt})`;
            }
        }
    };

    document.querySelectorAll('input[name="payment_method"]').forEach(radio => {
        radio.addEventListener('change', function() {
            if (typeof updateShippingFeeAndTotal === 'function') {
                updateShippingFeeAndTotal(window.activeShippingCharge, 499);
            } else {
                window.updatePaymentButtonLabel();
            }
            document.querySelectorAll('.payment-tile').forEach(tile => tile.classList.remove('active-tile'));
            const parentTile = this.closest('.payment-tile');
            if (parentTile) parentTile.classList.add('active-tile');
        });
    });

    function handleOrderSubmission(e) {
        if (e) e.preventDefault();

        const consentCb = document.getElementById('terms-consent-checkbox');
        if (consentCb && !consentCb.checked) {
            Swal.fire({
                icon: 'warning',
                title: 'Agreement Required',
                text: 'Please check the agreement box for the No Return Policy & Terms & Conditions before placing your order.',
                confirmButtonColor: '#6366f1'
            });
            consentCb.scrollIntoView({ behavior: 'smooth', block: 'center' });
            return;
        }

        if (!document.getElementById('hidden_shipping_address').value) {
            const errorBanner = document.getElementById('address-error-banner');
            if (errorBanner) {
                errorBanner.style.display = 'block';
                errorBanner.scrollIntoView({ behavior: 'smooth', block: 'center' });
            }
            return;
        }

        if (placeOrderBtn) {
            placeOrderBtn.disabled = true;
            placeOrderBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span> Processing...';
        }
        if (mobPlaceOrderBtn) {
            mobPlaceOrderBtn.disabled = true;
            mobPlaceOrderBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Processing...';
        }

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
            const data = await res.json();
            if (!res.ok || !data.success) {
                throw new Error(data.message || 'Could not place order. Please try again.');
            }
            return data;
        })
        .then(data => {
            if (data.redirect_url) {
                window.location.href = data.redirect_url;
            } else if (data.payment_session_id) {
                @php
                    $cfEnv = strtolower(\App\Models\Setting::where('key', 'cashfree_environment')->value('value') ?? config('services.cashfree.environment', env('CASHFREE_ENVIRONMENT', 'TEST')));
                    $cfMode = in_array($cfEnv, ['production', 'prod']) ? 'production' : 'sandbox';
                @endphp
                const cashfree = Cashfree({
                    mode: "{{ $cfMode }}"
                });

                cashfree.checkout({
                    paymentSessionId: data.payment_session_id,
                    redirectTarget: '_self'
                }).catch(err => {
                    console.error("DropJS error:", err);
                    if (placeOrderBtn) { placeOrderBtn.disabled = false; }
                    if (mobPlaceOrderBtn) { mobPlaceOrderBtn.disabled = false; }
                    updatePaymentButtonLabel();
                    Swal.fire({
                        icon: 'error',
                        title: 'Payment Gateway Error',
                        text: err.message || 'Could not open Cashfree payment modal.'
                    });
                });
            }
        })
        .catch(err => {
            console.error("Place Order Error:", err);
            if (placeOrderBtn) { placeOrderBtn.disabled = false; }
            if (mobPlaceOrderBtn) { mobPlaceOrderBtn.disabled = false; }
            updatePaymentButtonLabel();
            Swal.fire({
                icon: 'error',
                title: 'Order Notice',
                text: err.message || 'Failed to initialize order.'
            });
        });
    }

    if (checkoutForm) {
        checkoutForm.addEventListener('submit', handleOrderSubmission);
    }

    // Auto-restore buttons if user navigates back (BFCache / Browser Back Button)
    window.addEventListener('pageshow', function() {
        if (placeOrderBtn) placeOrderBtn.disabled = false;
        if (mobPlaceOrderBtn) mobPlaceOrderBtn.disabled = false;
        updatePaymentButtonLabel();
    });

    // Coupon logic
    const couponContainer = document.getElementById('coupon-status-wrapper');
    const errorBanner = document.getElementById('coupon-error-banner');
    const errorText = document.getElementById('coupon-error-text');
    const summaryDiscountRow = document.getElementById('summary-discount-row');
    const summaryDiscountLabel = document.getElementById('summary-discount-label');
    const summaryDiscountVal = document.getElementById('summary-discount-val');
    const summaryGrandTotal = document.getElementById('summary-grand-total');

    document.addEventListener('submit', function(e) {
        if (e.target && e.target.id === 'ajax-coupon-form') {
            e.preventDefault();
            const btn = document.getElementById('btn-apply-coupon');
            const input = document.getElementById('coupon_code_input');
            const code = input ? input.value.trim() : '';

            if (!code) return;

            if (btn) { btn.disabled = true; btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span>'; }
            if (errorBanner) errorBanner.style.display = 'none';

            fetch('{{ route("checkout.apply-coupon") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ coupon_code: code })
            })
            .then(async res => {
                const data = await res.json();
                if (res.ok && data.success) {
                    if (typeof confetti === 'function') {
                        confetti({
                            particleCount: 80,
                            spread: 60,
                            origin: { y: 0.6 }
                        });
                    }

                    couponContainer.innerHTML = `
                        <div class="p-2.5 rounded-3 shadow-xs d-flex align-items-center justify-content-between" id="applied-coupon-box" style="background: #f0fdf4; border: 1.5px solid #86efac;">
                            <div class="d-flex align-items-center gap-2">
                                <i class="bi bi-check-circle-fill text-success fs-5"></i>
                                <div>
                                    <div class="d-flex align-items-center gap-1.5">
                                        <span class="font-monospace fw-bold text-success px-2 py-0.5 rounded" style="background: #dcfce7; border: 1px dashed #22c55e; font-size: 0.8rem;">${data.coupon_code}</span>
                                        <span class="badge bg-success text-white rounded-pill px-2 py-0.5 fw-bold" style="font-size: 0.65rem;">APPLIED</span>
                                    </div>
                                    <div class="small fw-semibold text-dark mt-0.5" style="font-size: 0.78rem;">
                                        Saved <span class="text-success fw-bold">₹${data.formatted_discount}</span> with coupon!
                                    </div>
                                </div>
                            </div>
                            <button type="button" class="btn btn-sm btn-outline-danger rounded-pill px-2.5 py-0.5 fw-bold" id="btn-remove-coupon-ajax" style="font-size: 0.74rem;">
                                Remove
                            </button>
                        </div>
                    `;
                    if (summaryDiscountRow) summaryDiscountRow.classList.remove('d-none');
                    if (summaryDiscountLabel) summaryDiscountLabel.textContent = `Coupon (${data.coupon_code})`;
                    if (summaryDiscountVal) summaryDiscountVal.textContent = `- ₹${data.formatted_discount}`;
                    if (summaryGrandTotal) summaryGrandTotal.textContent = `₹${data.grand_total}`;
                    
                    updatePaymentButtonLabel();
                } else {
                    if (errorBanner && errorText) {
                        errorText.textContent = data.message || 'Failed to apply coupon.';
                        errorBanner.style.display = 'block';
                    }
                }
            })
            .catch(err => {
                console.error(err);
                if (errorBanner && errorText) {
                    errorText.textContent = 'An error occurred while applying the coupon.';
                    errorBanner.style.display = 'block';
                }
            })
            .finally(() => {
                if (btn) { btn.disabled = false; btn.innerHTML = 'APPLY'; }
            });
        }
    });

    document.addEventListener('click', function(e) {
        if (e.target && (e.target.id === 'btn-remove-coupon-ajax' || e.target.closest('#btn-remove-coupon-ajax'))) {
            e.preventDefault();
            const btn = document.getElementById('btn-remove-coupon-ajax');
            if (btn) btn.disabled = true;

            fetch('{{ route("checkout.remove-coupon") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                }
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    couponContainer.innerHTML = `
                        <form id="ajax-coupon-form" action="{{ route('checkout.apply-coupon') }}">
                            <input type="hidden" name="_token" value="{{ csrf_token() }}">
                            <div class="d-flex gap-2">
                                <div class="position-relative flex-grow-1">
                                    <input type="text" name="coupon_code" id="coupon_code_input" 
                                           class="form-control rounded-3 font-monospace fw-bold" 
                                           placeholder="ENTER COUPON CODE" required 
                                           style="text-transform: uppercase; font-size: 0.88rem; height: 42px;">
                                </div>
                                <button type="submit" class="btn btn-primary rounded-3 px-3.5 fw-bold flex-shrink-0" id="btn-apply-coupon" style="height: 42px; font-size: 0.84rem; background: #4f46e5; border: none;">
                                    APPLY
                                </button>
                            </div>
                        </form>
                        @if(isset($availableCoupons) && $availableCoupons->count() > 0)
                            <div class="mt-2.5 pt-2 border-top text-center" id="view-available-coupons-btn-container">
                                <button type="button" class="btn btn-sm btn-light border text-primary rounded-pill px-3 py-1.5 fw-bold w-100 d-flex align-items-center justify-content-center gap-1.5 shadow-xs" data-bs-toggle="modal" data-bs-target="#couponsModal" style="font-size: 0.82rem;">
                                    <i class="bi bi-ticket-perforated-fill text-warning"></i>
                                    <span>View Available Coupons ({{ $availableCoupons->count() }})</span>
                                    <i class="bi bi-chevron-right ms-auto small"></i>
                                </button>
                            </div>
                        @endif
                    `;
                    if (errorBanner) errorBanner.style.display = 'none';
                    if (summaryDiscountRow) summaryDiscountRow.classList.add('d-none');
                    if (summaryGrandTotal) summaryGrandTotal.textContent = `₹${data.grand_total}`;

                    updatePaymentButtonLabel();
                }
            })
            .catch(err => console.error(err));
        }
    });

    // Wallet Toggle Event Listener
    const walletSwitch = document.getElementById('toggle-wallet-switch');
    if (walletSwitch) {
        walletSwitch.addEventListener('change', function() {
            const isChecked = this.checked;
            const cardBox = document.getElementById('wallet-card-box');
            const appliedBadge = document.getElementById('wallet-applied-badge');

            fetch('{{ route("checkout.toggle-wallet") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ use_wallet: isChecked })
            })
            .then(async res => {
                const data = await res.json();
                if (!res.ok) {
                    throw new Error(data.message || 'Unable to update wallet status.');
                }
                return data;
            })
            .then(data => {
                if (data.success) {
                    const walletRow = document.getElementById('summary-wallet-row');
                    const walletVal = document.getElementById('summary-wallet-val');
                    const grandTotalEl = document.getElementById('summary-grand-total');

                    if (cardBox) {
                        if (data.use_wallet) {
                            cardBox.style.background = 'linear-gradient(135deg, #ecfdf5 0%, #f0fdf4 100%)';
                            cardBox.style.borderColor = '#10b981';
                        } else {
                            cardBox.style.background = '#ffffff';
                            cardBox.style.borderColor = '#cbd5e1';
                        }
                    }

                    if (appliedBadge) {
                        if (data.use_wallet && data.wallet_discount_raw > 0) {
                            appliedBadge.classList.remove('d-none');
                            const badgeVal = appliedBadge.querySelector('.font-monospace');
                            if (badgeVal) badgeVal.textContent = `- ₹${data.wallet_discount}`;
                        } else {
                            appliedBadge.classList.add('d-none');
                        }
                    }

                    if (walletRow && walletVal) {
                        if (data.wallet_discount_raw > 0) {
                            walletRow.classList.remove('d-none');
                            walletVal.textContent = `- ₹${data.wallet_discount}`;
                        } else {
                            walletRow.classList.add('d-none');
                        }
                    }

                    if (grandTotalEl) {
                        grandTotalEl.textContent = `₹${data.grand_total}`;
                    }

                    updatePaymentButtonLabel();

                    if (data.use_wallet && typeof confetti === 'function') {
                        confetti({
                            particleCount: 50,
                            spread: 60,
                            origin: { y: 0.7 }
                        });
                    }
                }
            })
            .catch(err => {
                console.error('Wallet toggle error:', err);
                walletSwitch.checked = !isChecked;
            });
        });
    }
});

function applySelectedAddress(addr) {
    if (!addr) return;
    currentSelectedAddress = addr;

    const noAddrMsg = document.getElementById('no-address-msg');
    if (noAddrMsg) noAddrMsg.style.display = 'none';

    const addrDetails = document.getElementById('active-address-details');
    if (addrDetails) addrDetails.style.display = 'block';

    const errorBanner = document.getElementById('address-error-banner');
    if (errorBanner) errorBanner.style.display = 'none';

    const nameEl = document.getElementById('display-name');
    if (nameEl) nameEl.textContent = addr.name;

    const streetCityEl = document.getElementById('display-street-city');
    if (streetCityEl) streetCityEl.textContent = `${addr.address}, ${addr.city}, ${addr.state} - ${addr.zip}`;

    const phoneEl = document.getElementById('display-phone');
    if (phoneEl) phoneEl.innerHTML = `<i class="bi bi-telephone-fill text-muted me-1.5 small"></i> Mobile: <strong class="text-dark">${addr.phone}</strong>`;

    document.getElementById('hidden_shipping_name').value = addr.name;
    document.getElementById('hidden_shipping_phone').value = addr.phone;
    document.getElementById('hidden_shipping_address').value = addr.address;
    document.getElementById('hidden_shipping_city').value = addr.city;
    document.getElementById('hidden_shipping_state').value = addr.state;
    document.getElementById('hidden_shipping_zip').value = addr.zip;

    const badgeEl = document.getElementById('active-address-serviceability-badge');
    const placeOrderBtn = document.getElementById('btn-place-order');
    const mobPlaceOrderBtn = document.getElementById('mobile-btn-place-order');
    
    if (badgeEl && addr.zip) {
        badgeEl.innerHTML = '<span class="text-muted small"><span class="spinner-border spinner-border-sm me-1"></span> Checking delivery speed...</span>';
        
        fetch('{{ route("delivery.check") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            },
            body: JSON.stringify({ pincode: addr.zip, save_location: true })
        })
        .then(r => r.json())
        .then(data => {
            if (data.is_serviceable) {
                badgeEl.innerHTML = `<span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 rounded-pill px-2.5 py-1 fw-semibold" style="font-size: 0.74rem;"><i class="bi bi-truck me-1"></i> Delivery available by <strong>${data.estimated_delivery}</strong></span>`;
                if (placeOrderBtn) placeOrderBtn.disabled = false;
                if (mobPlaceOrderBtn) mobPlaceOrderBtn.disabled = false;
                
                // Update DOM Delivery Fee badge & Grand Total live
                updateShippingFeeAndTotal(data.delivery_charge, data.free_shipping_min);
            } else {
                badgeEl.innerHTML = `<span class="badge bg-danger bg-opacity-10 text-danger border border-danger border-opacity-25 rounded-pill px-2.5 py-1 fw-semibold" style="font-size: 0.74rem;"><i class="bi bi-exclamation-octagon-fill me-1"></i> Unserviceable: ${data.message || 'Delivery unavailable to this PIN code.'}</span>`;
                if (placeOrderBtn) placeOrderBtn.disabled = true;
                if (mobPlaceOrderBtn) mobPlaceOrderBtn.disabled = true;
            }
        })
        .catch(() => {
            badgeEl.innerHTML = '';
        });
    }
}

window.activeShippingCharge = {{ $shippingFee ?? 0.00 }};
window.activeResolvedCodFee = {{ $resolvedCodFee ?? 40.00 }};

function updateShippingFeeAndTotal(deliveryCharge, freeShippingMin, codFee) {
    const subtotalText = document.getElementById('summary-subtotal')?.innerText || '0';
    const subtotal = parseFloat(subtotalText.replace(/[^0-9.]/g, '')) || 0;
    const freeMin = parseFloat(freeShippingMin || 499);
    
    let shippingFee = 0.00;
    if (subtotal < freeMin) {
        shippingFee = parseFloat(deliveryCharge || 0);
    }
    window.activeShippingCharge = shippingFee;

    if (codFee !== undefined && codFee !== null) {
        window.activeResolvedCodFee = parseFloat(codFee || 40.00);
    }

    const shippingEl = document.getElementById('summary-shipping-fee');
    if (shippingEl) {
        if (shippingFee > 0) {
            shippingEl.innerHTML = `<span class="font-monospace text-dark fw-bold">+ ₹${shippingFee.toFixed(2)}</span>`;
        } else {
            shippingEl.innerHTML = `<span class="badge bg-success bg-opacity-10 text-success fw-bold rounded-pill px-2 py-0.5">FREE</span>`;
        }
    }

    const selectedPaymentVal = document.querySelector('input[name="payment_method"]:checked')?.value;
    const isCodSelected = (selectedPaymentVal === 'cod');
    const codFeeValEl = document.getElementById('summary-cod-fee-val');
    let appliedCodFee = 0.00;

    if (codFeeValEl) {
        if (isCodSelected) {
            appliedCodFee = window.activeResolvedCodFee;
            codFeeValEl.innerHTML = `<span class="font-monospace text-dark fw-bold">+ ₹${appliedCodFee.toFixed(2)}</span>`;
        } else {
            codFeeValEl.innerHTML = `<span class="badge bg-success bg-opacity-10 text-success fw-bold rounded-pill px-2 py-0.5">₹0.00 (Prepaid Offer)</span>`;
        }
    }

    const grandTotalEl = document.getElementById('summary-grand-total');
    if (grandTotalEl) {
        let basePayable = subtotal + shippingFee + appliedCodFee;
        
        const couponValEl = document.getElementById('summary-discount-val');
        if (couponValEl && !document.getElementById('summary-discount-row').classList.contains('d-none')) {
            const couponVal = parseFloat(couponValEl.innerText.replace(/[^0-9.]/g, '')) || 0;
            basePayable = Math.max(0, basePayable - couponVal);
        }

        const walletValEl = document.getElementById('summary-wallet-val');
        if (walletValEl && !document.getElementById('summary-wallet-row').classList.contains('d-none')) {
            const walletVal = parseFloat(walletValEl.innerText.replace(/[^0-9.]/g, '')) || 0;
            basePayable = Math.max(0, basePayable - walletVal);
        }

        grandTotalEl.innerText = `₹${basePayable.toFixed(2)}`;
    }

    if (typeof window.updatePaymentButtonLabel === 'function') {
        window.updatePaymentButtonLabel();
    }
}

document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('input[name="payment_method"]').forEach(radio => {
        radio.addEventListener('change', function() {
            updateShippingFeeAndTotal(window.activeShippingCharge, 499);
        });
    });
});

function openNewAddressForm() {
    document.getElementById('new-address-card').style.display = 'block';
    document.getElementById('new-address-card').scrollIntoView({ behavior: 'smooth' });
}

function closeNewAddressForm() {
    document.getElementById('new-address-card').style.display = 'none';
}

function showAddressSelectorModal() {
    const modal = new bootstrap.Modal(document.getElementById('addressSelectorModal'));
    modal.show();
}

function selectModalAddress(addr) {
    applySelectedAddress(addr);
    const modalEl = document.getElementById('addressSelectorModal');
    const modal = bootstrap.Modal.getInstance(modalEl);
    if (modal) modal.hide();
}

function handleSaveAddress(e) {
    e.preventDefault();
    const btn = document.getElementById('btn-save-address');
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Saving...';

    const payload = {
        name: document.getElementById('new_name').value.trim(),
        phone: document.getElementById('new_phone').value.trim(),
        address: document.getElementById('new_address').value.trim(),
        city: document.getElementById('new_city').value.trim(),
        state: document.getElementById('new_state').value.trim(),
        zip: document.getElementById('new_zip').value.trim(),
        country: 'India'
    };

    fetch("{{ route('checkout.save-address') }}", {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': "{{ csrf_token() }}",
            'Accept': 'application/json'
        },
        body: JSON.stringify(payload)
    })
    .then(async res => {
        const data = await res.json();
        if (!res.ok || !data.success) {
            let errorMsg = data.message || 'Failed to save address.';
            if (data.errors) {
                const firstKey = Object.keys(data.errors)[0];
                if (firstKey && data.errors[firstKey][0]) {
                    errorMsg = data.errors[firstKey][0];
                }
            }
            throw new Error(errorMsg);
        }
        return data;
    })
    .then(data => {
        userAddresses = data.all_addresses;
        applySelectedAddress(data.address);

        const successBanner = document.getElementById('address-success-banner');
        if (successBanner) {
            successBanner.style.display = 'block';
            successBanner.scrollIntoView({ behavior: 'smooth', block: 'center' });
            setTimeout(() => {
                successBanner.style.display = 'none';
            }, 5000);
        }

        renderModalAddresses(data.all_addresses);

        const btnChange = document.getElementById('btn-change-address');
        if (btnChange) btnChange.style.display = 'inline-block';

        closeNewAddressForm();

        document.getElementById('new_address').value = '';
        document.getElementById('new_city').value = '';
        document.getElementById('new_state').value = '';
        document.getElementById('new_zip').value = '';
    })
    .catch(err => {
        console.error(err);
        alert(err.message || 'An error occurred while saving the address.');
    })
    .finally(() => {
        btn.disabled = false;
        btn.innerHTML = '<i class="bi bi-bookmark-check me-1.5"></i> Save Address';
    });
}

function renderModalAddresses(addresses) {
    const container = document.getElementById('modal-address-list');
    container.innerHTML = addresses.map(addr => `
        <div class="col-12">
            <div class="card border rounded-3 p-3 address-modal-option cursor-pointer" onclick='selectModalAddress(${JSON.stringify(addr)})'>
                <div class="d-flex justify-content-between align-items-start gap-2">
                    <div>
                        <strong class="text-dark" style="font-size: 0.9rem;">${addr.name}</strong>
                        <p class="text-secondary mb-1 mt-0.5" style="font-size: 0.82rem;">${addr.address}, ${addr.city}, ${addr.state} - ${addr.zip}</p>
                        <div class="small text-muted" style="font-size: 0.78rem;"><i class="bi bi-telephone me-1"></i> ${addr.phone}</div>
                    </div>
                    <button type="button" class="btn btn-sm btn-outline-primary rounded-pill px-3 py-0.5 flex-shrink-0" style="font-size: 0.76rem;">Deliver Here</button>
                </div>
            </div>
        </div>
    `).join('');
}

window.quickApplyCoupon = function(code) {
    const input = document.getElementById('coupon_code_input');
    const form = document.getElementById('ajax-coupon-form');
    if (input && form) {
        input.value = code;
        form.dispatchEvent(new Event('submit', { cancelable: true, bubbles: true }));
    }
};

window.applyCouponFromModal = function(code) {
    const modalEl = document.getElementById('couponsModal');
    if (modalEl) {
        const modal = bootstrap.Modal.getInstance(modalEl) || new bootstrap.Modal(modalEl);
        modal.hide();
    }
    quickApplyCoupon(code);
};
</script>
@endpush

@endsection