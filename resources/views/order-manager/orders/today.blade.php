@extends('order-manager.layouts.app')

@section('title', "Today's Orders & Dispatch Hub")
@section('header', "Today's Placed Orders & Dispatch Hub")

@section('actions')
    <div class="d-flex align-items-center gap-2 flex-wrap">
        <a href="{{ route('order-manager.orders.export-csv', ['today_only' => 1]) }}" class="btn btn-sm btn-outline-success rounded-pill px-3 py-1.5 fw-bold shadow-xs">
            <i class="bi bi-file-earmark-excel-fill text-success me-1"></i> Export to Excel (.csv)
        </a>
        <a href="{{ route('order-manager.orders.daily-manifest') }}" target="_blank" class="btn btn-sm btn-outline-primary rounded-pill px-3 py-1.5 fw-bold shadow-xs">
            <i class="bi bi-printer-fill me-1"></i> Print Dispatch Manifest
        </a>
        <a href="{{ route('order-manager.orders.bulk-shipping-labels', ['order_ids' => $orders->pluck('id')->implode(',')]) }}" target="_blank" class="btn btn-sm text-white rounded-pill px-3 py-1.5 fw-bold shadow-xs" style="background: #0f172a; border: 1px solid #f59e0b; color: #f59e0b !important;">
            <i class="bi bi-tag-fill me-1 text-warning"></i> Print All Barcode Labels (4x6)
        </a>
    </div>
@endsection

@section('content')

<!-- Today KPI Metric Highlights -->
<div class="row g-3 mb-4">
    <div class="col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm rounded-4 p-3 bg-white h-100">
            <div class="d-flex align-items-center justify-content-between mb-2">
                <span class="text-secondary small fw-bold text-uppercase" style="font-size: 0.68rem; letter-spacing: 0.5px;">Orders Placed Today</span>
                <span class="p-2 rounded-3 bg-light text-primary"><i class="bi bi-box-seam-fill"></i></span>
            </div>
            <div class="h3 fw-bolder text-dark mb-1">{{ $metrics['total_orders'] }}</div>
            <div class="text-muted small" style="font-size: 0.72rem;">
                <span class="text-warning fw-bold">{{ $metrics['cod_count'] }} COD</span> &bull; <span class="text-success fw-bold">{{ $metrics['prepaid_count'] }} Prepaid</span>
            </div>
        </div>
    </div>

    <div class="col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm rounded-4 p-3 bg-white h-100">
            <div class="d-flex align-items-center justify-content-between mb-2">
                <span class="text-secondary small fw-bold text-uppercase" style="font-size: 0.68rem; letter-spacing: 0.5px;">Today's Revenue</span>
                <span class="p-2 rounded-3 bg-light text-success"><i class="bi bi-currency-rupee"></i></span>
            </div>
            <div class="h3 fw-bolder text-success mb-1">₹{{ number_format($metrics['total_revenue'], 2) }}</div>
            <div class="text-muted small" style="font-size: 0.72rem;">Gross value placed today</div>
        </div>
    </div>

    <div class="col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm rounded-4 p-3 bg-white h-100">
            <div class="d-flex align-items-center justify-content-between mb-2">
                <span class="text-secondary small fw-bold text-uppercase" style="font-size: 0.68rem; letter-spacing: 0.5px;">Out for Delivery</span>
                <span class="p-2 rounded-3 bg-light text-warning"><i class="bi bi-bicycle"></i></span>
            </div>
            <div class="h3 fw-bolder text-amber-600 mb-1" style="color: #d97706;">{{ $metrics['out_for_delivery'] }}</div>
            <div class="text-muted small" style="font-size: 0.72rem;">Active with riders right now</div>
        </div>
    </div>

    <div class="col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm rounded-4 p-3 bg-white h-100">
            <div class="d-flex align-items-center justify-content-between mb-2">
                <span class="text-secondary small fw-bold text-uppercase" style="font-size: 0.68rem; letter-spacing: 0.5px;">Delivered Today</span>
                <span class="p-2 rounded-3 bg-light text-info"><i class="bi bi-check-circle-fill"></i></span>
            </div>
            <div class="h3 fw-bolder text-primary mb-1">{{ $metrics['delivered'] }}</div>
            <div class="text-muted small" style="font-size: 0.72rem;">Successfully delivered</div>
        </div>
    </div>
