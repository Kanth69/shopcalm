@extends('delivery.layouts.app')

@section('title', 'Rider Run-Sheet')

@section('content')

@php
    $totalAssigned = $activeOrders->count();
    $totalToday = $deliveredTodayCount + $totalAssigned;
    $progressPercent = $totalToday > 0 ? round(($deliveredTodayCount / $totalToday) * 100) : 0;
@endphp

<!-- ── 1. Amazon-Style Shift Manifest Header ── -->
<div class="d-flex align-items-center justify-content-between mb-3">
    <div>
        <h4 class="fw-bolder text-dark mb-0.5" style="letter-spacing: -0.02em; font-size: 1.25rem;">
            Delivery Manifest 🛵
        </h4>
        <div class="text-secondary small fw-medium" style="font-size: 0.78rem;">
            <i class="bi bi-geo-alt-fill text-danger me-0.5"></i> Bengaluru Fleet Hub • Planned Route
        </div>
    </div>
    <button type="button" onclick="window.location.reload()" class="btn btn-sm btn-white bg-white border rounded-pill px-3 py-1.5 fw-semibold d-flex align-items-center gap-1.5 shadow-xs text-dark" style="font-size: 0.76rem;">
        <i class="bi bi-arrow-clockwise text-primary"></i> Refresh
    </button>
</div>

<!-- ── 2. Route Progress & Shift Metrics Card (Amazon Flex Style) ── -->
<div class="card border-0 rounded-4 bg-white shadow-xs mb-3.5 overflow-hidden" style="border: 1.5px solid #e2e8f0 !important;">
    <div class="p-3">
        <!-- Progress Bar Header -->
        <div class="d-flex align-items-center justify-content-between mb-2">
            <span class="text-secondary small fw-bold text-uppercase" style="font-size: 0.68rem; letter-spacing: 0.04em;">
                Run Progress: {{ $deliveredTodayCount }} of {{ $totalToday }} Delivered
            </span>
            <span class="badge bg-primary bg-opacity-10 text-primary fw-bold font-monospace" style="font-size: 0.72rem;">
                {{ $progressPercent }}% Complete
            </span>
        </div>

        <!-- Visual Progress Bar -->
        <div class="progress rounded-pill mb-3" style="height: 7px; background: #e2e8f0;">
            <div class="progress-bar rounded-pill" role="progressbar" 
                 style="width: {{ $progressPercent }}%; background: linear-gradient(90deg, #4f46e5, #10b981);" 
                 aria-valuenow="{{ $progressPercent }}" aria-valuemin="0" aria-valuemax="100"></div>
        </div>

        <!-- 3-Segment Metric Strip -->
        <div class="d-flex align-items-center text-center pt-2 border-top">
            <!-- Remaining To Deliver -->
            <div class="flex-fill border-end px-1">
                <span class="text-secondary d-block fw-bold text-uppercase mb-0.5" style="font-size: 0.62rem; letter-spacing: 0.03em;">To Deliver</span>
                <span class="fw-bolder text-primary fs-5 font-monospace lh-1">{{ $readyOrders->count() }}</span>
            </div>

            <!-- Completed Today -->
            <div class="flex-fill border-end px-1">
                <span class="text-secondary d-block fw-bold text-uppercase mb-0.5" style="font-size: 0.62rem; letter-spacing: 0.03em;">Delivered</span>
                <span class="fw-bolder text-success fs-5 font-monospace lh-1">{{ $deliveredTodayCount }}</span>
            </div>

            <!-- Cash In Hand -->
            <div class="flex-fill px-1">
                <span class="text-secondary d-block fw-bold text-uppercase mb-0.5" style="font-size: 0.62rem; letter-spacing: 0.03em;">Cash In Pocket</span>
                <span class="fw-bolder {{ $cashInHandPending > 0 ? 'text-danger' : 'text-dark' }} fs-6 font-monospace lh-1">
                    ₹{{ number_format($cashInHandPending, 0) }}
                </span>
            </div>
        </div>
    </div>
</div>

