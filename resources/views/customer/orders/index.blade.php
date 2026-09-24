@extends('customer.account.layout')

@section('title', 'My Orders')

@section('account_content')

{{-- ── Hero ──────────────────────────────────────────────── --}}
<div class="rounded-4 mb-4 overflow-hidden position-relative"
     style="background: linear-gradient(135deg, #0f172a 0%, #1e293b 60%, #312e81 100%); min-height:120px;">
    <div class="position-absolute w-100 h-100"
         style="background-image:radial-gradient(circle,rgba(255,255,255,0.04) 1px,transparent 1px); background-size:24px 24px; top:0; left:0;"></div>
    <div class="position-relative p-4 d-flex align-items-center gap-4 flex-wrap">
        <div class="rounded-3 d-flex align-items-center justify-content-center flex-shrink-0"
             style="width:60px; height:60px; background:rgba(99,102,241,0.25); border:1.5px solid rgba(99,102,241,0.5);">
            <i class="bi bi-box-seam-fill text-white fs-3"></i>
        </div>
        <div class="flex-grow-1">
            <h5 class="fw-bold text-white mb-1">My Orders</h5>
            <p class="text-white-50 small mb-0">Track shipments, view receipts &amp; manage your purchases</p>
        </div>
        <div class="d-flex gap-3 flex-wrap">
            <div class="text-center px-3 py-2 rounded-3"
                 style="background:rgba(255,255,255,0.07); border:1px solid rgba(255,255,255,0.1);">
                <div class="fw-bold text-white fs-5 lh-1">{{ $totalCount }}</div>
                <div class="text-white-50" style="font-size:0.7rem; margin-top:2px;">Total</div>
            </div>
            <div class="text-center px-3 py-2 rounded-3"
                 style="background:rgba(16,185,129,0.15); border:1px solid rgba(16,185,129,0.3);">
                <div class="fw-bold lh-1" style="color:#6ee7b7; font-size:1.2rem;">{{ $deliveredCount }}</div>
                <div style="font-size:0.7rem; color:#6ee7b7; margin-top:2px; opacity:0.8;">Delivered</div>
            </div>
            <div class="text-center px-3 py-2 rounded-3"
                 style="background:rgba(251,191,36,0.15); border:1px solid rgba(251,191,36,0.3);">
                <div class="fw-bold lh-1" style="color:#fcd34d; font-size:1.2rem;">{{ $activeCount }}</div>
                <div style="font-size:0.7rem; color:#fcd34d; margin-top:2px; opacity:0.8;">Active</div>
            </div>
        </div>
    </div>
</div>

{{-- ── Status Tab Pills ─────────────────────────────────── --}}
<div class="mb-3 d-flex align-items-center gap-2 flex-wrap">
    @php
        $tabs = [
            ''                 => ['label'=>'All',               'icon'=>'bi-grid-3x3-gap'],
            'pending'          => ['label'=>'Pending',           'icon'=>'bi-clock'],
            'confirmed'        => ['label'=>'Confirmed',         'icon'=>'bi-check2'],
            'packed'           => ['label'=>'Packed',            'icon'=>'bi-box'],
            'shipped'          => ['label'=>'Shipped',           'icon'=>'bi-truck'],
            'out_for_delivery' => ['label'=>'Out for Delivery',  'icon'=>'bi-bicycle'],
            'delivered'        => ['label'=>'Delivered',         'icon'=>'bi-check-circle'],
            'cancelled'        => ['label'=>'Cancelled',         'icon'=>'bi-x-circle'],
        ];
        $activeTab = request('status', '');
    @endphp
    @foreach($tabs as $val => $tab)
    <a href="{{ route('account.orders.index', array_merge(request()->only('search'), $val ? ['status'=>$val] : [])) }}"
       class="btn btn-sm rounded-pill fw-semibold px-3"
       style="font-size:0.78rem; transition:all 0.2s;
              {{ $activeTab === $val
                ? 'background:linear-gradient(135deg,#6366f1,#8b5cf6); color:#fff; border:none; box-shadow:0 2px 8px rgba(99,102,241,0.35);'
                : 'background:#fff; color:#64748b; border:1px solid #e2e8f0;' }}">
        <i class="bi {{ $tab['icon'] }} me-1"></i>{{ $tab['label'] }}
    </a>
    @endforeach
