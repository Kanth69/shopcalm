@extends('delivery.layouts.app')

@section('title', 'Deliver #' . $order->order_number)

@push('styles')
<style>
    .order-task-card {
        background: #ffffff;
        border-radius: 1rem;
        border: 1px solid #e2e8f0;
        margin-bottom: 0.75rem;
        overflow: hidden;
    }

    .btn-rider-action {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 0.4rem;
        height: 44px;
        border-radius: 0.65rem;
        font-weight: 700;
        font-size: 0.85rem;
        text-decoration: none;
        border: 1px solid transparent;
        transition: transform 0.1s ease;
    }
    .btn-rider-action:active {
        transform: scale(0.97);
    }
    .btn-action-nav {
        background: #eff6ff;
        color: #1d4ed8;
        border-color: #bfdbfe;
    }
    .btn-action-phone {
        background: #ecfdf5;
        color: #047857;
        border-color: #a7f3d0;
    }

    /* 4-Box OTP input */
    .otp-grid {
        display: flex;
        gap: 0.6rem;
        justify-content: center;
        margin: 1rem 0;
    }
    .otp-box {
        width: 56px;
        height: 60px;
        border: 2px solid #cbd5e1;
        border-radius: 0.85rem;
        text-align: center;
        font-size: 1.6rem;
        font-weight: 800;
        font-family: 'JetBrains Mono', monospace;
        background: #f8fafc;
        color: #0f172a;
        transition: all 0.2s ease;
    }
    .otp-box:focus {
        border-color: #4f46e5;
        background: #ffffff;
        outline: none;
        box-shadow: 0 0 0 4px rgba(79, 70, 229, 0.18);
    }

    .btn-confirm-delivery {
        width: 100%;
        height: 52px;
        background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%);
        color: #ffffff;
        border: none;
        border-radius: 0.85rem;
        font-weight: 800;
        font-size: 0.95rem;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 0.5rem;
        box-shadow: 0 4px 16px rgba(79, 70, 229, 0.25);
    }
    .btn-confirm-delivery:active {
        transform: scale(0.98);
    }

    .btn-report-issue {
        width: 100%;
        height: 44px;
        background: #fff1f2;
        color: #e11d48;
        border: 1px solid #fecdd3;
        border-radius: 0.75rem;
        font-weight: 700;
        font-size: 0.85rem;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 0.4rem;
        transition: transform 0.1s ease;
    }
    .btn-report-issue:active {
        transform: scale(0.98);
    }

    /* Mobile Option Tile Radio */
    .issue-option-label {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        padding: 0.75rem 1rem;
        border: 1.5px solid #e2e8f0;
        border-radius: 0.75rem;
        cursor: pointer;
        background: #ffffff;
        transition: all 0.15s ease;
        margin-bottom: 0.5rem;
        font-size: 0.85rem;
        font-weight: 600;
        color: #1e293b;
    }
    .issue-option-label:hover, .issue-option-label:active {
        border-color: #e11d48;
        background: #fff1f2;
    }
    .issue-option-radio:checked + .issue-option-label {
        border-color: #e11d48;
        background: #fff1f2;
        color: #9f1239;
    }
</style>
@endpush

@section('content')

<!-- Back and Order ID -->
<div class="d-flex align-items-center justify-content-between mb-2.5">
    <a href="{{ route('delivery.dashboard') }}" class="btn btn-sm btn-white border bg-white rounded-pill px-3 py-1 text-dark fw-bold" style="font-size: 0.78rem;">
        <i class="bi bi-arrow-left me-1"></i> Run-Sheet
    </a>
    <span class="badge bg-white text-secondary border rounded-pill px-2.5 py-1 font-monospace fw-bold" style="font-size: 0.75rem;">
        #{{ $order->order_number }}
    </span>
</div>

