@extends('order-manager.layouts.app')

@section('title', 'Order #' . $order->order_number)
@section('header', 'Order Command Center')

@section('content')

@php
    $stages = ['pending', 'confirmed', 'processing', 'packed', 'shipped', 'out for delivery', 'delivered'];
    $currentStatusIndex = array_search(strtolower(str_replace('_', ' ', $order->status)), $stages);
    $isCancelled = in_array(strtolower((string)$order->status), ['cancelled', 'returned']);
    $isDelivered = strtolower(str_replace('_', ' ', (string)$order->status)) === 'delivered';
    $isOutForDelivery = strtolower(str_replace('_', ' ', (string)$order->status)) === 'out for delivery';
    $payment = $order->primaryPayment ?? $order->latestPayment;
@endphp

{{-- ── 1. Top Order Command Strip ── --}}
<div class="card border-0 shadow-sm rounded-4 mb-4 bg-white overflow-hidden" style="border: 1px solid #e2e8f0 !important;">
    <div class="p-3.5 p-lg-4">
        {{-- Navigation & Document Actions Row --}}
        <div class="d-flex align-items-center justify-content-between flex-wrap gap-2.5 pb-3 border-bottom">
            <div class="d-flex align-items-center gap-2">
                <a href="{{ route('order-manager.orders.index') }}" class="btn btn-sm btn-light border rounded-pill px-3 py-1.5 fw-semibold text-secondary d-inline-flex align-items-center gap-1.5 shadow-2xs">
                    <i class="bi bi-arrow-left"></i> <span>Orders Queue</span>
                </a>
                <span class="text-muted d-none d-sm-inline">•</span>
                <span class="text-muted small d-none d-sm-inline">
                    Placed {{ $order->created_at->format('d M, Y \a\t h:i A') }} ({{ $order->created_at->diffForHumans() }})
                </span>
            </div>

            {{-- Document Printouts & Quick Actions --}}
            <div class="d-flex align-items-center gap-1.5 flex-wrap">
                <a href="{{ route('order-manager.orders.packing-slip', $order) }}" target="_blank" class="btn btn-sm btn-light border rounded-pill px-3 py-1.5 fw-semibold text-dark shadow-2xs d-inline-flex align-items-center gap-1.5" title="Print Warehouse Packing Slip">
                    <i class="bi bi-printer text-primary"></i> <span>Packing Slip</span>
                </a>
                <a href="{{ route('order-manager.orders.shipping-label', $order) }}" target="_blank" class="btn btn-sm btn-light border rounded-pill px-3 py-1.5 fw-semibold text-dark shadow-2xs d-inline-flex align-items-center gap-1.5" title="Print Shipping Label (4x6 Thermal)">
                    <i class="bi bi-tag-fill text-warning"></i> <span>Label (4x6)</span>
                </a>
                @if($order->isLocalBengaluruDelivery())
                    <a href="{{ route('order-manager.orders.manifest', $order) }}" target="_blank" class="btn btn-sm btn-light border rounded-pill px-3 py-1.5 fw-semibold text-dark shadow-2xs d-inline-flex align-items-center gap-1.5" title="Local Fleet Run-Sheet">
                        <i class="bi bi-file-earmark-ruled text-info"></i> <span>Run-Sheet</span>
                    </a>
                @endif
                <a href="{{ route('order-manager.orders.invoice', $order) }}" target="_blank" class="btn btn-sm btn-light border rounded-pill px-3 py-1.5 fw-semibold text-dark shadow-2xs d-inline-flex align-items-center gap-1.5" title="Tax Invoice">
                    <i class="bi bi-receipt text-success"></i> <span>Invoice</span>
                </a>
                @if(!in_array($order->status, ['cancelled', 'returned']))
                    <button type="button" class="btn btn-sm btn-outline-danger rounded-pill px-3 py-1.5 fw-semibold shadow-2xs d-inline-flex align-items-center gap-1 ms-1" data-bs-toggle="modal" data-bs-target="#staffCancelOrderModal">
                        <i class="bi bi-x-circle"></i> <span>Cancel</span>
                    </button>
                @endif
            </div>
        </div>

        {{-- Order ID, Badges & Logistics Summary Row --}}
        <div class="d-flex align-items-center justify-content-between flex-wrap gap-3 pt-3">
            <div>
                <div class="d-flex align-items-center gap-2 flex-wrap mb-1">
                    <h3 class="fw-extrabold text-dark mb-0 font-monospace" style="letter-spacing: -0.02em; font-size: 1.55rem;">
                        #{{ $order->order_number }}
                    </h3>
                    <button type="button" class="btn btn-sm btn-light border rounded-pill px-2 py-0.5 text-muted small" onclick="navigator.clipboard.writeText('{{ $order->order_number }}'); this.innerText = 'Copied!'; setTimeout(() => this.innerHTML = '<i class=\'bi bi-copy\'></i>', 1500);" title="Copy Order Number">
                        <i class="bi bi-copy"></i>
                    </button>
                    <span id="topStatusBadge" class="badge rounded-pill px-3 py-1.5 fw-bold shadow-2xs" 
                          style="background: #eef2ff; color: #4338ca; border: 1px solid #c7d2fe; font-size: 0.78rem;">
                        Status: {{ ucfirst(str_replace('_', ' ', $order->status)) }}
                    </span>
                    <span id="topFulfillmentBadge">{!! $order->fulfillment_badge !!}</span>
                </div>

                <div class="d-flex align-items-center gap-2.5 text-secondary small flex-wrap pt-0.5" style="font-size: 0.82rem;">
                    <span>Customer: <strong class="text-dark">{{ $order->shipping_name }}</strong></span>
                    <span>&bull;</span>
                    <span id="topLogisticsInfo">
                        @if($order->isLocalBengaluruDelivery())
                            Bengaluru Local Fleet: <strong>{{ $order->rider_name ? 'Assigned to ' . $order->rider_name : 'Unassigned Rider' }}</strong>
                        @else
                            Courier: <strong>{{ $order->courier_partner ?: 'National Logistics' }}</strong> (AWB: {{ $order->tracking_number ?: 'Pending' }})
                        @endif
                    </span>
                </div>
            </div>

            {{-- Quick Payment Ledger Badge --}}
            <div class="text-start text-sm-end">
                <div class="d-flex align-items-center gap-2 justify-content-sm-end mb-1">
                    @if($order->payment_method === 'doorstep_upi')
                        <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 px-2.5 py-1 fw-bold" style="font-size: 0.72rem;">
                            <i class="bi bi-qr-code-scan me-1"></i>Doorstep UPI
                        </span>
                    @elseif($order->payment_method === 'online')
                        <span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25 px-2.5 py-1 fw-bold" style="font-size: 0.72rem;">
                            <i class="bi bi-shield-check me-1"></i>Prepaid Online
                        </span>
                    @elseif($order->payment_method === 'wallet' || ($order->total_amount <= 0 && $order->wallet_amount_used > 0))
                        <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 px-2.5 py-1 fw-bold" style="font-size: 0.72rem;">
                            <i class="bi bi-wallet2 me-1"></i>ShopCalm Wallet
                        </span>
                    @elseif($order->payment_method === 'cod')
                        <span class="badge bg-light text-secondary border px-2.5 py-1 fw-bold" style="font-size: 0.72rem;">
                            <i class="bi bi-cash me-1"></i>Cash on Delivery
                        </span>
                    @else
                        <span class="badge bg-light text-dark border px-2.5 py-1 fw-bold text-uppercase" style="font-size: 0.72rem;">
                            <i class="bi bi-wallet2 me-1"></i>{{ $order->payment_method }}
                        </span>
                    @endif
                    <span class="badge rounded-pill px-2.5 py-1 fw-bold {{ $order->payment_status === 'paid' ? 'bg-success text-white' : 'bg-warning text-dark' }}" style="font-size: 0.72rem;">
                        {{ ucfirst($order->payment_status ?? 'pending') }}
                    </span>
                </div>
                <div class="h4 fw-extrabold text-dark mb-0 font-monospace">₹{{ number_format($order->total_amount, 2) }}</div>
            </div>
        </div>

        {{-- Visual Status Stepper (Interactive & Responsive) --}}
        @if(!$isCancelled)
            <div class="mt-4 pt-2 border-top">
                <div class="position-relative px-2 my-2" id="trackerContainer">
                    <div class="progress" style="height: 6px; background-color: #f1f5f9; border-radius: 9999px;">
                        @php
                            $pct = $currentStatusIndex !== false ? (($currentStatusIndex) / (count($stages) - 1)) * 100 : 0;
                        @endphp
                        <div id="trackerProgressBar" class="progress-bar" role="progressbar" 
                             style="width: {{ $pct }}%; background: linear-gradient(90deg, #4f46e5, #10b981); transition: width 0.4s ease;" 
                             aria-valuenow="{{ $pct }}" aria-valuemin="0" aria-valuemax="100"></div>
                    </div>

                    <div class="d-flex justify-content-between position-relative mt-2 text-center" style="margin-top: -16px !important;">
                        @foreach($stages as $idx => $st)
                            @php
                                $isPast = $currentStatusIndex !== false && $idx <= $currentStatusIndex;
                                $isCurrent = $currentStatusIndex !== false && $idx === $currentStatusIndex;
                            @endphp
                            <div style="width: 14%;" class="stage-step" data-stage="{{ $st }}" data-index="{{ $idx }}">
                                <div class="stage-dot rounded-circle d-inline-flex align-items-center justify-content-center shadow-2xs" 
                                     style="width: 26px; height: 26px; font-size: 0.72rem; font-weight: 700; background: {{ $isPast ? '#10b981' : '#ffffff' }}; color: {{ $isPast ? '#ffffff' : '#64748b' }}; border: 2px solid {{ $isCurrent ? '#4f46e5' : ($isPast ? '#10b981' : '#cbd5e1') }}; transition: all 0.3s ease;">
                                    @if($isPast)
                                        <i class="bi bi-check-lg"></i>
                                    @else
                                        {{ $idx + 1 }}
                                    @endif
                                </div>
                                <div class="stage-label small fw-semibold mt-1 d-none d-md-block {{ $isCurrent ? 'text-primary fw-bold' : ($isPast ? 'text-dark' : 'text-muted') }}" 
                                     style="font-size: 0.72rem; text-transform: capitalize;">
                                    {{ $st }}
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        @endif
    </div>
