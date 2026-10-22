@extends('layouts.customer')

@section('title', 'Secure Checkout — ' . \App\Models\Setting::get('store_name', 'ShopCalm'))

@push('styles')
<script src="https://cdn.jsdelivr.net/npm/canvas-confetti@1.9.2/dist/confetti.browser.min.js"></script>
<style>
    /* Prevent zoom horizontal overflow */
    html, body {
        overflow-x: hidden !important;
        max-width: 100vw;
    }
    #checkout-page-container {
        max-width: 1200px;
    }
    
    /* Spacious Step 1 Address Box & Delivery Notes Styling */
    .checkout-address-box {
        padding: 1.25rem 1.35rem;
        margin-bottom: 1.5rem !important;
        border-radius: 1rem;
    }
    @media (min-width: 768px) {
        .checkout-address-box {
            padding: 1.5rem 1.75rem;
            margin-bottom: 1.75rem !important;
        }
    }
    .checkout-delivery-notes-card {
        margin-top: 1.5rem !important;
        margin-bottom: 1.5rem !important;
        border-radius: 1rem;
        background: #ffffff;
        border: 1.5px solid #e2e8f0 !important;
    }
    @media (max-width: 767.98px) {
        .checkout-delivery-notes-card {
            margin-top: 1.75rem !important;
            margin-bottom: 1.75rem !important;
        }
    }
    /* Delivering To Summary Strip in Steps 2 & 3 */
    .checkout-delivering-strip {
        padding: 1.25rem 1.45rem !important;
        margin-bottom: 1.5rem !important;
        border-radius: 1rem !important;
        background: #ffffff;
        border: 1.5px solid #cbd5e1 !important;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.04);
    }
    .p-3\.5 { padding: 1.25rem !important; }
    .px-3\.5 { padding-left: 1.25rem !important; padding-right: 1.25rem !important; }
    .py-3\.5 { padding-top: 1.25rem !important; padding-bottom: 1.25rem !important; }
    .mb-3\.5 { margin-bottom: 1.25rem !important; }
    .card {
        max-width: 100% !important;
        overflow: hidden;
    }
    .text-break-word {
        word-break: normal !important;
        overflow-wrap: break-word !important;
    }

    /* 4-Step Stepper Progress Header */
    .checkout-step-badge {
        width: 30px;
        height: 30px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 700;
        font-size: 0.82rem;
        transition: all 0.3s ease;
        flex-shrink: 0;
    }
    .stepper-tab-item {
        max-width: 78px;
        text-align: center;
    }
    
    /* Payment Tiles */
    .payment-tile {
        border: 2px solid #e2e8f0;
        border-radius: 1.15rem;
        padding: 1.35rem 1.4rem;
        margin-bottom: 1.25rem !important;
        cursor: pointer;
        transition: all 0.25s cubic-bezier(0.16, 1, 0.3, 1);
        background: #ffffff;
        position: relative;
    }
    @media (min-width: 768px) {
        .payment-tile {
            padding: 1.6rem 1.8rem;
            margin-bottom: 1.4rem !important;
        }
    }
    .payment-tile:hover {
        border-color: #a5b4fc;
        background: #fafbff;
        transform: translateY(-1px);
        box-shadow: 0 4px 14px rgba(79, 70, 229, 0.06);
    }
    .payment-tile.active-tile {
        border-color: #4f46e5 !important;
        background: #fbfbfe !important;
        box-shadow: 0 4px 20px rgba(79, 70, 229, 0.12) !important;
    }
    .payment-tile.disabled-tile {
        border-color: #fca5a5 !important;
        background: #fff5f5 !important;
        cursor: not-allowed !important;
        opacity: 0.72;
        transform: none !important;
        box-shadow: none !important;
    }
    .payment-tile .form-check-input {
        width: 1.3rem;
        height: 1.3rem;
        accent-color: #4f46e5;
        cursor: pointer;
        margin-top: 0.15rem;
        flex-shrink: 0;
    }
    .payment-method-chip {
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        color: #1e293b;
        padding: 0.35rem 0.75rem;
        border-radius: 9999px;
        font-size: 0.75rem;
        font-weight: 500;
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
        transition: all 0.2s ease;
    }
    .payment-tile:hover .payment-method-chip {
        background: #ffffff;
        border-color: #cbd5e1;
    }
    
    /* Promo Input */
    #coupon_code_input {
        color: #0f172a !important;
        font-weight: 800 !important;
        letter-spacing: 1px !important;
        background-color: #ffffff !important;
        border: 1.5px solid #cbd5e1 !important;
        height: 42px;
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
        box-shadow: 0 4px 14px rgba(79, 70, 229, 0.3) !important;
        transition: all 0.2s ease;
    }
    .btn-checkout-cta:hover {
        transform: translateY(-1px);
        box-shadow: 0 6px 18px rgba(79, 70, 229, 0.4) !important;
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
        margin-bottom: 0.82rem;
    }
    .grand-total-row-wrapper {
        border-top: 1.5px dashed #cbd5e1 !important;
        margin-top: 1rem;
        padding-top: 1.15rem !important;
        padding-bottom: 0.25rem !important;
    }
    @media (max-width: 767.98px) {
        .grand-total-row-wrapper {
            margin-top: 0.85rem;
            padding-top: 0.95rem !important;
            padding-bottom: 0.2rem !important;
        }
    }
    
    .address-card-selected {
        border: 2px solid #10b981 !important;
        background: linear-gradient(135deg, #f0fdf4 0%, #ffffff 100%) !important;
        max-width: 100%;
        overflow: hidden;
    }
    
    .cursor-pointer { cursor: pointer; }
    .address-modal-option {
        transition: all 0.22s ease;
        border: 1.5px solid #e2e8f0 !important;
        background: #ffffff;
        border-radius: 1rem !important;
    }
    .address-modal-option:hover {
        border-color: #6366f1 !important;
        background: #f8fafc;
        transform: translateY(-2px);
        box-shadow: 0 4px 14px rgba(99, 102, 241, 0.08);
    }
    .address-modal-option.is-selected {
        border-color: #10b981 !important;
        background: linear-gradient(135deg, #f0fdf4 0%, #ffffff 100%) !important;
        box-shadow: 0 2px 10px rgba(16, 185, 129, 0.12);
    }
    /* Step 2 Boxes Custom Spacing & Visual Depth */
    .step2-box {
        border-radius: 0.85rem;
        padding: 1.25rem 1.35rem;
        margin-bottom: 1.5rem !important;
    }
    @media (min-width: 768px) {
        .step2-box {
            padding: 1.45rem 1.6rem;
            margin-bottom: 1.65rem !important;
        }
    }
    .step2-wallet-box {
        background: linear-gradient(135deg, #f0fdf4 0%, #ffffff 100%);
        border: 1.5px solid #a7f3d0 !important;
        box-shadow: 0 1px 4px rgba(16, 185, 129, 0.08);
    }
    .step2-coupon-box {
        background: #f8fafc;
        border: 1.5px solid #e2e8f0 !important;
        box-shadow: 0 1px 4px rgba(0, 0, 0, 0.03);
    }
    .step2-savings-box {
        background: linear-gradient(135deg, #ecfdf5 0%, #f0fdf4 100%);
        border: 1.5px solid #6ee7b7 !important;
        box-shadow: 0 2px 8px rgba(16, 185, 129, 0.09);
    }

    /* Step 4 Confirm Order Spacious Styling */
    .confirm-review-card {
        border-radius: 0.95rem;
        border: 1.5px solid #e2e8f0 !important;
        background: #ffffff;
        margin-bottom: 1.5rem !important;
        box-shadow: 0 1px 4px rgba(0, 0, 0, 0.04);
        overflow: hidden;
    }
    .confirm-review-card .card-header {
        background: #ffffff;
        padding: 1rem 1.35rem !important;
        border-bottom: 1px solid #f1f5f9 !important;
    }
    .confirm-review-card .card-body {
        padding: 1.25rem 1.35rem !important;
    }
    .confirm-delivery-banner {
        background: linear-gradient(135deg, #f0fdf4 0%, #ecfdf5 100%);
        border: 1.5px solid #a7f3d0 !important;
        border-radius: 0.95rem;
        padding: 1.25rem 1.35rem !important;
        margin-bottom: 1.5rem !important;
    }
    .confirm-terms-box {
        background: #f8fafc;
        border: 1.5px solid #cbd5e1 !important;
        border-radius: 0.85rem;
        padding: 0.95rem 1.1rem !important;
        margin-bottom: 1.15rem !important;
        transition: border-color 0.2s ease, background-color 0.2s ease;
    }

    @media (max-width: 767.98px) {
        .checkout-step-badge {
            width: 26px;
            height: 26px;
            font-size: 0.72rem;
        }
        .stepper-tab-item {
            max-width: 65px;
        }
        .payment-tile {
            padding: 1.25rem 1.35rem !important;
            border-radius: 1.1rem !important;
            margin-bottom: 1.25rem !important;
        }
        .confirm-review-card .card-body {
            padding: 1.15rem 1.2rem !important;
        }
        .confirm-review-card .card-header {
            padding: 0.85rem 1.15rem !important;
        }
        .confirm-delivery-banner {
            padding: 1.15rem 1.2rem !important;
        }
        .confirm-terms-box {
            padding: 0.9rem 1rem !important;
            margin-bottom: 1rem !important;
        }

        /* Mobile Address Modal & Step 1 Enhancements */
        #addressSelectorModal .modal-dialog {
            margin: 0.75rem auto !important;
            width: calc(100% - 1.25rem) !important;
            max-width: 100% !important;
        }
        #addressSelectorModal .modal-content {
            border-radius: 1.25rem !important;
        }
        #addressSelectorModal .modal-body {
            padding: 1rem 0.9rem !important;
            max-height: 72vh;
            overflow-y: auto;
        }
        .address-modal-option {
            padding: 1rem 1.15rem !important;
            border-radius: 0.95rem !important;
        }
        .checkout-address-box {
            padding: 1.1rem 1.15rem !important;
            border-radius: 0.95rem !important;
            margin-bottom: 1.25rem !important;
        }
        .checkout-delivery-notes-card .card-body {
            padding: 1.15rem 1.15rem !important;
        }
        .checkout-delivering-strip {
            padding: 1.25rem 1.2rem !important;
            margin-top: 0.35rem !important;
            margin-bottom: 1.35rem !important;
        }
        #btn-confirm-address {
            width: 100% !important;
            padding-top: 0.75rem !important;
            padding-bottom: 0.75rem !important;
            font-size: 0.92rem !important;
            justify-content: center;
        }
    }
</style>
@endpush

@section('content')
<div class="container py-3 py-md-4 px-3 px-sm-3 px-md-4" id="checkout-page-container">
    
    <!-- Top Breadcrumb -->
    <div class="d-flex align-items-center justify-content-between mb-2 mb-md-3">
        <a href="{{ route('cart.index') }}" class="text-decoration-none small text-muted fw-bold d-inline-flex align-items-center">
            <i class="bi bi-arrow-left me-1.5"></i> Back to Shopping Bag
        </a>
    </div>

    <!-- 4-Step Stepper Progress Bar (1. Address -> 2. Offers & Wallet -> 3. Payment -> 4. Confirm) -->
    <div class="row justify-content-center mb-3 mb-md-4">
        <div class="col-12 col-md-10 col-lg-8">
            <div class="d-flex align-items-start justify-content-between position-relative px-1 px-sm-2">
                <!-- Background Progress Line -->
                <div class="position-absolute" style="top: 15px; left: 12%; right: 12%; height: 2px; background: #e2e8f0; z-index: 0;"></div>
                <!-- Active Progress Line -->
                <div class="position-absolute" id="stepper-progress-line" style="top: 15px; left: 12%; width: 0%; height: 2px; background: #4f46e5; z-index: 1; transition: width 0.35s ease;"></div>

                <!-- Step 1: Address -->
                <div class="d-flex flex-column align-items-center position-relative cursor-pointer stepper-tab-item" id="stepper-tab-1" onclick="goToStep(1)" style="z-index: 2;">
                    <div class="checkout-step-badge text-white shadow-xs" id="stepper-badge-1" style="background: #4f46e5; box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.2) !important;">
                        1
                    </div>
                    <span class="fw-bold text-primary mt-1 text-center" id="stepper-text-1" style="font-size: 0.7rem; line-height: 1.15;">Address</span>
                </div>

                <!-- Step 2: Offers & Wallet -->
                <div class="d-flex flex-column align-items-center position-relative cursor-pointer stepper-tab-item" id="stepper-tab-2" onclick="goToStep(2)" style="z-index: 2;">
                    <div class="checkout-step-badge bg-light text-muted border" id="stepper-badge-2">
                        2
                    </div>
                    <span class="fw-semibold text-muted mt-1 text-center" id="stepper-text-2" style="font-size: 0.7rem; line-height: 1.15;">Offers & Wallet</span>
                </div>

                <!-- Step 3: Payment -->
                <div class="d-flex flex-column align-items-center position-relative cursor-pointer stepper-tab-item" id="stepper-tab-3" onclick="goToStep(3)" style="z-index: 2;">
                    <div class="checkout-step-badge bg-light text-muted border" id="stepper-badge-3">
                        3
                    </div>
                    <span class="fw-semibold text-muted mt-1 text-center" id="stepper-text-3" style="font-size: 0.7rem; line-height: 1.15;">Payment</span>
                </div>

                <!-- Step 4: Confirm -->
                <div class="d-flex flex-column align-items-center position-relative cursor-pointer stepper-tab-item" id="stepper-tab-4" onclick="goToStep(4)" style="z-index: 2;">
                    <div class="checkout-step-badge bg-light text-muted border" id="stepper-badge-4">
                        4
                    </div>
                    <span class="fw-semibold text-muted mt-1 text-center" id="stepper-text-4" style="font-size: 0.7rem; line-height: 1.15;">Confirm</span>
                </div>
            </div>
        </div>
    </div>

    @if(isset($pendingOrder) && $pendingOrder)
    <!-- Pending Order Restriction Banner -->
    <div id="pending-order-restriction-banner" class="card border-0 shadow-sm rounded-4 overflow-hidden mb-3.5" style="background: #ffffff; border: 2px solid #f59e0b !important;">
        <div class="p-3 p-md-3.5" style="background: linear-gradient(135deg, #fffbeb 0%, #fef3c7 100%);">
            <div class="d-flex align-items-start justify-content-between gap-3 flex-wrap">
                <div class="d-flex align-items-start gap-2.5">
                    <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0 shadow-xs"
                         style="width: 42px; height: 42px; background: #ea580c; color: #ffffff; font-size: 1.25rem;">
                        <i class="bi bi-exclamation-triangle-fill"></i>
                    </div>
                    <div>
                        <div class="d-flex align-items-center gap-2 flex-wrap mb-0.5">
                            <h6 class="fw-bold text-dark mb-0" style="font-size: 0.92rem;">Pending Order (#{{ $pendingOrder->order_number }})</h6>
                            <span class="badge bg-warning text-dark border border-warning rounded-pill px-2 py-0.5 fw-bold font-monospace" style="font-size: 0.72rem;">
                                ₹{{ number_format($pendingOrder->total_amount, 2) }}
                            </span>
                        </div>
                        <p class="text-secondary small mb-0" style="font-size: 0.8rem; line-height: 1.4;">
                            Please complete or cancel your pending order before placing a new one.
                        </p>
                    </div>
                </div>
                <div class="d-flex align-items-center gap-2 flex-wrap ms-auto">
                    <a href="{{ route('checkout.payment_failed', $pendingOrder) }}"
                       class="btn btn-primary rounded-pill px-3 py-1.5 fw-semibold btn-sm shadow-xs"
                       style="background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%); border: none; font-size: 0.8rem;">
                        <i class="bi bi-credit-card me-1"></i> Proceed with #{{ $pendingOrder->order_number }}
                    </a>
                    <button type="button" id="btn-cancel-pending-banner"
                            onclick="cancelPendingOrderOnCheckout({{ $pendingOrder->id }}, '{{ $pendingOrder->order_number }}')"
                            class="btn btn-outline-danger rounded-pill px-3 py-1.5 fw-semibold btn-sm"
                            style="font-size: 0.8rem; background: #ffffff;">
                        <i class="bi bi-x-circle me-1"></i> Cancel
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Main Checkout Form -->
    <form action="{{ route('checkout.place-order') }}" method="POST" id="checkout-form">
        @csrf
        <input type="hidden" name="use_wallet" id="hidden_use_wallet" value="{{ $useWallet ? '1' : '0' }}">
        <input type="hidden" name="shipping_name" id="hidden_shipping_name">
        <input type="hidden" name="shipping_email" id="hidden_shipping_email" value="{{ auth()->user()->email }}">
        <input type="hidden" name="shipping_phone" id="hidden_shipping_phone">
        <input type="hidden" name="shipping_address" id="hidden_shipping_address">
        <input type="hidden" name="shipping_city" id="hidden_shipping_city">
        <input type="hidden" name="shipping_state" id="hidden_shipping_state">
        <input type="hidden" name="shipping_zip" id="hidden_shipping_zip">
        <input type="hidden" name="shipping_country" id="hidden_shipping_country" value="India">

        <!-- ========================================================================= -->
        <!-- STEPS 1 & 2 CONTAINER: Focused, Distraction-Free (NO Price Details Card!) -->
        <!-- ========================================================================= -->
        <div id="checkout-step-1-2-col" class="row justify-content-center">
            <div class="col-12 col-md-10 col-lg-8 col-xl-7">

                <!-- ====================================================== -->
                <!-- STEP 1 VIEW: ADDRESS SELECTION                        -->
                <!-- ====================================================== -->
                <div id="checkout-step-1-view">
                    <div class="card border-0 shadow-xs rounded-4 overflow-hidden mb-4" style="background: #ffffff; border: 1.5px solid #4f46e5 !important;">
                        <div class="card-header bg-white py-3 px-3.5 px-md-4 border-bottom d-flex align-items-center justify-content-between flex-wrap gap-2">
                            <div class="d-flex align-items-center gap-2.5">
                                <div class="rounded-circle text-white d-flex align-items-center justify-content-center flex-shrink-0" style="width: 32px; height: 32px; background: #4f46e5; font-size: 0.9rem;">
                                    <i class="bi bi-geo-alt-fill"></i>
                                </div>
                                <div>
                                    <h6 class="fw-bold mb-0 text-dark" style="font-size: 0.96rem;">Step 1: Delivery Address</h6>
                                    <small class="text-muted" style="font-size: 0.76rem;">Where should we deliver your order?</small>
                                </div>
                            </div>
                            <button type="button" id="btn-saved-addresses-header" class="btn btn-sm btn-outline-primary rounded-pill px-3 py-1.5 fw-bold" onclick="showAddressSelectorModal()" style="font-size: 0.78rem; display: {{ $addresses->count() > 0 ? 'inline-block' : 'none' }};">
                                <i class="bi bi-geo-alt me-1"></i> <span id="saved-addresses-count-text">Saved Addresses ({{ $addresses->count() }})</span>
                            </button>
                        </div>

                        <div class="card-body p-3.5 p-sm-4 p-md-4">
                            <div id="address-error-banner" class="alert alert-danger small mb-3 rounded-3 border-0 bg-danger-subtle text-danger" style="display: none;">
                                <i class="bi bi-exclamation-triangle-fill me-2"></i><span>Please select or add a valid delivery address before proceeding.</span>
                            </div>

                            <div id="address-success-banner" class="alert alert-success small mb-3 rounded-3 border-0 bg-success-subtle text-success" style="display: none;">
                                <i class="bi bi-check-circle-fill me-2"></i><span>Address saved successfully!</span>
                            </div>

                            <!-- Active Address Box (Spacious & Clean) -->
                            <div id="active-address-display" class="checkout-address-box border address-card-selected overflow-hidden">
                                <div id="no-address-msg" style="display: {{ $addresses->count() > 0 ? 'none' : 'block' }};" class="text-center py-4 text-muted">
                                    <i class="bi bi-building-add fs-2 text-primary d-block mb-2"></i>
                                    <span class="small fw-semibold text-dark">No saved address found. Click <strong>"+ Add New Address"</strong> below to add one.</span>
                                </div>

                                @php $activeAddr = $addresses->count() > 0 ? ($addresses->firstWhere('is_default', true) ?? $addresses->first()) : null; @endphp
                                <div id="active-address-details" style="display: {{ $addresses->count() > 0 ? 'block' : 'none' }};">
                                    <div class="d-flex justify-content-between align-items-center mb-2 flex-wrap gap-2">
                                        <div class="d-flex align-items-center gap-2 min-w-0 flex-grow-1">
                                            <h6 class="fw-bold mb-0 text-dark text-truncate" style="font-size: 0.96rem;" id="display-name">{{ $activeAddr ? $activeAddr->name : '' }}</h6>
                                            <span class="badge bg-success text-white rounded-pill px-2.5 py-1 fw-bold flex-shrink-0" style="font-size: 0.68rem;">✓ Selected</span>
                                        </div>
                                        <button type="button" id="btn-switch-address" class="btn btn-sm btn-outline-primary rounded-pill px-3 py-1 fw-semibold flex-shrink-0" onclick="showAddressSelectorModal()" style="font-size: 0.78rem; display: {{ $addresses->count() > 1 ? 'inline-block' : 'none' }};">
                                            <i class="bi bi-arrow-repeat me-1"></i> Switch Address
                                        </button>
                                    </div>
                                    <p class="text-secondary mb-2 text-break-word" id="display-street-city" style="line-height: 1.55; font-size: 0.88rem;">
                                        {{ $activeAddr ? "{$activeAddr->address}, {$activeAddr->city}, {$activeAddr->state}" : '' }} @if($activeAddr)<span class="text-nowrap font-monospace fw-bold text-dark">- {{ $activeAddr->zip }}</span>@endif
                                    </p>
                                    <div class="small text-muted pt-1 text-nowrap" id="display-phone" style="font-size: 0.84rem; white-space: nowrap !important;">
                                        <i class="bi bi-telephone-fill text-muted me-1.5 small"></i> Mobile: <strong class="text-dark font-monospace">{{ $activeAddr ? $activeAddr->phone : '' }}</strong>
                                    </div>
                                    <div id="active-address-serviceability-badge" class="mt-2.5 text-break-word"></div>
                                </div>
                            </div>

                            <!-- Add New Address Button -->
                            <div class="d-flex justify-content-between align-items-center mb-4">
                                <button type="button" class="btn btn-sm btn-outline-primary rounded-pill px-3.5 py-2 fw-semibold" onclick="openNewAddressForm()" style="font-size: 0.82rem;">
                                    <i class="bi bi-plus-lg me-1"></i> Add New Address
                                </button>
                            </div>

                            <!-- Collapsible Add New Address Form -->
                            <div class="card border rounded-4 mb-4 overflow-hidden shadow-xs" id="new-address-card" style="display: {{ $addresses->count() === 0 ? 'block' : 'none' }}; border-color: #cbd5e1 !important; background: #f8fafc;">
                                <div class="card-header bg-white py-2.5 px-3.5 d-flex justify-content-between align-items-center border-bottom">
                                    <h6 class="fw-bold mb-0 text-dark" style="font-size: 0.88rem;"><i class="bi bi-house-add text-primary me-2"></i>Add New Address</h6>
                                    <button type="button" class="btn-close" id="btn-close-new-address-header" onclick="closeNewAddressForm()" style="display: {{ $addresses->count() > 0 ? 'inline-block' : 'none' }};"></button>
                                </div>
                                <div class="card-body p-3.5 p-md-4">
                                    <div id="new-address-form">
                                        <div class="row g-2.5">
                                            <div class="col-12">
                                                <label for="new_name" class="form-label fw-semibold small mb-1" style="font-size: 0.8rem;">Full Name <span class="text-danger">*</span></label>
                                                <input type="text" class="form-control form-control-sm rounded-3 py-1.5" id="new_name" value="{{ auth()->user()->name ?? '' }}" placeholder="Enter full name">
                                            </div>
                                            <div class="col-12 col-sm-6">
                                                <label for="new_phone" class="form-label fw-semibold small mb-1" style="font-size: 0.8rem;">Mobile Number <span class="text-danger">*</span></label>
                                                <input type="tel" class="form-control form-control-sm rounded-3 py-1.5" id="new_phone" value="{{ auth()->user()->mobile_number ?? '' }}" placeholder="10-digit mobile" maxlength="15">
                                            </div>
                                            <div class="col-12 col-sm-6">
                                                <label for="new_zip" class="form-label fw-semibold small mb-1" style="font-size: 0.8rem;">Pincode <span class="text-danger">*</span></label>
                                                <input type="text" class="form-control form-control-sm rounded-3 font-monospace fw-bold py-1.5" id="new_zip" placeholder="6-digit PIN" maxlength="6" oninput="handleNewPincodeLookup(this.value)">
                                                <small id="new_zip_lookup_hint" class="text-muted d-block mt-0.5" style="font-size: 0.7rem;"></small>
                                            </div>
                                            <div class="col-12">
                                                <label for="new_address" class="form-label fw-semibold small mb-1" style="font-size: 0.8rem;">Street Address <span class="text-danger">*</span></label>
                                                <textarea class="form-control form-control-sm rounded-3 p-2" id="new_address" rows="2" placeholder="House no, Street, Landmark"></textarea>
                                            </div>
                                            <div class="col-12 col-sm-6">
                                                <label for="new_city" class="form-label fw-semibold small mb-1" style="font-size: 0.8rem;">City <span class="text-danger">*</span></label>
                                                <input type="text" class="form-control form-control-sm rounded-3 py-1.5" id="new_city" placeholder="City">
                                            </div>
                                            <div class="col-12 col-sm-6">
                                                <label for="new_state" class="form-label fw-semibold small mb-1" style="font-size: 0.8rem;">State <span class="text-danger">*</span></label>
                                                <input type="text" class="form-control form-control-sm rounded-3 py-1.5" id="new_state" placeholder="State">
                                            </div>
                                        </div>

                                        <div class="d-flex justify-content-end gap-2 mt-3">
                                            <button type="button" id="btn-cancel-new-address" class="btn btn-light border rounded-pill px-3.5 btn-sm fw-semibold" onclick="closeNewAddressForm()" style="display: {{ $addresses->count() > 0 ? 'inline-block' : 'none' }};">Cancel</button>
                                            <button type="button" onclick="handleSaveAddress(event)" class="btn btn-primary rounded-pill px-4 btn-sm shadow-xs fw-semibold" id="btn-save-address" style="background: #4f46e5; border: none;">
                                                <i class="bi bi-bookmark-check me-1"></i> Save Address
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Delivery Notes (Spacious on Mobile + Word/Character Limitation) -->
                            <div class="card checkout-delivery-notes-card shadow-xs">
                                <div class="card-body p-3.5 p-sm-4 p-md-4">
                                    <div class="d-flex align-items-center justify-content-between mb-2 flex-wrap gap-2">
                                        <label for="notes" class="fw-bold text-dark d-flex align-items-center gap-2 mb-0" style="font-size: 0.94rem;">
                                            <span class="rounded-circle d-flex align-items-center justify-content-center text-primary bg-primary bg-opacity-10" style="width: 30px; height: 30px;">
                                                <i class="bi bi-chat-left-text-fill" style="font-size: 0.85rem;"></i>
                                            </span>
                                            Delivery Instructions / Notes
                                        </label>
                                        <div class="d-flex align-items-center gap-2">
                                            <span class="badge bg-light text-secondary border rounded-pill px-2.5 py-1" style="font-size: 0.72rem;">Optional</span>
                                            <span id="notes-counter" class="badge rounded-pill bg-light text-secondary border font-monospace px-2.5 py-1" style="font-size: 0.72rem;">0 / 150</span>
                                        </div>
                                    </div>
                                    <p class="text-secondary small mb-2.5" style="font-size: 0.82rem; line-height: 1.5;">
                                        Add short instructions for delivery partner (e.g. landmark, call before delivery, leave with security). Max 150 characters (~25 words).
                                    </p>
                                    <textarea class="form-control rounded-3 p-3" 
                                              id="notes" 
                                              name="notes" 
                                              rows="2" 
                                              maxlength="150"
                                              placeholder="e.g. Ring bell twice / Call before reaching / Leave with gate security." 
                                              style="font-size: 0.86rem; line-height: 1.5; border: 1.5px solid #cbd5e1 !important; resize: none;"></textarea>
                                    <div class="d-flex justify-content-between align-items-center mt-2 px-0.5">
                                        <small class="text-muted" style="font-size: 0.74rem;">
                                            <i class="bi bi-shield-check text-success me-1"></i>Kept concise for delivery partner mobile screen
                                        </small>
                                        <small id="notes-warning" class="text-danger fw-semibold" style="font-size: 0.74rem; display: none;">
                                            <i class="bi bi-exclamation-circle me-1"></i>Max limit reached (150 chars)
                                        </small>
                                    </div>
                                </div>
                            </div>

                            <!-- Items in Order Summary Card (Spacious & Clean) -->
                            <div class="card border-0 rounded-4 shadow-xs mb-4 overflow-hidden" style="background: #ffffff; border: 1.5px solid #e2e8f0 !important;">
                                <div class="card-body p-3.5 p-sm-4">
                                    <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
                                        <div class="d-flex align-items-center gap-2.5 min-w-0">
                                            <div class="rounded-circle d-flex align-items-center justify-content-center text-primary flex-shrink-0" style="width: 38px; height: 38px; background: rgba(79, 70, 229, 0.08);">
                                                <i class="bi bi-bag-check-fill fs-5"></i>
                                            </div>
                                            <div class="min-w-0">
                                                <div class="d-flex align-items-center gap-2 flex-wrap">
                                                    <h6 class="fw-bold mb-0 text-dark" style="font-size: 0.94rem;">Items in Order</h6>
                                                    <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill px-2.5 py-0.5 fw-bold" style="font-size: 0.72rem;">
                                                        {{ $cart->items->sum('quantity') }} {{ Str::plural('item', $cart->items->sum('quantity')) }}
                                                    </span>
                                                </div>
                                                <small class="text-secondary d-block mt-0.5" style="font-size: 0.78rem;">Selected products ready for delivery</small>
                                            </div>
                                        </div>
                                        <a href="{{ route('cart.index') }}" class="btn btn-sm btn-outline-primary rounded-pill px-3.5 py-1.5 fw-semibold d-inline-flex align-items-center gap-1.5 flex-shrink-0" style="font-size: 0.8rem;">
                                            <i class="bi bi-bag"></i>
                                            <span>View Bag</span>
                                            <i class="bi bi-arrow-right small"></i>
                                        </a>
                                    </div>

                                    @if($cart->items->isNotEmpty())
                                    <div class="d-flex align-items-center gap-2 pt-3 border-top mt-3 flex-wrap">
                                        @foreach($cart->items as $cartItem)
                                            <div class="d-flex align-items-center gap-2 bg-light rounded-3 px-2.5 py-1.5 border" style="font-size: 0.78rem; border-color: #e2e8f0 !important;">
                                                @if($cartItem->product && $cartItem->product->main_image)
                                                    <img src="{{ asset('storage/' . $cartItem->product->main_image) }}" alt="{{ $cartItem->product->name }}" class="rounded-2 border flex-shrink-0 bg-white" style="width: 26px; height: 26px; object-fit: contain;">
                                                @else
                                                    <div class="rounded-2 bg-white border d-flex align-items-center justify-content-center flex-shrink-0 text-muted" style="width: 26px; height: 26px;">
                                                        <i class="bi bi-box-seam" style="font-size: 0.75rem;"></i>
                                                    </div>
                                                @endif
                                                <span class="text-dark fw-medium text-truncate" style="max-width: 140px;" title="{{ $cartItem->product->name ?? 'Product' }}">
                                                    {{ $cartItem->product->name ?? 'Product' }}
                                                </span>
                                                <span class="badge bg-white text-dark border rounded-pill px-1.5 py-0.5" style="font-size: 0.68rem;">×{{ $cartItem->quantity }}</span>
                                            </div>
                                        @endforeach
                                    </div>
                                    @endif
                                </div>
                            </div>

                            <!-- Step 1 Sleek Proceed Button (Responsive, Thumb-Friendly on Mobile) -->
                            <div class="d-flex justify-content-end mt-3">
                                <button type="button" class="btn btn-primary rounded-pill px-3.5 px-sm-4 py-2 fw-semibold shadow-xs d-inline-flex align-items-center justify-content-center gap-1.5 w-100 w-sm-auto" id="btn-confirm-address" onclick="proceedFromStep1()" style="font-size: 0.86rem; background: #4f46e5; border-color: #4f46e5;">
                                    <span>Proceed to Offers & Wallet</span>
                                    <i class="bi bi-arrow-right"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- ====================================================== -->
                <!-- STEP 2 VIEW: OFFERS & WALLET (Spacious, No Quick Apply)-->
                <!-- ====================================================== -->
                <div id="checkout-step-2-view" class="d-none">
                    
                    <!-- Selected Address Summary Strip (Zoom-Safe, Full Fit) -->
                    <div class="checkout-delivering-strip d-flex justify-content-between align-items-center gap-3 flex-wrap flex-sm-nowrap overflow-hidden">
                        <div class="d-flex align-items-start gap-3 min-w-0 flex-grow-1">
                            <div class="rounded-circle text-success bg-success bg-opacity-10 d-flex align-items-center justify-content-center flex-shrink-0 mt-0.5" style="width: 36px; height: 36px;">
                                <i class="bi bi-geo-alt-fill fs-5"></i>
                            </div>
                            <div class="min-w-0 flex-grow-1 text-break-word">
                                <span class="text-muted small d-block mb-1 fw-semibold text-uppercase" style="font-size: 0.72rem; line-height: 1.2; letter-spacing: 0.4px;">DELIVERING TO</span>
                                <strong class="text-dark d-block text-break-word" id="step-2-address-summary" style="font-size: 0.88rem; line-height: 1.5;"></strong>
                            </div>
                        </div>
                        <button type="button" class="btn btn-sm btn-outline-primary rounded-pill px-3 py-1.5 fw-bold flex-shrink-0 align-self-sm-center align-self-start" onclick="goToStep(1)" style="font-size: 0.78rem;">
                            <i class="bi bi-pencil me-1"></i> Change
                        </button>
                    </div>

                    <!-- Offers & Wallet Card (With generous breathing spacing) -->
                    <div class="card border-0 shadow-xs rounded-4 overflow-hidden mb-4" style="background: #ffffff; border: 1.5px solid #10b981 !important;">
                        <div class="card-header bg-white py-3 px-3 px-md-4 border-bottom d-flex align-items-center justify-content-between flex-wrap gap-2">
                            <div class="d-flex align-items-center gap-2">
                                <div class="rounded-circle text-white d-flex align-items-center justify-content-center" style="width: 30px; height: 30px; background: linear-gradient(135deg, #10b981 0%, #059669 100%); font-size: 0.9rem;">
                                    <i class="bi bi-gift-fill"></i>
                                </div>
                                <h6 class="fw-bold mb-0 text-dark" style="font-size: 0.95rem;">Step 2: Offers, Coupons & Wallet</h6>
                            </div>
                            <span class="badge bg-success bg-opacity-10 text-success rounded-pill px-2.5 py-1 fw-bold" style="font-size: 0.72rem;">
                                <i class="bi bi-stars me-1"></i> Instant Savings
                            </span>
                        </div>

                        <div class="card-body p-3 p-md-4">
                            
                            <!-- ============================================================== -->
                            <!-- SECTION 1 (FIRST): SHOPCALM WALLET                             -->
                            <!-- ============================================================== -->
                            @if(isset($userWallet))
                            <div class="step2-box step2-wallet-box mb-4">
                                <div class="d-flex align-items-center justify-content-between gap-3 flex-wrap flex-sm-nowrap">
                                    <div class="d-flex align-items-center gap-3 min-w-0">
                                        <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0 shadow-xs" style="width: 44px; height: 44px; background: #dcfce7; color: #16a34a;">
                                            <i class="bi bi-wallet2 fs-4"></i>
                                        </div>
                                        <div class="min-w-0">
                                            <div class="d-flex align-items-center gap-2 flex-wrap">
                                                <span class="fw-bold text-dark" style="font-size: 0.95rem;">ShopCalm Wallet</span>
                                                @if($userWallet->balance > 0)
                                                    <span class="badge bg-success text-white rounded-pill px-2.5 py-1 fw-bold font-monospace" style="font-size: 0.74rem;">
                                                        ₹{{ number_format($userWallet->balance, 2) }} Available
                                                    </span>
                                                @else
                                                    <span class="badge bg-light text-secondary border rounded-pill px-2 py-0.5" style="font-size: 0.7rem;">
                                                        ₹0.00 Balance
                                                    </span>
                                                @endif
                                            </div>
                                            <div class="text-secondary small mt-1 text-break-word" style="font-size: 0.78rem; line-height: 1.45;">
                                                @if($isWalletFrozen)
                                                    <span class="text-danger fw-semibold"><i class="bi bi-lock-fill me-1"></i>Wallet is locked by admin for security.</span>
                                                @elseif($userWallet->balance > 0)
                                                    <span>Use your wallet balance to pay for this order instantly.</span>
                                                @else
                                                    <span>Available balance is ₹0.00. Earn wallet credits with returns and rewards.</span>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                    @if($userWallet->balance > 0 && !$isWalletFrozen)
                                    <div class="form-check form-switch mb-0 flex-shrink-0 ms-auto">
                                        <input class="form-check-input custom-wallet-switch" type="checkbox" role="switch" id="toggle-wallet-switch" {{ $useWallet ? 'checked' : '' }} style="cursor: pointer; width: 2.7em; height: 1.4em;">
                                    </div>
                                    @endif
                                </div>
                            </div>
                            @endif

                            <!-- 100% Wallet Paid Notice Banner -->
                            <div id="full-wallet-pay-notice" class="alert alert-success border-0 rounded-3 mb-4 p-3 shadow-xs {{ ($useWallet && $grandTotal <= 0) ? '' : 'd-none' }}" style="background: #dcfce7; color: #166534;">
                                <div class="d-flex align-items-center gap-2">
                                    <i class="bi bi-shield-check fs-4 text-success flex-shrink-0"></i>
                                    <div>
                                        <h6 class="fw-bold mb-0.5" style="font-size: 0.88rem;">100% Covered by ShopCalm Wallet!</h6>
                                        <small style="font-size: 0.76rem; line-height: 1.4;" class="d-block">Your order is completely covered by wallet credits (₹0.00 remaining to pay).</small>
                                    </div>
                                </div>
                            </div>

                            <!-- ============================================================== -->
                            <!-- SECTION 2 (THEN): APPLY COUPONS & PROMO CODES                  -->
                            <!-- ============================================================== -->
                            <div class="step2-box step2-coupon-box mb-4">
                                <div class="d-flex align-items-center justify-content-between mb-3 flex-wrap gap-2">
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0" style="width: 28px; height: 28px; background: #e0e7ff; color: #4338ca;">
                                            <i class="bi bi-tag-fill" style="font-size: 0.82rem;"></i>
                                        </div>
                                        <h6 class="fw-bold mb-0 text-dark" style="font-size: 0.92rem;">Apply Coupon Code</h6>
                                    </div>
                                    @if(isset($availableCoupons) && $availableCoupons->count() > 0)
                                        <button type="button" class="btn btn-sm btn-link text-primary text-decoration-none fw-bold p-0" data-bs-toggle="modal" data-bs-target="#couponsModal" style="font-size: 0.78rem;">
                                            <i class="bi bi-ticket-perforated text-warning me-1"></i>View Available Coupons ({{ $availableCoupons->count() }})
                                        </button>
                                    @endif
                                </div>

                                <div id="coupon-status-wrapper">
                                    @if($couponCode)
                                        <div class="p-3 rounded-3 shadow-xs d-flex align-items-center justify-content-between bg-white border" id="applied-coupon-box" style="border-color: #86efac !important;">
                                            <div class="d-flex align-items-center gap-2">
                                                <i class="bi bi-check-circle-fill text-success fs-4"></i>
                                                <div>
                                                    <span class="font-monospace fw-bold text-success px-2.5 py-0.5 rounded" style="background: #dcfce7; border: 1px dashed #22c55e; font-size: 0.82rem;">{{ $couponCode }}</span>
                                                    <small class="text-dark d-block mt-1 fw-semibold" style="font-size: 0.76rem;">Saved ₹{{ number_format($discountAmount, 2) }} on your order</small>
                                                </div>
                                            </div>
                                            <button type="button" class="btn btn-sm btn-outline-danger rounded-pill px-3 py-1 fw-bold" id="btn-remove-coupon-ajax" onclick="handleRemoveCoupon()" style="font-size: 0.74rem;">Remove</button>
                                        </div>
                                    @else
                                        <div class="d-flex gap-2">
                                            <input type="text" id="coupon_code_input" class="form-control rounded-3 font-monospace fw-bold" placeholder="ENTER COUPON CODE" style="text-transform: uppercase; font-size: 0.88rem; height: 44px;">
                                            <button type="button" class="btn btn-primary rounded-3 px-4 fw-bold flex-shrink-0" onclick="handleApplyCoupon()" style="background: #4f46e5; border: none; font-size: 0.84rem; height: 44px;">APPLY</button>
                                        </div>
                                        <div class="text-muted mt-2 small" style="font-size: 0.75rem;">
                                            <i class="bi bi-info-circle text-primary me-1"></i>Apply valid promo code to get instant order discounts.
                                        </div>
                                    @endif
                                </div>
                            </div>

                            <!-- ============================================================== -->
                            <!-- SECTION 3 (FINALLY): SAVED THINGS & TOTAL SAVINGS SUMMARY      -->
                            <!-- ============================================================== -->
                            <div class="step2-box step2-savings-box mb-4">
                                <!-- Top Total Savings Row -->
                                <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="rounded-circle text-white d-flex align-items-center justify-content-center shadow-xs flex-shrink-0" style="width: 32px; height: 32px; background: linear-gradient(135deg, #10b981 0%, #059669 100%);">
                                            <i class="bi bi-stars fs-6"></i>
                                        </div>
                                        <div>
                                            <h6 class="fw-bold mb-0 text-success" style="font-size: 0.94rem;">Total Savings on This Order</h6>
                                            <small class="text-muted d-block" style="font-size: 0.74rem;">Combined discounts applied to your cart</small>
                                        </div>
                                    </div>
                                    <div class="text-end">
                                        <span class="font-monospace fw-bolder fs-5 text-success d-block" id="card-total-savings">
                                            ₹{{ number_format($totalSavings, 2) }}
                                        </span>
                                    </div>
                                </div>

                                <!-- Detailed Saved Things Breakdown -->
                                <div class="mt-3 pt-2.5 border-top" style="border-color: #a7f3d0 !important;">
                                    <div class="d-flex justify-content-between align-items-center py-1 text-secondary" style="font-size: 0.82rem;" id="saved-breakdown-mrp-row">
                                        <span><i class="bi bi-check2 text-success me-1.5"></i>Product MRP Discount</span>
                                        <span class="font-monospace fw-bold text-success">- ₹{{ number_format($totalDiscount, 2) }}</span>
                                    </div>
                                    
                                    <div class="d-flex justify-content-between align-items-center py-1 text-secondary {{ $discountAmount > 0 ? '' : 'd-none' }}" style="font-size: 0.82rem;" id="saved-breakdown-coupon-row">
                                        <span><i class="bi bi-check2 text-success me-1.5"></i>Coupon Discount</span>
                                        <span class="font-monospace fw-bold text-success" id="saved-breakdown-coupon">- ₹{{ number_format($discountAmount, 2) }}</span>
                                    </div>

                                    <div class="d-flex justify-content-between align-items-center py-1 text-secondary {{ $walletDiscount > 0 ? '' : 'd-none' }}" style="font-size: 0.82rem;" id="saved-breakdown-wallet-row">
                                        <span><i class="bi bi-check2 text-success me-1.5"></i>ShopCalm Wallet Balance</span>
                                        <span class="font-monospace fw-bold text-success" id="saved-breakdown-wallet">- ₹{{ number_format($walletDiscount, 2) }}</span>
                                    </div>

                                    @if($offerDiscount > 0)
                                    <div class="d-flex justify-content-between align-items-center py-1 text-secondary" style="font-size: 0.82rem;" id="saved-breakdown-offer-row">
                                        <span><i class="bi bi-check2 text-success me-1.5"></i>Special Offer Discount</span>
                                        <span class="font-monospace fw-bold text-success">- ₹{{ number_format($offerDiscount, 2) }}</span>
                                    </div>
                                    @endif
                                </div>
                            </div>

                            <!-- Step 2 Compact Action Buttons with Generous Spacing -->
                            <div class="d-flex justify-content-between align-items-center gap-2 mt-4 pt-2">
                                <button type="button" class="btn btn-light border rounded-pill px-3 px-sm-3.5 py-2 fw-semibold text-secondary btn-sm d-inline-flex align-items-center gap-1.5" onclick="goToStep(1)" style="font-size: 0.82rem;">
                                    <i class="bi bi-arrow-left"></i> <span>Back<span class="d-none d-sm-inline"> to Address</span></span>
                                </button>
                                <button type="button" class="btn btn-primary rounded-pill px-3.5 px-sm-4 py-2 fw-semibold shadow-xs d-inline-flex align-items-center gap-1.5" onclick="proceedFromStep2()" style="font-size: 0.85rem; background: #4f46e5; border-color: #4f46e5;">
                                    <span>Proceed to Payment</span>
                                    <i class="bi bi-arrow-right"></i>
                                </button>
                            </div>

                        </div>
                    </div>
                </div>

            </div>
        </div>

        <!-- ========================================================================= -->
        <!-- STEPS 3 & 4 CONTAINER: 2-Column Layout (With Price Details Card on Right) -->
        <!-- ========================================================================= -->
        <div id="checkout-step-3-4-col" class="row g-3 g-lg-4 d-none">
            
            <!-- Left Column: Step 3 (Payment) OR Step 4 (Confirm) -->
            <div class="col-lg-7 col-xl-8">

                <!-- ====================================================== -->
                <!-- STEP 3 VIEW: PAYMENT METHOD SELECTION                 -->
                <!-- ====================================================== -->
                <div id="checkout-step-3-view" class="d-none">
                    
                    <!-- Selected Address Summary Strip (Spacious, Clean) -->
                    <div class="checkout-delivering-strip d-flex justify-content-between align-items-center gap-3 flex-wrap flex-sm-nowrap overflow-hidden">
                        <div class="d-flex align-items-start gap-3 min-w-0 flex-grow-1">
                            <div class="rounded-circle text-success bg-success bg-opacity-10 d-flex align-items-center justify-content-center flex-shrink-0 mt-0.5" style="width: 36px; height: 36px;">
                                <i class="bi bi-geo-alt-fill fs-5"></i>
                            </div>
                            <div class="min-w-0 flex-grow-1 text-break-word">
                                <span class="text-muted small d-block mb-1 fw-semibold text-uppercase" style="font-size: 0.72rem; line-height: 1.2; letter-spacing: 0.4px;">DELIVERING TO</span>
                                <strong class="text-dark d-block text-break-word" id="step-3-address-summary" style="font-size: 0.88rem; line-height: 1.5;"></strong>
                            </div>
                        </div>
                        <button type="button" class="btn btn-sm btn-outline-primary rounded-pill px-3 py-1.5 fw-bold flex-shrink-0 align-self-sm-center align-self-start" onclick="goToStep(1)" style="font-size: 0.78rem;">
                            <i class="bi bi-pencil me-1"></i> Change
                        </button>
                    </div>

                    <!-- Step 3: 100% Wallet/Offers Covered Notice Banner -->
                    <div id="step-3-wallet-full-notice" class="alert alert-success border-0 rounded-4 mb-4 p-3 p-sm-4 shadow-xs {{ ($useWallet && $grandTotal <= 0) ? '' : 'd-none' }}" style="background: linear-gradient(135deg, #ecfdf5 0%, #d1fae5 100%); border: 1.5px solid #6ee7b7 !important; color: #065f46;">
                        <div class="d-flex align-items-center gap-3">
                            <div class="rounded-circle d-flex align-items-center justify-content-center text-white flex-shrink-0 shadow-xs" style="width: 42px; height: 42px; background: #10b981;">
                                <i class="bi bi-shield-fill-check fs-5"></i>
                            </div>
                            <div class="flex-grow-1 min-w-0">
                                <div class="d-flex align-items-center gap-2 flex-wrap mb-1">
                                    <span class="badge bg-success text-white fw-bold rounded-pill px-2.5 py-0.5" style="font-size: 0.72rem;">100% COVERED</span>
                                    <strong class="text-dark" style="font-size: 0.95rem;">No Payment Required (₹0.00 Due)</strong>
                                </div>
                                <p class="mb-0 small text-secondary" style="font-size: 0.82rem; line-height: 1.45;">
                                    Your order amount is fully covered by your <strong>ShopCalm Wallet &amp; Offers</strong>. Online &amp; COD payment options are automatically blocked because no external payment is needed. Click <strong>"Continue to Confirm"</strong> to review and place your order!
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Payment Method Card -->
                    <div class="card border-0 shadow-xs rounded-4 overflow-hidden mb-4" style="background: #ffffff; border: 1.5px solid #4f46e5 !important;">
                        <div class="card-header bg-white py-3 px-3.5 px-md-4 border-bottom d-flex align-items-center justify-content-between flex-wrap gap-2">
                            <div class="d-flex align-items-center gap-2.5">
                                <div class="rounded-circle text-white d-flex align-items-center justify-content-center flex-shrink-0 shadow-xs" style="width: 32px; height: 32px; background: #4f46e5; font-size: 0.95rem;">
                                    <i class="bi bi-credit-card-2-front-fill"></i>
                                </div>
                                <div>
                                    <h6 class="fw-bold mb-0 text-dark" style="font-size: 0.96rem;">Step 3: Select Payment Method</h6>
                                    <small class="text-muted" style="font-size: 0.76rem;">Choose your preferred payment method</small>
                                </div>
                            </div>
                            <div class="d-flex align-items-center gap-2">
                                <span class="badge bg-success text-white rounded-pill px-3 py-1.5 fw-bold shadow-xs d-inline-flex align-items-center gap-1.5" style="font-size: 0.75rem; letter-spacing: 0.2px;">
                                    <i class="bi bi-shield-fill-check"></i> 100% Secure Checkout
                                </span>
                                <button type="button" class="btn btn-sm btn-link text-primary text-decoration-none fw-bold p-0 ms-1" onclick="goToStep(2)" style="font-size: 0.76rem;">
                                    <i class="bi bi-gift me-1"></i> Offers
                                </button>
                            </div>
                        </div>

                        <div class="card-body p-3.5 p-sm-4">
                            <!-- Payment Tiles Container -->
                            <div id="payment-tiles-container" style="{{ ($useWallet && $grandTotal <= 0) ? 'opacity: 0.45; pointer-events: none; filter: grayscale(1);' : '' }}">
                                
                                <!-- Option 1: Razorpay Online Payment (Default) -->
                                <div class="payment-tile active-tile" onclick="document.getElementById('pay_online').checked = true; document.getElementById('pay_online').dispatchEvent(new Event('change'));">
                                    <div class="d-flex align-items-start gap-3">
                                        <input class="form-check-input mt-1 flex-shrink-0 cursor-pointer" type="radio" name="payment_method" id="pay_online" value="online" checked form="checkout-form" style="width: 1.25rem; height: 1.25rem; accent-color: #4f46e5;">
                                        <div class="flex-grow-1 min-w-0 text-break-word">
                                            <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-1">
                                                <label class="form-check-label fw-bold text-dark mb-0 cursor-pointer fs-6" for="pay_online">
                                                    ⚡ Instant Online Payment
                                                </label>
                                                <span class="badge bg-success text-white rounded-pill px-2.5 py-1 fw-bold" style="font-size: 0.68rem; letter-spacing: 0.3px;">FASTEST &amp; RECOMMENDED</span>
                                            </div>
                                            <p class="text-secondary mb-2" style="font-size: 0.82rem; line-height: 1.45;">
                                                Google Pay, PhonePe, Paytm, UPI, Cards &amp; NetBanking.
                                            </p>
                                            
                                            <!-- Concise Supported Mode Pills -->
                                            <div class="d-flex align-items-center gap-1.5 flex-wrap">
                                                <span class="payment-method-chip" style="padding: 0.25rem 0.65rem; font-size: 0.72rem;">
                                                    <i class="bi bi-qr-code text-primary"></i> UPI
                                                </span>
                                                <span class="payment-method-chip" style="padding: 0.25rem 0.65rem; font-size: 0.72rem;">
                                                    <i class="bi bi-credit-card-2-front text-secondary"></i> Cards
                                                </span>
                                                <span class="payment-method-chip" style="padding: 0.25rem 0.65rem; font-size: 0.72rem;">
                                                    <i class="bi bi-bank text-info"></i> Net Banking
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                @php
                                    $isGlobalCodEnabled = (\App\Models\Setting::get('enable_cod', '1') === '1');
                                @endphp
                                @if($isGlobalCodEnabled)
                                <!-- Option 2: Cash On Delivery (COD) -->
                                <div class="payment-tile" id="payment-tile-cod" onclick="handleCodTileClick()">
                                    <div class="d-flex align-items-start gap-3">
                                        <input class="form-check-input mt-1 flex-shrink-0 cursor-pointer" type="radio" name="payment_method" id="cod" value="cod" form="checkout-form" style="width: 1.25rem; height: 1.25rem; accent-color: #4f46e5;">
                                        <div class="flex-grow-1 min-w-0 text-break-word">
                                            <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-1">
                                                <label class="form-check-label fw-bold text-dark mb-0 cursor-pointer fs-6" for="cod" id="cod-label">
                                                    💵 Cash on Delivery (COD)
                                                </label>
                                                <span id="cod-badge" class="badge bg-light text-secondary border rounded-pill px-2.5 py-1 fw-bold" style="font-size: 0.68rem;">PAY AT DOORSTEP</span>
                                            </div>
                                            <p class="text-secondary mb-0" id="cod-description" style="font-size: 0.82rem; line-height: 1.45;">
                                                Pay with physical cash or UPI QR code to delivery executive at doorstep.
                                            </p>

                                            <div id="cod-unavailable-notice" class="alert alert-danger py-2 px-3 small mt-2.5 rounded-3 d-none mb-0" style="font-size: 0.76rem;">
                                                <i class="bi bi-slash-circle me-1.5"></i> Cash on Delivery is unavailable for this PIN code.
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                @endif
                            </div>

                            <!-- Lightweight Safe & Secure Strip -->
                            <div class="d-flex align-items-center justify-content-center gap-3 pt-3 mt-2 border-top text-muted small flex-wrap" style="border-color: #f1f5f9 !important; font-size: 0.76rem;">
                                <span><i class="bi bi-shield-fill-check text-success me-1"></i>100% Safe Payments</span>
                                <span><i class="bi bi-lock-fill text-primary me-1"></i>256-Bit SSL Protection</span>
                                <span><i class="bi bi-arrow-repeat text-info me-1"></i>Fast Refunds</span>
                            </div>
                        </div>
                    </div>

                </div>

                <!-- ====================================================== -->
                <!-- STEP 4 VIEW: CONFIRM ORDER & PLACE ORDER / PAY        -->
                <!-- ====================================================== -->
                <div id="checkout-step-4-view" class="d-none">
                    
                    <!-- Estimated Delivery Date Banner (Spacious) -->
                    <div class="confirm-delivery-banner shadow-xs d-flex align-items-center gap-3">
                        <div class="rounded-circle d-flex align-items-center justify-content-center text-success flex-shrink-0 shadow-xs" style="width: 44px; height: 44px; background: #dcfce7;">
                            <i class="bi bi-truck fs-4"></i>
                        </div>
                        <div class="min-w-0 text-break-word">
                            <span class="fw-bold text-dark d-block" style="font-size: 0.95rem;" id="review-delivery-date-title">Estimated Delivery</span>
                            <small class="text-secondary d-block mt-0.5" id="review-delivery-date-text" style="font-size: 0.82rem; line-height: 1.45;">Standard Dispatch within 24-48 hours • Safe Doorstep Delivery</small>
                        </div>
                    </div>

                    <!-- Review Card 1: Confirmed Shipping Address (Spacious) -->
                    <div class="confirm-review-card shadow-xs">
                        <div class="card-header bg-white d-flex align-items-center justify-content-between">
                            <div class="d-flex align-items-center gap-2">
                                <div class="rounded-circle text-white d-flex align-items-center justify-content-center flex-shrink-0" style="width: 28px; height: 28px; background: #4f46e5; font-size: 0.85rem;">
                                    <i class="bi bi-geo-alt-fill"></i>
                                </div>
                                <span class="fw-bold text-dark" style="font-size: 0.92rem;">1. Delivering To</span>
                            </div>
                            <button type="button" class="btn btn-sm btn-outline-primary rounded-pill px-3 py-1 fw-bold" onclick="goToStep(1)" style="font-size: 0.76rem;">
                                <i class="bi bi-pencil me-1"></i> Change
                            </button>
                        </div>
                        <div class="card-body text-break-word">
                            <div class="fw-bold text-dark mb-1" id="review-address-name" style="font-size: 0.94rem;"></div>
                            <div class="text-secondary mb-2 text-break-word" id="review-address-text" style="font-size: 0.86rem; line-height: 1.55;"></div>
                            <div class="text-dark fw-semibold" id="review-address-phone" style="font-size: 0.82rem;"></div>
                        </div>
                    </div>

                    <!-- Review Card 2: Applied Offers & Wallet (Spacious) -->
                    <div class="confirm-review-card shadow-xs">
                        <div class="card-header bg-white d-flex align-items-center justify-content-between">
                            <div class="d-flex align-items-center gap-2">
                                <div class="rounded-circle text-white d-flex align-items-center justify-content-center flex-shrink-0" style="width: 28px; height: 28px; background: #10b981; font-size: 0.85rem;">
                                    <i class="bi bi-gift-fill"></i>
                                </div>
                                <span class="fw-bold text-dark" style="font-size: 0.92rem;">2. Offers & Wallet Applied</span>
                            </div>
                            <button type="button" class="btn btn-sm btn-outline-primary rounded-pill px-3 py-1 fw-bold" onclick="goToStep(2)" style="font-size: 0.76rem;">
                                <i class="bi bi-pencil me-1"></i> Change
                            </button>
                        </div>
                        <div class="card-body">
                            <div id="review-offers-summary-text" class="text-secondary text-break-word" style="font-size: 0.86rem; line-height: 1.55;"></div>
                        </div>
                    </div>

                    <!-- Review Card 3: Payment Method (Spacious) -->
                    <div class="confirm-review-card shadow-xs">
                        <div class="card-header bg-white d-flex align-items-center justify-content-between">
                            <div class="d-flex align-items-center gap-2">
                                <div class="rounded-circle text-white d-flex align-items-center justify-content-center flex-shrink-0" style="width: 28px; height: 28px; background: #6366f1; font-size: 0.85rem;">
                                    <i class="bi bi-credit-card-2-front-fill"></i>
                                </div>
                                <span class="fw-bold text-dark" style="font-size: 0.92rem;">3. Selected Payment Method</span>
                            </div>
                            <button type="button" class="btn btn-sm btn-outline-primary rounded-pill px-3 py-1 fw-bold" onclick="goToStep(3)" style="font-size: 0.76rem;">
                                <i class="bi bi-pencil me-1"></i> Change
                            </button>
                        </div>
                        <div class="card-body">
                            <div id="review-payment-mode-text" class="text-break-word">
                                <div class="d-flex align-items-center gap-2 flex-wrap mb-1.5">
                                    <span class="badge text-white px-3 py-1.5 rounded-pill fw-bold shadow-xs" style="background: #4f46e5; font-size: 0.78rem; letter-spacing: 0.4px;">
                                        <i class="bi bi-lightning-charge-fill me-1 text-warning"></i>ONLINE PAYMENT
                                    </span>
                                    <span class="fw-bold text-dark fs-6">UPI / Cards / NetBanking / PhonePe</span>
                                </div>
                                <small class="text-secondary d-block" style="font-size: 0.82rem; line-height: 1.45;">
                                    Instant payment via Razorpay with instant order confirmation.
                                </small>
                            </div>
                        </div>
                    </div>

                </div>

            </div>

            <!-- Right Column: Price Details & Items Preview (Visible ONLY on Steps 3 & 4!) -->
            <div class="col-lg-5 col-xl-4" id="checkout-summary-column">
                <div class="sticky-top" style="top: 20px; z-index: 10;">
                    
                    <!-- Order Items Summary Card (Spacious & Clean) -->
                    <div class="card border-0 shadow-xs rounded-4 overflow-hidden mb-3.5" style="background: #ffffff; border: 1.5px solid #e2e8f0 !important;">
                        <div class="card-header bg-white py-3 px-3.5 border-bottom d-flex align-items-center justify-content-between">
                            <div class="d-flex align-items-center gap-3">
                                <div class="rounded-circle d-flex align-items-center justify-content-center text-primary flex-shrink-0" style="width: 34px; height: 34px; background: rgba(79, 70, 229, 0.08); font-size: 1rem;">
                                    <i class="bi bi-bag-check-fill"></i>
                                </div>
                                <div class="d-flex align-items-center gap-2">
                                    <h6 class="fw-bold mb-0 text-dark" style="font-size: 0.95rem;">Items in Order</h6>
                                    <span class="badge bg-dark text-white rounded-pill px-2.5 py-1 fw-bold font-monospace shadow-xs" style="font-size: 0.78rem;">
                                        {{ $cart->items->sum('quantity') }}
                                    </span>
                                </div>
                            </div>
                            <a href="{{ route('cart.index') }}" class="btn btn-sm btn-outline-primary rounded-pill px-3 py-1 fw-bold d-inline-flex align-items-center gap-1.5" style="font-size: 0.74rem;">
                                <i class="bi bi-pencil"></i> Edit Bag
                            </a>
                        </div>
                        <div class="card-body p-3 p-sm-3.5">
                            <div class="checkout-items-wrapper" style="max-height: 220px; overflow-y: auto;">
                                @foreach($cart->items as $item)
                                @php
                                    $origPrice = (float) ($item->product->price ?? $item->unit_price);
                                    $currPrice = (float) $item->unit_price;
                                    $origLineTotal = $item->quantity * $origPrice;
                                    $lineTotal = $item->quantity * $currPrice;
                                    $hasDiscount = ($origPrice > $currPrice);
                                @endphp
                                <div class="p-2 mb-2 bg-white rounded-3 border shadow-2xs d-flex align-items-center justify-content-between gap-2.5" style="border-color: #f1f5f9 !important;">
                                    <div class="d-flex align-items-center gap-2.5 min-w-0 flex-grow-1">
                                        @if($item->product && $item->product->main_image)
                                            <img src="{{ asset('storage/' . $item->product->main_image) }}" alt="{{ $item->product->name }}" class="rounded-2 border flex-shrink-0" style="width: 40px; height: 40px; object-fit: contain;">
                                        @else
                                            <div class="rounded-2 bg-light border d-flex align-items-center justify-content-center flex-shrink-0 text-muted" style="width: 40px; height: 40px;">
                                                <i class="bi bi-box-seam fs-6"></i>
                                            </div>
                                        @endif
                                        <div class="min-w-0 flex-grow-1">
                                            <h6 class="mb-1 fw-bold text-dark text-truncate" style="font-size: 0.82rem;" title="{{ $item->product->name ?? 'Product' }}">
                                                {{ $item->product->name ?? 'Product' }}
                                            </h6>
                                            <span class="badge bg-light text-secondary border px-2 py-0.5 rounded-pill" style="font-size: 0.68rem;">
                                                Qty: {{ $item->quantity }}
                                            </span>
                                        </div>
                                    </div>
                                    <div class="text-end flex-shrink-0 font-monospace fw-bold text-dark" style="font-size: 0.85rem;">
                                        ₹{{ number_format($lineTotal, 2) }}
                                    </div>
                                </div>
                                @endforeach
                            </div>
                        </div>
                    </div>

                    <!-- Price Details Card (Spacious, Clear & Beautiful) -->
                    <div class="card border-0 shadow-xs rounded-4 overflow-hidden mb-3.5" style="background: #ffffff; border: 1.5px solid #e2e8f0 !important;">
                        <div class="card-header bg-white py-3 px-3.5 border-bottom">
                            <h6 class="fw-bold mb-0 text-dark d-flex align-items-center" style="font-size: 0.95rem;">
                                <i class="bi bi-receipt text-primary fs-5 me-2.5"></i>
                                <span>Price Details</span>
                            </h6>
                        </div>
                        <div class="card-body p-3.5 p-sm-4">
                            <div class="p-3.5 p-sm-4 rounded-4 border mb-3" style="border-color: #e2e8f0 !important; background: #f8fafc !important;">
                                <div class="price-row text-secondary mb-2.5">
                                    <span>Total MRP:</span>
                                    <span class="font-monospace text-dark fw-semibold">₹{{ number_format($totalMrp ?? $subtotal, 2) }}</span>
                                </div>
                                @if(isset($totalDiscount) && $totalDiscount > 0)
                                <div class="price-row text-success mb-2.5">
                                    <span>Discount on MRP:</span>
                                    <span class="font-monospace fw-bold">- ₹{{ number_format($totalDiscount, 2) }}</span>
                                </div>
                                @endif
                                <div class="price-row text-secondary mb-2.5">
                                    <span>Cart Subtotal:</span>
                                    <span class="font-monospace fw-semibold text-dark" id="summary-subtotal">₹{{ number_format($subtotal, 2) }}</span>
                                </div>
                                <div class="price-row text-success mb-2.5 {{ $discountAmount > 0 ? '' : 'd-none' }}" id="summary-discount-row">
                                    <span class="d-flex align-items-center gap-1.5 fw-semibold">
                                        <i class="bi bi-tag-fill"></i> <span id="summary-discount-label">Coupon ({{ $couponCode }})</span>
                                    </span>
                                    <span class="font-monospace fw-bold" id="summary-discount-val">- ₹{{ number_format($discountAmount, 2) }}</span>
                                </div>
                                <div class="price-row text-success mb-2.5 {{ $walletDiscount > 0 ? '' : 'd-none' }}" id="summary-wallet-row">
                                    <span class="d-flex align-items-center gap-1.5 fw-semibold">
                                        <i class="bi bi-wallet2"></i> <span>Wallet Credits</span>
                                    </span>
                                    <span class="font-monospace fw-bold" id="summary-wallet-val">- ₹{{ number_format($walletDiscount, 2) }}</span>
                                </div>
                                <div class="price-row text-secondary mb-2.5">
                                    <span>Delivery Fee:</span>
                                    <span id="summary-shipping-fee">
                                        @if(isset($shippingFee) && $shippingFee > 0)
                                            <span class="font-monospace text-dark fw-bold">+ ₹{{ number_format($shippingFee, 2) }}</span>
                                        @else
                                            <span class="badge bg-success bg-opacity-10 text-success fw-bold rounded-pill px-2.5 py-1">FREE</span>
                                        @endif
                                    </span>
                                </div>
                                <div class="price-row text-secondary mb-2" id="summary-cod-fee-row">
                                    <span>COD Handling Fee:</span>
                                    <span id="summary-cod-fee-val">
                                        <span class="badge bg-success bg-opacity-10 text-success fw-bold rounded-pill px-2.5 py-1">₹0.00 (Prepaid Offer)</span>
                                    </span>
                                </div>
                                <div class="grand-total-row-wrapper d-flex justify-content-between align-items-center">
                                    <div class="pe-2">
                                        <span class="fw-bold text-dark fs-6 d-block">Grand Total:</span>
                                        <small class="text-muted d-inline-flex align-items-center gap-1" style="font-size: 0.74rem;">
                                            <i class="bi bi-shield-check text-success"></i> Inclusive of all taxes
                                        </small>
                                    </div>
                                    <div class="text-end ps-2">
                                        <span class="fs-4 fw-bolder text-dark font-monospace text-nowrap" id="summary-grand-total">₹{{ number_format($grandTotal, 2) }}</span>
                                    </div>
                                </div>
                            </div>

                            <!-- Safe & Secure Badges (Spacious, Distinct & Clear Separation) -->
                            <div class="d-flex align-items-center justify-content-center gap-3 pt-3 mt-1 border-top flex-wrap" style="border-color: #f1f5f9 !important;">
                                <span class="d-inline-flex align-items-center gap-1.5 px-3 py-1.5 rounded-pill bg-light border text-secondary fw-semibold" style="font-size: 0.78rem; border-color: #e2e8f0 !important;">
                                    <i class="bi bi-shield-lock-fill text-success fs-6"></i> 100% Safe Checkout
                                </span>
                                <span class="d-inline-flex align-items-center gap-1.5 px-3 py-1.5 rounded-pill bg-light border text-secondary fw-semibold" style="font-size: 0.78rem; border-color: #e2e8f0 !important;">
                                    <i class="bi bi-truck text-primary fs-6"></i> Fast Express Dispatch
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- ============================================================== -->
                    <!-- STEP 3 & STEP 4 ACTION BUTTONS (Generously Spaced Below Cards) -->
                    <!-- ============================================================== -->
                    <!-- Step 3 Action Buttons (Visible on Step 3) -->
                    <div id="step-3-summary-actions" class="mt-4 pt-2 mb-3">
                        <div class="d-flex justify-content-between align-items-center gap-2">
                            <button type="button" class="btn btn-light border rounded-pill px-3 px-sm-3.5 py-2 fw-semibold text-secondary btn-sm d-inline-flex align-items-center gap-1.5" onclick="goToStep(2)" style="font-size: 0.82rem;">
                                <i class="bi bi-arrow-left"></i> <span>Back<span class="d-none d-sm-inline"> to Offers</span></span>
                            </button>
                            <button type="button" class="btn btn-primary rounded-pill px-3.5 px-sm-4 py-2 fw-semibold shadow-xs d-inline-flex align-items-center gap-1.5" onclick="proceedFromStep3()" style="font-size: 0.85rem; background: #4f46e5; border-color: #4f46e5;">
                                <span>Continue to Confirm</span>
                                <i class="bi bi-arrow-right"></i>
                            </button>
                        </div>
                    </div>

                    <!-- Step 4 Action Buttons (Visible on Step 4) -->
                    <div id="step-4-summary-actions" class="mt-4 pt-2 mb-3 d-none">
                        <!-- Mandatory Terms & Conditions Checkbox Card -->
                        <div class="confirm-terms-box" id="confirm-terms-wrapper">
                            <div class="form-check d-flex align-items-start gap-2.5 m-0">
                                <input class="form-check-input cursor-pointer flex-shrink-0 mt-0.5" 
                                       type="checkbox" 
                                       id="terms-consent-checkbox" 
                                       name="terms_consent" 
                                       value="1" 
                                       required 
                                       form="checkout-form" 
                                       style="cursor: pointer; width: 1.25rem; height: 1.25rem; accent-color: #4f46e5;">
                                <label class="form-check-label text-dark small mb-0 user-select-none" for="terms-consent-checkbox" style="font-size: 0.82rem; line-height: 1.45; cursor: pointer;">
                                    I have reviewed my order and agree to {{ \App\Models\Setting::get('store_name', 'ShopCalm') }}'s 
                                    <a href="{{ route('page.return') }}" target="_blank" class="text-primary text-decoration-underline fw-semibold">No Return Policy</a> 
                                    and 
                                    <a href="{{ route('page.terms') }}" target="_blank" class="text-primary text-decoration-underline fw-semibold">Terms &amp; Conditions</a>.
                                    <span class="text-danger fw-bold ms-0.5">*</span>
                                </label>
                            </div>
                        </div>

                        <div class="d-flex justify-content-between align-items-center gap-2">
                            <button type="button" class="btn btn-light border rounded-pill px-3 px-sm-3.5 py-2 fw-semibold text-secondary btn-sm d-inline-flex align-items-center gap-1.5" onclick="goToStep(3)" style="font-size: 0.82rem;">
                                <i class="bi bi-arrow-left"></i> <span>Back</span>
                            </button>
                            <button type="submit" form="checkout-form" onclick="handleOrderSubmission(event)" class="btn btn-primary rounded-pill px-3.5 px-sm-4 py-2 fw-bold shadow-xs d-inline-flex align-items-center gap-1.5 btn-checkout-cta" id="btn-place-order" style="font-size: 0.85rem; background: #4f46e5; border-color: #4f46e5;">
                                <span id="btn-place-order-text">Pay Online (₹{{ number_format($grandTotal, 2) }})</span>
                                <i class="bi bi-arrow-right"></i>
                            </button>
                        </div>
                    </div>

                </div>
            </div>

        </div>

        <!-- Back to Shopping Bag link at bottom -->
        <div class="text-center mt-3 pb-3">
            <a href="{{ route('cart.index') }}" class="text-muted small text-decoration-none fw-semibold">
                <i class="bi bi-bag me-1"></i> Return to Shopping Bag ({{ $cart->items->sum('quantity') }} items)
            </a>
        </div>
    </form>
</div>

<!-- Available Coupons Modal -->
<div class="modal fade" id="couponsModal" tabindex="-1" aria-labelledby="couponsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
            <div class="modal-header bg-white py-3 px-3.5 px-md-4 border-bottom">
                <div class="d-flex align-items-center gap-2">
                    <div class="rounded-circle d-flex align-items-center justify-content-center" style="width: 32px; height: 32px; background: rgba(99, 102, 241, 0.12); color: #6366f1;">
                        <i class="bi bi-tag-fill"></i>
                    </div>
                    <h5 class="modal-title fw-bolder text-dark mb-0" id="couponsModalLabel" style="font-size: 1rem;">Available Coupons & Offers</h5>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-3 p-md-3.5 bg-light">
                @if(isset($availableCoupons) && $availableCoupons->count() > 0)
                    @php
                        $eligibleCoupons = $availableCoupons->where('is_eligible', true);
                        $ineligibleCoupons = $availableCoupons->where('is_eligible', false);
                    @endphp

                    <!-- Eligible Coupons Section -->
                    @if($eligibleCoupons->count() > 0)
                        <div class="mb-3">
                            <span class="fw-bold text-dark small mb-2 d-block text-uppercase" style="font-size: 0.75rem; letter-spacing: 0.5px;">Applicable to Your Order ({{ $eligibleCoupons->count() }})</span>
                            <div class="d-flex flex-column gap-2.5">
                                @foreach($eligibleCoupons as $c)
                                    @php $discType = is_object($c->discount_type) ? $c->discount_type->value : (string)$c->discount_type; @endphp
                                    <div class="card border-0 shadow-xs rounded-3 p-3 bg-white" style="border: 1.5px dashed #3b82f6 !important;">
                                        <div class="d-flex justify-content-between align-items-start mb-1">
                                            <div>
                                                <span class="font-monospace fw-bold px-2.5 py-1 rounded" style="background: #eff6ff; color: #1d4ed8; font-size: 0.85rem;">{{ $c->code }}</span>
                                                <span class="badge bg-success bg-opacity-10 text-success ms-2 fw-bold" style="font-size: 0.68rem;">Eligible</span>
                                            </div>
                                            <button type="button" class="btn btn-sm btn-primary rounded-pill px-3 py-1 fw-bold" onclick="applyCouponFromModal('{{ $c->code }}')" style="font-size: 0.76rem; background: #4f46e5; border: none;">APPLY</button>
                                        </div>
                                        <div class="fw-bold text-success mt-1" style="font-size: 0.9rem;">
                                            @if($discType === 'PERCENTAGE') Save {{ round($c->discount_value) }}% @else Save ₹{{ number_format($c->discount_value) }} FLAT @endif
                                        </div>
                                        @if($c->description)<p class="text-secondary small mb-0 mt-0.5" style="font-size: 0.78rem;">{{ $c->description }}</p>@endif
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    <!-- Ineligible Coupons Section -->
                    @if($ineligibleCoupons->count() > 0)
                        <div>
                            <span class="fw-bold text-muted small mb-2 d-block text-uppercase" style="font-size: 0.75rem; letter-spacing: 0.5px;">Other Store Coupons ({{ $ineligibleCoupons->count() }})</span>
                            <div class="d-flex flex-column gap-2.5">
                                @foreach($ineligibleCoupons as $c)
                                    @php $discType = is_object($c->discount_type) ? $c->discount_type->value : (string)$c->discount_type; @endphp
                                    <div class="card border-0 shadow-xs rounded-3 p-3 bg-white" style="border: 1px dashed #cbd5e1 !important; opacity: 0.85;">
                                        <div class="d-flex justify-content-between align-items-start mb-1">
                                            <span class="font-monospace fw-bold px-2.5 py-1 rounded text-secondary" style="background: #f1f5f9; font-size: 0.85rem;">{{ $c->code }}</span>
                                            <span class="badge bg-secondary bg-opacity-10 text-secondary fw-bold" style="font-size: 0.68rem;">Not Applicable</span>
                                        </div>
                                        <div class="fw-semibold text-secondary mt-1" style="font-size: 0.85rem;">
                                            @if($discType === 'PERCENTAGE') {{ round($c->discount_value) }}% OFF @else ₹{{ number_format($c->discount_value) }} FLAT OFF @endif
                                        </div>
                                        @if($c->ineligibility_reason)
                                            <div class="text-danger small mt-1" style="font-size: 0.76rem;">
                                                <i class="bi bi-info-circle me-1"></i> {{ $c->ineligibility_reason }}
                                            </div>
                                        @endif
                                        @if($c->description)<p class="text-muted small mb-0 mt-0.5" style="font-size: 0.76rem;">{{ $c->description }}</p>@endif
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif
                @else
                    <div class="text-center py-4 text-muted">
                        <i class="bi bi-ticket-perforated fs-1 d-block mb-2 text-secondary"></i>
                        <p class="mb-0 small fw-semibold">No coupons available at the moment.</p>
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
            <div class="modal-header border-bottom py-3 px-3.5 px-md-4 d-flex align-items-center justify-content-between">
                <div class="d-flex align-items-center gap-2.5">
                    <div class="rounded-circle d-flex align-items-center justify-content-center text-primary flex-shrink-0" style="width: 38px; height: 38px; background: rgba(79, 70, 229, 0.1);">
                        <i class="bi bi-geo-alt-fill fs-5"></i>
                    </div>
                    <div>
                        <h5 class="modal-title fw-bold mb-0 text-dark" id="addressModalLabel" style="font-size: 1.05rem;">Select Saved Address</h5>
                        <small class="text-secondary" style="font-size: 0.78rem;">Choose an address for your order delivery</small>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-3.5 p-md-4">
                <div class="row g-3" id="modal-address-list">
                    @foreach($addresses as $addr)
                    @php
                        $isCurrent = (isset($activeAddr) && $activeAddr->id === $addr->id);
                    @endphp
                    <div class="col-12">
                        <div class="card border rounded-4 p-3 p-sm-3.5 address-modal-option cursor-pointer position-relative shadow-xs {{ $isCurrent ? 'is-selected' : '' }}" onclick="selectModalAddress({{ json_encode($addr) }})">
                            <div class="d-flex align-items-start justify-content-between gap-2.5">
                                <div class="min-w-0 flex-grow-1">
                                    <div class="d-flex align-items-center gap-2 mb-1 flex-wrap">
                                        <strong class="text-dark fs-6">{{ $addr->name }}</strong>
                                        @if($isCurrent)
                                            <span class="badge bg-success text-white rounded-pill px-2.5 py-0.5 fw-bold" style="font-size: 0.68rem;">✓ Selected</span>
                                        @elseif($addr->is_default)
                                            <span class="badge bg-light text-secondary border rounded-pill px-2 py-0.5" style="font-size: 0.65rem;">Default</span>
                                        @endif
                                    </div>
                                    <div class="text-secondary mb-1.5" style="font-size: 0.86rem; line-height: 1.45;">
                                        {{ $addr->address }}, {{ $addr->city }}, {{ $addr->state }} <span class="text-nowrap font-monospace text-dark fw-bold">- {{ $addr->zip }}</span>
                                    </div>
                                    <div class="small text-muted text-nowrap" style="font-size: 0.82rem; white-space: nowrap !important;">
                                        <i class="bi bi-telephone-fill text-muted me-1 small"></i> Mobile: <strong class="text-dark font-monospace">{{ $addr->phone }}</strong>
                                    </div>
                                </div>
                                <div class="flex-shrink-0 align-self-center ps-1">
                                    @if($isCurrent)
                                        <span class="btn btn-sm btn-success rounded-pill px-3 py-1.5 fw-semibold shadow-xs text-nowrap" style="font-size: 0.78rem; pointer-events: none;">
                                            <i class="bi bi-check2"></i> Selected
                                        </span>
                                    @else
                                        <button type="button" class="btn btn-sm btn-outline-primary rounded-pill px-3 py-1.5 fw-semibold text-nowrap" style="font-size: 0.78rem;">
                                            Select
                                        </button>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
            <div class="modal-footer border-top bg-light py-3 px-3.5 px-md-4 d-flex justify-content-between align-items-center">
                <button type="button" class="btn btn-sm btn-primary rounded-pill px-4 py-2 fw-semibold d-inline-flex align-items-center gap-1.5 shadow-xs" onclick="openNewAddressFromModal()" style="background: #4f46e5; border: none; font-size: 0.82rem;">
                    <i class="bi bi-plus-lg"></i> Add New Address
                </button>
                <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill px-4 py-2 fw-semibold" data-bs-dismiss="modal" style="font-size: 0.82rem;">Close</button>
            </div>
        </div>
    </div>
</div>

<script src="https://checkout.razorpay.com/v1/checkout.js"></script>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
let userAddresses = {!! json_encode($addresses) !!};
let currentSelectedAddress = userAddresses.length > 0 ? userAddresses[0] : null;
window.isCurrentAddressServiceable = true;
window.isCurrentAddressCodAvailable = true;
window.activeShippingCharge = parseFloat("{{ $shippingFee ?? 0 }}");
window.activeCodFee = parseFloat("{{ $resolvedCodFee ?? 40 }}");
window.currentCheckoutStep = 1;

@if(isset($pendingOrder) && $pendingOrder)
window.activePendingOrder = {
    id: {{ $pendingOrder->id }},
    order_number: @json($pendingOrder->order_number),
    total: @json(number_format((float) $pendingOrder->total_amount, 2)),
    proceed_url: @json(route('checkout.payment_failed', $pendingOrder)),
    order_url: @json(route('account.orders.show', $pendingOrder)),
    cancel_url: @json(route('account.orders.cancel', $pendingOrder))
};
@else
window.activePendingOrder = null;
@endif

function showPendingOrderBlockModal() {
    const p = window.activePendingOrder;
    if (!p) return;

    Swal.fire({
        icon: 'warning',
        title: `Pending Order #${p.order_number}`,
        html: `
            <div class="text-start small" style="line-height: 1.55;">
                <p class="mb-2 text-secondary">
                    You currently have an unfinished pending order <strong class="text-dark">#${p.order_number}</strong> (Amount: <strong class="text-dark">₹${p.total}</strong>).
                </p>
                <div class="p-2.5 rounded-3 mb-2" style="background: #fffbeb; border: 1px solid #fde68a; color: #92400e;">
                    <i class="bi bi-info-circle-fill me-1"></i>
                    <strong>Store Policy:</strong> Please proceed with your pending order or cancel it before placing a new one.
                </div>
            </div>
        `,
        showDenyButton: true,
        showCancelButton: true,
        confirmButtonColor: '#4f46e5',
        denyButtonColor: '#ef4444',
        cancelButtonColor: '#64748b',
        confirmButtonText: `Proceed with #${p.order_number}`,
        denyButtonText: `Cancel Order #${p.order_number}`,
        cancelButtonText: 'Close',
        willClose: () => { if (typeof resetPlaceOrderButton === 'function') resetPlaceOrderButton(); },
        didClose: () => { if (typeof resetPlaceOrderButton === 'function') resetPlaceOrderButton(); }
    }).then((result) => {
        if (typeof resetPlaceOrderButton === 'function') resetPlaceOrderButton();
        if (result.isConfirmed) {
            window.location.href = p.proceed_url;
        } else if (result.isDenied) {
            cancelPendingOrderOnCheckout(p.id, p.order_number);
        }
    });
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
                refund_method: 'wallet'
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

    // Toggle 1-2 Container vs 3-4 Container (Price Details only appears on Step 3 & 4!)
    const step12Col = document.getElementById('checkout-step-1-2-col');
    const step34Col = document.getElementById('checkout-step-3-4-col');
    if (step === 1 || step === 2) {
        if (step12Col) step12Col.classList.remove('d-none');
        if (step34Col) step34Col.classList.add('d-none');
    } else {
        if (step12Col) step12Col.classList.add('d-none');
        if (step34Col) step34Col.classList.remove('d-none');
    }

    // Toggle Step Views
    document.getElementById('checkout-step-1-view')?.classList.toggle('d-none', step !== 1);
    document.getElementById('checkout-step-2-view')?.classList.toggle('d-none', step !== 2);
    document.getElementById('checkout-step-3-view')?.classList.toggle('d-none', step !== 3);
    document.getElementById('checkout-step-4-view')?.classList.toggle('d-none', step !== 4);

    // Toggle Summary Action Buttons (placed after Price Details)
    document.getElementById('step-3-summary-actions')?.classList.toggle('d-none', step !== 3);
    document.getElementById('step-4-summary-actions')?.classList.toggle('d-none', step !== 4);

    // Update Top Stepper Progress Bar
    const progressLine = document.getElementById('stepper-progress-line');
    const widths = { 1: '0%', 2: '27%', 3: '54%', 4: '80%' };
    if (progressLine) progressLine.style.width = widths[step] || '0%';

    for (let s = 1; s <= 4; s++) {
        const badge = document.getElementById(`stepper-badge-${s}`);
        const text = document.getElementById(`stepper-text-${s}`);
        if (!badge || !text) continue;

        if (s < step) {
            badge.className = 'checkout-step-badge text-white shadow-xs';
            badge.style.background = '#10b981';
            badge.style.boxShadow = 'none';
            badge.innerHTML = '<i class="bi bi-check-lg"></i>';
            text.className = 'fw-semibold text-success mt-1 text-center';
        } else if (s === step) {
            badge.className = 'checkout-step-badge text-white shadow-xs';
            badge.style.background = '#4f46e5';
            badge.style.boxShadow = '0 0 0 3px rgba(79, 70, 229, 0.2) !important';
            badge.innerHTML = s;
            text.className = 'fw-bold text-primary mt-1 text-center';
        } else {
            badge.className = 'checkout-step-badge bg-light text-muted border';
            badge.style.background = '';
            badge.style.boxShadow = '';
            badge.innerHTML = s;
            text.className = 'fw-semibold text-muted mt-1 text-center';
        }
    }

    // Populate Dynamic Summaries
    if (currentSelectedAddress) {
        const addrFmt = `${currentSelectedAddress.name} — ${currentSelectedAddress.address}, ${currentSelectedAddress.city} (${currentSelectedAddress.zip})`;
        const st2Summary = document.getElementById('step-2-address-summary');
        if (st2Summary) st2Summary.textContent = addrFmt;

        const st3Summary = document.getElementById('step-3-address-summary');
        if (st3Summary) st3Summary.textContent = addrFmt;

        const revName = document.getElementById('review-address-name');
        if (revName) revName.textContent = currentSelectedAddress.name;

        const revText = document.getElementById('review-address-text');
        if (revText) revText.textContent = `${currentSelectedAddress.address}, ${currentSelectedAddress.city}, ${currentSelectedAddress.state} - ${currentSelectedAddress.zip}`;

        const revPhone = document.getElementById('review-address-phone');
        if (revPhone) revPhone.innerHTML = `<i class="bi bi-telephone-fill text-muted me-1.5"></i> Mobile: <strong class="text-dark">${currentSelectedAddress.phone}</strong>`;
    }

    // Update Step 4 Reviews
    updateReviewSummaries();

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

function resetPlaceOrderButton() {
    const placeOrderBtn = document.getElementById('btn-place-order');
    if (!placeOrderBtn) return;
    placeOrderBtn.disabled = false;
    
    const isOnline = document.getElementById('pay_online')?.checked;
    const grandTotalFmt = document.getElementById('summary-grand-total')?.textContent?.trim() || '₹{{ number_format($grandTotal, 2) }}';
    const grandTotalRaw = document.getElementById('summary-grand-total')?.textContent.replace(/[^0-9.]/g, '') || '0';
    const grandTotalVal = parseFloat(grandTotalRaw);
    
    let btnText = `Pay Online (${grandTotalFmt})`;
    if (grandTotalVal <= 0) {
        btnText = 'Confirm Order (₹0.00 - Paid)';
    } else if (!isOnline) {
        btnText = `Place COD Order (${grandTotalFmt})`;
    }

    placeOrderBtn.innerHTML = `<span id="btn-place-order-text">${btnText}</span> <i class="bi bi-arrow-right ms-1"></i>`;
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

    // Step 4 Final CTA button label & state
    resetPlaceOrderButton();
}

document.addEventListener('DOMContentLoaded', function() {
    if (currentSelectedAddress) {
        applySelectedAddress(currentSelectedAddress);
    }

    const checkoutForm = document.getElementById('checkout-form');

    document.querySelectorAll('input[name="payment_method"]').forEach(radio => {
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

    window.handleOrderSubmission = function(e) {
        if (e) e.preventDefault();

        if (window.activePendingOrder) {
            showPendingOrderBlockModal();
            return;
        }

        if (window.currentCheckoutStep === 1) {
            proceedFromStep1();
            return;
        }
        if (window.currentCheckoutStep === 2) {
            proceedFromStep2();
            return;
        }
        if (window.currentCheckoutStep === 3) {
            proceedFromStep3();
            return;
        }

        // Step 4: Final Place Order Validation
        const consentCb = document.getElementById('terms-consent-checkbox');
        if (consentCb && !consentCb.checked) {
            const termsBox = document.getElementById('confirm-terms-wrapper');
            if (termsBox) {
                termsBox.style.borderColor = '#ef4444';
                termsBox.style.background = '#fef2f2';
            }
            Swal.fire({
                icon: 'warning',
                title: 'Agreement Required',
                text: 'Please check the agreement box for the No Return Policy & Terms & Conditions before placing your order.',
                confirmButtonColor: '#4f46e5'
            });
            consentCb.scrollIntoView({ behavior: 'smooth', block: 'center' });
            return;
        }

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

        const placeOrderBtn = document.getElementById('btn-place-order');
        if (placeOrderBtn) {
            placeOrderBtn.disabled = true;
            placeOrderBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span> Processing...';
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
                        if (placeOrderBtn) placeOrderBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span> Verifying Payment...';
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
                            if (data.failed_url) {
                                Swal.fire({
                                    icon: 'info',
                                    title: 'Payment Window Closed',
                                    text: 'You closed the payment window. Your ordered items remain safely reserved in your pending order.',
                                    confirmButtonColor: '#4f46e5',
                                    confirmButtonText: 'OK',
                                    allowOutsideClick: false,
                                    willClose: () => resetPlaceOrderButton()
                                }).then(() => {
                                    window.location.href = data.failed_url;
                                });
                            }
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
                        Swal.fire({
                            icon: 'error',
                            title: 'Payment Failed',
                            text: errorMsg,
                            confirmButtonColor: '#4f46e5',
                            confirmButtonText: 'OK',
                            allowOutsideClick: false,
                            willClose: () => resetPlaceOrderButton(),
                            didClose: () => resetPlaceOrderButton()
                        }).then(() => {
                            if (data.failed_url) {
                                window.location.href = data.failed_url;
                            }
                        });
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
        if (cardSavingsEl) {
            let totalSavedNow = parseFloat("{{ $totalDiscount ?? 0 }}") + discountAmt + walletDiscount + parseFloat("{{ $offerDiscount ?? 0 }}");
            cardSavingsEl.textContent = `₹${totalSavedNow.toFixed(2)}`;
        }

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

        if (isFullyCoveredByWallet || calculatedGrandTotal <= 0) {
            if (fullWalletNotice) fullWalletNotice.classList.remove('d-none');
            if (step3WalletNotice) step3WalletNotice.classList.remove('d-none');
            if (paymentContainer) {
                paymentContainer.style.opacity = '0.45';
                paymentContainer.style.pointerEvents = 'none';
                paymentContainer.style.filter = 'grayscale(1)';
            }
        } else {
            if (fullWalletNotice) fullWalletNotice.classList.add('d-none');
            if (step3WalletNotice) step3WalletNotice.classList.add('d-none');
            if (paymentContainer) {
                paymentContainer.style.opacity = '1';
                paymentContainer.style.pointerEvents = 'auto';
                paymentContainer.style.filter = 'none';
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
    document.getElementById('hidden_shipping_address').value = addr.address || '';
    document.getElementById('hidden_shipping_city').value = addr.city || '';
    document.getElementById('hidden_shipping_state').value = addr.state || '';
    document.getElementById('hidden_shipping_zip').value = addr.zip || '';

    const displayName = document.getElementById('display-name');
    const displayStreetCity = document.getElementById('display-street-city');
    const displayPhone = document.getElementById('display-phone');
    const activeDetails = document.getElementById('active-address-details');
    const noAddrMsg = document.getElementById('no-address-msg');

    if (displayName) displayName.textContent = addr.name;
    if (displayStreetCity) displayStreetCity.innerHTML = `${escapeAddressHtml(addr.address)}, ${escapeAddressHtml(addr.city)}, ${escapeAddressHtml(addr.state)} <span class="text-nowrap font-monospace fw-bold text-dark">- ${escapeAddressHtml(addr.zip)}</span>`;
    if (displayPhone) displayPhone.innerHTML = `<i class="bi bi-telephone-fill text-muted me-1 small"></i> Mobile: <strong class="text-dark font-monospace">${escapeAddressHtml(addr.phone)}</strong>`;
    
    if (activeDetails) activeDetails.style.display = 'block';
    if (noAddrMsg) noAddrMsg.style.display = 'none';

    checkPincodeServiceability(addr.zip);
}

function selectModalAddress(addr) {
    applySelectedAddress(addr);
    const modalEl = document.getElementById('addressSelectorModal');
    if (modalEl) {
        const modalInstance = bootstrap.Modal.getInstance(modalEl);
        if (modalInstance) modalInstance.hide();
    }
    const successBanner = document.getElementById('address-success-banner');
    if (successBanner) {
        successBanner.style.display = 'block';
        setTimeout(() => { successBanner.style.display = 'none'; }, 3000);
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
    const card = document.getElementById('new-address-card');
    if (card) {
        card.style.display = 'block';
        setTimeout(() => {
            card.scrollIntoView({ behavior: 'smooth', block: 'center' });
            const nameInput = document.getElementById('new_name');
            if (nameInput) nameInput.focus();
        }, 200);
    }
}

function openNewAddressForm() {
    const card = document.getElementById('new-address-card');
    if (card) {
        if (card.style.display === 'block') {
            card.style.display = 'none';
        } else {
            card.style.display = 'block';
            card.scrollIntoView({ behavior: 'smooth', block: 'center' });
            const nameInput = document.getElementById('new_name');
            if (nameInput) nameInput.focus();
        }
    }
}

function closeNewAddressForm() {
    const card = document.getElementById('new-address-card');
    if (card) {
        card.style.display = 'none';
    }
}

function handleNewPincodeLookup(val) {
    const pin = (val || '').replace(/\D/g, '').slice(0, 6);
    const zipInput = document.getElementById('new_zip');
    if (zipInput && zipInput.value !== pin) {
        zipInput.value = pin;
    }
    const hintEl = document.getElementById('new_zip_lookup_hint');
    if (pin.length < 6) {
        if (hintEl) hintEl.textContent = '';
        return;
    }

    if (hintEl) {
        hintEl.className = 'text-muted d-block mt-0.5';
        hintEl.innerHTML = '<span class="spinner-border spinner-border-sm me-1" style="width:0.6rem;height:0.6rem;"></span> Looking up PIN...';
    }

    fetch('{{ route("delivery.check") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json'
        },
        body: JSON.stringify({ pincode: pin })
    })
    .then(res => res.json())
    .then(data => {
        if (data.city && !data.city.startsWith('PIN ')) {
            const cityInput = document.getElementById('new_city');
            if (cityInput && !cityInput.value.trim()) cityInput.value = data.city;
        }
        if (data.state && data.state !== 'India') {
            const stateInput = document.getElementById('new_state');
            if (stateInput && !stateInput.value.trim()) stateInput.value = data.state;
        }
        if (hintEl) {
            if (data.is_serviceable) {
                hintEl.className = 'text-success fw-semibold d-block mt-0.5';
                hintEl.innerHTML = `<i class="bi bi-check-circle-fill me-1"></i>Serviceable (${data.city || pin})`;
            } else {
                hintEl.className = 'text-danger fw-semibold d-block mt-0.5';
                hintEl.innerHTML = `<i class="bi bi-x-circle-fill me-1"></i>Delivery unavailable for ${pin}`;
            }
        }
    })
    .catch(() => {
        if (hintEl) hintEl.textContent = '';
    });
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
            <div class="d-flex align-items-start justify-content-between gap-2.5">
                <div class="min-w-0 flex-grow-1">
                    <div class="d-flex align-items-center gap-2 mb-1 flex-wrap">
                        <strong class="text-dark fs-6">${escapeAddressHtml(safeName)}</strong>
                        ${isSelected ? '<span class="badge bg-success text-white rounded-pill px-2.5 py-0.5 fw-bold" style="font-size: 0.68rem;">✓ Selected</span>' : (addr.is_default ? '<span class="badge bg-light text-secondary border rounded-pill px-2 py-0.5" style="font-size: 0.65rem;">Default</span>' : '')}
                    </div>
                    <div class="text-secondary mb-1.5" style="font-size: 0.86rem; line-height: 1.45;">
                        ${escapeAddressHtml(addr.address || '')}, ${escapeAddressHtml(addr.city || '')}, ${escapeAddressHtml(addr.state || '')} <span class="text-nowrap font-monospace text-dark fw-bold">- ${escapeAddressHtml(safeZip)}</span>
                    </div>
                    <div class="small text-muted text-nowrap" style="font-size: 0.82rem; white-space: nowrap !important;">
                        <i class="bi bi-telephone-fill text-muted me-1 small"></i> Mobile: <strong class="text-dark font-monospace">${escapeAddressHtml(safePhone)}</strong>
                    </div>
                </div>
                <div class="flex-shrink-0 align-self-center ps-1">
                    ${isSelected ? `
                        <span class="btn btn-sm btn-success rounded-pill px-3 py-1.5 fw-semibold shadow-xs text-nowrap" style="font-size: 0.78rem; pointer-events: none;">
                            <i class="bi bi-check2"></i> Selected
                        </span>
                    ` : `
                        <button type="button" class="btn btn-sm btn-outline-primary rounded-pill px-3 py-1.5 fw-semibold text-nowrap" style="font-size: 0.78rem;">
                            Select
                        </button>
                    `}
                </div>
            </div>
        `;
        col.appendChild(card);
        listEl.appendChild(col);
    });

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

    const nameVal = (document.getElementById('new_name')?.value || '').trim();
    const phoneVal = (document.getElementById('new_phone')?.value || '').trim();
    const zipVal = (document.getElementById('new_zip')?.value || '').trim();
    const addressVal = (document.getElementById('new_address')?.value || '').trim();
    const cityVal = (document.getElementById('new_city')?.value || '').trim();
    const stateVal = (document.getElementById('new_state')?.value || '').trim();

    if (!nameVal || !phoneVal || !zipVal || !addressVal || !cityVal || !stateVal) {
        Swal.fire({
            icon: 'warning',
            title: 'Incomplete Address',
            text: 'Please fill in Full Name, Mobile Number, 6-digit PIN Code, Street Address, City, and State.',
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

    const btn = document.getElementById('btn-save-address');
    const origText = btn ? btn.innerHTML : '<i class="bi bi-bookmark-check me-1"></i> Save Address';
    if (btn) {
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Saving...';
    }

    const payload = {
        name: nameVal,
        phone: phoneVal,
        zip: zipVal,
        address: addressVal,
        city: cityVal,
        state: stateVal,
        country: 'India',
        is_default: true
    };

    fetch('{{ route("checkout.save-address") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json'
        },
        body: JSON.stringify(payload)
    })
    .then(async res => {
        const data = await res.json();
        if (!res.ok) {
            throw new Error(data.message || 'Could not save address.');
        }
        return data;
    })
    .then(data => {
        if (btn) {
            btn.disabled = false;
            btn.innerHTML = origText;
        }
        if (data.success || data.address) {
            const savedAddr = data.address || payload;
            if (Array.isArray(data.all_addresses)) {
                userAddresses = data.all_addresses;
            } else {
                userAddresses.unshift(savedAddr);
            }
            renderModalAddressList();
            applySelectedAddress(savedAddr);
            closeNewAddressForm();

            document.getElementById('new_address').value = '';
            document.getElementById('new_zip').value = '';
            document.getElementById('new_city').value = '';
            document.getElementById('new_state').value = '';
            const hintEl = document.getElementById('new_zip_lookup_hint');
            if (hintEl) hintEl.textContent = '';

            const successBanner = document.getElementById('address-success-banner');
            if (successBanner) {
                successBanner.style.display = 'block';
                setTimeout(() => { successBanner.style.display = 'none'; }, 3000);
            }

            if (!window.activePendingOrder) {
                goToStep(2);
            }
        } else {
            Swal.fire('Error', data.message || 'Could not save address.', 'error');
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

    if (!pincode) return;

    if (badgeEl) {
        badgeEl.innerHTML = `<span class="badge bg-light text-muted border font-monospace"><span class="spinner-border spinner-border-sm me-1" style="width:0.6rem;height:0.6rem;"></span> Checking PIN ${pincode}...</span>`;
    }

    fetch('{{ route("delivery.check") }}', {
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

            if (badgeEl) {
                badgeEl.innerHTML = `
                    <div class="d-flex align-items-center gap-2 flex-wrap text-break-word">
                        <span class="badge bg-success bg-opacity-10 text-success fw-bold rounded-pill px-2.5 py-0.5" style="font-size: 0.72rem;">
                            <i class="bi bi-truck me-1"></i> Delivery Available (${data.delivery_charge > 0 ? '₹' + data.delivery_charge : 'FREE'})
                        </span>
                        ${data.estimated_delivery ? `<span class="small text-muted" style="font-size: 0.74rem;"><i class="bi bi-clock me-1"></i> Est: ${data.estimated_delivery}</span>` : ''}
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
        }
    })
    .catch(err => {
        console.error("Serviceability check error:", err);
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
</script>
@endpush