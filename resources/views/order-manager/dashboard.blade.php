@extends('order-manager.layouts.app')

@section('title', 'Operations Dashboard')
@section('header', 'Fulfillment Operations')

@section('content')

<!-- Section 1: Logistics Command Header -->
<div class="card border-0 shadow-sm rounded-4 mb-4 p-4 p-md-4 bg-white" style="border-top: 4px solid #0284c7 !important;">
    <div class="row align-items-center justify-content-between g-4">
        <div class="col-lg-7">
            <div class="d-flex align-items-center gap-2 mb-2 flex-wrap">
                <span class="badge rounded-pill px-3 py-1.5 fw-bold" style="background: #e0f2fe; color: #0369a1; border: 1px solid #bae6fd; font-size: 0.75rem;">
                    <span class="pulse-dot me-1"></span> DUAL-ZONE LOGISTICS ENGINE
                </span>
                <span class="text-secondary small fw-semibold">&bull; Central Warehouse Hub #01</span>
            </div>
            <h3 class="fw-bolder text-dark mb-2" style="letter-spacing: -0.5px; font-size: 1.6rem;">
                Order Fulfillment & Logistics Command Center
            </h3>
            <p class="text-secondary small mb-3" style="max-width: 600px; line-height: 1.6; font-size: 0.875rem;">
                Live dispatch control for <strong>Bengaluru Express In-House Fleet</strong> and pan-India <strong>National Courier Shipments (BlueDart, Delhivery, DTDC)</strong>.
            </p>

            <!-- Navigation Buttons -->
            <div class="d-flex align-items-center gap-2.5 flex-wrap pt-1">
                <a href="{{ route('order-manager.orders.index', ['zone' => 'bengaluru']) }}" class="btn btn-sm rounded-pill px-3.5 py-2 fw-bold text-white shadow-xs" style="background: #0284c7; font-size: 0.8rem;">
                    <i class="bi bi-geo-fill me-1"></i> Bengaluru Fleet ({{ $stats['bengaluru_local'] }})
                </a>
                <a href="{{ route('order-manager.orders.index', ['zone' => 'courier']) }}" class="btn btn-sm rounded-pill px-3.5 py-2 fw-bold text-white shadow-xs" style="background: #4f46e5; font-size: 0.8rem;">
                    <i class="bi bi-truck me-1"></i> National Courier ({{ $stats['national_courier'] }})
                </a>
                <a href="{{ route('order-manager.orders.index') }}" class="btn btn-sm btn-light border rounded-pill px-3.5 py-2 fw-bold text-dark shadow-xs" style="font-size: 0.8rem;">
                    <i class="bi bi-box-seam me-1"></i> All Orders ({{ $stats['total_orders'] }})
                </a>
            </div>
        </div>

        <div class="col-lg-5 text-lg-end">
            <div class="d-inline-flex flex-column flex-sm-row gap-3 p-3 rounded-4 bg-light border shadow-xs">
                <div class="px-3 py-1.5 text-center">
                    <div class="text-secondary fw-bold small text-uppercase" style="font-size: 0.7rem; letter-spacing: 0.05em;">Orders Today</div>
                    <div id="kpiTodayOrders" class="h2 fw-bolder text-dark mb-0 mt-0.5" style="color: #0f172a !important;">{{ $stats['today_orders'] }}</div>
                    <div class="text-muted small" style="font-size: 0.7rem;">New orders placed</div>
                </div>
                <div class="vr bg-secondary opacity-25 d-none d-sm-block"></div>
                <div class="px-3 py-1.5 text-center">
                    <div class="text-secondary fw-bold small text-uppercase" style="font-size: 0.7rem; letter-spacing: 0.05em;">Today's Revenue</div>
                    <div id="kpiTodayRevenue" class="h2 fw-bolder text-dark mb-0 mt-0.5" style="color: #0f172a !important;">₹{{ number_format($stats['today_revenue'], 2) }}</div>
                    <div class="text-muted small" style="font-size: 0.7rem;">Gross processed</div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Section 2: 2 Dual-Mode Fulfillment Hub Cards -->
