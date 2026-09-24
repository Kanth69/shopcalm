@extends('admin.layouts.app')

@section('header', 'Statements & Report Generator')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item active" aria-current="page">Statements & Reports</li>
@endsection

@section('actions')
    <div class="d-flex align-items-center gap-2">
        <button onclick="window.print()" class="btn btn-outline-secondary rounded-pill px-3 shadow-xs fw-semibold" style="font-size: 0.85rem;">
            <i class="bi bi-printer me-1"></i> Print Statement
        </button>
        <a href="{{ request()->fullUrlWithQuery(['export' => 1]) }}" class="btn btn-success rounded-pill px-3.5 shadow-xs fw-semibold" style="font-size: 0.85rem;">
            <i class="bi bi-download me-1"></i> Download CSV
        </a>
    </div>
@endsection

@section('content')

{{-- Print Only Statement Header --}}
<div class="d-none d-print-block mb-4 border-bottom pb-3">
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h3 class="fw-bold mb-0 text-dark">ShopCalm Store Statement</h3>
            <p class="text-muted small mb-0">Official Business Statement • Confidential</p>
        </div>
        <div class="text-end small">
            <div><strong>Report:</strong> {{ strtoupper(str_replace('_', ' ', $type)) }}</div>
            <div><strong>Period:</strong> {{ \Carbon\Carbon::parse($start)->format('d M Y') }} to {{ \Carbon\Carbon::parse($end)->format('d M Y') }}</div>
            <div><strong>Printed On:</strong> {{ now()->format('d M Y, h:i A') }}</div>
        </div>
    </div>
</div>

{{-- 1. Report Type Selector Bar --}}
<div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-3 d-print-none">
    <div class="card-body p-2.5">
        <div class="d-flex gap-2 overflow-auto py-1 align-items-center">
            <span class="text-muted small fw-bold px-2 text-uppercase" style="font-size: 0.72rem; letter-spacing: 0.5px;">Statement:</span>
            
            <a href="{{ route('admin.reports.index', ['type' => 'sales', 'start_date' => $start, 'end_date' => $end]) }}" 
               class="btn btn-sm {{ $type == 'sales' ? 'btn-primary text-white shadow-xs' : 'btn-light text-dark' }} rounded-pill px-3 py-1.5 fw-semibold d-inline-flex align-items-center flex-shrink-0" style="font-size: 0.82rem;">
                <i class="bi bi-receipt me-1.5"></i> Sales Statement
            </a>

            <a href="{{ route('admin.reports.index', ['type' => 'orders', 'start_date' => $start, 'end_date' => $end]) }}" 
               class="btn btn-sm {{ $type == 'orders' ? 'btn-primary text-white shadow-xs' : 'btn-light text-dark' }} rounded-pill px-3 py-1.5 fw-semibold d-inline-flex align-items-center flex-shrink-0" style="font-size: 0.82rem;">
                <i class="bi bi-bag-check me-1.5"></i> Orders Registry
            </a>

            <a href="{{ route('admin.reports.index', ['type' => 'revenue', 'start_date' => $start, 'end_date' => $end]) }}" 
               class="btn btn-sm {{ $type == 'revenue' ? 'btn-primary text-white shadow-xs' : 'btn-light text-dark' }} rounded-pill px-3 py-1.5 fw-semibold d-inline-flex align-items-center flex-shrink-0" style="font-size: 0.82rem;">
                <i class="bi bi-calendar-check me-1.5"></i> Daily Revenue
            </a>

            <a href="{{ route('admin.reports.index', ['type' => 'products', 'start_date' => $start, 'end_date' => $end]) }}" 
               class="btn btn-sm {{ $type == 'products' ? 'btn-primary text-white shadow-xs' : 'btn-light text-dark' }} rounded-pill px-3 py-1.5 fw-semibold d-inline-flex align-items-center flex-shrink-0" style="font-size: 0.82rem;">
                <i class="bi bi-box-seam me-1.5"></i> Product Sales
            </a>

            <a href="{{ route('admin.reports.index', ['type' => 'customers', 'start_date' => $start, 'end_date' => $end]) }}" 
               class="btn btn-sm {{ $type == 'customers' ? 'btn-primary text-white shadow-xs' : 'btn-light text-dark' }} rounded-pill px-3 py-1.5 fw-semibold d-inline-flex align-items-center flex-shrink-0" style="font-size: 0.82rem;">
                <i class="bi bi-people me-1.5"></i> Customer Spending
            </a>

            @if(auth()->user()->isSuperAdmin())
            <a href="{{ route('admin.reports.index', ['type' => 'profit_loss', 'start_date' => $start, 'end_date' => $end]) }}" 
               class="btn btn-sm {{ $type == 'profit_loss' ? 'btn-dark text-warning border-warning shadow-xs' : 'btn-light text-dark' }} rounded-pill px-3 py-1.5 fw-bold d-inline-flex align-items-center flex-shrink-0 border" style="font-size: 0.82rem;">
                <i class="bi bi-piggy-bank text-warning me-1.5"></i> Profit & Loss (P&L)
                <span class="badge bg-warning text-dark ms-1.5 rounded-pill" style="font-size: 0.62rem;">SUPER</span>
            </a>
            @endif
        </div>
    </div>
