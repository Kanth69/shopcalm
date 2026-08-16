@extends('admin.layouts.app')

@section('header', 'Business & Financial Reports')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item active" aria-current="page">Reports & Analytics</li>
@endsection

@section('actions')
    <a href="{{ request()->fullUrlWithQuery(['export' => 1]) }}" class="btn btn-success rounded-pill px-3 shadow-sm">
        <i class="bi bi-file-earmark-spreadsheet me-1"></i> Export to CSV
    </a>
@endsection

@section('content')

<!-- Report Format Navigation Tabs -->
<div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-4">
    <div class="card-body p-3">
        <div class="d-flex gap-2 overflow-auto py-1">
            <a href="{{ route('admin.reports.index', ['type' => 'sales', 'start_date' => $start, 'end_date' => $end]) }}" 
               class="btn {{ $type == 'sales' ? 'btn-primary text-white' : 'btn-light text-dark' }} rounded-pill px-3.5 py-2 fw-semibold d-inline-flex align-items-center flex-shrink-0">
                <i class="bi bi-cash-stack me-2"></i> Gross Sales
            </a>
            <a href="{{ route('admin.reports.index', ['type' => 'orders', 'start_date' => $start, 'end_date' => $end]) }}" 
               class="btn {{ $type == 'orders' ? 'btn-primary text-white' : 'btn-light text-dark' }} rounded-pill px-3.5 py-2 fw-semibold d-inline-flex align-items-center flex-shrink-0">
                <i class="bi bi-bag-check me-2"></i> Orders Registry
            </a>
            <a href="{{ route('admin.reports.index', ['type' => 'revenue', 'start_date' => $start, 'end_date' => $end]) }}" 
               class="btn {{ $type == 'revenue' ? 'btn-primary text-white' : 'btn-light text-dark' }} rounded-pill px-3.5 py-2 fw-semibold d-inline-flex align-items-center flex-shrink-0">
                <i class="bi bi-graph-up-arrow me-2"></i> Daily Revenue
            </a>
            <a href="{{ route('admin.reports.index', ['type' => 'customers', 'start_date' => $start, 'end_date' => $end]) }}" 
               class="btn {{ $type == 'customers' ? 'btn-primary text-white' : 'btn-light text-dark' }} rounded-pill px-3.5 py-2 fw-semibold d-inline-flex align-items-center flex-shrink-0">
                <i class="bi bi-people me-2"></i> Customer Spending
            </a>
            <a href="{{ route('admin.reports.index', ['type' => 'products', 'start_date' => $start, 'end_date' => $end]) }}" 
               class="btn {{ $type == 'products' ? 'btn-primary text-white' : 'btn-light text-dark' }} rounded-pill px-3.5 py-2 fw-semibold d-inline-flex align-items-center flex-shrink-0">
                <i class="bi bi-box-seam me-2"></i> Product Velocity
            </a>
        </div>
    </div>
</div>

<!-- Date Range Filter Card -->
<div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-4">
    <div class="card-body p-4">
        <form method="GET" action="{{ route('admin.reports.index') }}">
            <input type="hidden" name="type" value="{{ $type }}">
            <div class="row g-3 align-items-end">
                <div class="col-md-4">
                    <label class="form-label fw-bold text-dark small">Reporting Start Date</label>
                    <input type="date" name="start_date" class="form-control" value="{{ $start }}">
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-bold text-dark small">Reporting End Date</label>
                    <input type="date" name="end_date" class="form-control" value="{{ $end }}">
                </div>
                <div class="col-md-4 d-flex gap-2">
                    <button type="submit" class="btn btn-primary rounded-pill px-4 flex-grow-1 fw-bold">
                        <i class="bi bi-funnel me-1"></i> Apply Filter
                    </button>
                    @if(request()->hasAny(['start_date', 'end_date']))
                        <a href="{{ route('admin.reports.index', ['type' => $type]) }}" class="btn btn-outline-secondary rounded-pill px-3" title="Clear Date Filter and Reset">
                            <i class="bi bi-x-circle me-1"></i> Reset
                        </a>
                    @endif
                    <a href="{{ request()->fullUrlWithQuery(['export' => 1]) }}" class="btn btn-outline-success rounded-pill px-3" title="Download CSV Export">
                        <i class="bi bi-download"></i>
                    </a>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Dynamic KPI Metrics Cards -->
