@extends('admin.layouts.app')

@section('header', 'Overview')

@section('breadcrumb')
    <li class="breadcrumb-item active" aria-current="page">Dashboard</li>
@endsection

@section('content')
<div class="row g-4 mb-4">
    <div class="col-md-6 col-lg-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="stat-card-icon bg-primary bg-opacity-10 text-primary me-3">
                        <i class="bi bi-currency-rupee"></i>
                    </div>
                    <div>
                        <h6 class="text-muted small text-uppercase mb-1">Total Revenue</h6>
                        <h4 class="mb-0 fw-bold">₹{{ number_format($stats['total_revenue'], 2) }}</h4>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-6 col-lg-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="stat-card-icon bg-success bg-opacity-10 text-success me-3">
                        <i class="bi bi-cart-check"></i>
                    </div>
                    <div>
                        <h6 class="text-muted small text-uppercase mb-1">Total Orders</h6>
                        <h4 class="mb-0 fw-bold">{{ number_format($stats['total_orders']) }}</h4>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-6 col-lg-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="stat-card-icon bg-info bg-opacity-10 text-info me-3">
                        <i class="bi bi-people"></i>
                    </div>
                    <div>
                        <h6 class="text-muted small text-uppercase mb-1">Customers</h6>
                        <h4 class="mb-0 fw-bold">{{ number_format($stats['total_customers']) }}</h4>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-6 col-lg-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="stat-card-icon bg-warning bg-opacity-10 text-warning me-3">
                        <i class="bi bi-star"></i>
                    </div>
                    <div>
                        <h6 class="text-muted small text-uppercase mb-1">Pending Reviews</h6>
                        <h4 class="mb-0 fw-bold">{{ number_format($stats['pending_reviews']) }}</h4>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row g-4 mt-2">
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">Sales Analytics</h5>
                <form action="{{ route('admin.dashboard') }}" method="GET" class="d-flex align-items-center">
                    <select name="period" class="form-select form-select-sm border-0 bg-light shadow-none" onchange="this.form.submit()">
                        <option value="today" {{ request('period') == 'today' ? 'selected' : '' }}>Today</option>
                        <option value="last_7_days" {{ request('period', 'last_7_days') == 'last_7_days' ? 'selected' : '' }}>Last 7 Days</option>
                        <option value="this_month" {{ request('period') == 'this_month' ? 'selected' : '' }}>This Month</option>
                        <option value="this_year" {{ request('period') == 'this_year' ? 'selected' : '' }}>This Year</option>
                    </select>
                </form>
            </div>
            <div class="card-body">
                <canvas id="revenueOrdersChart" height="300"></canvas>
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white py-3">
                <h5 class="card-title mb-0">Order Status Distribution</h5>
            </div>
            <div class="card-body d-flex align-items-center">
                <canvas id="orderStatusChart"></canvas>
            </div>
        </div>
    </div>
</div>

<div class="row g-4 mt-2">
    <div class="col-lg-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">Latest Orders</h5>
                <a href="{{ route('admin.orders.index') }}" class="btn btn-sm btn-link text-decoration-none">View All</a>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-3 small">Order #</th>
                                <th class="small">Customer</th>
                                <th class="small">Amount</th>
                                <th class="pe-3 text-end small">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($latestOrders as $order)
                            <tr>
                                <td class="ps-3"><span class="fw-bold">{{ $order->order_number }}</span></td>
                                <td>{{ $order->user->name }}</td>
                                <td>₹{{ number_format($order->total_amount, 2) }}</td>
                                <td class="pe-3 text-end">
                                    @include('customer.components.order-status-badge', ['status' => $order->status])
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="4" class="text-center py-4 text-muted">No recent orders found.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">Inventory Alert</h5>
                <a href="{{ route('admin.stock.dashboard') }}" class="btn btn-sm btn-link text-decoration-none">Stock Manager</a>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-3 small">Product</th>
                                <th class="small">SKU</th>
                                <th class="pe-3 text-end small">Stock</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($lowStockProducts as $product)
                            <tr>
                                <td class="ps-3 text-dark fw-medium">{{ \Illuminate\Support\Str::limit($product->name, 30) }}</td>
                                <td>{{ $product->sku }}</td>
                                <td class="pe-3 text-end"><span class="badge bg-danger bg-opacity-10 text-danger border border-danger border-opacity-25">{{ $product->stock }} left</span></td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="3" class="text-center py-4 text-muted">All products are well stocked.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="{{ asset('js/admin-dashboard.js') }}"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const chartData = @json($charts);
        initializeDashboardCharts(chartData);
    });
</script>
@endpush
