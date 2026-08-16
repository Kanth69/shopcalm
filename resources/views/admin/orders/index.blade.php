@extends('admin.layouts.app')

@section('header', 'Orders Management')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item active" aria-current="page">Orders</li>
@endsection

@section('content')

{{-- KPI Summary Stats --}}
<div class="row g-3 mb-4">
    <div class="col-6 col-lg-3">
        <div class="card border-0 shadow-sm rounded-4 p-3 h-100 bg-white">
            <div class="d-flex align-items-center justify-content-between mb-2">
                <span class="small text-muted fw-bold text-uppercase" style="font-size: 0.72rem; letter-spacing: 0.5px;">Total Orders</span>
                <div class="rounded-3 p-2 d-flex align-items-center justify-content-center" style="background: rgba(99, 102, 241, 0.1); color: #6366f1; width: 36px; height: 36px;">
                    <i class="bi bi-basket3-fill fs-6"></i>
                </div>
            </div>
            <div class="h3 fw-bold text-dark mb-0">{{ number_format($stats['total_orders']) }}</div>
            <div class="small text-muted mt-1" style="font-size: 0.75rem;">All lifetime customer orders</div>
        </div>
    </div>

    <div class="col-6 col-lg-3">
        <div class="card border-0 shadow-sm rounded-4 p-3 h-100 bg-white">
            <div class="d-flex align-items-center justify-content-between mb-2">
                <span class="small text-muted fw-bold text-uppercase" style="font-size: 0.72rem; letter-spacing: 0.5px;">Total Sales</span>
                <div class="rounded-3 p-2 d-flex align-items-center justify-content-center" style="background: rgba(16, 185, 129, 0.1); color: #10b981; width: 36px; height: 36px;">
                    <i class="bi bi-currency-rupee fs-6"></i>
                </div>
            </div>
            <div class="h3 fw-bold text-dark mb-0">₹{{ number_format($stats['total_revenue'], 2) }}</div>
            <div class="small text-success mt-1" style="font-size: 0.75rem;">Gross non-cancelled revenue</div>
        </div>
    </div>

    <div class="col-6 col-lg-3">
        <div class="card border-0 shadow-sm rounded-4 p-3 h-100 bg-white">
            <div class="d-flex align-items-center justify-content-between mb-2">
                <span class="small text-muted fw-bold text-uppercase" style="font-size: 0.72rem; letter-spacing: 0.5px;">Pending Action</span>
                <div class="rounded-3 p-2 d-flex align-items-center justify-content-center" style="background: rgba(245, 158, 11, 0.1); color: #f59e0b; width: 36px; height: 36px;">
                    <i class="bi bi-clock-history fs-6"></i>
                </div>
            </div>
            <div class="h3 fw-bold text-dark mb-0">{{ number_format($stats['pending_count']) }}</div>
            <div class="small text-warning mt-1" style="font-size: 0.75rem;">Requires confirmation / packing</div>
        </div>
    </div>

    <div class="col-6 col-lg-3">
        <div class="card border-0 shadow-sm rounded-4 p-3 h-100 bg-white">
            <div class="d-flex align-items-center justify-content-between mb-2">
                <span class="small text-muted fw-bold text-uppercase" style="font-size: 0.72rem; letter-spacing: 0.5px;">Delivered</span>
                <div class="rounded-3 p-2 d-flex align-items-center justify-content-center" style="background: rgba(14, 165, 233, 0.1); color: #0ea5e9; width: 36px; height: 36px;">
                    <i class="bi bi-check2-circle fs-6"></i>
                </div>
            </div>
            <div class="h3 fw-bold text-dark mb-0">{{ number_format($stats['delivered_count']) }}</div>
            <div class="small text-info mt-1" style="font-size: 0.75rem;">Successfully completed orders</div>
        </div>
    </div>
</div>

