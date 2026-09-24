@extends('delivery.layouts.app')

@section('title', 'Rider Run-Sheet')

@section('content')

<!-- ── 1. Rider Shift Hero Banner ── -->
<div class="card border-0 rounded-4 mb-3 text-white overflow-hidden shadow-sm" 
     style="background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%); border: 1px solid rgba(255, 255, 255, 0.08) !important; padding: 1.35rem 1.4rem;">
    <!-- Top Row: Zone & Shift Status Badge -->
    <div class="d-flex align-items-center justify-content-between mb-2.5">
        <div class="fw-bold text-uppercase d-flex align-items-center gap-1" style="font-size: 0.72rem; color: #38bdf8; letter-spacing: 0.06em;">
            <i class="bi bi-geo-alt-fill text-info"></i> BENGALURU FLEET ZONE
        </div>
        <span class="badge rounded-pill px-3 py-1.5 fw-bold shadow-xs" 
              style="background: rgba(16, 185, 129, 0.15); color: #34d399; border: 1px solid rgba(52, 211, 153, 0.35); font-size: 0.7rem; letter-spacing: 0.04em;">
            ● SHIFT ACTIVE
        </span>
    </div>

    <!-- Middle: Rider Name Greeting -->
    <h4 class="fw-bolder mb-1.5 text-white" style="letter-spacing: -0.02em; font-size: 1.3rem;">
        Hello, {{ explode(' ', $rider->name ?? 'Partner')[0] }}! 🛵
    </h4>

    <!-- Bottom: Orders Status Line -->
    <div class="d-flex align-items-center gap-1.5" style="color: #cbd5e1; font-size: 0.8rem;">
        <i class="bi bi-box-seam text-info"></i>
        <span>{{ $readyOrders->count() > 0 ? $readyOrders->count() . ' orders ready for delivery' : 'All deliveries completed for today' }}</span>
    </div>
</div>

<!-- ── 2. Three Metric KPI Cards ── -->
<div class="row g-2.5 mb-3">
    <div class="col-4">
        <div class="card border-0 rounded-4 p-3 bg-white text-center shadow-xs" style="border: 1px solid #e2e8f0 !important;">
            <div class="text-secondary small fw-bold text-uppercase mb-1" style="font-size: 0.62rem; letter-spacing: 0.04em;">To Deliver</div>
            <div class="h4 fw-bolder text-primary mb-0 font-monospace" style="font-size: 1.3rem;">{{ $readyOrders->count() }}</div>
        </div>
    </div>
    <div class="col-4">
        <div class="card border-0 rounded-4 p-3 bg-white text-center shadow-xs" style="border: 1px solid #e2e8f0 !important;">
            <div class="text-secondary small fw-bold text-uppercase mb-1" style="font-size: 0.62rem; letter-spacing: 0.04em;">Delivered</div>
            <div class="h4 fw-bolder text-success mb-0 font-monospace" style="font-size: 1.3rem;">{{ $deliveredTodayCount }}</div>
        </div>
    </div>
    <div class="col-4">
        <div class="card border-0 rounded-4 p-3 bg-white text-center shadow-xs" style="border: 1px solid #e2e8f0 !important;">
            <div class="text-secondary small fw-bold text-uppercase mb-1" style="font-size: 0.62rem; letter-spacing: 0.04em;">Cash in Hand</div>
            <div class="h5 fw-bolder {{ $cashInHandPending > 0 ? 'text-danger' : 'text-success' }} mb-0 font-monospace" style="font-size: 0.96rem;">
                ₹{{ number_format($cashInHandPending, 0) }}
            </div>
        </div>
    </div>
</div>

@if($cashInHandPending > 0)
    <!-- Floating Cash Alert Box -->
    <div class="p-3 rounded-4 bg-white border mb-3 shadow-xs d-flex align-items-center justify-content-between" style="border-left: 4px solid #10b981 !important; border-color: #e2e8f0;">
        <div class="d-flex align-items-center gap-2.5">
            <div class="rounded-circle d-flex align-items-center justify-content-center text-success flex-shrink-0" style="width: 34px; height: 34px; background: #ecfdf5; font-size: 1rem;">
                <i class="bi bi-wallet2"></i>
            </div>
            <div>
                <div class="fw-bold text-dark" style="font-size: 0.82rem;">💵 ₹{{ number_format($cashInHandPending, 2) }} Cash Collected</div>
                <div class="text-secondary small" style="font-size: 0.7rem;">Deposit at hub counter at shift end</div>
            </div>
        </div>
        <a href="{{ route('delivery.settlements') }}" class="btn btn-sm btn-outline-dark rounded-pill px-3 py-1 fw-bold" style="font-size: 0.72rem;">
            Deposit &rarr;
        </a>
    </div>