</div>

{{-- ── 2. Smart 1-Click Primary Action Banner ── --}}
@if(!$isCancelled)
    @if($order->status === 'pending' && $order->payment_method === 'cod')
        {{-- COD 1-Click Confirmation --}}
        <div class="card border-0 shadow-sm rounded-4 p-3.5 p-md-4 mb-4 text-white" style="background: linear-gradient(135deg, #059669 0%, #047857 100%);">
            <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
                <div class="d-flex align-items-center gap-3">
                    <div class="rounded-3 bg-white bg-opacity-20 d-flex align-items-center justify-content-center flex-shrink-0" style="width: 44px; height: 44px; font-size: 1.4rem;">
                        <i class="bi bi-cash-coin"></i>
                    </div>
                    <div>
                        <h6 class="fw-bold mb-0.5 text-white" style="font-size: 1rem;">Action Required: Pending COD Order Approval</h6>
                        <div class="text-white-50 small" style="font-size: 0.8rem;">
                            Customer requested Cash on Delivery (₹{{ number_format($order->total_amount, 2) }}). Confirm to release for warehouse packing.
                        </div>
                    </div>
                </div>
                <form action="{{ route('order-manager.orders.update-status', $order) }}" method="POST" class="ms-auto ms-sm-0">
                    @csrf
                    <input type="hidden" name="status" value="confirmed">
                    <input type="hidden" name="notes" value="COD order confirmed & approved by Order Manager.">
                    <button type="submit" class="btn btn-light rounded-pill px-4 py-2 fw-bold text-success shadow-sm d-inline-flex align-items-center gap-2">
                        <i class="bi bi-check-circle-fill"></i> <span>Accept & Confirm COD Order</span>
                    </button>
                </form>
            </div>
        </div>
    @elseif($order->status === 'pending' && $order->payment_method === 'online' && $order->payment_status !== 'paid')
        {{-- Online Payment Incomplete Warning --}}
        <div class="card border-0 shadow-sm rounded-4 p-3.5 p-md-4 mb-4 bg-white" style="border: 1.5px solid #fed7aa !important; border-left: 5px solid #ea580c !important;">
            <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
                <div class="d-flex align-items-center gap-3">
                    <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0" style="width: 44px; height: 44px; background: #ffedd5; color: #ea580c; font-size: 1.3rem;">
                        <i class="bi bi-hourglass-split"></i>
                    </div>
                    <div>
                        <h6 class="fw-bold text-dark mb-0.5" style="font-size: 0.96rem;">Awaiting Customer Online Payment</h6>
                        <div class="text-secondary small" style="font-size: 0.8rem;">
                            Do not pack or dispatch yet. The customer has not completed online payment via Razorpay.
                        </div>
                    </div>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <a href="tel:{{ $order->shipping_phone }}" class="btn btn-sm btn-outline-secondary rounded-pill px-3 py-1.5">
                        <i class="bi bi-telephone me-1"></i> Call Customer
                    </a>
                </div>
            </div>
        </div>
    @elseif($order->status === 'confirmed')
        {{-- 1-Click Move to Processing / Packing --}}
        <div class="card border-0 shadow-sm rounded-4 p-3.5 p-md-4 mb-4 bg-white" style="border: 1.5px solid #e0e7ff !important; border-left: 5px solid #4f46e5 !important;">
            <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
                <div class="d-flex align-items-center gap-3">
                    <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0" style="width: 44px; height: 44px; background: #e0e7ff; color: #4338ca; font-size: 1.3rem;">
                        <i class="bi bi-box-seam"></i>
                    </div>
                    <div>
                        <h6 class="fw-bold text-dark mb-0.5" style="font-size: 0.96rem;">Order Confirmed &bull; Ready for Warehouse Picking</h6>
                        <div class="text-secondary small" style="font-size: 0.8rem;">
                            Advance order to Warehouse Packing to begin parcel preparation.
                        </div>
                    </div>
                </div>
                <form action="{{ route('order-manager.orders.update-status', $order) }}" method="POST" class="ms-auto ms-sm-0">
                    @csrf
                    <input type="hidden" name="status" value="processing">
                    <input type="hidden" name="notes" value="Started warehouse picking & packing.">
                    <button type="submit" class="btn btn-primary rounded-pill px-4 py-2 fw-bold text-white shadow-sm d-inline-flex align-items-center gap-2" style="background: #4f46e5; border: none;">
                        <i class="bi bi-gear-fill"></i> <span>Start Warehouse Packing (Processing)</span>
                    </button>
                </form>
            </div>
        </div>
    @elseif($order->status === 'processing')
        {{-- 1-Click Move to Packed --}}
        <div class="card border-0 shadow-sm rounded-4 p-3.5 p-md-4 mb-4 bg-white" style="border: 1.5px solid #bae6fd !important; border-left: 5px solid #0284c7 !important;">
            <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
                <div class="d-flex align-items-center gap-3">
                    <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0" style="width: 44px; height: 44px; background: #e0f2fe; color: #0284c7; font-size: 1.3rem;">
                        <i class="bi bi-boxes"></i>
                    </div>
                    <div>
                        <h6 class="fw-bold text-dark mb-0.5" style="font-size: 0.96rem;">Packing in Progress</h6>
                        <div class="text-secondary small" style="font-size: 0.8rem;">
                            Items picked from shelf and packaged. Mark as packed to prepare for rider dispatch.
                        </div>
                    </div>
                </div>
                <form action="{{ route('order-manager.orders.update-status', $order) }}" method="POST" class="ms-auto ms-sm-0">
                    @csrf
                    <input type="hidden" name="status" value="packed">
                    <input type="hidden" name="notes" value="Package picked, packed, and labeled in warehouse.">
                    <button type="submit" class="btn rounded-pill px-4 py-2 fw-bold text-white shadow-sm d-inline-flex align-items-center gap-2" style="background: #0284c7; border: none;">
                        <i class="bi bi-box2-heart-fill"></i> <span>Mark Packed & Ready for Dispatch</span>
                    </button>
                </form>
            </div>
        </div>
    @elseif($order->status === 'packed')
        {{-- Ready for Dispatch Notice --}}
        <div class="card border-0 shadow-sm rounded-4 p-3.5 p-md-4 mb-4 bg-white" style="border: 1.5px solid #fde68a !important; border-left: 5px solid #d97706 !important;">
            <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
                <div class="d-flex align-items-center gap-3">
                    <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0" style="width: 44px; height: 44px; background: #fef3c7; color: #d97706; font-size: 1.3rem;">
                        <i class="bi bi-bicycle"></i>
                    </div>
                    <div>
                        <h6 class="fw-bold text-dark mb-0.5" style="font-size: 0.96rem;">Package Packed &bull; Ready for Logistics Handover</h6>
                        <div class="text-secondary small" style="font-size: 0.8rem;">
                            @if($order->isLocalBengaluruDelivery())
                                Assign a local fleet rider below to move order into <strong>Out for Delivery</strong>.
                            @else
                                Enter courier tracking details below to mark order as <strong>Shipped</strong>.
                            @endif
                        </div>
                    </div>
                </div>
                <a href="#dispatchCard" class="btn btn-warning text-dark rounded-pill px-4 py-2 fw-bold shadow-sm d-inline-flex align-items-center gap-2">
                    <i class="bi bi-send-fill"></i> <span>Dispatch Now</span>
                </a>
            </div>
        </div>
    @elseif($isOutForDelivery)
        {{-- Live Out For Delivery Banner with OTP (Delivery Handover handled exclusively by Delivery Partner) --}}
        <div class="card border-0 shadow-sm rounded-4 p-3.5 p-md-4 mb-4 bg-white" style="border: 1.5px solid #fde68a !important; border-left: 5px solid #f59e0b !important;">
            <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
                <div class="d-flex align-items-center gap-3">
                    <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0" style="width: 48px; height: 48px; background: #fffbeb; color: #d97706; font-size: 1.45rem;">
                        <i class="bi bi-bicycle"></i>
                    </div>
                    <div>
                        <div class="d-flex align-items-center gap-2 flex-wrap mb-1">
                            <span class="badge bg-warning text-dark fw-bold rounded-pill px-2.5 py-0.5 small">⚡ OUT FOR DELIVERY</span>
                            <strong class="text-dark" style="font-size: 0.96rem;">Rider: {{ $order->rider_name ?? 'In-House Fleet' }}</strong>
                            <span class="badge rounded-pill bg-info-subtle text-info-emphasis border border-info-subtle px-2 py-0.5" style="font-size: 0.72rem;">
                                <i class="bi bi-shield-lock-fill me-1"></i> Awaiting Rider OTP Handover
                            </span>
                        </div>
                        <div class="text-secondary small" style="font-size: 0.82rem;">
                            Customer Handover OTP: <strong class="badge bg-light text-dark border font-monospace fs-6 px-2.5 py-0.5">{{ $order->delivery_otp }}</strong>
                            &bull; Handover completion is restricted to the delivery partner portal upon verifying this OTP at doorstep.
                        </div>
                    </div>
                </div>

                {{-- Out For Delivery Actions for Order Manager --}}
                <div class="d-flex align-items-center gap-2 flex-wrap ms-auto ms-sm-0">
                    @if($order->rider_phone)
                        <a href="tel:{{ $order->rider_phone }}" class="btn btn-sm btn-outline-success rounded-pill px-3 py-1.5 fw-semibold d-inline-flex align-items-center gap-1.5 shadow-2xs">
                            <i class="bi bi-telephone-fill"></i> <span>Call Rider</span>
                        </a>
                    @endif
                    <a href="{{ route('delivery.orders.show', $order) }}" target="_blank" class="btn btn-sm btn-light border rounded-pill px-3 py-1.5 fw-semibold text-secondary d-inline-flex align-items-center gap-1.5 shadow-2xs" title="Open Delivery Partner Mobile View">
                        <i class="bi bi-box-arrow-up-right"></i> <span>Delivery Partner View</span>
                    </a>
                </div>
            </div>
        </div>
    @elseif($isDelivered)
        <div class="card border-0 shadow-sm rounded-4 p-3.5 p-md-4 mb-4 bg-white" style="border: 1.5px solid #a7f3d0 !important; border-left: 5px solid #059669 !important;">
            <div class="d-flex align-items-center gap-3">
                <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0" style="width: 44px; height: 44px; background: #ecfdf5; color: #059669; font-size: 1.3rem;">
                    <i class="bi bi-bag-check-fill"></i>
                </div>
                <div>
                    <h6 class="fw-bold text-dark mb-0.5" style="font-size: 0.96rem;">Package Delivered Successfully</h6>
                    <div class="text-secondary small" style="font-size: 0.8rem;">
                        Delivered on <strong>{{ $order->delivered_at ? $order->delivered_at->format('d M, Y \a\t h:i A') : $order->updated_at->format('d M, Y \a\t h:i A') }}</strong>
                        @if($order->rider_name) &bull; Handed over by <strong>{{ $order->rider_name }}</strong> @endif
                    </div>
                </div>
            </div>
        </div>
    @endif
