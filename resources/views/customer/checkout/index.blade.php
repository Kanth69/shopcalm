@extends('layouts.customer')

@section('title', 'Checkout - ShopCalm')

@push('styles')
<script src="https://cdn.jsdelivr.net/npm/canvas-confetti@1.9.2/dist/confetti.browser.min.js"></script>
<style>
.coupon-celebration-banner {
    animation: popIn 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
}
@keyframes popIn {
    0% { transform: scale(0.9); opacity: 0; }
    100% { transform: scale(1); opacity: 1; }
}
.coupon-tag-badge {
    background-color: #eff6ff !important;
    color: #2563eb !important;
    border: 1px solid #bfdbfe !important;
    font-weight: 700;
    font-size: 0.78rem;
    border-radius: 50rem;
    padding: 0.35rem 0.85rem;
    display: inline-flex;
    align-items: center;
}
.coupon-code-pill {
    background-color: #e0e7ff !important;
    color: #3730a3 !important;
    border: 1px dashed #6366f1 !important;
    font-family: monospace;
    font-weight: 800;
    font-size: 0.85rem;
    padding: 0.25rem 0.6rem;
    border-radius: 0.375rem;
    letter-spacing: 0.5px;
}
.btn-place-order-glow {
    background: linear-gradient(135deg, #8b5cf6 0%, #2563eb 100%);
    border: none;
    box-shadow: 0 8px 20px rgba(59, 130, 246, 0.35);
    transition: all 0.3s ease;
}
.btn-place-order-glow:hover {
    transform: translateY(-2px);
    box-shadow: 0 12px 25px rgba(59, 130, 246, 0.45);
}
</style>
@endpush

@section('content')
<div class="container my-5">
    <div class="row g-4">
        <div class="col-lg-7">
            <!-- 1. Delivery Address Card (Read-Only Selected Address + Switch/Add Options) -->
            <div class="card border-0 shadow-sm rounded-4 mb-4" id="delivery-address-card">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="fw-bold mb-0 text-dark">
                            <i class="bi bi-geo-alt-fill text-primary me-2"></i>1. Delivery Address
                        </h5>
                        <div class="d-flex gap-2">
                            <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill" id="btn-change-address" onclick="showAddressSelectorModal()" style="display: {{ $addresses->count() > 0 ? 'inline-block' : 'none' }};">
                                <i class="bi bi-arrow-repeat me-1"></i> Change Address
                            </button>
                            <button type="button" class="btn btn-sm btn-outline-primary rounded-pill" onclick="openNewAddressForm()">
                                <i class="bi bi-plus-lg me-1"></i> Add New Address
                            </button>
                        </div>
                    </div>

                    <!-- Error Banner for Missing Address -->
                    <div id="address-error-banner" class="alert alert-danger small mb-3 rounded-3 border-0 bg-danger-subtle text-danger" style="display: none;">
                        <i class="bi bi-exclamation-triangle-fill me-2"></i><span>Please select or add a valid delivery address before placing your order.</span>
                    </div>

                    <!-- Success Banner for Saved Address -->
                    <div id="address-success-banner" class="alert alert-success small mb-3 rounded-3 border-0 bg-success-subtle text-success" style="display: none;">
                        <i class="bi bi-check-circle-fill me-2"></i><span>Address saved successfully!</span>
                    </div>

                    <!-- Active Selected Shipping Display (Read-Only) -->
                    <div id="active-address-display" class="p-3 bg-light rounded-3 border">
                        <div id="no-address-msg" style="display: {{ $addresses->count() > 0 ? 'none' : 'block' }};" class="text-center py-3 text-muted">
                            <i class="bi bi-building-add fs-2 text-primary d-block mb-2"></i>
                            No saved address found. Click <strong>"Add New Address"</strong> below to save your delivery location.
                        </div>

                        @php $activeAddr = $addresses->count() > 0 ? ($addresses->firstWhere('is_default', true) ?? $addresses->first()) : null; @endphp
                        <div id="active-address-details" style="display: {{ $addresses->count() > 0 ? 'block' : 'none' }};">
                            <div class="d-flex justify-content-between align-items-start mb-1">
                                <h6 class="fw-bold mb-0 text-dark" id="display-name">{{ $activeAddr ? $activeAddr->name : '' }}</h6>
                                <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-3">Selected</span>
                            </div>
                            <p class="text-secondary mb-1" id="display-street-city">{{ $activeAddr ? "{$activeAddr->address}, {$activeAddr->city}, {$activeAddr->state} - {$activeAddr->zip}" : '' }}</p>
                            <div class="small text-muted" id="display-phone"><i class="bi bi-telephone me-1"></i> Phone: {{ $activeAddr ? $activeAddr->phone : '' }}</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 2. Add New Address Form Modal/Collapse -->
            <div class="card border-primary shadow-sm rounded-4 mb-4" id="new-address-card" style="display: {{ $addresses->count() === 0 ? 'block' : 'none' }};">
                <div class="card-header bg-primary text-white py-3 px-4 rounded-top-4 d-flex justify-content-between align-items-center">
                    <h6 class="fw-bold mb-0"><i class="bi bi-house-add me-2"></i>Add New Delivery Address</h6>
                    @if($addresses->count() > 0)
                        <button type="button" class="btn-close btn-close-white" onclick="closeNewAddressForm()"></button>
                    @endif
                </div>
                <div class="card-body p-4">
                    <form id="new-address-form" onsubmit="handleSaveAddress(event)">
                        @csrf
                        <div class="row g-3">
                            <div class="col-md-12">
                                <label for="new_name" class="form-label fw-semibold small">Full Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control rounded-3" id="new_name" required value="{{ auth()->user()->name ?? '' }}">
                            </div>
                            <div class="col-md-6">
                                <label for="new_phone" class="form-label fw-semibold small">Mobile Number <span class="text-danger">*</span></label>
                                <input type="tel" class="form-control rounded-3" id="new_phone" required value="{{ auth()->user()->mobile_number ?? '' }}">
                            </div>
                            <div class="col-md-6">
                                <label for="new_zip" class="form-label fw-semibold small">Pincode / ZIP <span class="text-danger">*</span></label>
                                <input type="text" class="form-control rounded-3" id="new_zip" required placeholder="6-digit PIN">
                            </div>
                            <div class="col-12">
                                <label for="new_address" class="form-label fw-semibold small">Flat / House No / Building / Street <span class="text-danger">*</span></label>
                                <textarea class="form-control rounded-3" id="new_address" rows="2" required placeholder="Enter full street address"></textarea>
                            </div>
                            <div class="col-md-6">
                                <label for="new_city" class="form-label fw-semibold small">City <span class="text-danger">*</span></label>
                                <input type="text" class="form-control rounded-3" id="new_city" required>
                            </div>
                            <div class="col-md-6">
                                <label for="new_state" class="form-label fw-semibold small">State <span class="text-danger">*</span></label>
                                <input type="text" class="form-control rounded-3" id="new_state" required>
                            </div>
                        </div>

                        <div class="d-flex justify-content-end gap-2 mt-4">
                            @if($addresses->count() > 0)
                                <button type="button" class="btn btn-light rounded-pill px-4" onclick="closeNewAddressForm()">Cancel</button>
                            @endif
                            <button type="submit" class="btn btn-primary rounded-pill px-4 shadow-sm" id="btn-save-address">
                                <i class="bi bi-bookmark-check me-2"></i> Save Address
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Hidden Actual Checkout Form (Stores selected address values) -->
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

                <!-- 3. Delivery Notes Section (Separately visible below address) -->
                <div class="card border-0 shadow-sm rounded-4 mb-4">
                    <div class="card-body p-4">
                        <h6 class="fw-bold mb-3 text-dark"><i class="bi bi-pencil-square me-2 text-primary"></i>Delivery Instructions / Order Notes</h6>
                        <textarea class="form-control rounded-3" id="notes" name="notes" rows="3" placeholder="Add any specific instructions for delivery (e.g. Leave at front door, call before delivery)."></textarea>
                    </div>
                </div>
            </form>
        </div>

        <div class="col-lg-5">
            <!-- Apply Coupon Card -->
            <div class="card border-0 shadow-sm rounded-4 mb-4 overflow-hidden" style="background: #ffffff; border: 1px solid #e2e8f0 !important;">
                <div class="card-body p-4" id="coupon-card-body">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <h5 class="fw-bold mb-0 text-dark d-flex align-items-center gap-2">
                            <i class="bi bi-ticket-perforated-fill text-primary fs-4"></i> Apply Coupon
                        </h5>
                        <span class="coupon-tag-badge"><i class="bi bi-percent me-1"></i> Coupon Savings</span>
                    </div>
                    
                    <div id="coupon-status-wrapper">
                        @if($couponCode)
                            <div class="coupon-celebration-banner p-3 rounded-4 text-white shadow-sm position-relative overflow-hidden" id="applied-coupon-box" style="background: linear-gradient(135deg, #10b981 0%, #059669 100%);">
                                <div class="d-flex align-items-center justify-content-between gap-2">
                                    <div class="d-flex align-items-center gap-3">
                                        <div class="celebration-icon bg-white text-success rounded-circle p-2 d-flex align-items-center justify-content-center shadow-sm flex-shrink-0" style="width: 44px; height: 44px;">
                                            <i class="bi bi-party-popper fs-4"></i>
                                        </div>
                                        <div>
                                            <h6 class="mb-0 fw-bolder text-white">Hurray! You saved <span id="hurray-savings-amount">₹{{ number_format($discountAmount, 2) }}</span></h6>
                                            <small class="text-white-50">Coupon <strong class="text-white text-uppercase" id="hurray-coupon-code">{{ $couponCode }}</strong> applied successfully 🎉</small>
                                        </div>
                                    </div>
                                    <button type="button" class="btn btn-sm btn-light border-0 text-danger rounded-pill px-3 fw-bold flex-shrink-0 shadow-sm" id="btn-remove-coupon-ajax">Remove</button>
                                </div>
                            </div>
                        @else
                            <form id="ajax-coupon-form" action="{{ route('checkout.apply-coupon') }}">
                                @csrf
                                <div class="position-relative">
                                    <i class="bi bi-ticket-perforated text-primary fs-5 position-absolute top-50 translate-middle-y ms-3" style="z-index: 5; pointer-events: none;"></i>
                                    <input type="text" name="coupon_code" id="coupon_code_input" class="form-control rounded-pill ps-5 pe-5 py-2.5 fw-bold border-2" placeholder="ENTER COUPON CODE" required style="text-transform: uppercase; letter-spacing: 1px; font-size: 0.92rem; border-color: #cbd5e1; height: 50px;">
                                    <button type="submit" class="btn btn-primary rounded-pill px-4 fw-bold position-absolute end-0 top-0 bottom-0 m-1 shadow-sm d-flex align-items-center gap-1" id="btn-apply-coupon">
                                        <span>Apply</span> <i class="bi bi-arrow-right"></i>
                                    </button>
                                </div>
                            </form>

                            @if(isset($availableCoupons) && $availableCoupons->count() > 0)
                                <div class="mt-3 pt-3 border-top text-center" id="available-coupons-section">
                                    <button type="button" class="btn btn-sm btn-light border text-primary rounded-pill px-4 py-2.5 fw-bold w-100 d-flex align-items-center justify-content-center gap-2 shadow-sm" data-bs-toggle="modal" data-bs-target="#couponsModal">
                                        <i class="bi bi-ticket-perforated-fill text-warning fs-5"></i>
                                        <span>View Available Coupons ({{ $availableCoupons->count() }})</span>
                                        <i class="bi bi-chevron-right ms-auto small"></i>
                                    </button>
                                </div>
                            @endif
                        @endif
                    </div>

                    <div id="coupon-error-banner" class="alert alert-danger small mt-3 mb-0 rounded-3 border-0 bg-danger-subtle text-danger" style="{{ $couponError ? 'display: block;' : 'display: none;' }}">
                        <i class="bi bi-exclamation-triangle-fill me-2"></i><span id="coupon-error-text">{{ $couponError ?? '' }}</span>
                    </div>
                </div>
            </div>

            <!-- Order Summary -->
            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-body p-4">
                    <h5 class="fw-bold mb-4"><i class="bi bi-bag-check me-2 text-primary"></i>Order Summary</h5>
                    <!-- Cart Items -->
                    <div class="checkout-items-wrapper mb-4">
                        @foreach($cart->items as $item)
                        <div class="d-flex justify-content-between align-items-center py-3 border-bottom">
                            <div class="d-flex align-items-center gap-3">
                                @if($item->product->main_image)
                                    <div class="position-relative">
                                        <img src="{{ asset('storage/' . $item->product->main_image) }}" alt="{{ $item->product->name }}" class="rounded-3 border shadow-sm" style="width: 56px; height: 56px; object-fit: cover;">
                                        <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-secondary border border-light">{{ $item->quantity }}</span>
                                    </div>
                                @endif
                                <div class="ms-2">
                                    <h6 class="mb-1 fw-bold text-dark" style="font-size: 0.95rem;">{{ $item->product->name }}</h6>
                                    <div class="d-flex align-items-center gap-2">
                                        <small class="fw-semibold text-primary">₹{{ number_format($item->unit_price, 2) }} <span class="text-muted fw-normal">× {{ $item->quantity }}</span></small>
                                        @if($item->product->price > $item->unit_price)
                                            <small class="text-muted text-decoration-line-through" style="font-size: 0.8rem;">₹{{ number_format($item->product->price, 2) }}</small>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            <span class="fw-bold text-dark">₹{{ number_format($item->quantity * $item->unit_price, 2) }}</span>
                        </div>
                        @endforeach
                    </div>

                    <!-- Price Breakdown Box -->
                    <div class="price-breakdown bg-light rounded-4 p-4 mb-4">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="text-muted fw-semibold">Total MRP</span>
                            <span class="fw-semibold text-dark">₹{{ number_format($totalMrp ?? $subtotal, 2) }}</span>
                        </div>

                        @if(isset($totalDiscount) && $totalDiscount > 0)
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="text-muted fw-semibold">Discount on MRP</span>
                            <span class="fw-bold text-success">-₹{{ number_format($totalDiscount, 2) }}</span>
                        </div>
                        @endif

                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="text-muted fw-semibold">Subtotal</span>
                            <span class="fw-semibold text-dark" id="summary-subtotal">₹{{ number_format($subtotal, 2) }}</span>
                        </div>

                        <div class="d-flex justify-content-between align-items-center mb-2 text-success {{ $discountAmount > 0 ? '' : 'd-none' }}" id="summary-discount-row">
                            <span class="fw-semibold" id="summary-discount-label">Coupon ({{ $couponCode }})</span>
                            <span class="fw-bold" id="summary-discount-val">-₹{{ number_format($discountAmount, 2) }}</span>
                        </div>

                        @if(isset($offerDiscount) && $offerDiscount > 0)
                        <div class="d-flex justify-content-between align-items-center mb-2 text-primary">
                            <span class="fw-semibold"><i class="bi bi-fire text-warning me-1"></i> Sale Offer</span>
                            <span class="fw-bold">-₹{{ number_format($offerDiscount, 2) }}</span>
                        </div>
                        @endif

                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <span class="text-muted fw-semibold">Shipping</span>
                            <span class="fw-bold text-success">FREE</span>
                        </div>

                        <hr class="border-secondary opacity-25 my-3">
                        
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="fs-5 fw-bold text-dark">Grand Total</span>
                            <span class="fs-4 fw-bolder text-primary" id="summary-grand-total">₹{{ number_format($grandTotal, 2) }}</span>
                        </div>
                    </div>

                    @php
                        $totalSavings = ($totalDiscount ?? 0) + ($discountAmount ?? 0) + ($offerDiscount ?? 0);
                    @endphp
                    <div id="total-savings-box" class="alert alert-success rounded-4 border-0 d-flex align-items-center mb-0 mt-3 py-2 px-3 {{ $totalSavings > 0 ? '' : 'd-none' }}">
                        <i class="bi bi-piggy-bank fs-4 me-3 text-success"></i>
                        <div class="fw-bold text-success mb-0">
                            You will save <span id="total-savings-amount">₹{{ number_format($totalSavings, 2) }}</span> on this order
                        </div>
                    </div>

                    <div class="mt-4 p-3 bg-light rounded-3">
                        <h6 class="fw-bold mb-2 small text-uppercase text-muted">Payment Option</h6>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="payment_method" id="cod" value="cod" checked form="checkout-form">
                            <label class="form-check-label fw-semibold text-dark" for="cod">
                                <i class="bi bi-cash-stack me-2 text-success"></i> Cash On Delivery (COD)
                            </label>
                        </div>
                    </div>

                    <div class="d-grid mt-4">
                        <button type="submit" form="checkout-form" class="btn btn-primary btn-lg rounded-pill shadow" id="btn-place-order">
                            Place Order (Cash On Delivery) <i class="bi bi-arrow-right ms-2"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal for selecting/changing saved address -->
<div class="modal fade" id="addressSelectorModal" tabindex="-1" aria-labelledby="addressModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content rounded-4 border-0 shadow">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold" id="addressModalLabel"><i class="bi bi-geo-alt-fill text-primary me-2"></i>Select Saved Address</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <div class="row g-3" id="modal-address-list">
                    @foreach($addresses as $addr)
                    <div class="col-12">
                        <div class="card border rounded-3 p-3 address-modal-option cursor-pointer" onclick="selectModalAddress({{ json_encode($addr) }})">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <strong class="text-dark fs-6">{{ $addr->name }}</strong>
                                    <p class="text-secondary mb-1 mt-1">{{ $addr->address }}, {{ $addr->city }}, {{ $addr->state }} - {{ $addr->zip }}</p>
                                    <div class="small text-muted"><i class="bi bi-telephone me-1"></i> {{ $addr->phone }}</div>
                                </div>
                                <button type="button" class="btn btn-sm btn-outline-primary rounded-pill px-3 mt-1">Deliver Here</button>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>

<script>
let userAddresses = {!! json_encode($addresses) !!};
let currentSelectedAddress = userAddresses.length > 0 ? userAddresses[0] : null;

document.addEventListener('DOMContentLoaded', function() {
    if (currentSelectedAddress) {
        applySelectedAddress(currentSelectedAddress);
    }

    document.getElementById('checkout-form').addEventListener('submit', function(e) {
        if (!document.getElementById('hidden_shipping_address').value) {
            e.preventDefault();
            const errorBanner = document.getElementById('address-error-banner');
            if (errorBanner) {
                errorBanner.style.display = 'block';
                // Scroll to the error banner smoothly
                errorBanner.scrollIntoView({ behavior: 'smooth', block: 'center' });
            }
        }
    });
});

function applySelectedAddress(addr) {
    currentSelectedAddress = addr;

    // Toggle active address display vs no address message
    const noAddrMsg = document.getElementById('no-address-msg');
    if (noAddrMsg) noAddrMsg.style.display = 'none';

    const addrDetails = document.getElementById('active-address-details');
    if (addrDetails) addrDetails.style.display = 'block';

    const errorBanner = document.getElementById('address-error-banner');
    if (errorBanner) errorBanner.style.display = 'none';

    // 1. Update Read-Only Card UI
    const nameEl = document.getElementById('display-name');
    if (nameEl) nameEl.textContent = addr.name;

    const streetCityEl = document.getElementById('display-street-city');
    if (streetCityEl) streetCityEl.textContent = `${addr.address}, ${addr.city}, ${addr.state} - ${addr.zip}`;

    const phoneEl = document.getElementById('display-phone');
    if (phoneEl) phoneEl.innerHTML = `<i class="bi bi-telephone me-1"></i> Phone: ${addr.phone}`;

    // 2. Populate Hidden Checkout Form Fields
    const hiddenName = document.getElementById('hidden_shipping_name');
    if (hiddenName) hiddenName.value = addr.name;

    const hiddenPhone = document.getElementById('hidden_shipping_phone');
    if (hiddenPhone) hiddenPhone.value = addr.phone;

    const hiddenAddr = document.getElementById('hidden_shipping_address');
    if (hiddenAddr) hiddenAddr.value = addr.address;

    const hiddenCity = document.getElementById('hidden_shipping_city');
    if (hiddenCity) hiddenCity.value = addr.city;

    const hiddenState = document.getElementById('hidden_shipping_state');
    if (hiddenState) hiddenState.value = addr.state;

    const hiddenZip = document.getElementById('hidden_shipping_zip');
    if (hiddenZip) hiddenZip.value = addr.zip;
}

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

        // Show success banner
        const successBanner = document.getElementById('address-success-banner');
        if (successBanner) {
            successBanner.style.display = 'block';
            successBanner.scrollIntoView({ behavior: 'smooth', block: 'center' });
            setTimeout(() => {
                successBanner.style.display = 'none';
            }, 5000);
        }

        // Show SweetAlert Toast if available
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                icon: 'success',
                title: 'Address Saved',
                text: 'Address saved successfully!',
                toast: true,
                position: 'top-end',
                showConfirmButton: false,
                timer: 3000,
                timerProgressBar: true
            });
        }

        // Update modal address list HTML dynamically
        renderModalAddresses(data.all_addresses);

        // Enable Change Address button
        const btnChange = document.getElementById('btn-change-address');
        if (btnChange) btnChange.style.display = 'inline-block';

        // Hide new address card
        closeNewAddressForm();

        // Clear inputs
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
        btn.innerHTML = '<i class="bi bi-bookmark-check me-2"></i> Save Address';
    });
}