@if($cashInHandPending > 0)
    <!-- Floating Cash Alert Banner -->
    <div class="p-3 rounded-4 bg-white border mb-3.5 shadow-xs d-flex align-items-center justify-content-between" style="border-left: 4.5px solid #f59e0b !important; border-color: #e2e8f0;">
        <div class="d-flex align-items-center gap-2.5 min-w-0">
            <i class="bi bi-wallet2 text-warning fs-4"></i>
            <div class="min-w-0">
                <div class="fw-bold text-dark text-truncate" style="font-size: 0.86rem;">₹{{ number_format($cashInHandPending, 2) }} Cash Collected</div>
                <div class="text-secondary small text-truncate" style="font-size: 0.72rem;">Deposit at hub desk at shift end</div>
            </div>
        </div>
        <a href="{{ route('delivery.settlements') }}" class="btn btn-sm btn-dark rounded-pill px-3 py-1 fw-bold flex-shrink-0 ms-2" style="font-size: 0.74rem;">
            Deposit &rarr;
        </a>
    </div>
@endif

<!-- ── 3. Queue Tabs (Active Deliveries vs On Hold) ── -->
<div class="d-flex gap-2 mb-3">
    <button type="button" id="tabBtnReady" class="btn btn-sm flex-fill rounded-pill fw-bold py-2 shadow-xs d-flex align-items-center justify-content-center gap-1.5"
            style="background: #0f172a; color: #ffffff; font-size: 0.82rem;" onclick="switchRiderTab('ready')">
        <i class="bi bi-bicycle"></i>
        <span>Active Stops ({{ $readyOrders->count() }})</span>
    </button>
    <button type="button" id="tabBtnOnHold" class="btn btn-sm flex-fill rounded-pill fw-bold py-2 text-secondary border bg-white shadow-xs d-flex align-items-center justify-content-center gap-1.5"
            style="font-size: 0.82rem;" onclick="switchRiderTab('onhold')">
        <i class="bi bi-exclamation-triangle-fill text-warning"></i>
        <span>On Hold / Issues ({{ $onHoldOrders->count() }})</span>
    </button>
</div>