</div>

{{-- ── Search Bar ───────────────────────────────────────── --}}
<div class="card border-0 shadow-sm rounded-4 mb-4" style="border:1px solid #e2e8f0 !important;">
    <div class="card-body p-3">
        <form method="GET" action="{{ route('account.orders.index') }}" class="d-flex gap-2 align-items-center">
            @if(request('status'))
                <input type="hidden" name="status" value="{{ request('status') }}">
            @endif
            <div class="input-group flex-grow-1">
                <span class="input-group-text bg-white border-end-0 text-muted"
                      style="border-radius:10px 0 0 10px; border-color:#e2e8f0;">
                    <i class="bi bi-search"></i>
                </span>
                <input type="text" name="search"
                       class="form-control border-start-0 ps-0"
                       placeholder="Search by order number…"
                       value="{{ request('search') }}"
                       style="border-radius:0 10px 10px 0; border-color:#e2e8f0; font-size:0.85rem;">
            </div>
            <button type="submit" class="btn btn-primary rounded-pill px-4 fw-semibold"
                    style="font-size:0.85rem; white-space:nowrap;">
                Search
            </button>
            @if(request('search'))
            <a href="{{ route('account.orders.index', request('status') ? ['status'=>request('status')] : []) }}"
               class="btn btn-light border rounded-pill px-3" style="font-size:0.85rem;" title="Clear">
                <i class="bi bi-x-lg"></i>
            </a>
            @endif
        </form>
    </div>
</div>

{{-- ── Orders ───────────────────────────────────────────── --}}
@if($orders->isNotEmpty())

<p class="text-muted small mb-3 px-1">
    Showing <strong class="text-dark">{{ $orders->firstItem() }}–{{ $orders->lastItem() }}</strong>
    of <strong class="text-dark">{{ $orders->total() }}</strong> order(s)
</p>

<div class="d-flex flex-column gap-3">
@foreach($orders as $order)
@php
    $s = strtolower(str_replace([' ', '-'], '_', $order->status));
    $map = [
        'delivered'        => ['bg'=>'#d1fae5','color'=>'#065f46','border'=>'#6ee7b7','stripe'=>'#10b981','icon'=>'bi-check-circle-fill','label'=>'Delivered'],
        'out_for_delivery' => ['bg'=>'#fef3c7','color'=>'#b45309','border'=>'#fcd34d','stripe'=>'#f59e0b','icon'=>'bi-bicycle','label'=>'Out for Delivery'],
        'shipped'          => ['bg'=>'#f3e8ff','color'=>'#6b21a8','border'=>'#c4b5fd','stripe'=>'#8b5cf6','icon'=>'bi-truck','label'=>'Shipped'],
        'packed'           => ['bg'=>'#dbeafe','color'=>'#1e40af','border'=>'#93c5fd','stripe'=>'#3b82f6','icon'=>'bi-box-seam-fill','label'=>'Packed'],
        'confirmed'        => ['bg'=>'#dbeafe','color'=>'#1e40af','border'=>'#93c5fd','stripe'=>'#3b82f6','icon'=>'bi-check2-square','label'=>'Confirmed'],
        'processing'       => ['bg'=>'#dbeafe','color'=>'#1e40af','border'=>'#93c5fd','stripe'=>'#3b82f6','icon'=>'bi-gear-fill','label'=>'Processing'],
        'cancelled'        => ['bg'=>'#fee2e2','color'=>'#991b1b','border'=>'#fca5a5','stripe'=>'#ef4444','icon'=>'bi-x-circle-fill','label'=>'Cancelled'],
        'pending'          => ['bg'=>'#fef3c7','color'=>'#92400e','border'=>'#fde68a','stripe'=>'#f59e0b','icon'=>'bi-clock-fill','label'=>'Pending'],
    ];
    $st = $map[$s] ?? $map['pending'];

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

    $stepIdx = $stageRankMap[$s] ?? false;

    $thumbs     = $order->items->take(3)->map(fn($i) => $i->product?->main_image)->filter();
    $extraCount = max(0, $order->items->count() - 3);
