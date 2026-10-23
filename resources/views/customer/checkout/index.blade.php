@extends('layouts.customer')

@section('title', 'Secure Checkout — ' . \App\Models\Setting::get('store_name', 'ShopCalm'))

@push('styles')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" crossorigin="" />
<script src="https://cdn.jsdelivr.net/npm/canvas-confetti@1.9.2/dist/confetti.browser.min.js"></script>
<style>
    /* Delivery Map Modal Styles */
    #delivery-map-container {
        min-height: 290px !important;
        height: 290px !important;
        width: 100% !important;
        background-color: #e2e8f0;
    }
    .leaflet-container {
        font-family: inherit;
    }

    /* Perfectly Centered Location Picker Modal (Desktop & Global) */
    #locationPickerModal .modal-dialog {
        max-width: 760px !important;
        margin: 1.75rem auto !important;
    }
    #locationPickerModal .modal-content {
        border-radius: 1.25rem !important;
        box-shadow: 0 20px 45px rgba(15, 23, 42, 0.18) !important;
        border: none !important;
        overflow: hidden !important;
    }

    @media (max-width: 767.98px) {
        #locationPickerModal {
            padding-left: 0 !important;
            padding-right: 0 !important;
        }
        #locationPickerModal .modal-dialog {
            margin: 0.75rem auto !important;
            width: calc(100% - 1rem) !important;
            max-width: 540px !important;
            max-height: calc(100dvh - 1.5rem) !important;
            min-height: calc(100% - 1.5rem) !important;
            display: flex !important;
            align-items: center !important;
            justify-content: center !important;
        }
        #locationPickerModal .modal-content {
            border-radius: 1.15rem !important;
            max-height: calc(100dvh - 1.5rem) !important;
            display: flex !important;
            flex-direction: column !important;
            overflow: hidden !important;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2) !important;
            border: none !important;
            width: 100% !important;
        }
        #locationPickerModal .modal-header {
            padding: 0.75rem 1rem !important;
            flex-shrink: 0 !important;
        }
        #locationPickerModal .modal-body {
            flex: 1 1 auto !important;
            overflow-y: auto !important;
            overflow-x: hidden !important;
            -webkit-overflow-scrolling: touch !important;
            padding: 0 !important;
        }
        #delivery-map-container {
            min-height: 220px !important;
            height: 220px !important;
            width: 100% !important;
        }
        #locationPickerModal .modal-footer {
            flex-shrink: 0 !important;
            position: sticky !important;
            bottom: 0 !important;
            background: #ffffff !important;
            box-shadow: 0 -4px 16px rgba(0,0,0,0.06) !important;
            z-index: 1050 !important;
            padding: 0.65rem 0.85rem calc(0.65rem + env(safe-area-inset-bottom, 0px)) 0.85rem !important;
            border-top: 1px solid #e2e8f0 !important;
        }
        #locationPickerModal .modal-footer .btn {
            font-size: 0.82rem !important;
        }

        /* Step 1 Address Action Buttons: Touch-optimized equal pills */
        .checkout-address-actions {
            display: flex !important;
            gap: 0.5rem !important;
        }
        .checkout-address-actions .btn {
            flex: 1 1 0 !important;
            justify-content: center !important;
            padding: 0.65rem 0.5rem !important;
            font-size: 0.78rem !important;
            white-space: nowrap !important;
        }

        /* Prevent auto-zoom on iOS Safari when tapping inputs */
        #locationPickerModal input.form-control,
        #checkoutAddressModal input.form-control,
        #checkoutAddressModal textarea.form-control {
            font-size: 16px !important;
        }
    }
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
        border-radius: 1rem;
        padding: 1.15rem 1.25rem;
        margin-bottom: 0.9rem !important;
        cursor: pointer;
        transition: all 0.25s cubic-bezier(0.16, 1, 0.3, 1);
        background: #ffffff;
        position: relative;
    }
    @media (min-width: 768px) {
        .payment-tile {
            padding: 1.15rem 1.35rem;
            margin-bottom: 0.95rem !important;
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
        font-size: 0.84rem;
        margin-bottom: 0.55rem;
    }
    .grand-total-row-wrapper {
        border-top: 1px dashed #cbd5e1 !important;
        margin-top: 0.65rem;
        padding-top: 0.75rem !important;
        padding-bottom: 0.15rem !important;
    }
    @media (max-width: 767.98px) {
        .grand-total-row-wrapper {
            margin-top: 0.55rem;
            padding-top: 0.65rem !important;
            padding-bottom: 0.1rem !important;
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
            padding: 0.95rem 1rem !important;
            border-radius: 1rem !important;
            margin-bottom: 0.75rem !important;
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
        #addressSelectorModal .modal-dialog,
        #checkoutAddressModal .modal-dialog {
            margin: 0.75rem auto !important;
            width: calc(100% - 1.25rem) !important;
            max-width: 560px !important;
        }
        #addressSelectorModal .modal-content,
        #checkoutAddressModal .modal-content {
            border-radius: 1.25rem !important;
        }
        #addressSelectorModal .modal-body,
        #checkoutAddressModal .modal-body {
            padding: 1rem 0.9rem !important;
            max-height: 72vh;
            overflow-y: auto;
        }
        #checkoutAddressModal .modal-footer {
            position: sticky;
            bottom: 0;
            background: #ffffff !important;
            z-index: 1055;
            box-shadow: 0 -4px 14px rgba(0, 0, 0, 0.05);
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

        /* Mobile Checkout Items in Order Overflow Protection */
        #checkout-items-summary-card,
        #checkout-step-4-view .card {
            max-width: 100% !important;
            overflow: hidden !important;
        }
        .checkout-items-wrapper {
            max-height: 230px !important;
            overflow-x: hidden !important;
            overflow-y: auto !important;
            -webkit-overflow-scrolling: touch !important;
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
        <input type="hidden" name="shipping_latitude" id="hidden_shipping_latitude">
        <input type="hidden" name="shipping_longitude" id="hidden_shipping_longitude">
        <input type="hidden" name="shipping_landmark" id="hidden_shipping_landmark">

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
                            <div id="active-address-display" class="checkout-address-box border address-card-selected">
                                <div id="no-address-msg" style="display: {{ $addresses->count() > 0 ? 'none' : 'block' }};" class="text-center py-4 text-muted">
                                    <i class="bi bi-building-add fs-2 text-primary d-block mb-2"></i>
                                    <span class="small fw-semibold text-dark d-block mb-2">No saved address found.</span>
                                    <button type="button" class="btn btn-sm btn-primary rounded-pill px-3.5 py-1.5 fw-semibold" onclick="openCheckoutAddressModal()">
                                        <i class="bi bi-plus-lg me-1"></i> Add Delivery Address
                                    </button>
                                </div>

                                @php $activeAddr = $addresses->count() > 0 ? ($addresses->firstWhere('is_default', true) ?? $addresses->first()) : null; @endphp
                                <div id="active-address-details" style="display: {{ $addresses->count() > 0 ? 'block' : 'none' }};">
                                    <div class="d-flex justify-content-between align-items-center mb-2 flex-wrap gap-2 pb-2 border-bottom">
                                        <div class="d-flex align-items-center gap-2 min-w-0">
                                            <h6 class="fw-bold mb-0 text-dark text-truncate" style="font-size: 0.96rem;" id="display-name">{{ $activeAddr ? $activeAddr->name : '' }}</h6>
                                            <span class="badge bg-success text-white rounded-pill px-2.5 py-1 fw-bold flex-shrink-0" style="font-size: 0.68rem;">✓ Selected</span>
                                        </div>
                                        <div class="d-flex align-items-center gap-1.5 flex-wrap">
                                            <button type="button" id="btn-switch-address" class="btn btn-sm btn-outline-primary rounded-pill px-2.5 py-1 fw-semibold d-inline-flex align-items-center" onclick="showAddressSelectorModal()" style="font-size: 0.76rem; display: {{ $addresses->count() > 1 ? 'inline-flex' : 'none' }};">
                                                <i class="bi bi-arrow-repeat me-1"></i> Switch
                                            </button>
                                            <button type="button" id="btn-edit-active-address" class="btn btn-sm btn-outline-secondary rounded-pill px-2.5 py-1 fw-semibold d-inline-flex align-items-center" onclick="editActiveCheckoutAddress()" style="font-size: 0.76rem;" title="Edit Selected Address">
                                                <i class="bi bi-pencil me-1"></i> Edit
                                            </button>
                                            <button type="button" id="btn-delete-active-address" class="btn btn-sm btn-outline-danger rounded-pill px-2.5 py-1 fw-semibold d-inline-flex align-items-center" onclick="deleteActiveCheckoutAddress(this)" style="font-size: 0.76rem;" title="Delete Selected Address">
                                                <i class="bi bi-trash me-1"></i> Delete
                                            </button>
                                        </div>
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

                            <!-- Address Action Buttons: Use Current Location / Add Manually -->
                            <div class="d-flex align-items-center gap-2 mb-3.5 flex-wrap checkout-address-actions">
                                <button type="button" class="btn btn-sm btn-primary rounded-pill px-3.5 py-2 fw-bold d-inline-flex align-items-center gap-1.5 shadow-xs" onclick="openLocationPickerModal(true)" style="font-size: 0.82rem; background: #4f46e5; border-color: #4f46e5;">
                                    <i class="bi bi-crosshair fs-6"></i>
                                    <span>Use Current Location</span>
                                </button>
                                <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill px-3.5 py-2 fw-semibold d-inline-flex align-items-center gap-1.5" onclick="openCheckoutAddressModal()" style="font-size: 0.82rem;">
                                    <i class="bi bi-plus-lg"></i>
                                    <span>+ Add New Address</span>
                                </button>
                            </div>



                            <!-- Delivery Notes Card (Compact & Clean on Mobile) -->
                            <div class="card checkout-delivery-notes-card shadow-xs">
                                <div class="card-body p-3 p-sm-3.5">
                                    <div class="d-flex align-items-center justify-content-between mb-2">
                                        <label for="notes" class="fw-bold text-dark d-flex align-items-center gap-2 mb-0" style="font-size: 0.88rem;">
                                            <i class="bi bi-chat-left-text-fill text-primary" style="font-size: 0.95rem;"></i>
                                            <span>Delivery Notes</span>
                                            <span class="text-muted fw-normal" style="font-size: 0.74rem;">(Optional)</span>
                                        </label>
                                        <span id="notes-counter" class="badge rounded-pill bg-light text-secondary border font-monospace px-2 py-0.5" style="font-size: 0.68rem;">0 / 150</span>
                                    </div>
                                    <textarea class="form-control rounded-3 px-3 py-2" 
                                              id="notes" 
                                              name="notes" 
                                              rows="2" 
                                              maxlength="150" 
                                              placeholder="e.g. Call before delivery, leave with security, landmark..." 
                                              style="font-size: 0.84rem; line-height: 1.45; border: 1px solid #cbd5e1 !important; resize: none;"></textarea>
                                    <div class="text-end mt-1" id="notes-warning-wrap">
                                        <small id="notes-warning" class="text-danger fw-semibold" style="font-size: 0.72rem; display: none;">
                                            <i class="bi bi-exclamation-circle me-1"></i>Max 150 characters reached
                                        </small>
                                    </div>
                                </div>
                            </div>

                            <!-- Items in Order Summary Card (Hidden on Mobile as requested) -->
                            <div class="card border-0 rounded-4 shadow-xs mb-4 overflow-hidden d-none d-md-block" style="background: #ffffff; border: 1.5px solid #e2e8f0 !important;">
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
                                                    <img src="{{ asset('storage/' . $cartItem->product->main_image) }}" alt="" aria-hidden="true" class="rounded-2 border flex-shrink-0 bg-white" style="width: 26px; height: 26px; object-fit: contain;">
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

                        <div class="card-body p-3 p-sm-3.5">
                            <!-- Payment Tiles Container -->
                            <div id="payment-tiles-container" style="{{ ($useWallet && $grandTotal <= 0) ? 'opacity: 0.45; pointer-events: none; filter: grayscale(1);' : '' }}">
                                
                                <!-- Option 1: Razorpay Online Payment (Default) -->
                                <div class="payment-tile active-tile" onclick="document.getElementById('pay_online').checked = true; document.getElementById('pay_online').dispatchEvent(new Event('change'));">
                                    <div class="d-flex align-items-center gap-3">
                                        <input class="form-check-input flex-shrink-0 cursor-pointer m-0" type="radio" name="payment_method" id="pay_online" value="online" checked form="checkout-form" style="width: 1.25rem; height: 1.25rem; accent-color: #4f46e5;">
                                        <div class="flex-grow-1 min-w-0">
                                            <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
                                                <label class="form-check-label fw-bold text-dark mb-0 cursor-pointer fs-6" for="pay_online">
                                                    💳 Online Payment (Razorpay)
                                                </label>
                                                <span class="badge bg-light text-secondary border rounded-pill px-2.5 py-1 fw-semibold" style="font-size: 0.68rem;">UPI, Cards, NetBanking</span>
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
                                    <div class="d-flex align-items-center gap-3">
                                        <input class="form-check-input flex-shrink-0 cursor-pointer m-0" type="radio" name="payment_method" id="cod" value="cod" form="checkout-form" style="width: 1.25rem; height: 1.25rem; accent-color: #4f46e5;">
                                        <div class="flex-grow-1 min-w-0">
                                            <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
                                                <label class="form-check-label fw-bold text-dark mb-0 cursor-pointer fs-6" for="cod" id="cod-label">
                                                    💵 Cash on Delivery (COD)
                                                </label>
                                                <span id="cod-badge" class="badge bg-light text-secondary border rounded-pill px-2.5 py-1 fw-semibold" style="font-size: 0.68rem;">PAY AT DOORSTEP</span>
                                            </div>

                                            <div id="cod-unavailable-notice" class="alert alert-danger py-2 px-3 small mt-2 rounded-3 d-none mb-0" style="font-size: 0.76rem;">
                                                <i class="bi bi-slash-circle me-1.5"></i> Cash on Delivery is unavailable for this PIN code.
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                @endif
                            </div>
                        </div>
                    </div>

                </div>

                <!-- ====================================================== -->
                <!-- STEP 4 VIEW: CONFIRM ORDER & PLACE ORDER / PAY        -->
                <!-- ====================================================== -->
                <div id="checkout-step-4-view" class="d-none">
                    
                    <!-- Items in Order Card -->
                    <div class="card border-0 shadow-xs rounded-4 overflow-hidden mb-4" style="background: #ffffff; border: 1.5px solid #4f46e5 !important;">
                        <div class="card-header bg-white py-3 px-3.5 px-md-4 border-bottom d-flex align-items-center justify-content-between flex-wrap gap-2">
                            <div class="d-flex align-items-center gap-2.5">
                                <div class="rounded-circle text-white d-flex align-items-center justify-content-center flex-shrink-0 shadow-xs" style="width: 32px; height: 32px; background: #4f46e5; font-size: 0.95rem;">
                                    <i class="bi bi-bag-check-fill"></i>
                                </div>
                                <div class="d-flex align-items-center gap-2">
                                    <h6 class="fw-bold mb-0 text-dark" style="font-size: 0.96rem;">Items in Your Order</h6>
                                    <span class="badge rounded-pill px-2.5 py-1 fw-bold font-monospace shadow-2xs" style="background: #4f46e5 !important; color: #ffffff !important; font-size: 0.76rem; letter-spacing: 0.3px;">
                                        {{ $cart->items->sum('quantity') }} {{ Str::plural('item', $cart->items->sum('quantity')) }}
                                    </span>
                                </div>
                            </div>
                            <a href="{{ route('cart.index') }}" class="btn btn-sm btn-outline-primary rounded-pill px-3 py-1 fw-bold d-inline-flex align-items-center gap-1.5" style="font-size: 0.74rem;">
                                <i class="bi bi-pencil"></i> Edit Bag
                            </a>
                        </div>
                        <div class="card-body p-2.5 p-sm-3 p-md-4">
                            <div class="d-flex flex-column gap-2.5">
                                @foreach($cart->items as $item)
                                @php
                                    $currPrice = (float) $item->unit_price;
                                    $lineTotal = $item->quantity * $currPrice;
                                @endphp
                                <div class="p-2.5 p-sm-3 bg-light bg-opacity-75 rounded-3 border d-flex align-items-center justify-content-between gap-2 overflow-hidden" style="border-color: #e2e8f0 !important;">
                                    <div class="d-flex align-items-center gap-2.5 min-w-0 flex-grow-1 overflow-hidden">
                                        @if($item->product && $item->product->main_image)
                                            <img src="{{ asset('storage/' . $item->product->main_image) }}" alt="" aria-hidden="true" class="rounded-2 border flex-shrink-0 bg-white" style="width: 44px; height: 44px; object-fit: contain;">
                                        @else
                                            <div class="rounded-2 bg-white border d-flex align-items-center justify-content-center flex-shrink-0 text-muted" style="width: 44px; height: 44px;">
                                                <i class="bi bi-box-seam fs-4"></i>
                                            </div>
                                        @endif
                                        <div class="min-w-0 flex-grow-1">
                                            <h6 class="mb-0.5 fw-bold text-dark text-truncate" style="font-size: 0.88rem;" title="{{ $item->product->name ?? 'Product' }}">
                                                {{ $item->product->name ?? 'Product' }}
                                            </h6>
                                            <div class="text-secondary small" style="font-size: 0.76rem;">Qty: {{ $item->quantity }}</div>
                                        </div>
                                    </div>
                                    <div class="text-end flex-shrink-0 ps-2">
                                        <span class="font-monospace fw-bold text-dark fs-6" style="white-space: nowrap;">
                                            ₹{{ number_format($lineTotal, 2) }}
                                        </span>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                        </div>
                    </div>

                </div>

            </div>

            <!-- Right Column: Price Details & Items Preview (Visible ONLY on Steps 3 & 4!) -->
            <div class="col-lg-5 col-xl-4" id="checkout-summary-column">
                <div class="sticky-top" style="top: 20px; z-index: 10;">
                    
                    <!-- Order Items Summary Card (Sleek & Compact) -->
                    <div class="card border-0 shadow-xs rounded-4 overflow-hidden mb-3" id="checkout-items-summary-card" style="background: #ffffff; border: 1.5px solid #e2e8f0 !important;">
                        <div class="card-header bg-white py-2.5 px-3 px-sm-3.5 border-bottom d-flex align-items-center justify-content-between">
                            <div class="d-flex align-items-center gap-2.5">
                                <div class="rounded-circle d-flex align-items-center justify-content-center text-primary flex-shrink-0" style="width: 28px; height: 28px; background: rgba(79, 70, 229, 0.08); font-size: 0.88rem;">
                                    <i class="bi bi-bag-check-fill"></i>
                                </div>
                                <div class="d-flex align-items-center gap-2">
                                    <h6 class="fw-bold mb-0 text-dark" style="font-size: 0.9rem;">Items in Order</h6>
                                    <span class="badge bg-dark text-white rounded-pill px-2 py-0.5 fw-bold font-monospace shadow-xs" style="font-size: 0.72rem;">
                                        {{ $cart->items->sum('quantity') }}
                                    </span>
                                </div>
                            </div>
                            <a href="{{ route('cart.index') }}" class="btn btn-sm btn-outline-primary rounded-pill px-2.5 py-0.5 fw-bold d-inline-flex align-items-center gap-1" style="font-size: 0.72rem;">
                                <i class="bi bi-pencil"></i> Edit Bag
                            </a>
                        </div>
                        <div class="card-body p-2.5 p-sm-3">
                            <div class="checkout-items-wrapper" style="max-height: 220px; overflow-y: auto;">
                                @foreach($cart->items as $item)
                                @php
                                    $currPrice = (float) $item->unit_price;
                                    $lineTotal = $item->quantity * $currPrice;
                                @endphp
                                <div class="p-2 mb-2 bg-light bg-opacity-50 rounded-3 border d-flex align-items-center justify-content-between gap-2 overflow-hidden" style="border-color: #e2e8f0 !important;">
                                    <div class="d-flex align-items-center gap-2 min-w-0 flex-grow-1 overflow-hidden">
                                        @if($item->product && $item->product->main_image)
                                            <img src="{{ asset('storage/' . $item->product->main_image) }}" alt="" aria-hidden="true" class="rounded-2 border flex-shrink-0 bg-white" style="width: 40px; height: 40px; object-fit: contain;">
                                        @else
                                            <div class="rounded-2 bg-white border d-flex align-items-center justify-content-center flex-shrink-0 text-muted" style="width: 40px; height: 40px;">
                                                <i class="bi bi-box-seam fs-6"></i>
                                            </div>
                                        @endif
                                        <div class="min-w-0 flex-grow-1">
                                            <h6 class="mb-0.5 fw-bold text-dark text-truncate" style="font-size: 0.82rem;" title="{{ $item->product->name ?? 'Product' }}">
                                                {{ $item->product->name ?? 'Product' }}
                                            </h6>
                                            <div class="text-secondary small" style="font-size: 0.72rem;">Qty: {{ $item->quantity }}</div>
                                        </div>
                                    </div>
                                    <div class="text-end flex-shrink-0 ps-2">
                                        <span class="font-monospace fw-bold text-dark" style="font-size: 0.86rem; white-space: nowrap;">
                                            ₹{{ number_format($lineTotal, 2) }}
                                        </span>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                        </div>
                    </div>

                    <!-- Price Details Card (Sleek & Compact) -->
                    <div class="card border-0 shadow-xs rounded-4 overflow-hidden mb-3" style="background: #ffffff; border: 1.5px solid #e2e8f0 !important;">
                        <div class="card-header bg-white py-2.5 px-3 px-sm-3.5 border-bottom d-flex align-items-center justify-content-between">
                            <h6 class="fw-bold mb-0 text-dark d-flex align-items-center" style="font-size: 0.88rem;">
                                <i class="bi bi-receipt text-primary me-2"></i>
                                <span>Price Details</span>
                            </h6>
                            <span class="badge bg-light text-secondary border px-2 py-0.5 rounded-pill" style="font-size: 0.7rem;">
                                {{ $cart->items->sum('quantity') }} {{ Str::plural('Item', $cart->items->sum('quantity')) }}
                            </span>
                        </div>
                        <div class="card-body p-3 px-sm-3.5 py-3">
                            <div class="price-row text-secondary">
                                <span>Total MRP</span>
                                <span class="font-monospace text-dark fw-semibold">₹{{ number_format($totalMrp ?? $subtotal, 2) }}</span>
                            </div>
                            @if(isset($totalDiscount) && $totalDiscount > 0)
                            <div class="price-row text-success">
                                <span>Discount on MRP</span>
                                <span class="font-monospace fw-bold">- ₹{{ number_format($totalDiscount, 2) }}</span>
                            </div>
                            @endif
                            <div class="price-row text-secondary">
                                <span>Cart Subtotal</span>
                                <span class="font-monospace fw-semibold text-dark" id="summary-subtotal">₹{{ number_format($subtotal, 2) }}</span>
                            </div>
                            <div class="price-row text-success {{ $discountAmount > 0 ? '' : 'd-none' }}" id="summary-discount-row">
                                <span class="d-flex align-items-center gap-1.5 fw-semibold">
                                    <i class="bi bi-tag-fill"></i> <span id="summary-discount-label">Coupon ({{ $couponCode }})</span>
                                </span>
                                <span class="font-monospace fw-bold" id="summary-discount-val">- ₹{{ number_format($discountAmount, 2) }}</span>
                            </div>
                            <div class="price-row text-success {{ $walletDiscount > 0 ? '' : 'd-none' }}" id="summary-wallet-row">
                                <span class="d-flex align-items-center gap-1.5 fw-semibold">
                                    <i class="bi bi-wallet2"></i> <span>Wallet Credits</span>
                                </span>
                                <span class="font-monospace fw-bold" id="summary-wallet-val">- ₹{{ number_format($walletDiscount, 2) }}</span>
                            </div>
                            <div class="price-row text-secondary">
                                <span>Delivery Fee</span>
                                <span id="summary-shipping-fee">
                                    @if(isset($shippingFee) && $shippingFee > 0)
                                        <span class="font-monospace text-dark fw-bold">+ ₹{{ number_format($shippingFee, 2) }}</span>
                                    @else
                                        <span class="badge bg-success bg-opacity-10 text-success fw-bold rounded-pill px-2 py-0.5" style="font-size: 0.72rem;">FREE</span>
                                    @endif
                                </span>
                            </div>
                            <div class="price-row text-secondary" id="summary-cod-fee-row">
                                <span>COD Handling Fee</span>
                                <span id="summary-cod-fee-val">
                                    <span class="badge bg-success bg-opacity-10 text-success fw-bold rounded-pill px-2 py-0.5" style="font-size: 0.72rem;">₹0.00 (Prepaid Offer)</span>
                                </span>
                            </div>
                            <div class="grand-total-row-wrapper d-flex justify-content-between align-items-center">
                                <div>
                                    <span class="fw-bold text-dark fs-6 d-block">Grand Total</span>
                                    <small class="text-muted d-inline-flex align-items-center gap-1" style="font-size: 0.72rem;">
                                        <i class="bi bi-shield-check text-success"></i> Inclusive of all taxes
                                    </small>
                                </div>
                                <div class="text-end">
                                    <span class="fs-5 fw-bolder text-dark font-monospace text-nowrap" id="summary-grand-total">₹{{ number_format($grandTotal, 2) }}</span>
                                </div>
                            </div>

                            @if((isset($totalDiscount) && $totalDiscount > 0) || $discountAmount > 0 || $walletDiscount > 0)
                            <div class="bg-success bg-opacity-10 text-success rounded-3 py-1.5 px-2.5 text-center mt-2.5 fw-semibold" style="font-size: 0.74rem;">
                                <i class="bi bi-check-circle-fill me-1"></i> You save ₹{{ number_format(($totalDiscount ?? 0) + $discountAmount + $walletDiscount, 2) }} on this order
                            </div>
                            @endif
                        </div>
                    </div>

                    <!-- ============================================================== -->
                    <!-- STEP 3 & STEP 4 ACTION BUTTONS (Generously Spaced Below Cards) -->
                    <!-- ============================================================== -->
                    <!-- Step 3 Action Buttons (Visible on Step 3) -->
                    <div id="step-3-summary-actions" class="mt-3 mb-3">
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
                    <div id="step-4-summary-actions" class="mt-3 mb-3 d-none">
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


<!-- ============================================================== -->
<!-- INTERACTIVE MAP LOCATION PICKER MODAL (AMAZON / SWIGGY STYLE)  -->
<!-- ============================================================== -->
<div class="modal fade" id="locationPickerModal" tabindex="-1" aria-labelledby="locationPickerModalLabel" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered modal-lg modal-dialog-scrollable">
        <div class="modal-content rounded-4 border-0 shadow-lg overflow-hidden">
            <div class="modal-header bg-white py-2.5 px-3.5 px-md-4 border-bottom d-flex align-items-center justify-content-between">
                <div class="d-flex align-items-center gap-2.5">
                    <div class="rounded-circle d-flex align-items-center justify-content-center text-primary flex-shrink-0" style="width: 34px; height: 34px; background: rgba(79, 70, 229, 0.1);">
                        <i class="bi bi-geo-alt-fill fs-6"></i>
                    </div>
                    <div>
                        <h6 class="modal-title fw-bold mb-0 text-dark" id="locationPickerModalLabel" style="font-size: 0.95rem;">Select Delivery Location on Map</h6>
                        <small class="text-muted" style="font-size: 0.72rem;">Drag the pin or search to set your exact doorstep</small>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            
            <div class="modal-body p-0">
                <!-- Map Container -->
                <div class="position-relative" style="background: #e2e8f0;">
                    <div id="delivery-map-container" style="min-height: 290px; height: 290px; width: 100%; z-index: 1; position: relative; background: #e2e8f0;"></div>

                    <!-- Location Permission / Fallback Alert -->
                    <div id="map-perm-hint" class="position-absolute top-0 start-0 end-0 m-2 alert alert-warning py-1.5 px-3 small d-none d-flex align-items-center justify-content-between shadow rounded-3 border-0" style="z-index: 1050; font-size: 0.74rem;">
                        <span><i class="bi bi-info-circle-fill me-1.5 text-warning"></i> GPS blocked. Centered via network area. Drag the pin or search above for your exact doorstep.</span>
                        <button type="button" class="btn-close ms-2" style="font-size: 0.65rem;" onclick="document.getElementById('map-perm-hint').classList.add('d-none')"></button>
                    </div>
                    
                    <!-- Search Location Floating Box -->
                    <div class="position-absolute top-0 start-0 end-0 p-2.5" style="z-index: 1000; pointer-events: none;">
                        <div class="input-group shadow-sm rounded-pill overflow-hidden bg-white border" style="pointer-events: auto;">
                            <span class="input-group-text bg-white border-0 text-muted ps-3 pe-1"><i class="bi bi-search"></i></span>
                            <input type="text" class="form-control border-0 py-2" id="map-search-input" placeholder="Search area, colony, or street in India..." style="font-size: 0.84rem;">
                            <button type="button" class="btn btn-white border-0 text-muted pe-3 d-none" id="btn-clear-map-search" onclick="clearMapSearch()"><i class="bi bi-x-circle-fill"></i></button>
                        </div>
                        <div id="map-search-results" class="list-group shadow-sm mt-1 d-none rounded-3 overflow-hidden" style="max-height: 180px; overflow-y: auto; pointer-events: auto; font-size: 0.8rem;"></div>
                    </div>

                    <!-- Floating Locate Me Button (Target GPS) -->
                    <button type="button" class="btn btn-light shadow rounded-circle d-flex align-items-center justify-content-center position-absolute bottom-0 end-0 m-3 border" id="btn-locate-me" onclick="recenterToGpsLocation()" title="Locate Me via GPS" style="width: 42px; height: 42px; z-index: 1000; color: #4f46e5;">
                        <i class="bi bi-crosshair fs-5"></i>
                    </button>
                    
                    <!-- Pin Tip Helper Banner -->
                    <div class="position-absolute bottom-0 start-0 m-2.5 px-2.5 py-1 rounded-pill bg-dark bg-opacity-75 text-white small" style="z-index: 1000; font-size: 0.72rem;">
                        <i class="bi bi-pin-map-fill me-1 text-warning"></i> Drag pin or click map to move
                    </div>
                </div>

                <!-- Detected Location Summary & Address Form -->
                <div class="p-3.5 p-md-4 bg-white">
                    
                    <!-- Detected Location Pill Bar -->
                    <div class="p-2.5 rounded-3 mb-3 d-flex align-items-start gap-2.5 border" id="map-detected-badge" style="background: #f8fafc; border-color: #e2e8f0 !important;">
                        <div class="rounded-circle d-flex align-items-center justify-content-center text-primary flex-shrink-0 mt-0.5" style="width: 26px; height: 26px; background: rgba(79, 70, 229, 0.1);">
                            <i class="bi bi-geo-alt-fill" style="font-size: 0.85rem;"></i>
                        </div>
                        <div class="min-w-0 flex-grow-1">
                            <div class="d-flex align-items-center justify-content-between gap-2 flex-wrap">
                                <span class="fw-bold text-dark small" id="map-detected-area">Detecting location...</span>
                                <span class="badge bg-primary text-white rounded-pill px-2.5 py-1 font-monospace fw-bold" id="map-detected-zip" style="font-size: 0.72rem;">------</span>
                            </div>
                            <div class="text-secondary mt-0.5 text-truncate" id="map-detected-city-state" style="font-size: 0.76rem;">Please allow GPS or search your area.</div>
                        </div>
                    </div>

                    <!-- Quick Address Form (House No + Landmark + Contact) -->
                    <div class="row g-2.5">
                        <div class="col-12 col-sm-6">
                            <label for="map_house_no" class="form-label fw-semibold small mb-1" style="font-size: 0.78rem;">Flat / House / Building No. <span class="text-danger">*</span></label>
                            <input type="text" class="form-control form-control-sm rounded-3 py-1.5" id="map_house_no" placeholder="e.g. Flat 302, Sai Residency">
                        </div>
                        <div class="col-12 col-sm-6">
                            <label for="map_landmark" class="form-label fw-semibold small mb-1" style="font-size: 0.78rem;">Landmark <span class="text-muted">(Optional)</span></label>
                            <input type="text" class="form-control form-control-sm rounded-3 py-1.5" id="map_landmark" placeholder="e.g. Near Apollo Pharmacy">
                        </div>
                        <div class="col-12">
                            <label for="map_road_area" class="form-label fw-semibold small mb-1" style="font-size: 0.78rem;">Road / Street / Colony <span class="text-danger">*</span></label>
                            <input type="text" class="form-control form-control-sm rounded-3 py-1.5" id="map_road_area" placeholder="Detected street or colony">
                        </div>
                        <div class="col-12 col-sm-4">
                            <label for="map_city" class="form-label fw-semibold small mb-1" style="font-size: 0.78rem;">City <span class="text-danger">*</span></label>
                            <input type="text" class="form-control form-control-sm rounded-3 py-1.5" id="map_city" placeholder="City" oninput="this.value = this.value.replace(/[^a-zA-Z\s]/g, '')">
                        </div>
                        <div class="col-12 col-sm-4">
                            <label for="map_state" class="form-label fw-semibold small mb-1" style="font-size: 0.78rem;">State <span class="text-danger">*</span></label>
                            <input type="text" class="form-control form-control-sm rounded-3 py-1.5" id="map_state" placeholder="State" oninput="this.value = this.value.replace(/[^a-zA-Z\s]/g, '')">
                        </div>
                        <div class="col-12 col-sm-4">
                            <label for="map_zip_code" class="form-label fw-semibold small mb-1" style="font-size: 0.78rem;">Pincode <span class="text-danger">*</span></label>
                            <input type="text" class="form-control form-control-sm rounded-3 font-monospace fw-bold py-1.5" id="map_zip_code" placeholder="6-digit PIN" maxlength="6" inputmode="numeric" onkeypress="return event.charCode >= 48 && event.charCode <= 57" oninput="this.value = this.value.replace(/\D/g, '').slice(0, 6)">
                        </div>
                        <div class="col-12 col-sm-6">
                            <label for="map_name" class="form-label fw-semibold small mb-1" style="font-size: 0.78rem;">Receiver Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control form-control-sm rounded-3 py-1.5" id="map_name" value="{{ auth()->user()->name ?? '' }}" placeholder="Name" autocomplete="name">
                        </div>
                        <div class="col-12 col-sm-6">
                            <label for="map_phone" class="form-label fw-semibold small mb-1" style="font-size: 0.78rem;">10-Digit Mobile <span class="text-danger">*</span></label>
                            <input type="tel" class="form-control form-control-sm rounded-3 py-1.5" id="map_phone" value="{{ auth()->user()->mobile_number ?? '' }}" placeholder="Mobile number" maxlength="10" inputmode="tel" autocomplete="tel" oninput="this.value = this.value.replace(/\D/g, '').slice(0, 10)">
                        </div>
                        
                        <!-- Address Type (Home / Work / Other) -->
                        <div class="col-12 pt-1">
                            <label class="form-label fw-semibold small mb-1 d-block" style="font-size: 0.78rem;">Save Address As</label>
                            <div class="btn-group btn-group-sm w-100" role="group">
                                <input type="radio" class="btn-check" name="map_address_type" id="map_type_home" value="home" checked autocomplete="off">
                                <label class="btn btn-outline-secondary py-1" for="map_type_home" style="font-size: 0.76rem;"><i class="bi bi-house me-1"></i>Home</label>

                                <input type="radio" class="btn-check" name="map_address_type" id="map_type_work" value="work" autocomplete="off">
                                <label class="btn btn-outline-secondary py-1" for="map_type_work" style="font-size: 0.76rem;"><i class="bi bi-briefcase me-1"></i>Work</label>

                                <input type="radio" class="btn-check" name="map_address_type" id="map_type_other" value="other" autocomplete="off">
                                <label class="btn btn-outline-secondary py-1" for="map_type_other" style="font-size: 0.76rem;"><i class="bi bi-geo me-1"></i>Other</label>
                            </div>
                        </div>
                    </div>

                </div>
            </div>

            <div class="modal-footer bg-light py-2.5 px-3.5 px-md-4 border-top d-flex justify-content-between align-items-center">
                <button type="button" class="btn btn-light border rounded-pill px-3.5 py-1.5 btn-sm fw-semibold" data-bs-dismiss="modal" style="font-size: 0.8rem;">Cancel</button>
                <button type="button" class="btn btn-primary rounded-pill px-4 py-2 btn-sm fw-bold shadow-xs d-inline-flex align-items-center gap-1.5" id="btn-save-map-address" onclick="saveMapAddressToCheckout()" style="background: #4f46e5; border: none; font-size: 0.84rem;">
                    <i class="bi bi-check2-circle fs-6"></i>
                    <span>Confirm Location &amp; Deliver Here</span>
                </button>
            </div>
        </div>
    </div>
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
    <div class="modal-dialog modal-dialog-centered modal-lg modal-dialog-scrollable">
        <div class="modal-content rounded-4 border-0 shadow">
            <div class="modal-header border-bottom py-3 px-3.5 px-md-4 d-flex align-items-center justify-content-between">
                <div class="d-flex align-items-center gap-2.5">
                    <div class="rounded-circle d-flex align-items-center justify-content-center text-primary flex-shrink-0" style="width: 38px; height: 38px; background: rgba(79, 70, 229, 0.1);">
                        <i class="bi bi-geo-alt-fill fs-5"></i>
                    </div>
                    <div>
                        <div class="d-flex align-items-center gap-2 flex-wrap">
                                <h5 class="modal-title fw-bold mb-0 text-dark" id="addressModalLabel" style="font-size: 1.05rem;">Select Saved Address</h5>
                                <button type="button" class="btn btn-sm btn-primary rounded-pill px-2.5 py-0.5 fw-semibold d-inline-flex align-items-center gap-1 ms-1 shadow-xs" onclick="const m = bootstrap.Modal.getInstance(document.getElementById('addressSelectorModal')); if (m) m.hide(); openLocationPickerModal(true);" style="font-size: 0.74rem; background: #4f46e5; border-color: #4f46e5;">
                                    <i class="bi bi-crosshair"></i> Use Current Location
                                </button>
                            </div>
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
                            <div class="d-flex flex-column flex-sm-row justify-content-between align-items-start align-items-sm-center gap-2.5">
                                <div class="min-w-0 flex-grow-1 w-100">
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
                                <div class="flex-shrink-0 align-self-end align-self-sm-center d-flex align-items-center gap-1.5 mt-2 mt-sm-0">
                                    @if($isCurrent)
                                        <span class="btn btn-sm btn-success rounded-pill px-3 py-1.5 fw-semibold shadow-xs text-nowrap" style="font-size: 0.78rem; pointer-events: none;">
                                            <i class="bi bi-check2"></i> Selected
                                        </span>
                                    @else
                                        <button type="button" class="btn btn-sm btn-outline-primary rounded-pill px-3 py-1.5 fw-semibold text-nowrap" style="font-size: 0.78rem;">
                                            Select
                                        </button>
                                    @endif
                                    <button type="button" class="btn btn-sm btn-outline-secondary rounded-circle p-0 d-inline-flex align-items-center justify-content-center" style="width: 34px; height: 34px;" onclick="editCheckoutAddress(event, {{ $addr->id }})" title="Edit Address">
                                        <i class="bi bi-pencil small"></i>
                                    </button>
                                    <button type="button" class="btn btn-sm btn-outline-danger rounded-circle p-0 d-inline-flex align-items-center justify-content-center" style="width: 34px; height: 34px;" onclick="deleteCheckoutAddress(event, {{ $addr->id }}, this)" title="Delete Address">
                                        <i class="bi bi-trash small"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
            <div class="modal-footer border-top bg-light py-3 px-3.5 px-md-4 d-flex justify-content-between align-items-center" style="position: sticky; bottom: 0; z-index: 10; background: #ffffff !important;">
                <button type="button" class="btn btn-sm btn-primary rounded-pill px-4 py-2 fw-semibold d-inline-flex align-items-center gap-1.5 shadow-xs" onclick="openNewAddressFromModal()" style="background: #4f46e5; border: none; font-size: 0.82rem;">
                    <i class="bi bi-plus-lg"></i> Add New Address
                </button>
                <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill px-4 py-2 fw-semibold" data-bs-dismiss="modal" style="font-size: 0.82rem;">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- Modern Popup Modal for Adding & Editing Address at Checkout (Mobile Scroll & Sticky Save Button) -->
<div class="modal fade" id="checkoutAddressModal" tabindex="-1" aria-labelledby="checkoutAddressModalLabel" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
        <form class="modal-content border-0 shadow" id="checkoutAddressModalForm" onsubmit="handleSaveAddress(event)" novalidate>
            @csrf
            <input type="hidden" id="edit_address_id" value="">

            {{-- Modal Header --}}
            <div class="modal-header border-bottom py-3 px-3.5 px-sm-4 bg-light bg-opacity-75 d-flex align-items-center justify-content-between">
                <div class="d-flex align-items-center gap-2.5">
                    <div class="rounded-3 d-flex align-items-center justify-content-center flex-shrink-0" id="checkoutAddressModalIconBox"
                         style="width: 38px; height: 38px; background: linear-gradient(135deg, #4f46e5 0%, #3730a3 100%); color: #ffffff;">
                        <i class="bi bi-house-add-fill fs-5" id="checkoutAddressModalIcon"></i>
                    </div>
                    <div>
                        <h5 class="modal-title fw-bold text-dark mb-0" id="checkoutAddressModalLabel" style="font-size: 1.02rem;">Add New Address</h5>
                        <p class="text-muted small mb-0" id="checkoutAddressModalSubtitle" style="font-size: 0.74rem;">Enter your delivery destination details</p>
                    </div>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <button type="button" class="btn btn-sm btn-link text-primary p-0 fw-semibold text-decoration-none d-none d-sm-inline" onclick="openLocationPickerFromAddressModal()" style="font-size: 0.76rem;">
                        <i class="bi bi-map-fill me-1"></i>Select on Map
                    </button>
                    <button type="button" class="btn-close shadow-none" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
            </div>

            {{-- Modal Body --}}
            <div class="modal-body p-3.5 p-sm-4">
                <div class="d-block d-sm-none mb-2.5 text-end">
                    <button type="button" class="btn btn-sm btn-light border rounded-pill px-2.5 py-1 text-primary fw-semibold text-decoration-none" onclick="openLocationPickerFromAddressModal()" style="font-size: 0.74rem;">
                        <i class="bi bi-map-fill me-1"></i>Select on Map
                    </button>
                </div>
                <div class="row g-2.5">
                    <div class="col-12">
                        <label for="new_name" class="form-label fw-semibold small mb-1" style="font-size: 0.8rem;">Full Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control form-control-sm rounded-3 py-1.5" id="new_name" value="{{ auth()->user()->name ?? '' }}" placeholder="Enter full name" required>
                    </div>
                    <div class="col-12 col-sm-6">
                        <label for="new_phone" class="form-label fw-semibold small mb-1" style="font-size: 0.8rem;">Mobile Number <span class="text-danger">*</span></label>
                        <input type="tel" class="form-control form-control-sm rounded-3 py-1.5" id="new_phone" value="{{ auth()->user()->mobile_number ?? '' }}" placeholder="10-digit mobile" maxlength="10" inputmode="tel" autocomplete="tel" oninput="this.value = this.value.replace(/\D/g, '').slice(0, 10)" required>
                    </div>
                    <div class="col-12 col-sm-6">
                        <label for="new_zip" class="form-label fw-semibold small mb-1" style="font-size: 0.8rem;">Pincode <span class="text-danger">*</span></label>
                        <input type="text" class="form-control form-control-sm rounded-3 font-monospace fw-bold py-1.5" id="new_zip" 
                               placeholder="6-digit PIN" maxlength="6" inputmode="numeric" pattern="[0-9]{6}" autocomplete="postal-code"
                               onkeypress="return event.charCode >= 48 && event.charCode <= 57" 
                               oninput="handleCheckoutPincodeInput(this)" required>
                        <small id="new_zip_lookup_hint" class="d-block mt-0.5 transition-all" style="font-size: 0.72rem; min-height: 16px;"></small>
                    </div>
                    <div class="col-12">
                        <label for="new_address" class="form-label fw-semibold small mb-1" style="font-size: 0.8rem;">Street Address <span class="text-danger">*</span></label>
                        <textarea class="form-control form-control-sm rounded-3 p-2" id="new_address" rows="2" 
                                  placeholder="Flat / House No. (e.g. #102/B), Building Name, Street, Area, Landmark" 
                                  style="line-height: 1.45;" required></textarea>
                    </div>
                    <div class="col-12 col-sm-6">
                        <label for="new_city" class="form-label fw-semibold small mb-1" style="font-size: 0.8rem;">City / District <span class="text-danger">*</span></label>
                        <div class="position-relative">
                            <input type="text" class="form-control form-control-sm rounded-3 py-1.5 ps-3 pe-4" id="new_city" 
                                   placeholder="City / District"
                                   oninput="this.value = this.value.replace(/[^a-zA-Z\s]/g, '')" required>
                            <i class="bi bi-lock-fill position-absolute top-50 end-0 translate-middle-y me-2.5 text-muted d-none" 
                               id="new_city_lock_icon" title="Auto-filled from PIN" style="pointer-events: none; font-size: 0.82rem;"></i>
                        </div>
                        <small id="new_city_hint" class="d-block mt-0.5 text-muted transition-all" style="font-size: 0.72rem; min-height: 16px;"></small>
                    </div>
                    <div class="col-12 col-sm-6">
                        <label for="new_state" class="form-label fw-semibold small mb-1" style="font-size: 0.8rem;">State <span class="text-danger">*</span></label>
                        <div class="position-relative">
                            <input type="text" class="form-control form-control-sm rounded-3 py-1.5 ps-3 pe-4" id="new_state" 
                                   placeholder="State"
                                   oninput="this.value = this.value.replace(/[^a-zA-Z\s]/g, '')" required>
                            <i class="bi bi-lock-fill position-absolute top-50 end-0 translate-middle-y me-2.5 text-muted d-none" 
                               id="new_state_lock_icon" title="Auto-filled from PIN" style="pointer-events: none; font-size: 0.82rem;"></i>
                        </div>
                        <small id="new_state_hint" class="d-block mt-0.5 text-muted transition-all" style="font-size: 0.72rem; min-height: 16px;"></small>
                    </div>
                </div>
            </div>

            {{-- Modal Sticky Footer --}}
            <div class="modal-footer py-2.5 px-3.5 px-sm-4 d-flex justify-content-between align-items-center">
                <button type="button" class="btn btn-sm btn-light border rounded-pill px-3.5 py-1.5 fw-semibold" data-bs-dismiss="modal" style="font-size: 0.82rem;">Cancel</button>
                <button type="submit" class="btn btn-sm btn-primary rounded-pill px-4 py-2 shadow-xs fw-semibold" id="btn-save-address" disabled style="background: #4f46e5; border: none; opacity: 0.6; cursor: not-allowed; font-size: 0.82rem;">
                    <i class="bi bi-bookmark-check me-1"></i> Save Address
                </button>
            </div>
        </form>
    </div>
</div>

<script src="https://checkout.razorpay.com/v1/checkout.js"></script>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" crossorigin=""></script>
<script>
let userAddresses = {!! json_encode($addresses) !!};
let currentSelectedAddress = userAddresses.find(a => a.is_default) || (userAddresses.length > 0 ? userAddresses[0] : null);
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

    // Toggle Summary Action Buttons & Items Card (placed after Price Details)
    document.getElementById('step-3-summary-actions')?.classList.toggle('d-none', step !== 3);
    document.getElementById('step-4-summary-actions')?.classList.toggle('d-none', step !== 4);
    document.getElementById('checkout-items-summary-card')?.classList.toggle('d-none', step === 4);

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

    if (displayName) displayName.textContent = addr.name;
    if (displayStreetCity) displayStreetCity.innerHTML = `${escapeAddressHtml(addr.address)}, ${escapeAddressHtml(addr.city)}, ${escapeAddressHtml(addr.state)} <span class="text-nowrap font-monospace fw-bold text-dark">- ${escapeAddressHtml(addr.zip)}</span>`;
    if (displayPhone) displayPhone.innerHTML = `<i class="bi bi-telephone-fill text-muted me-1 small"></i> Mobile: <strong class="text-dark font-monospace">${escapeAddressHtml(addr.phone)}</strong>`;
    
    if (activeDetails) activeDetails.style.display = 'block';
    if (noAddrMsg) noAddrMsg.style.display = 'none';

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
    const addrEl = document.getElementById('new_address');
    const cityEl = document.getElementById('new_city');
    const stateEl = document.getElementById('new_state');

    if (nameEl) nameEl.value = '{{ auth()->user()->name ?? "" }}';
    if (phoneEl) phoneEl.value = '{{ auth()->user()->mobile_number ?? "" }}';
    if (zipEl) zipEl.value = '';
    if (addrEl) addrEl.value = '';
    if (cityEl) cityEl.value = '';
    if (stateEl) stateEl.value = '';

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
    const addrEl = document.getElementById('new_address');
    const cityEl = document.getElementById('new_city');
    const stateEl = document.getElementById('new_state');

    if (nameEl) nameEl.value = addr.name || '';
    if (phoneEl) phoneEl.value = addr.phone || '';
    if (zipEl) zipEl.value = addr.zip || '';
    if (addrEl) addrEl.value = addr.address || '';
    if (cityEl) cityEl.value = addr.city || '';
    if (stateEl) stateEl.value = addr.state || '';

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
                            ${isSelected ? '<span class="badge bg-success text-white rounded-pill px-2.5 py-0.5 fw-bold" style="font-size: 0.68rem;">✓ Selected</span>' : (addr.is_default ? '<span class="badge bg-light text-secondary border rounded-pill px-2 py-0.5" style="font-size: 0.65rem;">Default</span>' : '')}
                        </div>
                        <div class="text-secondary mb-1.5" style="font-size: 0.86rem; line-height: 1.45;">
                            ${escapeAddressHtml(addr.address || '')}, ${escapeAddressHtml(addr.city || '')}, ${escapeAddressHtml(addr.state || '')} <span class="text-nowrap font-monospace text-dark fw-bold">- ${escapeAddressHtml(safeZip)}</span>
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
// LEAFLET INTERACTIVE MAP & DUAL LOCATION PROVIDER (FREE OSM + LOCATIONIQ)
// ==============================================================
const LOCATIONIQ_KEY = "{{ \App\Models\Setting::get('locationiq_api_key') ?: (config('services.locationiq.key') ?: 'pk.8fcbbfbdafcf2d840b41ffd3c60e443f') }}";
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

    // 1. Try LocationIQ primary with STRICT English output (&accept-language=en)
    if (LOCATIONIQ_KEY && LOCATIONIQ_KEY !== 'pk.your_api_key_here') {
        try {
            const url = 'https://us1.locationiq.com/v1/reverse?key=' + LOCATIONIQ_KEY + '&lat=' + lat + '&lon=' + lng + '&format=json&accept-language=en&addressdetails=1&normalizeaddress=1';
            const res = await fetch(url, {
                headers: { 'Accept-Language': 'en' }
            });
            if (res.ok) {
                const data = await res.json();
                if (data && (data.address || data.display_name)) {
                    addressData = extractAddressComponents(data.address, data.display_name);
                }
            }
        } catch (e) {
            console.warn('LocationIQ reverse failed, falling back to OpenStreetMap Nominatim:', e);
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

    // 1. Try LocationIQ
    if (LOCATIONIQ_KEY && LOCATIONIQ_KEY !== 'pk.your_api_key_here') {
        try {
            const url = 'https://api.locationiq.com/v1/autocomplete?key=' + LOCATIONIQ_KEY + '&q=' + encodeURIComponent(query) + '&countrycodes=in&accept-language=en&limit=5&format=json';
            const res = await fetch(url);
            if (res.ok) {
                const data = await res.json();
                if (Array.isArray(data) && data.length > 0) {
                    list = data;
                }
            }
        } catch (e) {
            console.warn('LocationIQ search failed, falling back to OSM:', e);
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
        address: fullStreetAddress,
        city: city || 'City',
        state: state || 'State',
        zip: zip,
        country: 'India',
        latitude: currentMapCoords.lat,
        longitude: currentMapCoords.lng,
        landmark: landmark || null,
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
</script>
@endpush