@endif

<!-- ── 3. Queue Tabs (Ready vs On Hold) ── -->
<div class="p-1 rounded-pill mb-3 d-flex border shadow-xs" style="background: #e2e8f0;">
    <button type="button" id="tabBtnReady" class="btn btn-sm flex-fill rounded-pill fw-bold py-2 d-flex align-items-center justify-content-center gap-1.5 shadow-xs"
            style="background: #0f172a; color: #ffffff; font-size: 0.82rem;" onclick="switchRiderTab('ready')">
        <i class="bi bi-bicycle"></i>
        <span>Ready to Deliver</span>
        <span class="badge rounded-pill bg-primary text-white ms-1" style="font-size: 0.68rem;">{{ $readyOrders->count() }}</span>
    </button>
    <button type="button" id="tabBtnOnHold" class="btn btn-sm flex-fill rounded-pill fw-bold py-2 d-flex align-items-center justify-content-center gap-1.5 text-secondary"
            style="background: transparent; font-size: 0.82rem;" onclick="switchRiderTab('onhold')">
        <i class="bi bi-exclamation-triangle-fill text-warning"></i>
        <span>On Hold / Issues</span>
        @if($onHoldOrders->count() > 0)
            <span class="badge rounded-pill bg-warning text-dark ms-1 font-monospace" style="font-size: 0.68rem;">{{ $onHoldOrders->count() }}</span>
        @else
            <span class="badge rounded-pill bg-white text-secondary border ms-1" style="font-size: 0.68rem;">0</span>
        @endif
    </button>
</div>