@endphp

<div class="card border-0 rounded-4 overflow-hidden order-row"
     style="box-shadow:0 1px 8px rgba(15,23,42,0.07); transition:box-shadow 0.2s,transform 0.2s; border:1.5px solid #f1f5f9 !important;">
    <div class="d-flex">
        {{-- Colored left accent --}}
        <div class="flex-shrink-0" style="width:5px; background:{{ $st['stripe'] }}; border-radius:16px 0 0 16px;"></div>

        <div class="flex-grow-1 p-4">
            {{-- Top row --}}
            <div class="d-flex align-items-start justify-content-between gap-3 flex-wrap">

                {{-- Product thumbnails + order meta --}}
                <div class="d-flex align-items-center gap-3 flex-wrap">
                    {{-- Stacked thumbs --}}
                    <div class="d-flex align-items-center" style="height:52px;">
                        @forelse($thumbs as $ti => $img)
                        <div class="rounded-3 border-2 border-white overflow-hidden flex-shrink-0"
                             style="width:50px; height:50px; margin-left:{{ $ti > 0 ? '-14px' : '0' }}; z-index:{{ 10-$ti }}; position:relative; border:2px solid white; box-shadow:0 1px 4px rgba(0,0,0,0.12);">
                            <img src="{{ asset('storage/'.$img) }}" alt="product"
                                 style="width:100%; height:100%; object-fit:cover;">
                        </div>
                        @empty
                        <div class="rounded-3 d-flex align-items-center justify-content-center flex-shrink-0"
                             style="width:50px; height:50px; background:#f1f5f9; border:2px solid #e2e8f0;">
                            <i class="bi bi-bag text-muted fs-5"></i>
                        </div>
                        @endforelse
                        @if($extraCount > 0)
                        <div class="rounded-3 d-flex align-items-center justify-content-center flex-shrink-0"
                             style="width:50px; height:50px; background:#e2e8f0; margin-left:-14px; z-index:1; position:relative; font-size:0.7rem; font-weight:700; color:#64748b; border:2px solid white;">
                            +{{ $extraCount }}
                        </div>
                        @endif
                    </div>

                    {{-- Order meta --}}
                    <div>
                        <div class="d-flex align-items-center gap-2 mb-1 flex-wrap">
                            <span class="fw-bold text-dark" style="font-size:0.9rem;">#{{ $order->order_number }}</span>
                            <span class="badge rounded-pill fw-semibold px-2.5 py-1"
                                  style="background:{{ $st['bg'] }}; color:{{ $st['color'] }}; border:1px solid {{ $st['border'] }}; font-size:0.72rem;">
                                <i class="bi {{ $st['icon'] }} me-1"></i>{{ $st['label'] ?? ucfirst($order->status) }}
                            </span>
                        </div>
                        <div class="d-flex align-items-center gap-3 flex-wrap" style="font-size:0.78rem; color:#64748b;">
                            <span><i class="bi bi-calendar3 me-1 opacity-75"></i>{{ $order->created_at ? $order->created_at->format('d M Y') : '—' }}</span>
                            <span><i class="bi bi-bag me-1 opacity-75"></i>{{ $order->items->count() }} item{{ $order->items->count() !== 1 ? 's' : '' }}</span>
                            @if($order->payment_method)
                            <span><i class="bi bi-credit-card me-1 opacity-75"></i>{{ strtoupper($order->payment_method) }}</span>
                            @endif
                        </div>
                    </div>
                </div>

                {{-- Amount + CTA --}}
                <div class="d-flex align-items-center gap-3 ms-auto flex-wrap">
                    <div class="text-end">
                        <div class="fw-bold text-dark" style="font-size:1.05rem;">₹{{ number_format($order->total_amount, 2) }}</div>
                        <div class="text-muted" style="font-size:0.72rem;">Total paid</div>
                    </div>
                    @if((\App\Models\Setting::get('allow_customer_cancellation', '1') == '1') && in_array($order->status, ['pending', 'confirmed']))
                        <button type="button" class="btn btn-sm btn-outline-danger rounded-pill px-3 py-2 fw-semibold" 
                                style="font-size:0.8rem;"
                                onclick="openCancellationModal({{ $order->id }}, '{{ $order->order_number }}')">
                            <i class="bi bi-x-circle me-1"></i> Cancel Order
                        </button>
                    @endif
                    <a href="{{ route('account.orders.show', $order) }}"
                       class="btn btn-sm fw-semibold rounded-pill px-4 py-2"
                       style="background:linear-gradient(135deg,#6366f1,#8b5cf6); color:#fff; border:none; font-size:0.8rem; white-space:nowrap; box-shadow:0 2px 8px rgba(99,102,241,0.3);">
                        View Details <i class="bi bi-arrow-right ms-1"></i>
                    </a>
                </div>

            </div>

            {{-- ── Progress Tracker ── --}}
            @if($s !== 'cancelled' && $stepIdx !== false)
            <div class="mt-4 pt-3" style="border-top:1px dashed #e2e8f0;">
                <div class="position-relative d-flex align-items-center justify-content-between" style="padding:0 2px;">
                    <div class="position-absolute" style="top:13px; left:14px; right:14px; height:3px; background:#e2e8f0; border-radius:99px; z-index:0;"></div>
                    @php $fillPct = $stepIdx > 0 ? round(($stepIdx / (count($stageList) - 1)) * 100) : 0; @endphp
                    <div class="position-absolute" style="top:13px; left:14px; width:calc({{ $fillPct }}% - 0px); max-width:calc(100% - 28px); height:3px; background:{{ $st['stripe'] }}; border-radius:99px; z-index:1; transition:width 0.5s ease;"></div>

                    @foreach($stageList as $si => $stage)
                    @php $done = $si <= $stepIdx; $current = $si === $stepIdx; @endphp
                    <div class="d-flex flex-column align-items-center position-relative flex-fill" style="z-index:2;">
                        <div class="rounded-circle d-flex align-items-center justify-content-center"
                             style="width:28px; height:28px;
                                    background:{{ $done ? $st['stripe'] : '#e2e8f0' }};
                                    border:2px solid {{ $done ? $st['stripe'] : '#e2e8f0' }};
                                    box-shadow:{{ $current ? '0 0 0 4px '.$st['bg'] : 'none' }};
                                    transition:all 0.3s;">
                            @if($done)
                                <i class="bi bi-check text-white" style="font-size:0.7rem; font-weight:900;"></i>
                            @else
                                <div style="width:7px;height:7px;border-radius:50%;background:#cbd5e1;"></div>
                            @endif
                        </div>
                        <span class="mt-1 text-center"
                              style="font-size:0.65rem; line-height:1.2; white-space:nowrap;
                                     color:{{ $done ? $st['color'] : '#94a3b8' }};
                                     font-weight:{{ $current ? '700' : ($done ? '600' : '400') }};">
                            {{ $stage['label'] }}
                        </span>
                    </div>
                    @endforeach
                </div>
            </div>

            @elseif($s === 'cancelled')
            <div class="mt-3 pt-3 d-flex align-items-center gap-2" style="border-top:1px dashed #e2e8f0;">
                <div class="rounded-pill px-3 py-1 d-inline-flex align-items-center gap-2"
                     style="background:#fee2e2; border:1px solid #fca5a5;">
                    <i class="bi bi-x-circle-fill" style="color:#dc2626; font-size:0.8rem;"></i>
                    <span style="color:#991b1b; font-size:0.78rem; font-weight:600;">Order Cancelled</span>
                </div>
            </div>
            @endif

        </div>
    </div>
