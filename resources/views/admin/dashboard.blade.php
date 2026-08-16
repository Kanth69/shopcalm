@extends('admin.layouts.app')

@section('header', 'Dashboard')

@section('breadcrumb')
    <li class="breadcrumb-item active" aria-current="page">Dashboard</li>
@endsection

@section('content')

{{-- Period Filter --}}
<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <p class="text-muted mb-0" style="font-size:0.82rem;">Here's what's happening in your store.</p>
    </div>
    <div class="d-flex gap-2 flex-wrap">
        @foreach(['today' => 'Today', 'last_7_days' => 'Last 7 Days', 'last_30_days' => 'Last 30 Days', 'this_month' => 'This Month', 'this_year' => 'This Year', 'all' => 'All Time'] as $value => $label)
        <a href="{{ route('admin.dashboard', ['period' => $value]) }}"
            class="btn btn-sm {{ request('period', 'last_7_days') === $value ? 'btn-primary' : 'btn-light' }}"
            style="{{ request('period', 'last_7_days') === $value ? '' : 'border:1px solid #e2e8f0;' }}">
            {{ $label }}
        </a>
        @endforeach
    </div>
</div>

{{-- KPI Stat Cards --}}
<div class="row g-3 mb-4" id="dashboard-stats-container">
    @php
        $kpis = [
            ['label' => 'Total Revenue',    'value' => '₹'.number_format($stats['total_revenue'],2),      'data' => 'total_revenue',    'icon' => 'bi-currency-rupee', 'color' => '#6366f1', 'bg' => '#ede9fe'],
            ['label' => 'Total Orders',     'value' => number_format($stats['total_orders']),              'data' => 'total_orders',     'icon' => 'bi-cart-check',     'color' => '#10b981', 'bg' => '#d1fae5'],
            ['label' => 'New Customers',    'value' => number_format($stats['new_customers']),             'data' => 'new_customers',    'icon' => 'bi-people',         'color' => '#06b6d4', 'bg' => '#cffafe'],
            ['label' => 'Avg Order Value',  'value' => '₹'.number_format($stats['avg_order_value'] ?? 0,2),'data' => 'avg_order_value',  'icon' => 'bi-graph-up-arrow', 'color' => '#f59e0b', 'bg' => '#fef3c7'],
        ];
    @endphp
    @foreach($kpis as $kpi)
    <div class="col-6 col-lg-3">
        <div class="card h-100" style="border-left: 4px solid {{ $kpi['color'] }} !important; border-radius:14px !important;">
            <div class="card-body py-3 px-4">
                <div class="d-flex align-items-start justify-content-between mb-2">
                    <p class="mb-0 text-uppercase fw-bold" style="font-size:0.68rem; letter-spacing:0.07em; color:{{ $kpi['color'] }};">{{ $kpi['label'] }}</p>
                    <div style="width:36px;height:36px;border-radius:10px;background:{{ $kpi['bg'] }};display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                        <i class="bi {{ $kpi['icon'] }}" style="font-size:1rem;color:{{ $kpi['color'] }};"></i>
                    </div>
                </div>
                <h3 class="mb-0 fw-bolder" data-stat="{{ $kpi['data'] }}" style="font-size:1.6rem;letter-spacing:-0.5px;color:#0f172a;">{{ $kpi['value'] }}</h3>
            </div>
        </div>
    </div>
    @endforeach
</div>

{{-- Secondary Stats Row --}}
<div class="row g-3 mb-4">
    @php
        $secondaries = [
            ['label' => 'Pending',     'value' => $stats['pending_orders'],      'color' => '#f59e0b', 'bg' => '#fffbeb'],
            ['label' => 'Delivered',   'value' => $stats['delivered_orders'],    'color' => '#10b981', 'bg' => '#f0fdf4'],
            ['label' => 'Cancelled',   'value' => $stats['cancelled_orders'],    'color' => '#ef4444', 'bg' => '#fef2f2'],
            ['label' => 'Low Stock',   'value' => $stats['low_stock_products'],  'color' => '#f97316', 'bg' => '#fff7ed'],
            ['label' => 'Out of Stock','value' => $stats['out_of_stock_products'],'color' => '#dc2626','bg' => '#fee2e2'],
            ['label' => 'Pending Reviews','value' => $stats['pending_reviews'],  'color' => '#8b5cf6', 'bg' => '#ede9fe'],
        ];
    @endphp
    @foreach($secondaries as $s)
    <div class="col-6 col-md-4 col-lg-2">
        <div class="card text-center" style="border-radius:12px !important;">
            <div class="card-body py-3 px-2">
                <div class="mx-auto mb-2" style="width:36px;height:36px;border-radius:10px;background:{{ $s['bg'] }};display:flex;align-items:center;justify-content:center;">
                    <span style="font-size:1.1rem;font-weight:700;color:{{ $s['color'] }};">{{ number_format($s['value']) }}</span>
                </div>
                <p class="mb-0 text-muted" style="font-size:0.7rem;font-weight:600;text-transform:uppercase;letter-spacing:0.05em;">{{ $s['label'] }}</p>
            </div>
        </div>
    </div>
    @endforeach
