@extends('customer.account.layout')

@section('title', 'Order Details - #' . $order->order_number)

@section('account_content')

@php
    $s = strtolower(str_replace([' ', '-'], '_', $order->status));
    $map = [
        'delivered'        => ['bg'=>'#d1fae5','color'=>'#065f46','border'=>'#a7f3d0','stripe'=>'#10b981','icon'=>'bi-check-circle-fill','label'=>'Delivered'],
        'out_for_delivery' => ['bg'=>'#e0f2fe','color'=>'#0369a1','border'=>'#7dd3fc','stripe'=>'#0284c7','icon'=>'bi-bicycle','label'=>'Out for Delivery'],
        'shipped'          => ['bg'=>'#f3e8ff','color'=>'#6b21a8','border'=>'#d8b4fe','stripe'=>'#8b5cf6','icon'=>'bi-truck','label'=>'Shipped'],
        'packed'           => ['bg'=>'#dbeafe','color'=>'#1e40af','border'=>'#93c5fd','stripe'=>'#3b82f6','icon'=>'bi-box-seam-fill','label'=>'Packed'],
        'confirmed'        => ['bg'=>'#dbeafe','color'=>'#1e40af','border'=>'#93c5fd','stripe'=>'#3b82f6','icon'=>'bi-check2-square','label'=>'Confirmed'],
        'processing'       => ['bg'=>'#dbeafe','color'=>'#1e40af','border'=>'#93c5fd','stripe'=>'#3b82f6','icon'=>'bi-gear-fill','label'=>'Processing'],
        'cancelled'        => ['bg'=>'#fee2e2','color'=>'#991b1b','border'=>'#fca5a5','stripe'=>'#ef4444','icon'=>'bi-x-circle-fill','label'=>'Cancelled'],
        'failed'           => ['bg'=>'#fee2e2','color'=>'#991b1b','border'=>'#fca5a5','stripe'=>'#ef4444','icon'=>'bi-exclamation-octagon-fill','label'=>'Payment Failed'],
        'pending'          => ['bg'=>'#fef3c7','color'=>'#92400e','border'=>'#fde68a','stripe'=>'#f59e0b','icon'=>'bi-clock-fill','label'=>'Pending Payment'],
    ];
    $st = $map[$s] ?? $map['pending'];
    $statusStyle = "background:{$st['bg']}; color:{$st['color']}; border:1px solid {$st['border']};";

    $stageList = [
        ['key' => 'pending',          'label' => 'Placed'],
        ['key' => 'confirmed',        'label' => 'Confirmed'],
        ['key' => 'packed',           'label' => 'Packed'],
        ['key' => 'shipped',          'label' => 'Shipped'],
        ['key' => 'out_for_delivery', 'label' => 'Out for Delivery'],
        ['key' => 'delivered',        'label' => 'Delivered'],
    ];

    $stageRankMap = [
        'pending'          => 0,
        'placed'           => 0,
        'confirmed'        => 1,
        'processing'       => 1,
        'packed'           => 2,
        'shipped'          => 3,
        'out_for_delivery' => 4,
        'delivered'        => 5,
    ];

    $stepIdx = in_array($s, ['cancelled', 'failed']) ? false : ($stageRankMap[$s] ?? false);
@endphp

{{-- Header Banner Card --}}
<div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-4" style="background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%); color: #ffffff;">
    <div class="card-body p-3 p-sm-4">
        <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
            <div>
                <div class="d-flex align-items-center gap-2 mb-1.5 flex-wrap">
                    <h4 class="fw-bold mb-0 text-white" style="font-size: clamp(1.15rem, 3.2vw, 1.4rem); letter-spacing: -0.01em;">
                        Order #{{ $order->order_number }}
                    </h4>
                    <span class="badge rounded-pill px-3 py-1.5 fw-bold shadow-xs d-inline-flex align-items-center gap-1" style="{{ $statusStyle }}; font-size: 0.74rem;">
                        <i class="bi {{ $st['icon'] }}"></i> {{ $st['label'] ?? ucfirst($order->status) }}
                    </span>
                </div>
                <p class="text-white-50 small mb-0" style="font-size: 0.8rem;">
                    <i class="bi bi-calendar3 me-1 opacity-75"></i>Placed on {{ $order->created_at ? $order->created_at->format('d M, Y h:i A') : 'N/A' }}
                </p>
            </div>
            <div class="d-flex gap-2 align-items-center w-100 w-sm-auto justify-content-between justify-content-sm-end pt-3 pt-sm-0 border-top border-white border-opacity-10 border-top-sm-0">
                <a href="{{ route('account.orders.index') }}" class="btn btn-outline-light rounded-pill px-3 py-2 fw-semibold btn-sm d-inline-flex align-items-center justify-content-center gap-1.5 flex-grow-1 flex-sm-grow-0" style="font-size: 0.78rem;">
                    <i class="bi bi-arrow-left"></i> <span>Orders</span>
                </a>
                <a href="{{ route('account.orders.invoice', $order) }}" target="_blank" class="btn btn-light rounded-pill px-3 py-2 fw-bold btn-sm shadow-sm d-inline-flex align-items-center justify-content-center gap-1.5 text-dark flex-grow-1 flex-sm-grow-0" style="background: #ffffff; color: #0f172a; border: 1px solid rgba(255, 255, 255, 0.4); font-size: 0.78rem;">
                    <i class="bi bi-file-earmark-pdf-fill text-danger"></i> <span>Invoice</span>
                </a>
            </div>
        </div>
    </div>
</div>

@php
    $isPendingUnpaid = in_array($order->status, ['pending', 'failed']) || $order->payment_status === 'failed';
    $canCancel = ($isPendingUnpaid || ((\App\Models\Setting::get('allow_customer_cancellation', '1') == '1') && $order->status === 'confirmed')) && $order->status !== 'cancelled' && $order->status !== 'delivered';
    $canProceedToPay = $isPendingUnpaid && $order->payment_method === 'online' && $order->payment_status !== 'paid' && $order->status !== 'cancelled';
@endphp

{{-- ── Order Action & Payment Recovery Card ── --}}
@if($canProceedToPay || $canCancel)
<div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-4" style="background: #ffffff; border: 1.5px solid {{ $canProceedToPay ? '#fdba74' : '#e2e8f0' }} !important;">
    <div class="card-body p-3 p-sm-4">
        <div class="d-flex align-items-center justify-content-between gap-3 flex-wrap">
            <div class="d-flex align-items-center gap-3 min-w-0">
                <div class="rounded-3 d-flex align-items-center justify-content-center flex-shrink-0"
                     style="width: 48px; height: 48px; background: {{ $canProceedToPay ? '#ffedd5' : '#ede9fe' }}; color: {{ $canProceedToPay ? '#ea580c' : '#6366f1' }}; font-size: 1.35rem;">
                    <i class="bi {{ $canProceedToPay ? 'bi-exclamation-triangle-fill' : 'bi-shield-check' }}"></i>
                </div>
                <div class="min-w-0">
                    <h6 class="fw-bold text-dark mb-1 text-truncate" style="font-size: 0.96rem;">
                        {{ $canProceedToPay ? 'Payment Incomplete for this Order' : 'Order Actions' }}
                    </h6>
                    <p class="text-muted small mb-0" style="font-size: 0.82rem; line-height: 1.4;">
                        @if($canProceedToPay)
                            Online payment of <strong class="text-dark font-monospace">₹{{ number_format($order->total_amount, 2) }}</strong> was not completed. You can proceed to pay or cancel this order.
                        @else
                            This order is confirmed and scheduled for dispatch. You can cancel free before it is shipped.
                        @endif
                    </p>
                </div>
            </div>

            {{-- Action Buttons --}}
            <div class="d-flex align-items-center gap-2 flex-wrap w-100 w-sm-auto justify-content-end ms-auto pt-2 pt-sm-0">
                @if($canProceedToPay)
                    <a href="{{ route('checkout.payment_failed', $order) }}"
                       class="btn btn-warning text-white fw-bold rounded-pill px-4 py-2.5 shadow-xs d-inline-flex align-items-center justify-content-center gap-1.5 flex-grow-1 flex-sm-grow-0"
                       style="background: #ea580c; border: none; font-size: 0.85rem;">
                        <i class="bi bi-arrow-repeat me-0.5"></i> <span>Proceed to Pay</span>
                    </a>
                @endif

                @if($canCancel)
                    <button type="button" id="btn-order-cancel-pending" onclick="cancelPendingOrderFromDetails()"
                            class="btn btn-outline-danger rounded-pill px-4 py-2.5 fw-semibold d-inline-flex align-items-center justify-content-center gap-1.5 flex-grow-1 flex-sm-grow-0"
                            style="font-size: 0.85rem;">
                        <i class="bi bi-x-circle me-0.5"></i> <span>Cancel Order</span>
                    </button>
                @endif
            </div>
        </div>
    </div>
</div>
@endif

{{-- ── Cancellation & Refund Audit Card ── --}}
@if($s === 'cancelled')
@php
    $cancellation = $order->cancellation;
    $isAdminCancelled = false;

    if ($cancellation) {
        $isAdminCancelled = ($cancellation->cancelled_by_type === 'admin');
    } else {
        $cancelHistory = $order->statusHistories()->where('current_status', 'cancelled')->latest()->first();
        if ($cancelHistory) {
            $notesLower = strtolower($cancelHistory->notes ?? '');
            if (str_contains($notesLower, 'by store admin') || str_contains($notesLower, 'by store management')) {
                $isAdminCancelled = true;
            }
        }
    }