function renderModalAddresses(addresses) {
    const container = document.getElementById('modal-address-list');
    container.innerHTML = addresses.map(addr => `
        <div class="col-12">
            <div class="card border rounded-3 p-3 address-modal-option cursor-pointer" onclick='selectModalAddress(${JSON.stringify(addr)})'>
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <strong class="text-dark fs-6">${addr.name}</strong>
                        <p class="text-secondary mb-1 mt-1">${addr.address}, ${addr.city}, ${addr.state} - ${addr.zip}</p>
                        <div class="small text-muted"><i class="bi bi-telephone me-1"></i> ${addr.phone}</div>
                    </div>
                    <button type="button" class="btn btn-sm btn-outline-primary rounded-pill px-3 mt-1">Deliver Here</button>
                </div>
            </div>
        </div>
    `).join('');
}

document.addEventListener('DOMContentLoaded', function() {
    const couponContainer = document.getElementById('coupon-status-wrapper');
    const errorBanner = document.getElementById('coupon-error-banner');
    const errorText = document.getElementById('coupon-error-text');
    const summaryDiscountRow = document.getElementById('summary-discount-row');
    const summaryDiscountLabel = document.getElementById('summary-discount-label');
    const summaryDiscountVal = document.getElementById('summary-discount-val');
    const summaryGrandTotal = document.getElementById('summary-grand-total');

    // Delegate submit event for ajax coupon form
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
                    // Trigger confetti celebration burst
                    if (typeof confetti === 'function') {
                        confetti({
                            particleCount: 100,
                            spread: 70,
                            origin: { y: 0.6 }
                        });
                    }

                    // Render Celebration Banner
                    couponContainer.innerHTML = `
                        <div class="coupon-celebration-banner p-3 rounded-4 text-white shadow-sm position-relative overflow-hidden" id="applied-coupon-box" style="background: linear-gradient(135deg, #10b981 0%, #059669 100%);">
                            <div class="d-flex align-items-center justify-content-between gap-2">
                                <div class="d-flex align-items-center gap-3">
                                    <div class="celebration-icon bg-white text-success rounded-circle p-2 d-flex align-items-center justify-content-center shadow-sm flex-shrink-0" style="width: 44px; height: 44px;">
                                        <i class="bi bi-party-popper fs-4"></i>
                                    </div>
                                    <div>
                                        <h6 class="mb-0 fw-bolder text-white">Hurray! You saved <span id="hurray-savings-amount">₹${data.formatted_discount}</span></h6>
                                        <small class="text-white-50">Coupon <strong class="text-white text-uppercase" id="hurray-coupon-code">${data.coupon_code}</strong> applied successfully 🎉</small>
                                    </div>
                                </div>
                                <button type="button" class="btn btn-sm btn-light border-0 text-danger rounded-pill px-3 fw-bold flex-shrink-0 shadow-sm" id="btn-remove-coupon-ajax">Remove</button>
                            </div>
                        </div>
                    `;
                    if (summaryDiscountRow) summaryDiscountRow.classList.remove('d-none');
                    if (summaryDiscountLabel) summaryDiscountLabel.textContent = `Coupon (${data.coupon_code})`;
                    if (summaryDiscountVal) summaryDiscountVal.textContent = `-₹${data.formatted_discount}`;
                    if (summaryGrandTotal) summaryGrandTotal.textContent = `₹${data.grand_total}`;
                    
                    const savingsBox = document.getElementById('total-savings-box');
                    const savingsAmount = document.getElementById('total-savings-amount');
                    if (savingsBox && savingsAmount && data.total_savings) {
                        const parsedSavings = parseFloat(data.total_savings.replace(/,/g, ''));
                        if (parsedSavings > 0) {
                            savingsBox.classList.remove('d-none');
                            savingsAmount.textContent = `₹${data.total_savings}`;
                        } else {
                            savingsBox.classList.add('d-none');
                        }
                    }
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
                if (btn) { btn.disabled = false; btn.textContent = 'Apply'; }
            });
        }
    });

    // Delegate click event for remove coupon button
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
                    const availCount = {{ isset($availableCoupons) ? $availableCoupons->count() : 0 }};
                    let availSectionHtml = '';
                    if (availCount > 0) {
                        availSectionHtml = `
                            <div class="mt-3 pt-3 border-top text-center" id="available-coupons-section">
                                <button type="button" class="btn btn-sm btn-light border text-primary rounded-pill px-4 py-2.5 fw-bold w-100 d-flex align-items-center justify-content-center gap-2 shadow-sm" data-bs-toggle="modal" data-bs-target="#couponsModal">
                                    <i class="bi bi-ticket-perforated-fill text-warning fs-5"></i>
                                    <span>View Available Coupons (${availCount})</span>
                                    <i class="bi bi-chevron-right ms-auto small"></i>
                                </button>
                            </div>
                        `;
                    }

                    couponContainer.innerHTML = `
                        <form id="ajax-coupon-form" action="{{ route('checkout.apply-coupon') }}">
                            <input type="hidden" name="_token" value="{{ csrf_token() }}">
                            <div class="position-relative">
                                <i class="bi bi-ticket-perforated text-primary fs-5 position-absolute top-50 translate-middle-y ms-3" style="z-index: 5; pointer-events: none;"></i>
                                <input type="text" name="coupon_code" id="coupon_code_input" class="form-control rounded-pill ps-5 pe-5 py-2.5 fw-bold border-2" placeholder="ENTER COUPON CODE" required style="text-transform: uppercase; letter-spacing: 1px; font-size: 0.92rem; border-color: #cbd5e1; height: 50px;">
                                <button type="submit" class="btn btn-primary rounded-pill px-4 fw-bold position-absolute end-0 top-0 bottom-0 m-1 shadow-sm d-flex align-items-center gap-1" id="btn-apply-coupon">
                                    <span>Apply</span> <i class="bi bi-arrow-right"></i>
                                </button>
                            </div>
                        </form>
                        ${availSectionHtml}
                    `;
                    if (errorBanner) errorBanner.style.display = 'none';
                    if (summaryDiscountRow) summaryDiscountRow.classList.add('d-none');
                    if (summaryGrandTotal) summaryGrandTotal.textContent = `₹${data.grand_total}`;
                }
            })
            .catch(err => console.error(err));
        }
    });
});

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