</div>
@endforeach
</div>

{{-- Pagination --}}
@if($orders->hasPages())
<div class="mt-4">
    {{ $orders->links('pagination::bootstrap-5') }}
</div>
@endif

@else
    @include('customer.account.components.empty-state', [
        'icon'        => 'bi-box-seam',
        'title'       => 'No Orders Found',
        'message'     => "You haven't placed any orders matching your criteria.",
        'button_text' => 'Start Shopping',
        'button_url'  => route('shop')
    ])
@endif

@push('styles')
<style>
.order-row:hover {
    box-shadow: 0 8px 32px rgba(99,102,241,0.13) !important;
    transform: translateY(-2px);
}
</style>
@endpush

@endsection

<!-- ── Cancellation & Refund Modal ── -->
<div class="modal fade" id="cancelOrderModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
            <div class="modal-header border-bottom py-3 px-4 bg-light">
                <h6 class="modal-title fw-bold text-dark" id="modalOrderTitle">
                    <i class="bi bi-x-circle-fill text-danger me-2"></i>Cancel Order #<span id="modalOrderNumber"></span>
                </h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <div class="text-center py-4" id="cancelModalLoader">
                    <div class="spinner-border text-primary" role="status"></div>
                    <div class="small text-muted mt-2">Calculating GST fee & refund breakdown...</div>
                </div>

                <div id="cancelModalContent" style="display: none;">
                    <form id="cancelOrderForm" onsubmit="submitOrderCancellation(event)">
                        <input type="hidden" id="cancelOrderId" value="">

                        <!-- Reason Selection -->
                        <div class="mb-3">
                            <label class="form-label fw-bold text-dark small">Reason for Cancellation <span class="text-danger">*</span></label>
                            <select id="cancellationReasonSelect" class="form-select fw-semibold" required>
                                <option value="" selected disabled>Select a reason...</option>
                                <option value="Ordered by mistake">Ordered by mistake</option>
                                <option value="Changed my mind">Changed my mind</option>
                                <option value="Found lower price elsewhere">Found lower price elsewhere</option>
                                <option value="Delivery time is too long">Delivery time is too long</option>
                                <option value="Incorrect shipping address">Incorrect shipping address</option>
                                <option value="Other">Other</option>
                            </select>
                        </div>

                        <!-- Financial Summary Box -->
                        <div class="card border-0 bg-light rounded-3 p-3 mb-3" style="border: 1px solid #cbd5e1 !important;">
                            <div class="d-flex justify-content-between small mb-1">
                                <span class="text-secondary">Order Amount:</span>
                                <span class="fw-bold text-dark font-monospace" id="summaryTotalAmount">₹0.00</span>
                            </div>
                            <div class="d-flex justify-content-between small mb-1 text-danger">
                                <span>Non-Refundable GST Tax Fee:</span>
                                <span class="fw-bold font-monospace" id="summaryGstFee">-₹0.00</span>
                            </div>
                            <hr class="my-2">
                            <div class="d-flex justify-content-between fw-bold" id="refundOrPayableRow">
                                <span id="refundOrPayableLabel" class="text-success">Net Refund Amount:</span>
                                <span id="refundOrPayableAmount" class="text-success font-monospace fs-6">₹0.00</span>
                            </div>
                        </div>

                        <!-- Refund Destination Choice (For Prepaid) -->
                        <div id="prepaidRefundOptionsSection" class="mb-3" style="display: none;">
                            <label class="form-label fw-bold text-dark small">Select Refund Destination <span class="text-danger">*</span></label>
                            <div class="form-check border rounded-3 p-2.5 mb-2 bg-white shadow-xs">
                                <input class="form-check-input" type="radio" name="refund_method" id="refund_method_wallet" value="wallet" checked onchange="toggleUpiInput()">
                                <label class="form-check-label w-100" for="refund_method_wallet">
                                    <div class="fw-bold text-dark small">⚡ ShopCalm Store Wallet <span class="badge bg-success rounded-pill ms-1" style="font-size: 0.65rem;">INSTANT 1-SEC</span></div>
                                    <div class="text-muted" style="font-size: 0.72rem;">Refund credited immediately for your next purchase.</div>
                                </label>
                            </div>
                            <div class="form-check border rounded-3 p-2.5 bg-white shadow-xs">
                                <input class="form-check-input" type="radio" name="refund_method" id="refund_method_bank" value="bank_upi" onchange="toggleUpiInput()">
                                <label class="form-check-label w-100" for="refund_method_bank">
                                    <div class="fw-bold text-dark small">🏦 Original Bank UPI / Account</div>
                                    <div class="text-muted" style="font-size: 0.72rem;">Processed back to your UPI VPA handle.</div>
                                </label>
                            </div>

                            <div class="mt-2.5" id="upiIdInputGroup" style="display: none;">
                                <label class="form-label fw-bold text-dark small">Enter Your UPI VPA Handle (e.g. mobile@paytm / user@ybl)</label>
                                <input type="text" id="refundUpiIdInput" class="form-control form-control-sm font-monospace fw-bold" placeholder="username@upi">
                            </div>
                        </div>

                        <!-- COD GST Fee Notice -->
                        <div id="codGstFeeNoticeSection" class="alert alert-warning border-0 rounded-3 p-3 mb-3 shadow-xs" style="display: none; background: #fffbeb; border: 1.5px solid #fde68a !important;">
                            <div class="fw-bold text-dark small mb-1"><i class="bi bi-exclamation-triangle-fill text-warning me-1"></i> Cash on Delivery Cancellation Fee</div>
                            <div class="text-secondary small" style="font-size: 0.78rem; line-height: 1.35;">
                                To cancel this confirmed COD order, please complete online payment of the non-refundable GST fee (<strong id="codGstFeeText">₹0.00</strong>).
                            </div>
                        </div>

                        <div class="d-flex gap-2 justify-content-end mt-4 pt-2 border-top">
                            <button type="button" class="btn btn-sm btn-light border rounded-pill px-4" data-bs-dismiss="modal">Keep Order</button>
                            <button type="submit" class="btn btn-sm btn-danger rounded-pill px-4 fw-bold" id="confirmCancelBtn">
                                Confirm Cancellation
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<!-- SweetAlert2 JS CDN -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<!-- Cashfree DropJS SDK v3 -->
<script src="https://sdk.cashfree.com/js/v3/cashfree.js"></script>
<script>
let currentCancelOrderData = null;