<div class="row g-3 mb-4">
    @if($type == 'sales' || $type == 'orders')
        <div class="col-sm-6 col-xl-4">
            <div class="card border-0 shadow-sm rounded-4 p-3 bg-white h-100">
                <div class="d-flex align-items-center gap-3">
                    <div class="rounded-4 p-3 bg-primary bg-opacity-10 text-primary d-flex align-items-center justify-content-center" style="width: 52px; height: 52px;">
                        <i class="bi bi-receipt fs-4"></i>
                    </div>
                    <div>
                        <div class="text-muted small fw-semibold text-uppercase" style="font-size: 0.72rem; letter-spacing: 0.5px;">Filtered Orders</div>
                        <div class="fs-4 fw-bold text-dark">{{ $data->count() }} Orders</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-4">
            <div class="card border-0 shadow-sm rounded-4 p-3 bg-white h-100">
                <div class="d-flex align-items-center gap-3">
                    <div class="rounded-4 p-3 bg-success bg-opacity-10 text-success d-flex align-items-center justify-content-center" style="width: 52px; height: 52px;">
                        <i class="bi bi-currency-rupee fs-4"></i>
                    </div>
                    <div>
                        <div class="text-muted small fw-semibold text-uppercase" style="font-size: 0.72rem; letter-spacing: 0.5px;">Gross Volume</div>
                        <div class="fs-4 fw-bold text-success">₹{{ number_format($data->sum('total_amount'), 2) }}</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-4">
            <div class="card border-0 shadow-sm rounded-4 p-3 bg-white h-100">
                <div class="d-flex align-items-center gap-3">
                    <div class="rounded-4 p-3 bg-info bg-opacity-10 text-info d-flex align-items-center justify-content-center" style="width: 52px; height: 52px;">
                        <i class="bi bi-calculator fs-4"></i>
                    </div>
                    <div>
                        <div class="text-muted small fw-semibold text-uppercase" style="font-size: 0.72rem; letter-spacing: 0.5px;">Average Ticket Size</div>
                        <div class="fs-4 fw-bold text-dark">
                            ₹{{ number_format($data->count() > 0 ? ($data->sum('total_amount') / $data->count()) : 0, 2) }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @elseif($type == 'revenue')
        <div class="col-sm-6 col-xl-6">
            <div class="card border-0 shadow-sm rounded-4 p-3 bg-white h-100">
                <div class="d-flex align-items-center gap-3">
                    <div class="rounded-4 p-3 bg-success bg-opacity-10 text-success d-flex align-items-center justify-content-center" style="width: 52px; height: 52px;">
                        <i class="bi bi-cash-coin fs-4"></i>
                    </div>
                    <div>
                        <div class="text-muted small fw-semibold text-uppercase" style="font-size: 0.72rem; letter-spacing: 0.5px;">Total Net Revenue</div>
                        <div class="fs-3 fw-bold text-success">₹{{ number_format($data->sum('total'), 2) }}</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-6">
            <div class="card border-0 shadow-sm rounded-4 p-3 bg-white h-100">
                <div class="d-flex align-items-center gap-3">
                    <div class="rounded-4 p-3 bg-primary bg-opacity-10 text-primary d-flex align-items-center justify-content-center" style="width: 52px; height: 52px;">
                        <i class="bi bi-calendar-check fs-4"></i>
                    </div>
                    <div>
                        <div class="text-muted small fw-semibold text-uppercase" style="font-size: 0.72rem; letter-spacing: 0.5px;">Active Sales Days</div>
                        <div class="fs-3 fw-bold text-dark">{{ $data->count() }} Days Recorded</div>
                    </div>
                </div>
            </div>
        </div>
    @elseif($type == 'customers')
        <div class="col-sm-6 col-xl-6">
            <div class="card border-0 shadow-sm rounded-4 p-3 bg-white h-100">
                <div class="d-flex align-items-center gap-3">
                    <div class="rounded-4 p-3 bg-primary bg-opacity-10 text-primary d-flex align-items-center justify-content-center" style="width: 52px; height: 52px;">
                        <i class="bi bi-people-fill fs-4"></i>
                    </div>
                    <div>
                        <div class="text-muted small fw-semibold text-uppercase" style="font-size: 0.72rem; letter-spacing: 0.5px;">Customer Base</div>
                        <div class="fs-3 fw-bold text-dark">{{ $data->count() }} Registered</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-6">
            <div class="card border-0 shadow-sm rounded-4 p-3 bg-white h-100">
                <div class="d-flex align-items-center gap-3">
                    <div class="rounded-4 p-3 bg-success bg-opacity-10 text-success d-flex align-items-center justify-content-center" style="width: 52px; height: 52px;">
                        <i class="bi bi-wallet2 fs-4"></i>
                    </div>
                    <div>
                        <div class="text-muted small fw-semibold text-uppercase" style="font-size: 0.72rem; letter-spacing: 0.5px;">Total Lifetime Spend</div>
                        <div class="fs-3 fw-bold text-success">₹{{ number_format($data->sum('orders_sum_total_amount'), 2) }}</div>
                    </div>
                </div>
            </div>
        </div>
    @elseif($type == 'products')
        <div class="col-sm-6 col-xl-6">
            <div class="card border-0 shadow-sm rounded-4 p-3 bg-white h-100">
                <div class="d-flex align-items-center gap-3">
                    <div class="rounded-4 p-3 bg-primary bg-opacity-10 text-primary d-flex align-items-center justify-content-center" style="width: 52px; height: 52px;">
                        <i class="bi bi-boxes fs-4"></i>
                    </div>
                    <div>
                        <div class="text-muted small fw-semibold text-uppercase" style="font-size: 0.72rem; letter-spacing: 0.5px;">Total Units Sold</div>
                        <div class="fs-3 fw-bold text-dark">{{ number_format($data->sum('sold_count')) }} Units</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-6">
            <div class="card border-0 shadow-sm rounded-4 p-3 bg-white h-100">
                <div class="d-flex align-items-center gap-3">
                    <div class="rounded-4 p-3 bg-success bg-opacity-10 text-success d-flex align-items-center justify-content-center" style="width: 52px; height: 52px;">
                        <i class="bi bi-tags fs-4"></i>
                    </div>
                    <div>
                        <div class="text-muted small fw-semibold text-uppercase" style="font-size: 0.72rem; letter-spacing: 0.5px;">Catalog Products</div>
                        <div class="fs-3 fw-bold text-dark">{{ $data->count() }} Items Analyzed</div>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>

<!-- Main Report Data Table Card -->
<div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-4">
    <div class="card-header bg-white py-3 border-bottom d-flex align-items-center justify-content-between">
        <div class="d-flex align-items-center gap-2">
            <i class="bi bi-table text-primary fs-5"></i>
            <h6 class="mb-0 fw-bold text-dark">
                @if($type == 'sales') Delivered Sales Ledger
                @elseif($type == 'orders') Orders Transaction Log
                @elseif($type == 'revenue') Daily Revenue Summary
                @elseif($type == 'customers') Customer Lifetime Value
                @elseif($type == 'products') Product Performance & Velocity
                @endif
            </h6>
        </div>
        <span class="badge bg-light text-dark border px-2.5 py-1 rounded-pill font-monospace small">
            {{ \Carbon\Carbon::parse($start)->format('d M Y') }} &rarr; {{ \Carbon\Carbon::parse($end)->format('d M Y') }}
        </span>
    </div>

    <div class="card-body p-0">
        @if($type == 'orders' || $type == 'sales')
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-4">Order #</th>
                            <th>Customer Name</th>
                            <th>Amount</th>
                            @if($type == 'orders') <th>Payment</th> @endif
                            <th>Order Status</th>
                            <th class="pe-4 text-end">Date Placed</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($data as $order)
                        <tr>
                            <td class="ps-4">
                                <a href="{{ route('admin.orders.show', $order) }}" class="fw-bold text-decoration-none text-primary font-monospace">
                                    {{ $order->order_number }}
                                </a>
                            </td>
                            <td>
                                <div class="fw-bold text-dark">{{ $order->user?->name ?? 'Guest/Deleted' }}</div>
                                <div class="small text-muted" style="font-size: 0.72rem;">{{ $order->user?->email ?? 'N/A' }}</div>
                            </td>
                            <td>
                                <span class="fw-bold text-dark">₹{{ number_format($order->total_amount, 2) }}</span>
                            </td>
                            @if($type == 'orders')
                                <td>
                                    @if(strtolower($order->payment_status) == 'paid')
                                        <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 rounded-pill px-2.5 py-1 small">
                                            <i class="bi bi-check-circle me-1"></i> Paid
                                        </span>
                                    @else
                                        <span class="badge bg-warning bg-opacity-10 text-warning border border-warning border-opacity-25 rounded-pill px-2.5 py-1 small">
                                            Pending
                                        </span>
                                    @endif
                                </td>
                            @endif
                            <td>
                                @php
                                    $statusMap = [
                                        'pending' => ['bg' => 'bg-warning bg-opacity-10 text-warning border-warning border-opacity-25', 'label' => 'Pending'],
                                        'confirmed' => ['bg' => 'bg-info bg-opacity-10 text-info border-info border-opacity-25', 'label' => 'Confirmed'],
                                        'packed' => ['bg' => 'bg-secondary bg-opacity-10 text-secondary border-secondary border-opacity-25', 'label' => 'Packed'],
                                        'shipped' => ['bg' => 'bg-primary bg-opacity-10 text-primary border-primary border-opacity-25', 'label' => 'Shipped'],
                                        'out_for_delivery' => ['bg' => 'bg-indigo bg-opacity-10 text-indigo border-indigo border-opacity-25', 'label' => 'Out for Delivery'],
                                        'delivered' => ['bg' => 'bg-success bg-opacity-10 text-success border-success border-opacity-25', 'label' => 'Delivered'],
                                        'cancelled' => ['bg' => 'bg-danger bg-opacity-10 text-danger border-danger border-opacity-25', 'label' => 'Cancelled'],
                                    ];
                                    $st = $statusMap[strtolower($order->status)] ?? ['bg' => 'bg-light text-dark border', 'label' => ucfirst($order->status)];
                                @endphp
                                <span class="badge {{ $st['bg'] }} border rounded-pill px-2.5 py-1 small">
                                    {{ $st['label'] }}
                                </span>
                            </td>
                            <td class="pe-4 text-end small text-muted">
                                {{ $order->created_at->format('d M Y, h:i A') }}
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center py-5 text-muted">
                                <i class="bi bi-journal-x text-muted opacity-50 display-6 d-block mb-2"></i>
                                <h6 class="fw-bold text-dark">No Orders Found for This Date Range</h6>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        @elseif($type == 'revenue')
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-4">Date</th>
                            <th class="text-center">Delivered Orders</th>
                            <th class="pe-4 text-end">Daily Gross Revenue</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($data as $row)
                        <tr>
                            <td class="ps-4 fw-bold text-dark">
                                {{ \Carbon\Carbon::parse($row->date)->format('d M Y, l') }}
                            </td>
                            <td class="text-center">
                                <span class="badge bg-light text-dark border rounded-pill px-2.5 py-1">
                                    {{ $row->order_count }} orders
                                </span>
                            </td>
                            <td class="pe-4 text-end fw-bold text-success fs-6">
                                ₹{{ number_format($row->total, 2) }}
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="3" class="text-center py-5 text-muted">
                                <i class="bi bi-calendar-x text-muted opacity-50 display-6 d-block mb-2"></i>
                                <h6 class="fw-bold text-dark">No Revenue Activity Recorded</h6>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                    @if($data->count() > 0)
                        <tfoot class="table-light border-top">
                            <tr>
                                <th class="ps-4 fw-bolder text-dark">AGGREGATE TOTAL</th>
                                <th class="text-center font-monospace fw-bold">{{ $data->sum('order_count') }} Orders</th>
                                <th class="pe-4 text-end text-success fw-bolder fs-5">₹{{ number_format($data->sum('total'), 2) }}</th>
                            </tr>
                        </tfoot>
                    @endif
                </table>
            </div>
        @elseif($type == 'customers')
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-4">Customer Details</th>
                            <th>Contact Information</th>
                            <th class="text-center">Total Orders Placed</th>
                            <th class="text-end">Lifetime Value (Paid)</th>
                            <th class="pe-4 text-end">Member Since</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($data as $customer)
                        <tr>
                            <td class="ps-4">
                                <div class="d-flex align-items-center gap-2.5">
                                    <div class="rounded-circle d-flex align-items-center justify-content-center text-white fw-bold shadow-xs flex-shrink-0" 
                                         style="width: 36px; height: 36px; background: #6366f1; font-size: 0.8rem;">
                                        {{ strtoupper(substr($customer->name, 0, 2)) }}
                                    </div>
                                    <div class="fw-bold text-dark">{{ $customer->name }}</div>
                                </div>
                            </td>
                            <td>
                                <div><a href="mailto:{{ $customer->email }}" class="text-decoration-none text-primary small">{{ $customer->email }}</a></div>
                                @if($customer->mobile_number)
                                    <div class="small text-muted" style="font-size: 0.72rem;">{{ $customer->mobile_number }}</div>
                                @endif
                            </td>
                            <td class="text-center">
                                <span class="badge bg-light text-dark border rounded-pill px-2.5 py-1">
                                    {{ $customer->orders_count }} orders
                                </span>
                            </td>
                            <td class="text-end fw-bold text-success">
                                ₹{{ number_format($customer->orders_sum_total_amount ?? 0, 2) }}
                            </td>
                            <td class="pe-4 text-end small text-muted">
                                {{ $customer->created_at->format('d M Y') }}
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="text-center py-5 text-muted">
                                <i class="bi bi-people text-muted opacity-50 display-6 d-block mb-2"></i>
                                <h6 class="fw-bold text-dark">No Customers Registered in This Period</h6>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        @elseif($type == 'products')
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-4">Product Name</th>
                            <th>SKU</th>
                            <th>Category</th>
                            <th>Unit Price</th>
                            <th class="text-center">Units Sold</th>
                            <th class="pe-4 text-end">Sales Revenue</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($data as $product)
                        <tr>
                            <td class="ps-4">
                                <div class="d-flex align-items-center gap-2.5">
                                    @if($product->main_image)
                                        <img src="{{ asset('storage/' . $product->main_image) }}" class="rounded border flex-shrink-0" style="width: 40px; height: 40px; object-fit: cover;">
                                    @else
                                        <div class="rounded bg-light d-flex align-items-center justify-content-center text-muted border flex-shrink-0" style="width: 40px; height: 40px;">
                                            <i class="bi bi-box"></i>
                                        </div>
                                    @endif
                                    <div class="fw-bold text-dark text-truncate" style="max-width: 260px;" title="{{ $product->name }}">
                                        {{ $product->name }}
                                    </div>
                                </div>
                            </td>
                            <td>
                                <code class="text-secondary small bg-light px-2 py-1 rounded">{{ $product->sku }}</code>
                            </td>
                            <td>
                                <span class="badge bg-light text-dark border rounded-pill px-2 py-1 small">
                                    {{ $product->category?->name ?? 'Unassigned' }}
                                </span>
                            </td>
                            <td class="fw-semibold text-dark">
                                ₹{{ number_format($product->price, 2) }}
                            </td>
                            <td class="text-center">
                                <span class="badge {{ ($product->sold_count ?? 0) > 0 ? 'bg-primary' : 'bg-light text-muted border' }} rounded-pill px-3 py-1 font-monospace">
                                    {{ $product->sold_count ?? 0 }}
                                </span>
                            </td>
                            <td class="pe-4 text-end fw-bold text-success">
                                ₹{{ number_format($product->total_sales_amount ?? 0, 2) }}
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center py-5 text-muted">
                                <i class="bi bi-box-seam text-muted opacity-50 display-6 d-block mb-2"></i>
                                <h6 class="fw-bold text-dark">No Product Movement Recorded</h6>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</div>

@endsection