@endphp
<div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-4" style="background: #ffffff; border: 1px solid #fecaca !important;">
    <div class="card-header py-3 px-3 px-md-4 border-bottom border-danger-subtle d-flex align-items-center justify-content-between flex-wrap gap-2.5"
         style="background: linear-gradient(135deg, #fef2f2 0%, #fff1f1 100%);">
        <div class="d-flex align-items-center gap-3">
            <div class="rounded-circle bg-danger text-white d-flex align-items-center justify-content-center flex-shrink-0 shadow-xs" style="width: 42px; height: 42px;">
                <i class="bi bi-x-circle-fill fs-5"></i>
            </div>
            <div>
                <h6 class="fw-bold text-danger mb-0.5" style="font-size: 0.98rem; letter-spacing: -0.01em;">Order Cancellation Summary</h6>
                <div class="text-secondary small" style="font-size: 0.76rem;">
                    Cancelled on <span class="fw-semibold text-dark">{{ $cancellation ? $cancellation->created_at->format('d M, Y \a\t h:i A') : ($order->updated_at ? $order->updated_at->format('d M, Y \a\t h:i A') : 'N/A') }}</span>
                </div>
            </div>
        </div>
        <div>
            @if($isAdminCancelled)
                <span class="badge bg-danger text-white rounded-pill px-3 py-1.5 fw-bold shadow-xs" style="font-size: 0.74rem;">
                    <i class="bi bi-shield-x me-1"></i> Cancelled by Store Management
                </span>
            @else
                <span class="badge bg-secondary text-white rounded-pill px-3 py-1.5 fw-bold shadow-xs" style="font-size: 0.74rem;">
                    <i class="bi bi-person-x me-1"></i> Cancelled by You
                </span>
            @endif
        </div>
    </div>

    <div class="card-body p-3 p-md-4">
        {{-- Prominent Customer Notification Alert --}}
        @if($isAdminCancelled)
            <div class="alert alert-danger rounded-3 py-2 px-3 small mb-3 border-0 d-flex align-items-center gap-2" style="background: #fef2f2; color: #991b1b; border-left: 4px solid #ef4444 !important;">
                <i class="bi bi-info-circle-fill fs-5 flex-shrink-0"></i>
                <div>
                    <strong class="d-block" style="font-size: 0.85rem;">Notice from Store Management:</strong>
                    <span>This order was cancelled by store management. @if($order->payment_status === 'paid' || $order->wallet_amount_used > 0)A 100% full refund of <strong class="font-monospace">₹{{ number_format($cancellation?->refund_amount ?? $order->total_amount, 2) }}</strong> has been initiated back to your original payment method / bank account.@else No payment was collected for this order.@endif</span>
                </div>
            </div>
        @endif

        {{-- Top Reason & Refund Destination Grid --}}
        <div class="row g-3 mb-4">
            {{-- Reason Box --}}
            <div class="col-12 col-md-6">
                <div class="p-3 rounded-4 bg-light-subtle border h-100 d-flex flex-column justify-content-center" style="background: #f8fafc; border-color: #e2e8f0 !important;">
                    <div class="text-uppercase text-muted fw-bold mb-1.5" style="font-size: 0.67rem; letter-spacing: 0.06em;">Cancellation Reason</div>
                    <div class="fw-bold text-dark d-flex align-items-start gap-2" style="font-size: 0.88rem; line-height: 1.35;">
                        <i class="bi bi-chat-left-quote-fill text-danger opacity-75 fs-6 flex-shrink-0 mt-0.5"></i>
                        <span>{{ $cancellation?->cancellation_reason ?? ($isAdminCancelled ? 'Cancelled by store management' : 'Cancelled by customer') }}</span>
                    </div>
                    @if($cancellation && $cancellation->admin_notes)
                        <div class="mt-2 text-muted small p-2 rounded bg-white border" style="font-size: 0.74rem;">
                            <strong>Store Note:</strong> {{ $cancellation->admin_notes }}
                        </div>
                    @endif
                </div>
            </div>

            {{-- Refund Destination Box --}}
            <div class="col-12 col-md-6">
                <div class="p-3 rounded-4 bg-light-subtle border h-100 d-flex flex-column justify-content-center" style="background: #f8fafc; border-color: #e2e8f0 !important;">
                    <div class="text-uppercase text-muted fw-bold mb-2" style="font-size: 0.67rem; letter-spacing: 0.06em;">Refund Destination &amp; Status</div>
                    
                    @php
                        $walletUsedInOrder = (float) ($order->wallet_amount_used ?? 0);
                        $recordedRefundAmount = (float) ($cancellation?->refund_amount ?? 0);
                        $walletRefundVal = (float) ($cancellation?->wallet_refund_amount ?? 0);
                        $onlineRefundVal = (float) ($cancellation?->online_refund_amount ?? 0);

                        // Fallback for legacy records
                        if ($walletRefundVal == 0 && $onlineRefundVal == 0 && $recordedRefundAmount > 0) {
                            if ($cancellation?->refund_method === 'wallet') {
                                $walletRefundVal = $recordedRefundAmount;
                            } elseif (in_array($cancellation?->refund_method, ['original_source', 'online'])) {
                                $onlineRefundVal = $recordedRefundAmount;
                            }
                        }

                        $isCod = strtolower($order->payment_method ?? 'cod') === 'cod';
                        $hasNoRefund = ($walletRefundVal == 0 && $onlineRefundVal == 0 && $recordedRefundAmount == 0) || ($cancellation?->refund_method === 'none');
                        $isCodNoRefund = $isCod && $hasNoRefund;
                        $isOnlineNoRefund = !$isCod && $hasNoRefund;
                        $ticketNo = 'CNL-' . ($order->order_number ?? $order->id);
                    @endphp

                    @if($isCodNoRefund)
                        <div class="p-2.5 rounded-3 bg-white border">
                            <div class="d-flex align-items-center justify-content-between flex-wrap gap-1.5 mb-1">
                                <span class="fw-bold text-dark" style="font-size: 0.88rem;"><i class="bi bi-receipt-cutoff text-secondary me-1.5"></i> Cash on Delivery (COD)</span>
                                <span class="badge bg-success text-white rounded-pill px-2.5 py-1 fw-bold" style="font-size: 0.7rem;">✓ 100% Free Cancellation</span>
                            </div>
                            <div class="text-muted small mt-1" style="font-size: 0.73rem;">
                                No cash was collected for this order. Order delivery has been cancelled with zero charges.
                            </div>
                        </div>
                    @elseif($isOnlineNoRefund)
                        <div class="p-2.5 rounded-3 bg-white border">
                            <div class="d-flex align-items-center justify-content-between flex-wrap gap-1.5 mb-1">
                                <span class="fw-bold text-dark" style="font-size: 0.88rem;"><i class="bi bi-credit-card text-secondary me-1.5"></i> Online Payment (Unpaid / Cancelled)</span>
                                <span class="badge bg-success text-white rounded-pill px-2.5 py-1 fw-bold" style="font-size: 0.7rem;">✓ 100% Free Cancellation</span>
                            </div>
                            <div class="text-muted small mt-1" style="font-size: 0.73rem;">
                                No payment was collected for this order. The pending online transaction has been cancelled with zero charges.
                            </div>
                        </div>
                    @else
                        @if($walletRefundVal > 0)
                            <div class="p-2.5 rounded-3 bg-white border mb-2">
                                <div class="d-flex align-items-center justify-content-between flex-wrap gap-1.5">
                                    <span class="fw-bold text-dark" style="font-size: 0.85rem;"><i class="bi bi-wallet2 text-success me-1.5"></i> ShopCalm Wallet</span>
                                    <span class="badge bg-success text-white rounded-pill px-2 py-0.5" style="font-size: 0.68rem;">⚡ Instantly Credited</span>
                                </div>
                                <div class="text-muted small mt-1" style="font-size: 0.73rem;">
                                    <strong>₹{{ number_format($walletRefundVal, 2) }}</strong> credited back to your ShopCalm Wallet balance.
                                </div>
                            </div>
                        @endif

                        @if($onlineRefundVal > 0 || ($cancellation && in_array($cancellation->refund_method, ['original_source', 'online', 'dual']) && $walletRefundVal == 0))
                            @php
                                $displayOnlineAmt = $onlineRefundVal > 0 ? $onlineRefundVal : $recordedRefundAmount;
                                $refundProof = $cancellation->razorpay_refund_id ?: $cancellation->payment_reference;
                                $isProcessed = ($cancellation->online_refund_status === 'processed' || $cancellation->refund_status === 'processed');
                            @endphp
                            <div class="p-2.5 rounded-3 bg-white border">
                                <div class="d-flex align-items-center justify-content-between flex-wrap gap-1.5 mb-1.5">
                                    <span class="fw-bold text-dark" style="font-size: 0.85rem;"><i class="bi bi-credit-card text-primary me-1.5"></i> Original Payment Source (Razorpay Direct)</span>
                                    @if($isProcessed)
                                        <span class="badge bg-success text-white rounded-pill px-2 py-0.5" style="font-size: 0.68rem;">✓ Refund Processed</span>
                                    @else
                                        <span class="badge bg-warning text-dark rounded-pill px-2 py-0.5" style="font-size: 0.68rem;">⏳ Direct Refund Initiated</span>
                                    @endif
                                </div>

                                <!-- Refund Ticket Badge (Always Available for Support Complaint) -->
                                <div class="p-2 rounded-2 bg-light text-dark small d-flex align-items-center justify-content-between mb-1.5" style="font-size: 0.74rem;">
                                    <div>
                                        <i class="bi bi-ticket-perforated-fill text-primary me-1"></i><strong>Support Ticket No:</strong>
                                        <code class="text-dark bg-white px-1.5 py-0.5 rounded border font-monospace ms-1">{{ $ticketNo }}</code>
                                    </div>
                                    <button type="button" class="btn btn-link p-0 text-muted" onclick="navigator.clipboard.writeText('{{ $ticketNo }}')" title="Copy Ticket No">
                                        <i class="bi bi-copy"></i>
                                    </button>
                                </div>

                                @if($isProcessed && $refundProof)
                                    <div class="p-2 rounded-2 bg-success bg-opacity-10 border border-success border-opacity-25 text-dark small d-flex align-items-center justify-content-between mb-1" style="font-size: 0.74rem;">
                                        <div>
                                            <i class="bi bi-shield-check text-success me-1"></i><strong>Bank UTR Ref:</strong>
                                            <code class="text-dark bg-white px-1.5 py-0.5 rounded border font-monospace ms-1">{{ $refundProof }}</code>
                                        </div>
                                        <button type="button" class="btn btn-link p-0 text-muted" onclick="navigator.clipboard.writeText('{{ $refundProof }}')" title="Copy UTR Reference">
                                            <i class="bi bi-copy"></i>
                                        </button>
                                    </div>
                                    <div class="text-muted small" style="font-size: 0.72rem;">₹{{ number_format($displayOnlineAmt, 2) }} credited to original bank account.</div>
                                @else
                                    <div class="text-muted small" style="font-size: 0.72rem; line-height: 1.35;">
                                        Refund of <strong>₹{{ number_format($displayOnlineAmt, 2) }}</strong> initiated via Razorpay PG. Expected credit: 3-5 business days.
                                    </div>
                                @endif
                            </div>
                        @endif

                        @if($cancellation && $cancellation->refund_method === 'bank_upi' && $walletRefundVal == 0 && $onlineRefundVal == 0)
                            <div class="p-2.5 rounded-3 bg-white border">
                                <div class="d-flex align-items-center justify-content-between flex-wrap gap-1.5 mb-1">
                                    <span class="fw-bold text-dark" style="font-size: 0.85rem;"><i class="bi bi-bank text-info me-1.5"></i> Bank UPI: <code class="text-dark bg-light px-1.5 py-0.5 rounded border font-monospace">{{ $cancellation->refund_upi_id }}</code></span>
                                    @if($cancellation->refund_status === 'processed')
                                        <span class="badge bg-success text-white rounded-pill px-2 py-0.5" style="font-size: 0.68rem;">✓ Processed</span>
                                    @else
                                        <span class="badge bg-warning text-dark rounded-pill px-2 py-0.5" style="font-size: 0.68rem;">⏳ Pending Payout</span>
                                    @endif
                                </div>
                                @if($cancellation->refund_status === 'processed')
                                    <div class="text-success small fw-semibold" style="font-size: 0.73rem;"><i class="bi bi-check-circle-fill me-1"></i> Bank UTR Ref: <span class="font-monospace text-dark">{{ $cancellation->payment_reference }}</span></div>
                                @else
                                    <div class="text-muted small" style="font-size: 0.73rem;">Refund will be processed to your UPI handle within 2-3 business days.</div>
                                @endif
                            </div>
                        @endif
                    @endif
                </div>
            </div>
        </div>

        {{-- Financial Refund Breakdown Card --}}
        <div class="p-3 p-md-4 rounded-4 bg-white border shadow-2xs" style="border-color: #e2e8f0 !important;">
            <div class="text-uppercase text-muted fw-bold mb-2 pb-1 border-bottom" style="font-size: 0.67rem; letter-spacing: 0.06em;">
                <i class="bi bi-calculator me-1"></i> Financial Refund Calculation Breakdown
            </div>
            
            @php
                $netRefundTotal = $walletRefundVal + $onlineRefundVal;
                if ($netRefundTotal == 0 && !$hasNoRefund) {
                    $netRefundTotal = $recordedRefundAmount;
                }
            @endphp

            <div class="d-flex align-items-center justify-content-between py-1 text-dark" style="font-size: 0.84rem;">
                <span>Original Order Total:</span>
                <strong class="font-monospace text-dark">₹{{ number_format($order->total_amount > 0 ? $order->total_amount : ($walletUsedInOrder > 0 ? $walletUsedInOrder : 0), 2) }}</strong>
            </div>

            @if($cancellation?->cancellation_fee > 0)
                <div class="d-flex align-items-center justify-content-between py-1 text-danger" style="font-size: 0.84rem;">
                    <span>Retained Non-Refundable GST Tax Fee:</span>
                    <strong class="font-monospace">-₹{{ number_format($cancellation->cancellation_fee, 2) }}</strong>
                </div>
            @endif

            <hr class="my-2 border-secondary-subtle">

            <div class="d-flex align-items-center justify-content-between pt-1 fw-bold {{ $hasNoRefund ? 'text-secondary' : 'text-success' }} flex-wrap gap-1">
                <span class="fs-6" style="font-size: 0.92rem !important;">Net Refund Amount:</span>
                <span class="font-monospace fs-5 {{ $hasNoRefund ? 'text-secondary' : 'text-success' }}">₹{{ number_format($netRefundTotal, 2) }}</span>
            </div>
        </div>
    </div>
</div>
@endif