function showAlert(icon, title, message, callback) {
    if (typeof Swal !== 'undefined') {
        Swal.fire({
            icon: icon,
            title: title,
            text: message,
            confirmButtonText: 'OK'
        }).then(() => {
            if (callback) callback();
        });
    } else {
        alert(title + "\n\n" + message);
        if (callback) callback();
    }
}

function openCancellationModal(orderId, orderNumber) {
    document.getElementById('cancelOrderId').value = orderId;
    document.getElementById('modalOrderNumber').innerText = orderNumber;
    document.getElementById('cancelModalLoader').style.display = 'block';
    document.getElementById('cancelModalContent').style.display = 'none';

    const modal = new bootstrap.Modal(document.getElementById('cancelOrderModal'));
    modal.show();

    fetch(`/account/orders/${orderId}/cancellation-summary`, {
        headers: {
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
        .then(async res => {
            const data = await res.json();
            if (!res.ok) {
                throw new Error(data.message || 'Server returned status ' + res.status);
            }
            return data;
        })
        .then(data => {
            if (data.success) {
                currentCancelOrderData = data;
                document.getElementById('cancelModalLoader').style.display = 'none';
                document.getElementById('cancelModalContent').style.display = 'block';

                document.getElementById('summaryTotalAmount').innerText = '₹' + data.summary.total_amount.toFixed(2);
                document.getElementById('summaryGstFee').innerText = '-₹' + data.summary.cancellation_fee.toFixed(2);

                if (data.is_cod && !data.is_paid) {
                    // COD Order
                    document.getElementById('prepaidRefundOptionsSection').style.display = 'none';
                    document.getElementById('codGstFeeNoticeSection').style.display = 'block';
                    document.getElementById('codGstFeeText').innerText = '₹' + data.summary.cancellation_fee.toFixed(2);
                    document.getElementById('refundOrPayableLabel').innerText = 'GST Fee Payable Online:';
                    document.getElementById('refundOrPayableLabel').className = 'text-danger';
                    document.getElementById('refundOrPayableAmount').innerText = '₹' + data.summary.cancellation_fee.toFixed(2);
                    document.getElementById('refundOrPayableAmount').className = 'text-danger font-monospace fs-6';
                    document.getElementById('confirmCancelBtn').innerText = 'Pay GST Fee & Cancel Order';
                } else {
                    // Prepaid / Wallet / Paid COD Order
                    document.getElementById('prepaidRefundOptionsSection').style.display = 'block';
                    document.getElementById('codGstFeeNoticeSection').style.display = 'none';
                    document.getElementById('refundOrPayableLabel').innerText = 'Net Refund Amount:';
                    document.getElementById('refundOrPayableLabel').className = 'text-success';
                    document.getElementById('refundOrPayableAmount').innerText = '₹' + data.summary.net_refund.toFixed(2);
                    document.getElementById('refundOrPayableAmount').className = 'text-success font-monospace fs-6';
                    document.getElementById('confirmCancelBtn').innerText = 'Confirm Cancellation';
                }
            } else {
                showAlert('error', 'Error', data.message || 'Error fetching order details.');
            }
        })
        .catch(err => {
            console.error('Cancellation Summary Fetch Error:', err);
            showAlert('error', 'Error', err.message || 'Failed to load order cancellation summary.');
        });
}

function toggleUpiInput() {
    const bankRadio = document.getElementById('refund_method_bank');
    document.getElementById('upiIdInputGroup').style.display = bankRadio.checked ? 'block' : 'none';
}

function submitOrderCancellation(event) {
    event.preventDefault();
    const orderId = document.getElementById('cancelOrderId').value;
    const reason = document.getElementById('cancellationReasonSelect').value;
    const refundMethod = document.querySelector('input[name="refund_method"]:checked')?.value || 'wallet';
    const upiId = document.getElementById('refundUpiIdInput').value;

    if (!reason) {
        showAlert('warning', 'Cancellation Reason Required', 'Please select a cancellation reason.');
        return;
    }

    if (refundMethod === 'bank_upi' && !upiId.trim() && document.getElementById('prepaidRefundOptionsSection').style.display !== 'none') {
        showAlert('warning', 'UPI Handle Required', 'Please enter your UPI VPA handle for bank refund.');
        return;
    }

    const btn = document.getElementById('confirmCancelBtn');
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Processing...';

    const payload = {
        cancellation_reason: reason,
        refund_method: refundMethod,
        refund_upi_id: upiId
    };

    fetch(`/account/orders/${orderId}/cancel`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: JSON.stringify(payload)
    })
    .then(async res => {
        const data = await res.json();
        if (!res.ok) {
            throw new Error(data.message || 'Server error ' + res.status);
        }
        return data;
    })
    .then(data => {
        if (data.cashfree_checkout && data.payment_session_id) {
            // Launch Cashfree Payment SDK Modal for GST Fee payment
            const cashfree = Cashfree({
                mode: "{{ config('services.cashfree.environment', 'TEST') === 'PRODUCTION' ? 'production' : 'sandbox' }}"
            });
            cashfree.checkout({
                paymentSessionId: data.payment_session_id,
                redirectTarget: "_self"
            });
            return;
        }

        if (data.success) {
            showAlert('success', 'Order Cancelled', data.message, () => {
                location.reload();
            });
        } else {
            showAlert('error', 'Cancellation Failed', data.message || 'Cancellation failed.');
            btn.disabled = false;
            btn.innerText = 'Confirm Cancellation';
        }
    })
    .catch(err => {
        console.error(err);
        showAlert('error', 'Cancellation Error', err.message || 'Error processing cancellation.');
        btn.disabled = false;
        btn.innerText = 'Confirm Cancellation';
    });
}
</script>
@endpush