</div>

{{-- Charts Row --}}
<div class="row g-4 mb-4">
    <div class="col-lg-8">
        <div class="card h-100" style="border-radius:14px !important;">
            <div class="card-header d-flex align-items-center justify-content-between" style="background:#fff;border-bottom:1px solid #f1f5f9;padding:1rem 1.25rem;">
                <div>
                    <h6 class="mb-0 fw-bold" style="font-size:0.9rem;">Revenue & Orders Trend</h6>
                    <p class="mb-0 text-muted" style="font-size:0.72rem;">{{ request('period', 'last_7_days') === 'last_7_days' ? 'Last 7 days' : ucwords(str_replace('_', ' ', request('period', 'last_7_days'))) }}</p>
                </div>
                <div class="d-flex gap-3" style="font-size:0.72rem;">
                    <span><span style="display:inline-block;width:10px;height:10px;border-radius:50%;background:#6366f1;margin-right:4px;"></span>Revenue</span>
                    <span><span style="display:inline-block;width:10px;height:10px;border-radius:50%;background:#10b981;margin-right:4px;"></span>Orders</span>
                </div>
            </div>
            <div class="card-body" style="padding:1.25rem;">
                <canvas id="revenueOrdersChart" height="280"></canvas>
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="card h-100" style="border-radius:14px !important;">
            <div class="card-header" style="background:#fff;border-bottom:1px solid #f1f5f9;padding:1rem 1.25rem;">
                <h6 class="mb-0 fw-bold" style="font-size:0.9rem;">Order Status</h6>
                <p class="mb-0 text-muted" style="font-size:0.72rem;">Distribution breakdown</p>
            </div>
            <div class="card-body d-flex align-items-center justify-content-center" style="padding:1.25rem;">
                <canvas id="orderStatusChart"></canvas>
            </div>
        </div>
    </div>
</div>

{{-- Latest Orders + Low Stock --}}
<div class="row g-4 mb-4">
    <div class="col-lg-7">
        <div class="card" style="border-radius:14px !important;">
            <div class="card-header d-flex align-items-center justify-content-between" style="background:#fff;border-bottom:1px solid #f1f5f9;padding:1rem 1.25rem;">
                <h6 class="mb-0 fw-bold" style="font-size:0.9rem;">Latest Orders</h6>
                <a href="{{ route('admin.orders.index') }}" class="btn btn-sm btn-light" style="font-size:0.75rem;border:1px solid #e2e8f0;">View All →</a>
            </div>
            <div class="card-body p-0">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            <th class="ps-4">Order</th>
                            <th>Customer</th>
                            <th>Amount</th>
                            <th class="pe-4 text-end">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($latestOrders as $order)
                        <tr>
                            <td class="ps-4">
                                <a href="{{ route('admin.orders.show', $order) }}" class="fw-bold text-decoration-none" style="color:#6366f1;font-size:0.82rem;">{{ $order->order_number }}</a>
                                <div style="font-size:0.7rem;color:#94a3b8;">{{ $order->created_at->diffForHumans() }}</div>
                            </td>
                            <td style="font-size:0.82rem;">{{ $order->user->name ?? '—' }}</td>
                            <td class="fw-semibold" style="font-size:0.82rem;">₹{{ number_format($order->total_amount, 2) }}</td>
                            <td class="pe-4 text-end">
                                @include('customer.components.order-status-badge', ['status' => $order->status])
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="4" class="text-center py-4 text-muted">No recent orders.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="col-lg-5">
        <div class="card" style="border-radius:14px !important;">
            <div class="card-header d-flex align-items-center justify-content-between" style="background:#fff;border-bottom:1px solid #f1f5f9;padding:1rem 1.25rem;">
                <h6 class="mb-0 fw-bold" style="font-size:0.9rem;">⚠️ Low Stock Alert</h6>
                <a href="{{ route('admin.stock.dashboard') }}" class="btn btn-sm btn-light" style="font-size:0.75rem;border:1px solid #e2e8f0;">Manage Stock →</a>
            </div>
            <div class="card-body p-0">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            <th class="ps-4">Product</th>
                            <th class="pe-4 text-end">Stock</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($lowStockProducts as $product)
                        <tr>
                            <td class="ps-4">
                                <div class="fw-semibold" style="font-size:0.82rem;color:#0f172a;">{{ Str::limit($product->name, 28) }}</div>
                                <div style="font-size:0.7rem;color:#94a3b8;">{{ $product->sku }}</div>
                            </td>
                            <td class="pe-4 text-end">
                                <span class="badge" style="background:#fee2e2;color:#dc2626;border-radius:999px;font-size:0.7rem;padding:0.28em 0.75em;">
                                    {{ $product->stock }} left
                                </span>
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="2" class="text-center py-4 text-muted">All products well stocked ✅</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

