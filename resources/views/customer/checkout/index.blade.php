@extends('layouts.customer')

@section('title', 'Secure Checkout — ' . \App\Models\Setting::get('store_name', 'ShopCalm'))

@push('styles')
<script src="https://cdn.jsdelivr.net/npm/canvas-confetti@1.9.2/dist/confetti.browser.min.js"></script>
<style>
    /* Checkout Stepper Badges */
    .checkout-step-badge {
        width: 32px;
        height: 32px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 700;
        font-size: 0.85rem;
        transition: all 0.3s ease;
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
            width: 26px;
            height: 26px;
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
    
    <!-- Top Stepper Progress Header (1. Address -> 2. Payment & Coupons -> 3. Confirm & Pay) -->
    <div class="row justify-content-center mb-3 mb-md-4">
        <div class="col-lg-9">
            <div class="d-flex align-items-center justify-content-between position-relative px-2 px-md-4">
                <!-- Background Progress Line -->
                <div class="position-absolute" style="top: 14px; left: 12%; right: 12%; height: 2.5px; background: #e2e8f0; z-index: 0;"></div>
                <div class="position-absolute" id="stepper-progress-line" style="top: 14px; left: 12%; width: 0%; height: 2.5px; background: #4f46e5; z-index: 1; transition: width 0.4s ease;"></div>

                <!-- Step 1 Tab: Address -->
                <div class="d-flex flex-column align-items-center position-relative cursor-pointer" id="stepper-tab-1" onclick="editStep1()" style="z-index: 2;">
                    <div class="checkout-step-badge text-white shadow-xs" id="stepper-badge-1" style="background: #4f46e5; box-shadow: 0 0 0 4px rgba(79, 70, 229, 0.2) !important;">
                        1
                    </div>
                    <span class="fw-bold text-primary mt-1" id="stepper-text-1" style="font-size: 0.76rem;">1. Address</span>
                </div>

                <!-- Step 2 Tab: Payment & Offers -->
                <div class="d-flex flex-column align-items-center position-relative cursor-pointer" id="stepper-tab-2" onclick="editStep2()" style="z-index: 2;">
                    <div class="checkout-step-badge bg-light text-muted border" id="stepper-badge-2">
                        2
                    </div>
                    <span class="fw-semibold text-muted mt-1" id="stepper-text-2" style="font-size: 0.76rem;">2. Payment & Offers</span>
                </div>

                <!-- Step 3 Tab: Confirm & Pay -->
                <div class="d-flex flex-column align-items-center position-relative" id="stepper-tab-3" style="z-index: 2;">
                    <div class="checkout-step-badge bg-light text-muted border" id="stepper-badge-3">
                        3
                    </div>
                    <span class="fw-semibold text-muted mt-1" id="stepper-text-3" style="font-size: 0.76rem;">3. Confirm & Pay</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Hidden Checkout Form -->
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

        <!-- ====================================================== -->
        <!-- TAB 1: DELIVERY ADDRESS & ORDER ITEMS PREVIEW          -->
        <!-- ====================================================== -->
        <div id="checkout-tab-1-view" class="row justify-content-center g-3 g-lg-4">
            <div class="col-lg-8">
                <!-- Back to Bag link -->
                <div class="mb-2">
                    <a href="{{ route('cart.index') }}" class="text-decoration-none small text-muted fw-semibold">
                        <i class="bi bi-arrow-left me-1"></i> Back to Shopping Bag
                    </a>
                </div>

                <!-- Delivery Address Selection Card -->
                <div class="card border-0 shadow-xs rounded-4 overflow-hidden mb-3.5" style="background: #ffffff; border: 1.5px solid #4f46e5 !important;">
                    <div class="card-header bg-white py-3 px-3 px-md-4 border-bottom d-flex align-items-center justify-content-between flex-wrap gap-2">
                        <div class="d-flex align-items-center gap-2.5">
                            <div class="rounded-circle text-white d-flex align-items-center justify-content-center" style="width: 32px; height: 32px; background: #4f46e5;">
                                <i class="bi bi-geo-alt-fill fs-6"></i>
                            </div>
                            <div>
                                <h6 class="fw-bold mb-0 text-dark" style="font-size: 0.95rem;">Select Delivery Address</h6>
                                <small class="text-muted" style="font-size: 0.74rem;">Where should we send your package?</small>
                            </div>
                        </div>
                        @if($addresses->count() > 0)
                        <button type="button" class="btn btn-sm btn-outline-primary rounded-pill px-3 py-1 fw-bold" onclick="showAddressSelectorModal()" style="font-size: 0.78rem;">
                            <i class="bi bi-geo-alt me-1"></i> Saved Addresses ({{ $addresses->count() }})
                        </button>
                        @endif
                    </div>

                    <div class="card-body p-3 p-md-4">
                        <div id="address-error-banner" class="alert alert-danger small mb-3 rounded-3 border-0 bg-danger-subtle text-danger" style="display: none;">
                            <i class="bi bi-exclamation-triangle-fill me-2"></i><span>Please select or add a valid delivery address before proceeding.</span>
                        </div>

                        <div id="address-success-banner" class="alert alert-success small mb-3 rounded-3 border-0 bg-success-subtle text-success" style="display: none;">
                            <i class="bi bi-check-circle-fill me-2"></i><span>Address saved successfully!</span>
                        </div>

                        <!-- Active Address Box (Always Visible if Addresses Exist) -->
                        <div id="active-address-display" class="p-3 p-md-3.5 rounded-3 border address-card-selected mb-3">
                            <div id="no-address-msg" style="display: {{ $addresses->count() > 0 ? 'none' : 'block' }};" class="text-center py-3 text-muted">
                                <i class="bi bi-building-add fs-2 text-primary d-block mb-1.5"></i>
                                <span class="small fw-semibold text-dark">No saved address found. Click <strong>"+ Add New Address"</strong> below to add one.</span>
                            </div>

                            @php $activeAddr = $addresses->count() > 0 ? ($addresses->firstWhere('is_default', true) ?? $addresses->first()) : null; @endphp
                            <div id="active-address-details" style="display: {{ $addresses->count() > 0 ? 'block' : 'none' }};">
                                <div class="d-flex justify-content-between align-items-center mb-1.5 flex-wrap gap-1">
                                    <div class="d-flex align-items-center gap-2">
                                        <h6 class="fw-bold mb-0 text-dark" style="font-size: 0.92rem;" id="display-name">{{ $activeAddr ? $activeAddr->name : '' }}</h6>
                                        <span class="badge bg-success text-white rounded-pill px-2 py-0.5 fw-bold" style="font-size: 0.68rem;">✓ Selected</span>
                                    </div>
                                    @if($addresses->count() > 1)
                                    <button type="button" class="btn btn-sm btn-link text-primary text-decoration-none fw-bold p-0" onclick="showAddressSelectorModal()" style="font-size: 0.78rem;">
                                        <i class="bi bi-arrow-repeat me-1"></i> Switch Address
                                    </button>
                                    @endif
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

                        <!-- Add New Address Button Action Bar -->
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill px-3 py-1.5 fw-semibold" onclick="openNewAddressForm()" style="font-size: 0.8rem;">
                                <i class="bi bi-plus-lg me-1"></i> + Add New Address
                            </button>
                        </div>

                        <!-- Collapsible Add New Address Form -->
                        <div class="card border rounded-3 mb-3.5 overflow-hidden shadow-xs" id="new-address-card" style="display: {{ $addresses->count() === 0 ? 'block' : 'none' }}; border-color: #cbd5e1 !important; background: #f8fafc;">
                            <div class="card-header bg-white py-2.5 px-3 d-flex justify-content-between align-items-center border-bottom">
                                <h6 class="fw-bold mb-0 text-dark" style="font-size: 0.88rem;"><i class="bi bi-house-add text-primary me-2"></i>Add New Delivery Address</h6>
                                @if($addresses->count() > 0)
                                    <button type="button" class="btn-close" onclick="closeNewAddressForm()"></button>
                                @endif
                            </div>
                            <div class="card-body p-3 p-md-3.5">
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
                                            <button type="button" class="btn btn-light border rounded-pill px-3.5 btn-sm fw-semibold" onclick="closeNewAddressForm()">Cancel</button>
                                        @endif
                                        <button type="submit" class="btn btn-primary rounded-pill px-4 btn-sm shadow-xs fw-semibold" id="btn-save-address" style="background: #4f46e5; border: none;">
                                            <i class="bi bi-bookmark-check me-1"></i> Save Address & Continue
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>

                        <!-- Delivery Instructions / Notes (Optional) -->
                        <div class="p-3 rounded-3 border bg-light mb-3">
                            <h6 class="fw-bold mb-1.5 text-dark" style="font-size: 0.84rem;">
                                <i class="bi bi-chat-left-text text-primary me-1.5"></i>Delivery Notes (Optional)
                            </h6>
                            <textarea class="form-control rounded-3" id="notes" name="notes" rows="2" placeholder="e.g. Leave with security / Call before ringing the bell." style="font-size: 0.82rem;"></textarea>
                        </div>

                        <!-- Deliver to This Address Button -->
                        <button type="button" class="btn btn-primary btn-lg rounded-pill w-100 py-3 fw-bold shadow-xs text-white d-flex align-items-center justify-content-center gap-2" id="btn-confirm-address" onclick="proceedToStep2()" style="background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%); border: none; font-size: 0.92rem; letter-spacing: 0.02em;">
                            <span>DELIVER TO THIS ADDRESS</span>
                            <i class="bi bi-arrow-right fs-5"></i>
                        </button>
                    </div>
                </div>

                <!-- Order Items Summary Preview Card -->
                <div class="card border-0 shadow-xs rounded-4 overflow-hidden" style="background: #ffffff; border: 1px solid #e2e8f0 !important;">
                    <div class="card-header bg-white py-3 px-3 px-md-4 border-bottom">
                        <h6 class="fw-bold mb-0 text-dark" style="font-size: 0.95rem;">
                            <i class="bi bi-bag-check-fill text-primary me-1.5"></i>Items in Order ({{ $cart->items->sum('quantity') }})
                        </h6>
                    </div>
                    <div class="card-body p-3 p-md-4">
                        <div class="checkout-items-wrapper" style="max-height: 240px; overflow-y: auto;">
                            @foreach($cart->items as $item)
                            @php
                                $origPrice = (float) ($item->product->price ?? $item->unit_price);
                                $currPrice = (float) $item->unit_price;
                                $origLineTotal = $item->quantity * $origPrice;
                                $lineTotal = $item->quantity * $currPrice;
                                $hasDiscount = ($origPrice > $currPrice);
                            @endphp
                            <div class="p-2.5 mb-2 bg-white rounded-3 border shadow-xs d-flex align-items-center justify-content-between gap-2.5" style="border-color: #f1f5f9 !important;">
                                <div class="d-flex align-items-center gap-2.5 min-w-0">
                                    @if($item->product && $item->product->main_image)
                                        <img src="{{ asset('storage/' . $item->product->main_image) }}" alt="{{ $item->product->name }}" class="rounded-2 border flex-shrink-0" style="width: 44px; height: 44px; object-fit: contain;">
                                    @else
                                        <div class="rounded-2 bg-light border d-flex align-items-center justify-content-center flex-shrink-0 text-muted" style="width: 44px; height: 44px;">
                                            <i class="bi bi-box-seam fs-5"></i>
                                        </div>
                                    @endif
                                    <div class="min-w-0">
                                        <h6 class="mb-0.5 fw-bold text-dark text-truncate" style="font-size: 0.84rem;" title="{{ $item->product->name ?? 'Product' }}">
                                            {{ $item->product->name ?? 'Product' }}
                                        </h6>
                                        <div class="d-flex align-items-center gap-1.5 flex-wrap">
                                            <span class="badge bg-light text-dark border px-2 py-0.5" style="font-size: 0.65rem;">
                                                Qty: {{ $item->quantity }} @if(!empty($item->selected_option)) | {{ $item->selected_option }} @endif
                                            </span>
                                            @if($hasDiscount)
                                                <span class="text-success fw-bold" style="font-size: 0.68rem;">
                                                    ({{ round((($origPrice - $currPrice) / $origPrice) * 100) }}% OFF)
                                                </span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                                <div class="text-end flex-shrink-0">
                                    @if($hasDiscount)
                                        <div class="text-muted text-decoration-line-through font-monospace" style="font-size: 0.74rem;">
                                            ₹{{ number_format($origLineTotal, 2) }}
                                        </div>
                                    @endif
                                    <div class="fw-bold text-dark font-monospace" style="font-size: 0.88rem;">
                                        ₹{{ number_format($lineTotal, 2) }}
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- ====================================================== -->
        <!-- TAB 2: PAYMENT METHOD & PROMO COUPONS                  -->
        <!-- ====================================================== -->
        <div id="checkout-tab-2-view" class="row justify-content-center g-3 g-lg-4 d-none">
            <div class="col-lg-8">
                
                <!-- Confirmed Delivery Address Summary Strip -->
                <div class="p-3 rounded-4 border mb-3.5 bg-white shadow-xs d-flex align-items-center justify-content-between" style="border-color: #cbd5e1 !important;">
                    <div class="d-flex align-items-center gap-2.5 min-w-0">
                        <div class="rounded-circle text-success bg-success bg-opacity-10 d-flex align-items-center justify-content-center flex-shrink-0" style="width: 36px; height: 36px;">
                            <i class="bi bi-geo-alt-fill fs-6"></i>
                        </div>
                        <div class="min-w-0">
                            <span class="text-muted small d-block" style="font-size: 0.72rem; line-height: 1;">Delivering To:</span>
                            <strong class="text-dark d-block text-truncate" id="tab-2-address-summary-title" style="font-size: 0.88rem;"></strong>
                        </div>
                    </div>
                    <button type="button" class="btn btn-sm btn-outline-primary rounded-pill px-3 py-1 fw-bold flex-shrink-0" onclick="editStep1()" style="font-size: 0.76rem;">
                        <i class="bi bi-pencil me-1"></i> Edit Address
                    </button>
                </div>

                <!-- Payment Methods Card -->
                <div class="card border-0 shadow-xs rounded-4 overflow-hidden mb-3.5" style="background: #ffffff; border: 1.5px solid #4f46e5 !important;">
                    <div class="card-header bg-white py-3 px-3 px-md-4 border-bottom d-flex align-items-center justify-content-between">
                        <div class="d-flex align-items-center gap-2.5">
                            <div class="rounded-circle text-white d-flex align-items-center justify-content-center" style="width: 32px; height: 32px; background: #4f46e5;">
                                <i class="bi bi-credit-card-2-front-fill fs-6"></i>
                            </div>
                            <div>
                                <h6 class="fw-bold mb-0 text-dark" style="font-size: 0.95rem;">Select Payment Method</h6>
                                <small class="text-muted" style="font-size: 0.74rem;">Choose how you want to pay</small>
                            </div>
                        </div>
                    </div>

                    <div class="card-body p-3 p-md-4">
                        <!-- Wallet Switch Box -->
                        @if(isset($userWallet) && $userWallet->balance > 0 && (!isset($isWalletFrozen) || !$isWalletFrozen))
                        <div class="p-3 rounded-3 border bg-light mb-3" style="border-color: #a7f3d0 !important;">
                            <div class="d-flex align-items-center justify-content-between">
                                <div class="d-flex align-items-center gap-2.5">
                                    <div class="rounded-circle bg-success bg-opacity-10 p-2 d-flex align-items-center justify-content-center" style="width: 38px; height: 38px;">
                                        <i class="bi bi-wallet2 text-success fs-5"></i>
                                    </div>
                                    <div>
                                        <span class="fw-bold text-dark d-block" style="font-size: 0.88rem;">Use ShopCalm Wallet Balance</span>
                                        <small class="text-muted d-block" style="font-size: 0.75rem;">Available Balance: <strong class="text-success">₹{{ number_format($userWallet->balance, 2) }}</strong></small>
                                    </div>
                                </div>
                                <div class="form-check form-switch mb-0">
                                    <input class="form-check-input custom-wallet-switch" type="checkbox" role="switch" id="toggle-wallet-switch" {{ $useWallet ? 'checked' : '' }} style="cursor: pointer; width: 2.5em; height: 1.3em;">
                                </div>
                            </div>
                        </div>
                        @endif

                        <!-- 100% Wallet Paid Notice Banner -->
                        <div id="full-wallet-pay-notice" class="alert alert-success border-0 rounded-3 mb-3 p-3 shadow-xs {{ ($useWallet && $grandTotal <= 0) ? '' : 'd-none' }}" style="background: #dcfce7; color: #166534;">
                            <div class="d-flex align-items-center gap-2.5">
                                <i class="bi bi-shield-check fs-4 text-success flex-shrink-0"></i>
                                <div>
                                    <h6 class="fw-bold mb-0.5" style="font-size: 0.88rem;">100% Covered by ShopCalm Wallet!</h6>
                                    <small style="font-size: 0.76rem; line-height: 1.4;" class="d-block">Your wallet balance covers the full order amount (₹0.00 remaining to pay). Payment gateway selection is disabled.</small>
                                </div>
                            </div>
                        </div>

                        <!-- Payment Tiles Container -->
                        <div id="payment-tiles-container" style="{{ ($useWallet && $grandTotal <= 0) ? 'opacity: 0.45; pointer-events: none; filter: grayscale(1);' : '' }}">
                            <!-- Option 1: Razorpay Online Payment (Active) -->
                            <div class="payment-tile active-tile mb-2.5" onclick="document.getElementById('pay_online').checked = true; document.getElementById('pay_online').dispatchEvent(new Event('change'));">
                                <div class="d-flex align-items-start gap-2.5">
                                    <input class="form-check-input mt-1 flex-shrink-0" type="radio" name="payment_method" id="pay_online" value="online" checked form="checkout-form">
                                    <div class="flex-grow-1">
                                        <div class="d-flex align-items-center justify-content-between flex-wrap gap-1 mb-1">
                                            <label class="form-check-label fw-bold text-dark mb-0 cursor-pointer" style="font-size: 0.92rem;" for="pay_online">
                                                ⚡ UPI / Cards / NetBanking / PhonePe
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
                            <div class="payment-tile mb-3" onclick="document.getElementById('cod').checked = true; document.getElementById('cod').dispatchEvent(new Event('change'));">
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

                        <!-- Promo Coupons Box -->
                        <div class="p-3 rounded-3 border bg-light mb-3">
                            <h6 class="fw-bold mb-2 text-dark" style="font-size: 0.88rem;">
                                <i class="bi bi-tag-fill text-primary me-1.5"></i>Coupons & Offers
                            </h6>
                            <div id="coupon-status-wrapper">
                                @if($couponCode)
                                    <div class="p-2.5 rounded-3 shadow-xs d-flex align-items-center justify-content-between bg-white border" id="applied-coupon-box" style="border-color: #86efac !important;">
                                        <div class="d-flex align-items-center gap-2">
                                            <i class="bi bi-check-circle-fill text-success fs-5"></i>
                                            <div>
                                                <span class="font-monospace fw-bold text-success px-2 py-0.5 rounded" style="background: #dcfce7; border: 1px dashed #22c55e; font-size: 0.8rem;">{{ $couponCode }}</span>
                                                <small class="text-dark d-block mt-0.5 fw-semibold" style="font-size: 0.76rem;">Saved ₹{{ number_format($discountAmount, 2) }}</small>
                                            </div>
                                        </div>
                                        <button type="button" class="btn btn-sm btn-outline-danger rounded-pill px-2.5 py-0.5 fw-bold" id="btn-remove-coupon-ajax" onclick="handleRemoveCoupon()" style="font-size: 0.74rem;">Remove</button>
                                    </div>
                                @else
                                    <div class="d-flex gap-2">
                                        <input type="text" id="coupon_code_input" class="form-control rounded-3 font-monospace fw-bold" placeholder="ENTER COUPON CODE" style="text-transform: uppercase; font-size: 0.88rem;">
                                        <button type="button" class="btn btn-primary rounded-3 px-3.5 fw-bold flex-shrink-0" onclick="handleApplyCoupon()" style="background: #4f46e5; border: none; font-size: 0.84rem;">APPLY</button>
                                    </div>
                                    @if(isset($availableCoupons) && $availableCoupons->count() > 0)
                                        <div class="mt-2 text-center">
                                            <button type="button" class="btn btn-sm btn-link text-primary text-decoration-none fw-bold" data-bs-toggle="modal" data-bs-target="#couponsModal" style="font-size: 0.8rem;">
                                                <i class="bi bi-ticket-perforated-fill text-warning me-1"></i> View Available Coupons ({{ $availableCoupons->count() }})
                                            </button>
                                        </div>
                                    @endif
                                @endif
                            </div>
                        </div>

                        <!-- Step 2 CTA: Continue to Confirm -->
                        <button type="button" class="btn btn-primary btn-lg rounded-pill w-100 py-3 fw-bold shadow-xs text-white d-flex align-items-center justify-content-center gap-2" onclick="proceedToStep3()" style="background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%); border: none; font-size: 0.92rem; letter-spacing: 0.02em;">
                            <span>CONTINUE TO CONFIRMATION</span>
                            <i class="bi bi-arrow-right fs-5"></i>
                        </button>
                    </div>
                </div>

            </div>
        </div>

        <!-- ====================================================== -->
        <!-- TAB 3: CONFIRM ORDER & PLACE ORDER / PAY              -->
        <!-- ====================================================== -->
        <div id="checkout-tab-3-view" class="row justify-content-center g-3 g-lg-4 d-none">
            <div class="col-lg-7">
                
                <!-- Estimated Delivery Date Banner -->
                <div class="p-3 rounded-4 mb-3.5 d-flex align-items-center gap-3 bg-white border shadow-xs" style="border-color: #cbd5e1 !important;">
                    <div class="rounded-circle d-flex align-items-center justify-content-center text-primary flex-shrink-0" style="width: 42px; height: 42px; background: #eff6ff;">
                        <i class="bi bi-truck fs-4"></i>
                    </div>
                    <div>
                        <span class="fw-bold text-dark d-block" style="font-size: 0.92rem;" id="review-delivery-date-title">Estimated Delivery</span>
                        <small class="text-secondary d-block" id="review-delivery-date-text" style="font-size: 0.78rem;">Standard Dispatch within 24-48 hours</small>
                    </div>
                </div>

                <!-- Confirmed Shipping Address Card -->
                <div class="card border-0 shadow-xs rounded-4 overflow-hidden mb-3.5" style="background: #ffffff; border: 1px solid #e2e8f0 !important;">
                    <div class="card-header bg-white py-2.5 px-3 px-md-4 border-bottom d-flex align-items-center justify-content-between">
                        <span class="fw-bold text-dark small"><i class="bi bi-geo-alt-fill text-primary me-1.5"></i>1. Delivering To</span>
                        <button type="button" class="btn btn-sm btn-link text-primary text-decoration-none fw-bold p-0" onclick="editStep1()" style="font-size: 0.78rem;">Change</button>
                    </div>
                    <div class="card-body p-3 p-md-3.5">
                        <div class="fw-bold text-dark" id="review-address-name" style="font-size: 0.9rem;"></div>
                        <div class="text-secondary small" id="review-address-text" style="font-size: 0.83rem; line-height: 1.45;"></div>
                        <div class="small text-muted mt-1" id="review-address-phone" style="font-size: 0.78rem;"></div>
                    </div>
                </div>

                <!-- Confirmed Payment Mode Card -->
                <div class="card border-0 shadow-xs rounded-4 overflow-hidden mb-3.5" style="background: #ffffff; border: 1px solid #e2e8f0 !important;">
                    <div class="card-header bg-white py-2.5 px-3 px-md-4 border-bottom d-flex align-items-center justify-content-between">
                        <span class="fw-bold text-dark small"><i class="bi bi-credit-card-2-front-fill text-success me-1.5"></i>2. Payment Method</span>
                        <button type="button" class="btn btn-sm btn-link text-primary text-decoration-none fw-bold p-0" onclick="editStep2()" style="font-size: 0.78rem;">Change</button>
                    </div>
                    <div class="card-body p-3 p-md-3.5">
                        <div class="fw-bold text-dark" id="review-payment-mode-text" style="font-size: 0.9rem;"></div>
                    </div>
                </div>

                <!-- Order Items Breakdown -->
                <div class="card border-0 shadow-xs rounded-4 overflow-hidden mb-3.5" style="background: #ffffff; border: 1px solid #e2e8f0 !important;">
                    <div class="card-header bg-white py-2.5 px-3 px-md-4 border-bottom">
                        <span class="fw-bold text-dark small"><i class="bi bi-bag-check-fill text-primary me-1.5"></i>Order Details ({{ $cart->items->sum('quantity') }} items)</span>
                    </div>
                    <div class="card-body p-3 p-md-3.5">
                        <div class="checkout-items-wrapper" style="max-height: 220px; overflow-y: auto;">
                            @foreach($cart->items as $item)
                            @php
                                $origPrice = (float) ($item->product->price ?? $item->unit_price);
                                $currPrice = (float) $item->unit_price;
                                $origLineTotal = $item->quantity * $origPrice;
                                $lineTotal = $item->quantity * $currPrice;
                                $hasDiscount = ($origPrice > $currPrice);
                            @endphp
                            <div class="p-2.5 mb-2 bg-white rounded-3 border d-flex align-items-center justify-content-between gap-2.5" style="border-color: #f1f5f9 !important;">
                                <div class="d-flex align-items-center gap-2.5 min-w-0">
                                    @if($item->product && $item->product->main_image)
                                        <img src="{{ asset('storage/' . $item->product->main_image) }}" alt="{{ $item->product->name }}" class="rounded-2 border flex-shrink-0" style="width: 40px; height: 40px; object-fit: contain;">
                                    @else
                                        <div class="rounded-2 bg-light border d-flex align-items-center justify-content-center flex-shrink-0 text-muted" style="width: 40px; height: 40px;">
                                            <i class="bi bi-box-seam fs-6"></i>
                                        </div>
                                    @endif
                                    <div class="min-w-0">
                                        <h6 class="mb-0.5 fw-bold text-dark text-truncate" style="font-size: 0.82rem;" title="{{ $item->product->name ?? 'Product' }}">
                                            {{ $item->product->name ?? 'Product' }}
                                        </h6>
                                        <div class="d-flex align-items-center gap-1.5 flex-wrap">
                                            <span class="small text-muted" style="font-size: 0.72rem;">Qty: {{ $item->quantity }} @if(!empty($item->selected_option)) | {{ $item->selected_option }} @endif</span>
                                            @if($hasDiscount)
                                                <span class="text-success fw-bold" style="font-size: 0.65rem;">
                                                    ({{ round((($origPrice - $currPrice) / $origPrice) * 100) }}% OFF)
                                                </span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                                <div class="text-end flex-shrink-0">
                                    @if($hasDiscount)
                                        <div class="text-muted text-decoration-line-through font-monospace" style="font-size: 0.72rem;">
                                            ₹{{ number_format($origLineTotal, 2) }}
                                        </div>
                                    @endif
                                    <div class="fw-bold text-dark font-monospace small">₹{{ number_format($lineTotal, 2) }}</div>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                </div>

            </div>

            <!-- Right Column: Final Price Breakdown & Pay Button -->
            <div class="col-lg-5">
                <div class="card border-0 shadow-xs rounded-4 overflow-hidden" style="background: #ffffff; border: 1px solid #e2e8f0 !important;">
                    <div class="card-header bg-white py-3 px-3 px-md-4 border-bottom">
                        <h6 class="fw-bold mb-0 text-dark" style="font-size: 0.95rem;">Price Details</h6>
                    </div>
                    <div class="card-body p-3 p-md-4">
                        <div class="p-3 rounded-3 bg-light border mb-3" style="border-color: #e2e8f0 !important;">
                            <div class="price-row text-secondary">
                                <span>Total MRP:</span>
                                <span class="font-monospace text-dark fw-semibold">₹{{ number_format($totalMrp ?? $subtotal, 2) }}</span>
                            </div>
                            @if(isset($totalDiscount) && $totalDiscount > 0)
                            <div class="price-row text-success">
                                <span>Discount on MRP:</span>
                                <span class="font-monospace fw-bold">- ₹{{ number_format($totalDiscount, 2) }}</span>
                            </div>
                            @endif
                            <div class="price-row text-secondary">
                                <span>Cart Subtotal:</span>
                                <span class="font-monospace fw-semibold text-dark" id="summary-subtotal">₹{{ number_format($subtotal, 2) }}</span>
                            </div>
                            <div class="price-row text-success {{ $discountAmount > 0 ? '' : 'd-none' }}" id="summary-discount-row">
                                <span class="d-flex align-items-center gap-1 fw-semibold">
                                    <i class="bi bi-tag-fill"></i> <span id="summary-discount-label">Coupon ({{ $couponCode }})</span>
                                </span>
                                <span class="font-monospace fw-bold" id="summary-discount-val">- ₹{{ number_format($discountAmount, 2) }}</span>
                            </div>
                            <div class="price-row text-success {{ $walletDiscount > 0 ? '' : 'd-none' }}" id="summary-wallet-row">
                                <span class="d-flex align-items-center gap-1 fw-semibold">
                                    <i class="bi bi-wallet2"></i> <span>Wallet Credits</span>
                                </span>
                                <span class="font-monospace fw-bold" id="summary-wallet-val">- ₹{{ number_format($walletDiscount, 2) }}</span>
                            </div>
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
                            <div class="price-row text-secondary mb-1" id="summary-cod-fee-row">
                                <span>COD Handling Fee:</span>
                                <span id="summary-cod-fee-val">
                                    <span class="badge bg-success bg-opacity-10 text-success fw-bold rounded-pill px-2 py-0.5">₹0.00 (Prepaid Offer)</span>
                                </span>
                            </div>
                            <hr class="my-2.5" style="border-color: #cbd5e1;">
                            <div class="d-flex justify-content-between align-items-center pt-1">
                                <div>
                                    <span class="fw-bold text-dark fs-6 d-block">Grand Total:</span>
                                    <small class="text-muted" style="font-size: 0.7rem;"><i class="bi bi-shield-check text-success me-1"></i>Inclusive of all taxes</small>
                                </div>
                                <span class="fs-4 fw-bolder text-dark font-monospace" id="summary-grand-total">₹{{ number_format($grandTotal, 2) }}</span>
                            </div>
                        </div>

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

                        <div class="d-none d-md-grid mb-3">
                            <button type="submit" form="checkout-form" onclick="handleOrderSubmission(event)" class="btn btn-primary btn-lg rounded-pill btn-checkout-cta d-flex align-items-center justify-content-center gap-2 py-3 shadow-xs text-white" id="btn-place-order">
                                <span id="btn-place-order-text" class="fw-bold text-white fs-6">Pay Online</span>
                                <i class="bi bi-arrow-right fs-5 text-white"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
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
                <button type="submit" form="checkout-form" onclick="handleOrderSubmission(event)" class="btn btn-primary w-100 rounded-pill py-2.5 fw-bold text-nowrap shadow-xs btn-checkout-cta" id="mobile-btn-place-order" style="font-size: 0.84rem;">
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
                    <h5 class="modal-title fw-bolder text-dark mb-0" id="couponsModalLabel" style="font-size: 1rem;">Available Coupons</h5>
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
                            <span class="fw-bold text-dark small mb-2 d-block text-uppercase" style="font-size: 0.75rem; letter-spacing: 0.5px;">Applicable Coupons ({{ $eligibleCoupons->count() }})</span>
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
                                <button type="button" class="btn btn-sm btn-outline-primary rounded-pill px-3 py-0.5 flex-shrink-0" style="font-size: 0.76rem;">Select Address</button>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
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
window.currentCheckoutStep = 1;

document.addEventListener('DOMContentLoaded', function() {
    if (currentSelectedAddress) {
        applySelectedAddress(currentSelectedAddress);
    }

    const checkoutForm = document.getElementById('checkout-form');

    window.updatePaymentButtonLabel = function() {
        const placeOrderBtn = document.getElementById('btn-place-order');
        const mobPlaceOrderBtn = document.getElementById('mobile-btn-place-order');

        const selectedVal = document.querySelector('input[name="payment_method"]:checked')?.value;
        const isOnline = (selectedVal === 'online');
        const grandTotalRaw = document.getElementById('summary-grand-total')?.textContent.replace(/[^0-9.]/g, '') || '0';
        const grandTotalVal = parseFloat(grandTotalRaw);
        const grandTotalFmt = document.getElementById('summary-grand-total')?.textContent || '₹{{ number_format($grandTotal, 2) }}';

        const mobGrandEl = document.getElementById('mobile-summary-grand-total');
        if (mobGrandEl) mobGrandEl.textContent = grandTotalFmt;

        let desktopText = `Place COD Order (${grandTotalFmt})`;
        let mobText = `Place COD Order (${grandTotalFmt})`;

        if (grandTotalVal <= 0) {
            desktopText = `Confirm & Place Order (Wallet Paid)`;
            mobText = `Place Order (Wallet)`;
        } else if (isOnline) {
            desktopText = `Pay Online ${grandTotalFmt}`;
            mobText = `Pay Online (${grandTotalFmt})`;
        }

        if (placeOrderBtn) {
            placeOrderBtn.disabled = false;
            placeOrderBtn.innerHTML = `<span id="btn-place-order-text" class="fw-bold text-white fs-6">${desktopText}</span><i class="bi bi-arrow-right fs-5 text-white"></i>`;
        }

        if (mobPlaceOrderBtn) {
            mobPlaceOrderBtn.disabled = false;
            mobPlaceOrderBtn.innerHTML = `<span id="mobile-btn-place-order-text">${mobText}</span> <i class="bi bi-arrow-right ms-1"></i>`;
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

    window.handleOrderSubmission = function(e) {
        if (e) e.preventDefault();

        if (window.currentCheckoutStep === 1) {
            proceedToStep2();
            return;
        }
        if (window.currentCheckoutStep === 2) {
            proceedToStep3();
            return;
        }

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

        if (!document.getElementById('hidden_shipping_address').value || window.isCurrentAddressServiceable === false) {
            Swal.fire('Invalid Address', 'Please select or add a valid delivery address before proceeding.', 'error');
            editStep1();
            return;
        }

        const placeOrderBtn = document.getElementById('btn-place-order');
        const mobPlaceOrderBtn = document.getElementById('mobile-btn-place-order');

        if (placeOrderBtn) {
            placeOrderBtn.disabled = true;
            placeOrderBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span> Processing...';
        }
        if (mobPlaceOrderBtn) {
            mobPlaceOrderBtn.disabled = true;
            mobPlaceOrderBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Processing...';
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
            const data = await res.json();
            if (!res.ok || !data.success) {
                throw new Error(data.message || 'Could not place order. Please try again.');
            }
            return data;
        })
        .then(data => {
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
                        if (mobPlaceOrderBtn) mobPlaceOrderBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Verifying Payment...';
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
                                window.updatePaymentButtonLabel();
                                Swal.fire('Verification Failed', verifyData.message || 'Payment signature error.', 'error');
                            }
                        })
                        .catch(err => {
                            console.error("Verification error:", err);
                            window.updatePaymentButtonLabel();
                            Swal.fire('Error', 'Payment verification error.', 'error');
                        });
                    },
                    modal: {
                        ondismiss: function() {
                            window.updatePaymentButtonLabel();
                        }
                    }
                };

                try {
                    const rzp = new Razorpay(options);
                    rzp.on('payment.failed', function(resp) {
                        window.updatePaymentButtonLabel();
                        Swal.fire({
                            icon: 'error',
                            title: 'Payment Failed',
                            text: (resp && resp.error) ? resp.error.description : 'Payment authorization failed.'
                        });
                    });
                    rzp.open();
                } catch (rzpErr) {
                    console.error("Razorpay open error:", rzpErr);
                    window.updatePaymentButtonLabel();
                    Swal.fire({
                        icon: 'error',
                        title: 'Gateway Notice',
                        text: rzpErr.message || 'Could not open Razorpay modal.'
                    });
                }
            }
        })
        .catch(err => {
            console.error("Place Order Error:", err);
            window.updatePaymentButtonLabel();
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

    window.addEventListener('pageshow', function() {
        updatePaymentButtonLabel();
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
                        updatePaymentButtonLabel();
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
        let codFee = (isCod && !isFullyCoveredByWallet) ? parseFloat("{{ \App\Models\Setting::get('cod_extra_charge', 40) }}") : 0;

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

        const fullWalletNotice = document.getElementById('full-wallet-pay-notice');
        const paymentContainer = document.getElementById('payment-tiles-container');

        if (isFullyCoveredByWallet || calculatedGrandTotal <= 0) {
            if (fullWalletNotice) fullWalletNotice.classList.remove('d-none');
            if (paymentContainer) {
                paymentContainer.style.opacity = '0.45';
                paymentContainer.style.pointerEvents = 'none';
                paymentContainer.style.filter = 'grayscale(1)';
            }
        } else {
            if (fullWalletNotice) fullWalletNotice.classList.add('d-none');
            if (paymentContainer) {
                paymentContainer.style.opacity = '1';
                paymentContainer.style.pointerEvents = 'auto';
                paymentContainer.style.filter = 'none';
            }
        }

        window.updatePaymentButtonLabel();
    };
});

// Clean Step Switchers
function proceedToStep2() {
    if (!currentSelectedAddress || !document.getElementById('hidden_shipping_address').value) {
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

    window.currentCheckoutStep = 2;

    // Show Tab 2, Hide Tab 1 & Tab 3
    document.getElementById('checkout-tab-1-view').classList.add('d-none');
    document.getElementById('checkout-tab-2-view').classList.remove('d-none');
    document.getElementById('checkout-tab-3-view').classList.add('d-none');

    // Update Tab 2 Address Summary Strip
    document.getElementById('tab-2-address-summary-title').textContent = `${currentSelectedAddress.name} — ${currentSelectedAddress.address}, ${currentSelectedAddress.city} (${currentSelectedAddress.zip})`;

    // Update Top Stepper Progress (35%)
    const progressLine = document.getElementById('stepper-progress-line');
    if (progressLine) progressLine.style.width = '35%';

    const stBadge1 = document.getElementById('stepper-badge-1');
    if (stBadge1) {
        stBadge1.style.background = '#10b981';
        stBadge1.style.boxShadow = 'none';
        stBadge1.innerHTML = '<i class="bi bi-check-lg"></i>';
    }

    const stBadge2 = document.getElementById('stepper-badge-2');
    if (stBadge2) {
        stBadge2.classList.remove('bg-light', 'text-muted', 'border');
        stBadge2.classList.add('text-white', 'shadow-xs');
        stBadge2.style.background = '#4f46e5';
        stBadge2.style.boxShadow = '0 0 0 4px rgba(79, 70, 229, 0.2)';
    }
    const stText2 = document.getElementById('stepper-text-2');
    if (stText2) {
        stText2.classList.remove('text-muted');
        stText2.classList.add('text-primary', 'fw-bold');
    }

    window.updatePaymentButtonLabel();
    window.scrollTo({ top: 0, behavior: 'smooth' });
}

function proceedToStep3() {
    window.currentCheckoutStep = 3;

    const selectedPayMethod = document.querySelector('input[name="payment_method"]:checked')?.value || 'online';
    const grandTotalRaw = document.getElementById('summary-grand-total')?.textContent.replace(/[^0-9.]/g, '') || '0';
    const grandTotalVal = parseFloat(grandTotalRaw);

    let payTitle = '';
    if (grandTotalVal <= 0) {
        payTitle = '💳 ShopCalm Wallet (100% Paid — ₹0.00 Remaining)';
    } else {
        payTitle = selectedPayMethod === 'online' ? '⚡ UPI / Cards / NetBanking / PhonePe' : '💵 Cash on Delivery (COD)';
        const walletVal = document.getElementById('summary-wallet-row')?.classList.contains('d-none') ? 0 : parseFloat(document.getElementById('summary-wallet-val')?.textContent.replace(/[^0-9.]/g, '') || 0);
        if (walletVal > 0) {
            payTitle += ` (Partial Wallet Used: ₹${walletVal.toFixed(2)})`;
        }
    }

    // Show Tab 3, Hide Tab 1 & Tab 2
    document.getElementById('checkout-tab-1-view').classList.add('d-none');
    document.getElementById('checkout-tab-2-view').classList.add('d-none');
    document.getElementById('checkout-tab-3-view').classList.remove('d-none');

    // Populate Tab 3 Summary Details
    if (currentSelectedAddress) {
        document.getElementById('review-address-name').textContent = currentSelectedAddress.name;
        document.getElementById('review-address-text').textContent = `${currentSelectedAddress.address}, ${currentSelectedAddress.city}, ${currentSelectedAddress.state} - ${currentSelectedAddress.zip}`;
        document.getElementById('review-address-phone').textContent = `Mobile: ${currentSelectedAddress.phone}`;
    }
    
    const reviewPayEl = document.getElementById('review-payment-mode-text');
    if (reviewPayEl) {
        if (grandTotalVal <= 0) {
            reviewPayEl.innerHTML = `<span class="badge bg-success text-white px-2.5 py-1 rounded-pill fw-bold me-1.5" style="font-size: 0.76rem;">✓ 100% WALLET PAID</span> <span class="fw-bold text-dark">${payTitle}</span>`;
        } else {
            reviewPayEl.textContent = payTitle;
        }
    }

    // Update Top Stepper Progress (80%)
    const progressLine = document.getElementById('stepper-progress-line');
    if (progressLine) progressLine.style.width = '80%';

    const stBadge2 = document.getElementById('stepper-badge-2');
    if (stBadge2) {
        stBadge2.style.background = '#10b981';
        stBadge2.style.boxShadow = 'none';
        stBadge2.innerHTML = '<i class="bi bi-check-lg"></i>';
    }

    const stBadge3 = document.getElementById('stepper-badge-3');
    if (stBadge3) {
        stBadge3.classList.remove('bg-light', 'text-muted', 'border');
        stBadge3.classList.add('text-white', 'shadow-xs');
        stBadge3.style.background = '#4f46e5';
        stBadge3.style.boxShadow = '0 0 0 4px rgba(79, 70, 229, 0.2)';
    }
    const stText3 = document.getElementById('stepper-text-3');
    if (stText3) {
        stText3.classList.remove('text-muted');
        stText3.classList.add('text-primary', 'fw-bold');
    }

    window.updatePaymentButtonLabel();
    window.scrollTo({ top: 0, behavior: 'smooth' });
}

function editStep1() {
    window.currentCheckoutStep = 1;

    document.getElementById('checkout-tab-1-view').classList.remove('d-none');
    document.getElementById('checkout-tab-2-view').classList.add('d-none');
    document.getElementById('checkout-tab-3-view').classList.add('d-none');

    const progressLine = document.getElementById('stepper-progress-line');
    if (progressLine) progressLine.style.width = '0%';

    const stBadge1 = document.getElementById('stepper-badge-1');
    if (stBadge1) {
        stBadge1.style.background = '#4f46e5';
        stBadge1.style.boxShadow = '0 0 0 4px rgba(79, 70, 229, 0.2)';
        stBadge1.innerHTML = '1';
    }

    const stBadge2 = document.getElementById('stepper-badge-2');
    if (stBadge2) {
        stBadge2.classList.add('bg-light', 'text-muted', 'border');
        stBadge2.classList.remove('text-white', 'shadow-xs');
        stBadge2.style.background = '';
        stBadge2.style.boxShadow = '';
        stBadge2.innerHTML = '2';
    }
    const stText2 = document.getElementById('stepper-text-2');
    if (stText2) {
        stText2.classList.add('text-muted');
        stText2.classList.remove('text-primary', 'fw-bold');
    }

    const stBadge3 = document.getElementById('stepper-badge-3');
    if (stBadge3) {
        stBadge3.classList.add('bg-light', 'text-muted', 'border');
        stBadge3.classList.remove('text-white', 'shadow-xs');
        stBadge3.style.background = '';
        stBadge3.style.boxShadow = '';
        stBadge3.innerHTML = '3';
    }
    const stText3 = document.getElementById('stepper-text-3');
    if (stText3) {
        stText3.classList.add('text-muted');
        stText3.classList.remove('text-primary', 'fw-bold');
    }

    window.updatePaymentButtonLabel();
    window.scrollTo({ top: 0, behavior: 'smooth' });
}

function editStep2() {
    if (!currentSelectedAddress || window.isCurrentAddressServiceable === false) {
        editStep1();
        return;
    }

    window.currentCheckoutStep = 2;

    document.getElementById('checkout-tab-1-view').classList.add('d-none');
    document.getElementById('checkout-tab-2-view').classList.remove('d-none');
    document.getElementById('checkout-tab-3-view').classList.add('d-none');

    const progressLine = document.getElementById('stepper-progress-line');
    if (progressLine) progressLine.style.width = '35%';

    const stBadge2 = document.getElementById('stepper-badge-2');
    if (stBadge2) {
        stBadge2.style.background = '#4f46e5';
        stBadge2.style.boxShadow = '0 0 0 4px rgba(79, 70, 229, 0.2)';
        stBadge2.innerHTML = '2';
    }

    const stBadge3 = document.getElementById('stepper-badge-3');
    if (stBadge3) {
        stBadge3.classList.add('bg-light', 'text-muted', 'border');
        stBadge3.classList.remove('text-white', 'shadow-xs');
        stBadge3.style.background = '';
        stBadge3.style.boxShadow = '';
        stBadge3.innerHTML = '3';
    }
    const stText3 = document.getElementById('stepper-text-3');
    if (stText3) {
        stText3.classList.add('text-muted');
        stText3.classList.remove('text-primary', 'fw-bold');
    }

    window.updatePaymentButtonLabel();
    window.scrollTo({ top: 0, behavior: 'smooth' });
}

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
                    <div class="p-2.5 rounded-3 shadow-xs d-flex align-items-center justify-content-between bg-white border" id="applied-coupon-box" style="border-color: #86efac !important;">
                        <div class="d-flex align-items-center gap-2">
                            <i class="bi bi-check-circle-fill text-success fs-5"></i>
                            <div>
                                <span class="font-monospace fw-bold text-success px-2 py-0.5 rounded" style="background: #dcfce7; border: 1px dashed #22c55e; font-size: 0.8rem;">${data.coupon_code}</span>
                                <small class="text-dark d-block mt-0.5 fw-semibold" style="font-size: 0.76rem;">Saved ₹${parseFloat(data.discount_amount).toFixed(2)}</small>
                            </div>
                        </div>
                        <button type="button" class="btn btn-sm btn-outline-danger rounded-pill px-2.5 py-0.5 fw-bold" id="btn-remove-coupon-ajax" onclick="handleRemoveCoupon()" style="font-size: 0.74rem;">Remove</button>
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
                updatePaymentButtonLabel();
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
                        <div class="p-2.5 rounded-3 shadow-xs d-flex align-items-center justify-content-between bg-white border" id="applied-coupon-box" style="border-color: #86efac !important;">
                            <div class="d-flex align-items-center gap-2">
                                <i class="bi bi-check-circle-fill text-success fs-5"></i>
                                <div>
                                    <span class="font-monospace fw-bold text-success px-2 py-0.5 rounded" style="background: #dcfce7; border: 1px dashed #22c55e; font-size: 0.8rem;">${data.coupon_code}</span>
                                    <small class="text-dark d-block mt-0.5 fw-semibold" style="font-size: 0.76rem;">Saved ₹${parseFloat(data.discount_amount).toFixed(2)}</small>
                                </div>
                            </div>
                            <button type="button" class="btn btn-sm btn-outline-danger rounded-pill px-2.5 py-0.5 fw-bold" id="btn-remove-coupon-ajax" onclick="handleRemoveCoupon()" style="font-size: 0.74rem;">Remove</button>
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
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Removing...';
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
            const availableCount = {{ isset($availableCoupons) ? $availableCoupons->count() : 0 }};
            let availBtnHtml = '';
            if (availableCount > 0) {
                availBtnHtml = `
                    <div class="mt-2 text-center" id="view-available-coupons-wrapper">
                        <button type="button" class="btn btn-sm btn-link text-primary text-decoration-none fw-bold" data-bs-toggle="modal" data-bs-target="#couponsModal" style="font-size: 0.8rem;">
                            <i class="bi bi-ticket-perforated-fill text-warning me-1"></i> View Available Coupons (${availableCount})
                        </button>
                    </div>
                `;
            }

            if (couponContainer) {
                couponContainer.innerHTML = `
                    <div class="d-flex gap-2">
                        <input type="text" id="coupon_code_input" class="form-control rounded-3 font-monospace fw-bold" placeholder="ENTER COUPON CODE" style="text-transform: uppercase; font-size: 0.88rem;">
                        <button type="button" class="btn btn-primary rounded-3 px-3.5 fw-bold flex-shrink-0" onclick="handleApplyCoupon()" style="background: #4f46e5; border: none; font-size: 0.84rem;">APPLY</button>
                    </div>
                    ${availBtnHtml}
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
                updatePaymentButtonLabel();
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
    if (displayStreetCity) displayStreetCity.textContent = `${addr.address}, ${addr.city}, ${addr.state} - ${addr.zip}`;
    if (displayPhone) displayPhone.innerHTML = `<i class="bi bi-telephone-fill text-muted me-1 small"></i> Mobile: <strong class="text-dark">${addr.phone}</strong>`;
    
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
    const modalEl = document.getElementById('addressSelectorModal');
    if (modalEl) {
        const modalInstance = new bootstrap.Modal(modalEl);
        modalInstance.show();
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
        }
    }
}

function closeNewAddressForm() {
    const card = document.getElementById('new-address-card');
    if (card) {
        card.style.display = 'none';
    }
}

function handleSaveAddress(e) {
    e.preventDefault();
    const btn = document.getElementById('btn-save-address');
    const origText = btn ? btn.innerHTML : 'Save Address';
    if (btn) {
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Saving...';
    }

    const payload = {
        name: document.getElementById('new_name').value,
        phone: document.getElementById('new_phone').value,
        zip: document.getElementById('new_zip').value,
        address: document.getElementById('new_address').value,
        city: document.getElementById('new_city').value,
        state: document.getElementById('new_state').value,
        country: 'India',
        is_default: true
    };

    fetch('{{ route("account.addresses.store") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json'
        },
        body: JSON.stringify(payload)
    })
    .then(res => res.json())
    .then(data => {
        if (btn) {
            btn.disabled = false;
            btn.innerHTML = origText;
        }
        if (data.success || data.address) {
            const savedAddr = data.address || payload;
            userAddresses.unshift(savedAddr);
            applySelectedAddress(savedAddr);
            closeNewAddressForm();
            
            const successBanner = document.getElementById('address-success-banner');
            if (successBanner) {
                successBanner.style.display = 'block';
                setTimeout(() => { successBanner.style.display = 'none'; }, 3000);
            }

            proceedToStep2();
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
        applySelectedAddress(payload);
        closeNewAddressForm();
        proceedToStep2();
    });
}

function checkPincodeServiceability(pincode) {
    const badgeEl = document.getElementById('active-address-serviceability-badge');
    const confirmBtn = document.getElementById('btn-confirm-address');
    const errorBanner = document.getElementById('address-error-banner');

    if (!pincode) return;

    if (badgeEl) {
        badgeEl.innerHTML = `<span class="badge bg-light text-muted border font-monospace"><span class="spinner-border spinner-border-sm me-1" style="width:0.6rem;height:0.6rem;"></span> Checking delivery to ${pincode}...</span>`;
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
            if (errorBanner) errorBanner.style.display = 'none';

            if (badgeEl) {
                badgeEl.innerHTML = `
                    <div class="d-flex align-items-center gap-2 flex-wrap">
                        <span class="badge bg-success bg-opacity-10 text-success fw-bold rounded-pill px-2.5 py-1" style="font-size: 0.72rem;">
                            <i class="bi bi-truck me-1"></i> Delivery Available (${data.delivery_charge > 0 ? '₹' + data.delivery_charge : 'FREE'})
                        </span>
                        ${data.estimated_delivery ? `<span class="small text-muted" style="font-size: 0.75rem;"><i class="bi bi-clock me-1"></i> Est: ${data.estimated_delivery}</span>` : ''}
                    </div>
                `;

                const revDateTitle = document.getElementById('review-delivery-date-title');
                const revDateText = document.getElementById('review-delivery-date-text');
                if (revDateTitle) revDateTitle.textContent = `Estimated Delivery: ${data.estimated_delivery}`;
                if (revDateText) revDateText.textContent = `Delivering to ${data.city} (${data.pincode})`;
            }

            if (confirmBtn) {
                confirmBtn.disabled = false;
                confirmBtn.classList.remove('btn-secondary');
                confirmBtn.classList.add('btn-primary');
                confirmBtn.style.background = 'linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%)';
                confirmBtn.innerHTML = `<span>DELIVER TO THIS ADDRESS</span><i class="bi bi-arrow-right fs-5"></i>`;
            }

            if (typeof updateShippingFeeAndTotal === 'function') {
                updateShippingFeeAndTotal(data.delivery_charge || 0, data.free_shipping_min || 499);
            }
        } else {
            window.isCurrentAddressServiceable = false;
            
            if (badgeEl) {
                badgeEl.innerHTML = `
                    <span class="badge bg-danger bg-opacity-10 text-danger border border-danger rounded-pill px-2.5 py-1" style="font-size: 0.72rem;">
                        <i class="bi bi-x-circle-fill text-danger me-1"></i> Delivery Unavailable (${pincode})
                    </span>
                `;
            }

            if (errorBanner) {
                errorBanner.className = 'alert alert-danger small mb-3 rounded-3 border-0 bg-danger-subtle text-danger';
                errorBanner.innerHTML = `<i class="bi bi-exclamation-octagon-fill me-2"></i><strong>Delivery Unavailable:</strong> We currently do not deliver to PIN code <strong>${pincode}</strong>. Please select or add a different address.`;
                errorBanner.style.display = 'block';
            }

            if (confirmBtn) {
                confirmBtn.disabled = true;
                confirmBtn.classList.remove('btn-primary');
                confirmBtn.classList.add('btn-secondary');
                confirmBtn.style.background = '#94a3b8';
                confirmBtn.innerHTML = `<span>DELIVERY NOT AVAILABLE TO THIS PINCODE</span><i class="bi bi-slash-circle fs-5 ms-1"></i>`;
            }
        }
    })
    .catch(err => {
        console.error("Serviceability check error:", err);
    });
}
</script>
@endpush