<div class="row g-4 mb-4">
    <!-- Hub A: Bengaluru Local Fleet -->
    <div class="col-md-6">
        <div class="card border-0 shadow-sm rounded-4 p-4 h-100 position-relative kpi-card"
             style="background: #ffffff; border: 1.5px solid #bae6fd !important; border-top: 4px solid #0284c7 !important;">
            <div class="d-flex align-items-center justify-content-between mb-3">
                <div class="d-flex align-items-center gap-3">
                    <div style="width: 48px; height: 48px; border-radius: 12px; background: #0284c7; color: #ffffff; display: flex; align-items: center; justify-content: center; font-size: 1.35rem; box-shadow: 0 4px 12px rgba(2, 132, 199, 0.3);">
                        <i class="bi bi-geo-fill"></i>
                    </div>
                    <div>
                        <h5 class="fw-bold text-dark mb-0.5">🏙️ Bengaluru Local Fleet Hub</h5>
                        <span class="text-secondary small" style="font-size: 0.78rem;">In-House Riders, Porter, Dunzo Express (560xxx)</span>
                    </div>
                </div>
                <span class="badge rounded-pill px-3 py-1.5 fw-bold text-white" style="background: #0284c7; font-size: 0.75rem;">
                    {{ $stats['bengaluru_local'] }} Orders
                </span>
            </div>

            <div class="row g-2.5 pt-3 border-top mt-2">
                <div class="col-6">
                    <div class="p-3 rounded-3 bg-light border">
                        <div class="text-secondary small fw-bold text-uppercase" style="font-size: 0.7rem;">Rider Pending</div>
                        <div class="h3 fw-bolder {{ $stats['bengaluru_pending'] > 0 ? 'text-danger' : 'text-success' }} mb-0 mt-1">
                            {{ $stats['bengaluru_pending'] }}
                        </div>
                    </div>
                </div>
                <div class="col-6">
                    <div class="p-3 rounded-3 bg-light border">
                        <div class="text-secondary small fw-bold text-uppercase" style="font-size: 0.7rem;">Delivery Window</div>
                        <div class="fw-bold text-dark mb-0 mt-1" style="font-size: 0.85rem;">
                            <i class="bi bi-check-circle-fill text-success me-1"></i>1–2 Business Days
                        </div>
                    </div>
                </div>
            </div>

            <div class="mt-3.5 text-end">
                <a href="{{ route('order-manager.orders.index', ['zone' => 'bengaluru']) }}" class="btn btn-sm rounded-pill px-4 py-2 fw-bold text-white shadow-xs" style="background: #0284c7; border: none; font-size: 0.8rem;">
                    Manage Bengaluru Dispatch Queue <i class="bi bi-arrow-right ms-1"></i>
                </a>
            </div>
        </div>
    </div>

    <!-- Hub B: National Courier Logistics -->
    <div class="col-md-6">
        <div class="card border-0 shadow-sm rounded-4 p-4 h-100 position-relative kpi-card"
             style="background: #ffffff; border: 1.5px solid #ddd6fe !important; border-top: 4px solid #4f46e5 !important;">
            <div class="d-flex align-items-center justify-content-between mb-3">
                <div class="d-flex align-items-center gap-3">
                    <div style="width: 48px; height: 48px; border-radius: 12px; background: #4f46e5; color: #ffffff; display: flex; align-items: center; justify-content: center; font-size: 1.35rem; box-shadow: 0 4px 12px rgba(79, 70, 229, 0.3);">
                        <i class="bi bi-truck"></i>
                    </div>
                    <div>
                        <h5 class="fw-bold text-dark mb-0.5">🚚 National Courier Logistics Hub</h5>
                        <span class="text-secondary small" style="font-size: 0.78rem;">BlueDart, Delhivery, DTDC, India Post (Pan-India)</span>
                    </div>
                </div>
                <span class="badge rounded-pill px-3 py-1.5 fw-bold text-white" style="background: #4f46e5; font-size: 0.75rem;">
                    {{ $stats['national_courier'] }} Orders
                </span>
            </div>

            <div class="row g-2.5 pt-3 border-top mt-2">
                <div class="col-6">
                    <div class="p-3 rounded-3 bg-light border">
                        <div class="text-secondary small fw-bold text-uppercase" style="font-size: 0.7rem;">AWB Pending</div>
                        <div class="h3 fw-bolder {{ $stats['courier_pending'] > 0 ? 'text-danger' : 'text-success' }} mb-0 mt-1">
                            {{ $stats['courier_pending'] }}
                        </div>
                    </div>
                </div>
                <div class="col-6">
                    <div class="p-3 rounded-3 bg-light border">
                        <div class="text-secondary small fw-bold text-uppercase" style="font-size: 0.7rem;">Transit Window</div>
                        <div class="fw-bold text-dark mb-0 mt-1" style="font-size: 0.85rem;">
                            <i class="bi bi-box-seam text-primary me-1"></i>2–5 Days Surface/Air
                        </div>
                    </div>
                </div>
            </div>

            <div class="mt-3.5 text-end">
                <a href="{{ route('order-manager.orders.index', ['zone' => 'courier']) }}" class="btn btn-sm rounded-pill px-4 py-2 fw-bold text-white shadow-xs" style="background: #4f46e5; border: none; font-size: 0.8rem;">
                    Manage National Courier Queue <i class="bi bi-arrow-right ms-1"></i>
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Section 3: 4 Operations KPI Cards -->
<div class="row g-3 g-md-4 mb-4">
    <!-- Card 1: Needs Processing -->
    <div class="col-sm-6 col-xl-3">
        <a href="{{ route('order-manager.orders.index', ['status' => 'pending,confirmed']) }}" class="text-decoration-none">
            <div class="card border-0 shadow-sm rounded-4 p-4 h-100 position-relative kpi-card" 
                 style="background: #ffffff; border-top: 4px solid #f59e0b !important;">
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <div style="width: 44px; height: 44px; border-radius: 12px; background: #fef3c7; color: #d97706; display: flex; align-items: center; justify-content: center; font-size: 1.25rem;">
                        <i class="bi bi-hourglass-split"></i>
                    </div>
                    <span class="badge rounded-pill px-2.5 py-1 fw-bold" style="background: #fef3c7; color: #92400e; font-size: 0.72rem;">
                        Action Required
                    </span>
                </div>
                <div class="text-secondary small fw-bold text-uppercase" style="font-size: 0.72rem; letter-spacing: 0.05em;">Needs Processing</div>
                <div id="kpiNeedsProcessing" class="h2 fw-bolder text-dark mb-1 mt-1">{{ $stats['needs_processing'] }}</div>
                <div class="text-secondary small d-flex align-items-center justify-content-between mt-2 pt-2 border-top" style="font-size: 0.75rem;">
                    <span>Awaiting confirmation</span>
                    <i class="bi bi-arrow-right text-warning fw-bold"></i>
                </div>
            </div>
        </a>
    </div>

    <!-- Card 2: Packing Queue -->
    <div class="col-sm-6 col-xl-3">
        <a href="{{ route('order-manager.orders.index', ['status' => 'processing,packed']) }}" class="text-decoration-none">
            <div class="card border-0 shadow-sm rounded-4 p-4 h-100 position-relative kpi-card" 
                 style="background: #ffffff; border-top: 4px solid #6366f1 !important;">
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <div style="width: 44px; height: 44px; border-radius: 12px; background: #e0e7ff; color: #6366f1; display: flex; align-items: center; justify-content: center; font-size: 1.25rem;">
                        <i class="bi bi-box-seam"></i>
                    </div>
                    <span class="badge rounded-pill px-2.5 py-1 fw-bold" style="background: #e0e7ff; color: #3730a3; font-size: 0.72rem;">
                        Warehouse Pick
                    </span>
                </div>
                <div class="text-secondary small fw-bold text-uppercase" style="font-size: 0.72rem; letter-spacing: 0.05em;">Packing Queue</div>
                <div id="kpiPackingQueue" class="h2 fw-bolder text-dark mb-1 mt-1">{{ $stats['packing_queue'] }}</div>
                <div class="text-secondary small d-flex align-items-center justify-content-between mt-2 pt-2 border-top" style="font-size: 0.75rem;">
                    <span>Ready for packaging</span>
                    <i class="bi bi-arrow-right text-primary fw-bold"></i>
                </div>
            </div>
        </a>
    </div>

    <!-- Card 3: In-Transit -->
    <div class="col-sm-6 col-xl-3">
        <a href="{{ route('order-manager.orders.index', ['status' => 'shipped,out for delivery']) }}" class="text-decoration-none">
            <div class="card border-0 shadow-sm rounded-4 p-4 h-100 position-relative kpi-card" 
                 style="background: #ffffff; border-top: 4px solid #0284c7 !important;">
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <div style="width: 44px; height: 44px; border-radius: 12px; background: #e0f2fe; color: #0284c7; display: flex; align-items: center; justify-content: center; font-size: 1.25rem;">
                        <i class="bi bi-truck"></i>
                    </div>
                    <span class="badge rounded-pill px-2.5 py-1 fw-bold" style="background: #e0f2fe; color: #0369a1; font-size: 0.72rem;">
                        In-Transit
                    </span>
                </div>
                <div class="text-secondary small fw-bold text-uppercase" style="font-size: 0.72rem; letter-spacing: 0.05em;">Out with Carrier/Rider</div>
                <div id="kpiInTransit" class="h2 fw-bolder text-dark mb-1 mt-1">{{ $stats['in_transit'] }}</div>
                <div class="text-secondary small d-flex align-items-center justify-content-between mt-2 pt-2 border-top" style="font-size: 0.75rem;">
                    <span>Out for customer delivery</span>
                    <i class="bi bi-arrow-right text-info fw-bold"></i>
                </div>
            </div>
        </a>
    </div>

    <!-- Card 4: Delivered Total -->
    <div class="col-sm-6 col-xl-3">
        <a href="{{ route('order-manager.orders.index', ['status' => 'delivered']) }}" class="text-decoration-none">
            <div class="card border-0 shadow-sm rounded-4 p-4 h-100 position-relative kpi-card" 
                 style="background: #ffffff; border-top: 4px solid #10b981 !important;">
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <div style="width: 44px; height: 44px; border-radius: 12px; background: #d1fae5; color: #10b981; display: flex; align-items: center; justify-content: center; font-size: 1.25rem;">
                        <i class="bi bi-check-circle-fill"></i>
                    </div>
                    <span class="badge rounded-pill px-2.5 py-1 fw-bold" style="background: #d1fae5; color: #065f46; font-size: 0.72rem;">
                        Fulfilled
                    </span>
                </div>
                <div class="text-secondary small fw-bold text-uppercase" style="font-size: 0.72rem; letter-spacing: 0.05em;">Delivered Orders</div>
                <div class="h2 fw-bolder text-dark mb-1 mt-1">{{ $stats['delivered'] }}</div>
                <div class="text-secondary small d-flex align-items-center justify-content-between mt-2 pt-2 border-top" style="font-size: 0.75rem;">
                    <span>{{ $stats['total_orders'] }} all-time total</span>
                    <i class="bi bi-arrow-right text-success fw-bold"></i>
                </div>
            </div>
        </a>
    </div>