@if($order->fulfillment?->hasActiveDeliveryIssue())
    <div class="alert alert-warning border-0 rounded-4 p-3.5 mb-3 shadow-xs d-flex align-items-start gap-2.5" style="background: #fffbeb; border: 1.5px solid #fde68a !important;">
        <div class="rounded-circle bg-warning text-dark d-flex align-items-center justify-content-center flex-shrink-0" style="width: 28px; height: 28px; font-size: 0.85rem;">
            <i class="bi bi-exclamation-triangle-fill"></i>
        </div>
        <div>
            <div class="fw-bolder text-dark small" style="font-size: 0.85rem;">Delivery Issue Logged &bull; On Hold</div>
            <div class="text-secondary small mt-0.5" style="font-size: 0.76rem; line-height: 1.35;">
                Reported: <strong>{{ $order->fulfillment->delivery_issue }}</strong>. Warehouse Order Manager is contacting the customer. If customer arrives, enter OTP below to complete.
            </div>
        </div>
    </div>
@endif

<!-- 1. Payment Requirement Bar -->
@if($order->payment_status === 'paid')
    <div id="paymentRequirementCard" class="order-task-card p-3 border-start border-4 border-success" style="border-left-width: 5px !important;">
        <div class="d-flex align-items-center justify-content-between">
            <div>
                <div class="text-secondary small fw-bold text-uppercase" style="font-size: 0.65rem;">Payment Requirement</div>
                <div class="fw-bolder text-success" style="font-size: 0.95rem;">
                    ✅ Paid {{ $order->payment_method === 'doorstep_upi' ? 'via Doorstep UPI' : 'Online' }} &bull; Collect ₹0
                </div>
                <div class="text-secondary small" style="font-size: 0.72rem;">Do not collect any cash from customer</div>
            </div>
            <div class="text-end">
                <span class="badge bg-success rounded-pill px-2.5 py-1 fw-bold">PAID</span>
            </div>
        </div>
    </div>
@else
    <div id="paymentRequirementCard" class="order-task-card p-3 border-start border-4 border-warning" style="border-left-width: 5px !important;">
        <div class="d-flex align-items-center justify-content-between mb-2.5">
            <div>
                <div class="text-secondary small fw-bold text-uppercase" style="font-size: 0.65rem;">Payment Requirement</div>
                <div id="paymentStatusHeading" class="fw-bolder text-dark" style="font-size: 0.95rem;">💵 Payment Pending from Customer</div>
                <div id="paymentStatusSub" class="text-secondary small" style="font-size: 0.72rem;">Customer can pay with physical Cash or UPI QR</div>
            </div>
            <div class="text-end">
                <div class="h4 fw-bolder text-dark mb-0 font-monospace" style="font-size: 1.35rem;">₹{{ number_format($order->total_amount, 2) }}</div>
            </div>
        </div>

        <!-- 2 Payment Choice Options for Rider -->
        <div class="row g-2 pt-2 border-top" id="paymentOptionsRow">
            <div class="col-6">
                <div class="p-2 rounded-3 border text-center" style="background: #fffbeb; border-color: #fde68a !important;">
                    <div class="fw-bold text-dark small" style="font-size: 0.78rem;"><i class="bi bi-cash text-warning me-1"></i> Cash COD</div>
                    <div class="text-muted" style="font-size: 0.68rem;">Collect exact notes</div>
                </div>
            </div>
            <div class="col-6">
                <button type="button" class="btn btn-sm btn-primary w-100 fw-bold py-2 rounded-3 d-flex flex-column align-items-center justify-content-center shadow-xs" 
                        data-bs-toggle="modal" data-bs-target="#modalDoorstepUpi" 
                        style="background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%); border: none; min-height: 44px;">
                    <span style="font-size: 0.78rem;"><i class="bi bi-qr-code-scan me-1"></i> Collect via UPI</span>
                    <span class="text-white-50" style="font-size: 0.65rem;">GPay / PhonePe / Paytm</span>
                </button>
            </div>
        </div>
    </div>
@endif