{{-- ── Progress Tracker Card (Live Tracking) ── --}}
@if($s !== 'cancelled' && $stepIdx !== false)
<div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-4 p-3 p-sm-4" style="background: #ffffff; border: 1.5px solid #e2e8f0 !important;">
    <div class="d-flex align-items-center justify-content-between mb-3 pb-2 border-bottom">
        <span class="fw-bold text-dark small text-uppercase d-flex align-items-center gap-1.5" style="letter-spacing: 0.05em; font-size: 0.76rem;">
            <i class="bi bi-truck text-primary fs-6"></i> Live Delivery Progress
        </span>
        <span class="badge rounded-pill px-3 py-1.5 fw-bold" style="{{ $statusStyle }}; font-size: 0.72rem;">
            {{ $st['label'] ?? ucfirst($order->status) }}
        </span>
    </div>

    {{-- 💻 Desktop Stepper (>= 768px): Full-Width Horizontal Stepper --}}
    <div class="d-none d-md-block py-2">
        <div class="position-relative d-flex align-items-center justify-content-between my-2" style="padding: 0 16px;">
            {{-- Background track --}}
            <div class="position-absolute" style="top:16px; left:36px; right:36px; height:4px; background:#e2e8f0; border-radius:99px; z-index:0;"></div>
            {{-- Filled track --}}
            @php $fillPct = $stepIdx > 0 ? ($stepIdx / (count($stageList) - 1)) * 100 : 0; @endphp
            <div class="position-absolute" style="top:16px; left:36px; width:calc({{ round($fillPct) }}% - 0px); max-width:calc(100% - 72px); height:4px; background:{{ $st['stripe'] }}; border-radius:99px; z-index:1; transition:width 0.5s ease;"></div>

            @foreach($stageList as $si => $stage)
            @php $done = $si <= $stepIdx; $current = $si === $stepIdx; @endphp
            <div class="d-flex flex-column align-items-center position-relative flex-fill px-2" style="z-index:2;">
                <div class="rounded-circle d-flex align-items-center justify-content-center"
                     style="width:34px; height:34px;
                            background:{{ $done ? $st['stripe'] : '#ffffff' }};
                            border:2.5px solid {{ $done ? $st['stripe'] : '#cbd5e1' }};
                            box-shadow:{{ $current ? '0 0 0 4px '.($st['bg']) : 'none' }};
                            transition:all 0.3s;">
                    @if($done)
                        <i class="bi bi-check-lg text-white small fw-bold"></i>
                    @else
                        <div style="width:7px; height:7px; border-radius:50%; background:#cbd5e1;"></div>
                    @endif
                </div>
                <span class="mt-2 text-center text-nowrap"
                      style="font-size:0.78rem; line-height:1.2;
                             color:{{ $done ? $st['color'] : '#94a3b8' }};
                             font-weight:{{ $current ? '700' : ($done ? '600' : '400') }};">
                    {{ $stage['label'] }}
                </span>
            </div>
            @endforeach
        </div>
    </div>

    {{-- 📱 Mobile Stepper (< 768px): Clean Vertical App-Style Timeline (Zero Clipping, 100% Readable) --}}
    <div class="d-block d-md-none py-1">
        <div class="position-relative ps-1">
            @foreach($stageList as $si => $stage)
            @php
                $done = $si <= $stepIdx;
                $current = $si === $stepIdx;
                $isLast = $si === count($stageList) - 1;
            @endphp
            <div class="d-flex align-items-start position-relative {{ !$isLast ? 'pb-3' : '' }}">
                {{-- Vertical connecting line --}}
                @if(!$isLast)
                    <div class="position-absolute"
                         style="top: 24px; bottom: 0; left: 11px; width: 2px; background: {{ $si < $stepIdx ? $st['stripe'] : '#e2e8f0' }}; z-index: 0;"></div>
                @endif

                {{-- Status Circle Icon --}}
                <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0 position-relative"
                     style="width: 24px; height: 24px; z-index: 1; background: {{ $done ? $st['stripe'] : '#ffffff' }}; border: 2px solid {{ $done ? $st['stripe'] : '#cbd5e1' }}; box-shadow: {{ $current ? '0 0 0 3px ' . $st['bg'] : 'none' }};">
                    @if($done)
                        <i class="bi bi-check-lg text-white" style="font-size: 0.72rem; font-weight: 700;"></i>
                    @else
                        <div style="width: 5px; height: 5px; border-radius: 50%; background: #94a3b8;"></div>
                    @endif
                </div>

                {{-- Milestone Text & Status Info --}}
                <div class="ms-3 min-w-0 flex-grow-1">
                    <div class="d-flex align-items-center justify-content-between flex-wrap gap-1">
                        <span class="fw-bold" style="font-size: 0.84rem; color: {{ $done ? '#0f172a' : '#94a3b8' }};">
                            {{ $stage['label'] }}
                        </span>
                        @if($current)
                            <span class="badge rounded-pill px-2 py-0.5 fw-bold" style="{{ $statusStyle }}; font-size: 0.65rem;">
                                Current Status
                            </span>
                        @elseif($done)
                            <span class="text-success small fw-semibold" style="font-size: 0.7rem;">
                                <i class="bi bi-check2"></i> Completed
                            </span>
                        @endif
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</div>
@endif

{{-- ── Context-Aware Logistics & Live Handover Card ── --}}
@php
    $isOutForDelivery = ($s === 'out_for_delivery') || strtolower(str_replace(['_', '-'], ' ', (string) $order->status)) === 'out for delivery';
    $isDelivered = ($s === 'delivered') || strtolower((string) $order->status) === 'delivered';
@endphp
@if($order->isLocalBengaluruDelivery() && $s !== 'cancelled')
    @if($isDelivered)
        {{-- Delivered State Card --}}
        <div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-4 p-3 p-sm-4" style="background: #ffffff; border: 1.5px solid #a7f3d0 !important; max-width: 100%;">
            <div class="d-flex align-items-start align-items-sm-center justify-content-between flex-wrap gap-2.5" style="min-width: 0; max-width: 100%;">
                <div class="d-flex align-items-start align-items-sm-center gap-2.5" style="min-width: 0; flex: 1 1 auto;">
                    <div style="width: 42px; height: 42px; min-width: 42px; border-radius: 12px; background: #ecfdf5; color: #059669; border: 1px solid #a7f3d0; display: flex; align-items: center; justify-content: center; font-size: 1.25rem; flex-shrink: 0;">
                        <i class="bi bi-bag-check-fill"></i>
                    </div>
                    <div style="min-width: 0; flex: 1 1 auto; overflow: hidden; word-break: break-word;">
                        <div class="d-flex align-items-center gap-1.5 mb-1 flex-wrap">
                            <span class="badge rounded-pill px-2.5 py-1 fw-bold text-uppercase" style="background: #059669; color: #ffffff; font-size: 0.65rem; letter-spacing: 0.03em;">
                                <i class="bi bi-check-circle-fill me-1"></i> DELIVERED
                            </span>
                            <strong class="text-dark" style="font-size: 0.92rem; line-height: 1.35;">Package Handed Over Successfully</strong>
                        </div>
                        <div class="text-secondary small mt-0.5" style="font-size: 0.76rem; line-height: 1.35; word-break: break-word;">
                            Delivered on <span class="fw-semibold text-dark">{{ $order->delivered_at?->format('d M, Y \a\t h:i A') ?? $order->updated_at->format('d M, Y \a\t h:i A') }}</span>
                            @if($order->rider_name)
                                &bull; Handed over by <span class="fw-semibold text-dark">{{ $order->rider_name }}</span>
                            @endif
                        </div>
                        @php
                            $podPhoto = null;
                            $dropType = null;
                            if ($order->fulfillment && preg_match('/\[POD_PHOTO:(.*?)\]/', $order->fulfillment->notes ?? '', $mPhoto)) {
                                $podPhoto = $mPhoto[1];
                            }
                            if ($order->fulfillment && preg_match('/\[DROP_TYPE:(.*?)\]/', $order->fulfillment->notes ?? '', $mDrop)) {
                                $dropType = $mDrop[1];
                            }
                            $dropLabels = [
                                'security_guard' => 'Building Security Guard / Reception',
                                'doorstep'       => 'Doorstep / Shoe Rack',
                                'neighbor'       => 'Neighbor / Flatmate',
                            ];
                            $dropLabel = $dropLabels[$dropType] ?? 'Contactless Safe Drop';
                        @endphp
                        @if($podPhoto)
                            <div class="mt-2 pt-2 border-top border-success-subtle d-flex align-items-center justify-content-between flex-wrap gap-2">
                                <div class="d-flex align-items-center gap-1.5">
                                    <span class="badge bg-light text-dark border border-secondary-subtle px-2 py-1 small">
                                        <i class="bi bi-camera-fill text-primary me-1"></i> Left at: {{ $dropLabel }}
                                    </span>
                                </div>
                                <button type="button" class="btn btn-xs btn-outline-primary rounded-pill px-2.5 py-1 fw-bold d-inline-flex align-items-center gap-1 shadow-xs" data-bs-toggle="modal" data-bs-target="#modalPodPhoto" style="font-size: 0.74rem;">
                                    <i class="bi bi-image"></i> View Photo Proof
                                </button>
                            </div>
                        @endif
                    </div>
                </div>

                <div class="ms-auto ms-sm-2 flex-shrink-0 align-self-start align-self-sm-center d-flex align-items-center gap-1.5 flex-wrap">
                    <button type="button" class="btn btn-sm {{ $order->feedback ? 'btn-outline-success' : 'btn-warning text-dark' }} rounded-pill px-3 py-1.5 fw-bold shadow-xs d-inline-flex align-items-center gap-1" 
                            data-bs-toggle="modal" data-bs-target="#modalOrderFeedback" style="font-size: 0.78rem; white-space: nowrap;">
                        <i class="bi {{ $order->feedback ? 'bi-star-fill text-warning' : 'bi-star-fill' }}"></i> 
                        <span>{{ $order->feedback ? 'Feedback (' . $order->feedback->delivery_rating . '★)' : 'Rate Delivery' }}</span>
                    </button>
                    <form action="{{ route('account.orders.reorder', $order) }}" method="POST" class="d-inline m-0 p-0">
                        @csrf
                        <button type="submit" class="btn btn-sm btn-primary rounded-pill px-3 py-1.5 fw-bold shadow-xs d-inline-flex align-items-center gap-1" style="font-size: 0.78rem; white-space: nowrap;">
                            <i class="bi bi-arrow-repeat"></i> <span>Buy Again</span>
                        </button>
                    </form>
                </div>
            </div>
        </div>
    @else
        {{-- Active Delivery Card with Partner Assignment & Handover OTP --}}
        <div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-4 p-3 p-sm-4" style="background: #ffffff; border: 1.5px solid {{ $order->rider_name ? '#bbf7d0' : '#e2e8f0' }} !important; max-width: 100%;">
            {{-- Top Row: Rider & Status info --}}
            <div class="d-flex align-items-start align-items-sm-center justify-content-between gap-2 pb-3 border-bottom" style="min-width: 0; max-width: 100%;">
                <div class="d-flex align-items-start align-items-sm-center gap-2.5" style="min-width: 0; flex: 1 1 auto;">
                    @if($order->rider_name)
                        {{-- Assigned Rider Icon --}}
                        <div style="width: 42px; height: 42px; min-width: 42px; border-radius: 12px; background: {{ $isOutForDelivery ? '#e0f2fe' : '#ecfdf5' }}; color: {{ $isOutForDelivery ? '#0284c7' : '#059669' }}; border: 1px solid {{ $isOutForDelivery ? '#7dd3fc' : '#a7f3d0' }}; display: flex; align-items: center; justify-content: center; font-size: 1.25rem; flex-shrink: 0;">
                            <i class="bi bi-bicycle"></i>
                        </div>
                        <div style="min-width: 0; flex: 1 1 auto; overflow: hidden; word-break: break-word;">
                            <div class="d-flex align-items-center gap-1.5 mb-1 flex-wrap">
                                <span class="badge rounded-pill px-2.5 py-1 fw-bold text-uppercase" style="background: {{ $isOutForDelivery ? '#0284c7' : '#059669' }}; color: #ffffff; font-size: 0.65rem; letter-spacing: 0.03em;">
                                    <i class="bi {{ $isOutForDelivery ? 'bi-lightning-charge-fill' : 'bi-check2-circle' }} me-1"></i>
                                    {{ $isOutForDelivery ? 'OUT FOR DELIVERY' : 'RIDER ASSIGNED' }}
                                </span>
                            </div>
                            <div class="fw-bold text-dark text-break" style="font-size: 0.92rem; line-height: 1.35;" title="{{ $order->rider_name }}">
                                {{ $order->rider_name }}
                            </div>
                            <div class="text-secondary small text-break mt-0.5" style="font-size: 0.75rem; line-height: 1.35;">
                                @php
                                    $deliveryStatusText = 'Arriving today at your doorstep';
                                    if ($order->fulfillment?->hasActiveDeliveryIssue()) {
                                        $deliveryStatusText = 'Delivery Attempted — Delivery partner will re-attempt soon';
                                    } elseif (str_contains(strtolower($order->delivery_slot ?? ''), 'tomorrow') || str_contains(strtolower($order->fulfillment?->delivery_slot ?? ''), 'tomorrow')) {
                                        $deliveryStatusText = 'Rescheduled — Arriving tomorrow at your doorstep';
                                    }
                                @endphp
                                <i class="bi bi-clock-history me-1 text-muted"></i>{{ $isOutForDelivery ? $deliveryStatusText : ($order->delivery_slot ?: 'Preparing for dispatch') }}
                            </div>
                        </div>
                    @else
                        {{-- Preparing for Dispatch State --}}
                        <div style="width: 42px; height: 42px; min-width: 42px; border-radius: 12px; background: #f8fafc; color: #475569; border: 1px solid #e2e8f0; display: flex; align-items: center; justify-content: center; font-size: 1.25rem; flex-shrink: 0;">
                            <i class="bi bi-box-seam"></i>
                        </div>
                        <div style="min-width: 0; flex: 1 1 auto; overflow: hidden; word-break: break-word;">
                            <div class="d-flex align-items-center gap-1.5 mb-1 flex-wrap">
                                <span class="badge rounded-pill px-2.5 py-1 fw-bold bg-primary bg-opacity-10 text-primary border border-primary-subtle" style="font-size: 0.65rem; letter-spacing: 0.03em;">
                                    <i class="bi bi-hourglass-split me-1"></i> ORDER IN PROGRESS
                                </span>
                            </div>
                            <div class="fw-bold text-dark text-break" style="font-size: 0.92rem; line-height: 1.35;">
                                Preparing for Dispatch
                            </div>
                            <div class="text-secondary small text-break mt-0.5" style="font-size: 0.75rem; line-height: 1.35;">
                                Our warehouse team is packing your items for fast doorstep delivery
                            </div>
                        </div>
                    @endif
                </div>

                {{-- Right Action: Call Rider Button --}}
                <div class="flex-shrink-0 ms-2 align-self-start align-self-sm-center">
                    @if($order->rider_name && $order->rider_phone)
                        <a href="tel:{{ $order->rider_phone }}" class="btn btn-sm rounded-pill px-2.5 px-sm-3 py-1.5 fw-bold text-white shadow-xs d-inline-flex align-items-center gap-1" style="background: #059669; border: none; font-size: 0.78rem; white-space: nowrap;">
                            <i class="bi bi-telephone-fill"></i> <span class="d-none d-sm-inline">Call Rider</span><span class="d-inline d-sm-none">Call</span>
                        </a>
                    @elseif(!$order->rider_name)
                        <span class="badge rounded-pill px-2.5 py-1 bg-light text-muted border small d-inline-flex align-items-center gap-1" style="font-size: 0.7rem; white-space: nowrap;">
                            <i class="bi bi-box-seam"></i> <span class="d-none d-sm-inline">In Packing</span><span class="d-inline d-sm-none">Packing</span>
                        </span>
                    @endif
                </div>
            </div>

            {{-- Bottom Row: High-Contrast Payment Status Pill --}}
            <div class="pt-3">
                <div class="p-3 rounded-3" style="background: #f8fafc; border: 1.5px solid #e2e8f0; max-width: 100%;">
                    <div class="d-flex align-items-center justify-content-between flex-wrap gap-2.5">
                        @if($order->payment_status === 'paid')
                            <div class="d-inline-flex align-items-center gap-2 px-3 py-1.5 rounded-pill shadow-xs" style="background: #ecfdf5; border: 1.5px solid #86efac;">
                                <span class="fw-bold font-monospace d-inline-flex align-items-center" style="font-size: 0.84rem; color: #15803d !important;">
                                    <i class="bi bi-check-circle-fill me-2 fs-6 text-success" style="font-size: 1rem !important;"></i>
                                    <span>
                                        @if($order->payment_method === 'doorstep_upi')
                                            Paid via Doorstep UPI &bull; Collect ₹0
                                        @elseif($order->payment_method === 'cod')
                                            Cash Paid at Doorstep &bull; Collect ₹0
                                        @elseif($order->payment_method === 'wallet')
                                            Paid via ShopCalm Wallet &bull; Collect ₹0
                                        @else
                                            Paid Online &bull; Collect ₹0
                                        @endif
                                    </span>
                                </span>
                            </div>
                            <span class="text-secondary fw-semibold small ps-1" style="font-size: 0.74rem;">
                                <i class="bi bi-shield-check text-success me-1"></i> Zero amount to pay at doorstep
                            </span>
                        @else
                            <div class="d-inline-flex align-items-center gap-2.5 px-3 py-1.5 rounded-pill shadow-xs" style="background: #fffbeb; border: 1.5px solid #fde68a;">
                                <span class="fw-bold d-inline-flex align-items-center text-uppercase" style="font-size: 0.76rem; color: #b45309 !important; letter-spacing: 0.3px;">
                                    <i class="bi bi-cash-stack me-2 fs-5" style="color: #b45309 !important; line-height: 1;"></i>
                                    <span>Payable at Doorstep:</span>
                                </span>
                                <span class="fw-extrabold text-dark font-monospace ps-1" style="font-size: 0.95rem;">
                                    ₹{{ number_format($order->total_amount, 2) }}
                                </span>
                            </div>
                            <span class="text-muted small ps-1" style="font-size: 0.74rem;">
                                <i class="bi bi-info-circle me-1 text-secondary"></i> Cash or Instant UPI QR to Rider
                            </span>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    @endif