</div>

<!-- Filter Status Tabs for Today -->
<div class="d-flex align-items-center gap-2 overflow-auto pb-2 mb-4">
    <a href="{{ route('order-manager.orders.today') }}" 
       class="btn btn-sm rounded-pill px-3.5 py-1.5 fw-bold {{ !request()->filled('status') ? 'btn-primary' : 'btn-light border text-secondary' }}" 
       style="{{ !request()->filled('status') ? 'background: #0284c7; border-color: #0284c7;' : '' }}">
        All Today ({{ $metrics['total_orders'] }})
    </a>
    <a href="{{ route('order-manager.orders.today', ['status' => 'pending,confirmed,processing']) }}" 
       class="btn btn-sm rounded-pill px-3.5 py-1.5 fw-bold {{ request()->get('status') === 'pending,confirmed,processing' ? 'btn-warning text-dark' : 'btn-light border text-secondary' }}">
        Needs Action / Processing ({{ $metrics['needs_action'] }})
    </a>
    <a href="{{ route('order-manager.orders.today', ['status' => 'shipped,out for delivery']) }}" 
       class="btn btn-sm rounded-pill px-3.5 py-1.5 fw-bold {{ request()->get('status') === 'shipped,out for delivery' ? 'btn-info text-white' : 'btn-light border text-secondary' }}"
       style="{{ request()->get('status') === 'shipped,out for delivery' ? 'background: #0284c7; border-color: #0284c7;' : '' }}">
        Out for Delivery ({{ $metrics['out_for_delivery'] }})
    </a>
    <a href="{{ route('order-manager.orders.today', ['status' => 'delivered']) }}" 
       class="btn btn-sm rounded-pill px-3.5 py-1.5 fw-bold {{ request()->get('status') === 'delivered' ? 'btn-success text-white' : 'btn-light border text-secondary' }}">
        Delivered ({{ $metrics['delivered'] }})
    </a>
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
                    <th class="py-3">Customer &amp; Address</th>
                    <th class="py-3">Items</th>
                    <th class="py-3">Amount &amp; Pay</th>
                    <th class="py-3">Status</th>
                    <th class="py-3">Delivery Partner</th>
                    <th class="pe-4 py-3 text-end">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y">
                @forelse($orders as $order)
                    @php
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
                                Placed {{ $order->created_at->format('h:i A') }}
                            </div>
                        </td>

                        <td>
                            <div class="fw-semibold text-dark small">{{ $order->shipping_name }}</div>
                            <div class="text-muted small text-truncate" style="font-size: 0.72rem; max-width: 220px;">
                                <i class="bi bi-geo-alt me-0.5"></i>{{ $order->shipping_address }}, {{ $order->shipping_city }}
                            </div>
                            <div class="text-muted small" style="font-size: 0.7rem;">
                                <i class="bi bi-telephone me-0.5"></i>{{ $order->shipping_phone }}
                            </div>
                        </td>

                        <td>
                            <span class="badge bg-light text-secondary border px-2 py-1 rounded-pill small fw-bold">
                                {{ $order->items->sum('quantity') }} items
                            </span>
                            <div class="text-muted text-truncate" style="font-size: 0.68rem; max-width: 140px;">
                                {{ $order->items->first()?->product_name ?? 'Parcel' }}
                            </div>
                        </td>

                        <td>
                            <div class="fw-bold text-dark small">₹{{ number_format($order->total_amount, 2) }}</div>
                            <div class="d-flex align-items-center gap-1 mt-0.5">
                                <span class="badge {{ $order->payment_method === 'cod' ? 'bg-warning text-dark' : 'bg-success' }}" style="font-size: 0.62rem; text-transform: uppercase;">
                                    {{ $order->payment_method }}
                                </span>
                            </div>
                        </td>

                        <td>
                            <span class="badge rounded-pill px-2.5 py-1 fw-bold" style="background: {{ $statusBadge['bg'] }}; color: {{ $statusBadge['color'] }}; font-size: 0.72rem;">
                                {{ $statusBadge['label'] }}
                            </span>
                        </td>

                        <td>
                            @if($order->rider_name)
                                <div class="fw-bold text-dark small" style="font-size: 0.75rem;">
                                    <i class="bi bi-person-badge text-primary me-0.5"></i>{{ $order->rider_name }}
                                </div>
                            @else
                                <span class="badge bg-secondary bg-opacity-10 text-secondary border border-secondary border-opacity-25 rounded-pill px-2 py-0.5" style="font-size: 0.65rem;">
                                    Unassigned
                                </span>
                            @endif
                        </td>

                        <td class="pe-4 text-end">
                            <div class="d-flex align-items-center justify-content-end gap-1.5 flex-nowrap">
                                <a href="{{ route('order-manager.orders.show', $order) }}" class="action-btn-circle action-btn-view" title="Process Order">
                                    <i class="bi bi-arrow-right-circle-fill"></i>
                                </a>
                                <a href="{{ route('order-manager.orders.shipping-label', $order) }}" target="_blank" class="action-btn-circle" style="background: #0f172a; color: #f59e0b;" title="Print 4x6 Thermal Barcode Sticker">
                                    <i class="bi bi-tag-fill"></i>
                                </a>
                                <a href="{{ route('order-manager.orders.packing-slip', $order) }}" target="_blank" class="action-btn-circle action-btn-invoice" title="Print Packing Slip">
                                    <i class="bi bi-printer-fill"></i>
                                </a>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="text-center py-5">
                            <div class="py-4">
                                <i class="bi bi-calendar-check text-muted opacity-50 display-6 mb-3 d-block"></i>
                                <h6 class="fw-bold text-dark mb-1">No Orders Placed Today Yet</h6>
                                <p class="text-muted mb-0 small">New orders placed by customers today will automatically appear here.</p>
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