<!-- Available Coupons Modal -->
<div class="modal fade" id="couponsModal" tabindex="-1" aria-labelledby="couponsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
            <div class="modal-header text-white py-3 px-4" style="background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%);">
                <div>
                    <h5 class="modal-title fw-bold mb-0 text-white" id="couponsModalLabel">
                        <i class="bi bi-ticket-perforated-fill text-warning me-2"></i> Available Coupons
                    </h5>
                    <small class="text-white-50">Select an eligible coupon to save on your cart</small>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4 bg-light-subtle">
                @if(isset($availableCoupons) && $availableCoupons->count() > 0)
                    @php
                        $eligibleCoupons = $availableCoupons->where('is_eligible', true);
                        $ineligibleCoupons = $availableCoupons->where('is_eligible', false);
                    @endphp

                    <!-- Eligible Coupons Section -->
                    @if($eligibleCoupons->count() > 0)
                        <div class="mb-4">
                            <div class="d-flex align-items-center gap-2 mb-3">
                                <span class="badge bg-success text-white rounded-pill px-3 py-1 fw-bold small text-uppercase"><i class="bi bi-check-circle-fill me-1"></i> Applicable Coupons ({{ $eligibleCoupons->count() }})</span>
                            </div>

                            <div class="d-flex flex-column gap-3">
                                @foreach($eligibleCoupons as $c)
                                    <div class="card border-success border-2 shadow-sm rounded-3 overflow-hidden">
                                        <div class="card-body p-3.5 bg-white">
                                            <div class="d-flex justify-content-between align-items-start mb-2">
                                                <span class="coupon-code-pill" style="background-color: #dcfce7 !important; color: #15803d !important; border-color: #22c55e !important; font-size: 0.95rem;">{{ $c->code }}</span>
                                                <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-3 py-1 fw-bold">Save ₹{{ number_format($c->calculated_discount, 2) }}</span>
                                            </div>
                                            <h6 class="fw-bold text-dark mb-1">{{ $c->name }}</h6>
                                            @if($c->description)
                                                <p class="text-secondary small mb-2">{{ $c->description }}</p>
                                            @endif
                                            <div class="d-flex justify-content-between align-items-center pt-2 border-top">
                                                <small class="text-muted"><i class="bi bi-clock me-1"></i> Valid until {{ $c->valid_until ? $c->valid_until->format('d M Y') : 'End of Month' }}</small>
                                                <button type="button" class="btn btn-sm btn-success rounded-pill px-4 fw-bold shadow-sm" onclick="applyCouponFromModal('{{ $c->code }}')">
                                                    Apply Coupon
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    <!-- Ineligible Coupons Section -->
                    @if($ineligibleCoupons->count() > 0)
                        <div>
                            <div class="d-flex align-items-center gap-2 mb-3">
                                <span class="badge bg-secondary text-white rounded-pill px-3 py-1 fw-bold small text-uppercase"><i class="bi bi-lock-fill me-1"></i> Not Eligible for this Order ({{ $ineligibleCoupons->count() }})</span>
                            </div>

                            <div class="d-flex flex-column gap-3">
                                @foreach($ineligibleCoupons as $c)
                                    <div class="card border-0 shadow-sm rounded-3 overflow-hidden" style="background-color: #f8fafc; border: 1px solid #e2e8f0 !important; opacity: 0.85;">
                                        <div class="card-body p-3.5">
                                            <div class="d-flex justify-content-between align-items-start mb-2">
                                                <span class="coupon-code-pill" style="background-color: #f1f5f9 !important; color: #64748b !important; border-color: #cbd5e1 !important; font-size: 0.95rem;">{{ $c->code }}</span>
                                                <span class="badge bg-light text-secondary border rounded-pill px-2.5 py-1 small">Ineligible</span>
                                            </div>
                                            <h6 class="fw-semibold text-secondary mb-1">{{ $c->name }}</h6>
                                            <div class="alert alert-warning border-0 bg-warning-subtle text-warning-emphasis small p-2 rounded-2 mb-2">
                                                <i class="bi bi-exclamation-circle-fill me-1"></i> {{ $c->ineligibility_reason }}
                                            </div>
                                            <div class="d-flex justify-content-between align-items-center pt-2 border-top">
                                                <small class="text-muted">Min order: ₹{{ number_format($c->minimum_order_amount, 2) }}</small>
                                                <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill px-3 fw-bold" disabled>Cannot Apply</button>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif
                @else
                    <div class="text-center py-5 text-muted">
                        <i class="bi bi-ticket-detailed fs-1 d-block mb-2 text-secondary"></i>
                        No active coupons currently available.
                    </div>
                @endif
            </div>
            <div class="modal-footer bg-white py-2 px-4">
                <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<style>
.cursor-pointer { cursor: pointer; }
.address-modal-option { transition: all 0.2s; }
.address-modal-option:hover { border-color: var(--bs-primary) !important; background-color: var(--bs-primary-bg-subtle); }
.available-coupon-pill { transition: all 0.2s ease; }
.available-coupon-pill:hover { border-color: var(--bs-primary) !important; background-color: #f8fafc; }
.hover-lift { transition: transform 0.2s; }
.hover-lift:hover { transform: translateY(-1px); }
</style>
@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
@endpush

@endsection