</div>

<!-- Section 4: Main Operational Layout -->
<div class="row g-4">
    <!-- Left Column: Priority Fulfillment Queue (8 Cols) -->
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-4">
            <div class="card-header bg-white py-3.5 px-4 border-bottom d-flex align-items-center justify-content-between flex-wrap gap-2">
                <div class="d-flex align-items-center gap-2.5">
                    <div class="rounded-3 bg-danger bg-opacity-10 text-danger d-flex align-items-center justify-content-center" style="width: 34px; height: 34px; font-size: 1.05rem;">
                        <i class="bi bi-fire"></i>
                    </div>
                    <div>
                        <h6 class="mb-0 fw-bold text-dark" style="font-size: 0.95rem;">Priority Fulfillment Queue</h6>
                        <span class="text-secondary small" style="font-size: 0.75rem;">Orders pending warehouse processing & dispatch</span>
                    </div>
                </div>
                <a href="{{ route('order-manager.orders.index', ['status' => 'pending,confirmed,processing,packed']) }}" class="btn btn-sm btn-outline-primary rounded-pill px-3.5 py-1.5 fw-bold" style="font-size: 0.78rem;">
                    View Complete Queue <i class="bi bi-arrow-right ms-1"></i>
                </a>
            </div>

            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead style="background: #f8fafc; border-bottom: 1px solid #e2e8f0; color: #334155; font-size: 0.75rem; letter-spacing: 0.04em;">
                        <tr>
                            <th class="ps-4 py-3 text-uppercase fw-bold">Order #</th>
                            <th class="py-3 text-uppercase fw-bold">Zone & Destination</th>
                            <th class="py-3 text-uppercase fw-bold">Items</th>
                            <th class="py-3 text-uppercase fw-bold">Amount</th>
                            <th class="py-3 text-uppercase fw-bold">Stage</th>
                            <th class="pe-4 py-3 text-end text-uppercase fw-bold">Dispatch Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y">
                        @forelse($urgentOrders as $ord)
                            @php
                                $statusBadge = match($ord->status) {
                                    'pending'          => ['bg' => '#fef3c7', 'color' => '#92400e', 'label' => 'Pending'],
                                    'confirmed'        => ['bg' => '#e0e7ff', 'color' => '#3730a3', 'label' => 'Confirmed'],
                                    'processing'       => ['bg' => '#dbeafe', 'color' => '#1e40af', 'label' => 'Processing'],
                                    'packed'           => ['bg' => '#f3e8ff', 'color' => '#6b21a8', 'label' => 'Packed'],
                                    'shipped'          => ['bg' => '#cffafe', 'color' => '#155e75', 'label' => 'Shipped'],
                                    'out for delivery' => ['bg' => '#ffedd5', 'color' => '#9a3412', 'label' => 'Out for Delivery'],
                                    'delivered'        => ['bg' => '#d1fae5', 'color' => '#065f46', 'label' => 'Delivered'],
                                    default            => ['bg' => '#fee2e2', 'color' => '#991b1b', 'label' => ucfirst($ord->status)],
                                };
                            @endphp
                            <tr>
                                <td class="ps-4 py-3">
                                    <a href="{{ route('order-manager.orders.show', $ord) }}" class="fw-bold text-primary text-decoration-none font-monospace" style="font-size: 0.85rem;">
                                        #{{ $ord->order_number }}
                                    </a>
                                    <div class="text-secondary" style="font-size: 0.7rem;">
                                        <i class="bi bi-clock me-0.5"></i>{{ $ord->created_at->diffForHumans() }}
                                    </div>
                                </td>

                                <td class="py-3">
                                    <div class="mb-1">{!! $ord->fulfillment_badge !!}</div>
                                    <div class="fw-bold text-dark small">{{ $ord->shipping_name }}</div>
                                    <div class="text-secondary small" style="font-size: 0.72rem;">
                                        <i class="bi bi-geo-alt me-0.5"></i>{{ $ord->shipping_city }} ({{ $ord->shipping_zip }})
                                    </div>
                                </td>

                                <td class="py-3">
                                    <span class="badge bg-light text-dark border px-2.5 py-1 rounded-pill small fw-bold">
                                        {{ $ord->items->sum('quantity') }} {{ Str::plural('unit', $ord->items->sum('quantity')) }}
                                    </span>
                                </td>

                                <td class="py-3">
                                    <div class="fw-bolder text-dark" style="font-size: 0.88rem;">₹{{ number_format($ord->total_amount, 2) }}</div>
                                    <div class="text-secondary font-monospace" style="font-size: 0.68rem; text-transform: uppercase;">
                                        {{ $ord->payment_method }} &bull; <span class="{{ $ord->payment_status === 'paid' ? 'text-success fw-bold' : 'text-warning fw-bold' }}">{{ $ord->payment_status ?? 'pending' }}</span>
                                    </div>
                                </td>

                                <td class="py-3">
                                    <span class="badge rounded-pill px-2.5 py-1.5 fw-bold" style="background: {{ $statusBadge['bg'] }}; color: {{ $statusBadge['color'] }}; font-size: 0.72rem;">
                                        {{ $statusBadge['label'] }}
                                    </span>
                                </td>

                                <td class="pe-4 py-3 text-end">
                                    <div class="d-flex align-items-center justify-content-end gap-1.5 flex-nowrap">
                                        <a href="{{ route('order-manager.orders.show', $ord) }}" class="btn btn-sm btn-primary rounded-pill px-3 py-1.5 d-inline-flex align-items-center gap-1 shadow-xs fw-bold text-white" style="font-size: 0.75rem; background: #0284c7; border-color: #0284c7;">
                                            <span>Dispatch</span> <i class="bi bi-arrow-right"></i>
                                        </a>
                                        @if($ord->isLocalBengaluruDelivery())
                                            <a href="{{ route('order-manager.orders.manifest', $ord) }}" target="_blank" class="action-btn-circle action-btn-view" title="Print Delivery Run-Sheet">
                                                <i class="bi bi-file-earmark-text-fill"></i>
                                            </a>
                                        @else
                                            <a href="{{ route('order-manager.orders.packing-slip', $ord) }}" target="_blank" class="action-btn-circle action-btn-invoice" title="Print Packing Slip">
                                                <i class="bi bi-printer-fill"></i>
                                            </a>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-5 text-secondary">
                                    <div class="py-3">
                                        <i class="bi bi-check2-circle text-success display-6 d-block mb-2 opacity-75"></i>
                                        <h6 class="fw-bold text-dark mb-1">Fulfillment Queue is Up to Date!</h6>
                                        <span class="small text-secondary">All pending orders have been processed and dispatched.</span>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Right Column: Logistics Dispatches & Activity Stream (4 Cols) -->
    <div class="col-lg-4">
        <!-- Dispatches Card -->
        <div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-4">
            <div class="card-header bg-white py-3 px-4 border-bottom d-flex align-items-center justify-content-between">
                <div class="d-flex align-items-center gap-2">
                    <div class="rounded-3 d-flex align-items-center justify-content-center" style="width: 32px; height: 32px; font-size: 0.95rem; background: #e0f2fe; color: #0284c7;">
                        <i class="bi bi-truck"></i>
                    </div>
                    <h6 class="mb-0 fw-bold text-dark" style="font-size: 0.92rem;">Recent Dispatches</h6>
                </div>
                <span class="badge bg-light text-dark border px-2 py-0.5 rounded-pill small fw-bold" style="font-size: 0.68rem;">Live Feed</span>
            </div>
            <div class="card-body p-0">
                @if($recentDispatches->count() > 0)
                    <div class="list-group list-group-flush">
                        @foreach($recentDispatches as $disp)
                            <div class="list-group-item p-3 border-bottom d-flex align-items-center justify-content-between">
                                <div class="overflow-hidden pe-2">
                                    <a href="{{ route('order-manager.orders.show', $disp) }}" class="fw-bold text-dark text-decoration-none small text-truncate d-block" style="font-size: 0.82rem;">
                                        #{{ $disp->order_number }}
                                    </a>
                                    <div class="text-secondary small text-truncate" style="font-size: 0.72rem;">
                                        To: <strong>{{ $disp->shipping_name }}</strong> &bull; {{ $disp->shipping_city }}
                                    </div>
                                    @if($disp->isLocalBengaluruDelivery() && $disp->rider_name)
                                        <div class="text-primary small fw-bold mt-0.5" style="font-size: 0.7rem;">
                                            <i class="bi bi-person-badge me-0.5"></i>Rider: {{ $disp->rider_name }}
                                        </div>
                                    @elseif($disp->tracking_number)
                                        <div class="text-primary font-monospace mt-0.5" style="font-size: 0.7rem;">
                                            <i class="bi bi-upc-scan me-1"></i>{{ $disp->courier_partner }}: {{ $disp->tracking_number }}
                                        </div>
                                    @endif
                                </div>
                                <div class="text-end flex-shrink-0">
                                    <div class="mb-1">{!! $disp->fulfillment_badge !!}</div>
                                    <span class="badge bg-light text-dark border px-2 py-0.5 rounded-pill small fw-bold" style="font-size: 0.68rem;">
                                        {{ ucfirst($disp->status) }}
                                    </span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-4 text-secondary small">No recent dispatches recorded.</div>
                @endif
            </div>
        </div>

        <!-- Activity Stream Card -->
        <div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-4">
            <div class="card-header bg-white py-3 px-4 border-bottom">
                <div class="d-flex align-items-center gap-2">
                    <div class="rounded-3 d-flex align-items-center justify-content-center" style="width: 32px; height: 32px; font-size: 0.95rem; background: #f1f5f9; color: #475569;">
                        <i class="bi bi-clock-history"></i>
                    </div>
                    <h6 class="mb-0 fw-bold text-dark" style="font-size: 0.92rem;">Fulfillment Activity Stream</h6>
                </div>
            </div>
            <div class="card-body p-0">
                @if($recentActivity->count() > 0)
                    <div class="list-group list-group-flush">
                        @foreach($recentActivity->take(6) as $act)
                            <div class="list-group-item p-3 border-bottom">
                                <div class="d-flex align-items-center justify-content-between mb-1">
                                    <span class="fw-bold text-dark small font-monospace">#{{ $act->order?->order_number ?? 'Order' }}</span>
                                    <span class="text-secondary" style="font-size: 0.7rem;">{{ $act->created_at->diffForHumans() }}</span>
                                </div>
                                <div class="text-dark small" style="font-size: 0.75rem;">
                                    <span class="badge bg-light text-dark border px-1.5 py-0.5 rounded fw-bold me-1" style="font-size: 0.68rem;">
                                        {{ ucfirst(str_replace('_', ' ', $act->current_status)) }}
                                    </span>
                                    <span class="text-secondary">{{ $act->notes ?? 'Status transition recorded' }}</span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-4 text-secondary small">No activity logs recorded yet.</div>
                @endif
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
    .kpi-card {
        transition: all 0.22s cubic-bezier(.4,0,.2,1);
    }
    .kpi-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 12px 25px -5px rgba(0, 0, 0, 0.08), 0 0 1px 1px rgba(0, 0, 0, 0.04) !important;
    }
</style>
@endpush

@endsection