</div>

{{-- 2. Report Controls & Filters Bar --}}
<div class="card border-0 shadow-sm rounded-4 mb-4 d-print-none bg-white">
    <div class="card-body p-3.5">
        <form method="GET" action="{{ route('admin.reports.index') }}" class="row g-3 align-items-center">
            <input type="hidden" name="type" value="{{ $type }}">
            
            {{-- Quick Presets (5 compact pills) --}}
            <div class="col-12 col-xl-5">
                <div class="d-flex align-items-center gap-1 flex-wrap">
                    <span class="text-muted small fw-bold me-1 text-uppercase" style="font-size: 0.68rem; letter-spacing: 0.5px;">Period:</span>
                    <a href="{{ route('admin.reports.index', ['type' => $type, 'preset' => 'today']) }}" 
                       class="btn btn-sm rounded-pill px-2.5 py-1 {{ ($preset ?? '') == 'today' ? 'btn-primary fw-bold' : 'btn-light border text-dark' }}" style="font-size: 0.75rem;">
                        Today
                    </a>
                    <a href="{{ route('admin.reports.index', ['type' => $type, 'preset' => '7_days']) }}" 
                       class="btn btn-sm rounded-pill px-2.5 py-1 {{ ($preset ?? '') == '7_days' || ($preset ?? '') == 'this_week' ? 'btn-primary fw-bold' : 'btn-light border text-dark' }}" style="font-size: 0.75rem;">
                        7 Days
                    </a>
                    <a href="{{ route('admin.reports.index', ['type' => $type, 'preset' => 'this_month']) }}" 
                       class="btn btn-sm rounded-pill px-2.5 py-1 {{ ($preset ?? '') == 'this_month' || (empty($preset) && !request()->has('start_date')) ? 'btn-primary fw-bold' : 'btn-light border text-dark' }}" style="font-size: 0.75rem;">
                        This Month
                    </a>
                    <a href="{{ route('admin.reports.index', ['type' => $type, 'preset' => 'this_year']) }}" 
                       class="btn btn-sm rounded-pill px-2.5 py-1 {{ ($preset ?? '') == 'this_year' ? 'btn-primary fw-bold' : 'btn-light border text-dark' }}" style="font-size: 0.75rem;">
                        This Year
                    </a>
                    <a href="{{ route('admin.reports.index', ['type' => $type, 'preset' => 'all_time']) }}" 
                       class="btn btn-sm rounded-pill px-2.5 py-1 {{ ($preset ?? '') == 'all_time' ? 'btn-primary fw-bold' : 'btn-light border text-dark' }}" style="font-size: 0.75rem;">
                        All Time
                    </a>
                </div>
            </div>

            {{-- Custom Date Pickers --}}
            <div class="col-sm-6 col-md-5 col-xl-4">
                <div class="input-group input-group-sm">
                    <span class="input-group-text bg-light text-muted small"><i class="bi bi-calendar3"></i></span>
                    <input type="date" name="start_date" class="form-control" value="{{ $start }}" title="From Date">
                    <span class="input-group-text bg-light text-muted small">&rarr;</span>
                    <input type="date" name="end_date" class="form-control" value="{{ $end }}" title="To Date">
                </div>
            </div>

            {{-- Action Buttons --}}
            <div class="col-sm-6 col-md-7 col-xl-3 d-flex align-items-center gap-2 justify-content-sm-end">
                <button type="submit" class="btn btn-sm btn-primary rounded-pill px-3 fw-bold">
                    <i class="bi bi-funnel me-1"></i> Apply
                </button>
                @if(request()->hasAny(['start_date', 'end_date', 'preset']))
                    <a href="{{ route('admin.reports.index', ['type' => $type]) }}" class="btn btn-sm btn-outline-secondary rounded-pill px-2.5" title="Reset Filters">
                        <i class="bi bi-arrow-counterclockwise"></i>
                    </a>
                @endif
                <a href="{{ request()->fullUrlWithQuery(['export' => 1]) }}" class="btn btn-sm btn-success rounded-pill px-3 fw-bold text-white shadow-xs" title="Download Excel/CSV Report">
                    <i class="bi bi-download me-1"></i> CSV
                </a>
            </div>
        </form>
    </div>
