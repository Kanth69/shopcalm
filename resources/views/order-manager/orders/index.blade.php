@extends('order-manager.layouts.app')

@section('title', 'Orders Catalog')
@section('header', 'Orders Fulfillment & Dispatch')

@section('actions')
    <form action="{{ route('order-manager.orders.auto-assign-riders') }}" method="POST" class="d-inline">
        @csrf
        <button type="submit" class="btn text-white rounded-pill px-4 py-2 fw-bold shadow-sm" style="background: linear-gradient(135deg, #091322 0%, #0284c7 100%); border: 1px solid rgba(56, 189, 248, 0.4); font-size: 0.85rem; box-shadow: 0 4px 14px rgba(2, 132, 199, 0.3);">
            <i class="bi bi-lightning-charge-fill me-1.5 text-warning"></i> Auto-Assign Local Riders
        </button>
    </form>
@endsection

@section('content')

<!-- Filter Status Tabs -->
<div class="d-flex align-items-center gap-2 overflow-auto pb-2 mb-4">
    <a href="{{ route('order-manager.orders.index') }}" 
       class="btn btn-sm rounded-pill px-3.5 py-1.5 fw-bold {{ !request()->filled('status') ? 'btn-primary' : 'btn-light border text-secondary' }}" 
       style="{{ !request()->filled('status') ? 'background: #0284c7; border-color: #0284c7;' : '' }}">
        All Orders ({{ $statusCounts['all'] }})
    </a>
    <a href="{{ route('order-manager.orders.index', ['zone' => 'bengaluru']) }}" 
       class="btn btn-sm rounded-pill px-3.5 py-1.5 fw-bold {{ request()->get('zone') === 'bengaluru' ? 'text-white' : 'btn-light border text-secondary' }}"
       style="{{ request()->get('zone') === 'bengaluru' ? 'background: #0284c7; border-color: #0284c7;' : '' }}">
        <i class="bi bi-geo-fill me-1"></i> 🏙️ Bengaluru Local ({{ $statusCounts['bengaluru_local'] }})
    </a>
    <a href="{{ route('order-manager.orders.index', ['zone' => 'courier']) }}" 
       class="btn btn-sm rounded-pill px-3.5 py-1.5 fw-bold {{ request()->get('zone') === 'courier' ? 'text-white' : 'btn-light border text-secondary' }}"
       style="{{ request()->get('zone') === 'courier' ? 'background: #4f46e5; border-color: #4f46e5;' : '' }}">
        <i class="bi bi-truck me-1"></i> 🚚 National Courier ({{ $statusCounts['national_courier'] }})
    </a>
    <a href="{{ route('order-manager.orders.index', ['status' => 'pending,confirmed']) }}" 
       class="btn btn-sm rounded-pill px-3.5 py-1.5 fw-bold {{ request()->get('status') === 'pending,confirmed' ? 'btn-warning text-dark' : 'btn-light border text-secondary' }}">
        Needs Processing ({{ $statusCounts['needs_processing'] }})
    </a>
    <a href="{{ route('order-manager.orders.index', ['status' => 'processing,packed']) }}" 
       class="btn btn-sm rounded-pill px-3.5 py-1.5 fw-bold {{ request()->get('status') === 'processing,packed' ? 'btn-indigo text-white' : 'btn-light border text-secondary' }}"
       style="{{ request()->get('status') === 'processing,packed' ? 'background: #6366f1; border-color: #6366f1;' : '' }}">
        Packing Queue ({{ $statusCounts['packing_queue'] }})
    </a>
    <a href="{{ route('order-manager.orders.index', ['status' => 'shipped,out for delivery']) }}" 
       class="btn btn-sm rounded-pill px-3.5 py-1.5 fw-bold {{ request()->get('status') === 'shipped,out for delivery' ? 'btn-info text-dark' : 'btn-light border text-secondary' }}">
        In-Transit & Shipped ({{ $statusCounts['in_transit'] }})
    </a>
    <a href="{{ route('order-manager.orders.index', ['status' => 'delivered']) }}" 
       class="btn btn-sm rounded-pill px-3.5 py-1.5 fw-bold {{ request()->get('status') === 'delivered' ? 'btn-success text-white' : 'btn-light border text-secondary' }}">
        Delivered ({{ $statusCounts['delivered'] }})
    </a>
    <a href="{{ route('order-manager.orders.index', ['status' => 'cancelled,returned']) }}" 
       class="btn btn-sm rounded-pill px-3.5 py-1.5 fw-bold {{ request()->get('status') === 'cancelled,returned' ? 'btn-danger text-white' : 'btn-light border text-secondary' }}">
        Cancelled / Returns ({{ $statusCounts['cancelled'] }})
    </a>
</div>