<!-- ═══════════ TAB 1: READY TO DELIVER (Amazon-Grade Stop Cards) ═══════════ -->
<div id="tabContentReady">
    @forelse($readyOrders as $index => $order)
        @php
            $stopNumber = $index + 1;
            $isPaid = ($order->payment_status === 'paid' || $order->payment_method === 'wallet' || ($order->total_amount <= 0 && $order->wallet_amount_used > 0));
            $isCod = ($order->payment_method === 'cod' && !$isPaid && $order->total_amount > 0);

            // Extract preferred delivery time slot if formatted inside notes: [Time: ...]
            $timeSlot = '';
            $customerNotes = $order->notes ?? '';
            if (preg_match('/\[Time:\s*([^\]]+)\]/i', $customerNotes, $matches)) {
                $timeSlot = trim($matches[1]);
                $customerNotes = trim(str_replace($matches[0], '', $customerNotes));
            }

            // Check if address is Office / Work for Business Hours Priority
            $isOffice = str_contains(strtolower($order->shipping_address ?? ''), 'office') 
                     || str_contains(strtolower($order->shipping_address ?? ''), 'tech park') 
                     || str_contains(strtolower($order->shipping_address ?? ''), 'tower')
                     || str_contains(strtolower($timeSlot), 'office');
        @endphp

        <!-- Amazon-Style Sequenced Stop Card -->
        <div class="card border-0 rounded-4 mb-3.5 bg-white shadow-sm overflow-hidden" style="border: 1.5px solid #cbd5e1 !important;">
            <!-- Stop Header: STOP #X + Order Number + Mode Badge -->
            <div class="p-3 border-bottom d-flex align-items-center justify-content-between flex-wrap gap-2 bg-light bg-opacity-75">
                <div class="d-flex align-items-center gap-1.5 min-w-0">
                    <span class="badge rounded-pill bg-dark text-white px-2.5 py-1 fw-bolder font-monospace" style="font-size: 0.72rem;">
                        STOP #{{ $stopNumber }}
                    </span>
                    <span class="font-monospace fw-bold text-dark text-truncate" style="font-size: 0.88rem;">
                        #{{ $order->order_number }}
                    </span>
                    <button type="button" class="btn btn-xs btn-link text-muted p-0 border-0" onclick="navigator.clipboard.writeText('{{ $order->order_number }}'); Swal.fire({toast: true, position: 'top', icon: 'success', title: 'Order # copied', showConfirmButton: false, timer: 1000});" title="Copy Order Number">
                        <i class="bi bi-copy" style="font-size: 0.8rem;"></i>
                    </button>
                </div>

                <div class="flex-shrink-0">
                    @if($isCod)
                        <span class="badge rounded-pill px-2.5 py-1 fw-bold font-monospace shadow-xs" 
                              style="background: #fef3c7; color: #92400e; border: 1.5px solid #fcd34d; font-size: 0.74rem;">
                            COD
                        </span>
                    @else
                        <span class="badge rounded-pill px-2.5 py-1 fw-bold text-white shadow-xs" 
                              style="background: #10b981; font-size: 0.72rem;">
                            ✓ Paid
                        </span>
                    @endif
                </div>
            </div>

            <!-- Card Body: Customer, Payment, Location & Action -->
            <div class="p-3.5">
                <!-- Highlight Cash Collection Banner (If COD) -->
                @if($isCod)
                    <div class="p-3 rounded-3 mb-3 d-flex align-items-center justify-content-between shadow-xs" 
                         style="background: #fffbeb; border: 1.5px solid #fcd34d;">
                        <div>
                            <span class="text-uppercase small fw-bold text-secondary d-block" style="font-size: 0.68rem; letter-spacing: 0.04em;">
                                Cash to Collect at Doorstep
                            </span>
                            <span class="fs-4 fw-bolder text-dark font-monospace lh-1">
                                ₹{{ number_format($order->total_amount, 2) }}
                            </span>
                        </div>
                        <span class="badge bg-warning text-dark px-2.5 py-1 fw-bold rounded-pill" style="font-size: 0.72rem;">
                            💵 COD
                        </span>
                    </div>
                @else
                    <div class="p-2.5 px-3 rounded-3 mb-3 d-flex align-items-center justify-content-between shadow-xs" 
                         style="background: #f0fdf4; border: 1.5px solid #bbf7d0;">
                        <div>
                            <span class="text-uppercase small fw-bold text-success d-block" style="font-size: 0.68rem; letter-spacing: 0.04em;">
                                Payment Status
                            </span>
                            <span class="fs-5 fw-bolder text-success font-monospace lh-1">
                                ✓ Paid Online (Collect ₹0)
                            </span>
                        </div>
                    </div>
                @endif

                <!-- Customer Details & Quick Call -->
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <div class="d-flex align-items-center gap-2.5">
                        <div class="rounded-circle text-white d-flex align-items-center justify-content-center fw-bold shadow-xs flex-shrink-0" 
                             style="width: 42px; height: 42px; background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%); font-size: 1.05rem;">
                            {{ strtoupper(substr($order->shipping_name ?? 'C', 0, 1)) }}
                        </div>
                        <div>
                            <h5 class="fw-bolder text-dark mb-0 fs-5" style="letter-spacing: -0.01em;">
                                {{ $order->shipping_name }}
                            </h5>
                            <small class="text-muted" style="font-size: 0.78rem;">Customer &bull; Stop #{{ $stopNumber }}</small>
                        </div>
                    </div>
                    <a href="tel:{{ $order->shipping_phone }}" class="btn btn-success rounded-pill px-3.5 py-2 fw-bold d-inline-flex align-items-center gap-1.5 shadow-xs" style="font-size: 0.85rem;">
                        <i class="bi bi-telephone-fill"></i> <span>Call</span>
                    </a>
                </div>

                <!-- Full Delivery Destination Box -->
                <div class="p-3 rounded-3 mb-3 bg-light border" style="border-color: #e2e8f0 !important;">
                    <div class="d-flex align-items-start gap-2.5">
                        <i class="bi bi-geo-alt-fill text-danger flex-shrink-0 mt-0.5 fs-5"></i>
                        <div style="line-height: 1.5;">
                            <div class="text-dark fw-semibold" style="font-size: 0.92rem;">
                                {{ $order->shipping_address }}
                            </div>
                            <div class="text-secondary small mt-0.5" style="font-size: 0.82rem;">
                                {{ $order->shipping_city }} - <strong class="text-dark">{{ $order->shipping_zip }}</strong>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Delivery Timing & Business Priority Pills -->
                <div class="d-flex flex-wrap gap-2 mb-3">
                    @if($isOffice)
                        <div class="badge rounded-pill bg-warning bg-opacity-20 text-dark border border-warning px-3 py-1.5 fw-bold d-inline-flex align-items-center gap-1.5" style="font-size: 0.78rem;">
                            <i class="bi bi-briefcase-fill text-warning"></i> Office Priority (Deliver Before 6 PM)
                        </div>
                    @endif

                    @if(!empty($timeSlot))
                        <div class="badge rounded-pill bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25 px-3 py-1.5 fw-bold d-inline-flex align-items-center gap-1.5 font-monospace" style="font-size: 0.78rem;">
                            <i class="bi bi-clock-history"></i> {{ $timeSlot }}
                        </div>
                    @endif

                    @if(!empty($customerNotes))
                        <div class="badge rounded-pill bg-warning bg-opacity-20 text-dark border border-warning border-opacity-35 px-3 py-1.5 fw-semibold d-inline-flex align-items-center gap-1.5" style="font-size: 0.78rem;">
                            <i class="bi bi-chat-left-text-fill text-warning"></i> "{{ $customerNotes }}"
                        </div>
                    @endif
                </div>

                <!-- Parcel Summary Preview -->
                @if($order->items && $order->items->isNotEmpty())
                    <div class="p-2.5 px-3 rounded-3 bg-light border mb-3 text-secondary small d-flex align-items-center justify-content-between" style="border-color: #e2e8f0 !important; font-size: 0.8rem;">
                        <span class="fw-semibold text-dark">
                            📦 {{ $order->items->sum('quantity') }} Item(s)
                        </span>
                        <span class="text-truncate" style="max-width: 220px;">
                            {{ $order->items->first()?->product_name }}
                            @if($order->items->count() > 1) + {{ $order->items->count() - 1 }} more @endif
                        </span>
                    </div>
                @endif

                <!-- Big 48px Thumb Action Buttons -->
                <div class="row g-2 pt-1">
                    <div class="col-5">
                        <a href="https://www.google.com/maps/dir/?api=1&destination={{ urlencode($order->shipping_address . ', ' . $order->shipping_city . ' ' . $order->shipping_zip) }}" 
                           target="_blank" class="btn btn-outline-primary w-100 rounded-3 fw-bold d-flex align-items-center justify-content-center gap-1.5 shadow-xs" style="height: 48px; font-size: 0.9rem;">
                            <i class="bi bi-map-fill"></i> <span>Maps</span>
                        </a>
                    </div>
                    <div class="col-7">
                        <a href="{{ route('delivery.orders.show', $order) }}" 
                           class="btn btn-primary w-100 rounded-3 fw-bold d-flex align-items-center justify-content-center gap-2 shadow-xs text-white" 
                           style="height: 48px; font-size: 0.92rem; background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%); border: none;">
                            <span>Deliver Now</span> <i class="bi bi-arrow-right"></i>
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
            <h5 class="fw-bolder text-dark mb-1">Manifest Completed! 🎉</h5>
            <p class="text-secondary small mb-0" style="font-size: 0.84rem;">All planned deliveries for this run are complete. Head back to the hub to settle your cash.</p>
        </div>
    @endforelse