@elseif(($order->courier_partner || $order->tracking_number || in_array($order->status, ['shipped', 'out for delivery', 'delivered'])) && $s !== 'cancelled')
    {{-- Pan-India National Courier Logistics Card --}}
    <div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-4 p-3 p-sm-4" style="background: #ffffff; border: 1.5px solid #e2e8f0 !important; max-width: 100%;">
        <div class="d-flex align-items-start align-items-sm-center justify-content-between flex-wrap gap-2.5" style="min-width: 0; max-width: 100%;">
            <div class="d-flex align-items-start align-items-sm-center gap-2.5" style="min-width: 0; flex: 1 1 auto;">
                <div style="width: 42px; height: 42px; min-width: 42px; border-radius: 12px; background: {{ $order->status === 'delivered' ? '#ecfdf5' : '#f0f9ff' }}; color: {{ $order->status === 'delivered' ? '#059669' : '#0284c7' }}; border: 1px solid {{ $order->status === 'delivered' ? '#a7f3d0' : '#bae6fd' }}; display: flex; align-items: center; justify-content: center; font-size: 1.25rem; flex-shrink: 0;">
                    <i class="bi {{ $order->status === 'delivered' ? 'bi-bag-check-fill' : 'bi-box-seam-fill' }}"></i>
                </div>
                <div style="min-width: 0; flex: 1 1 auto; overflow: hidden; word-break: break-word;">
                    <div class="d-flex align-items-center gap-1.5 mb-1 flex-wrap">
                        <span class="badge {{ $order->status === 'delivered' ? 'bg-success' : 'bg-primary' }} text-white rounded-pill px-2.5 py-1 fw-bold text-uppercase" style="font-size: 0.65rem; letter-spacing: 0.03em;">
                            {{ $order->status === 'delivered' ? 'DELIVERED' : 'COURIER DISPATCH' }}
                        </span>
                        <strong class="text-dark" style="font-size: 0.92rem; line-height: 1.35;">
                            {{ $order->status === 'delivered' ? 'Delivered via ' . ($order->courier_partner ?: 'National Logistics') : ($order->courier_partner ?: 'Express Logistics Partner') }}
                        </strong>
                    </div>
                    <div class="text-secondary small mt-0.5" style="font-size: 0.76rem; line-height: 1.35; word-break: break-word;">
                        @if($order->status === 'delivered')
                            Delivered on <span class="fw-semibold text-dark">{{ $order->delivered_at?->format('d M, Y \a\t h:i A') ?? $order->updated_at->format('d M, Y \a\t h:i A') }}</span>
                        @elseif($order->tracking_number)
                            Tracking ID: <span class="font-monospace fw-bold text-dark px-1.5 py-0.5 rounded bg-light border text-break">{{ $order->tracking_number }}</span>
                        @else
                            Dispatched & In-Transit with Logistics Partner
                        @endif
                    </div>
                </div>
            </div>

            <div class="d-flex align-items-center gap-2 flex-wrap ms-auto ms-sm-2 flex-shrink-0 align-self-start align-self-sm-center">
                @if($order->status === 'delivered')
                    <button type="button" class="btn btn-sm {{ $order->feedback ? 'btn-outline-success' : 'btn-warning text-dark' }} rounded-pill px-3 py-1.5 fw-bold shadow-xs d-inline-flex align-items-center gap-1" 
                            data-bs-toggle="modal" data-bs-target="#modalOrderFeedback" style="font-size: 0.78rem; white-space: nowrap;">
                        <i class="bi {{ $order->feedback ? 'bi-star-fill text-warning' : 'bi-star-fill' }}"></i> 
                        <span>{{ $order->feedback ? 'Feedback (' . $order->feedback->delivery_rating . '★)' : 'Rate Delivery' }}</span>
                    </button>
                    <form action="{{ route('account.orders.reorder', $order) }}" method="POST" class="d-inline m-0 p-0">
                        @csrf
                        <button type="submit" class="btn btn-sm btn-primary rounded-pill px-3 py-1.5 fw-bold shadow-xs d-inline-flex align-items-center gap-1" style="font-size: 0.78rem; white-space: nowrap;">
                            <i class="bi bi-arrow-repeat"></i> <span>Buy Again</span>
                        </button>
                    </form>
                @elseif($order->tracking_url)
                    <a href="{{ $order->tracking_url }}" target="_blank" class="btn btn-sm btn-primary rounded-pill px-3 py-1.5 fw-bold shadow-sm" style="background: #0284c7; border: none; font-size: 0.78rem; white-space: nowrap;">
                        <i class="bi bi-geo-alt-fill me-1"></i> Track on Courier <i class="bi bi-box-arrow-up-right ms-0.5 small"></i>
                    </a>
                @elseif($order->tracking_number)
                    <a href="https://www.google.com/search?q={{ urlencode(($order->courier_partner ?? 'courier') . ' tracking ' . $order->tracking_number) }}" target="_blank" class="btn btn-sm btn-primary rounded-pill px-3 py-1.5 fw-bold shadow-sm" style="background: #0284c7; border: none; font-size: 0.78rem; white-space: nowrap;">
                        <i class="bi bi-geo-alt-fill me-1"></i> Track Shipment <i class="bi bi-box-arrow-up-right ms-0.5 small"></i>
                    </a>
                @endif
            </div>
        </div>
    </div>
@endif

{{-- ── 2-Way Synchronized Delivery Decisions Card (Customer Exception Action) ── --}}
@if($order->fulfillment?->hasActiveDeliveryIssue() && !$isDelivered && $s !== 'cancelled')
    <div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-4 p-3 p-sm-4" style="background: #fffbeb; border: 1.5px solid #fde68a !important; max-width: 100%;">
        <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-2 pb-2 border-bottom border-warning-subtle">
            <span class="badge rounded-pill bg-warning text-dark fw-bold px-2.5 py-1 font-monospace" style="font-size: 0.72rem; letter-spacing: 0.02em;">
                <i class="bi bi-exclamation-triangle-fill me-1"></i> DELIVERY ACTION REQUIRED
            </span>
            <span class="small text-secondary fw-semibold" style="font-size: 0.78rem;">
                Reported by Partner: <span class="text-dark fw-bold">{{ $order->fulfillment->delivery_issue }}</span>
            </span>
        </div>
        <h6 class="fw-extrabold text-dark mb-1" style="font-size: 0.96rem;">How would you like us to proceed with your delivery?</h6>
        <p class="text-secondary small mb-3" style="font-size: 0.8rem;">
            Select your preferred instruction below. Your choice will automatically reschedule your package and synchronize instantly with our dispatch hub and delivery partner.
        </p>

        <form action="{{ route('account.orders.delivery-decision', $order) }}" method="POST" id="formDeliveryDecision">
            @csrf
            <div class="row g-2 mb-3">
                <div class="col-12 col-md-4">
                    <label class="card h-100 p-2.5 rounded-3 border bg-white cursor-pointer decision-opt-card" style="border-color: #cbd5e1; cursor: pointer;">
                        <div class="form-check p-0 d-flex align-items-start gap-2 m-0">
                            <input class="form-check-input ms-0 mt-1 flex-shrink-0" type="radio" name="decision" value="reschedule_tomorrow" id="dec_tomorrow" checked onchange="handleDecisionChange('reschedule_tomorrow')">
                            <div class="w-100">
                                <div class="fw-bold text-dark small"><i class="bi bi-calendar-event text-primary me-1"></i> Deliver Tomorrow</div>
                                <div class="text-muted" style="font-size: 0.72rem;">Choose morning or evening window</div>
                            </div>
                        </div>
                    </label>
                </div>
                <div class="col-12 col-md-4">
                    <label class="card h-100 p-2.5 rounded-3 border bg-white cursor-pointer decision-opt-card" style="border-color: #cbd5e1; cursor: pointer;">
                        <div class="form-check p-0 d-flex align-items-start gap-2 m-0">
                            <input class="form-check-input ms-0 mt-1 flex-shrink-0" type="radio" name="decision" value="leave_security_neighbor" id="dec_security" onchange="handleDecisionChange('leave_security_neighbor')">
                            <div class="w-100">
                                <div class="fw-bold text-dark small"><i class="bi bi-building text-success me-1"></i> Security / Neighbor</div>
                                <div class="text-muted" style="font-size: 0.72rem;">Safe contactless drop with guard</div>
                            </div>
                        </div>
                    </label>
                </div>
                <div class="col-12 col-md-4">
                    <label class="card h-100 p-2.5 rounded-3 border bg-white cursor-pointer decision-opt-card" style="border-color: #cbd5e1; cursor: pointer;">
                        <div class="form-check p-0 d-flex align-items-start gap-2 m-0">
                            <input class="form-check-input ms-0 mt-1 flex-shrink-0" type="radio" name="decision" value="reattempt_today" id="dec_home" onchange="handleDecisionChange('reattempt_today')">
                            <div class="w-100">
                                <div class="fw-bold text-dark small"><i class="bi bi-geo-alt-fill text-warning me-1"></i> I Am Home Now</div>
                                <div class="text-muted" style="font-size: 0.72rem;">Ready for doorstep handover today</div>
                            </div>
                        </div>
                    </label>
                </div>
            </div>

            {{-- Reschedule Tomorrow Slot Selector --}}
            <div id="sectionRescheduleSlot" class="mb-3 p-2.5 rounded-3 bg-white border border-secondary-subtle">
                <label class="form-label small fw-bold text-dark mb-1.5 d-block">Select Tomorrow's Preferred Delivery Window:</label>
                <div class="d-flex gap-2 flex-wrap">
                    <label class="btn btn-sm btn-outline-primary rounded-pill px-3 py-1 fw-semibold slot-btn active" style="font-size: 0.78rem;">
                        <input type="radio" name="delivery_slot" value="Tomorrow Morning (8:00 AM - 12:00 PM)" class="d-none" checked onchange="selectSlotBtn(this)">
                        <i class="bi bi-sunrise me-1"></i> Morning: 8:00 AM – 12:00 PM
                    </label>
                    <label class="btn btn-sm btn-outline-primary rounded-pill px-3 py-1 fw-semibold slot-btn" style="font-size: 0.78rem;">
                        <input type="radio" name="delivery_slot" value="Tomorrow Evening (4:00 PM - 8:00 PM)" class="d-none" onchange="selectSlotBtn(this)">
                        <i class="bi bi-sunset me-1"></i> Evening: 4:00 PM – 8:00 PM
                    </label>
                </div>
            </div>

            {{-- Security / Neighbor Custom Instructions --}}
            <div id="sectionNeighborNote" class="mb-3 p-2.5 rounded-3 bg-white border border-secondary-subtle" style="display: none;">
                <label class="form-label small fw-bold text-dark mb-1.5 d-block">Drop Instructions for Delivery Partner:</label>
                <input type="text" name="instructions" class="form-control form-control-sm rounded-pill px-3" placeholder="e.g. Please leave with Security Guard at Tower A gate, or Flat 204 neighbor" style="font-size: 0.82rem;">
            </div>

            <button type="submit" class="btn btn-warning btn-sm text-dark fw-bold rounded-pill px-4 py-2 shadow-xs d-inline-flex align-items-center gap-1.5" style="font-size: 0.82rem;">
                <i class="bi bi-arrow-repeat"></i> <span>Confirm & Update Delivery Instructions</span>
            </button>
        </form>
    </div>