{{-- Latest Customers + Pending Reviews --}}
<div class="row g-4">
    <div class="col-lg-6">
        <div class="card" style="border-radius:14px !important;">
            <div class="card-header d-flex align-items-center justify-content-between" style="background:#fff;border-bottom:1px solid #f1f5f9;padding:1rem 1.25rem;">
                <h6 class="mb-0 fw-bold" style="font-size:0.9rem;">New Customers</h6>
                <a href="{{ route('admin.customers.index') }}" class="btn btn-sm btn-light" style="font-size:0.75rem;border:1px solid #e2e8f0;">View All →</a>
            </div>
            <div class="card-body p-0">
                <table class="table table-hover align-middle mb-0">
                    <thead><tr><th class="ps-4">Customer</th><th class="pe-4 text-end">Joined</th></tr></thead>
                    <tbody>
                        @forelse($latestCustomers as $customer)
                        <tr>
                            <td class="ps-4">
                                <div class="d-flex align-items-center gap-2">
                                    <div style="width:30px;height:30px;border-radius:50%;background:linear-gradient(135deg,#6366f1,#8b5cf6);display:flex;align-items:center;justify-content:center;font-size:0.7rem;font-weight:700;color:#fff;flex-shrink:0;">
                                        {{ strtoupper(substr($customer->name,0,1)) }}
                                    </div>
                                    <div>
                                        <div class="fw-semibold" style="font-size:0.82rem;">{{ $customer->name }}</div>
                                        <div style="font-size:0.7rem;color:#94a3b8;">{{ $customer->email }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="pe-4 text-end" style="font-size:0.75rem;color:#94a3b8;">{{ $customer->created_at->diffForHumans() }}</td>
                        </tr>
                        @empty
                        <tr><td colspan="2" class="text-center py-4 text-muted">No new customers.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="col-lg-6">
        <div class="card" style="border-radius:14px !important;">
            <div class="card-header d-flex align-items-center justify-content-between" style="background:#fff;border-bottom:1px solid #f1f5f9;padding:1rem 1.25rem;">
                <h6 class="mb-0 fw-bold" style="font-size:0.9rem;">Pending Reviews</h6>
                <a href="{{ route('admin.reviews.index') }}" class="btn btn-sm btn-light" style="font-size:0.75rem;border:1px solid #e2e8f0;">Manage →</a>
            </div>
            <div class="card-body p-0">
                <table class="table table-hover align-middle mb-0">
                    <thead><tr><th class="ps-4">Review</th><th class="pe-4 text-end">Rating</th></tr></thead>
                    <tbody>
                        @forelse($pendingReviews as $review)
                        <tr>
                            <td class="ps-4">
                                <div class="fw-semibold" style="font-size:0.82rem;">{{ Str::limit($review->product->name ?? '—', 24) }}</div>
                                <div style="font-size:0.7rem;color:#94a3b8;">by {{ $review->user->name ?? '—' }} · {{ $review->created_at->diffForHumans() }}</div>
                            </td>
                            <td class="pe-4 text-end">
                                <span style="color:#f59e0b;font-size:0.8rem;">
                                    @for($i=1;$i<=5;$i++){{ $i <= $review->rating ? '★' : '☆' }}@endfor
                                </span>
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="2" class="text-center py-4 text-muted">No pending reviews ✅</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const chartData = @json($charts);

    // ── Revenue & Orders Chart ──
    const revenueCtx = document.getElementById('revenueOrdersChart');
    if (revenueCtx) {
        new Chart(revenueCtx, {
            type: 'line',
            data: {
                labels: chartData.order_trend.labels.map(d => {
                    if (typeof d === 'string' && d.length === 7) {
                        const parts = d.split('-');
                        const dt = new Date(parseInt(parts[0]), parseInt(parts[1]) - 1, 1);
                        return dt.toLocaleDateString('en-IN', { month: 'short', year: '2-digit' });
                    }
                    const dt = new Date(d + 'T00:00:00');
                    return isNaN(dt.getTime()) ? d : dt.toLocaleDateString('en-IN', { day: 'numeric', month: 'short' });
                }),
                datasets: [
                    {
                        label: 'Revenue (₹)',
                        data: chartData.revenue_trend.values,
                        borderColor: '#6366f1',
                        backgroundColor: 'rgba(99,102,241,0.08)',
                        borderWidth: 2.5,
                        pointRadius: 4,
                        pointBackgroundColor: '#6366f1',
                        pointBorderColor: '#fff',
                        pointBorderWidth: 2,
                        tension: 0.4,
                        fill: true,
                        yAxisID: 'y1',
                    },
                    {
                        label: 'Orders',
                        data: chartData.order_trend.values,
                        borderColor: '#10b981',
                        backgroundColor: 'rgba(16,185,129,0.07)',
                        borderWidth: 2.5,
                        pointRadius: 4,
                        pointBackgroundColor: '#10b981',
                        pointBorderColor: '#fff',
                        pointBorderWidth: 2,
                        tension: 0.4,
                        fill: true,
                        yAxisID: 'y',
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: { mode: 'index', intersect: false },
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        backgroundColor: '#0f172a',
                        titleColor: '#94a3b8',
                        bodyColor: '#fff',
                        padding: 12,
                        cornerRadius: 8,
                        callbacks: {
                            label: (ctx) => ctx.dataset.label === 'Revenue (₹)'
                                ? ` ₹${parseFloat(ctx.raw).toLocaleString('en-IN')}`
                                : ` ${ctx.raw} orders`
                        }
                    }
                },
                scales: {
                    x: {
                        grid: { color: '#f1f5f9', drawBorder: false },
                        ticks: { color: '#94a3b8', font: { size: 11 } }
                    },
                    y: {
                        position: 'left',
                        grid: { color: '#f8fafc', drawBorder: false },
                        ticks: { color: '#10b981', font: { size: 11 }, stepSize: 1 },
                        title: { display: false }
                    },
                    y1: {
                        position: 'right',
                        grid: { drawOnChartArea: false },
                        ticks: {
                            color: '#6366f1', font: { size: 11 },
                            callback: v => '₹' + Number(v).toLocaleString('en-IN')
                        }
                    }
                }
            }
        });
    }

    // ── Order Status Doughnut ──
    const statusCtx = document.getElementById('orderStatusChart');
    if (statusCtx) {
        const statusColors = {
            pending:           '#f59e0b',
            confirmed:         '#6366f1',
            packed:            '#06b6d4',
            shipped:           '#8b5cf6',
            'out for delivery':'#f97316',
            delivered:         '#10b981',
            cancelled:         '#ef4444',
        };
        const labels  = Object.keys(chartData.order_status_distribution);
        const values  = Object.values(chartData.order_status_distribution);
        const colors  = labels.map(l => statusColors[l] ?? '#94a3b8');

        new Chart(statusCtx, {
            type: 'doughnut',
            data: {
                labels: labels.map(l => l.charAt(0).toUpperCase() + l.slice(1)),
                datasets: [{ data: values, backgroundColor: colors, borderWidth: 2, borderColor: '#fff', hoverOffset: 6 }]
            },
            options: {
                responsive: true,
                cutout: '68%',
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: { font: { size: 11 }, padding: 12, color: '#64748b', boxWidth: 10, usePointStyle: true }
                    },
                    tooltip: {
                        backgroundColor: '#0f172a',
                        titleColor: '#94a3b8',
                        bodyColor: '#fff',
                        padding: 10,
                        cornerRadius: 8,
                    }
                }
            }
        });
    }
});
</script>
@endpush