<!-- Search & Filters Toolbar -->
<div class="card border-0 shadow-sm rounded-4 mb-4 p-3 bg-white">
    <form method="GET" action="{{ route('order-manager.orders.index') }}" class="row g-2 align-items-center">
        @if(request()->filled('status'))
            <input type="hidden" name="status" value="{{ request('status') }}">
        @endif

        <div class="col-md-4 col-lg-5">
            <div class="input-group">
                <span class="input-group-text bg-light border-end-0 text-muted"><i class="bi bi-search"></i></span>
                <input type="text" name="search" class="form-control bg-light border-start-0 small" 
                       placeholder="Search order #, customer, phone, email, city, AWB..." 
                       value="{{ request('search') }}">
            </div>
        </div>

        <div class="col-sm-6 col-md-3 col-lg-2">
            <select name="payment_method" class="form-select bg-light small" onchange="this.form.submit()">
                <option value="">All Payments</option>
                <option value="cod" {{ request('payment_method') === 'cod' ? 'selected' : '' }}>COD (Cash On Delivery)</option>
                <option value="card" {{ request('payment_method') === 'card' ? 'selected' : '' }}>Credit / Debit Card</option>
                <option value="upi" {{ request('payment_method') === 'upi' ? 'selected' : '' }}>UPI / QR</option>
                <option value="netbanking" {{ request('payment_method') === 'netbanking' ? 'selected' : '' }}>Net Banking</option>
            </select>
        </div>

        <div class="col-sm-6 col-md-3 col-lg-2">
            <select name="date" class="form-select bg-light small" onchange="this.form.submit()">
                <option value="">All Time</option>
                <option value="today" {{ request('date') === 'today' ? 'selected' : '' }}>Today</option>
                <option value="yesterday" {{ request('date') === 'yesterday' ? 'selected' : '' }}>Yesterday</option>
                <option value="week" {{ request('date') === 'week' ? 'selected' : '' }}>Last 7 Days</option>
                <option value="month" {{ request('date') === 'month' ? 'selected' : '' }}>Last 30 Days</option>
            </select>
        </div>

        <div class="col-md-2 col-lg-3 text-end d-flex gap-2 justify-content-end">
            <button type="submit" class="btn btn-primary btn-sm rounded-pill px-3 fw-bold" style="background: #0284c7; border-color: #0284c7;">Filter</button>
            @if(request()->anyFilled(['search', 'payment_method', 'date']))
                <a href="{{ route('order-manager.orders.index', ['status' => request('status')]) }}" class="btn btn-outline-secondary btn-sm rounded-pill px-2.5">
                    <i class="bi bi-x-circle"></i> Clear
                </a>
            @endif
        </div>
    </form>
</div>