@elseif($order->fulfillment?->delivery_issue_resolved_at && !$isDelivered && $s !== 'cancelled')
    <div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-4 p-3 d-flex flex-row align-items-center justify-content-between flex-wrap gap-2.5" style="background: #ecfdf5; border: 1.5px solid #86efac !important; max-width: 100%;">
        <div class="d-flex align-items-center gap-2.5">
            <div style="width: 38px; height: 38px; border-radius: 10px; background: #dcfce7; color: #16a34a; display: flex; align-items: center; justify-content: center; font-size: 1.15rem; flex-shrink: 0;">
                <i class="bi bi-check-circle-fill text-success"></i>
            </div>
            <div>
                <span class="fw-bold text-dark small d-block" style="font-size: 0.88rem;">Delivery Schedule Synchronized</span>
                <span class="text-secondary small" style="font-size: 0.78rem;">{{ $order->fulfillment->delivery_issue_resolution }}</span>
            </div>
        </div>
        <span class="badge bg-white text-success border border-success-subtle rounded-pill px-2.5 py-1 fw-bold" style="font-size: 0.72rem;">
            <i class="bi bi-check2-all me-1"></i> Synced with Hub & Rider
        </span>
    </div>
@endif

{{-- ── Prominent Delivery Experience 5-Star Rating Card (Delivered State) ── --}}
@if($isDelivered)
    @if(!$order->feedback)
        <div class="card border-0 shadow-sm rounded-4 mb-4 p-4 text-center overflow-hidden" style="background: linear-gradient(135deg, #f0fdf4 0%, #ffffff 100%); border: 1.5px solid #86efac !important; max-width: 100%;">
            <div class="d-inline-flex align-items-center justify-content-center mx-auto mb-2 shadow-xs" style="width: 44px; height: 44px; border-radius: 12px; background: #dcfce7; color: #16a34a; font-size: 1.2rem;">
                <i class="bi bi-star-fill text-warning"></i>
            </div>
            <h5 class="fw-bold text-dark mb-1" style="font-size: 1rem;">Rate Delivery Experience</h5>
            <p class="text-secondary small mb-3 mx-auto" style="max-width: 440px; font-size: 0.82rem;">How was the delivery by {{ $order->rider_name ?: 'our delivery partner' }}?</p>
            <div class="d-flex align-items-center justify-content-center gap-2 mb-3">
                @for($star = 1; $star <= 5; $star++)
                    <button type="button" class="btn btn-light rounded-circle shadow-xs border star-quick-btn" style="width: 40px; height: 40px; color: #f59e0b; background: #fffbeb; border-color: #fde68a !important; display: flex; align-items: center; justify-content: center; transition: transform 0.15s ease;" onclick="openFeedbackWithRating({{ $star }})" title="Rate {{ $star }} Stars">
                        <i class="bi bi-star-fill" style="font-size: 1.15rem;"></i>
                    </button>
                @endfor
            </div>
            <button type="button" class="btn btn-sm btn-primary rounded-pill px-4 py-2 fw-semibold shadow-xs mx-auto d-inline-flex align-items-center gap-1.5" data-bs-toggle="modal" data-bs-target="#modalOrderFeedback" style="font-size: 0.82rem;">
                <i class="bi bi-pencil-square"></i> <span>Write Feedback</span>
            </button>
        </div>
    @else
        <div class="card border-0 shadow-sm rounded-4 mb-4 p-3.5 d-flex flex-row align-items-center justify-content-between flex-wrap gap-2.5 overflow-hidden" style="background: #f0fdf4; border: 1.5px solid #86efac !important; max-width: 100%;">
            <div class="d-flex align-items-center gap-2.5">
                <div style="width: 38px; height: 38px; border-radius: 10px; background: #dcfce7; color: #16a34a; display: flex; align-items: center; justify-content: center; font-size: 1.15rem; flex-shrink: 0;">
                    <i class="bi bi-patch-check-fill text-success"></i>
                </div>
                <div>
                    <div class="fw-bold text-dark small" style="font-size: 0.88rem;">
                        You rated this delivery:
                        <span class="text-warning fw-extrabold font-monospace">
                            @for($i = 1; $i <= $order->feedback->delivery_rating; $i++)★@endfor
                        </span>
                        <span class="text-muted">({{ $order->feedback->delivery_rating }}/5)</span>
                    </div>
                    @if(!empty($order->feedback->tags))
                        <div class="d-flex gap-1 flex-wrap mt-1">
                            @foreach($order->feedback->tags as $t)
                                <span class="badge bg-white text-success border border-success-subtle px-2 py-0.5 rounded-pill" style="font-size: 0.68rem;">{{ $t }}</span>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
            <button type="button" class="btn btn-sm btn-outline-success rounded-pill px-3 py-1.5 fw-bold shadow-xs d-inline-flex align-items-center gap-1" data-bs-toggle="modal" data-bs-target="#modalOrderFeedback" style="font-size: 0.78rem;">
                <i class="bi bi-pencil"></i> <span>Edit Feedback</span>
            </button>
        </div>
    @endif
@endif