<!-- 2. Customer & Address Card with Fast Action Buttons -->
<div class="order-task-card p-3.5 mb-3">
    <!-- Customer Header -->
    <div class="d-flex align-items-center justify-content-between mb-3">
        <div>
            <h5 class="fw-bolder text-dark mb-0.5" style="font-size: 1.1rem; letter-spacing: -0.02em;">{{ $order->shipping_name }}</h5>
            <div class="text-secondary small font-monospace" style="font-size: 0.74rem;">
                <i class="bi bi-telephone-fill text-success me-1"></i>{{ $order->shipping_phone }}
            </div>
        </div>
        <span class="badge bg-light text-secondary border rounded-pill px-3 py-1.5 fw-semibold" style="font-size: 0.7rem;">
            <i class="bi bi-lightning-charge-fill text-warning me-1"></i>{{ $order->delivery_slot ?? 'Express' }}
        </span>
    </div>

    <!-- Address Box with Generous Padding & Hierarchy -->
    <div class="p-3 rounded-3 bg-light border mb-3 text-dark shadow-xs" style="font-size: 0.86rem; line-height: 1.45;">
        <div class="text-secondary small fw-bold text-uppercase mb-1" style="font-size: 0.65rem; letter-spacing: 0.04em;">
            <i class="bi bi-geo-alt-fill text-danger me-1"></i> Delivery Address
        </div>
        <div class="fw-bold text-dark mb-1">
            {{ $order->shipping_address }}
        </div>
        <div class="text-secondary" style="font-size: 0.8rem;">
            {{ $order->shipping_city }} &bull; PIN: <strong>{{ $order->shipping_zip }}</strong>
        </div>
    </div>

    <!-- Delivery Notes / Customer Instructions -->
    @if(!empty($order->notes))
    <div class="p-3 rounded-3 mb-3 border d-flex align-items-start gap-2.5 shadow-xs" style="background: #fff7ed; border: 1.5px solid #fdba74 !important;">
        <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0 text-white" style="width: 28px; height: 28px; background: #ea580c; font-size: 0.8rem;">
            <i class="bi bi-sticky-fill"></i>
        </div>
        <div class="flex-grow-1 min-w-0">
            <div class="fw-bold text-uppercase mb-0.5" style="font-size: 0.72rem; color: #c2410c; letter-spacing: 0.03em;">
                <i class="bi bi-chat-left-text-fill me-1"></i> Customer Delivery Instructions:
            </div>
            <div class="fw-bolder text-dark" style="font-size: 0.88rem; line-height: 1.35;">
                "{{ $order->notes }}"
            </div>
        </div>
    </div>
    @else
    <div class="p-2.5 px-3 rounded-3 mb-3 border d-flex align-items-center gap-2 text-muted shadow-xs" style="background: #f8fafc; font-size: 0.76rem;">
        <i class="bi bi-info-circle text-secondary"></i>
        <span>No special delivery instructions provided.</span>
    </div>
    @endif

    <!-- 2 Action Buttons (Maps, Call) -->
    <div class="row g-2.5 pt-1">
        <div class="col-6">
            <a href="https://www.google.com/maps/dir/?api=1&destination={{ urlencode($order->shipping_address . ', ' . $order->shipping_city . ' ' . $order->shipping_zip) }}" 
               target="_blank" class="btn-rider-action btn-action-nav w-100 shadow-xs" style="height: 46px; font-size: 0.85rem;">
                <i class="bi bi-map-fill"></i> Google Maps
            </a>
        </div>
        <div class="col-6">
            <a href="tel:{{ $order->shipping_phone }}" class="btn-rider-action btn-action-phone w-100 shadow-xs" style="height: 46px; font-size: 0.85rem;">
                <i class="bi bi-telephone-fill"></i> Call Customer
            </a>
        </div>
    </div>
</div>