<!-- Orders Table -->
<div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-5">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="bg-light text-muted small" style="font-size: 0.75rem; letter-spacing: 0.04em;">
                <tr>
                    <th style="width: 36px;" class="ps-3 py-3">
                        <input type="checkbox" id="selectAllOrders" class="form-check-input" title="Select All On This Page">
                    </th>
                    <th class="ps-2 py-3">Order Details</th>
                    <th class="py-3">Customer & Shipping</th>
                    <th class="py-3">Items</th>
                    <th class="py-3">Amount & Payment</th>
                    <th class="py-3">Fulfillment Status</th>
                    <th class="py-3">Logistics / Tracking</th>
                    <th class="pe-4 py-3 text-end">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y">
                @forelse($orders as $order)
                    @php
                        if ($order->status === 'pending' && $order->payment_method === 'cod') {
                            $statusBadge = ['bg' => '#fef3c7', 'color' => '#92400e', 'label' => 'Pending COD Approval'];
                        } elseif ($order->status === 'pending' && $order->payment_method === 'online' && $order->payment_status !== 'paid') {
                            $statusBadge = ['bg' => '#fee2e2', 'color' => '#991b1b', 'label' => 'Awaiting Online Pay'];
                        } else {
                            $statusBadge = match($order->status) {
                                'pending'          => ['bg' => '#fef3c7', 'color' => '#92400e', 'label' => 'Pending'],
                                'confirmed'        => ['bg' => '#e0e7ff', 'color' => '#3730a3', 'label' => 'Confirmed'],
                                'processing'       => ['bg' => '#dbeafe', 'color' => '#1e40af', 'label' => 'Processing'],
                                'packed'           => ['bg' => '#f3e8ff', 'color' => '#6b21a8', 'label' => 'Packed'],
                                'shipped'          => ['bg' => '#cffafe', 'color' => '#155e75', 'label' => 'Shipped'],
                                'out for delivery' => ['bg' => '#ffedd5', 'color' => '#9a3412', 'label' => 'Out for Delivery'],
                                'delivered'        => ['bg' => '#d1fae5', 'color' => '#065f46', 'label' => 'Delivered'],
                                default            => ['bg' => '#fee2e2', 'color' => '#991b1b', 'label' => ucfirst($order->status)],
                            };
                        }
                    @endphp
                    <tr>
                        <td class="ps-3">
                            <input type="checkbox" class="form-check-input order-checkbox" value="{{ $order->id }}">
                        </td>
                        <td class="ps-2">
                            <a href="{{ route('order-manager.orders.show', $order) }}" class="fw-bold text-primary text-decoration-none font-monospace small">
                                #{{ $order->order_number }}
                            </a>
                            <div class="text-muted" style="font-size: 0.7rem;">
                                {{ $order->created_at->format('d M Y, h:i A') }}
                            </div>
                        </td>

                        <td>
                            <div class="fw-semibold text-dark small">{{ $order->shipping_name }}</div>
                            <div class="text-muted small" style="font-size: 0.72rem;">
                                <i class="bi bi-geo-alt me-0.5"></i>{{ $order->shipping_city }}, {{ $order->shipping_state }}
                            </div>
                            <div class="text-muted small" style="font-size: 0.7rem;">
                                <i class="bi bi-telephone me-0.5"></i>{{ $order->shipping_phone }}
                            </div>
                        </td>

                        <td>
                            <span class="badge bg-light text-secondary border px-2 py-1 rounded-pill small fw-bold">
                                {{ $order->items->sum('quantity') }} {{ Str::plural('unit', $order->items->sum('quantity')) }}
                            </span>
                            <div class="text-muted" style="font-size: 0.68rem;">{{ $order->items->count() }} unique SKUs</div>
                        </td>

                        <td>
                            <div class="fw-bold text-dark small">₹{{ number_format($order->total_amount, 2) }}</div>
                            <div class="d-flex align-items-center gap-1 mt-0.5">
                                <span class="badge bg-light text-dark border" style="font-size: 0.65rem; text-transform: uppercase;">
                                    {{ ($order->payment_method === 'wallet' || ($order->total_amount <= 0 && $order->wallet_amount_used > 0)) ? 'WALLET (100%)' : $order->payment_method }}
                                </span>
                                <span class="badge {{ ($order->payment_status === 'paid' || $order->payment_method === 'wallet' || ($order->total_amount <= 0 && $order->wallet_amount_used > 0)) ? 'bg-success' : 'bg-warning text-dark' }}" style="font-size: 0.62rem;">
                                    {{ ucfirst($order->payment_status ?? 'pending') }}
                                </span>
                            </div>
                        </td>

                        <td>
                            <span class="badge rounded-pill px-2.5 py-1 fw-bold" style="background: {{ $statusBadge['bg'] }}; color: {{ $statusBadge['color'] }}; font-size: 0.72rem;">
                                {{ $statusBadge['label'] }}
                            </span>
                        </td>

                        <td>
                            <div class="mb-1">{!! $order->fulfillment_badge !!}</div>
                            @if($order->isLocalBengaluruDelivery())
                                @if($order->rider_name)
                                    <div class="fw-bold text-dark small" style="font-size: 0.75rem;"><i class="bi bi-person-badge text-primary me-0.5"></i>{{ $order->rider_name }}</div>
                                    <div class="text-muted small" style="font-size: 0.68rem;"><i class="bi bi-telephone text-muted me-0.5"></i>{{ $order->rider_phone }}</div>
                                @else
                                    <span class="badge bg-secondary bg-opacity-10 text-secondary border border-secondary border-opacity-25 rounded-pill px-2 py-0.5" style="font-size: 0.65rem;">
                                        <i class="bi bi-person-x"></i> Rider Unassigned
                                    </span>
                                @endif
                            @else
                                @if($order->tracking_number)
                                    <div class="fw-bold text-dark small" style="font-size: 0.75rem;"><i class="bi bi-truck text-indigo me-0.5"></i>{{ $order->courier_partner }}</div>
                                    <div class="text-primary font-monospace" style="font-size: 0.7rem;">
                                        @if($order->tracking_url)
                                            <a href="{{ $order->tracking_url }}" target="_blank" class="text-primary text-decoration-underline">
                                                {{ $order->tracking_number }} <i class="bi bi-box-arrow-up-right" style="font-size: 0.65rem;"></i>
                                            </a>
                                        @else
                                            {{ $order->tracking_number }}
                                        @endif
                                    </div>
                                @else
                                    <span class="badge bg-secondary bg-opacity-10 text-secondary border border-secondary border-opacity-25 rounded-pill px-2 py-0.5" style="font-size: 0.65rem;">
                                        <i class="bi bi-clock-history"></i> AWB Unassigned
                                    </span>
                                @endif
                            @endif
                        </td>

                        <td class="pe-4 text-end">
                            <div class="d-flex align-items-center justify-content-end gap-1.5 flex-nowrap">
                                @if($order->status === 'pending' && $order->payment_method === 'cod')
                                <form action="{{ route('order-manager.orders.update-status', $order) }}" method="POST" class="d-inline">
                                    @csrf
                                    <input type="hidden" name="status" value="confirmed">
                                    <input type="hidden" name="notes" value="Cash on Delivery order approved & confirmed by Order Manager.">
                                    <button type="submit" class="btn btn-sm btn-success rounded-pill px-2.5 py-1 fw-bold d-inline-flex align-items-center gap-1 shadow-xs" style="font-size: 0.72rem;" title="Accept COD Order">
                                        <i class="bi bi-check-lg"></i> Accept
                                    </button>
                                </form>
                                @endif
                                <a href="{{ route('order-manager.orders.show', $order) }}" class="action-btn-circle action-btn-view" title="View & Process Order">
                                    <i class="bi bi-arrow-right-circle-fill"></i>
                                </a>
                                <a href="{{ route('order-manager.orders.shipping-label', $order) }}" target="_blank" class="action-btn-circle" style="background: #0f172a; color: #f59e0b;" title="Print 4x6 Thermal Shipping Label">
                                    <i class="bi bi-tag-fill"></i>
                                </a>
                                <a href="{{ route('order-manager.orders.packing-slip', $order) }}" target="_blank" class="action-btn-circle action-btn-invoice" title="Print Warehouse Packing Slip">
                                    <i class="bi bi-printer-fill"></i>
                                </a>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="text-center py-5">
                            <div class="py-4">
                                <i class="bi bi-box-seam text-muted opacity-50 display-6 mb-3 d-block"></i>
                                <h6 class="fw-bold text-dark mb-1">No Orders Found</h6>
                                <p class="text-muted mb-0 small">Try adjusting your filters or search keywords.</p>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($orders->hasPages())
        <div class="card-footer bg-white border-top p-3 d-flex align-items-center justify-content-between">
            <div class="small text-muted">
                Showing {{ $orders->firstItem() }} to {{ $orders->lastItem() }} of {{ $orders->total() }} orders
            </div>
            {{ $orders->links('pagination::bootstrap-5') }}
        </div>
    @endif
