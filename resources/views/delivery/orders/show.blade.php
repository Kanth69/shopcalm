@extends('delivery.layouts.app')

@section('title', 'Deliver #' . $order->order_number)

@push('styles')
<style>
    /* 4-Box Large Touch OTP input */
    .otp-grid {
        display: flex;
        gap: 0.75rem;
        justify-content: center;
        margin: 1.25rem 0;
    }
    .otp-box {
        width: 60px;
        height: 64px;
        border: 2px solid #cbd5e1;
        border-radius: 1rem;
        text-align: center;
        font-size: 1.85rem;
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
        font-size: 0.96rem;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 0.5rem;
        box-shadow: 0 4px 16px rgba(79, 70, 229, 0.25);
        transition: transform 0.15s ease;
    }
    .btn-confirm-delivery:active {
        transform: scale(0.98);
    }

    .btn-report-issue {
        width: 100%;
        height: 46px;
        background: #fff1f2;
        color: #e11d48;
        border: 1.5px solid #fecdd3;
        border-radius: 0.85rem;
        font-weight: 700;
        font-size: 0.86rem;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 0.45rem;
        transition: transform 0.15s ease;
    }
    .btn-report-issue:active {
        transform: scale(0.98);
    }

    /* Modal Touch Tile Radio */
    .issue-option-label {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        padding: 0.85rem 1rem;
        border: 1.5px solid #e2e8f0;
        border-radius: 0.85rem;
        cursor: pointer;
        background: #ffffff;
        transition: all 0.15s ease;
        margin-bottom: 0.6rem;
        font-size: 0.86rem;
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

@php
    $timeSlot = '';
    $customerNotes = $order->notes ?? '';
    if (preg_match('/\[Time:\s*([^\]]+)\]/i', $customerNotes, $matches)) {
        $timeSlot = trim($matches[1]);
        $customerNotes = trim(str_replace($matches[0], '', $customerNotes));
    }
@endphp

<!-- ── 1. Top Bar: Back to Manifest + Order Number ── -->
<div class="d-flex align-items-center justify-content-between mb-3">
    <a href="{{ route('delivery.dashboard') }}" class="btn btn-sm btn-white border bg-white rounded-pill px-3 py-1.5 text-dark fw-bold d-inline-flex align-items-center gap-1 shadow-xs" style="font-size: 0.8rem;">
        <i class="bi bi-arrow-left"></i> Run-Sheet
    </a>
    <div class="d-flex align-items-center gap-1.5 font-monospace fw-bold text-dark fs-6">
        <span>#{{ $order->order_number }}</span>
        <button type="button" class="btn btn-xs btn-link text-muted p-0 border-0" onclick="navigator.clipboard.writeText('{{ $order->order_number }}'); Swal.fire({toast: true, position: 'top', icon: 'success', title: 'Order # copied', showConfirmButton: false, timer: 1000});">
            <i class="bi bi-copy fs-6"></i>
        </button>
    </div>
</div>

@if($order->fulfillment?->hasActiveDeliveryIssue())
    <!-- Active Issue Warning -->
    <div class="alert alert-warning border-0 rounded-4 p-3 mb-3 shadow-xs d-flex align-items-start gap-2.5" style="background: #fffbeb; border: 1.5px solid #fde68a !important;">
        <div class="rounded-circle bg-warning text-dark d-flex align-items-center justify-content-center flex-shrink-0" style="width: 30px; height: 30px; font-size: 0.9rem;">
            <i class="bi bi-exclamation-triangle-fill"></i>
        </div>
        <div>
            <div class="fw-bolder text-dark" style="font-size: 0.88rem;">Delivery Issue Logged &bull; On Hold</div>
            <div class="text-secondary small mt-0.5" style="font-size: 0.78rem; line-height: 1.4;">
                Reason: <strong>{{ $order->fulfillment->delivery_issue }}</strong>. If customer arrives or calls, enter the 4-digit code below to deliver.
            </div>
        </div>
    </div>
@endif

<!-- ── 2. Doorstep Payment Requirement Module ── -->
@if($order->payment_status === 'paid')
    <!-- Online Paid Status -->
    <div id="paymentRequirementCard" class="card border-0 rounded-4 p-3.5 bg-white shadow-xs mb-3" style="border-left: 5px solid #10b981 !important; border-color: #e2e8f0;">
        <div class="d-flex align-items-center justify-content-between">
            <div>
                <span class="text-secondary small fw-bold text-uppercase d-block" style="font-size: 0.65rem; letter-spacing: 0.04em;">Payment Status</span>
                <div class="fw-bolder text-success fs-5">
                    ✅ Paid {{ $order->payment_method === 'doorstep_upi' ? 'via Doorstep UPI' : 'Online' }}
                </div>
                <div class="text-secondary small" style="font-size: 0.78rem;">Collect ₹0 cash &bull; Zero liability</div>
            </div>
            <span class="badge bg-success rounded-pill px-3 py-1.5 fw-bold">PAID</span>
        </div>
    </div>
@else
    <!-- Cash on Delivery (COD) Requirement -->
    <div id="paymentRequirementCard" class="card border-0 rounded-4 p-3.5 bg-white shadow-xs mb-3" style="border-left: 5px solid #f59e0b !important; border-color: #e2e8f0;">
        <div class="d-flex align-items-center justify-content-between mb-2.5">
            <div>
                <span class="text-secondary small fw-bold text-uppercase d-block" style="font-size: 0.65rem; letter-spacing: 0.04em;">Cash to Collect at Doorstep</span>
                <div id="paymentStatusHeading" class="fw-bolder text-dark fs-4 font-monospace lh-1">
                    ₹{{ number_format($order->total_amount, 2) }}
                </div>
            </div>
            <span class="badge bg-warning text-dark rounded-pill px-2.5 py-1 fw-bold" style="font-size: 0.74rem;">💵 COD</span>
        </div>

        <!-- 2 Payment Choice Buttons for Rider -->
        <div class="row g-2 pt-2 border-top" id="paymentOptionsRow">
            <div class="col-6">
                <div class="p-2.5 rounded-3 border text-center bg-light h-100 d-flex flex-column justify-content-center" style="border-color: #cbd5e1 !important;">
                    <span class="fw-bold text-dark small" style="font-size: 0.8rem;"><i class="bi bi-cash text-warning me-1"></i> Cash Notes</span>
                    <span class="text-secondary" style="font-size: 0.7rem;">Collect physical cash</span>
                </div>
            </div>
            <div class="col-6">
                <button type="button" class="btn btn-primary w-100 fw-bold py-2 rounded-3 d-flex flex-column align-items-center justify-content-center shadow-xs" 
                        data-bs-toggle="modal" data-bs-target="#modalDoorstepUpi" 
                        style="background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%); border: none; min-height: 48px;">
                    <span style="font-size: 0.82rem;"><i class="bi bi-qr-code-scan me-1"></i> Collect via UPI</span>
                    <span class="text-white-50" style="font-size: 0.68rem;">GPay / PhonePe / Paytm</span>
                </button>
            </div>
        </div>
    </div>
@endif

<!-- ── 3. Customer & Address Details ── -->
<div class="card border-0 rounded-4 p-3.5 bg-white shadow-xs mb-3" style="border: 1.5px solid #e2e8f0 !important;">
    <!-- Customer Header -->
    <div class="d-flex align-items-center justify-content-between mb-3">
        <div class="d-flex align-items-center gap-2.5">
            <div class="rounded-circle text-white d-flex align-items-center justify-content-center fw-bold shadow-xs flex-shrink-0" 
                 style="width: 44px; height: 44px; background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%); font-size: 1.1rem;">
                {{ strtoupper(substr($order->shipping_name ?? 'C', 0, 1)) }}
            </div>
            <div>
                <h5 class="fw-bolder text-dark mb-0 fs-5" style="letter-spacing: -0.01em;">
                    {{ $order->shipping_name }}
                </h5>
                <small class="text-muted" style="font-size: 0.78rem;">Customer</small>
            </div>
        </div>
        <a href="tel:{{ $order->shipping_phone }}" class="btn btn-success rounded-pill px-3.5 py-2 fw-bold d-inline-flex align-items-center gap-1.5 shadow-xs" style="font-size: 0.85rem;">
            <i class="bi bi-telephone-fill"></i> <span>Call</span>
        </a>
    </div>

    <!-- Address Box with Generous Padding -->
    <div class="p-3 rounded-3 bg-light border mb-3 text-dark shadow-xs" style="border-color: #e2e8f0 !important;">
        <div class="d-flex align-items-start gap-2.5">
            <i class="bi bi-geo-alt-fill text-danger flex-shrink-0 mt-0.5 fs-5"></i>
            <div style="line-height: 1.5;">
                <div class="fw-semibold text-dark" style="font-size: 0.94rem;">
                    {{ $order->shipping_address }}
                </div>
                <div class="text-secondary small mt-0.5" style="font-size: 0.82rem;">
                    {{ $order->shipping_city }} - <strong class="text-dark">{{ $order->shipping_zip }}</strong>
                </div>
            </div>
        </div>
    </div>

    <!-- Preferred Time Slot & Special Instructions -->
    @if(!empty($timeSlot) || !empty($customerNotes))
        <div class="mb-3">
            @if(!empty($timeSlot))
                <div class="badge rounded-pill bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25 px-3 py-1.5 fw-bold font-monospace mb-2 d-inline-flex align-items-center gap-1.5" style="font-size: 0.78rem;">
                    <i class="bi bi-clock-history"></i> Preferred Slot: {{ $timeSlot }}
                </div>
            @endif

            @if(!empty($customerNotes))
                <div class="p-3 rounded-3 border d-flex align-items-start gap-2.5 shadow-xs" style="background: #fff7ed; border: 1.5px solid #fdba74 !important;">
                    <i class="bi bi-chat-left-text-fill text-warning flex-shrink-0 mt-0.5 fs-5"></i>
                    <div>
                        <span class="fw-bold text-uppercase d-block mb-0.5" style="font-size: 0.72rem; color: #c2410c; letter-spacing: 0.04em;">
                            Customer Instructions:
                        </span>
                        <div class="fw-bold text-dark" style="font-size: 0.88rem; line-height: 1.4;">
                            "{{ $customerNotes }}"
                        </div>
                    </div>
                </div>
            @endif
        </div>
    @endif

    <!-- Maps Navigation Button -->
    <a href="https://www.google.com/maps/dir/?api=1&destination={{ urlencode($order->shipping_address . ', ' . $order->shipping_city . ' ' . $order->shipping_zip) }}" 
       target="_blank" class="btn btn-outline-primary w-100 rounded-3 fw-bold d-flex align-items-center justify-content-center gap-1.5 shadow-xs" style="height: 48px; font-size: 0.9rem;">
        <i class="bi bi-map-fill"></i> <span>Navigate in Google Maps</span>
    </a>
</div>

<!-- ── 4. Package Contents ── -->
<div class="card border-0 rounded-4 p-3 bg-white shadow-xs mb-3" style="border: 1.5px solid #e2e8f0 !important;">
    <div class="text-secondary small fw-bold text-uppercase mb-2" style="font-size: 0.68rem; letter-spacing: 0.04em;">
        📦 Items in Package ({{ $order->items->sum('quantity') }} Total)
    </div>

    <div>
        @foreach($order->items as $item)
            <div class="d-flex align-items-center justify-content-between py-2 {{ !$loop->last ? 'border-bottom' : '' }}">
                <div class="d-flex align-items-center gap-2.5 overflow-hidden">
                    @php $riderThumb = $item->display_image ?? $item->product?->main_image; @endphp
                    @if($riderThumb)
                        <img src="{{ asset('storage/' . $riderThumb) }}" alt="{{ $item->product_name }}" 
                             class="rounded-2 border object-fit-cover flex-shrink-0" style="width: 40px; height: 40px;">
                    @else
                        <div class="rounded-2 bg-light border d-flex align-items-center justify-content-center text-secondary flex-shrink-0" style="width: 40px; height: 40px;">
                            <i class="bi bi-box fs-5"></i>
                        </div>
                    @endif
                    <div class="overflow-hidden">
                        <div class="fw-bold text-dark text-truncate" style="font-size: 0.86rem;">{{ $item->product_name }}</div>
                        <div class="text-secondary small font-monospace" style="font-size: 0.72rem;">SKU: {{ $item->product?->sku ?? 'N/A' }}</div>
                    </div>
                </div>
                <span class="badge bg-light text-dark border px-2.5 py-1 rounded-pill fw-bold ms-2 font-monospace" style="font-size: 0.74rem;">
                    {{ $item->quantity }}x
                </span>
            </div>
        @endforeach
    </div>
</div>

<!-- ── 5. Customer Handover & 1-Tap Delivery Confirmation ── -->
@if($order->status !== 'delivered')
    <div class="card border-0 rounded-4 p-3.5 bg-white shadow-sm text-center mb-3" style="border: 1.5px solid #cbd5e1 !important;">
        <div class="fw-bolder text-dark mb-0.5 fs-5">Doorstep Handover</div>
        <div class="text-secondary small mb-3" style="font-size: 0.78rem;">
            Confirm physical parcel handover to customer
        </div>

        <form id="formOrderComplete" action="{{ route('delivery.orders.complete', $order) }}" method="POST">
            @csrf

            <!-- Primary Action: Confirm Delivery -->
            <button type="submit" id="btnSubmitOtp" class="btn-confirm-delivery">
                <i class="bi bi-check2-circle fs-5"></i>
                <span>Confirm Delivery & Handover</span>
            </button>
        </form>

        <!-- Report Issue Button -->
        <div class="pt-3 mt-3 border-top">
            <button type="button" class="btn-report-issue" data-bs-toggle="modal" data-bs-target="#modalReportIssue">
                <i class="bi bi-exclamation-octagon-fill"></i>
                <span>Cannot Deliver? Report Issue</span>
            </button>
        </div>
    </div>
@else
    <div class="card border-0 rounded-4 p-4 bg-white text-center shadow-xs mb-3" style="border: 1.5px solid #bbf7d0 !important;">
        <div class="rounded-circle d-inline-flex align-items-center justify-content-center text-success mb-2" style="width: 52px; height: 52px; background: #ecfdf5; font-size: 1.8rem;">
            <i class="bi bi-check2-all"></i>
        </div>
        <h5 class="fw-bolder text-dark mb-1">Package Delivered 🎉</h5>
        <div class="text-secondary small">Delivered on {{ $order->delivered_at?->format('d M Y, h:i A') }}</div>
    </div>
@endif

<!-- Modal: Doorstep UPI Live QR Collection & Auto-Detection -->
<div class="modal fade" id="modalDoorstepUpi" tabindex="-1" aria-labelledby="modalDoorstepUpiLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-sm modal-dialog-scrollable">
        <div class="modal-content border-0 rounded-4 shadow-lg overflow-hidden">
            <div class="modal-header py-3 px-3.5 border-bottom text-white" style="background: linear-gradient(135deg, #1e1b4b 0%, #312e81 100%);">
                <div class="d-flex align-items-center gap-2">
                    <i class="bi bi-qr-code-scan text-warning fs-5"></i>
                    <div>
                        <h6 class="modal-title fw-bold text-white mb-0" id="modalDoorstepUpiLabel" style="font-size: 0.94rem;">
                            Doorstep UPI Payment
                        </h6>
                        <small class="text-white-50" style="font-size: 0.7rem;">Scan with GPay / PhonePe / Paytm</small>
                    </div>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close" style="font-size: 0.75rem;"></button>
            </div>

            <div class="modal-body p-3.5 text-center">
                <!-- Amount Pill -->
                <div class="bg-light p-2.5 rounded-3 border mb-3">
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

                <div class="mb-2">
                    <span class="badge bg-primary bg-opacity-10 text-primary rounded-pill px-2.5 py-1 font-monospace" style="font-size: 0.72rem;">
                        <i class="bi bi-shield-check text-primary me-1"></i> Razorpay Auto-Verified Gateway
                    </span>
                </div>

                <div class="d-flex align-items-center justify-content-center gap-1.5 mb-2.5 text-muted" style="font-size: 0.74rem;">
                    <span class="badge bg-light text-dark border px-2 py-1"><i class="bi bi-google text-primary me-1"></i>GPay</span>
                    <span class="badge bg-light text-dark border px-2 py-1"><i class="bi bi-phone text-purple me-1"></i>PhonePe</span>
                    <span class="badge bg-light text-dark border px-2 py-1"><i class="bi bi-wallet2 text-info me-1"></i>Paytm</span>
                </div>

                <div id="upiStatusIndicator" class="alert alert-light border d-flex align-items-center justify-content-center gap-2 py-2 px-2.5 mb-2.5 rounded-3 text-secondary" style="font-size: 0.78rem;">
                    <span class="spinner-grow spinner-grow-sm text-primary" role="status"></span>
                    <span>Waiting for customer to pay on phone...</span>
                </div>

                <!-- 1-Click Instant Confirm Button -->
                <button type="button" id="btnConfirmUpiPayment" class="btn btn-sm btn-success w-100 py-2.5 rounded-3 fw-bold d-flex align-items-center justify-content-center gap-1.5 shadow-xs">
                    <i class="bi bi-check2-circle fs-6"></i>
                    <span>Confirm UPI Payment Received</span>
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal: Reporting Delivery Issues -->
<div class="modal fade" id="modalReportIssue" tabindex="-1" aria-labelledby="modalReportIssueLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-sm modal-dialog-scrollable">
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

            <form id="formReportIssue" action="{{ route('delivery.orders.report-issue', $order) }}" method="POST" class="d-flex flex-column h-100" style="min-height: 0; overflow: hidden; flex: 1 1 auto;">
                @csrf
                <div class="modal-body p-3.5">
                    <p class="text-secondary small mb-3" style="font-size: 0.8rem;">
                        Select the reason why this parcel cannot be delivered:
                    </p>

                    <!-- Touch Tiles for Reasons -->
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

                <div class="modal-footer bg-light py-2.5 px-3.5 border-top d-flex gap-2" style="flex-shrink: 0; position: sticky; bottom: 0; z-index: 10; background: #ffffff !important;">
                    <button type="button" class="btn btn-light border bg-white flex-grow-1 fw-bold" data-bs-dismiss="modal" style="font-size: 0.86rem; height: 44px;">
                        Cancel
                    </button>
                    <button type="submit" id="btnSubmitIssue" class="btn btn-danger flex-grow-1 fw-bold" style="font-size: 0.86rem; height: 44px;">
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

            const btn = document.getElementById('btnSubmitOtp');
            const originalText = btn.innerHTML;
            btn.disabled = true;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span> Confirming...';

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
            osc.frequency.setValueAtTime(587.33, ctx.currentTime);
            osc.frequency.setValueAtTime(880, ctx.currentTime + 0.12);
            gain.gain.setValueAtTime(0.3, ctx.currentTime);
            gain.gain.exponentialRampToValueAtTime(0.001, ctx.currentTime + 0.6);
            osc.start();
            osc.stop(ctx.currentTime + 0.6);
        } catch (e) {
            console.log('Audio chime not supported');
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

            if (paymentReqCard) {
                paymentReqCard.className = 'card border-0 rounded-4 p-3.5 bg-white shadow-xs mb-3';
                paymentReqCard.style.borderLeft = '5px solid #10b981';
                paymentReqCard.innerHTML = `
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <span class="text-secondary small fw-bold text-uppercase d-block" style="font-size: 0.65rem;">Payment Status</span>
                            <div class="fw-bolder text-success fs-5">
                                ✅ Paid via Razorpay UPI &bull; Collect ₹0 Cash
                            </div>
                            <div class="text-secondary small" style="font-size: 0.76rem;">Verified via Razorpay &bull; Zero liability</div>
                        </div>
                        <span class="badge bg-success rounded-pill px-3 py-1.5 fw-bold">PAID</span>
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