<!-- 3. Items To Handover -->
<div class="order-task-card p-3">
    <div class="d-flex align-items-center justify-content-between mb-2">
        <div class="fw-bold text-dark small" style="font-size: 0.8rem; text-transform: uppercase;">
            Items in Package ({{ $order->items->sum('quantity') }} total)
        </div>
    </div>

    <div class="divide-y">
        @foreach($order->items as $item)
            <div class="d-flex align-items-center justify-content-between py-2 {{ !$loop->last ? 'border-bottom' : '' }}">
                <div class="d-flex align-items-center gap-2 overflow-hidden">
                    @if($item->product && $item->product->main_image)
                        <img src="{{ asset('storage/' . $item->product->main_image) }}" alt="{{ $item->product_name }}" 
                             class="rounded-2 border object-fit-cover flex-shrink-0" style="width: 36px; height: 36px;">
                    @else
                        <div class="rounded-2 bg-light border d-flex align-items-center justify-content-center text-secondary flex-shrink-0" style="width: 36px; height: 36px;">
                            <i class="bi bi-box"></i>
                        </div>
                    @endif
                    <div class="overflow-hidden">
                        <div class="fw-bold text-dark small text-truncate" style="font-size: 0.82rem;">{{ $item->product_name }}</div>
                        <div class="text-secondary small font-monospace" style="font-size: 0.68rem;">SKU: {{ $item->product?->sku ?? 'N/A' }}</div>
                    </div>
                </div>
                <span class="badge bg-light text-dark border px-2 py-1 rounded-pill fw-bold ms-2" style="font-size: 0.72rem;">
                    {{ $item->quantity }}x
                </span>
            </div>
        @endforeach
    </div>
</div>

<!-- 4. OTP Verification & Handover Card -->
@if($order->status !== 'delivered')
    <div class="order-task-card p-3.5 text-center">
        <div class="fw-bolder text-dark mb-0.5" style="font-size: 0.95rem;">Enter Customer 4-Digit OTP</div>
        <div class="text-secondary small mb-2" style="font-size: 0.75rem;">
            Ask the customer for the code sent to their phone
        </div>

        <form id="formOrderComplete" action="{{ route('delivery.orders.complete', $order) }}" method="POST">
            @csrf
            
            <div class="otp-grid">
                <input type="text" maxlength="1" class="otp-box" id="box1" inputmode="numeric" autocomplete="one-time-code">
                <input type="text" maxlength="1" class="otp-box" id="box2" inputmode="numeric">
                <input type="text" maxlength="1" class="otp-box" id="box3" inputmode="numeric">
                <input type="text" maxlength="1" class="otp-box" id="box4" inputmode="numeric">
            </div>

            <input type="hidden" name="otp" id="finalOtpValue">

            <!-- Primary Action: Complete Delivery -->
            <button type="submit" id="btnSubmitOtp" class="btn-confirm-delivery mt-2">
                <i class="bi bi-check2-circle fs-5"></i>
                <span>Complete Delivery</span>
            </button>
        </form>

        <!-- Dedicated Mobile Report Issue Button (Perfect Alignment) -->
        <div class="pt-3 mt-3 border-top">
            <button type="button" class="btn-report-issue" data-bs-toggle="modal" data-bs-target="#modalReportIssue">
                <i class="bi bi-exclamation-octagon-fill"></i>
                <span>Cannot Deliver? Report Issue</span>
            </button>
        </div>
    </div>
@else
    <div class="order-task-card p-4 text-center">
        <div class="rounded-circle d-inline-flex align-items-center justify-content-center text-success mb-2" style="width: 48px; height: 48px; background: #ecfdf5; font-size: 1.6rem;">
            <i class="bi bi-check2"></i>
        </div>
        <h5 class="fw-bolder text-dark mb-1">Package Delivered</h5>
        <div class="text-secondary small">Delivered on {{ $order->delivered_at?->format('d M Y, h:i A') }}</div>
    </div>
@endif