</div>

<!-- ═══════════ TAB 2: ON HOLD / REPORTED ISSUES (Warehouse Review) ═══════════ -->
<div id="tabContentOnHold" style="display: none;">
    <div class="alert alert-warning border-0 rounded-3 p-3 mb-3 d-flex align-items-start gap-2.5 shadow-xs" style="background: #fffbeb; border: 1.5px solid #fde68a !important;">
        <i class="bi bi-info-circle-fill text-warning fs-5 flex-shrink-0"></i>
        <div class="small text-dark" style="font-size: 0.8rem; line-height: 1.4;">
            These parcels have reported issues. The warehouse is contacting the customer. If customer arrives or calls, tap <strong>Re-Attempt</strong>.
        </div>
    </div>

    @forelse($onHoldOrders as $order)
        <div class="card border-0 rounded-4 mb-3.5 bg-white shadow-xs overflow-hidden" style="border: 1.5px solid #fde68a !important; border-left: 5px solid #f59e0b !important;">
            <div class="p-3.5">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="fw-bold font-monospace text-secondary fs-6">#{{ $order->order_number }}</span>
                    <span class="badge bg-danger rounded-pill px-2.5 py-1 fw-bold" style="font-size: 0.68rem;">
                        <i class="bi bi-exclamation-octagon-fill me-1"></i> Issue Logged
                    </span>
                </div>

                <div class="p-2 px-2.5 rounded-3 bg-danger bg-opacity-10 border border-danger border-opacity-25 mb-2.5 d-flex align-items-center justify-content-between" style="font-size: 0.78rem;">
                    <span class="text-danger fw-bold text-truncate" style="max-width: 240px;">
                        <i class="bi bi-megaphone-fill me-1"></i> {{ $order->fulfillment?->delivery_issue ?? 'Delivery Issue Logged' }}
                    </span>
                    <span class="badge bg-white text-danger border rounded-pill px-2 py-0.5" style="font-size: 0.64rem;">
                        {{ $order->fulfillment?->delivery_issue_at?->diffForHumans() ?? 'Recently' }}
                    </span>
                </div>

                <div class="mb-3">
                    <h5 class="fw-bolder text-dark mb-1 fs-5">{{ $order->shipping_name }}</h5>
                    <div class="text-secondary" style="line-height: 1.45; font-size: 0.86rem;">
                        <i class="bi bi-geo-alt-fill text-danger me-1"></i>{{ $order->shipping_address }}, {{ $order->shipping_city }} - <strong>{{ $order->shipping_zip }}</strong>
                    </div>
                </div>

                <div class="row g-2 pt-2 border-top">
                    <div class="col-6">
                        <a href="tel:{{ $order->shipping_phone }}" class="btn btn-outline-secondary w-100 rounded-3 fw-bold d-flex align-items-center justify-content-center gap-1.5 shadow-xs" style="height: 46px; font-size: 0.86rem;">
                            <i class="bi bi-telephone-fill text-success"></i> Call Customer
                        </a>
                    </div>
                    <div class="col-6">
                        <a href="{{ route('delivery.orders.show', $order) }}" class="btn btn-warning text-dark w-100 fw-bold rounded-3 d-flex align-items-center justify-content-center gap-1 shadow-xs" 
                           style="height: 46px; font-size: 0.88rem;">
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
            <p class="text-secondary small mb-0" style="font-size: 0.84rem;">Great job! None of your deliveries have reported issues.</p>
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
        btnReady.classList.remove('border', 'bg-white', 'text-secondary');
        btnOnHold.style.background = '#ffffff';
        btnOnHold.style.color = '#64748b';
        btnOnHold.classList.add('border');
    } else {
        readyContent.style.display = 'none';
        onHoldContent.style.display = 'block';
        btnOnHold.style.background = '#0f172a';
        btnOnHold.style.color = '#ffffff';
        btnOnHold.classList.remove('border', 'bg-white', 'text-secondary');
        btnReady.style.background = '#ffffff';
        btnReady.style.color = '#64748b';
        btnReady.classList.add('border');
    }
}
</script>

@endsection
