<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Product;
use App\Models\ProductReview;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminDashboardService
{
    public function getDashboardData(Request $request, bool $statsOnly = false)
    {
        $period = $request->input('period', 'last_7_days');
        [$startDate, $endDate] = $this->parseDateRange($period, $request);

        $data = [];

        // Stats
        $data['stats'] = $this->getStats($startDate, $endDate);

        if ($statsOnly) {
            return $data['stats'];
        }

        // Charts
        $data['charts'] = $this->getChartData($startDate, $endDate);

        // Latest Activity
        $data['latestOrders'] = Order::with('user')->latest()->take(5)->get();
        // Updated to use the correct Customer role ID (3)
        $data['latestCustomers'] = User::where('role_id', 3)->latest()->take(5)->get();
        $data['lowStockProducts'] = Product::where('stock', '<=', DB::raw('low_stock_alert'))->where('stock', '>', 0)->orderBy('stock', 'asc')->take(5)->get();
        $data['pendingReviews'] = ProductReview::with('product', 'user')->where('status', 'Pending')->latest()->take(5)->get();

        return $data;
    }

    private function parseDateRange(string $period, Request $request): array
    {
        switch ($period) {
            case 'today':
                return [Carbon::today(), Carbon::today()->endOfDay()];
            case 'yesterday':
                return [Carbon::yesterday(), Carbon::yesterday()->endOfDay()];
            case 'last_7_days':
                return [Carbon::today()->subDays(6), Carbon::today()->endOfDay()];
            case 'this_month':
                return [Carbon::now()->startOfMonth(), Carbon::now()->endOfMonth()];
            case 'last_month':
                return [Carbon::now()->subMonth()->startOfMonth(), Carbon::now()->subMonth()->endOfMonth()];
            case 'this_year':
                return [Carbon::now()->startOfYear(), Carbon::now()->endOfYear()];
            case 'custom':
                $start = Carbon::parse($request->input('start_date', Carbon::today()->subDays(6)));
                $end = Carbon::parse($request->input('end_date', Carbon::today()));
                return [$start, $end->endOfDay()];
            default:
                return [Carbon::today()->subDays(6), Carbon::today()->endOfDay()];
        }
    }

    private function getStats(Carbon $startDate, Carbon $endDate): array
    {
        // Updated to use the correct Customer role ID (3)
        return [
            'total_customers' => User::where('role_id', 3)->count(),
            'new_customers' => User::where('role_id', 3)->whereBetween('created_at', [$startDate, $endDate])->count(),
            'total_orders' => Order::whereBetween('created_at', [$startDate, $endDate])->count(),
            'pending_orders' => Order::where('status', 'pending')->whereBetween('created_at', [$startDate, $endDate])->count(),
            'processing_orders' => Order::where('status', 'processing')->whereBetween('created_at', [$startDate, $endDate])->count(),
            'delivered_orders' => Order::where('status', 'delivered')->whereBetween('created_at', [$startDate, $endDate])->count(),
            'cancelled_orders' => Order::where('status', 'cancelled')->whereBetween('created_at', [$startDate, $endDate])->count(),
            'total_revenue' => Order::where('status', 'delivered')->whereBetween('created_at', [$startDate, $endDate])->sum('total_amount'),
            'avg_order_value' => Order::where('status', 'delivered')->whereBetween('created_at', [$startDate, $endDate])->avg('total_amount'),
            'total_products' => Product::count(),
            'active_products' => Product::where('status', 'Active')->count(),
            'low_stock_products' => Product::where('stock', '<=', DB::raw('low_stock_alert'))->where('stock', '>', 0)->count(),
            'out_of_stock_products' => Product::where('stock', '=', 0)->count(),
            'pending_reviews' => ProductReview::where('status', 'Pending')->count(),
        ];
    }

    private function getChartData(Carbon $startDate, Carbon $endDate): array
    {
        $orderTrend = Order::select(DB::raw('DATE(created_at) as date'), DB::raw('count(*) as count'))
            ->whereBetween('created_at', [$startDate, $endDate])
            ->groupBy('date')
            ->orderBy('date', 'asc')
            ->pluck('count', 'date');

        $revenueTrend = Order::select(DB::raw('DATE(created_at) as date'), DB::raw('sum(total_amount) as total'))
            ->where('status', 'delivered')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->groupBy('date')
            ->orderBy('date', 'asc')
            ->pluck('total', 'date');

        // Updated to use the correct Customer role ID (3)
        $customerTrend = User::select(DB::raw('DATE(created_at) as date'), DB::raw('count(*) as count'))
            ->where('role_id', 3)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->groupBy('date')
            ->orderBy('date', 'asc')
            ->pluck('count', 'date');

        $orderStatusDistribution = Order::select('status', DB::raw('count(*) as count'))
            ->whereBetween('created_at', [$startDate, $endDate])
            ->groupBy('status')
            ->pluck('count', 'status');

        return [
            'order_trend' => $this->prepareChartData($orderTrend, $startDate, $endDate),
            'revenue_trend' => $this->prepareChartData($revenueTrend, $startDate, $endDate),
            'customer_trend' => $this->prepareChartData($customerTrend, $startDate, $endDate),
            'order_status_distribution' => $orderStatusDistribution,
        ];
    }

    private function prepareChartData($data, Carbon $startDate, Carbon $endDate): array
    {
        $labels = [];
        $values = [];
        for ($date = $startDate->copy(); $date <= $endDate; $date->addDay()) {
            $formattedDate = $date->format('Y-m-d');
            $labels[] = $formattedDate;
            $values[] = $data[$formattedDate] ?? 0;
        }
        return ['labels' => $labels, 'values' => $values];
    }
}