<!-- ═══════════ TAB 1: READY TO DELIVER (Active Route) ═══════════ -->
<div id="tabContentReady">
    <div class="d-flex align-items-center justify-content-between mb-2.5 px-1">
        <div class="fw-bold text-dark small text-uppercase" style="letter-spacing: 0.04em; font-size: 0.74rem;">
            Active Deliveries ({{ $readyOrders->count() }})
        </div>
        <span class="text-muted small" style="font-size: 0.7rem;">Tap Deliver to complete</span>
    </div>

    @forelse($readyOrders as $order)
        <div class="card border-0 rounded-4 mb-3 bg-white shadow-xs overflow-hidden" style="border: 1px solid #e2e8f0 !important;">
            <!-- Top Header: Order Number & Payment Status Badge -->
            <div class="p-3 border-bottom d-flex align-items-center justify-content-between bg-light bg-opacity-60">
                <div class="d-flex align-items-center gap-2 overflow-hidden">
                    <span class="fw-bolder text-dark font-monospace text-truncate" style="font-size: 0.9rem;">#{{ $order->order_number }}</span>
                    <span class="badge bg-white text-secondary border rounded-pill px-2.5 py-1 flex-shrink-0" style="font-size: 0.66rem;">
                        {{ $order->delivery_slot ?? 'Express' }}
                    </span>
                </div>

                <div class="flex-shrink-0 ms-2">
                    @if($order->payment_status === 'paid')
                        <span class="badge rounded-pill px-3 py-1.5 fw-bold text-white shadow-xs" 
                              style="background: #10b981; font-size: 0.7rem; letter-spacing: 0.02em;">
                            ✓ PAID
                        </span>
                    @elseif($order->payment_method === 'cod')
                        <span class="badge rounded-pill px-3 py-1.5 fw-bold font-monospace shadow-xs" 
                              style="background: #fffbeb; color: #b45309; border: 1.5px solid #fde68a; font-size: 0.72rem;">
                            💵 COD CASH
                        </span>
                    @else
                        <span class="badge rounded-pill px-3 py-1.5 fw-bold text-white shadow-xs" 
                              style="background: #10b981; font-size: 0.7rem;">
                            ✓ PAID
                        </span>
                    @endif
                </div>
            </div>

            <!-- Customer & Delivery Content -->
            <div class="p-3.5">
                <!-- Customer Details -->
                <div class="d-flex align-items-start gap-3 mb-3">
                    <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0 text-white fw-bold shadow-xs" 
                         style="width: 40px; height: 40px; background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%); font-size: 1rem;">
                        {{ strtoupper(substr($order->shipping_name, 0, 1)) }}
                    </div>
                    <div class="flex-grow-1 min-w-0">
                        <h6 class="fw-bolder text-dark mb-1 text-truncate" style="font-size: 0.96rem;">{{ $order->shipping_name }}</h6>
                        <div class="text-secondary small" style="line-height: 1.4; font-size: 0.82rem;">
                            <i class="bi bi-geo-alt-fill text-danger me-1"></i>{{ $order->shipping_address }}, {{ $order->shipping_city }} - <strong>{{ $order->shipping_zip }}</strong>
                        </div>
                    </div>
                </div>

                <!-- Parcel Contents & Selected Options -->
                @if($order->items && $order->items->isNotEmpty())
                    <div class="mb-3 p-2.5 rounded-3 bg-light border shadow-xs">
                        <div class="text-secondary small fw-bold text-uppercase mb-1" style="font-size: 0.65rem; letter-spacing: 0.03em;">📦 Parcel Items:</div>
                        @foreach($order->items as $item)
                            <div class="d-flex align-items-center justify-content-between text-dark small py-0.5" style="font-size: 0.8rem;">
                                <span class="fw-semibold text-truncate me-2">• {{ $item->product_name }} <span class="text-secondary">x{{ $item->quantity }}</span></span>
                                @if($item->selected_option)
                                    <span class="badge rounded-pill bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25 font-monospace fw-bold px-2 py-0.5 flex-shrink-0" style="font-size: 0.68rem;">
                                        {{ $item->selected_option }}
                                    </span>
                                @endif
                            </div>
                        @endforeach
                    </div>
                @endif

                <!-- Dedicated Order Total & Cash Collection Requirement Bar -->
                <div class="p-2.5 px-3 rounded-3 mb-3 d-flex align-items-center justify-content-between border shadow-xs" 
                     style="background: {{ $order->payment_status === 'paid' ? '#f0fdf4; border-color: #bbf7d0 !important;' : '#fffbeb; border-color: #fde68a !important;' }}">
                    <div>
                        <div class="text-secondary small fw-bold text-uppercase mb-0.5" style="font-size: 0.64rem; letter-spacing: 0.03em;">
                            {{ $order->payment_status === 'paid' ? 'Payment Status' : 'Doorstep Requirement' }}
                        </div>
                        <div class="fw-bolder {{ $order->payment_status === 'paid' ? 'text-success' : 'text-danger' }}" style="font-size: 0.84rem;">
                            {{ $order->payment_status === 'paid' ? '✓ Paid Online (Collect ₹0)' : '💵 Collect Cash from Customer' }}
                        </div>
                    </div>
                    <div class="text-end">
                        <div class="text-secondary small fw-bold text-uppercase mb-0.5" style="font-size: 0.64rem;">Order Total</div>
                        <div class="fw-bolder text-dark mb-0 font-monospace" style="font-size: 1.1rem;">
                            ₹{{ number_format($order->total_amount, 2) }}
                        </div>
                    </div>
                </div>

                @if(!empty($order->notes))
                    <!-- Customer Special Instructions -->
                    <div class="p-2.5 px-3 rounded-3 border mb-3 d-flex align-items-start gap-2 shadow-xs" style="background: #fff7ed; border-color: #fdba74 !important; font-size: 0.78rem;">
                        <i class="bi bi-sticky-fill text-warning flex-shrink-0 mt-0.5"></i>
                        <div class="fw-bold text-dark" style="line-height: 1.35;">Note: "{{ $order->notes }}"</div>
                    </div>
                @endif

                <!-- 3 Large Action Buttons (Maps, Call, Deliver CTA) -->
                <div class="row g-2.5 pt-2.5 border-top">
                    <div class="col-4">
                        <a href="https://www.google.com/maps/dir/?api=1&destination={{ urlencode($order->shipping_address . ', ' . $order->shipping_city . ' ' . $order->shipping_zip) }}" 
                           target="_blank" class="btn btn-sm btn-rider-action btn-action-nav w-100 shadow-xs" style="height: 44px; font-size: 0.82rem;">
                            <i class="bi bi-map-fill"></i> Maps
                        </a>
                    </div>
                    <div class="col-4">
                        <a href="tel:{{ $order->shipping_phone }}" class="btn btn-sm btn-rider-action btn-action-phone w-100 shadow-xs" style="height: 44px; font-size: 0.82rem;">
                            <i class="bi bi-telephone-fill"></i> Call
                        </a>
                    </div>
                    <div class="col-4">
                        <a href="{{ route('delivery.orders.show', $order) }}" class="btn btn-sm w-100 fw-bold rounded-3 d-flex align-items-center justify-content-center gap-1 shadow-xs text-white" 
                           style="background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%); border: none; height: 44px; font-size: 0.86rem;">
                            <span>Deliver</span> <i class="bi bi-arrow-right"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    @empty
        <div class="card border-0 rounded-4 p-5 bg-white text-center my-3 shadow-xs" style="border: 1px solid #e2e8f0 !important;">
            <div class="rounded-circle d-inline-flex align-items-center justify-content-center text-success mb-2" style="width: 52px; height: 52px; background: #ecfdf5; font-size: 1.6rem;">
                <i class="bi bi-check-circle-fill"></i>
            </div>
            <h5 class="fw-bolder text-dark mb-1">Queue is Empty</h5>
            <p class="text-secondary small mb-0" style="font-size: 0.8rem;">All assigned active deliveries are completed.</p>
        </div>
    @endforelse
