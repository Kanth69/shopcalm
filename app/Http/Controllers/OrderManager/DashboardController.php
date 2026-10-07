<?php

namespace App\Http\Controllers\OrderManager;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderStatusHistory;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /**
     * Display the Order Manager Operations Dashboard.
     */
    public function index(): View
    {
        $stats = [
            'total_orders'       => Order::count(),
            'bengaluru_local'    => Order::whereHas('fulfillment', fn($q) => $q->where('type', 'local_fleet'))->count(),
            'national_courier'   => Order::whereHas('fulfillment', fn($q) => $q->where('type', 'courier'))->count(),
            'bengaluru_pending'  => Order::whereHas('fulfillment', fn($q) => $q->where('type', 'local_fleet')->whereNull('rider_name'))->whereNotIn('status', ['delivered', 'cancelled'])->count(),
            'courier_pending'    => Order::whereHas('fulfillment', fn($q) => $q->where('type', 'courier')->whereNull('tracking_number'))->whereNotIn('status', ['delivered', 'cancelled'])->count(),
            'needs_processing'   => Order::whereIn('status', ['pending', 'confirmed'])->count(),
            'packing_queue'      => Order::whereIn('status', ['processing', 'packed'])->count(),
            'in_transit'         => Order::whereIn('status', ['shipped', 'out for delivery'])->count(),
            'delivered'          => Order::where('status', 'delivered')->count(),
            'cancelled'          => Order::whereIn('status', ['cancelled', 'returned'])->count(),
            'today_orders'       => Order::whereDate('created_at', today())->count(),
            'today_revenue'      => Order::whereDate('created_at', today())->whereIn('status', Order::INCOME_STATUSES)->sum('total_amount'),
        ];

        // Priority queue for warehouse pack & dispatch
        $urgentOrders = Order::with(['user', 'items.product', 'fulfillment'])
            ->whereIn('status', ['pending', 'confirmed', 'processing', 'packed'])
            ->latest()
            ->take(8)
            ->get();

        // Recent Dispatches
        $recentDispatches = Order::with(['user', 'items.product', 'fulfillment'])
            ->whereIn('status', ['shipped', 'out for delivery', 'delivered'])
            ->latest('updated_at')
            ->take(6)
            ->get();

        // Recent timeline activity
        $recentActivity = OrderStatusHistory::with(['order.fulfillment', 'user'])
            ->latest()
            ->take(8)
            ->get();

        // Live Delivery Exceptions & Rider Alerts
        $recentDeliveryIssues = OrderStatusHistory::with(['order.fulfillment', 'user'])
            ->where(function($q) {
                $q->where('notes', 'like', '%Delivery Exception%')
                  ->orWhere('notes', 'like', '%issue reported%')
                  ->orWhere('notes', 'like', '%Delivery issue%');
            })
            ->latest()
            ->take(6)
            ->get();

        return view('order-manager.dashboard', compact('stats', 'urgentOrders', 'recentDispatches', 'recentActivity', 'recentDeliveryIssues'));
    }

    /**
     * Display the Dedicated Delivery Alerts & Exceptions Center.
     */
    public function alerts(\Illuminate\Http\Request $request): View
    {
        $tab = $request->get('tab', 'active');

        // 1. Active Pending Exceptions (Requires OM Action)
        $activeExceptions = \App\Models\OrderFulfillment::with(['order.items.product', 'rider', 'order.user'])
            ->activeExceptions()
            ->whereHas('order', function($q) {
                $q->whereNotIn('status', ['delivered', 'cancelled', 'returned']);
            })
            ->latest('delivery_issue_at')
            ->paginate(15, ['*'], 'active_page');

        // 2. Resolved Exceptions History
        $resolvedExceptions = \App\Models\OrderFulfillment::with(['order.items.product', 'rider', 'order.user'])
            ->resolvedExceptions()
            ->latest('delivery_issue_resolved_at')
            ->paginate(15, ['*'], 'resolved_page');

        $pendingResolutionsCount = \App\Models\OrderFulfillment::activeExceptions()
            ->whereHas('order', function($q) {
                $q->whereNotIn('status', ['delivered', 'cancelled', 'returned']);
            })
            ->count();

        $resolvedCount = \App\Models\OrderFulfillment::resolvedExceptions()->count();

        return view('order-manager.alerts', compact(
            'tab',
            'activeExceptions',
            'resolvedExceptions',
            'pendingResolutionsCount',
            'resolvedCount'
        ));
    }
}
