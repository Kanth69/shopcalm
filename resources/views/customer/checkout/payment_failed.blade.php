@extends('layouts.customer')

@section('title', 'Payment Incomplete — #' . $order->order_number)

@push('styles')
<style>
    .payment-failed-card {
        border: 1.5px solid #fed7aa !important;
        background: #ffffff;
        box-shadow: 0 4px 20px rgba(234, 88, 12, 0.06);
        border-radius: 1.1rem !important;
        overflow: hidden;
        margin-bottom: 1.5rem !important;
    }
    .payment-failed-header {
        background: linear-gradient(135deg, #fff7ed 0%, #ffedd5 100%);
        padding: 2.25rem 1.75rem !important;
    }
    .payment-failed-actions {
        padding: 1.85rem 1.5rem !important;
        background: #ffffff;
    }
    .status-avatar {
        width: 52px;
        height: 52px;
        background: linear-gradient(135deg, #ea580c 0%, #c2410c 100%);
        color: #ffffff;
        font-size: 1.4rem;
        box-shadow: 0 6px 16px rgba(234, 88, 12, 0.25);
    }
    .amount-due-box {
        background: #ffffff;
        border: 1.5px solid #fdba74;
        border-radius: 0.95rem;
        padding: 0.95rem 1.4rem;
        display: inline-flex;
        align-items: center;
        gap: 1.5rem;
        box-shadow: 0 2px 8px rgba(234, 88, 12, 0.08);
    }
    
    /* Compact, Proportionate Action Buttons (Small, Refined Size) */
    .btn-retry-cta {
        background: linear-gradient(135deg, #4f46e5 0%, #6366f1 100%);
        border: none;
        color: #ffffff;
        font-size: 0.85rem;
        padding: 0.62rem 1.45rem;
        border-radius: 50rem;
        box-shadow: 0 3px 10px rgba(79, 70, 229, 0.22);
        transition: all 0.15s ease;
    }
    .btn-retry-cta:hover {
        background: linear-gradient(135deg, #4338ca 0%, #4f46e5 100%);
        color: #ffffff;
        transform: translateY(-1px);
        box-shadow: 0 5px 14px rgba(79, 70, 229, 0.3);
    }
    .btn-cod-cta {
        background: #ffffff;
        border: 1.5px solid #cbd5e1;
        color: #1e293b;
        font-size: 0.83rem;
        padding: 0.58rem 1.35rem;
        border-radius: 50rem;
        transition: all 0.15s ease;
    }
    .btn-cod-cta:hover {
        background: #f8fafc;
        border-color: #94a3b8;
        color: #0f172a;
    }
    .btn-cancel-cta {
        background: #fff5f5;
        border: 1.5px solid #fecaca;
        color: #dc2626;
        font-size: 0.82rem;
        padding: 0.52rem 1.25rem;
        border-radius: 50rem;
        transition: all 0.15s ease;
    }
    .btn-cancel-cta:hover {
        background: #fee2e2;
        border-color: #fca5a5;
        color: #b91c1c;
    }

    /* Distinct Section Cards with Guaranteed Spacious Padding */
    .failed-info-card {
        background: #ffffff;
        border: 1.5px solid #e2e8f0 !important;
        border-radius: 1.1rem !important;
        margin-bottom: 1.5rem !important;
        box-shadow: 0 1px 4px rgba(0, 0, 0, 0.03);
        overflow: hidden;
    }
    .failed-delivery-card {
        padding: 1.4rem 1.5rem !important;
        border: 1.5px solid #cbd5e1 !important;
        border-radius: 1.1rem !important;
        background: #ffffff;
        margin-bottom: 1.5rem !important;
        box-shadow: 0 1px 4px rgba(0, 0, 0, 0.03);
        overflow: hidden;
    }
    .failed-item-box {
        padding: 0.85rem 1rem;
        border-radius: 0.85rem;
        background: #ffffff;
        border: 1.5px solid #f1f5f9;
        margin-bottom: 0.75rem;
        transition: border-color 0.15s ease;
    }
    .failed-item-box:last-child {
        margin-bottom: 0;
    }

    /* Price Details Inner Container & Grand Total with Guaranteed Airy Padding */
    .price-breakdown-card {
        background: #f8fafc;
        border: 1.5px solid #e2e8f0;
        border-radius: 1rem;
        padding: 1.35rem 1.45rem !important;
        margin-bottom: 1.25rem !important;
    }
    .price-summary-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 0.35rem 0;
        font-size: 0.88rem;
    }
    .grand-total-row-wrapper {
        border-top: 1.5px dashed #cbd5e1 !important;
        margin-top: 1rem;
        padding-top: 1.15rem !important;
        padding-bottom: 0.25rem !important;
    }

    @media (max-width: 767.98px) {
        .payment-failed-header {
            padding: 1.85rem 1.35rem !important;
        }
        .payment-failed-actions {
            padding: 1.5rem 1.25rem !important;
        }
        .amount-due-box {
            width: 100%;
            justify-content: space-between;
            padding: 0.95rem 1.25rem !important;
            gap: 1rem;
        }
        .status-avatar {
            width: 46px;
            height: 46px;
            font-size: 1.25rem;
        }
        .failed-delivery-card {
            padding: 1.35rem 1.25rem !important;
            margin-bottom: 1.25rem !important;
        }
        .failed-info-card {
            margin-bottom: 1.25rem !important;
        }
        .failed-item-box {
            padding: 0.75rem 0.9rem;
        }
        .price-breakdown-card {
            padding: 1.15rem 1.15rem !important;
            margin-bottom: 1rem !important;
        }
        .price-summary-row {
            padding: 0.3rem 0;
            font-size: 0.84rem;
        }
        .grand-total-row-wrapper {
            margin-top: 0.85rem;
            padding-top: 0.95rem !important;
            padding-bottom: 0.2rem !important;
        }
    }
</style>
@endpush

@section('content')
<div class="container py-3 py-md-4 px-3 px-sm-4" style="max-width: 660px; overflow-x: hidden;">

    <!-- Top Navigation Breadcrumb -->
    <div class="d-flex align-items-center justify-content-between mb-3 px-0.5">
        <a href="{{ route('home') }}" class="text-decoration-none small text-muted fw-semibold d-inline-flex align-items-center gap-1.5">
            <i class="bi bi-arrow-left"></i> Return to Shopping
        </a>
        <span class="badge bg-light text-secondary border font-monospace px-2.5 py-1 rounded-pill" style="font-size: 0.74rem;">
            Order #{{ $order->order_number }}
        </span>
    </div>

    <!-- ============================================================== -->
    <!-- ⚠️ 1. MAIN STATUS & COMPACT RECOVERY ACTIONS CARD               -->
    <!-- ============================================================== -->
    <div class="card border-0 payment-failed-card">
        
        <!-- Header Info (Generously Spaced & Airy) -->
        <div class="text-center payment-failed-header border-bottom" style="border-color: #fed7aa !important;">
            
            <!-- Status Avatar -->
            <div class="rounded-circle d-inline-flex align-items-center justify-content-center mb-3 status-avatar">
                <i class="bi bi-shield-exclamation"></i>
            </div>
            
            <!-- Pill Badge -->
            <div class="mb-2">
                <span class="badge rounded-pill bg-danger-subtle text-danger border border-danger-subtle px-3 py-1 fw-bold font-monospace" style="font-size: 0.72rem; letter-spacing: 0.4px;">
                    PAYMENT INCOMPLETE
                </span>
            </div>

            <!-- Title -->
            <h4 class="fw-bold text-dark mb-2" style="letter-spacing: -0.01em; font-size: 1.35rem;">
                Payment Not Completed
            </h4>
            
            <!-- Explanatory Subtitle -->
            <p class="text-secondary small mb-3.5" style="font-size: 0.88rem; max-width: 480px; margin-left: auto; margin-right: auto; line-height: 1.65;">
                We could not verify your online payment for order <strong class="text-dark font-monospace">#{{ $order->order_number }}</strong>. Don't worry, your ordered items are safely reserved for you.
            </p>

            <!-- Gateway Error Reason (If Present) -->
            @php $lastPayment = $order->payments()->latest()->first(); @endphp
            @if($lastPayment && !empty($lastPayment->gateway_message) && !str_contains(strtolower($lastPayment->gateway_message), 'initiated'))
            <div class="mb-3.5 px-3.5 py-2 rounded-4 d-inline-flex align-items-center gap-2 bg-danger bg-opacity-10 border border-danger border-opacity-25 text-danger text-start" style="font-size: 0.78rem; line-height: 1.45; max-width: 100%; word-break: break-word;">
                <i class="bi bi-info-circle-fill flex-shrink-0"></i>
                <span class="fw-semibold">Reason: {{ $lastPayment->gateway_message }}</span>
            </div>
            <br>
            @endif

            <!-- Amount Due Box (Generous Breathing Space) -->
            <div class="amount-due-box text-start mt-1">
                <div>
                    <span class="text-muted d-block text-uppercase fw-semibold" style="font-size: 0.68rem; letter-spacing: 0.5px;">Amount Payable</span>
                    <small class="text-secondary" style="font-size: 0.76rem;">Inclusive of all taxes &amp; fees</small>
                </div>
                <div class="text-end">
                    <span class="fs-4 fw-bolder text-dark font-monospace">₹{{ number_format($order->total_amount, 2) }}</span>
                </div>
            </div>
        </div>

        <!-- Recovery Action Buttons (Compact, Small & Centered with Breathing Space) -->
        <div class="payment-failed-actions text-center">
            <div class="d-flex flex-column align-items-center gap-3 mx-auto" style="max-width: 330px;">
                
                <!-- Action 1: Retry Online Payment (Primary Compact Button) -->
                <div class="w-100">
                    <button type="button" id="btn-retry-payment" class="btn btn-retry-cta fw-bold w-100 d-inline-flex align-items-center justify-content-center gap-1.5">
                        <i class="bi bi-arrow-repeat fs-6"></i>
                        <span>Retry Online Payment</span>
                    </button>
                    <small class="text-muted d-block mt-1.5" style="font-size: 0.72rem;">
                        <i class="bi bi-shield-check text-success me-1"></i>Supports UPI, Cards &amp; NetBanking
                    </small>
                </div>

                <!-- Action 2: Switch to Cash on Delivery (COD) -->
                {{-- Only shown if Admin has enabled COD for this pincode AND order is not already COD --}}
                @if(isset($isCodAvailable) && $isCodAvailable && $order->payment_method !== 'cod')
                    <form action="{{ route('checkout.switch_to_cod', $order) }}" method="POST" class="w-100 m-0">
                        @csrf
                        <button type="submit" class="btn btn-cod-cta fw-bold w-100 d-inline-flex align-items-center justify-content-center gap-1.5">
                            <i class="bi bi-cash-stack text-success"></i>
                            <span>Switch to Cash on Delivery</span>
                            @if(isset($resolvedCodFee) && $resolvedCodFee > 0)
                                <span class="badge bg-light text-secondary border font-monospace ms-1 fw-normal" style="font-size: 0.7rem;">+ ₹{{ number_format($resolvedCodFee, 2) }}</span>
                            @endif
                        </button>
                    </form>
                @endif

                <!-- Action 3: Cancel Order (Small, Subtle & Clean) -->
                @if(in_array($order->status, ['pending', 'failed', 'confirmed']))
                <div class="w-100">
                    <button type="button" id="btn-cancel-pending-order" onclick="handleCancelPendingOrder()" class="btn btn-cancel-cta fw-semibold w-100 d-inline-flex align-items-center justify-content-center gap-1.5">
                        <i class="bi bi-x-circle"></i>
                        <span>Cancel Order</span>
                    </button>
                    <small class="text-muted d-block mt-1" style="font-size: 0.7rem;">100% free cancellation with zero charges</small>
                </div>
                @endif

                <!-- Secondary Link: View in My Orders -->
                <div class="pt-2.5 border-top w-100 mt-1" style="border-color: #f1f5f9 !important;">
                    <a href="{{ route('account.orders.show', $order) }}" class="text-decoration-none text-secondary small fw-semibold d-inline-flex align-items-center gap-1.5" style="font-size: 0.8rem;">
                        <i class="bi bi-box-seam text-primary"></i> View Order in My Account
                    </a>
                </div>

            </div>
        </div>
    </div>

    <!-- ============================================================== -->
    <!-- 📍 2. DELIVERING TO (DEDICATED CARD WITH GUARANTEED PADDING)    -->
    <!-- ============================================================== -->
    <div class="failed-delivery-card card border-0 shadow-xs bg-white">
        <div class="d-flex align-items-start gap-3">
            <div class="rounded-circle text-success bg-success bg-opacity-10 d-flex align-items-center justify-content-center flex-shrink-0 mt-0.5" style="width: 36px; height: 36px;">
                <i class="bi bi-geo-alt-fill fs-5"></i>
            </div>
            <div class="min-w-0 flex-grow-1">
                <span class="text-muted small d-block mb-1.5 fw-semibold text-uppercase" style="font-size: 0.7rem; line-height: 1.2; letter-spacing: 0.5px;">DELIVERING TO</span>
                <div class="text-dark fw-bold mb-1" style="font-size: 0.94rem;">
                    {{ $order->shipping_name }} <span class="text-muted fw-normal font-monospace small ms-1">(📞 {{ $order->shipping_phone }})</span>
                </div>
                <p class="text-secondary mb-0" style="line-height: 1.55; font-size: 0.85rem; word-break: break-word;">
                    {{ $order->shipping_address }}, {{ $order->shipping_city }}, {{ $order->shipping_state }} <span class="font-monospace fw-bold text-dark text-nowrap">- {{ $order->shipping_zip }}</span>
                </p>
            </div>
        </div>
    </div>

    <!-- ============================================================== -->
    <!-- 📦 3. ITEMS IN ORDER (OVERFLOW-SAFE, CRISP & AIRY)             -->
    <!-- ============================================================== -->
    <div class="card border-0 shadow-xs failed-info-card">
        <div class="card-header bg-white py-3 px-3.5 px-sm-4 border-bottom d-flex align-items-center justify-content-between flex-wrap gap-2">
            <div class="d-flex align-items-center gap-2">
                <div class="rounded-circle d-flex align-items-center justify-content-center text-primary flex-shrink-0" style="width: 32px; height: 32px; background: rgba(79, 70, 229, 0.08); font-size: 0.92rem;">
                    <i class="bi bi-bag-check-fill"></i>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <h6 class="fw-bold mb-0 text-dark" style="font-size: 0.9rem;">Items in Order</h6>
                    <span class="badge bg-dark text-white rounded-pill px-2.5 py-0.5 fw-bold font-monospace" style="font-size: 0.72rem;">
                        {{ $order->items->sum('quantity') }}
                    </span>
                </div>
            </div>
            <span class="badge bg-light text-secondary border font-monospace px-2.5 py-1 rounded-pill" style="font-size: 0.72rem;">
                #{{ $order->order_number }}
            </span>
        </div>

        <div class="card-body p-3 p-sm-3.5 bg-white">
            <div class="d-flex flex-column gap-2.5">
                @foreach($order->items as $item)
                @php
                    $product = $item->product;
                    $origPrice = (float) ($item->original_price > 0 ? $item->original_price : ($product->price ?? $item->unit_price));
                    $unitPrice = (float) $item->unit_price;
                    $hasDiscount = ($origPrice > $unitPrice);
                    $itemDiscount = $hasDiscount ? ($origPrice - $unitPrice) : 0;
                    $discountPct = ($hasDiscount && $origPrice > 0) ? round(($itemDiscount / $origPrice) * 100) : 0;
                @endphp
                <div class="failed-item-box">
                    <div class="d-flex align-items-start gap-3">
                        @if($product && $product->main_image)
                            <img src="{{ asset('storage/' . $product->main_image) }}" alt="" class="rounded-3 border flex-shrink-0 bg-white" width="50" height="50" style="object-fit: contain;">
                        @else
                            <div class="rounded-3 bg-light border d-flex align-items-center justify-content-center text-muted flex-shrink-0" style="width: 50px; height: 50px;">
                                <i class="bi bi-image opacity-25 fs-5"></i>
                            </div>
                        @endif
                        <div class="min-w-0 flex-grow-1">
                            <h6 class="fw-bold text-dark mb-1 text-truncate" style="font-size: 0.9rem;" title="{{ $item->product_name }}">
                                {{ $item->product_name }}
                            </h6>

                            <div class="d-flex align-items-center gap-1.5 flex-wrap mb-1.5">
                                <span class="badge bg-light text-secondary border px-2 py-0.5 rounded-pill" style="font-size: 0.68rem;">
                                    Qty: {{ $item->quantity }}
                                </span>
                                @if(!empty($item->selected_option))
                                <span class="badge bg-light text-secondary border px-2 py-0.5 rounded-pill" style="font-size: 0.68rem;">
                                    {{ $item->selected_option }}
                                </span>
                                @endif
                                @if($item->quantity > 1)
                                <span class="text-muted font-monospace small" style="font-size: 0.72rem;">
                                    (₹{{ number_format($unitPrice, 2) }} each)
                                </span>
                                @endif
                            </div>

                            <div class="d-flex align-items-center gap-2 flex-wrap">
                                <span class="fw-bolder text-dark font-monospace fs-6">
                                    ₹{{ number_format($item->total_price, 2) }}
                                </span>
                                @if($hasDiscount)
                                <span class="text-muted text-decoration-line-through font-monospace small" style="font-size: 0.78rem;">
                                    ₹{{ number_format($origPrice * $item->quantity, 2) }}
                                </span>
                                <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-2 py-0.5 fw-bold font-monospace" style="font-size: 0.68rem;">
                                    {{ $discountPct }}% OFF
                                </span>
                                <span class="text-success fw-semibold font-monospace small" style="font-size: 0.74rem;">
                                    Saved ₹{{ number_format($itemDiscount * $item->quantity, 2) }}
                                </span>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>

    <!-- ============================================================== -->
    <!-- 🧾 4. PRICE DETAILS (DISTINCT SPACIOUS CARD)                   -->
    <!-- ============================================================== -->
    @php
        $totalMrp = (float) $order->items->sum(function($it) {
            $p = (float) ($it->original_price > 0 ? $it->original_price : ($it->product?->price ?? $it->unit_price));
            return $p * $it->quantity;
        });
        $orderSubtotal = (float) ($order->subtotal_amount > 0 ? $order->subtotal_amount : $order->items->sum('total_price'));
        $mrpDiscount = max(0, $totalMrp - $orderSubtotal);
        $couponDiscount = (float) ($order->coupon_discount_amount ?? 0);
        $walletUsed = (float) ($order->wallet_amount_used ?? 0);
        $shippingCharge = (float) ($order->shipping_charge ?? 0);
        $codFee = (float) ($order->cod_fee ?? 0);
        $grandTotal = (float) $order->total_amount;
    @endphp
    <div class="card border-0 shadow-xs mb-4 failed-info-card">
        <div class="card-header bg-white py-3 px-3.5 px-sm-4 border-bottom d-flex align-items-center justify-content-between flex-wrap gap-2">
            <h6 class="fw-bold mb-0 text-dark d-flex align-items-center gap-2" style="font-size: 0.9rem;">
                <i class="bi bi-receipt text-primary fs-5"></i> Price Details
            </h6>
            <span class="badge bg-light text-secondary border font-monospace px-2.5 py-1 rounded-pill" style="font-size: 0.72rem;">
                {{ $order->items->sum('quantity') }} {{ Str::plural('Item', $order->items->sum('quantity')) }}
            </span>
        </div>
        <div class="card-body p-3.5 p-sm-4 bg-white">
            <div class="price-breakdown-card">
                
                @if($mrpDiscount > 0)
                <div class="price-summary-row text-secondary mb-1">
                    <span>Total MRP:</span>
                    <span class="font-monospace text-dark fw-semibold">₹{{ number_format($totalMrp, 2) }}</span>
                </div>

                <div class="price-summary-row text-success mb-1">
                    <span class="d-inline-flex align-items-center gap-1.5"><i class="bi bi-tag-fill"></i> Discount on MRP:</span>
                    <span class="font-monospace fw-bold">- ₹{{ number_format($mrpDiscount, 2) }}</span>
                </div>
                @endif

                <div class="price-summary-row text-secondary mb-1">
                    <span>Cart Subtotal:</span>
                    <span class="font-monospace text-dark fw-semibold">₹{{ number_format($orderSubtotal, 2) }}</span>
                </div>

                @if($couponDiscount > 0)
                <div class="price-summary-row text-success mb-1">
                    <span class="d-inline-flex align-items-center gap-1.5"><i class="bi bi-ticket-perforated-fill"></i> Coupon Discount:</span>
                    <span class="font-monospace fw-bold">- ₹{{ number_format($couponDiscount, 2) }}</span>
                </div>
                @endif

                @if($walletUsed > 0)
                <div class="price-summary-row text-success mb-1">
                    <span class="d-inline-flex align-items-center gap-1.5"><i class="bi bi-wallet2"></i> Wallet Credits:</span>
                    <span class="font-monospace fw-bold">- ₹{{ number_format($walletUsed, 2) }}</span>
                </div>
                @endif

                <div class="price-summary-row text-secondary mb-1">
                    <span>Delivery Fee:</span>
                    <span>
                        @if($shippingCharge > 0)
                            <span class="font-monospace text-dark fw-bold">+ ₹{{ number_format($shippingCharge, 2) }}</span>
                        @else
                            <span class="badge bg-success bg-opacity-10 text-success fw-bold rounded-pill px-2.5 py-0.5" style="font-size: 0.72rem;">FREE</span>
                        @endif
                    </span>
                </div>

                @if($codFee > 0)
                <div class="price-summary-row text-secondary mb-1">
                    <span>COD Handling Fee:</span>
                    <span class="font-monospace text-dark fw-semibold">+ ₹{{ number_format($codFee, 2) }}</span>
                </div>
                @endif

                <div class="grand-total-row-wrapper d-flex justify-content-between align-items-center">
                    <div class="pe-2">
                        <span class="fw-bold text-dark d-block" style="font-size: 0.96rem;">Grand Total:</span>
                        <small class="text-muted d-inline-flex align-items-center gap-1" style="font-size: 0.72rem;">
                            <i class="bi bi-shield-check text-success"></i> Inclusive of all taxes
                        </small>
                    </div>
                    <div class="text-end ps-2">
                        <span class="fs-4 fw-bolder text-dark font-monospace text-nowrap">₹{{ number_format($grandTotal, 2) }}</span>
                    </div>
                </div>
            </div>

            <!-- Trust Badges Strip -->
            <div class="d-flex align-items-center justify-content-center gap-3 pt-2 text-secondary small flex-wrap" style="font-size: 0.76rem;">
                <span class="d-inline-flex align-items-center gap-1"><i class="bi bi-shield-lock-fill text-success"></i> 100% Safe Payments</span>
                <span class="text-muted opacity-50">•</span>
                <span class="d-inline-flex align-items-center gap-1"><i class="bi bi-arrow-repeat text-primary"></i> Fast Verification</span>
                <span class="text-muted opacity-50">•</span>
                <span class="d-inline-flex align-items-center gap-1"><i class="bi bi-truck text-info"></i> Express Dispatch</span>
            </div>
        </div>
    </div>

</div>

{{-- Razorpay SDK Loader & SweetAlert2 --}}
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://checkout.razorpay.com/v1/checkout.js"></script>
<script>
function handleCancelPendingOrder() {
    Swal.fire({
        title: 'Cancel Order #{{ $order->order_number }}?',
        text: 'Cancelling this unpaid pending order is 100% free. Once cancelled, you can place a new order anytime.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#ef4444',
        cancelButtonColor: '#64748b',
        confirmButtonText: 'Yes, Cancel Order',
        cancelButtonText: 'Keep Order'
    }).then((result) => {
        if (!result.isConfirmed) return;

        const btnCancel = document.getElementById('btn-cancel-pending-order');
        if (btnCancel) {
            btnCancel.disabled = true;
            btnCancel.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Cancelling...';
        }

        fetch("{{ route('account.orders.cancel', $order) }}", {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                "X-CSRF-TOKEN": "{{ csrf_token() }}",
                "Accept": "application/json"
            },
            body: JSON.stringify({
                cancellation_reason: "Customer cancelled pending online order",
                refund_method: "wallet"
            })
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                Swal.fire({
                    icon: 'success',
                    title: 'Order Cancelled',
                    text: data.message || 'Your pending order has been cancelled.',
                    confirmButtonColor: '#10b981'
                }).then(() => {
                    window.location.href = "{{ route('account.orders.show', $order) }}";
                });
            } else {
                if (btnCancel) {
                    btnCancel.disabled = false;
                    btnCancel.innerHTML = '<i class="bi bi-x-circle"></i><span>Cancel Order</span>';
                }
                Swal.fire('Error', data.message || 'Could not cancel order.', 'error');
            }
        })
        .catch(err => {
            console.error("Cancel order error:", err);
            if (btnCancel) {
                btnCancel.disabled = false;
                btnCancel.innerHTML = '<i class="bi bi-x-circle"></i><span>Cancel Order</span>';
            }
            Swal.fire('Error', 'Network error while cancelling order.', 'error');
        });
    });
}

document.addEventListener('DOMContentLoaded', function() {
    const btnRetry = document.getElementById('btn-retry-payment');
    if (!btnRetry) return;

    btnRetry.addEventListener('click', async function() {
        btnRetry.disabled = true;
        btnRetry.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Initializing...';

        try {
            const response = await fetch("{{ route('checkout.retry_payment', $order) }}", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "X-CSRF-TOKEN": "{{ csrf_token() }}",
                    "Accept": "application/json"
                }
            });

            const data = await response.json();

            if (data.success && data.razorpay_order_id) {
                const options = {
                    key: data.key_id,
                    amount: data.amount,
                    currency: data.currency || "INR",
                    name: data.name || "ShopCalm",
                    description: data.description || "Order #" + (data.order_number || ""),
                    order_id: data.razorpay_order_id,
                    prefill: data.prefill || {},
                    theme: { color: "#4f46e5" },
                    handler: function (res) {
                        btnRetry.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Verifying...';
                        fetch("{{ route('checkout.razorpay.verify') }}", {
                            method: "POST",
                            headers: {
                                "Content-Type": "application/json",
                                "X-CSRF-TOKEN": "{{ csrf_token() }}",
                                "Accept": "application/json"
                            },
                            body: JSON.stringify({
                                order_id: {{ $order->id }},
                                razorpay_order_id: res.razorpay_order_id,
                                razorpay_payment_id: res.razorpay_payment_id,
                                razorpay_signature: res.razorpay_signature
                            })
                        })
                        .then(r => r.json())
                        .then(verifyData => {
                            if (verifyData.success && verifyData.redirect_url) {
                                window.location.href = verifyData.redirect_url;
                            } else {
                                btnRetry.disabled = false;
                                btnRetry.innerHTML = '<i class="bi bi-arrow-repeat"></i><span>Retry Online Payment</span>';
                                Swal.fire('Verification Failed', verifyData.message || 'Payment signature verification failed.', 'error');
                            }
                        })
                        .catch(err => {
                            console.error("Verify retry error:", err);
                            btnRetry.disabled = false;
                            btnRetry.innerHTML = '<i class="bi bi-arrow-repeat"></i><span>Retry Online Payment</span>';
                            Swal.fire('Error', 'Payment verification error. Please refresh the page.', 'error');
                        });
                    },
                    modal: {
                        ondismiss: function () {
                            btnRetry.disabled = false;
                            btnRetry.innerHTML = '<i class="bi bi-arrow-repeat"></i><span>Retry Online Payment</span>';
                        }
                    }
                };
                const rzp = new Razorpay(options);
                rzp.on('payment.failed', function (resp) {
                    btnRetry.disabled = false;
                    btnRetry.innerHTML = '<i class="bi bi-arrow-repeat"></i><span>Retry Online Payment</span>';
                    Swal.fire({
                        icon: 'error',
                        title: 'Payment Failed',
                        text: (resp && resp.error && resp.error.description) ? resp.error.description : 'Payment authorization failed. Please try again.'
                    });
                });
                rzp.open();
            } else {
                Swal.fire('Gateway Notice', data.message || "Failed to initialize payment retry.", 'error');
                btnRetry.disabled = false;
                btnRetry.innerHTML = '<i class="bi bi-arrow-repeat"></i><span>Retry Online Payment</span>';
            }
        } catch (err) {
            console.error("Retry payment error:", err);
            Swal.fire('Error', "Error communicating with payment gateway. Please try again.", 'error');
            btnRetry.disabled = false;
            btnRetry.innerHTML = '<i class="bi bi-arrow-repeat"></i><span>Retry Online Payment</span>';
        }
    });
});
</script>
@endsection