{{-- Filter and Tab Pills Card --}}
<div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-4">
    <div class="card-body p-4">
        <!-- Status Tabs Bar -->
        <div class="d-flex align-items-center gap-2 overflow-x-auto pb-3 mb-3 border-bottom" style="white-space: nowrap;">
            <a href="{{ route('admin.orders.index', array_merge(request()->except(['status', 'page']))) }}" 
               class="btn btn-sm rounded-pill px-3 fw-bold {{ !request('status') ? 'btn-primary' : 'btn-light border text-secondary' }}">
                All Orders <span class="badge {{ !request('status') ? 'bg-white text-primary' : 'bg-secondary text-white' }} ms-1">{{ $stats['total_orders'] }}</span>
            </a>

            @php
                $statusMap = [
                    'pending' => ['label' => 'Pending', 'class' => 'warning'],
                    'confirmed' => ['label' => 'Confirmed', 'class' => 'info'],
                    'packed' => ['label' => 'Packed', 'class' => 'primary'],
                    'shipped' => ['label' => 'Shipped', 'class' => 'primary'],
                    'out for delivery' => ['label' => 'Out for Delivery', 'class' => 'info'],
                    'delivered' => ['label' => 'Delivered', 'class' => 'success'],
                    'cancelled' => ['label' => 'Cancelled', 'class' => 'danger'],
                ];
            @endphp

            @foreach($statusMap as $statusCode => $statusMeta)
                @php
                    $count = $statusCounts[$statusCode] ?? 0;
                    $isActive = request('status') === $statusCode;
                @endphp
                <a href="{{ route('admin.orders.index', array_merge(request()->except(['page']), ['status' => $statusCode])) }}" 
                   class="btn btn-sm rounded-pill px-3 fw-bold {{ $isActive ? 'btn-primary' : 'btn-light border text-secondary' }}">
                    {{ $statusMeta['label'] }}
                    <span class="badge {{ $isActive ? 'bg-white text-primary' : 'bg-secondary text-white' }} ms-1">{{ $count }}</span>
                </a>
            @endforeach
        </div>

        <!-- Search & Advanced Filter Controls -->
        <form method="GET" action="{{ route('admin.orders.index') }}">
            @if(request('status'))
                <input type="hidden" name="status" value="{{ request('status') }}">
            @endif

            <div class="row g-3 align-items-center">
                <div class="col-md-6">
                    <div class="input-group">
                        <span class="input-group-text bg-white border-end-0 text-muted">
                            <i class="bi bi-search"></i>
                        </span>
                        <input type="text" name="search" class="form-control border-start-0 ps-0" 
                            placeholder="Search by Order #, Customer Name, Email, or Phone..." 
                            value="{{ request('search') }}">
                    </div>
                </div>
                <div class="col-md-3">
                    <select name="payment_method" class="form-select" onchange="this.form.submit()">
                        <option value="">All Payment Methods</option>
                        <option value="cod" {{ request('payment_method') === 'cod' ? 'selected' : '' }}>Cash on Delivery (COD)</option>
                        <option value="online" {{ request('payment_method') === 'online' ? 'selected' : '' }}>Online Payment</option>
                    </select>
                </div>
                <div class="col-md-3 d-flex gap-2">
                    <button type="submit" class="btn btn-primary rounded-pill px-4 w-100 fw-bold">
                        Filter
                    </button>
                    @if(request()->hasAny(['search', 'status', 'payment_method']))
                        <a href="{{ route('admin.orders.index') }}" class="btn btn-outline-secondary rounded-pill px-3" title="Clear all filters">
                            Clear
                        </a>
                    @endif
                </div>
            </div>
        </form>
    </div>
</div>