<!-- ========================================================================================= -->
<!-- FLOATING BULK SUPER-BAR (Multi-Select Status Change, Rider Assign & Barcode Labels Print) -->
<!-- ========================================================================================= -->
<div id="bulkSuperBar" class="position-fixed bottom-0 start-50 translate-middle-x mb-4 shadow-lg p-3 bg-dark text-white rounded-4 d-none align-items-center gap-3 flex-wrap" style="z-index: 1050; border: 2px solid #0284c7; min-width: 720px; max-width: 95vw;">
    <div class="d-flex align-items-center gap-2 border-end pe-3">
        <span class="badge bg-primary rounded-pill px-2.5 py-1 fw-bold font-monospace fs-6" id="bulkSelectedCount">0</span>
        <span class="small fw-bold text-white text-nowrap">Selected</span>
    </div>

    <!-- 1. Bulk Status Update Form -->
    <form action="{{ route('order-manager.orders.bulk-update-status') }}" method="POST" id="formBulkStatus" class="d-flex align-items-center gap-1.5">
        @csrf
        <input type="hidden" name="order_ids" class="bulk-order-ids-input" value="">
        <select name="new_status" class="form-select form-select-sm bg-secondary text-white border-0 fw-semibold" style="width: 160px; font-size: 0.78rem;">
            <option value="confirmed">Mark Confirmed</option>
            <option value="processing">Mark Processing</option>
            <option value="packed">Mark Packed</option>
            <option value="out for delivery">Mark Out for Delivery</option>
            <option value="delivered">Mark Delivered</option>
        </select>
        <button type="submit" class="btn btn-sm btn-primary rounded-3 px-3 py-1 fw-bold text-nowrap" style="font-size: 0.78rem;">
            Apply Status
        </button>
    </form>

    <!-- 2. Bulk Assign Rider Form -->
    <form action="{{ route('order-manager.orders.bulk-assign-rider') }}" method="POST" id="formBulkRider" class="d-flex align-items-center gap-1.5 border-start ps-3">
        @csrf
        <input type="hidden" name="order_ids" class="bulk-order-ids-input" value="">
        <select name="rider_id" class="form-select form-select-sm bg-secondary text-white border-0 fw-semibold" style="width: 150px; font-size: 0.78rem;">
            <option value="">Choose Rider...</option>
            @foreach($riders as $r)
                <option value="{{ $r->id }}">{{ $r->name }}</option>
            @endforeach
        </select>
        <button type="submit" class="btn btn-sm btn-warning text-dark rounded-3 px-3 py-1 fw-bold text-nowrap" style="font-size: 0.78rem;">
            Assign Rider
        </button>
    </form>

    <!-- 3. Direct Print & Export Buttons -->
    <div class="d-flex align-items-center gap-1.5 border-start ps-3 ms-auto">
        <button type="button" class="btn btn-sm btn-outline-warning rounded-3 px-2.5 py-1 fw-bold" onclick="submitBulkPrintLabels()" title="Print 4x6 Thermal Barcodes for Selected">
            <i class="bi bi-tag-fill"></i> Print Stickers
        </button>
        <button type="button" class="btn btn-sm btn-outline-info rounded-3 px-2.5 py-1 fw-bold" onclick="submitBulkManifest()" title="Print Manifest for Selected">
            <i class="bi bi-printer-fill"></i> Manifest
        </button>
        <button type="button" class="btn btn-sm btn-outline-light rounded-circle ms-2" onclick="clearAllSelections()" title="Deselect All">
            <i class="bi bi-x-lg"></i>
        </button>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const selectAll = document.getElementById('selectAllOrders');
        const checkboxes = document.querySelectorAll('.order-checkbox');
        const bulkBar = document.getElementById('bulkSuperBar');
        const countBadge = document.getElementById('bulkSelectedCount');
        const inputs = document.querySelectorAll('.bulk-order-ids-input');

        function updateSuperBar() {
            const checked = Array.from(document.querySelectorAll('.order-checkbox:checked')).map(cb => cb.value);
            const count = checked.length;
            if (count > 0) {
                bulkBar.classList.remove('d-none');
                bulkBar.classList.add('d-flex');
                countBadge.textContent = count;
                inputs.forEach(inp => inp.value = checked.join(','));
            } else {
                bulkBar.classList.remove('d-flex');
                bulkBar.classList.add('d-none');
                inputs.forEach(inp => inp.value = '');
            }
        }

        if (selectAll) {
            selectAll.addEventListener('change', function() {
                checkboxes.forEach(cb => cb.checked = selectAll.checked);
                updateSuperBar();
            });
        }

        checkboxes.forEach(cb => {
            cb.addEventListener('change', function() {
                if (!this.checked && selectAll) {
                    selectAll.checked = false;
                }
                updateSuperBar();
            });
        });

        window.clearAllSelections = function() {
            checkboxes.forEach(cb => cb.checked = false);
            if (selectAll) selectAll.checked = false;
            updateSuperBar();
        };

        window.submitBulkPrintLabels = function() {
            const checked = Array.from(document.querySelectorAll('.order-checkbox:checked')).map(cb => cb.value);
            if (checked.length === 0) return;
            const url = "{{ route('order-manager.orders.bulk-shipping-labels') }}?order_ids=" + checked.join(',');
            window.open(url, '_blank');
        };

        window.submitBulkManifest = function() {
            const checked = Array.from(document.querySelectorAll('.order-checkbox:checked')).map(cb => cb.value);
            if (checked.length === 0) return;
            const url = "{{ route('order-manager.orders.daily-manifest') }}?order_ids=" + checked.join(',');
            window.open(url, '_blank');
        };
    });
</script>

@endsection