</div>

{{-- 3. Statement Header & Live In-Table Search Banner --}}
@php
    $recordCount = is_countable($data) ? count($data) : 0;
    $sumAmount = 0;
    if ($type === 'sales' || $type === 'orders') {
        $sumAmount = $data->sum('total_amount');
    } elseif ($type === 'revenue') {
        $sumAmount = $data->sum('revenue');
    } elseif ($type === 'products') {
        $sumAmount = $data->sum('revenue');
    } elseif ($type === 'customers') {
        $sumAmount = $data->sum('total_spent');
    } elseif ($type === 'profit_loss') {
        $sumAmount = $data->sum('total_amount');
        $sumCost = $data->sum('total_cost');
        $sumProfit = $data->sum('gross_profit');
    }
@endphp

<div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-3">
    <div class="card-body p-3 bg-light border-bottom d-flex flex-wrap align-items-center justify-content-between gap-3">
        <div class="d-flex align-items-center gap-3">
            <div class="badge bg-dark rounded-pill px-3 py-2 text-uppercase fw-bold" style="font-size: 0.75rem; letter-spacing: 0.5px;">
                {{ str_replace('_', ' ', $type) }} Statement
            </div>
            <span class="text-muted small">
                Showing <strong>{{ $recordCount }}</strong> entries for 
                <strong>{{ \Carbon\Carbon::parse($start)->format('d M Y') }}</strong> &rarr; <strong>{{ \Carbon\Carbon::parse($end)->format('d M Y') }}</strong>
            </span>
            @if($sumAmount > 0)
                <span class="badge bg-white text-dark border rounded-pill px-3 py-1.5 fw-bold small">
                    Statement Total: <span class="text-primary font-monospace">₹{{ number_format($sumAmount, 2) }}</span>
                </span>
            @endif
            @if($type === 'profit_loss' && isset($sumProfit))
                <span class="badge bg-white text-dark border rounded-pill px-3 py-1.5 fw-bold small">
                    Net Profit: <span class="{{ $sumProfit >= 0 ? 'text-success' : 'text-danger' }} font-monospace">
                        {{ $sumProfit >= 0 ? '+' : '' }}₹{{ number_format($sumProfit, 2) }}
                    </span>
                </span>
            @endif
        </div>

        {{-- Live Search Filter --}}
        <div class="d-print-none" style="min-width: 240px;">
            <div class="input-group input-group-sm">
                <span class="input-group-text bg-white border-end-0"><i class="bi bi-search text-muted"></i></span>
                <input type="text" id="statementSearch" class="form-control border-start-0 ps-0" placeholder="Quick filter rows..." onkeyup="filterReportTable()">
            </div>
        </div>
    </div>

    {{-- 4. Clean Ledger Data Table --}}
    <div class="table-responsive bg-white">
        <table class="table table-hover align-middle mb-0" id="reportLedgerTable">
            
            {{-- CASE A: SALES STATEMENT --}}
            @if($type == 'sales')
            <thead class="table-light text-uppercase small" style="font-size: 0.75rem; letter-spacing: 0.5px;">
                <tr>
                    <th class="ps-3">Order #</th>
                    <th>Date & Time</th>
                    <th>Customer</th>
                    <th>Payment Method</th>
                    <th class="text-center">Items</th>
                    <th class="text-end pe-3">Revenue (₹)</th>
                </tr>
            </thead>
            <tbody>
                @forelse($data as $order)
                <tr>
                    <td class="ps-3">
                        <a href="{{ route('admin.orders.show', $order) }}" class="fw-bold text-primary text-decoration-none">
                            #{{ $order->order_number }}
                        </a>
                    </td>
                    <td class="small text-muted">{{ $order->created_at->format('d M Y, h:i A') }}</td>
                    <td class="fw-semibold text-dark small">{{ $order->user?->name ?? 'Guest' }}</td>
                    <td>
                        <span class="badge bg-light text-dark border text-uppercase" style="font-size: 0.72rem;">
                            {{ $order->payment_method ?? 'COD' }}
                        </span>
                    </td>
                    <td class="text-center font-monospace small">{{ $order->items->sum('quantity') }}</td>
                    <td class="text-end pe-3 fw-bold text-dark font-monospace">₹{{ number_format($order->total_amount, 2) }}</td>
                </tr>
                @empty
                <tr><td colspan="6" class="text-center py-5 text-muted small">No sales records found in this date range.</td></tr>
                @endforelse
            </tbody>
            @if($data->count() > 0)
            <tfoot class="table-light fw-bold border-top">
                <tr>
                    <td colspan="4" class="ps-3 text-uppercase small">Total Summary ({{ $data->count() }} Orders)</td>
                    <td class="text-center font-monospace">{{ $data->sum(fn($o) => $o->items->sum('quantity')) }}</td>
                    <td class="text-end pe-3 font-monospace text-primary fs-6">₹{{ number_format($sumAmount, 2) }}</td>
                </tr>
            </tfoot>
            @endif

            {{-- CASE B: ORDERS REGISTRY --}}
            @elseif($type == 'orders')
            <thead class="table-light text-uppercase small" style="font-size: 0.75rem; letter-spacing: 0.5px;">
                <tr>
                    <th class="ps-3">Order #</th>
                    <th>Date</th>
                    <th>Customer</th>
                    <th>Fulfillment Status</th>
                    <th>Payment Status</th>
                    <th class="text-end pe-3">Amount (₹)</th>
                </tr>
            </thead>
            <tbody>
                @forelse($data as $order)
                <tr>
                    <td class="ps-3">
                        <a href="{{ route('admin.orders.show', $order) }}" class="fw-bold text-primary text-decoration-none">
                            #{{ $order->order_number }}
                        </a>
                    </td>
                    <td class="small text-muted">{{ $order->created_at->format('d M Y') }}</td>
                    <td class="fw-semibold text-dark small">{{ $order->user?->name ?? 'Guest' }}</td>
                    <td>
                        <span class="badge rounded-pill px-2.5 py-1 text-uppercase" style="font-size: 0.68rem; background: {{ $order->status == 'delivered' ? '#10b981' : ($order->status == 'cancelled' ? '#ef4444' : '#6366f1') }}; color: #fff;">
                            {{ $order->status }}
                        </span>
                    </td>
                    <td>
                        <span class="badge rounded-pill px-2.5 py-1 text-uppercase {{ $order->payment_status == 'paid' ? 'bg-success text-white' : 'bg-warning text-dark' }}" style="font-size: 0.68rem;">
                            {{ $order->payment_status }}
                        </span>
                    </td>
                    <td class="text-end pe-3 fw-bold text-dark font-monospace">₹{{ number_format($order->total_amount, 2) }}</td>
                </tr>
                @empty
                <tr><td colspan="6" class="text-center py-5 text-muted small">No order records found in this range.</td></tr>
                @endforelse
            </tbody>
            @if($data->count() > 0)
            <tfoot class="table-light fw-bold border-top">
                <tr>
                    <td colspan="5" class="ps-3 text-uppercase small">Total Registry Amount</td>
                    <td class="text-end pe-3 font-monospace text-primary fs-6">₹{{ number_format($sumAmount, 2) }}</td>
                </tr>
            </tfoot>
            @endif

            {{-- CASE C: PROFIT & LOSS LEDGER (SUPER ADMIN) --}}
            @elseif($type == 'profit_loss')
            <thead class="table-dark text-uppercase small" style="font-size: 0.74rem; letter-spacing: 0.5px;">
                <tr>
                    <th class="ps-3">Order #</th>
                    <th>Date</th>
                    <th>Customer</th>
                    <th class="text-end">Revenue Paid</th>
                    <th class="text-end">Product Cost</th>
                    <th class="text-end">Gross Profit</th>
                    <th class="pe-3 text-center">Margin %</th>
                </tr>
            </thead>
            <tbody>
                @forelse($data as $order)
                <tr>
                    <td class="ps-3">
                        <a href="{{ route('admin.orders.show', $order) }}" class="fw-bold text-primary text-decoration-none font-monospace">
                            #{{ $order->order_number }}
                        </a>
                    </td>
                    <td class="small text-muted">{{ $order->created_at->format('d M Y') }}</td>
                    <td class="fw-medium text-dark small">{{ $order->user?->name ?? 'Guest' }}</td>
                    <td class="text-end fw-semibold font-monospace">₹{{ number_format($order->total_amount, 2) }}</td>
                    <td class="text-end text-muted font-monospace small">₹{{ number_format($order->total_cost, 2) }}</td>
                    <td class="text-end fw-bold font-monospace {{ $order->gross_profit >= 0 ? 'text-success' : 'text-danger' }}">
                        {{ $order->gross_profit >= 0 ? '+' : '' }}₹{{ number_format($order->gross_profit, 2) }}
                    </td>
                    <td class="pe-3 text-center">
                        <span class="badge rounded-pill px-2 py-0.5 fw-bold {{ $order->profit_margin >= 20 ? 'bg-success text-white' : ($order->profit_margin >= 0 ? 'bg-warning text-dark' : 'bg-danger text-white') }}" style="font-size: 0.72rem;">
                            {{ $order->profit_margin }}%
                        </span>
                    </td>
                </tr>
                @empty
                <tr><td colspan="7" class="text-center py-5 text-muted small">No fulfilled orders for Profit & Loss calculation.</td></tr>
                @endforelse
            </tbody>
            @if($data->count() > 0)
            <tfoot class="table-light fw-bold border-top">
                <tr>
                    <td colspan="3" class="ps-3 text-uppercase small">Total P&L Summary</td>
                    <td class="text-end font-monospace">₹{{ number_format($sumAmount, 2) }}</td>
                    <td class="text-end font-monospace text-danger">₹{{ number_format($sumCost ?? 0, 2) }}</td>
                    <td class="text-end font-monospace {{ ($sumProfit ?? 0) >= 0 ? 'text-success' : 'text-danger' }} fs-6">
                        {{ ($sumProfit ?? 0) >= 0 ? '+' : '' }}₹{{ number_format($sumProfit ?? 0, 2) }}
                    </td>
                    <td class="pe-3 text-center font-monospace">
                        {{ $sumAmount > 0 ? round((($sumProfit ?? 0) / $sumAmount) * 100, 1) : 0 }}%
                    </td>
                </tr>
            </tfoot>
            @endif

            {{-- CASE D: DAILY REVENUE STATEMENT --}}
            @elseif($type == 'revenue')
            <thead class="table-light text-uppercase small" style="font-size: 0.75rem; letter-spacing: 0.5px;">
                <tr>
                    <th class="ps-3">Date</th>
                    <th class="text-center">Orders Count</th>
                    <th class="text-end pe-3">Revenue (₹)</th>
                </tr>
            </thead>
            <tbody>
                @forelse($data as $day)
                <tr>
                    <td class="ps-3 fw-bold text-dark font-monospace">{{ \Carbon\Carbon::parse($day->date)->format('d M Y (D)') }}</td>
                    <td class="text-center">
                        <span class="badge bg-light text-dark border font-monospace px-2.5 py-1">{{ $day->orders_count }} orders</span>
                    </td>
                    <td class="text-end pe-3 fw-bold text-success font-monospace">₹{{ number_format($day->revenue, 2) }}</td>
                </tr>
                @empty
                <tr><td colspan="3" class="text-center py-5 text-muted small">No revenue records found.</td></tr>
                @endforelse
            </tbody>
            @if($data->count() > 0)
            <tfoot class="table-light fw-bold border-top">
                <tr>
                    <td class="ps-3 text-uppercase small">Total Daily Revenue</td>
                    <td class="text-center font-monospace">{{ $data->sum('orders_count') }} Orders</td>
                    <td class="text-end pe-3 font-monospace text-primary fs-6">₹{{ number_format($sumAmount, 2) }}</td>
                </tr>
            </tfoot>
            @endif

            {{-- CASE E: PRODUCTS SALES VELOCITY --}}
            @elseif($type == 'products')
            <thead class="table-light text-uppercase small" style="font-size: 0.75rem; letter-spacing: 0.5px;">
                <tr>
                    <th class="ps-3">Product Name</th>
                    <th class="text-center">Units Sold</th>
                    <th class="text-end pe-3">Total Sales Revenue (₹)</th>
                </tr>
            </thead>
            <tbody>
                @forelse($data as $prod)
                <tr>
                    <td class="ps-3 fw-semibold text-dark">{{ $prod->product_name ?? 'Unknown' }}</td>
                    <td class="text-center">
                        <span class="badge bg-light text-dark border font-monospace px-2.5 py-1">{{ $prod->total_qty }} units</span>
                    </td>
                    <td class="text-end pe-3 fw-bold text-dark font-monospace">₹{{ number_format($prod->revenue, 2) }}</td>
                </tr>
                @empty
                <tr><td colspan="3" class="text-center py-5 text-muted small">No product sales found.</td></tr>
                @endforelse
            </tbody>
            @if($data->count() > 0)
            <tfoot class="table-light fw-bold border-top">
                <tr>
                    <td class="ps-3 text-uppercase small">Total Product Sales</td>
                    <td class="text-center font-monospace">{{ $data->sum('total_qty') }} Units</td>
                    <td class="text-end pe-3 font-monospace text-primary fs-6">₹{{ number_format($sumAmount, 2) }}</td>
                </tr>
            </tfoot>
            @endif

            {{-- CASE F: CUSTOMERS SPENDING STATEMENT --}}
            @elseif($type == 'customers')
            <thead class="table-light text-uppercase small" style="font-size: 0.75rem; letter-spacing: 0.5px;">
                <tr>
                    <th class="ps-3">Customer Name</th>
                    <th>Email</th>
                    <th class="text-center">Completed Orders</th>
                    <th class="text-end pe-3">Total Lifetime Spend (₹)</th>
                </tr>
            </thead>
            <tbody>
                @forelse($data as $cust)
                <tr>
                    <td class="ps-3 fw-semibold text-dark">{{ $cust->name }}</td>
                    <td class="small text-muted">{{ $cust->email }}</td>
                    <td class="text-center">
                        <span class="badge bg-light text-dark border font-monospace px-2.5 py-1">{{ $cust->orders_count }} orders</span>
                    </td>
                    <td class="text-end pe-3 fw-bold text-success font-monospace">₹{{ number_format($cust->total_spent, 2) }}</td>
                </tr>
                @empty
                <tr><td colspan="4" class="text-center py-5 text-muted small">No customer spending found.</td></tr>
                @endforelse
            </tbody>
            @if($data->count() > 0)
            <tfoot class="table-light fw-bold border-top">
                <tr>
                    <td colspan="2" class="ps-3 text-uppercase small">Total Customer Spending</td>
                    <td class="text-center font-monospace">{{ $data->sum('orders_count') }} Orders</td>
                    <td class="text-end pe-3 font-monospace text-primary fs-6">₹{{ number_format($sumAmount, 2) }}</td>
                </tr>
            </tfoot>
            @endif
            @endif

        </table>
    </div>
</div>

@push('scripts')
<script>
function filterReportTable() {
    const input = document.getElementById("statementSearch");
    const filter = input.value.toLowerCase();
    const table = document.getElementById("reportLedgerTable");
    const tr = table.getElementsByTagName("tbody")[0].getElementsByTagName("tr");

    for (let i = 0; i < tr.length; i++) {
        const text = tr[i].textContent || tr[i].innerText;
        if (text.toLowerCase().indexOf(filter) > -1) {
            tr[i].style.display = "";
        } else {
            tr[i].style.display = "none";
        }
    }
}
</script>
<style>
@media print {
    .sidebar, .navbar, .breadcrumb, .d-print-none, #statementSearch, .btn {
        display: none !important;
    }
    .main-content, .card, body {
        padding: 0 !important;
        margin: 0 !important;
        background: #fff !important;
        box-shadow: none !important;
        border: none !important;
    }
    .table {
        border-collapse: collapse !important;
        width: 100% !important;
        font-size: 10pt !important;
    }
    .table th, .table td {
        border: 1px solid #ddd !important;
        padding: 6px 8px !important;
    }
}
</style>
@endpush

@endsection
