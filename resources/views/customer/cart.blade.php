@extends('layouts.customer')

@section('title', 'Your Shopping Bag - ' . \App\Models\Setting::get('store_name', 'ShopCalm'))

@section('content')
<div class="container my-3 my-md-4 px-2 px-md-4">

    {{-- 1. Breadcrumbs --}}
    <nav aria-label="breadcrumb" class="mb-3">
        <ol class="breadcrumb small mb-0" style="font-size: 0.82rem;">
            <li class="breadcrumb-item"><a href="{{ route('home') }}" class="text-decoration-none text-muted">Home</a></li>
            <li class="breadcrumb-item"><a href="{{ route('shop') }}" class="text-decoration-none text-muted">Shop</a></li>
            <li class="breadcrumb-item active text-dark fw-semibold" aria-current="page">Shopping Bag</li>
        </ol>
    </nav>

    @if ($cart && $cart->items->filter(fn($i) => $i->product)->count() > 0)
        @php
            $activeItems = $cart->items->filter(fn($i) => $i->product);
            $totalCount = $activeItems->sum('quantity');
            $selectedCount = $activeItems->where('is_selected', true)->count();
            $allSelected = $activeItems->count() > 0 && $selectedCount === $activeItems->count();
            $freeShippingThreshold = (float) \App\Models\Setting::get('free_shipping_min', 499);
            $currentSubtotal = $subtotal ?? 0;
            $freeShippingProgress = min(100, ($currentSubtotal / $freeShippingThreshold) * 100);
            $amountNeeded = max(0, $freeShippingThreshold - $currentSubtotal);
        @endphp

        {{-- 2. Page Header Bar --}}
        <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-3 mb-md-4 pb-1">
            <div class="d-flex align-items-center gap-2.5">
                <div class="rounded-3 d-flex align-items-center justify-content-center flex-shrink-0" 
                     style="width: 42px; height: 42px; background: rgba(99, 102, 241, 0.12); color: #4f46e5;">
                    <i class="bi bi-bag-check-fill fs-5"></i>
                </div>
                <div>
                    <div class="d-flex align-items-center gap-2">
                        <h1 class="h4 fw-bold text-dark mb-0" style="font-size: clamp(1.2rem, 2.5vw, 1.5rem); letter-spacing: -0.01em;">
                            Shopping Bag
                        </h1>
                        <span class="badge rounded-pill fw-semibold" id="cart-page-badge" 
                              style="background: #e0e7ff; color: #4338ca; font-size: 0.76rem; border: 1px solid #c7d2fe;">
                            {{ $totalCount }} {{ Str::plural('item', $totalCount) }}
                        </span>
                    </div>
                </div>
            </div>

            <div class="d-flex align-items-center gap-2 flex-wrap">
                <div class="form-check mb-0 d-flex align-items-center gap-2 bg-light border rounded-pill px-3 py-1.5 shadow-xs">
                    <input type="checkbox" id="select-all-checkbox" class="form-check-input border-secondary" style="width: 1.15em; height: 1.15em; cursor: pointer;" {{ $allSelected ? 'checked' : '' }} onchange="toggleSelectAllItems(this.checked)">
                    <label class="form-check-label text-dark fw-bold small mb-0 text-nowrap" for="select-all-checkbox" style="cursor: pointer; font-size: 0.8rem;">
                        Select All (<span id="selected-items-label">{{ $selectedCount }}</span>/{{ $activeItems->count() }})
                    </label>
                </div>
                <a href="{{ route('shop') }}" class="btn btn-sm btn-light border rounded-pill px-3 py-1.5 fw-semibold text-secondary shadow-xs" style="font-size: 0.8rem;">
                    <i class="bi bi-arrow-left me-1"></i> Continue Shopping
                </a>
                <button type="button" class="btn btn-sm btn-outline-danger rounded-pill px-3 py-1.5 fw-semibold shadow-xs" style="font-size: 0.8rem;" onclick="clearCart()">
                    <i class="bi bi-trash3 me-1"></i> Clear Bag
                </button>
            </div>
        </div>

        {{-- 3. Main Content: Items List + Order Summary --}}
        <div class="row g-3 g-lg-4">
            
            {{-- Left Column: Cart Items --}}
            <div class="col-lg-8">
                
                {{-- Dynamic Free Shipping Progress Banner --}}
                <div class="card border-0 shadow-xs rounded-4 mb-3 overflow-hidden" id="free-shipping-banner" style="background: #ffffff; border: 1px solid #e2e8f0 !important;">
                    <div class="card-body p-3 p-md-3.5">
                        <div class="d-flex align-items-center justify-content-between mb-2">
                            <div class="d-flex align-items-center gap-2">
                                <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0" id="fs-icon-container"
                                     style="width: 34px; height: 34px; background: {{ $amountNeeded <= 0 ? 'rgba(16, 185, 129, 0.12)' : 'rgba(99, 102, 241, 0.12)' }}; color: {{ $amountNeeded <= 0 ? '#059669' : '#4f46e5' }};">
                                    <i class="bi {{ $amountNeeded <= 0 ? 'bi-truck-flatbed' : 'bi-truck' }} fs-6"></i>
                                </div>
                                <div>
                                    <span class="fw-bold text-dark d-block" id="fs-title-text" style="font-size: 0.88rem;">
                                        @if($amountNeeded <= 0)
                                            🎉 You've unlocked <span class="text-success fw-bolder">FREE Delivery</span> on this order!
                                        @else
                                            Add <span class="text-primary fw-bolder" id="fs-amount-needed">₹{{ number_format($amountNeeded, 2) }}</span> more to unlock <span class="fw-bolder">FREE Delivery!</span>
                                        @endif
                                    </span>
                                </div>
                            </div>
                            <span class="badge rounded-pill fw-bold" id="fs-badge" style="background: {{ $amountNeeded <= 0 ? '#d1fae5' : '#e0e7ff' }}; color: {{ $amountNeeded <= 0 ? '#047857' : '#4338ca' }}; font-size: 0.72rem;">
                                {{ $amountNeeded <= 0 ? 'UNLOCKED' : round($freeShippingProgress) . '%' }}
                            </span>
                        </div>
                        <div class="progress rounded-pill bg-light overflow-hidden" style="height: 8px;">
                            <div class="progress-bar progress-bar-striped progress-bar-animated" id="fs-progress-bar"
                                 role="progressbar" 
                                 style="width: {{ $freeShippingProgress }}%; background: {{ $amountNeeded <= 0 ? 'linear-gradient(90deg, #10b981 0%, #059669 100%)' : 'linear-gradient(90deg, #6366f1 0%, #4f46e5 100%)' }};" 
                                 aria-valuenow="{{ $freeShippingProgress }}" aria-valuemin="0" aria-valuemax="100">
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Modern Cart Items List (Clean Unified Cards for Laptop & Mobile) --}}
                <div class="d-flex flex-column gap-3 mb-3">
                    @foreach($activeItems as $item)
                    @php
                        $hasDiscount = $item->product->price > $item->unit_price;
                        $discountPct = $hasDiscount ? round((($item->product->price - $item->unit_price) / $item->product->price) * 100) : 0;
                    @endphp
                    <div class="card border-0 shadow-xs rounded-4 overflow-hidden cart-item-card transition-all" id="cart-item-{{ $item->id }}" style="background: #ffffff; border: 1px solid #e2e8f0 !important; {{ !$item->is_selected ? 'opacity: 0.6; filter: grayscale(35%);' : '' }}">
                        <div class="card-body p-3 p-md-3.5">
                            <div class="row align-items-center g-3">
                                
                                {{-- 1. Checkbox + Thumbnail + Info --}}
                                <div class="col-12 col-md-6 d-flex align-items-center gap-2.5 min-w-0">
                                    <div class="form-check mb-0 flex-shrink-0">
                                        <input type="checkbox" class="form-check-input item-select-checkbox" 
                                               id="checkbox-item-{{ $item->id }}" 
                                               data-item-id="{{ $item->id }}" 
                                               style="width: 1.25em; height: 1.25em; cursor: pointer;" 
                                               {{ $item->is_selected ? 'checked' : '' }} 
                                               onchange="toggleSelectItem({{ $item->id }}, this.checked)">
                                    </div>
                                    <a href="{{ route('product.show', $item->product->slug) }}" class="flex-shrink-0 position-relative">
                                        @if($item->product->main_image)
                                            <img src="{{ asset('storage/' . $item->product->main_image) }}" 
                                                 class="rounded-3 border p-1 bg-white shadow-xs" 
                                                 width="84" height="84" style="object-fit: contain;">
                                        @else
                                            <div class="rounded-3 border bg-light d-flex align-items-center justify-content-center text-muted" style="width: 84px; height: 84px;">
                                                <i class="bi bi-image fs-3 opacity-25"></i>
                                            </div>
                                        @endif
                                    </a>
                                    <div class="min-w-0 flex-grow-1">
                                        @if($item->product->brand)
                                            <span class="badge bg-light text-secondary border rounded-pill px-2 py-0.5 mb-1" style="font-size: 0.68rem;">
                                                {{ $item->product->brand->name }}
                                            </span>
                                        @endif
                                        <a href="{{ route('product.show', $item->product->slug) }}" class="text-dark fw-bold text-decoration-none d-block mb-1 text-truncate" style="font-size: 0.94rem; line-height: 1.35;" title="{{ $item->product->name }}">
                                            {{ $item->product->name }}
                                        </a>

                                        @if(!empty($item->selected_option))
                                            <div class="mb-1">
                                                <span class="badge bg-light text-dark border rounded-2 px-2 py-0.5 font-monospace fw-normal" style="font-size: 0.72rem; background-color: #f8fafc !important;">
                                                    <i class="bi bi-sliders me-1 text-primary"></i>Variant: <strong>{{ $item->selected_option }}</strong>
                                                </span>
                                            </div>
                                        @endif

                                        <div class="d-flex align-items-baseline gap-2 flex-wrap">
                                            <span class="fw-bolder text-dark" style="font-size: 0.95rem;">₹{{ number_format($item->unit_price, 2) }}</span>
                                            @if($hasDiscount)
                                                <span class="text-muted text-decoration-line-through small" style="font-size: 0.76rem;">₹{{ number_format($item->product->price, 2) }}</span>
                                                <span class="badge bg-success bg-opacity-10 text-success fw-bold px-1.5 py-0.5" style="font-size: 0.65rem;">-{{ $discountPct }}%</span>
                                            @endif
                                        </div>
                                    </div>
                                </div>

                                {{-- 2. Quantity Stepper --}}
                                <div class="col-6 col-md-3 d-flex align-items-center justify-content-start justify-content-md-center">
                                    @php $maxStock = $item->product->getOptionStock($item->selected_option); @endphp
                                    <div>
                                        <div class="input-group input-group-sm border rounded-pill overflow-hidden shadow-xs bg-white" style="width: 110px;">
                                            <button type="button" class="btn btn-light border-0 shadow-none px-2.5 py-1 text-muted" onclick="updateQty({{ $item->id }}, -1)">
                                                <i class="bi bi-dash"></i>
                                            </button>
                                            <input type="number" id="qty-{{ $item->id }}" value="{{ $item->quantity }}" min="1" max="{{ $maxStock }}" class="form-control text-center border-0 fw-bold shadow-none bg-white p-0" readonly style="font-size: 0.85rem;">
                                            <button type="button" class="btn btn-light border-0 shadow-none px-2.5 py-1 text-muted" onclick="updateQty({{ $item->id }}, 1)">
                                                <i class="bi bi-plus"></i>
                                            </button>
                                        </div>
                                        @if($maxStock <= 5 && $maxStock > 0)
                                            <small class="text-danger d-block text-center mt-1 fw-semibold" style="font-size: 0.7rem;">Only {{ $maxStock }} left</small>
                                        @elseif($maxStock <= 0)
                                            <small class="text-danger d-block text-center mt-1 fw-bold" style="font-size: 0.7rem;">Out of stock</small>
                                        @endif
                                    </div>
                                </div>

                                {{-- 3. Line Total & Remove / Wishlist Actions --}}
                                <div class="col-6 col-md-3 d-flex flex-column align-items-end justify-content-center">
                                    <div class="text-muted small d-md-none" style="font-size: 0.72rem;">Total:</div>
                                    <div class="fw-bolder text-dark mb-1" id="item-total-{{ $item->id }}" style="font-size: 1.05rem; letter-spacing: -0.01em;">
                                        ₹{{ number_format($item->quantity * $item->unit_price, 2) }}
                                    </div>
                                    <div class="d-flex align-items-center justify-content-end gap-1.5 flex-wrap mt-1">
                                        <button type="button" class="btn btn-sm btn-link text-secondary p-0 text-decoration-none hover-primary small d-inline-flex align-items-center gap-1" onclick="moveToWishlist({{ $item->id }})" title="Move to Wishlist">
                                            <i class="bi bi-heart"></i> <span class="small d-none d-sm-inline">Wishlist</span>
                                        </button>
                                        <span class="text-muted small opacity-50">|</span>
                                        <button type="button" class="btn btn-sm btn-link text-muted p-0 text-decoration-none hover-danger small d-inline-flex align-items-center gap-1" onclick="removeItem({{ $item->id }})" title="Remove Item">
                                            <i class="bi bi-trash3"></i> <span class="small">Remove</span>
                                        </button>
                                    </div>
                                </div>

                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>

                {{-- Shopping Perks Info Bar (2 Clean Modern Badges) --}}
                <div class="card border-0 shadow-xs rounded-4 mb-3" style="background: #ffffff; border: 1px solid #e2e8f0 !important;">
                    <div class="card-body p-2.5 p-sm-3">
                        <div class="row g-2">
                            <div class="col-6 d-flex align-items-center gap-2 justify-content-center">
                                <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0" style="width: 28px; height: 28px; background: rgba(99, 102, 241, 0.1); color: #4f46e5;">
                                    <i class="bi bi-shield-lock-fill small"></i>
                                </div>
                                <span class="fw-semibold text-dark text-truncate" style="font-size: 0.76rem;">100% Secure Checkout</span>
                            </div>
                            <div class="col-6 d-flex align-items-center gap-2 justify-content-center">
                                <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0" style="width: 28px; height: 28px; background: rgba(16, 185, 129, 0.1); color: #059669;">
                                    <i class="bi bi-patch-check-fill small"></i>
                                </div>
                                <span class="fw-semibold text-dark text-truncate" style="font-size: 0.76rem;">Genuine Warranty</span>
                            </div>
                        </div>
                    </div>
                </div>

            </div>

            {{-- Right Column: Order Summary & Checkout Card --}}
            <div class="col-lg-4">
                <div class="card border-0 shadow-sm rounded-4 sticky-top overflow-hidden" style="top: 90px; z-index: 10; background: #ffffff; border: 1px solid #e2e8f0 !important;">
                    
                    {{-- Summary Header --}}
                    <div class="card-header bg-white py-3 px-3.5 border-bottom d-flex align-items-center justify-content-between">
                        <div class="d-flex align-items-center gap-2">
                            <i class="bi bi-receipt-cutoff text-primary fs-5"></i>
                            <h5 class="fw-bold mb-0 text-dark" style="font-size: 1.05rem;">Price Details</h5>
                        </div>
                        <span class="badge rounded-pill fw-semibold" style="background: #f1f5f9; color: #475569; font-size: 0.72rem;">
                            <span id="summary-items-count">{{ $totalCount }}</span> Items
                        </span>
                    </div>

                    <div class="card-body p-3.5">
                        
                        {{-- MRP Row --}}
                        <div class="d-flex justify-content-between mb-2.5 text-secondary" style="font-size: 0.88rem;">
                            <span>Total MRP</span>
                            <span class="fw-semibold text-dark" id="summary-mrp">₹{{ isset($totalMrp) ? number_format($totalMrp, 2) : number_format($subtotal, 2) }}</span>
                        </div>

                        {{-- Discount Row --}}
                        <div class="d-flex justify-content-between mb-2.5" style="font-size: 0.88rem;">
                            <span class="text-secondary">Discount on MRP</span>
                            <span class="text-success fw-bold" id="summary-discount">-₹{{ isset($totalDiscount) ? number_format($totalDiscount, 2) : '0.00' }}</span>
                        </div>

                        {{-- Extra Offer Discount Row --}}
                        <div class="d-flex justify-content-between mb-2.5 {{ (isset($offerDiscount) && $offerDiscount > 0) ? '' : 'd-none' }}" id="summary-offer-discount-container" style="font-size: 0.88rem;">
                            <span class="text-secondary d-flex align-items-center gap-1">
                                <i class="bi bi-tag-fill text-success small"></i> Coupon / Offer
                            </span>
                            <span class="text-success fw-bold" id="summary-offer-discount">-₹{{ number_format($offerDiscount ?? 0, 2) }}</span>
                        </div>

                        {{-- Tax / GST --}}
                        <div class="d-flex justify-content-between mb-3 text-secondary" style="font-size: 0.88rem;">
                            <span>Taxes & GST</span>
                            <span class="text-muted small">Included</span>
                        </div>

                        <hr class="my-3 opacity-25">

                        {{-- Grand Total Amount --}}
                        <div class="d-flex justify-content-between align-items-baseline mb-2">
                            <div>
                                <span class="h6 fw-bold text-dark mb-0 d-block">Total Payable</span>
                                <small class="text-muted" style="font-size: 0.72rem;">Inclusive of all taxes</small>
                            </div>
                            <span class="h4 fw-bolder text-primary mb-0" id="summary-total" style="letter-spacing: -0.02em;">
                                ₹{{ isset($grandTotal) ? number_format($grandTotal, 2) : number_format($subtotal, 2) }}
                            </span>
                        </div>

                        {{-- Total Savings Pill --}}
                        @php
                            $currentSavings = ($totalDiscount ?? 0) + ($offerDiscount ?? 0);
                        @endphp
                        <div class="alert alert-success border-0 py-2 px-3 rounded-3 mb-3 d-flex align-items-center gap-2 {{ $currentSavings > 0 ? '' : 'd-none' }}" id="summary-savings-banner" style="background: rgba(16, 185, 129, 0.1); color: #047857; font-size: 0.8rem;">
                            <i class="bi bi-piggy-bank-fill fs-6"></i>
                            <span>You will save <strong id="summary-savings-amount">₹{{ number_format($currentSavings, 2) }}</strong> on this order!</span>
                        </div>

                        {{-- Checkout Primary CTA --}}
                        <div class="d-grid gap-2">
                            <button type="button" id="proceed-checkout-btn" class="btn btn-primary btn-lg py-2.5 rounded-pill shadow-sm fw-bold d-flex align-items-center justify-content-center gap-2 w-100 {{ $selectedCount === 0 ? 'disabled' : '' }}" style="font-size: 0.95rem; background: linear-gradient(135deg, #4f46e5 0%, #6366f1 100%); border: none; {{ $selectedCount === 0 ? 'opacity: 0.6;' : '' }}" onclick="handleProceedToCheckout(event)">
                                <i class="bi bi-lock-fill"></i> Proceed to Checkout <i class="bi bi-arrow-right"></i>
                            </button>
                        </div>

                    </div>
                </div>
            </div>

        </div>

        {{-- 📱 App-Style Sticky Bottom Checkout Bar for Mobile Devices --}}
        <div class="d-block d-md-none position-fixed start-0 end-0 bg-white border-top shadow-lg p-2.5 px-3" 
             id="mobile-checkout-sticky-bar"
             style="bottom: 60px; z-index: 1035; backdrop-filter: blur(10px); background: rgba(255, 255, 255, 0.98) !important;">
            <div class="d-flex align-items-center justify-content-between gap-2">
                <div>
                    <span class="text-muted d-block" style="font-size: 0.68rem; line-height: 1;">Total Payable</span>
                    <span class="fw-bolder text-primary fs-5" id="mobile-summary-total" style="letter-spacing: -0.01em;">
                        ₹{{ isset($grandTotal) ? number_format($grandTotal, 2) : number_format($subtotal, 2) }}
                    </span>
                </div>
                <button type="button" id="mobile-proceed-checkout-btn" 
                        class="btn btn-primary rounded-pill px-4 py-2 fw-bold shadow-sm d-flex align-items-center gap-1.5 {{ $selectedCount === 0 ? 'disabled' : '' }}" 
                        style="font-size: 0.88rem; background: linear-gradient(135deg, #4f46e5 0%, #6366f1 100%); border: none; {{ $selectedCount === 0 ? 'opacity: 0.6;' : '' }}" 
                        onclick="handleProceedToCheckout(event)">
                    <span>Checkout</span> <i class="bi bi-arrow-right"></i>
                </button>
            </div>
        </div>

    @else
        {{-- 5. Illustrated Empty Bag State --}}
        <div class="card border-0 shadow-xs rounded-4 py-5 text-center my-3" style="background: #ffffff; border: 1px solid #e2e8f0 !important;">
            <div class="card-body py-5 px-3">
                <div class="rounded-circle d-inline-flex align-items-center justify-content-center mb-3" 
                     style="width: 100px; height: 100px; background: rgba(99, 102, 241, 0.08); color: #6366f1;">
                    <i class="bi bi-bag-x-fill" style="font-size: 3rem;"></i>
                </div>
                <h3 class="fw-bold text-dark mb-2" style="letter-spacing: -0.01em;">Your Shopping Bag is Empty</h3>
                <p class="text-muted mb-4 mx-auto small" style="max-width: 420px; font-size: 0.88rem;">
                    Looks like you haven't added anything to your cart yet. Explore our curated collections and discover great deals today!
                </p>
                <div class="d-flex align-items-center justify-content-center gap-2 flex-wrap">
                    <a href="{{ route('shop') }}" class="btn btn-primary rounded-pill px-4 py-2 fw-bold shadow-xs btn-sm" style="font-size: 0.86rem;">
                        <i class="bi bi-shop me-1"></i> Start Shopping
                    </a>
                    <a href="{{ route('categories.index') }}" class="btn btn-light border rounded-pill px-4 py-2 fw-semibold text-secondary shadow-xs btn-sm" style="font-size: 0.86rem;">
                        <i class="bi bi-grid me-1"></i> Browse Categories
                    </a>
                </div>
            </div>
        </div>
    @endif