{{-- Main Orders Table --}}
<div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-4">
    <div class="card-header bg-white py-3 border-bottom d-flex align-items-center justify-content-between">
        <div class="d-flex align-items-center gap-2">
            <i class="bi bi-bag-check-fill text-primary fs-5"></i>
            <h6 class="mb-0 fw-bold text-dark">Orders Registry</h6>
        </div>
        <span class="badge bg-light text-dark border px-2.5 py-1.5 rounded-pill font-monospace small">
            Showing {{ $orders->count() }} of {{ $orders->total() }} Orders
        </span>
    </div>

    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-4" style="width: 140px;">Order #</th>
                        <th>Customer</th>
                        <th>Items</th>
                        <th>Amount</th>
                        <th>Payment</th>
                        <th class="text-center">Status</th>
                        <th class="pe-4 text-end" style="width: 130px;">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($orders as $order)
                    <tr>
                        <!-- Order # & Date -->
                        <td class="ps-4">
                            <a href="{{ route('admin.orders.show', $order) }}" class="fw-bold text-decoration-none text-primary font-monospace">
                                #{{ $order->order_number }}
                            </a>
                            <div class="text-muted" style="font-size: 0.72rem;">
                                {{ $order->created_at->format('d M Y') }} • {{ $order->created_at->format('h:i A') }}
                            </div>
                        </td>

                        <!-- Customer Details -->
                        <td>
                            <div class="d-flex align-items-center gap-2.5">
                                <div class="rounded-circle d-flex align-items-center justify-content-center fw-bold shadow-sm flex-shrink-0"
                                     style="width: 38px; height: 38px; background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%); color: #fff; font-size: 0.85rem;">
                                    {{ strtoupper(substr($order->shipping_name ?? ($order->user->name ?? 'C'), 0, 1)) }}
                                </div>
                                <div class="overflow-hidden">
                                    <div class="fw-bold text-dark text-truncate" style="max-width: 180px;">
                                        {{ $order->shipping_name ?? ($order->user->name ?? 'Guest User') }}
                                    </div>
                                    <div class="text-muted small text-truncate" style="font-size: 0.75rem; max-width: 180px;">
                                        <i class="bi bi-geo-alt me-1 text-secondary"></i>{{ $order->shipping_city ?? 'India' }} • {{ $order->shipping_phone ?? '' }}
                                    </div>
                                </div>
                            </div>
                        </td>

                        <!-- Items Count -->
                        <td>
                            <span class="badge bg-light text-dark border rounded-pill px-2.5 py-1">
                                <i class="bi bi-box-seam me-1 text-primary"></i>{{ $order->items->count() }} {{ Str::plural('item', $order->items->count()) }}
                            </span>
                        </td>

                        <!-- Amount & Savings -->
                        <td>
                            <div class="fw-bold text-dark fs-6">₹{{ number_format($order->total_amount, 2) }}</div>
                            @if($order->coupon_discount_amount > 0)
                                <div class="small text-success fw-semibold" style="font-size: 0.72rem;">
                                    <i class="bi bi-tag-fill me-1"></i>Saved ₹{{ number_format($order->coupon_discount_amount, 2) }}
                                </div>
                            @endif
                        </td>

                        <!-- Payment Method -->
                        <td>
                            @if(strtolower($order->payment_method ?? 'cod') === 'online')
                                <span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25 rounded-pill px-2.5 py-1" style="font-size: 0.72rem;">
                                    <i class="bi bi-credit-card me-1"></i>ONLINE
                                </span>
                            @else
                                <span class="badge bg-secondary bg-opacity-10 text-secondary border border-secondary border-opacity-25 rounded-pill px-2.5 py-1" style="font-size: 0.72rem;">
                                    <i class="bi bi-cash-stack me-1"></i>COD
                                </span>
                            @endif
                            <div class="small text-muted mt-0.5" style="font-size: 0.7rem;">
                                {{ ucfirst($order->payment_status ?? 'pending') }}
                            </div>
                        </td>

                        <!-- Order Status Badge -->
                        <td class="text-center">
                            @include('customer.components.order-status-badge', ['status' => $order->status])
                        </td>

                        <!-- Actions -->
                        <td class="pe-4 text-end">
                            <a href="{{ route('admin.orders.show', $order) }}" class="btn btn-sm btn-outline-primary rounded-pill px-3 fw-semibold">
                                Details <i class="bi bi-chevron-right ms-1 small"></i>
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center py-5 text-muted">
                            <i class="bi bi-inbox-fill text-muted opacity-50 display-6 d-block mb-2"></i>
                            <h6 class="fw-bold text-dark">No Orders Found</h6>
                            <p class="small text-muted mb-0">Try changing your search query or selected status tab.</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($orders->hasPages())
            <div class="p-4 border-top bg-white d-flex justify-content-between align-items-center flex-wrap gap-2">
                <div class="small text-muted">
                    Showing <strong>{{ $orders->firstItem() }}</strong> to <strong>{{ $orders->lastItem() }}</strong> of <strong>{{ $orders->total() }}</strong> total orders
                </div>
                <div>
                    {{ $orders->withQueryString()->links('pagination::bootstrap-5') }}
                </div>
            </div>
        @endif
    </div>
</div>

@endsection