</div>

<!-- Floating Bulk Label Action Bar -->
<div id="bulkPrintBar" class="position-fixed bottom-0 start-50 translate-middle-x mb-4 shadow-lg p-2.5 bg-dark text-white rounded-pill d-none align-items-center gap-3" style="z-index: 1050; border: 2px solid #0284c7;">
    <div class="d-flex align-items-center gap-2 ps-3">
        <span class="badge bg-primary rounded-pill px-2.5 py-1 fw-bold font-monospace" id="selectedCountBadge">0</span>
        <span class="small fw-semibold text-white">Orders Selected</span>
    </div>
    <div class="d-flex align-items-center gap-2 pe-2">
        <button type="button" class="btn btn-sm btn-primary rounded-pill px-3.5 py-1.5 fw-bold shadow-xs d-flex align-items-center gap-1.5" onclick="submitBulkPrint()">
            <i class="bi bi-tag-fill text-warning"></i> Print Batch Labels (4x6)
        </button>
        <button type="button" class="btn btn-sm btn-outline-light rounded-pill px-2.5 py-1" onclick="clearSelection()" title="Deselect All">
            <i class="bi bi-x-lg"></i>
        </button>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const selectAll = document.getElementById('selectAllOrders');
        const checkboxes = document.querySelectorAll('.order-checkbox');
        const bulkBar = document.getElementById('bulkPrintBar');
        const countBadge = document.getElementById('selectedCountBadge');

        function updateBulkBar() {
            const checked = document.querySelectorAll('.order-checkbox:checked');
            const count = checked.length;
            if (count > 0) {
                bulkBar.classList.remove('d-none');
                bulkBar.classList.add('d-flex');
                countBadge.textContent = count;
            } else {
                bulkBar.classList.remove('d-flex');
                bulkBar.classList.add('d-none');
            }
        }

        if (selectAll) {
            selectAll.addEventListener('change', function() {
                checkboxes.forEach(cb => cb.checked = selectAll.checked);
                updateBulkBar();
            });
        }

        checkboxes.forEach(cb => {
            cb.addEventListener('change', function() {
                if (!this.checked && selectAll) {
                    selectAll.checked = false;
                }
                updateBulkBar();
            });
        });

        window.clearSelection = function() {
            checkboxes.forEach(cb => cb.checked = false);
            if (selectAll) selectAll.checked = false;
            updateBulkBar();
        };

        window.submitBulkPrint = function() {
            const checked = Array.from(document.querySelectorAll('.order-checkbox:checked')).map(cb => cb.value);
            if (checked.length === 0) return;

            const url = "{{ route('order-manager.orders.bulk-shipping-labels') }}?order_ids=" + checked.join(',');
            window.open(url, '_blank');
        };
    });
</script>
@endsection