</div>

@push('scripts')
<script>
    function formatCurrency(amount) {
        return '₹' + parseFloat(amount).toLocaleString('en-IN', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }

    function updateTotalsAndBadges(data) {
        if (data.cart_count === 0) {
            window.location.reload();
            return;
        }

        // 1. Update Cart Page Header Badge & Summary Count
        const badge = document.getElementById('cart-page-badge');
        if (badge) badge.textContent = data.cart_count + (data.cart_count === 1 ? ' item' : ' items');

        const summaryItemsCount = document.getElementById('summary-items-count');
        if (summaryItemsCount) summaryItemsCount.textContent = data.cart_count;

        // 2. Update Pricing Breakdown
        const mrpEl = document.getElementById('summary-mrp');
        if (mrpEl && data.total_mrp !== undefined) mrpEl.textContent = formatCurrency(data.total_mrp);

        const discountEl = document.getElementById('summary-discount');
        if (discountEl && data.total_discount !== undefined) discountEl.textContent = '-' + formatCurrency(data.total_discount);

        const offerDiscountContainer = document.getElementById('summary-offer-discount-container');
        const offerDiscountEl = document.getElementById('summary-offer-discount');
        if (offerDiscountEl && data.offer_discount !== undefined) {
            offerDiscountEl.textContent = '-' + formatCurrency(data.offer_discount);
            if (offerDiscountContainer) {
                if (data.offer_discount > 0) offerDiscountContainer.classList.remove('d-none');
                else offerDiscountContainer.classList.add('d-none');
            }
        }

        // 3. Update Grand Total (Desktop & Mobile)
        const totalEl = document.getElementById('summary-total');
        const mobileTotalEl = document.getElementById('mobile-summary-total');
        const newTotalStr = data.grand_total !== undefined ? formatCurrency(data.grand_total) : (data.subtotal !== undefined ? formatCurrency(data.subtotal) : '₹0.00');

        if (totalEl) totalEl.textContent = newTotalStr;
        if (mobileTotalEl) mobileTotalEl.textContent = newTotalStr;

        // 4. Update Savings Banner
        const savingsBanner = document.getElementById('summary-savings-banner');
        const savingsAmount = document.getElementById('summary-savings-amount');
        const totalSavings = (data.total_discount || 0) + (data.offer_discount || 0);
        if (savingsBanner && savingsAmount) {
            if (totalSavings > 0) {
                savingsAmount.textContent = formatCurrency(totalSavings);
                savingsBanner.classList.remove('d-none');
            } else {
                savingsBanner.classList.add('d-none');
            }
        }
        
        // 5. Update Navbar Badge
        const navBadge = document.getElementById('cart-badge');
        if (navBadge) {
            navBadge.textContent = data.cart_count;
            navBadge.style.display = data.cart_count > 0 ? 'inline-block' : 'none';
        }

        // 6. Update Dynamic Free Shipping Progress Banner
        const freeShippingThreshold = {{ (float) \App\Models\Setting::get('free_shipping_min', 499) }};
        const currentSubtotal = data.subtotal !== undefined ? data.subtotal : (data.grand_total || 0);
        const amountNeeded = Math.max(0, freeShippingThreshold - currentSubtotal);
        const progressPct = Math.min(100, (currentSubtotal / freeShippingThreshold) * 100);

        const fsTitleText = document.getElementById('fs-title-text');
        const fsBadge = document.getElementById('fs-badge');
        const fsProgressBar = document.getElementById('fs-progress-bar');
        const fsIconContainer = document.getElementById('fs-icon-container');

        if (fsTitleText && fsBadge && fsProgressBar) {
            if (amountNeeded <= 0) {
                fsTitleText.innerHTML = '🎉 You\'ve unlocked <span class="text-success fw-bolder">FREE Delivery</span> on this order!';
                fsBadge.textContent = 'UNLOCKED';
                fsBadge.style.background = '#d1fae5';
                fsBadge.style.color = '#047857';
                fsProgressBar.style.width = '100%';
                fsProgressBar.style.background = 'linear-gradient(90deg, #10b981 0%, #059669 100%)';
                if (fsIconContainer) {
                    fsIconContainer.style.background = 'rgba(16, 185, 129, 0.12)';
                    fsIconContainer.style.color = '#059669';
                    fsIconContainer.innerHTML = '<i class="bi bi-truck-flatbed fs-6"></i>';
                }
            } else {
                fsTitleText.innerHTML = 'Add <span class="text-primary fw-bolder" id="fs-amount-needed">' + formatCurrency(amountNeeded) + '</span> more to unlock <span class="fw-bolder">FREE Delivery!</span>';
                fsBadge.textContent = Math.round(progressPct) + '%';
                fsBadge.style.background = '#e0e7ff';
                fsBadge.style.color = '#4338ca';
                fsProgressBar.style.width = progressPct + '%';
                fsProgressBar.style.background = 'linear-gradient(90deg, #6366f1 0%, #4f46e5 100%)';
                if (fsIconContainer) {
                    fsIconContainer.style.background = 'rgba(99, 102, 241, 0.12)';
                    fsIconContainer.style.color = '#4f46e5';
                    fsIconContainer.innerHTML = '<i class="bi bi-truck fs-6"></i>';
                }
            }
        }
    }

    function moveToWishlist(itemId) {
        const card = document.getElementById('cart-item-' + itemId);
        if (card) card.style.opacity = '0.4';

        fetch(`/cart/move-to-wishlist/${itemId}`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        })
        .then(res => {
            if (res.status === 401) {
                window.location.href = '{{ route("login") }}';
                return;
            }
            return res.json();
        })
        .then(data => {
            if (data && data.success) {
                if (card) card.remove();
                updateTotalsAndBadges(data);
                if (window.Swal) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Moved to Wishlist',
                        text: data.message,
                        timer: 2000,
                        showConfirmButton: false
                    });
                }
            } else if (data) {
                alert(data.message || 'Unable to move item to wishlist.');
                window.location.reload();
            }
        })
        .catch(err => {
            console.error(err);
            window.location.reload();
        });
    }

    function updateQty(itemId, delta) {
        const input = document.getElementById('qty-' + itemId);
        if (!input) return;

        const max = parseInt(input.max) || 99;
        let currentVal = parseInt(input.value) || 1;
        let newVal = currentVal + delta;

        if (newVal >= 1 && newVal <= max) {
            input.value = newVal;
            
            fetch(`/cart/update/${itemId}`, {
                method: 'PATCH',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ quantity: newVal })
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    const itemTotal = document.getElementById('item-total-' + itemId);
                    if (itemTotal) itemTotal.textContent = formatCurrency(data.item_total);
                    
                    updateTotalsAndBadges(data);
                } else {
                    alert(data.message || 'Unable to update quantity.');
                    window.location.reload();
                }
            })
            .catch(err => {
                console.error(err);
                window.location.reload();
            });
        }
    }

    function removeItem(itemId) {
        const card = document.getElementById('cart-item-' + itemId);
        if (card) card.style.opacity = '0.4';

        fetch(`/cart/remove/${itemId}`, {
            method: 'DELETE',
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
                if (card) card.remove();
                updateTotalsAndBadges(data);
            } else {
                alert('Error removing item from bag.');
                window.location.reload();
            }
        })
        .catch(err => {
            console.error(err);
            window.location.reload();
        });
    }

    function clearCart() {
        if (!confirm('Are you sure you want to clear your shopping bag?')) return;

        fetch(`{{ route('cart.clear') }}`, {
            method: 'DELETE',
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
                window.location.reload();
            } else {
                alert('Error clearing bag.');
            }
        })
        .catch(err => {
            console.error(err);
            window.location.reload();
        });
    }

    function toggleSelectItem(itemId, isSelected) {
        const card = document.getElementById('cart-item-' + itemId);
        if (card) {
            card.style.opacity = isSelected ? '1' : '0.6';
            card.style.filter = isSelected ? 'none' : 'grayscale(35%)';
        }

        fetch(`/cart/toggle-select/${itemId}`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            },
            body: JSON.stringify({ is_selected: isSelected ? 1 : 0 })
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                updateTotalsAndBadges(data);
                updateSelectAllState();
            }
        })
        .catch(err => {
            console.error(err);
        });
    }

    function toggleSelectAllItems(isSelected) {
        const checkboxes = document.querySelectorAll('.item-select-checkbox');
        checkboxes.forEach(cb => {
            cb.checked = isSelected;
            const itemId = cb.getAttribute('data-item-id');
            const card = document.getElementById('cart-item-' + itemId);
            if (card) {
                card.style.opacity = isSelected ? '1' : '0.6';
                card.style.filter = isSelected ? 'none' : 'grayscale(35%)';
            }
        });

        fetch(`/cart/toggle-select-all`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            },
            body: JSON.stringify({ is_selected: isSelected ? 1 : 0 })
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                updateTotalsAndBadges(data);
                updateSelectAllState();
            }
        })
        .catch(err => {
            console.error(err);
        });
    }

    function updateSelectAllState() {
        const checkboxes = document.querySelectorAll('.item-select-checkbox');
        const checkedCount = document.querySelectorAll('.item-select-checkbox:checked').length;
        const selectAllCb = document.getElementById('select-all-checkbox');
        const selectedLabel = document.getElementById('selected-items-label');

        if (selectAllCb) selectAllCb.checked = checkboxes.length > 0 && checkedCount === checkboxes.length;
        if (selectedLabel) selectedLabel.textContent = checkedCount;

        const proceedBtn = document.getElementById('proceed-checkout-btn');
        const mobileProceedBtn = document.getElementById('mobile-proceed-checkout-btn');

        [proceedBtn, mobileProceedBtn].forEach(btn => {
            if (btn) {
                if (checkedCount === 0) {
                    btn.classList.add('disabled');
                    btn.style.opacity = '0.6';
                } else {
                    btn.classList.remove('disabled');
                    btn.style.opacity = '1';
                }
            }
        });
    }

    function handleProceedToCheckout(e) {
        const checkedCount = document.querySelectorAll('.item-select-checkbox:checked').length;
        if (checkedCount === 0) {
            if (e) e.preventDefault();
            if (window.Swal) {
                Swal.fire({
                    icon: 'warning',
                    title: 'No Items Selected',
                    text: 'Please select at least 1 item to proceed to checkout.',
                    confirmButtonColor: '#4f46e5'
                });
            } else {
                alert('Please select at least 1 item to proceed to checkout.');
            }
            return false;
        }
        window.location.href = '{{ route("checkout.index") }}';
    }
</script>
@endpush

<style>
    .hover-danger:hover { color: var(--bs-danger) !important; }
    .hover-primary:hover { color: #4f46e5 !important; }
    .shadow-xs { box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05); }
    .cart-item-row:last-child { border-bottom: none !important; }
    
    @media (max-width: 767.98px) {
        body {
            padding-bottom: 130px !important;
        }
        .cart-item-card img {
            width: 72px !important;
            height: 72px !important;
        }
        .cart-item-card {
            border-radius: 1rem !important;
        }
    }
</style>
@endsection