{{-- Order Details Grid (Order Summary & Shipping Address) --}}
<div class="row g-3 g-md-4 mb-4">
    {{-- Order Summary Card --}}
    <div class="col-12 col-md-6">
        <div class="card border-0 shadow-sm rounded-4 h-100 overflow-hidden" style="background: #ffffff; border: 1.5px solid #e2e8f0 !important;">
            <div class="card-header bg-white py-3 px-3 px-md-4 border-bottom">
                <h6 class="mb-0 fw-bold text-dark d-flex align-items-center gap-2" style="font-size: 0.92rem;">
                    <i class="bi bi-info-circle-fill text-primary"></i> Order Overview
                </h6>
            </div>
            <div class="card-body p-3 p-md-4">
                <div class="d-flex justify-content-between align-items-center border-bottom pb-2 mb-2 flex-wrap gap-1" style="font-size: 0.86rem;">
                    <span class="text-muted text-nowrap">Order Reference:</span>
                    <span class="fw-bold text-dark font-monospace text-end ps-2 ms-auto">#{{ $order->order_number }}</span>
                </div>
                <div class="d-flex justify-content-between align-items-center border-bottom pb-2 mb-2 flex-wrap gap-1" style="font-size: 0.86rem;">
                    <span class="text-muted text-nowrap">Order Date:</span>
                    <span class="fw-semibold text-dark text-end ps-2 ms-auto">{{ $order->created_at ? $order->created_at->format('d M, Y') : 'N/A' }}</span>
                </div>
                <div class="d-flex justify-content-between align-items-start border-bottom pb-2 mb-2 flex-wrap gap-1" style="font-size: 0.86rem;">
                    <span class="text-muted text-nowrap">Payment Mode:</span>
                    <span class="fw-bold text-dark text-end ps-2 ms-auto" style="max-width: 100%;">
                        @if($order->payment_method === 'wallet' || ($order->total_amount <= 0 && $order->wallet_amount_used > 0))
                            <span class="text-success"><i class="bi bi-wallet2 me-1"></i>ShopCalm Wallet (100% Paid)</span>
                        @elseif($order->wallet_amount_used > 0)
                            <span class="text-dark"><i class="bi bi-wallet2 me-1"></i>Wallet (₹{{ number_format($order->wallet_amount_used, 2) }}) + {{ $order->payment_method === 'online' ? 'Prepaid Online' : ($order->payment_method === 'doorstep_upi' ? 'Doorstep UPI' : 'Cash on Delivery') }}</span>
                        @elseif($order->payment_method === 'doorstep_upi')
                            <span class="text-success"><i class="bi bi-qr-code-scan me-1"></i>Doorstep UPI (Razorpay QR)</span>
                        @elseif($order->payment_method === 'online')
                            <span class="text-success"><i class="bi bi-shield-check me-1"></i>Prepaid ({{ $order->primaryPayment?->method_display ?? 'Online' }})</span>
                        @elseif($order->payment_method === 'cod')
                            @if($order->payment_status === 'paid')
                                <span class="text-success"><i class="bi bi-cash-stack me-1"></i>Cash on Delivery (Paid in Cash)</span>
                            @else
                                <span class="text-secondary"><i class="bi bi-cash me-1"></i>Cash on Delivery (Pay on Delivery)</span>
                            @endif
                        @else
                            <span class="text-secondary">{{ strtoupper($order->payment_method) }}</span>
                        @endif
                    </span>
                </div>
                @if($order->primaryPayment?->bank_reference)
                <div class="d-flex justify-content-between align-items-center border-bottom pb-2 mb-2 flex-wrap gap-1" style="font-size: 0.86rem;">
                    <span class="text-muted text-nowrap">Bank Ref (UTR):</span>
                    <span class="font-monospace fw-bold text-primary text-end ps-2 ms-auto">{{ $order->primaryPayment->bank_reference }}</span>
                </div>
                @endif
                <div class="d-flex justify-content-between align-items-center border-bottom pb-2 mb-2 flex-wrap gap-1" style="font-size: 0.86rem;">
                    <span class="text-muted text-nowrap">Status:</span>
                    <span class="badge rounded-pill px-3 py-1 fw-bold text-end ms-auto" style="{{ $statusStyle }}; font-size: 0.72rem;">{{ ucfirst($order->status) }}</span>
                </div>
                <div class="d-flex justify-content-between align-items-center pt-1 flex-wrap gap-1" style="font-size: 0.88rem;">
                    <span class="text-muted fw-semibold">Total Amount:</span>
                    <span class="fw-bolder text-primary fs-6 font-monospace text-end ps-2 ms-auto">₹{{ number_format($order->total_amount, 2) }}</span>
                </div>
            </div>
        </div>
    </div>

    {{-- Shipping Address Card --}}
    <div class="col-12 col-md-6">
        <div class="card border-0 shadow-sm rounded-4 h-100 overflow-hidden" style="background: #ffffff; border: 1.5px solid #e2e8f0 !important;">
            <div class="card-header bg-white py-3 px-3 px-md-4 border-bottom">
                <h6 class="mb-0 fw-bold text-dark d-flex align-items-center gap-2" style="font-size: 0.92rem;">
                    <i class="bi bi-geo-alt-fill text-danger"></i> Shipping Address
                </h6>
            </div>
            <div class="card-body p-3 p-md-4">
                <div class="d-flex align-items-center gap-2 mb-2">
                    <i class="bi bi-person-circle text-primary fs-5"></i>
                    <h6 class="fw-bold text-dark mb-0" style="font-size: 0.94rem;">{{ $order->shipping_name }}</h6>
                </div>
                <p class="text-secondary small mb-3 ps-0 ps-sm-3" style="line-height: 1.5; font-size: 0.84rem;">
                    {{ $order->shipping_address }}<br>
                    {{ $order->shipping_city }}, {{ $order->shipping_state }} - {{ $order->shipping_zip }}<br>
                    {{ $order->shipping_country }}
                </p>
                <div class="ps-0 ps-sm-3 text-muted small d-flex flex-column gap-1.5" style="font-size: 0.8rem;">
                    <div><i class="bi bi-envelope me-2 text-info"></i>{{ $order->shipping_email }}</div>
                    <div><i class="bi bi-telephone-fill me-2 text-success"></i>{{ $order->shipping_phone }}</div>
                    @php
                        $rawCustomerNotes = $order->notes ?? '';
                        $prefTimeSlot = '';
                        $cleanInstruction = $rawCustomerNotes;
                        if (preg_match('/\[Time:\s*([^\]]+)\]/i', $rawCustomerNotes, $noteMatches)) {
                            $prefTimeSlot = trim($noteMatches[1]);
                            $cleanInstruction = trim(str_replace($noteMatches[0], '', $rawCustomerNotes));
                        }
                    @endphp
                    @if(!empty($cleanInstruction) || !empty($prefTimeSlot))
                    <div class="mt-2.5 p-2.5 rounded-3 border" style="background: #fffbeb; border-color: #fde68a !important;">
                        <div class="d-flex align-items-center gap-1.5 fw-bold small mb-1" style="font-size: 0.8rem; color: #92400e !important;">
                            <i class="bi bi-pin-angle-fill text-warning"></i>
                            <span>Your Delivery Instruction (Recorded):</span>
                        </div>
                        @if(!empty($cleanInstruction))
                            <div class="fw-semibold text-dark" style="font-size: 0.82rem; line-height: 1.4;">
                                "{{ $cleanInstruction }}"
                            </div>
                        @endif
                        @if(!empty($prefTimeSlot))
                            <div class="mt-1 small text-secondary" style="font-size: 0.74rem;">
                                <i class="bi bi-clock-history me-1"></i> Preferred Window: <span class="badge bg-white text-dark border px-2 py-0.5 rounded-pill fw-semibold">{{ $prefTimeSlot }}</span>
                            </div>
                        @endif
                        <div class="text-muted small mt-1" style="font-size: 0.7rem;">
                            <i class="bi bi-shield-check text-success me-1"></i> Transmitted directly to your delivery partner and dispatch hub.
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Order Items Card (Perfect Alignment for Mobile & Desktop) --}}
<div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-4" style="background: #ffffff; border: 1.5px solid #e2e8f0 !important; max-width: 100%;">
    <div class="card-header bg-white py-3 px-3 px-md-4 border-bottom d-flex align-items-center justify-content-between">
        <h6 class="mb-0 fw-bold text-dark d-flex align-items-center gap-2" style="font-size: 0.92rem;">
            <i class="bi bi-bag-check-fill text-primary"></i> Purchased Items ({{ $order->items->count() }})
        </h6>
    </div>

    {{-- Desktop Table Header Bar --}}
    <div class="d-none d-md-flex align-items-center px-4 py-2 bg-light border-bottom text-muted small fw-bold" style="font-size: 0.75rem; letter-spacing: 0.04em;">
        <div style="flex: 1 1 50%;">ITEM DETAILS</div>
        <div style="flex: 0 0 16%;" class="text-center">UNIT PRICE</div>
        <div style="flex: 0 0 16%;" class="text-center">QUANTITY</div>
        <div style="flex: 0 0 18%;" class="text-end">SUBTOTAL</div>
    </div>

    <div class="card-body p-0" style="max-width: 100%; overflow: hidden;">
        <div class="d-flex flex-column" style="max-width: 100%;">
            @foreach($order->items as $index => $item)
            @php 
                $product = $item->product;
                $origPrice = (float) ($item->original_price ?: ($item->unit_price ?: $item->price));
                $unitPrice = (float) ($item->unit_price ?: $item->price);
                $hasDiscount = $origPrice > $unitPrice;
                $discountPct = $hasDiscount && $origPrice > 0 ? round((($origPrice - $unitPrice) / $origPrice) * 100) : 0;
                $savedPerUnit = $hasDiscount ? ($origPrice - $unitPrice) : 0;
                $totalSaved = $savedPerUnit * $item->quantity;
            @endphp
            <div class="p-3 p-md-4 {{ $index < $order->items->count()-1 ? 'border-bottom' : '' }}" style="border-color: #f1f5f9 !important; max-width: 100%; min-width: 0; overflow: hidden;">
                
                {{-- 💻 Desktop Layout (Aligned to Header Bar) --}}
                <div class="d-none d-md-flex align-items-center" style="min-width: 0; max-width: 100%;">
                    {{-- 1. Thumbnail + Info (50%) --}}
                    <div class="d-flex align-items-center gap-3 min-w-0" style="flex: 1 1 50%; min-width: 0; overflow: hidden;">
                        <div class="flex-shrink-0">
                            @php $orderThumb = $item->display_image ?? $product?->main_image; @endphp
                            @if($orderThumb)
                                <a href="{{ route('product.show', $product->slug) }}">
                                    <img src="{{ asset('storage/' . $orderThumb) }}"
                                         alt="{{ $item->product_name }}"
                                         class="rounded-3 border p-1 bg-white shadow-2xs"
                                         width="64" height="64" style="object-fit: contain;">
                                </a>
                            @else
                                <div class="rounded-3 bg-light border d-flex align-items-center justify-content-center text-muted"
                                     style="width: 64px; height: 64px;">
                                    <i class="bi bi-image fs-4 opacity-25"></i>
                                </div>
                            @endif
                        </div>

                        <div class="min-w-0 flex-grow-1 pe-3" style="min-width: 0; overflow: hidden;">
                            <div class="d-flex align-items-center gap-1.5 flex-wrap mb-1">
                                @if($product && $product->brand)
                                    <span class="badge bg-light text-secondary border rounded-pill px-2 py-0.5" style="font-size: 0.65rem;">
                                        {{ $product->brand->name }}
                                    </span>
                                @endif
                                @if($product && $product->category)
                                    <span class="badge bg-light text-muted border rounded-pill px-2 py-0.5" style="font-size: 0.65rem;">
                                        {{ $product->category->name }}
                                    </span>
                                @endif
                                @if($item->selected_option)
                                    <span class="badge rounded-pill px-2.5 py-0.5 fw-bold d-inline-flex align-items-center gap-1 shadow-2xs" 
                                          style="background: #e0f2fe !important; color: #0369a1 !important; border: 1px solid #7dd3fc !important; font-size: 0.74rem;">
                                        <i class="bi bi-tag-fill" style="font-size: 0.65rem; color: #0284c7;"></i>
                                        <span>{{ $item->selected_option }}</span>
                                    </span>
                                @endif
                            </div>
                            @if($product)
                                <a href="{{ route('product.show', $product->slug) }}"
                                   class="fw-bold text-dark text-decoration-none d-block text-truncate"
                                   style="font-size: 0.9rem; line-height: 1.35;"
                                   title="{{ $item->product_name }}">
                                    {{ $item->product_name }}
                                </a>
                            @else
                                <div class="fw-bold text-dark text-truncate" style="font-size: 0.9rem;">{{ $item->product_name }}</div>
                                <span class="text-muted small" style="font-size: 0.72rem;">Product no longer available</span>
                            @endif
                        </div>
                    </div>

                    {{-- 2. Unit Price (16%) --}}
                    <div style="flex: 0 0 16%;" class="text-center">
                        <div class="fw-bold text-dark font-monospace" style="font-size: 0.92rem;">₹{{ number_format($unitPrice, 2) }}</div>
                        @if($hasDiscount)
                            <div class="d-flex align-items-center justify-content-center gap-1.5 mt-0.5">
                                <span class="text-muted text-decoration-line-through small" style="font-size: 0.74rem;">₹{{ number_format($origPrice, 2) }}</span>
                                <span class="badge rounded-pill bg-success bg-opacity-10 text-success border border-success border-opacity-25 px-1.5 py-0.5 fw-bold" style="font-size: 0.65rem;">{{ $discountPct }}% OFF</span>
                            </div>
                        @endif
                    </div>

                    {{-- 3. Quantity (16%) --}}
                    <div style="flex: 0 0 16%;" class="text-center">
                        <span class="badge bg-light text-dark border px-3 py-1 rounded-pill fw-bold font-monospace" style="font-size: 0.8rem;">
                            ×{{ $item->quantity }}
                        </span>
                    </div>

                    {{-- 4. Line Total (18%) --}}
                    <div style="flex: 0 0 18%;" class="text-end">
                        <div class="fw-bolder text-dark font-monospace" style="font-size: 1rem;">
                            ₹{{ number_format($item->total_price, 2) }}
                        </div>
                    </div>
                </div>

                {{-- 📱 Mobile Layout (Spacious Vertical Stack with Clear Price Hierarchy) --}}
                <div class="d-block d-md-none" style="width: 100%; max-width: 100%; min-width: 0; overflow: hidden;">
                    <div class="d-flex align-items-start gap-2.5 gap-sm-3 mb-2.5" style="width: 100%; max-width: 100%; min-width: 0;">
                        <div class="flex-shrink-0">
                            @php $orderThumb = $item->display_image ?? $product?->main_image; @endphp
                            @if($orderThumb)
                                <a href="{{ route('product.show', $product->slug) }}">
                                    <img src="{{ asset('storage/' . $orderThumb) }}"
                                         alt="{{ $item->product_name }}"
                                         class="rounded-3 border p-1 bg-white shadow-2xs"
                                         width="64" height="64" style="width: 64px; height: 64px; object-fit: contain; flex-shrink: 0;">
                                </a>
                            @else
                                <div class="rounded-3 bg-light border d-flex align-items-center justify-content-center text-muted"
                                     style="width: 64px; height: 64px; flex-shrink: 0;">
                                    <i class="bi bi-image fs-4 opacity-25"></i>
                                </div>
                            @endif
                        </div>

                        <div class="flex-grow-1 min-w-0" style="min-width: 0; overflow: hidden; word-break: break-word;">
                            <div class="d-flex align-items-center gap-1.5 flex-wrap mb-1" style="max-width: 100%;">
                                @if($product && $product->brand)
                                    <span class="badge bg-light text-secondary border rounded-pill px-2 py-0.5" style="font-size: 0.65rem;">
                                        {{ $product->brand->name }}
                                    </span>
                                @endif
                                @if($product && $product->category)
                                    <span class="badge bg-light text-muted border rounded-pill px-2 py-0.5" style="font-size: 0.65rem;">
                                        {{ $product->category->name }}
                                    </span>
                                @endif
                                @if($item->selected_option)
                                    <span class="badge rounded-pill px-2.5 py-0.5 fw-bold d-inline-flex align-items-center gap-1 shadow-2xs" 
                                          style="background: #e0f2fe !important; color: #0369a1 !important; border: 1px solid #7dd3fc !important; font-size: 0.74rem;">
                                        <i class="bi bi-tag-fill" style="font-size: 0.65rem; color: #0284c7;"></i>
                                        <span>{{ $item->selected_option }}</span>
                                    </span>
                                @endif
                            </div>
                            @if($product)
                                <a href="{{ route('product.show', $product->slug) }}"
                                   class="fw-bold text-dark text-decoration-none d-block mb-1 text-line-clamp-2"
                                   style="font-size: 0.9rem; line-height: 1.35;"
                                   title="{{ $item->product_name }}">
                                    {{ $item->product_name }}
                                </a>
                            @else
                                <div class="fw-bold text-dark mb-1 text-line-clamp-2" style="font-size: 0.9rem; line-height: 1.35;">{{ $item->product_name }}</div>
                            @endif

                            {{-- Price Row (Clear deal price, real price, % off) --}}
                            <div class="d-flex align-items-center gap-2 flex-wrap pt-0.5" style="max-width: 100%;">
                                <span class="fw-bold text-dark font-monospace" style="font-size: 0.96rem;">₹{{ number_format($unitPrice, 2) }}</span>
                                @if($hasDiscount)
                                    <span class="text-muted text-decoration-line-through small" style="font-size: 0.76rem;">₹{{ number_format($origPrice, 2) }}</span>
                                    <span class="badge rounded-pill bg-success bg-opacity-10 text-success border border-success border-opacity-25 px-2 py-0.5 fw-bold" style="font-size: 0.65rem;">{{ $discountPct }}% OFF</span>
                                @endif
                            </div>
                        </div>
                    </div>

                    {{-- Mobile Bottom Bar: Qty Badge on Left + Subtotal on Right --}}
                    <div class="d-flex align-items-center justify-content-between px-2.5 px-sm-3 py-2 rounded-3 border flex-wrap gap-2" style="background: #f8fafc; border-color: #e2e8f0 !important; font-size: 0.84rem; width: 100%; max-width: 100%;">
                        <div class="d-flex align-items-center gap-1.5 flex-wrap">
                            <span class="text-muted small">Qty:</span>
                            <span class="badge bg-white text-dark border px-2.5 py-1 fw-bold font-monospace shadow-2xs">×{{ $item->quantity }}</span>
                        </div>
                        <div class="d-flex align-items-baseline gap-1.5 flex-wrap ms-auto">
                            <span class="text-muted small">Subtotal:</span>
                            <span class="fw-bolder text-dark font-monospace" style="font-size: 0.96rem;">₹{{ number_format($item->total_price, 2) }}</span>
                        </div>
                    </div>
                </div>

            </div>
            @endforeach
        </div>
    </div>
</div>

{{-- Payment & Total Receipt Summary Box --}}
@php
    $totalMrp = (float) $order->items->sum(function($it) {
        $orig = (float) ($it->original_price > 0 ? $it->original_price : ($it->product?->price ?? ($it->unit_price ?: $it->price)));
        return $orig * $it->quantity;
    });
    $orderSubtotal = (float) ($order->subtotal_amount > 0 ? $order->subtotal_amount : $order->items->sum('total_price'));
    $mrpDiscount = max(0, $totalMrp - $orderSubtotal);
    $couponDiscount = (float) ($order->coupon_discount_amount ?? 0);
    $walletUsed = (float) ($order->wallet_amount_used ?? 0);
    $shippingCharge = (float) ($order->shipping_charge ?? 0);
    $codFee = (float) ($order->cod_fee ?? 0);
    $grandTotal = (float) $order->total_amount;
    $totalSavings = $mrpDiscount + $couponDiscount + $walletUsed;
    $savingsPct = ($totalMrp > 0 && $totalSavings > 0) ? round(($totalSavings / $totalMrp) * 100) : 0;
    $totalItemsCount = (int) $order->items->sum('quantity');
@endphp