@else
    {{-- Order Cancelled Banner --}}
    @php
        $cancellation = $order->cancellation;
        $isAdminCancelled = ($cancellation && $cancellation->cancelled_by_type === 'admin');
        $ticketNo = 'CNL-' . $order->order_number;
        $walletRefundVal = (float) ($cancellation?->wallet_refund_amount ?? 0);
        $onlineRefundVal = (float) ($cancellation?->online_refund_amount ?? 0);
        $totalRefundVal = (float) ($cancellation?->refund_amount ?? ($walletRefundVal + $onlineRefundVal));
        $refundProof = $cancellation?->razorpay_refund_id ?: $cancellation?->payment_reference;
    @endphp
    <div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-4" style="background: #ffffff; border: 1.5px solid #fecaca !important; border-left: 5px solid #ef4444 !important;">
        <div class="card-header py-3 px-4 border-bottom border-danger-subtle d-flex align-items-center justify-content-between flex-wrap gap-2" style="background: #fff5f5;">
            <div class="d-flex align-items-center gap-2.5">
                <div class="rounded-circle bg-danger text-white d-flex align-items-center justify-content-center flex-shrink-0 shadow-2xs" style="width: 36px; height: 36px;">
                    <i class="bi bi-x-circle-fill fs-5"></i>
                </div>
                <div>
                    <h6 class="fw-bold text-danger mb-0" style="font-size: 0.95rem;">Order Cancelled</h6>
                    <div class="text-secondary small" style="font-size: 0.76rem;">
                        Cancelled on {{ $cancellation ? $cancellation->created_at->format('d M, Y \a\t h:i A') : $order->updated_at->format('d M, Y \a\t h:i A') }}
                    </div>
                </div>
            </div>
            <div class="d-flex align-items-center gap-2">
                <span class="badge bg-white text-dark border font-monospace px-2.5 py-1">Ticket: {{ $ticketNo }}</span>
                <span class="badge {{ $isAdminCancelled ? 'bg-danger text-white' : 'bg-secondary text-white' }} rounded-pill px-2.5 py-1">
                    {{ $isAdminCancelled ? 'Store Staff Cancelled' : 'Customer Cancelled' }}
                </span>
            </div>
        </div>
        <div class="card-body p-4">
            <div class="row g-3">
                <div class="col-md-6">
                    <div class="p-3 rounded-3 bg-light border h-100">
                        <div class="text-uppercase text-muted fw-bold mb-1" style="font-size: 0.68rem;">Reason</div>
                        <div class="fw-bold text-dark small">{{ $cancellation?->cancellation_reason ?? 'No specific reason entered.' }}</div>
                        @if($cancellation && $cancellation->admin_notes)
                            <div class="mt-2 text-muted small p-2 rounded bg-white border" style="font-size: 0.75rem;">
                                <strong>Remarks:</strong> {{ $cancellation->admin_notes }}
                            </div>
                        @endif
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="p-3 rounded-3 bg-light border h-100">
                        <div class="text-uppercase text-muted fw-bold mb-1" style="font-size: 0.68rem;">Refund Summary</div>
                        <div class="d-flex align-items-center justify-content-between mb-1">
                            <span class="text-secondary small">Total Refunded:</span>
                            <strong class="font-monospace text-success fs-6">₹{{ number_format($totalRefundVal, 2) }}</strong>
                        </div>
                        @if($walletRefundVal > 0)
                            <div class="small text-muted" style="font-size: 0.74rem;">• Store Wallet: ₹{{ number_format($walletRefundVal, 2) }}</div>
                        @endif
                        @if($onlineRefundVal > 0)
                            <div class="small text-muted" style="font-size: 0.74rem;">• Razorpay PG: ₹{{ number_format($onlineRefundVal, 2) }}</div>
                        @endif
                        @if($refundProof)
                            <div class="small text-muted mt-1 font-monospace" style="font-size: 0.74rem;">Bank UTR: <strong>{{ $refundProof }}</strong></div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
@endif

{{-- ── 3. Active Delivery Issue Banner (If Rider Reported Exception) ── --}}
@php
    $hasActiveException = $order->fulfillment?->hasActiveDeliveryIssue() && !in_array($order->status, ['delivered', 'cancelled', 'returned']);
@endphp
@if($hasActiveException)
    <div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-4" style="border: 1.5px solid #fecdd3 !important; border-left: 5px solid #e11d48 !important; background: #ffffff;">
        <div class="card-header bg-white py-3 px-4 border-bottom d-flex align-items-center justify-content-between">
            <div class="d-flex align-items-center gap-2">
                <div class="rounded-circle bg-danger text-white d-flex align-items-center justify-content-center shadow-2xs" style="width: 28px; height: 28px; font-size: 0.8rem;">
                    <i class="bi bi-exclamation-triangle-fill"></i>
                </div>
                <h6 class="mb-0 fw-bold text-danger" style="font-size: 0.94rem;">Active Delivery Exception Reported</h6>
            </div>
            <span class="badge bg-danger rounded-pill px-2.5 py-1 text-white small fw-bold">Action Needed</span>
        </div>
        <div class="card-body p-4">
            <div class="alert alert-danger bg-danger bg-opacity-10 border-0 rounded-3 p-3 mb-3">
                <div class="fw-bold text-danger small"><i class="bi bi-megaphone-fill me-1"></i> Issue:</div>
                <div class="text-dark fw-bold mt-1" style="font-size: 0.9rem;">{{ $order->fulfillment->delivery_issue }}</div>
                <div class="text-secondary small mt-1 font-monospace" style="font-size: 0.75rem;">
                    Reported by <strong>{{ $order->fulfillment->rider?->name ?? ($order->rider_name ?? 'Fleet Rider') }}</strong> &bull; {{ $order->fulfillment->delivery_issue_at?->diffForHumans() ?? 'Recently' }}
                </div>
            </div>

            <div class="d-flex align-items-center gap-2 mb-3">
                <a href="tel:{{ $order->shipping_phone }}" class="btn btn-sm btn-success rounded-pill px-3 py-2 flex-grow-1 fw-bold text-center shadow-2xs" style="font-size: 0.8rem;">
                    <i class="bi bi-telephone-fill me-1"></i> Call Customer ({{ $order->shipping_phone }})
                </a>
                @php
                    $cleanP = preg_replace('/[^0-9]/', '', $order->shipping_phone);
                    if (strlen($cleanP) === 10) $cleanP = '91' . $cleanP;
                @endphp
                <a href="https://wa.me/{{ $cleanP }}" target="_blank" class="btn btn-sm btn-outline-success rounded-pill px-3 py-2 flex-grow-1 fw-bold text-center shadow-2xs" style="font-size: 0.8rem;">
                    <i class="bi bi-whatsapp me-1"></i> WhatsApp
                </a>
            </div>

            <form action="{{ route('order-manager.orders.resolve-exception', $order) }}" method="POST">
                @csrf
                <div class="row g-2 align-items-end">
                    <div class="col-md-5">
                        <label class="form-label fw-bold text-dark small mb-1">Resolution Action</label>
                        <select name="resolution_action" class="form-select form-select-sm" required>
                            <option value="re-attempt">🚀 1. Re-attempt Delivery Today</option>
                            <option value="reschedule">📅 2. Reschedule Delivery Slot</option>
                            <option value="return_warehouse">🔄 3. Return Parcel to Warehouse</option>
                            <option value="cancel">❌ 4. Customer Refused — Cancel Order</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-bold text-dark small mb-1">Resolution Notes</label>
                        <input type="text" name="resolution_notes" class="form-control form-control-sm" placeholder="e.g. Spoke to customer, delivering now">
                    </div>
                    <div class="col-md-3">
                        <button type="submit" class="btn btn-sm btn-danger w-100 fw-bold rounded-pill py-2 shadow-2xs">
                            Apply Resolution
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
@endif