<!-- Modal: Doorstep UPI Live QR Collection & Auto-Detection -->
<div class="modal fade" id="modalDoorstepUpi" tabindex="-1" aria-labelledby="modalDoorstepUpiLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content border-0 rounded-4 shadow-lg overflow-hidden">
            <div class="modal-header py-3 px-3.5 border-bottom text-white" style="background: linear-gradient(135deg, #1e1b4b 0%, #312e81 100%);">
                <div class="d-flex align-items-center gap-2">
                    <i class="bi bi-qr-code-scan text-warning fs-5"></i>
                    <div>
                        <h6 class="modal-title fw-bold text-white mb-0" id="modalDoorstepUpiLabel" style="font-size: 0.92rem;">
                            Doorstep UPI Payment
                        </h6>
                        <small class="text-white-50" style="font-size: 0.68rem;">Scan with GPay / PhonePe / Paytm</small>
                    </div>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close" style="font-size: 0.75rem;"></button>
            </div>

            <div class="modal-body p-3.5 text-center">
                <!-- Amount Pill -->
                <div class="bg-light p-2 rounded-3 border mb-3">
                    <div class="text-muted small text-uppercase fw-bold" style="font-size: 0.65rem;">Payable Amount</div>
                    <div class="h3 fw-extrabold text-primary mb-0 font-monospace">₹{{ number_format($order->total_amount, 2) }}</div>
                </div>

                <!-- QR Code Display Container -->
                <div id="upiQrArea" class="position-relative d-inline-block p-2.5 bg-white border rounded-4 shadow-xs mb-2">
                    @php
                        $upiIntent = "upi://pay?pa=shopcalm@razorpay&pn=ShopCalm&am=" . $order->total_amount . "&cu=INR&tn=" . urlencode("Order #" . $order->order_number);
                        $qrUrl = "https://api.qrserver.com/v1/create-qr-code/?size=220x220&data=" . urlencode($upiIntent);
                    @endphp
                    <img id="upiQrImage" src="{{ $qrUrl }}" alt="Razorpay UPI QR Code" class="img-fluid rounded-3" style="width: 200px; height: 200px;">
                    
                    <!-- Success Overlay (Initially Hidden) -->
                    <div id="upiSuccessOverlay" class="position-absolute top-0 start-0 w-100 h-100 rounded-4 d-none flex-column align-items-center justify-content-center text-white" 
                         style="background: rgba(16, 185, 129, 0.96); backdrop-filter: blur(4px);">
                        <div class="rounded-circle bg-white text-success d-flex align-items-center justify-content-center mb-2 shadow" style="width: 50px; height: 50px; font-size: 1.8rem;">
                            <i class="bi bi-check2"></i>
                        </div>
                        <h6 class="fw-bold mb-0">Payment Received!</h6>
                        <small class="text-white-50">₹{{ number_format($order->total_amount, 2) }} Paid via Razorpay</small>
                    </div>
                </div>

                <!-- Razorpay Trust & Supported Apps -->
                <div class="mb-2">
                    <span class="badge bg-primary bg-opacity-10 text-primary rounded-pill px-2.5 py-1 font-monospace" style="font-size: 0.7rem;">
                        <i class="bi bi-shield-check text-primary me-1"></i> Razorpay Auto-Verified Gateway
                    </span>
                </div>

                <!-- Supported Apps Pills -->
                <div class="d-flex align-items-center justify-content-center gap-1.5 mb-2.5 text-muted" style="font-size: 0.72rem;">
                    <span class="badge bg-light text-dark border px-2 py-1"><i class="bi bi-google text-primary me-1"></i>GPay</span>
                    <span class="badge bg-light text-dark border px-2 py-1"><i class="bi bi-phone text-purple me-1"></i>PhonePe</span>
                    <span class="badge bg-light text-dark border px-2 py-1"><i class="bi bi-wallet2 text-info me-1"></i>Paytm</span>
                </div>

                <!-- Live Auto-Detection Pulse -->
                <div id="upiStatusIndicator" class="alert alert-light border d-flex align-items-center justify-content-center gap-2 py-1.5 px-2 mb-2 rounded-3 text-secondary" style="font-size: 0.75rem;">
                    <span class="spinner-grow spinner-grow-sm text-primary" role="status"></span>
                    <span>Waiting for customer to pay on phone...</span>
                </div>

                <!-- 1-Click Instant Confirm Button -->
                <button type="button" id="btnConfirmUpiPayment" class="btn btn-sm btn-success w-100 py-2 rounded-3 fw-bold d-flex align-items-center justify-content-center gap-1.5 shadow-xs">
                    <i class="bi bi-check2-circle fs-6"></i>
                    <span>Confirm UPI Payment Received</span>
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Native Mobile Bottom Sheet Modal for Reporting Issues -->
<div class="modal fade" id="modalReportIssue" tabindex="-1" aria-labelledby="modalReportIssueLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content border-0 rounded-4 shadow-lg overflow-hidden">
            <div class="modal-header bg-light py-3 px-3.5 border-bottom">
                <div class="d-flex align-items-center gap-2">
                    <div class="rounded-circle bg-danger text-white d-flex align-items-center justify-content-center" style="width: 28px; height: 28px; font-size: 0.85rem;">
                        <i class="bi bi-exclamation-triangle-fill"></i>
                    </div>
                    <h6 class="modal-title fw-bolder text-dark mb-0" id="modalReportIssueLabel" style="font-size: 0.95rem;">
                        Report Delivery Issue
                    </h6>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close" style="font-size: 0.75rem;"></button>
            </div>

            <form id="formReportIssue" action="{{ route('delivery.orders.report-issue', $order) }}" method="POST">
                @csrf
                <div class="modal-body p-3.5">
                    <p class="text-secondary small mb-3" style="font-size: 0.78rem;">
                        Select the reason why you cannot deliver this parcel right now:
                    </p>

                    <!-- Clean Touch Tiles for Reasons -->
                    <div class="issue-options-list">
                        <input type="radio" class="d-none issue-option-radio" name="reason" id="r1" value="Customer phone switched off / unreachable" required checked>
                        <label class="issue-option-label" for="r1">
                            <i class="bi bi-telephone-x text-danger fs-6"></i>
                            <span>Phone Switched Off / Unreachable</span>
                        </label>

                        <input type="radio" class="d-none issue-option-radio" name="reason" id="r2" value="Door locked / Customer not home">
                        <label class="issue-option-label" for="r2">
                            <i class="bi bi-door-closed text-warning fs-6"></i>
                            <span>Door Locked / Customer Not Home</span>
                        </label>

                        <input type="radio" class="d-none issue-option-radio" name="reason" id="r3" value="Customer requested reschedule">
                        <label class="issue-option-label" for="r3">
                            <i class="bi bi-clock-history text-primary fs-6"></i>
                            <span>Customer Requested Reschedule</span>
                        </label>

                        <input type="radio" class="d-none issue-option-radio" name="reason" id="r4" value="Wrong address / Pincode mismatch">
                        <label class="issue-option-label" for="r4">
                            <i class="bi bi-geo-alt text-secondary fs-6"></i>
                            <span>Wrong Address / Mismatch</span>
                        </label>

                        <input type="radio" class="d-none issue-option-radio" name="reason" id="r5" value="Customer refused package">
                        <label class="issue-option-label" for="r5">
                            <i class="bi bi-x-circle text-danger fs-6"></i>
                            <span>Customer Refused Package</span>
                        </label>
                    </div>
                </div>

                <div class="modal-footer bg-light py-2.5 px-3.5 border-top d-flex gap-2">
                    <button type="button" class="btn btn-light border bg-white flex-grow-1 fw-bold" data-bs-dismiss="modal" style="font-size: 0.85rem; height: 42px;">
                        Cancel
                    </button>
                    <button type="submit" id="btnSubmitIssue" class="btn btn-danger flex-grow-1 fw-bold" style="font-size: 0.85rem; height: 42px;">
                        <i class="bi bi-send me-1"></i> Log Issue
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const boxes = [
        document.getElementById('box1'),
        document.getElementById('box2'),
        document.getElementById('box3'),
        document.getElementById('box4')
    ];
    const finalOtp = document.getElementById('finalOtpValue');

    boxes.forEach((box, idx) => {
        if (!box) return;

        box.addEventListener('input', function () {
            if (this.value.length === 1 && idx < 3) {
                boxes[idx + 1].focus();
            }
            updateOtp();
        });

        box.addEventListener('keydown', function (e) {
            if (e.key === 'Backspace' && !this.value && idx > 0) {
                boxes[idx - 1].focus();
            }
        });

        box.addEventListener('paste', function (e) {
            e.preventDefault();
            const paste = (e.clipboardData || window.clipboardData).getData('text').trim();
            if (paste.length === 4) {
                paste.split('').forEach((char, i) => {
                    if (boxes[i]) boxes[i].value = char;
                });
                boxes[3].focus();
                updateOtp();
            }
        });
    });

    function updateOtp() {
        const full = boxes.map(b => b ? b.value : '').join('');
        if (finalOtp) finalOtp.value = full;
    }

    const formComplete = document.getElementById('formOrderComplete');
    if (formComplete) {
        formComplete.addEventListener('submit', async function (e) {
            e.preventDefault();
            updateOtp();

            if (finalOtp.value.length < 4) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Enter 4-Digit OTP',
                    text: 'Please enter the 4-digit code provided by the customer.'
                });
                boxes[0].focus();
                return;
            }

            const btn = document.getElementById('btnSubmitOtp');
            const originalText = btn.innerHTML;
            btn.disabled = true;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span> Verifying...';

            const formData = new FormData(formComplete);

            try {
                const res = await fetch(formComplete.action, {
                    method: 'POST',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: formData
                });

                const data = await res.json();
                if (res.ok && data.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Delivered! 🎉',
                        text: data.message,
                        timer: 1800,
                        showConfirmButton: false
                    }).then(() => {
                        window.location.href = data.redirect || '{{ route("delivery.dashboard") }}';
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Verification Failed',
                        text: data.message || 'Invalid OTP. Please check with customer.'
                    });
                }
            } catch (err) {
                console.error(err);
                Swal.fire({
                    icon: 'error',
                    title: 'Network Error',
                    text: 'Please check your connection.'
                });
            } finally {
                btn.disabled = false;
                btn.innerHTML = originalText;
            }
        });
    }

    const formReport = document.getElementById('formReportIssue');
    if (formReport) {
        formReport.addEventListener('submit', async function (e) {
            e.preventDefault();
            const btn = document.getElementById('btnSubmitIssue');
            const originalHtml = btn.innerHTML;
            btn.disabled = true;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Logging...';

            const formData = new FormData(formReport);

            try {
                const res = await fetch(formReport.action, {
                    method: 'POST',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: formData
                });

                const data = await res.json();
                if (res.ok && data.success) {
                    // Close modal
                    const modalEl = document.getElementById('modalReportIssue');
                    const modalObj = bootstrap.Modal.getInstance(modalEl);
                    if (modalObj) modalObj.hide();

                    Swal.fire({
                        icon: 'info',
                        title: 'Issue Logged',
                        text: data.message,
                        timer: 1500,
                        showConfirmButton: false
                    }).then(() => {
                        window.location.href = data.redirect || "{{ route('delivery.dashboard') }}";
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Could Not Log Issue',
                        text: data.message || 'Please try again.'
                    });
                }
            } catch (err) {
                console.error(err);
                Swal.fire({
                    icon: 'error',
                    title: 'Network Error',
                    text: 'Please check your connection.'
                });
            } finally {
                btn.disabled = false;
                btn.innerHTML = originalHtml;
            }
        });
    }

    // Doorstep UPI Live Polling & Auto-Verification
    let upiPollInterval = null;
    const modalUpiEl = document.getElementById('modalDoorstepUpi');
    const btnConfirmUpi = document.getElementById('btnConfirmUpiPayment');
    const upiSuccessOverlay = document.getElementById('upiSuccessOverlay');
    const paymentReqCard = document.getElementById('paymentRequirementCard');

    function playPaymentChime() {
        try {
            const ctx = new (window.AudioContext || window.webkitAudioContext)();
            const osc = ctx.createOscillator();
            const gain = ctx.createGain();
            osc.connect(gain);
            gain.connect(ctx.destination);
            osc.type = 'sine';
            osc.frequency.setValueAtTime(587.33, ctx.currentTime); // D5
            osc.frequency.setValueAtTime(880, ctx.currentTime + 0.12); // A5
            gain.gain.setValueAtTime(0.3, ctx.currentTime);
            gain.gain.exponentialRampToValueAtTime(0.001, ctx.currentTime + 0.6);
            osc.start();
            osc.stop(ctx.currentTime + 0.6);
        } catch (e) {
            console.log('Audio chime not supported or muted');
        }
    }

    function handlePaymentSuccess(amount) {
        if (upiPollInterval) {
            clearInterval(upiPollInterval);
            upiPollInterval = null;
        }

        if (upiSuccessOverlay) {
            upiSuccessOverlay.classList.remove('d-none');
            upiSuccessOverlay.classList.add('d-flex');
        }

        playPaymentChime();

        setTimeout(() => {
            const modalObj = bootstrap.Modal.getInstance(modalUpiEl);
            if (modalObj) modalObj.hide();

            // Update Main Screen Payment Card to Green
            if (paymentReqCard) {
                paymentReqCard.className = 'order-task-card p-3 border-start border-4 border-success';
                paymentReqCard.innerHTML = `
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <div class="text-secondary small fw-bold text-uppercase" style="font-size: 0.65rem;">Payment Requirement</div>
                            <div class="fw-bolder text-success" style="font-size: 0.95rem;">
                                ✅ Paid via Razorpay UPI &bull; Collect ₹0 Cash
                            </div>
                            <div class="text-secondary small" style="font-size: 0.72rem;">Zero cash liability on rider &bull; Verified via Razorpay</div>
                        </div>
                        <div class="text-end">
                            <span class="badge bg-success rounded-pill px-2.5 py-1 fw-bold">PAID</span>
                        </div>
                    </div>
                `;
            }

            Swal.fire({
                icon: 'success',
                title: 'Payment Received! 🎉',
                text: '₹' + amount + ' received via Razorpay UPI. Please ask customer for Delivery OTP.',
                confirmButtonColor: '#0f172a',
                confirmButtonText: 'Enter OTP Now',
                timer: 3500,
                timerProgressBar: true
            }).then(() => {
                if (boxes && boxes[0]) boxes[0].focus();
            });
        }, 1000);
    }

    if (modalUpiEl) {
        modalUpiEl.addEventListener('shown.bs.modal', function () {
            // Start live polling every 1.8 seconds for instant detection
            if (upiPollInterval) clearInterval(upiPollInterval);
            upiPollInterval = setInterval(async () => {
                try {
                    const res = await fetch('{{ route("delivery.orders.check-payment", $order) }}', {
                        headers: { 'Accept': 'application/json' }
                    });
                    const data = await res.json();
                    if (data.is_paid) {
                        handlePaymentSuccess(data.formatted_amount || '{{ number_format($order->total_amount, 2) }}');
                    }
                } catch (err) {
                    console.error('UPI status check error:', err);
                }
            }, 1800);
        });

        modalUpiEl.addEventListener('hidden.bs.modal', function () {
            if (upiPollInterval) {
                clearInterval(upiPollInterval);
                upiPollInterval = null;
            }
        });
    }

    if (btnConfirmUpi) {
        btnConfirmUpi.addEventListener('click', async function () {
            const originalHtml = btnConfirmUpi.innerHTML;
            btnConfirmUpi.disabled = true;
            btnConfirmUpi.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Verifying...';

            try {
                const res = await fetch('{{ route("delivery.orders.confirm-doorstep-upi", $order) }}', {
                    method: 'POST',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify({ notes: 'Verified by Rider via Doorstep UPI' })
                });

                const data = await res.json();
                if (data.success) {
                    handlePaymentSuccess('{{ number_format($order->total_amount, 2) }}');
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Verification Failed',
                        text: data.message || 'Could not verify UPI payment.'
                    });
                }
            } catch (e) {
                console.error(e);
                Swal.fire({
                    icon: 'error',
                    title: 'Network Error',
                    text: 'Please check your connection.'
                });
            } finally {
                btnConfirmUpi.disabled = false;
                btnConfirmUpi.innerHTML = originalHtml;
            }
        });
    }
});
</script>
@endpush

@endsection