<div class="row justify-content-end mb-4">
    <div class="col-12 col-md-7 col-lg-6 col-xl-5">
        <div class="card border-0 shadow-sm rounded-4 overflow-hidden" style="background:#ffffff; border: 1.5px solid #e2e8f0 !important; max-width: 100%;">
            <div class="card-header bg-white py-3 px-3 px-sm-4 border-bottom d-flex align-items-center justify-content-between">
                <h6 class="mb-0 fw-bold text-dark d-flex align-items-center gap-2" style="font-size: 0.92rem;">
                    <i class="bi bi-receipt text-primary"></i> Price Details
                </h6>
                <span class="badge bg-light text-muted border rounded-pill px-3 py-1 font-monospace" style="font-size: 0.72rem;">
                    {{ $totalItemsCount }} {{ Str::plural('Item', $totalItemsCount) }}
                </span>
            </div>

            <div class="card-body p-3 p-sm-4">
                {{-- 1. Total MRP (if discounted) --}}
                @if($mrpDiscount > 0)
                <div class="d-flex justify-content-between align-items-center text-muted small mb-2" style="font-size: 0.86rem;">
                    <span>Total MRP:</span>
                    <span class="text-muted text-decoration-line-through font-monospace">₹{{ number_format($totalMrp, 2) }}</span>
                </div>

                {{-- 2. Discount on MRP / Offer Discount --}}
                <div class="d-flex justify-content-between align-items-center small mb-2" style="color: #16a34a; font-size: 0.86rem;">
                    <span class="d-inline-flex align-items-center gap-1.5">
                        <i class="bi bi-tag-fill text-success"></i>
                        <span>Discount on MRP:</span>
                        @if($totalMrp > 0)
                            <span class="badge rounded-pill bg-success bg-opacity-10 text-success border border-success border-opacity-25 px-2 py-0.5 fw-bold" style="font-size: 0.68rem;">
                                {{ round(($mrpDiscount / $totalMrp) * 100) }}% OFF
                            </span>
                        @endif
                    </span>
                    <span class="fw-bold font-monospace text-success">-₹{{ number_format($mrpDiscount, 2) }}</span>
                </div>
                @endif

                {{-- 3. Items Cart Subtotal --}}
                <div class="d-flex justify-content-between align-items-center text-muted small mb-2" style="font-size: 0.86rem;">
                    <span>Cart Subtotal:</span>
                    <span class="fw-semibold text-dark font-monospace">₹{{ number_format($orderSubtotal, 2) }}</span>
                </div>

                {{-- 4. Delivery Charge --}}
                <div class="d-flex justify-content-between align-items-center text-muted small mb-2" style="font-size: 0.86rem;">
                    <span>Delivery Fee:</span>
                    @if($shippingCharge > 0)
                        <span class="fw-bold text-dark font-monospace">+₹{{ number_format($shippingCharge, 2) }}</span>
                    @else
                        <span class="badge bg-success bg-opacity-10 text-success rounded-pill px-2.5 py-0.5 fw-bold" style="font-size: 0.72rem; padding: 0.25rem 0.6rem;">FREE</span>
                    @endif
                </div>

                {{-- 5. COD Handling Fee (if used) --}}
                @if($codFee > 0)
                <div class="d-flex justify-content-between align-items-center text-muted small mb-2" style="font-size: 0.86rem;">
                    <span class="d-inline-flex align-items-center gap-1.5"><i class="bi bi-cash text-success"></i> COD Handling Fee:</span>
                    <span class="fw-bold text-dark font-monospace">+₹{{ number_format($codFee, 2) }}</span>
                </div>
                @endif

                {{-- 6. Coupon Discount (if used) --}}
                @if($couponDiscount > 0)
                <div class="d-flex justify-content-between align-items-center text-success small mb-2" style="font-size: 0.86rem;">
                    <span class="d-inline-flex align-items-center gap-1.5">
                        <i class="bi bi-ticket-perforated-fill text-success"></i>
                        <span>Coupon Discount @if($order->coupon)({{ $order->coupon->code }})@endif:</span>
                    </span>
                    <span class="fw-bold font-monospace">-₹{{ number_format($couponDiscount, 2) }}</span>
                </div>
                @endif

                {{-- 7. Wallet Balance Applied (if used) --}}
                @if($walletUsed > 0)
                <div class="d-flex justify-content-between align-items-center small mb-2" style="color: #6366f1; font-size: 0.86rem;">
                    <span class="d-inline-flex align-items-center gap-1.5">
                        <i class="bi bi-wallet2"></i>
                        <span>ShopCalm Wallet Applied:</span>
                    </span>
                    <span class="fw-bold font-monospace" style="color: #6366f1;">-₹{{ number_format($walletUsed, 2) }}</span>
                </div>
                @endif

                {{-- 8. Grand Total --}}
                <div class="border-top pt-3 mt-3 d-flex justify-content-between align-items-center" style="border-top: 1.5px dashed #cbd5e1 !important;">
                    <div class="pe-2">
                        <span class="fw-bold text-dark fs-6 d-block">Grand Total:</span>
                        <small class="text-muted d-inline-flex align-items-center gap-1" style="font-size: 0.72rem;">
                            <i class="bi bi-shield-check text-success"></i> Inclusive of all taxes
                        </small>
                    </div>
                    <div class="text-end ps-2">
                        <span class="fw-bolder text-primary fs-5 font-monospace text-nowrap">₹{{ number_format($grandTotal, 2) }}</span>
                    </div>
                </div>

                {{-- 9. Total Order Savings Callout --}}
                @if($totalSavings > 0)
                <div class="mt-3 p-3 rounded-3 d-flex align-items-center gap-2" style="background: #f0fdf4; border: 1px dashed #86efac; color: #15803d;">
                    <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0" style="width: 28px; height: 28px; background: #dcfce7; color: #16a34a;">
                        <i class="bi bi-piggy-bank-fill" style="font-size: 0.95rem;"></i>
                    </div>
                    <div class="small fw-semibold min-w-0" style="font-size: 0.82rem; line-height: 1.4;">
                        You saved <span class="fw-bold font-monospace text-success">₹{{ number_format($totalSavings, 2) }}</span>@if($savingsPct > 0) ({{ $savingsPct }}%)@endif on this order!
                    </div>
                </div>
                @endif

                {{-- 10. Payment Method & Status Badge Bar --}}
                <div class="mt-3 pt-3 border-top d-flex align-items-center justify-content-between flex-wrap gap-2" style="font-size: 0.8rem; border-color: #f1f5f9 !important;">
                    <div class="d-flex align-items-center gap-1.5 text-muted">
                        <span>Payment Method:</span>
                        <strong class="text-dark">
                            @if($order->payment_method === 'doorstep_upi')
                                <span class="text-success"><i class="bi bi-qr-code-scan me-1"></i>Doorstep UPI (Razorpay QR)</span>
                            @elseif($order->payment_method === 'online')
                                <span class="text-primary"><i class="bi bi-credit-card me-1"></i>Prepaid Online (Razorpay)</span>
                            @elseif($order->payment_method === 'wallet' || ($order->total_amount <= 0 && $order->wallet_amount_used > 0))
                                <span class="text-success"><i class="bi bi-wallet2 me-1"></i>ShopCalm Wallet (100% Paid)</span>
                            @elseif($order->payment_method === 'cod')
                                <span class="text-secondary"><i class="bi bi-cash me-1"></i>Cash on Delivery</span>
                            @else
                                <span>{{ strtoupper($order->payment_method ?: 'N/A') }}</span>
                            @endif
                        </strong>
                    </div>
                    <div>
                        @if($order->payment_status === 'paid')
                            <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 rounded-pill px-3 py-1 fw-bold d-inline-flex align-items-center gap-1">
                                <i class="bi bi-check-circle-fill"></i> Paid @if($order->payment_method === 'doorstep_upi') via Doorstep UPI @endif
                            </span>
                        @elseif($order->payment_method === 'cod')
                            <span class="badge bg-info bg-opacity-10 text-info border border-info border-opacity-25 rounded-pill px-3 py-1 fw-bold text-dark d-inline-flex align-items-center gap-1">
                                <i class="bi bi-cash"></i> Pay on Delivery
                            </span>
                        @else
                            <span class="badge bg-warning bg-opacity-15 text-dark border border-warning border-opacity-50 rounded-pill px-3 py-1 fw-bold d-inline-flex align-items-center gap-1">
                                <i class="bi bi-clock-history text-warning"></i> Payment Pending
                            </span>
                        @endif
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>

{{-- ── Contactless Delivery Proof Modal ── --}}
@if(isset($podPhoto) && $podPhoto)
<div class="modal fade" id="modalPodPhoto" tabindex="-1" aria-labelledby="modalPodPhotoLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow">
            <div class="modal-header border-bottom py-3 px-4">
                <h6 class="modal-title fw-bold text-dark d-flex align-items-center gap-2" id="modalPodPhotoLabel">
                    <i class="bi bi-camera-fill text-primary"></i> Contactless Delivery Proof
                </h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-3 text-center">
                <img src="{{ asset('storage/' . $podPhoto) }}" alt="Delivery Proof" class="img-fluid rounded-3 shadow-sm border" style="max-height: 420px; width: auto; object-fit: contain;">
                <div class="mt-2 text-muted small">
                    <i class="bi bi-geo-alt-fill text-danger me-1"></i> {{ $dropLabel ?? 'Contactless Drop' }} &bull; Watermarked at Delivery
                </div>
            </div>
            <div class="modal-footer border-0 p-3 pt-0">
                <button type="button" class="btn btn-light rounded-pill px-4 fw-semibold text-secondary btn-sm" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
@endif