{{-- ── 4. Main Two-Column Layout ── --}}
<div class="row g-4">
    {{-- ── LEFT COLUMN (7 Cols) — Operations, Products & Timeline ── --}}
    <div class="col-lg-7">
        {{-- Card A: Unified Logistics & Rider Dispatch Card --}}
        @if(!$isCancelled && !$isDelivered)
            <div id="dispatchCard" class="card border-0 shadow-sm rounded-4 overflow-hidden mb-4" style="border: 1px solid #e2e8f0 !important;">
                <div class="card-header bg-white py-3 px-4 border-bottom d-flex align-items-center justify-content-between">
                    <h6 class="mb-0 fw-bold text-dark d-flex align-items-center gap-2" style="font-size: 0.95rem;">
                        <i class="bi {{ $order->isLocalBengaluruDelivery() ? 'bi-geo-fill text-info' : 'bi-truck text-primary' }}"></i>
                        <span>{{ $order->isLocalBengaluruDelivery() ? 'Bengaluru Local Fleet Dispatch' : 'National Courier Logistics' }}</span>
                    </h6>
                    <span class="badge rounded-pill px-2.5 py-1 fw-bold text-white shadow-2xs" style="background: {{ $order->isLocalBengaluruDelivery() ? '#0284c7' : '#4f46e5' }}; font-size: 0.68rem;">
                        {{ $order->isLocalBengaluruDelivery() ? 'In-House Fleet' : '3PL Carrier' }}
                    </span>
                </div>

                <div class="card-body p-4">
                    @if($order->isLocalBengaluruDelivery())
                        {{-- Bengaluru Local Rider Assignment Form --}}
                        <form id="formAssignRider" action="{{ route('order-manager.orders.assign-rider', $order) }}" method="POST">
                            @csrf
                            <div class="row g-3">
                                <div class="col-12">
                                    <label class="form-label fw-bold text-dark small mb-1">Select Delivery Partner / Rider</label>
                                    <select id="selectRegisteredRider" name="rider_id" class="form-select form-select-sm">
                                        <option value="">-- Choose In-House Delivery Partner --</option>
                                        @foreach($deliveryPartners as $dp)
                                            <option value="{{ $dp->id }}" 
                                                    data-name="{{ $dp->name }}" 
                                                    data-phone="{{ $dp->mobile_number }}"
                                                    {{ $order->rider_id === $dp->id ? 'selected' : '' }}>
                                                🛵 {{ $dp->name }} ({{ $dp->mobile_number ?? 'No Phone' }}) &bull; {{ $dp->assigned_deliveries_count }} active loads
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-sm-6">
                                    <label class="form-label fw-bold text-dark small mb-1">Rider Name <span class="text-danger">*</span></label>
                                    <input type="text" id="inputRiderName" name="rider_name" class="form-control form-control-sm" 
                                           value="{{ old('rider_name', $order->rider_name) }}" required placeholder="e.g. Ramesh Kumar">
                                </div>

                                <div class="col-sm-6">
                                    <label class="form-label fw-bold text-dark small mb-1">Rider Mobile Number <span class="text-danger">*</span></label>
                                    <input type="text" id="inputRiderPhone" name="rider_phone" class="form-control form-control-sm" 
                                           value="{{ old('rider_phone', $order->rider_phone) }}" required placeholder="e.g. 9876543210">
                                </div>

                                <div class="col-sm-6">
                                    <label class="form-label fw-bold text-dark small mb-1">Delivery Window Slot</label>
                                    <select id="selectDeliverySlot" name="delivery_slot" class="form-select form-select-sm">
                                        <option value="Next-Day Express Delivery (1-2 Days)" {{ ($order->delivery_slot === 'Next-Day Express Delivery (1-2 Days)' || !$order->delivery_slot) ? 'selected' : '' }}>⚡ Next-Day Express (1-2 Days)</option>
                                        <option value="Morning Slot (10:00 AM - 02:00 PM)" {{ $order->delivery_slot === 'Morning Slot (10:00 AM - 02:00 PM)' ? 'selected' : '' }}>🌅 Morning (10 AM - 2 PM)</option>
                                        <option value="Evening Slot (04:00 PM - 08:00 PM)" {{ $order->delivery_slot === 'Evening Slot (04:00 PM - 08:00 PM)' ? 'selected' : '' }}>🌆 Evening (4 PM - 8 PM)</option>
                                        <option value="Standard Local Delivery (2-3 Days)" {{ $order->delivery_slot === 'Standard Local Delivery (2-3 Days)' ? 'selected' : '' }}>📦 Standard Local (2-3 Days)</option>
                                    </select>
                                </div>

                                <div class="col-sm-6">
                                    <label class="form-label fw-bold text-dark small mb-1">Delivery Instructions</label>
                                    <input type="text" id="inputRiderNotes" name="notes" class="form-control form-control-sm" 
                                           value="{{ old('notes', $order->fulfillment?->notes) }}" placeholder="e.g. Call customer before gate">
                                </div>

                                <div class="col-12 pt-2">
                                    <button type="submit" id="btnSubmitRider" class="btn text-white w-100 fw-bold rounded-pill shadow-xs py-2 d-inline-flex align-items-center justify-content-center gap-2" style="background: #0284c7; border: none; font-size: 0.88rem;">
                                        <i class="bi bi-send-fill"></i>
                                        <span>Assign Rider & Mark Out for Delivery</span>
                                    </button>
                                </div>
                            </div>
                        </form>
                    @else
                        {{-- National Courier Tracking Form --}}
                        <form id="formUpdateTracking" action="{{ route('order-manager.orders.update-tracking', $order) }}" method="POST">
                            @csrf
                            <div class="row g-3">
                                <div class="col-sm-6">
                                    <label class="form-label fw-bold text-dark small mb-1">Courier Partner <span class="text-danger">*</span></label>
                                    <select id="selectCourierPartner" name="courier_partner" class="form-select form-select-sm" required>
                                        <option value="Delhivery" {{ $order->courier_partner === 'Delhivery' ? 'selected' : '' }}>Delhivery Express</option>
                                        <option value="BlueDart" {{ $order->courier_partner === 'BlueDart' ? 'selected' : '' }}>BlueDart Aviation</option>
                                        <option value="DTDC" {{ $order->courier_partner === 'DTDC' ? 'selected' : '' }}>DTDC Courier</option>
                                        <option value="Ecom Express" {{ $order->courier_partner === 'Ecom Express' ? 'selected' : '' }}>Ecom Express</option>
                                        <option value="FedEx" {{ $order->courier_partner === 'FedEx' ? 'selected' : '' }}>FedEx India</option>
                                        <option value="India Post" {{ $order->courier_partner === 'India Post' ? 'selected' : '' }}>India Post SpeedPost</option>
                                        <option value="Shadowfax" {{ $order->courier_partner === 'Shadowfax' ? 'selected' : '' }}>Shadowfax</option>
                                    </select>
                                </div>

                                <div class="col-sm-6">
                                    <label class="form-label fw-bold text-dark small mb-1">AWB / Tracking Number <span class="text-danger">*</span></label>
                                    <input type="text" id="inputTrackingNumber" name="tracking_number" class="form-control form-control-sm font-monospace" 
                                           value="{{ old('tracking_number', $order->tracking_number) }}" required placeholder="e.g. BD983471092">
                                </div>

                                <div class="col-12">
                                    <label class="form-label fw-bold text-dark small mb-1">Tracking URL (Optional)</label>
                                    <input type="url" id="inputTrackingUrl" name="tracking_url" class="form-control form-control-sm" 
                                           value="{{ old('tracking_url', $order->tracking_url) }}" placeholder="https://www.bluedart.com/track/...">
                                </div>

                                <div class="col-12 pt-2">
                                    <button type="submit" id="btnSubmitTracking" class="btn text-white w-100 fw-bold rounded-pill shadow-xs py-2 d-inline-flex align-items-center justify-content-center gap-2" style="background: #4f46e5; border: none; font-size: 0.88rem;">
                                        <i class="bi bi-box-seam-fill"></i>
                                        <span>Save Tracking & Mark as Shipped</span>
                                    </button>
                                </div>
                            </div>
                        </form>
                    @endif
                </div>

                {{-- Clean Collapsible Manual Status & Notes Override --}}
                <div class="card-footer bg-light border-top p-3">
                    <div class="accordion accordion-flush" id="accordionStatusOverride">
                        <div class="accordion-item bg-transparent border-0">
                            <h2 class="accordion-header" id="headingOverride">
                                <button class="accordion-button collapsed py-1 px-0 bg-transparent text-secondary small fw-bold shadow-none" type="button" data-bs-toggle="collapse" data-bs-target="#collapseOverride" aria-expanded="false" aria-controls="collapseOverride" style="font-size: 0.8rem;">
                                    <i class="bi bi-sliders me-1.5 text-primary"></i> Need to force a different status or add warehouse note?
                                </button>
                            </h2>
                            <div id="collapseOverride" class="accordion-collapse collapse" aria-labelledby="headingOverride">
                                <div class="pt-3">
                                    <form id="formUpdateStatus" action="{{ route('order-manager.orders.update-status', $order) }}" method="POST">
                                        @csrf
                                        <div class="row g-2 align-items-end">
                                            <div class="col-sm-5">
                                                <label class="form-label text-dark small fw-bold mb-1">Change Status to:</label>
                                                <select id="orderStatusSelect" name="status" class="form-select form-select-sm fw-bold">
                                                    <option value="pending" {{ $order->status === 'pending' ? 'selected' : '' }}>⏳ Pending</option>
                                                    <option value="confirmed" {{ $order->status === 'confirmed' ? 'selected' : '' }}>✅ Confirmed</option>
                                                    <option value="processing" {{ $order->status === 'processing' ? 'selected' : '' }}>📦 Processing (Warehouse)</option>
                                                    <option value="packed" {{ $order->status === 'packed' ? 'selected' : '' }}>🏷️ Packed & Ready</option>
                                                    <option value="shipped" {{ $order->status === 'shipped' ? 'selected' : '' }}>🚚 Shipped (In-Transit)</option>
                                                    <option value="out for delivery" {{ $order->status === 'out for delivery' ? 'selected' : '' }}>📍 Out for Delivery</option>
                                                    @if($order->isLocalBengaluruDelivery() || $order->rider_id)
                                                        @if($order->status === 'delivered')
                                                            <option value="delivered" selected>🎉 Delivered</option>
                                                        @else
                                                            <option value="delivered" disabled class="text-muted">🔒 Delivered (Delivery Partner OTP Handover Only)</option>
                                                        @endif
                                                    @else
                                                        <option value="delivered" {{ $order->status === 'delivered' ? 'selected' : '' }}>🎉 Delivered</option>
                                                    @endif
                                                </select>
                                            </div>
                                            <div class="col-sm-5">
                                                <label class="form-label text-dark small fw-bold mb-1">Fulfillment Note</label>
                                                <input type="text" id="statusNotesInput" name="notes" class="form-control form-control-sm" placeholder="Internal log notes...">
                                            </div>
                                            <div class="col-sm-2">
                                                <button type="submit" id="btnSubmitStatus" class="btn btn-sm btn-dark w-100 fw-bold rounded-pill">
                                                    Update
                                                </button>
                                            </div>
                                        </div>
                                    @if($order->isLocalBengaluruDelivery() || $order->rider_id)
                                        <div class="form-text text-muted small mt-1.5">
                                            <i class="bi bi-shield-lock-fill text-warning me-1"></i> Delivery completion is restricted to the assigned delivery partner upon customer OTP verification.
                                        </div>
                                    @endif
                                </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        {{-- Card B: Ordered Items & Financial Summary Table --}}
        <div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-4" style="border: 1px solid #e2e8f0 !important;">
            <div class="card-header bg-white py-3 px-4 border-bottom d-flex align-items-center justify-content-between">
                <h6 class="mb-0 fw-bold text-dark d-flex align-items-center gap-2" style="font-size: 0.95rem;">
                    <i class="bi bi-box-seam-fill text-primary"></i>
                    <span>Purchased Items ({{ $order->items->count() }})</span>
                </h6>
                <span class="badge bg-light text-dark border px-2.5 py-1 rounded-pill small fw-bold">
                    {{ $order->items->sum('quantity') }} Total Units
                </span>
            </div>

            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead style="background: #f8fafc; border-bottom: 1px solid #e2e8f0; color: #475569; font-size: 0.74rem; letter-spacing: 0.04em;">
                        <tr>
                            <th class="ps-4 py-2.5 text-uppercase fw-bold">Product</th>
                            <th class="py-2.5 text-uppercase fw-bold">SKU</th>
                            <th class="py-2.5 text-center text-uppercase fw-bold">Qty</th>
                            <th class="py-2.5 text-end text-uppercase fw-bold">Unit Price</th>
                            <th class="pe-4 py-2.5 text-end text-uppercase fw-bold">Line Total</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y">
                        @foreach($order->items as $item)
                            <tr>
                                <td class="ps-4 py-3">
                                    <div class="d-flex align-items-center gap-3">
                                        @if($item->product && $item->product->main_image)
                                            <img src="{{ asset('storage/' . $item->product->main_image) }}" alt="{{ $item->product_name }}" 
                                                 class="rounded-3 border object-fit-contain shadow-2xs p-1 bg-white flex-shrink-0" width="48" height="48">
                                        @else
                                            <div class="rounded-3 bg-light border d-flex align-items-center justify-content-center text-muted shadow-2xs flex-shrink-0" style="width: 48px; height: 48px;">
                                                <i class="bi bi-image fs-5 opacity-25"></i>
                                            </div>
                                        @endif
                                        <div class="min-w-0">
                                            <div class="fw-bold text-dark small text-truncate" style="font-size: 0.88rem; max-width: 260px;" title="{{ $item->product_name }}">
                                                {{ $item->product_name }}
                                            </div>
                                            <div class="d-flex align-items-center gap-1.5 flex-wrap mt-0.5">
                                                @if($item->selected_option)
                                                    <span class="badge rounded-pill px-2 py-0.5 fw-bold d-inline-flex align-items-center gap-1 shadow-2xs" 
                                                          style="background: #e0f2fe !important; color: #0369a1 !important; border: 1px solid #7dd3fc !important; font-size: 0.68rem;">
                                                        <i class="bi bi-tag-fill" style="font-size: 0.6rem; color: #0284c7;"></i>
                                                        <span>{{ $item->selected_option }}</span>
                                                    </span>
                                                @endif
                                                @if($item->product)
                                                    <a href="{{ route('admin.products.show', $item->product_id) }}" target="_blank" class="text-secondary small text-decoration-none" style="font-size: 0.72rem;">
                                                        Catalog <i class="bi bi-box-arrow-up-right" style="font-size: 0.62rem;"></i>
                                                    </a>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </td>

                                <td class="font-monospace small text-secondary">
                                    {{ $item->product?->sku ?? '—' }}
                                </td>

                                <td class="text-center fw-bold text-dark">
                                    <span class="badge bg-light text-dark border px-2.5 py-1 rounded-pill fw-bold" style="font-size: 0.76rem;">
                                        ×{{ $item->quantity }}
                                    </span>
                                </td>

                                <td class="text-end small text-secondary font-monospace">
                                    ₹{{ number_format($item->unit_price ?? $item->price ?? 0, 2) }}
                                </td>

                                <td class="pe-4 text-end fw-bold text-dark font-monospace" style="font-size: 0.92rem;">
                                    ₹{{ number_format($item->total_price ?? (($item->unit_price ?? $item->price ?? 0) * $item->quantity), 2) }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{-- Financial Summary Footer --}}
            <div class="card-footer bg-light border-top p-3.5">
                <div class="row justify-content-end">
                    <div class="col-sm-7 col-md-6 col-lg-6">
                        <div class="d-flex justify-content-between mb-1.5 small text-secondary">
                            <span>Subtotal Amount:</span>
                            <span class="fw-bold text-dark font-monospace">₹{{ number_format($order->subtotal_amount, 2) }}</span>
                        </div>

                        @if($order->coupon_discount_amount > 0)
                            <div class="d-flex justify-content-between mb-1.5 small text-success">
                                <span>Coupon ({{ $order->coupon?->code ?? 'PROMO' }}):</span>
                                <span class="fw-bold font-monospace">- ₹{{ number_format($order->coupon_discount_amount, 2) }}</span>
                            </div>
                        @endif

                        @if($order->wallet_amount_used > 0)
                            <div class="d-flex justify-content-between mb-1.5 small text-primary">
                                <span><i class="bi bi-wallet2 me-1"></i>Wallet Applied:</span>
                                <span class="fw-bold font-monospace">- ₹{{ number_format($order->wallet_amount_used, 2) }}</span>
                            </div>
                        @endif

                        <div class="d-flex justify-content-between mb-1.5 small text-secondary">
                            <span>Delivery Charges:</span>
                            @if((float) $order->shipping_charge > 0)
                                <span class="fw-bold text-dark font-monospace">+₹{{ number_format($order->shipping_charge, 2) }}</span>
                            @else
                                <span class="badge bg-success bg-opacity-10 text-success fw-bold rounded-pill px-2">FREE</span>
                            @endif
                        </div>

                        @if((float) $order->cod_fee > 0)
                            <div class="d-flex justify-content-between mb-1.5 small text-secondary">
                                <span><i class="bi bi-cash-stack me-1"></i>COD Handling Fee:</span>
                                <span class="fw-bold text-dark font-monospace">+₹{{ number_format($order->cod_fee, 2) }}</span>
                            </div>
                        @endif

                        <hr class="my-2 border-secondary-subtle">

                        <div class="d-flex justify-content-between align-items-center">
                            <span class="fw-bold text-dark" style="font-size: 0.95rem;">
                                {{ $order->payment_status === 'paid' ? 'Grand Total Paid:' : 'Grand Total Payable:' }}
                            </span>
                            <span class="h4 fw-extrabold text-primary mb-0 font-monospace" style="font-size: 1.25rem;">
                                ₹{{ number_format($order->total_amount, 2) }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Card C: Timeline & Activity Audit Trail --}}
        <div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-4" style="border: 1px solid #e2e8f0 !important;">
            <div class="card-header bg-white py-3 px-4 border-bottom">
                <h6 class="mb-0 fw-bold text-dark d-flex align-items-center gap-2" style="font-size: 0.95rem;">
                    <i class="bi bi-clock-history text-secondary"></i>
                    <span>Fulfillment Timeline & Status Audit</span>
                </h6>
            </div>
            <div class="card-body p-4">
                <div id="timelineContainer" class="timeline position-relative ps-3">
                    @forelse($order->statusHistories->sortByDesc('created_at') as $history)
                        <div class="position-relative pb-3.5 ps-4 border-start border-2 border-primary timeline-item">
                            <div class="position-absolute start-0 top-0 translate-middle-x rounded-circle bg-primary text-white d-flex align-items-center justify-content-center shadow-2xs" 
                                 style="width: 18px; height: 18px; font-size: 0.6rem; left: -1px;">
                                <i class="bi bi-check-lg"></i>
                            </div>
                            <div class="d-flex align-items-center justify-content-between mb-0.5">
                                <span class="badge bg-light text-dark border px-2 py-0.5 rounded-pill fw-bold" style="font-size: 0.7rem;">
                                    {{ ucfirst(str_replace('_', ' ', $history->current_status)) }}
                                </span>
                                <span class="text-secondary small" style="font-size: 0.72rem;">
                                    {{ $history->created_at->format('d M Y, h:i A') }} ({{ $history->created_at->diffForHumans() }})
                                </span>
                            </div>
                            <p class="text-dark small mb-0 mt-0.5" style="font-size: 0.82rem; line-height: 1.35;">
                                {{ $history->notes }}
                            </p>
                            @if($history->user)
                                <span class="text-secondary small mt-0.5 d-inline-block" style="font-size: 0.68rem;">By: {{ $history->user->name }}</span>
                            @endif
                        </div>
                    @empty
                        <div id="emptyTimelineMsg" class="text-center py-3 text-secondary small">No status change events logged yet.</div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    {{-- ── RIGHT COLUMN (5 Cols) — Customer, Location & Security ── --}}
    <div class="col-lg-5">
        {{-- Card 1: Handover OTP & Doorstep Payment Security --}}
        @if($order->delivery_otp && !$isCancelled)
            <div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-4" style="background: #ffffff; border: 1.5px solid #cbd5e1 !important;">
                <div class="card-header bg-dark text-white py-2.5 px-3.5 d-flex align-items-center justify-content-between">
                    <span class="small fw-bold text-uppercase d-flex align-items-center gap-1.5" style="font-size: 0.74rem; letter-spacing: 0.04em;">
                        <i class="bi bi-shield-lock-fill text-warning"></i> Customer Handover Code
                    </span>
                    <span class="badge bg-warning text-dark rounded-pill px-2 py-0.5 fw-bold" style="font-size: 0.65rem;">
                        Doorstep OTP
                    </span>
                </div>
                <div class="card-body p-3.5">
                    <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
                        <div>
                            <div class="text-secondary small mb-1" style="font-size: 0.74rem;">Share code with rider upon handover:</div>
                            <div class="d-flex align-items-center gap-1.5">
                                @foreach(str_split((string) $order->delivery_otp) as $digit)
                                    <span class="d-inline-flex align-items-center justify-content-center fw-extrabold rounded-2 bg-light border text-dark shadow-2xs font-monospace" 
                                          style="width: 36px; height: 40px; font-size: 1.35rem; border-color: #94a3b8 !important;">
                                        {{ $digit }}
                                    </span>
                                @endforeach
                                <button type="button" class="btn btn-sm btn-light border rounded-pill px-2.5 py-1 text-secondary fw-semibold ms-1" 
                                        onclick="navigator.clipboard.writeText('{{ $order->delivery_otp }}'); this.innerText = 'Copied!'; setTimeout(() => this.innerHTML = '<i class=\'bi bi-copy\'></i>', 1500);" style="font-size: 0.74rem;" title="Copy OTP">
                                    <i class="bi bi-copy"></i>
                                </button>
                            </div>
                        </div>

                        {{-- Payment Due at Doorstep --}}
                        <div class="text-start text-sm-end">
                            <div class="text-uppercase text-muted fw-bold small" style="font-size: 0.65rem;">Doorstep Collection</div>
                            @if($order->payment_status === 'paid')
                                <span class="badge bg-success bg-opacity-15 text-success rounded-pill px-2.5 py-1 fw-bold" style="font-size: 0.74rem;">
                                    <i class="bi bi-check-circle-fill me-1"></i> Collect ₹0 (Paid)
                                </span>
                            @else
                                <div class="h5 fw-extrabold text-dark mb-0 font-monospace">₹{{ number_format($order->total_amount, 2) }}</div>
                                <div class="text-muted small" style="font-size: 0.68rem;">Cash or UPI QR</div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        @endif

        {{-- Card 2: Customer & Delivery Address Card --}}
        <div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-4" style="border: 1px solid #e2e8f0 !important;">
            <div class="card-header bg-white py-3 px-4 border-bottom d-flex align-items-center justify-content-between">
                <h6 class="mb-0 fw-bold text-dark d-flex align-items-center gap-2" style="font-size: 0.95rem;">
                    <i class="bi bi-person-circle text-primary"></i>
                    <span>Customer & Delivery Destination</span>
                </h6>
                <span class="badge bg-light text-secondary border rounded-pill px-2 py-0.5 small">
                    ID #{{ $order->user_id }}
                </span>
            </div>

            <div class="card-body p-4">
                {{-- Customer Identity & Fast Contact Buttons --}}
                <div class="d-flex align-items-start justify-content-between flex-wrap gap-2 mb-3 pb-3 border-bottom">
                    <div>
                        <div class="fw-bold text-dark" style="font-size: 1rem;">{{ $order->shipping_name }}</div>
                        <div class="text-muted small mt-0.5"><i class="bi bi-envelope me-1"></i>{{ $order->shipping_email }}</div>
                    </div>
                    <div class="d-flex align-items-center gap-1.5">
                        <a href="tel:{{ $order->shipping_phone }}" class="btn btn-sm btn-outline-success rounded-pill px-3 py-1 fw-bold shadow-2xs d-inline-flex align-items-center gap-1" style="font-size: 0.78rem;">
                            <i class="bi bi-telephone-fill"></i> <span>Call</span>
                        </a>
                        @php
                            $cleanPhone = preg_replace('/[^0-9]/', '', $order->shipping_phone);
                            if (strlen($cleanPhone) === 10) $cleanPhone = '91' . $cleanPhone;
                        @endphp
                        <a href="https://wa.me/{{ $cleanPhone }}" target="_blank" class="btn btn-sm btn-success rounded-pill px-3 py-1 fw-bold text-white shadow-2xs d-inline-flex align-items-center gap-1" style="font-size: 0.78rem;">
                            <i class="bi bi-whatsapp"></i> <span>WhatsApp</span>
                        </a>
                    </div>
                </div>

                {{-- Delivery Address Block --}}
                <div class="mb-3">
                    <div class="text-secondary small fw-bold text-uppercase mb-1.5" style="font-size: 0.68rem; letter-spacing: 0.04em;">
                        <i class="bi bi-geo-alt-fill text-danger me-1"></i> Delivery Street Address
                    </div>
                    <div class="p-3 rounded-3 bg-light border text-dark small" style="line-height: 1.5; font-size: 0.84rem;">
                        <div class="fw-semibold text-dark">{{ $order->shipping_address }}</div>
                        <div>{{ $order->shipping_city }}, {{ $order->shipping_state }} - <strong>{{ $order->shipping_zip }}</strong></div>
                        <div class="text-secondary">{{ $order->shipping_country ?? 'India' }}</div>
                        @if($order->shipping_landmark)
                            <div class="mt-1 pt-1 border-top border-secondary-subtle text-muted">
                                <strong>Landmark:</strong> {{ $order->shipping_landmark }}
                            </div>
                        @endif
                    </div>
                </div>

                {{-- Map Coordinates Link (If Lat/Lng Present) --}}
                @if($order->shipping_latitude && $order->shipping_longitude)
                    <div class="p-2.5 rounded-3 border d-flex align-items-center justify-content-between mb-2" style="background: #f0fdf4; border-color: #bbf7d0 !important;">
                        <div class="d-flex align-items-center gap-2">
                            <i class="bi bi-geo-fill text-success fs-5"></i>
                            <div>
                                <div class="fw-bold text-success small" style="font-size: 0.78rem;">Exact GPS Pin Verified</div>
                                <div class="text-muted font-monospace" style="font-size: 0.7rem;">{{ round($order->shipping_latitude, 5) }}, {{ round($order->shipping_longitude, 5) }}</div>
                            </div>
                        </div>
                        <a href="https://www.google.com/maps?q={{ $order->shipping_latitude }},{{ $order->shipping_longitude }}" target="_blank" class="btn btn-sm btn-success rounded-pill px-3 py-1 fw-bold text-white" style="font-size: 0.75rem;">
                            <i class="bi bi-box-arrow-up-right me-1"></i> View on Map
                        </a>
                    </div>
                @else
                    <a href="https://www.google.com/maps/search/?api=1&query={{ urlencode($order->shipping_address . ' ' . $order->shipping_city . ' ' . $order->shipping_zip) }}" target="_blank" class="btn btn-sm btn-light border rounded-pill w-100 fw-semibold text-secondary py-1.5" style="font-size: 0.78rem;">
                        <i class="bi bi-search me-1"></i> Search Address on Google Maps
                    </a>
                @endif
            </div>
        </div>

        {{-- Card 3: Payment & Gateway Audit Trail --}}
        <div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-4" style="border: 1px solid #e2e8f0 !important;">
            <div class="card-header bg-white py-3 px-4 border-bottom d-flex align-items-center justify-content-between">
                <h6 class="mb-0 fw-bold text-dark d-flex align-items-center gap-2" style="font-size: 0.95rem;">
                    <i class="bi bi-credit-card-2-front-fill text-success"></i>
                    <span>Payment & Transaction Audit</span>
                </h6>
                {!! $payment?->status_badge ?? ($order->payment_status === 'paid' ? '<span class="badge bg-success rounded-pill px-2.5 py-1 text-white">Paid</span>' : '<span class="badge bg-warning text-dark rounded-pill px-2.5 py-1">Pending</span>') !!}
            </div>

            <div class="card-body p-4 small">
                <div class="mb-2.5">
                    <span class="text-secondary fw-semibold text-uppercase" style="font-size: 0.68rem;">Payment Channel</span>
                    <div class="fw-bold text-dark mt-0.5" style="font-size: 0.88rem;">
                        @if($order->payment_method === 'wallet' || ($order->total_amount <= 0 && $order->wallet_amount_used > 0))
                            💳 ShopCalm Wallet (100% Paid)
                        @elseif($order->wallet_amount_used > 0)
                            💳 ShopCalm Wallet (₹{{ number_format($order->wallet_amount_used, 2) }}) + {{ $order->payment_method === 'online' ? '⚡ Razorpay PG (' . ($payment?->method_display ?? 'Online UPI') . ')' : ($order->payment_method === 'doorstep_upi' ? '📲 Doorstep UPI (Razorpay QR)' : '💵 Cash on Delivery (COD)') }}
                        @elseif($order->payment_method === 'doorstep_upi')
                            📲 Doorstep UPI &bull; Paid via Razorpay Dynamic QR to Fleet Rider
                            @if($order->cod_fee > 0)
                                <div class="small fw-normal text-muted mt-0.5">Originally ordered as COD &bull; Settled digitally at doorstep</div>
                            @endif
                        @elseif($order->payment_method === 'online')
                            ⚡ Razorpay PG ({{ $payment?->method_display ?? 'Online UPI / Card' }})
                        @elseif($order->payment_method === 'cod')
                            @if($order->payment_status === 'paid')
                                💵 Cash on Delivery &bull; Paid in Cash at Doorstep
                            @else
                                💵 Cash on Delivery (COD) &bull; Pending Collection
                            @endif
                        @else
                            {{ strtoupper($order->payment_method) }}
                        @endif
                    </div>
                </div>

                @if($payment && $payment->bank_reference)
                    <div class="mb-2.5 p-2 rounded-2 bg-light border">
                        <span class="text-secondary fw-semibold text-uppercase" style="font-size: 0.65rem;">Bank UTR / Reference</span>
                        <div class="fw-bold font-monospace text-primary" style="font-size: 0.88rem;">
                            {{ $payment->bank_reference }}
                        </div>
                    </div>
                @endif

                @if($payment && $payment->gateway_payment_id)
                    <div class="mb-2.5">
                        <span class="text-secondary fw-semibold text-uppercase" style="font-size: 0.65rem;">Razorpay Payment ID</span>
                        <div class="text-dark font-monospace" style="font-size: 0.82rem;">
                            #{{ $payment->gateway_payment_id }}
                        </div>
                    </div>
                @endif

                @if($payment && $payment->payment_time)
                    <div>
                        <span class="text-secondary fw-semibold text-uppercase" style="font-size: 0.65rem;">Verified At</span>
                        <div class="text-muted" style="font-size: 0.78rem;">
                            {{ $payment->payment_time->format('d M Y, h:i A') }}
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const stages = ['pending', 'confirmed', 'processing', 'packed', 'shipped', 'out for delivery', 'delivered'];

    const Toast = Swal.mixin({
        toast: true,
        position: 'top-end',
        showConfirmButton: false,
        timer: 3500,
        timerProgressBar: true,
        didOpen: (toast) => {
            toast.addEventListener('mouseenter', Swal.stopTimer);
            toast.addEventListener('mouseleave', Swal.resumeTimer);
        }
    });

    function updateProgressTracker(newStatus) {
        const topBadge = document.getElementById('topStatusBadge');
        if (topBadge) {
            topBadge.textContent = 'Status: ' + newStatus.charAt(0).toUpperCase() + newStatus.slice(1).replace('_', ' ');
        }

        const idx = stages.indexOf(newStatus.toLowerCase().replace('_', ' '));
        const progressBar = document.getElementById('trackerProgressBar');
        if (progressBar && idx !== -1) {
            const pct = (idx / (stages.length - 1)) * 100;
            progressBar.style.width = pct + '%';
        }

        document.querySelectorAll('.stage-step').forEach((step, sIdx) => {
            const dot = step.querySelector('.stage-dot');
            const label = step.querySelector('.stage-label');
            const isPast = (idx !== -1 && sIdx <= idx);
            const isCurrent = (idx !== -1 && sIdx === idx);

            if (dot) {
                dot.style.background = isPast ? '#10b981' : '#ffffff';
                dot.style.color = isPast ? '#ffffff' : '#64748b';
                dot.style.border = isCurrent ? '2px solid #4f46e5' : (isPast ? '2px solid #10b981' : '2px solid #cbd5e1');
                dot.innerHTML = isPast ? '<i class="bi bi-check-lg"></i>' : (sIdx + 1);
            }
            if (label) {
                label.className = 'stage-label small fw-semibold mt-1 d-none d-md-block ' + (isCurrent ? 'text-primary fw-bold' : (isPast ? 'text-dark' : 'text-muted'));
            }
        });

        const statusSelect = document.getElementById('orderStatusSelect');
        if (statusSelect) {
            statusSelect.value = newStatus;
        }
    }

    function prependTimeline(status, notes) {
        const container = document.getElementById('timelineContainer');
        const emptyMsg = document.getElementById('emptyTimelineMsg');
        if (emptyMsg) emptyMsg.remove();

        if (container) {
            const item = document.createElement('div');
            item.className = 'position-relative pb-3.5 ps-4 border-start border-2 border-primary timeline-item';
            item.innerHTML = `
                <div class="position-absolute start-0 top-0 translate-middle-x rounded-circle bg-primary text-white d-flex align-items-center justify-content-center shadow-2xs" 
                     style="width: 18px; height: 18px; font-size: 0.6rem; left: -1px;">
                    <i class="bi bi-check-lg"></i>
                </div>
                <div class="d-flex align-items-center justify-content-between mb-0.5">
                    <span class="badge bg-light text-dark border px-2 py-0.5 rounded-pill fw-bold" style="font-size: 0.7rem;">
                        ${status.charAt(0).toUpperCase() + status.slice(1).replace('_', ' ')}
                    </span>
                    <span class="text-secondary small" style="font-size: 0.72rem;">Just now</span>
                </div>
                <p class="text-dark small mb-0 mt-0.5" style="font-size: 0.82rem; line-height: 1.35;">
                    ${notes || 'Status updated via Order Command Center'}
                </p>
                <span class="text-secondary small mt-0.5 d-inline-block" style="font-size: 0.68rem;">By: Staff Manager</span>
            `;
            container.insertBefore(item, container.firstChild);
        }
    }

    // 1. AJAX Status Update Form
    const formStatus = document.getElementById('formUpdateStatus');
    if (formStatus) {
        formStatus.addEventListener('submit', async function (e) {
            e.preventDefault();
            const btn = document.getElementById('btnSubmitStatus');
            const originalBtnHtml = btn ? btn.innerHTML : '';
            if (btn) {
                btn.disabled = true;
                btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1" role="status"></span> Updating...';
            }

            const formData = new FormData(formStatus);

            try {
                const res = await fetch(formStatus.action, {
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
                    Toast.fire({
                        icon: 'success',
                        title: data.message || 'Order status updated successfully!'
                    });
                    updateProgressTracker(data.status);
                    prependTimeline(data.status, formData.get('notes'));
                    const noteInput = document.getElementById('statusNotesInput');
                    if (noteInput) noteInput.value = '';
                } else {
                    Toast.fire({
                        icon: 'error',
                        title: data.message || 'Could not update status. Please try again.'
                    });
                }
            } catch (err) {
                console.error(err);
                Toast.fire({
                    icon: 'error',
                    title: 'Network error. Please try again.'
                });
            } finally {
                if (btn) {
                    btn.disabled = false;
                    btn.innerHTML = originalBtnHtml;
                }
            }
        });
    }

    // Auto-populate Rider details when selecting a registered delivery partner
    const selectRegisteredRider = document.getElementById('selectRegisteredRider');
    if (selectRegisteredRider) {
        selectRegisteredRider.addEventListener('change', function () {
            const selectedOpt = selectRegisteredRider.options[selectRegisteredRider.selectedIndex];
            if (selectedOpt && selectedOpt.value) {
                const name = selectedOpt.getAttribute('data-name');
                const phone = selectedOpt.getAttribute('data-phone');
                if (name && document.getElementById('inputRiderName')) document.getElementById('inputRiderName').value = name;
                if (phone && document.getElementById('inputRiderPhone')) document.getElementById('inputRiderPhone').value = phone;
            }
        });
    }

    // 2. AJAX Rider Assignment Form
    const formRider = document.getElementById('formAssignRider');
    if (formRider) {
        formRider.addEventListener('submit', async function (e) {
            e.preventDefault();
            const btn = document.getElementById('btnSubmitRider');
            const originalBtnHtml = btn ? btn.innerHTML : '';
            if (btn) {
                btn.disabled = true;
                btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1" role="status"></span> Assigning Rider...';
            }

            const formData = new FormData(formRider);

            try {
                const res = await fetch(formRider.action, {
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
                    Toast.fire({
                        icon: 'success',
                        title: data.message || 'Rider assigned & dispatched successfully!'
                    });
                    updateProgressTracker(data.status || 'out for delivery');
                    const riderInfo = document.getElementById('topLogisticsInfo');
                    if (riderInfo) {
                        riderInfo.innerHTML = `Bengaluru Local Fleet: Rider <strong>${data.rider_name}</strong> (${data.rider_phone})`;
                    }
                    prependTimeline('out for delivery', `Assigned rider: ${data.rider_name} (${data.rider_phone}) [${data.delivery_slot}]`);
                } else {
                    Toast.fire({
                        icon: 'error',
                        title: data.message || 'Could not assign rider. Please try again.'
                    });
                }
            } catch (err) {
                console.error(err);
                Toast.fire({
                    icon: 'error',
                    title: 'Network error. Please try again.'
                });
            } finally {
                if (btn) {
                    btn.disabled = false;
                    btn.innerHTML = originalBtnHtml;
                }
            }
        });
    }

    // 3. AJAX Courier Tracking Form
    const formTracking = document.getElementById('formUpdateTracking');
    if (formTracking) {
        formTracking.addEventListener('submit', async function (e) {
            e.preventDefault();
            const btn = document.getElementById('btnSubmitTracking');
            const originalBtnHtml = btn ? btn.innerHTML : '';
            if (btn) {
                btn.disabled = true;
                btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1" role="status"></span> Saving Tracking...';
            }

            const formData = new FormData(formTracking);

            try {
                const res = await fetch(formTracking.action, {
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
                    Toast.fire({
                        icon: 'success',
                        title: data.message || 'Tracking saved and order marked as Shipped!'
                    });
                    updateProgressTracker(data.status || 'shipped');
                    const info = document.getElementById('topLogisticsInfo');
                    if (info) {
                        info.innerHTML = `National Logistics: <strong>${data.courier_partner}</strong> &bull; AWB: <strong class="font-monospace">${data.tracking_number}</strong>`;
                    }
                    prependTimeline('shipped', `Dispatched via ${data.courier_partner}. AWB: ${data.tracking_number}`);
                } else {
                    Toast.fire({
                        icon: 'error',
                        title: data.message || 'Could not save tracking details.'
                    });
                }
            } catch (err) {
                console.error(err);
                Toast.fire({
                    icon: 'error',
                    title: 'Network error. Please try again.'
                });
            } finally {
                if (btn) {
                    btn.disabled = false;
                    btn.innerHTML = originalBtnHtml;
                }
            }
        });
    }
});


</script>
@endpush

{{-- ── Staff / Admin Cancel Order Modal ── --}}
@if(!in_array($order->status, ['cancelled', 'returned']))
<div class="modal fade" id="staffCancelOrderModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
            <div class="modal-header bg-danger text-white py-3 px-4">
                <h6 class="modal-title fw-bold text-white mb-0">
                    <i class="bi bi-shield-x me-2"></i> Cancel Order #{{ $order->order_number }}
                </h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('admin.orders.cancel', $order) }}" method="POST" class="d-flex flex-column h-100" style="min-height: 0; overflow: hidden; flex: 1 1 auto;">
                @csrf
                <div class="modal-body p-4">
                    <div class="alert alert-warning py-2.5 px-3 small rounded-3 mb-3 border-0 d-flex align-items-center gap-2" style="background: #fffbe6; color: #856404;">
                        <i class="bi bi-exclamation-triangle-fill fs-6 flex-shrink-0"></i>
                        <span>Cancelling will automatically restock inventory items. 100% refund will be issued to the customer with zero fee.</span>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold text-dark small">Admin Cancellation Reason Topic <span class="text-danger">*</span></label>
                        <select name="cancellation_reason" class="form-select fw-semibold" required>
                            <option value="" selected disabled>Select Cancellation Topic...</option>
                            <option value="Item Out of Stock / Inventory Unavailable">📦 Item Out of Stock / Inventory Unavailable</option>
                            <option value="Product Damaged in Warehouse / QC Failure">⚠️ Product Damaged in Warehouse / Quality Control Failure</option>
                            <option value="Delivery Location Unserviceable by Logistics Partner">🚚 Delivery Location Unserviceable by Logistics Partner</option>
                            <option value="Customer Requested Cancellation via Phone / Support Chat">📞 Customer Requested Cancellation via Phone / Support Chat</option>
                            <option value="Incorrect Pricing / Technical System Issue">🏷️ Incorrect Pricing / Technical System Issue</option>
                            <option value="Suspected Fraudulent Transaction / Duplicate Order">🚫 Suspected Fraudulent Transaction / Duplicate Order</option>
                            <option value="Other Store Admin Reason">📝 Other Store Admin Reason</option>
                        </select>
                    </div>

                    @php
                        $isPaid = ($order->payment_status === 'paid');
                        $walletUsed = (float) ($order->wallet_amount_used ?? 0);
                        $totalAmt = (float) ($order->total_amount ?? 0);
                        $method = strtolower($order->payment_method ?? 'cod');

                        if (!$isPaid && $method === 'cod') {
                            if ($walletUsed > 0) {
                                $destType = 'wallet';
                                $destTitle = 'ShopCalm Wallet (₹' . number_format($walletUsed, 2) . ')';
                                $destBadge = '⚡ Instant Wallet Credit';
                                $destDesc = 'Wallet amount of ₹' . number_format($walletUsed, 2) . ' will be refunded to customer\'s wallet. Cash on Delivery portion is ₹0 (No cash collected - 100% Free Cancellation).';
                                $destIcon = 'bi-wallet2 text-success';
                                $destBg = '#ecfdf5';
                                $destBorder = '#a7f3d0';
                            } else {
                                $destType = 'none';
                                $destTitle = 'No Refund Required (₹0.00)';
                                $destBadge = '✓ Free COD Cancellation';
                                $destDesc = 'This is an unpaid Cash on Delivery order. No cash was collected from the customer. Zero refund charges.';
                                $destIcon = 'bi-receipt-cutoff text-secondary';
                                $destBg = '#f8fafc';
                                $destBorder = '#e2e8f0';
                            }
                        } elseif (!$isPaid && $method === 'online') {
                            if ($walletUsed > 0) {
                                $destType = 'wallet';
                                $destTitle = 'ShopCalm Wallet (₹' . number_format($walletUsed, 2) . ')';
                                $destBadge = '⚡ Instant Wallet Credit';
                                $destDesc = 'Wallet amount of ₹' . number_format($walletUsed, 2) . ' will be refunded to customer\'s wallet. Unpaid online portion cancelled with ₹0 charges.';
                                $destIcon = 'bi-wallet2 text-success';
                                $destBg = '#ecfdf5';
                                $destBorder = '#a7f3d0';
                            } else {
                                $destType = 'none';
                                $destTitle = 'No Refund Required (₹0.00)';
                                $destBadge = '✓ Unpaid Online Transaction';
                                $destDesc = 'Online payment was not completed for this order. No refund required.';
                                $destIcon = 'bi-slash-circle text-secondary';
                                $destBg = '#f8fafc';
                                $destBorder = '#e2e8f0';
                            }
                        } elseif ($method === 'online' && $isPaid) {
                            if ($walletUsed > 0) {
                                $destType = 'dual';
                                $destTitle = 'Dual Split Automatic Refund (100%)';
                                $destBadge = '🔄 Dual Split Refund';
                                $destDesc = '• ₹' . number_format($walletUsed, 2) . ' credited back to customer\'s ShopCalm Wallet (Instant)' . "\n" . '• ₹' . number_format($totalAmt, 2) . ' direct refund to Original Payment Source (Razorpay Direct API)';
                                $destIcon = 'bi-arrow-left-right text-primary';
                                $destBg = '#eff6ff';
                                $destBorder = '#bfdbfe';
                            } else {
                                $destType = 'original_source';
                                $destTitle = 'Original Payment Source (Razorpay Direct API)';
                                $destBadge = '🏦 100% PG Direct Refund';
                                $destDesc = '100% full refund of ₹' . number_format($totalAmt, 2) . ' will be initiated directly back to customer\'s original payment source (UPI / Card / Netbanking via Razorpay API).';
                                $destIcon = 'bi-credit-card-2-front text-primary';
                                $destBg = '#eff6ff';
                                $destBorder = '#bfdbfe';
                            }
                        } elseif ($method === 'wallet' || ($walletUsed >= $totalAmt && $walletUsed > 0)) {
                            $destType = 'wallet';
                            $destTitle = 'ShopCalm Wallet (100% Instant Credit)';
                            $destBadge = '⚡ 100% Wallet Refund';
                            $destDesc = 'Full amount of ₹' . number_format($totalAmt > 0 ? $totalAmt : $walletUsed, 2) . ' will be credited back to customer\'s ShopCalm Wallet balance immediately.';
                            $destIcon = 'bi-wallet2 text-success';
                            $destBg = '#ecfdf5';
                            $destBorder = '#a7f3d0';
                        } else {
                            // Doorstep UPI, Cash collected at doorstep, or other paid doorstep method
                            $destType = 'wallet';
                            $destTitle = 'ShopCalm Wallet (100% Instant Refund)';
                            $destBadge = '⚡ 100% Doorstep Refund';
                            $destDesc = 'Payment collected at doorstep (₹' . number_format($totalAmt, 2) . ') will be credited directly to customer\'s ShopCalm Wallet balance instantly.';
                            $destIcon = 'bi-wallet2 text-success';
                            $destBg = '#ecfdf5';
                            $destBorder = '#a7f3d0';
                        }
                    @endphp

                    <div class="mb-3">
                        <label class="form-label fw-bold text-dark small mb-1">
                            Refund Destination Mode <span class="badge bg-secondary-subtle text-secondary border small fw-normal ms-1">Auto-Determined</span>
                        </label>
                        <input type="hidden" name="refund_method" value="{{ $destType }}">
                        <div class="p-3 rounded-3 border d-flex align-items-start gap-2.5" style="background: {{ $destBg }}; border-color: {{ $destBorder }} !important;">
                            <div class="flex-shrink-0 mt-0.5 fs-5">
                                <i class="bi {{ $destIcon }}"></i>
                            </div>
                            <div class="min-w-0 flex-grow-1">
                                <div class="d-flex align-items-center justify-content-between flex-wrap gap-1 mb-1">
                                    <span class="fw-bold text-dark" style="font-size: 0.9rem;">{{ $destTitle }}</span>
                                    <span class="badge bg-white text-dark border shadow-xs small" style="font-size: 0.72rem;">{{ $destBadge }}</span>
                                </div>
                                <div class="text-secondary small" style="font-size: 0.78rem; line-height: 1.4; white-space: pre-line;">{{ $destDesc }}</div>
                            </div>
                        </div>
                    </div>

                    <div class="mb-0">
                        <label class="form-label fw-bold text-dark small">Admin Internal Remarks / Customer Note (Optional)</label>
                        <textarea name="admin_notes" class="form-control" rows="2" placeholder="Internal remarks for store log or customer notice..."></textarea>
                    </div>
                </div>
                <div class="modal-footer bg-light py-2.5 px-4 border-top" style="flex-shrink: 0; position: sticky; bottom: 0; z-index: 10; background: #ffffff !important;">
                    <button type="button" class="btn btn-light border rounded-pill px-4" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-danger rounded-pill px-4 fw-bold shadow-sm">
                        <i class="bi bi-x-circle me-1"></i> Confirm Cancellation
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif

@endsection