</div>

<!-- ═══════════ TAB 2: ON HOLD / REPORTED ISSUES (Warehouse Review) ═══════════ -->
<div id="tabContentOnHold" style="display: none;">
    <div class="alert alert-warning border-0 rounded-4 p-3 mb-3 d-flex align-items-start gap-2.5 shadow-xs" style="background: #fffbeb; border: 1.5px solid #fde68a !important;">
        <i class="bi bi-info-circle-fill text-warning fs-5 flex-shrink-0"></i>
        <div class="small text-dark" style="font-size: 0.78rem; line-height: 1.4;">
            These parcels have reported issues. The warehouse is contacting the customer. If customer arrives or calls, tap <strong>Deliver</strong>.
        </div>
    </div>

    @forelse($onHoldOrders as $order)
        <div class="card border-0 rounded-4 mb-3 bg-white shadow-xs overflow-hidden" style="border: 1.5px solid #fde68a !important; border-left: 5px solid #f59e0b !important;">
            <!-- Header -->
            <div class="p-3 border-bottom d-flex align-items-center justify-content-between bg-warning bg-opacity-10">
                <div class="d-flex align-items-center gap-2 overflow-hidden">
                    <span class="fw-bolder text-dark font-monospace text-truncate" style="font-size: 0.9rem;">#{{ $order->order_number }}</span>
                    <span class="badge bg-danger rounded-pill px-2.5 py-1 fw-bold flex-shrink-0" style="font-size: 0.66rem;">
                        <i class="bi bi-exclamation-octagon-fill me-1"></i> Issue Logged
                    </span>
                </div>

                <div class="flex-shrink-0 ms-2">
                    @if($order->payment_method === 'cod' && $order->payment_status !== 'paid')
                        <span class="badge rounded-pill px-3 py-1.5 fw-bold font-monospace shadow-xs" 
                              style="background: #fffbeb; color: #b45309; border: 1.5px solid #fde68a; font-size: 0.7rem;">
                            💵 COD Cash
                        </span>
                    @else
                        <span class="badge rounded-pill px-3 py-1.5 fw-bold text-white shadow-xs" 
                              style="background: #10b981; font-size: 0.7rem;">
                            ✓ PAID
                        </span>
                    @endif
                </div>
            </div>

            <!-- Content Area -->
            <div class="p-3.5">
                <div class="p-2.5 rounded-3 bg-danger bg-opacity-10 border border-danger border-opacity-25 mb-3 d-flex align-items-center justify-content-between" style="font-size: 0.78rem;">
                    <span class="text-danger fw-bold text-truncate" style="max-width: 250px;">
                        <i class="bi bi-megaphone-fill me-1"></i> {{ $order->fulfillment?->delivery_issue ?? 'Delivery Issue Logged' }}
                    </span>
                    <span class="badge bg-white text-danger border rounded-pill px-2.5 py-1" style="font-size: 0.64rem;">
                        {{ $order->fulfillment?->delivery_issue_at?->diffForHumans() ?? 'Recently' }}
                    </span>
                </div>

                <div class="d-flex align-items-start gap-3 mb-3">
                    <div class="flex-grow-1 min-w-0">
                        <h6 class="fw-bolder text-dark mb-1" style="font-size: 0.96rem;">{{ $order->shipping_name }}</h6>
                        <div class="text-secondary small" style="line-height: 1.4; font-size: 0.82rem;">
                            <i class="bi bi-geo-alt-fill text-danger me-1"></i>{{ $order->shipping_address }}, {{ $order->shipping_city }} - <strong>{{ $order->shipping_zip }}</strong>
                        </div>
                    </div>
                </div>

                <!-- Price & Collection Requirement Strip -->
                <div class="p-2.5 px-3 rounded-3 mb-3 d-flex align-items-center justify-content-between border shadow-xs" 
                     style="background: {{ $order->payment_status === 'paid' ? '#f0fdf4; border-color: #bbf7d0 !important;' : '#fffbeb; border-color: #fde68a !important;' }}">
                    <div>
                        <div class="text-secondary small fw-bold text-uppercase mb-0.5" style="font-size: 0.64rem;">
                            {{ $order->payment_status === 'paid' ? 'Payment Status' : 'Doorstep Requirement' }}
                        </div>
                        <div class="fw-bolder {{ $order->payment_status === 'paid' ? 'text-success' : 'text-danger' }}" style="font-size: 0.84rem;">
                            {{ $order->payment_status === 'paid' ? '✓ Paid Online (Collect ₹0)' : '💵 Collect Cash from Customer' }}
                        </div>
                    </div>
                    <div class="text-end">
                        <div class="text-secondary small fw-bold text-uppercase mb-0.5" style="font-size: 0.64rem;">Order Total</div>
                        <div class="fw-bolder text-dark mb-0 font-monospace" style="font-size: 1.1rem;">
                            ₹{{ number_format($order->total_amount, 2) }}
                        </div>
                    </div>
                </div>

                <div class="row g-2.5 pt-2.5 border-top">
                    <div class="col-6">
                        <a href="tel:{{ $order->shipping_phone }}" class="btn btn-sm btn-rider-action btn-action-phone w-100 shadow-xs" style="height: 44px; font-size: 0.82rem;">
                            <i class="bi bi-telephone-fill"></i> Call Customer
                        </a>
                    </div>
                    <div class="col-6">
                        <a href="{{ route('delivery.orders.show', $order) }}" class="btn btn-sm btn-warning text-dark w-100 fw-bold rounded-3 d-flex align-items-center justify-content-center gap-1 shadow-xs" 
                           style="height: 44px; font-size: 0.86rem;">
                            <span>Re-Attempt</span> <i class="bi bi-arrow-right"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    @empty
        <div class="card border-0 rounded-4 p-5 bg-white text-center my-3 shadow-xs" style="border: 1px solid #e2e8f0 !important;">
            <div class="rounded-circle d-inline-flex align-items-center justify-content-center text-success mb-2" style="width: 52px; height: 52px; background: #ecfdf5; font-size: 1.6rem;">
                <i class="bi bi-emoji-smile-fill"></i>
            </div>
            <h5 class="fw-bolder text-dark mb-1">No Orders on Hold</h5>
            <p class="text-secondary small mb-0" style="font-size: 0.8rem;">Great job! None of your deliveries have reported issues.</p>
        </div>
    @endforelse
</div>

<!-- Tab Switching Script -->
<script>
function switchRiderTab(tab) {
    const readyContent = document.getElementById('tabContentReady');
    const onHoldContent = document.getElementById('tabContentOnHold');
    const btnReady = document.getElementById('tabBtnReady');
    const btnOnHold = document.getElementById('tabBtnOnHold');

    if (tab === 'ready') {
        readyContent.style.display = 'block';
        onHoldContent.style.display = 'none';
        btnReady.style.background = '#0f172a';
        btnReady.style.color = '#ffffff';
        btnReady.classList.add('shadow-xs');
        btnOnHold.style.background = 'transparent';
        btnOnHold.style.color = '#64748b';
        btnOnHold.classList.remove('shadow-xs');
    } else {
        readyContent.style.display = 'none';
        onHoldContent.style.display = 'block';
        btnOnHold.style.background = '#0f172a';
        btnOnHold.style.color = '#ffffff';
        btnOnHold.classList.add('shadow-xs');
        btnReady.style.background = 'transparent';
        btnReady.style.color = '#64748b';
        btnReady.classList.remove('shadow-xs');
    }
}
</script>

@endsection
