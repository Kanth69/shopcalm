<div class="row g-4">
    <div class="col-md-3">
        @include('admin.components.dashboard.stat-card', [
            'title' => 'Total Revenue',
            'value' => '₹' . number_format($stats['total_revenue'], 2),
            'icon' => 'bi-currency-rupee',
            'color' => 'success',
            'statKey' => 'total_revenue'
        ])
    </div>
    <div class="col-md-3">
        @include('admin.components.dashboard.stat-card', [
            'title' => 'Total Orders',
            'value' => number_format($stats['total_orders']),
            'icon' => 'bi-cart-check',
            'color' => 'primary',
            'statKey' => 'total_orders'
        ])
    </div>
    <div class="col-md-3">
        @include('admin.components.dashboard.stat-card', [
            'title' => 'New Customers',
            'value' => number_format($stats['new_customers']),
            'icon' => 'bi-person-plus',
            'color' => 'info',
            'statKey' => 'new_customers'
        ])
    </div>
    <div class="col-md-3">
        @include('admin.components.dashboard.stat-card', [
            'title' => 'Pending Orders',
            'value' => number_format($stats['pending_orders']),
            'icon' => 'bi-clock-history',
            'color' => 'warning',
            'statKey' => 'pending_orders'
        ])
    </div>
</div>