{{-- ── Order Delivery Experience Feedback Modal ── --}}
@if($order->status === 'delivered')
<div class="modal fade" id="modalOrderFeedback" tabindex="-1" aria-labelledby="modalOrderFeedbackLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content border-0 rounded-4 shadow-lg overflow-hidden">
            
            {{-- Form Section --}}
            <div id="feedbackFormSection" class="flex-column h-100" style="display: flex; min-height: 0; overflow: hidden; flex: 1 1 auto;">
                <div class="modal-header border-0 pb-0 px-4 pt-4">
                    <div class="d-flex align-items-center gap-2">
                        <div style="width: 36px; height: 36px; border-radius: 10px; background: #fef3c7; color: #d97706; display: flex; align-items: center; justify-content: center; font-size: 1.1rem;">
                            <i class="bi bi-star-fill"></i>
                        </div>
                        <div>
                            <h6 class="modal-title fw-bold text-dark mb-0" id="modalOrderFeedbackLabel">Delivery Feedback</h6>
                            <small class="text-muted">Order #{{ $order->order_number }}</small>
                        </div>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <form id="formOrderFeedback" action="{{ route('account.orders.feedback.store', $order) }}" method="POST" class="d-flex flex-column" style="min-height: 0; overflow: hidden; flex: 1 1 auto;">
                    @csrf
                    <div class="modal-body px-4 py-3">
                        <div class="alert alert-light border rounded-3 p-2.5 mb-3 d-flex align-items-center gap-2" style="background:#f8fafc;">
                            <i class="bi bi-info-circle text-primary fs-5"></i>
                            <span class="small text-muted">Your feedback helps us evaluate rider service, timely delivery, and packaging quality.</span>
                        </div>

                        {{-- Delivery Experience Rating --}}
                        <div class="mb-3 text-center p-3 rounded-3" style="background: #fafafa; border: 1px dashed #e2e8f0;">
                            <label class="form-label fw-bold text-dark small text-uppercase mb-1" style="letter-spacing: 0.05em;">
                                1. How was your Delivery Experience?
                            </label>
                            <div class="d-flex justify-content-center gap-2 my-1" id="starGroupDelivery">
                                @for($i = 1; $i <= 5; $i++)
                                    <i class="bi bi-star-fill star-item-delivery fs-3 cursor-pointer text-warning" 
                                       data-rating="{{ $i }}" style="cursor: pointer; transition: transform 0.2s;"></i>
                                @endfor
                            </div>
                            <input type="hidden" name="delivery_rating" id="inputDeliveryRating" value="{{ $order->feedback->delivery_rating ?? 5 }}">
                            <span class="small fw-semibold text-secondary" id="labelDeliveryRating">
                                {{ $order->feedback ? $order->feedback->delivery_rating . ' / 5 Stars' : '5 / 5 - Excellent' }}
                            </span>
                        </div>

                        {{-- Product & Packaging Rating --}}
                        <div class="mb-3 text-center p-3 rounded-3" style="background: #fafafa; border: 1px dashed #e2e8f0;">
                            <label class="form-label fw-bold text-dark small text-uppercase mb-1" style="letter-spacing: 0.05em;">
                                2. Product Condition & Packaging?
                            </label>
                            <div class="d-flex justify-content-center gap-2 my-1" id="starGroupProduct">
                                @for($i = 1; $i <= 5; $i++)
                                    <i class="bi bi-star-fill star-item-product fs-3 cursor-pointer text-warning" 
                                       data-rating="{{ $i }}" style="cursor: pointer; transition: transform 0.2s;"></i>
                                @endfor
                            </div>
                            <input type="hidden" name="product_rating" id="inputProductRating" value="{{ $order->feedback->product_rating ?? 5 }}">
                            <span class="small fw-semibold text-secondary" id="labelProductRating">
                                {{ $order->feedback ? $order->feedback->product_rating . ' / 5 Stars' : '5 / 5 - Perfect' }}
                            </span>
                        </div>

                        {{-- Quick Feedback Tags --}}
                        <div class="mb-3">
                            <label class="form-label fw-bold text-dark small mb-1.5">Highlights (Tap to select):</label>
                            @php
                                $availableTags = [
                                    'Fast Delivery',
                                    'Careful Packaging',
                                    'Polite Rider',
                                    'Proactive Call',
                                    'On-Time Arrival',
                                    'Contactless Handover',
                                ];
                                $selectedTags = $order->feedback->tags ?? [];
                            @endphp
                            <div class="d-flex flex-wrap gap-2">
                                @foreach($availableTags as $tag)
                                    @php $isSelected = in_array($tag, $selectedTags); @endphp
                                    <button type="button" 
                                            class="btn btn-sm btn-tag {{ $isSelected ? 'btn-primary text-white' : 'btn-light border text-dark' }} rounded-pill px-3 py-1 fw-semibold"
                                            style="font-size: 0.75rem;" 
                                            onclick="toggleFeedbackTag(this, '{{ $tag }}')">
                                        {{ $tag }}
                                    </button>
                                @endforeach
                            </div>
                            <div id="hiddenTagsContainer">
                                @foreach($selectedTags as $tag)
                                    <input type="hidden" name="tags[]" value="{{ $tag }}">
                                @endforeach
                            </div>
                        </div>

                        {{-- Optional Comments --}}
                        <div class="mb-2">
                            <label class="form-label fw-bold text-dark small mb-1">Additional Comments (Optional):</label>
                            <textarea name="comment" class="form-control rounded-3" rows="3" placeholder="Tell us more about your delivery or package experience..." style="font-size: 0.85rem;">{{ $order->feedback->comment ?? '' }}</textarea>
                        </div>
                    </div>

                    <div class="modal-footer border-0 px-4 pb-3 pt-2 d-flex justify-content-end gap-2" style="flex-shrink: 0; position: sticky; bottom: 0; z-index: 10; background: #ffffff !important; border-top: 1px solid #e2e8f0 !important;">
                        <button type="button" class="btn btn-light rounded-pill px-3 py-2 fw-semibold" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" id="btnSubmitFeedback" class="btn btn-primary rounded-pill px-4 py-2 fw-bold d-inline-flex align-items-center gap-1.5 shadow-sm">
                            <i class="bi bi-check-circle-fill"></i> <span>{{ $order->feedback ? 'Update Feedback' : 'Submit Feedback' }}</span>
                        </button>
                    </div>
                </form>
            </div>

            {{-- Thank You Pop-up Success Section --}}
            <div id="feedbackSuccessSection" class="p-4 py-5 text-center d-none" style="display: none;">
                <div style="width: 60px; height: 60px; border-radius: 50%; background: #ecfdf5; color: #059669; border: 2px solid #a7f3d0; display: flex; align-items: center; justify-content: center; font-size: 1.8rem; margin: 0 auto 1.25rem;">
                    <i class="bi bi-check-lg"></i>
                </div>
                <h5 class="fw-bold text-dark mb-1">Thank You For Your Feedback</h5>
                <p class="text-secondary small mb-3">Your rating helps us evaluate rider service and packaging standards.</p>
                <div class="d-inline-flex align-items-center gap-2 py-1.5 px-3 rounded-pill bg-light border mb-4">
                    <div class="text-warning fw-bold fs-6 d-flex gap-1" id="feedbackSuccessStars"></div>
                    <span class="fw-bold text-dark small" id="feedbackSuccessLabel">Rated 5/5 Stars</span>
                </div>
                <div>
                    <button type="button" class="btn btn-dark rounded-pill px-4 py-2 fw-semibold btn-sm shadow-xs" data-bs-dismiss="modal" onclick="location.reload()">
                        Done
                    </button>
                </div>
            </div>

        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    // Delivery Stars Handler
    const deliveryStars = document.querySelectorAll('.star-item-delivery');
    const inputDelivery = document.getElementById('inputDeliveryRating');
    const labelDelivery = document.getElementById('labelDeliveryRating');
    const ratingsMap = { 1: '1 / 5 - Poor', 2: '2 / 5 - Fair', 3: '3 / 5 - Good', 4: '4 / 5 - Very Good', 5: '5 / 5 - Excellent' };

    function setDeliveryStars(val) {
        deliveryStars.forEach(s => {
            const r = parseInt(s.dataset.rating);
            if (r <= val) {
                s.className = 'bi bi-star-fill star-item-delivery fs-3 cursor-pointer text-warning';
            } else {
                s.className = 'bi bi-star star-item-delivery fs-3 cursor-pointer text-muted';
            }
        });
        inputDelivery.value = val;
        labelDelivery.innerText = ratingsMap[val] || (val + ' / 5');
    }
    window.setDeliveryStars = setDeliveryStars;

    deliveryStars.forEach(s => {
        s.addEventListener('click', () => setDeliveryStars(parseInt(s.dataset.rating)));
        s.addEventListener('mouseenter', () => { s.style.transform = 'scale(1.2)'; });
        s.addEventListener('mouseleave', () => { s.style.transform = 'scale(1)'; });
    });
    setDeliveryStars(parseInt(inputDelivery.value) || 5);

    // Reset modal state whenever modal is opened
    const feedbackModalEl = document.getElementById('modalOrderFeedback');
    if (feedbackModalEl) {
        feedbackModalEl.addEventListener('show.bs.modal', function () {
            const formSection = document.getElementById('feedbackFormSection');
            const successSection = document.getElementById('feedbackSuccessSection');
            if (formSection && successSection) {
                formSection.classList.remove('d-none');
                formSection.style.setProperty('display', 'flex', 'important');
                successSection.classList.add('d-none');
                successSection.style.setProperty('display', 'none', 'important');
            }
        });
    }

    // Product Stars Handler
    const productStars = document.querySelectorAll('.star-item-product');
    const inputProduct = document.getElementById('inputProductRating');
    const labelProduct = document.getElementById('labelProductRating');
    const productRatingsMap = { 1: '1 / 5 - Damaged', 2: '2 / 5 - Below Average', 3: '3 / 5 - Average', 4: '4 / 5 - Good', 5: '5 / 5 - Perfect' };

    function setProductStars(val) {
        productStars.forEach(s => {
            const r = parseInt(s.dataset.rating);
            if (r <= val) {
                s.className = 'bi bi-star-fill star-item-product fs-3 cursor-pointer text-warning';
            } else {
                s.className = 'bi bi-star star-item-product fs-3 cursor-pointer text-muted';
            }
        });
        inputProduct.value = val;
        labelProduct.innerText = productRatingsMap[val] || (val + ' / 5');
    }

    productStars.forEach(s => {
        s.addEventListener('click', () => setProductStars(parseInt(s.dataset.rating)));
        s.addEventListener('mouseenter', () => { s.style.transform = 'scale(1.2)'; });
        s.addEventListener('mouseleave', () => { s.style.transform = 'scale(1)'; });
    });
    setProductStars(parseInt(inputProduct.value) || 5);

    // Form Submit via AJAX
    const form = document.getElementById('formOrderFeedback');
    const btnSubmit = document.getElementById('btnSubmitFeedback');

    if (form) {
        form.addEventListener('submit', async function (e) {
            e.preventDefault();
            btnSubmit.disabled = true;
            btnSubmit.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Submitting...';

            try {
                const formData = new FormData(form);
                const res = await fetch(form.action, {
                    method: 'POST',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    },
                    body: formData
                });

                const data = await res.json();
                if (data.success) {
                    // Switch modal view to Thank You success screen
                    const formSection = document.getElementById('feedbackFormSection');
                    const successSection = document.getElementById('feedbackSuccessSection');
                    const successStars = document.getElementById('feedbackSuccessStars');
                    const successLabel = document.getElementById('feedbackSuccessLabel');

                    const rating = data.feedback ? data.feedback.delivery_rating : parseInt(inputDelivery.value);
                    if (successStars) {
                        successStars.innerHTML = '';
                        for (let s = 1; s <= 5; s++) {
                            const icon = document.createElement('i');
                            icon.className = s <= rating ? 'bi bi-star-fill text-warning' : 'bi bi-star text-muted';
                            successStars.appendChild(icon);
                        }
                    }
                    if (successLabel) {
                        successLabel.innerText = `Rated ${rating}/5 Stars`;
                    }

                    formSection.classList.add('d-none');
                    formSection.style.setProperty('display', 'none', 'important');
                    successSection.classList.remove('d-none');
                    successSection.style.setProperty('display', 'block', 'important');

                    // Auto-reload after 2s
                    setTimeout(() => {
                        location.reload();
                    }, 2000);
                } else {
                    alert(data.message || 'Something went wrong.');
                    btnSubmit.disabled = false;
                    btnSubmit.innerHTML = '<i class="bi bi-check-circle-fill"></i> <span>Submit Feedback</span>';
                }
            } catch (err) {
                console.error(err);
                btnSubmit.disabled = false;
                btnSubmit.innerHTML = '<i class="bi bi-check-circle-fill"></i> <span>Submit Feedback</span>';
            }
        });
    }
});

function toggleFeedbackTag(btn, tag) {
    const container = document.getElementById('hiddenTagsContainer');
    const isSelected = btn.classList.contains('btn-primary');

    if (isSelected) {
        btn.className = 'btn btn-sm btn-tag btn-light border text-dark rounded-pill px-3 py-1 fw-semibold';
        const existingInput = container.querySelector(`input[value="${tag}"]`);
        if (existingInput) existingInput.remove();
    } else {
        btn.className = 'btn btn-sm btn-tag btn-primary text-white rounded-pill px-3 py-1 fw-semibold';
        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = 'tags[]';
        input.value = tag;
        container.appendChild(input);
    }
}

function openFeedbackWithRating(star) {
    if (typeof window.setDeliveryStars === 'function') {
        window.setDeliveryStars(star);
    }
    const modalEl = document.getElementById('modalOrderFeedback');
    if (modalEl && window.bootstrap) {
        const modal = bootstrap.Modal.getOrCreateInstance(modalEl);
        modal.show();
    }
}

function handleDecisionChange(val) {
    const secSlot = document.getElementById('sectionRescheduleSlot');
    const secNote = document.getElementById('sectionNeighborNote');
    if (secSlot) secSlot.style.display = (val === 'reschedule_tomorrow') ? 'block' : 'none';
    if (secNote) secNote.style.display = (val === 'leave_security_neighbor') ? 'block' : 'none';
}

function selectSlotBtn(radio) {
    document.querySelectorAll('.slot-btn').forEach(l => l.classList.remove('active'));
    if (radio && radio.closest('.slot-btn')) {
        radio.closest('.slot-btn').classList.add('active');
    }
}
</script>
@endif

{{-- ── 15-Second Real-Time Auto-Refresh Poller (Active Delivery States) ── --}}
@if(!$isDelivered && $s !== 'cancelled')
<script>
(function() {
    let currentStatus = "{{ $order->status }}";
    let currentHasIssue = {{ $order->fulfillment?->hasActiveDeliveryIssue() ? 'true' : 'false' }};
    let currentSlot = "{{ $order->fulfillment?->delivery_slot ?: ($order->delivery_slot ?: '') }}";

    const pollInterval = setInterval(() => {
        if (document.hidden) return; // Pause when tab is in background

        fetch("{{ route('account.orders.status-poll', $order) }}", {
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(res => res.json())
        .then(data => {
            if (!data.success) return;

            const statusChanged = data.status !== currentStatus;
            const issueChanged = Boolean(data.has_active_issue) !== currentHasIssue;
            const slotChanged = Boolean(data.delivery_slot) && data.delivery_slot !== currentSlot;

            if (statusChanged || issueChanged || slotChanged) {
                clearInterval(pollInterval);
                setTimeout(() => {
                    window.location.reload();
                }, 800);
            }
        })
        .catch(() => {});
    }, 15000);
})();
</script>
@endif

@if($canCancel)
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
function cancelPendingOrderFromDetails() {
    Swal.fire({
        title: 'Cancel Order #{{ $order->order_number }}?',
        text: 'Please select a reason for cancelling this order:',
        input: 'select',
        inputOptions: {
            'Order placed by mistake': 'Order placed by mistake',
            'Found better price elsewhere': 'Found better price elsewhere',
            'Need to change payment method': 'Need to change payment method',
            'Need to change delivery address': 'Need to change delivery address',
            'Changed my mind': 'Changed my mind',
            'Other reason': 'Other reason'
        },
        inputValue: 'Order placed by mistake',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#ef4444',
        cancelButtonColor: '#64748b',
        confirmButtonText: 'Yes, Cancel Order',
        cancelButtonText: 'Keep Order'
    }).then((result) => {
        if (!result.isConfirmed) return;

        const chosenReason = result.value || 'Customer cancelled from order details';
        const btnCancel = document.getElementById('btn-order-cancel-pending');
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
                cancellation_reason: chosenReason,
                refund_method: "wallet"
            })
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                Swal.fire({
                    icon: 'success',
                    title: 'Order Cancelled!',
                    text: data.message || 'Your order has been cancelled successfully.',
                    confirmButtonColor: '#10b981'
                }).then(() => {
                    window.location.reload();
                });
            } else {
                if (btnCancel) {
                    btnCancel.disabled = false;
                    btnCancel.innerHTML = '<i class="bi bi-x-circle me-1"></i> Cancel Order';
                }
                Swal.fire('Error', data.message || 'Could not cancel order.', 'error');
            }
        })
        .catch(err => {
            console.error("Cancel error:", err);
            if (btnCancel) {
                btnCancel.disabled = false;
                btnCancel.innerHTML = '<i class="bi bi-x-circle me-1"></i> Cancel Order';
            }
            Swal.fire('Error', 'Network error while cancelling order.', 'error');
        });
    });
}
</script>
@endif

@endsection

@if(isset($recommendedProducts) && count($recommendedProducts) > 0)
@section('full_width_account_content')
{{-- Recommended Products Full Container Width --}}
@include('customer.account.components.recommended-products', ['recommendedProducts' => $recommendedProducts])
@endsection
@endif
