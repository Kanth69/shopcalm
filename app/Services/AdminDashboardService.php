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
        $data['lowStockProducts'] = Product::where('stock', '<=', 5)->where('stock', '>', 0)->orderBy('stock', 'asc')->take(5)->get();
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
            case 'last_30_days':
                return [Carbon::today()->subDays(29), Carbon::today()->endOfDay()];
            case 'this_month':
                return [Carbon::now()->startOfMonth(), Carbon::now()->endOfMonth()];
            case 'last_month':
                return [Carbon::now()->subMonth()->startOfMonth(), Carbon::now()->subMonth()->endOfMonth()];
            case 'this_year':
                return [Carbon::now()->startOfYear(), Carbon::now()->endOfYear()];
            case 'all':
                $earliestOrder = Order::oldest()->first();
                $start = $earliestOrder ? $earliestOrder->created_at->startOfDay() : Carbon::now()->startOfYear();
                return [$start, Carbon::now()->endOfDay()];
            default:
                return [Carbon::today()->subDays(6), Carbon::today()->endOfDay()];
        }
    }

    private function getStats(Carbon $startDate, Carbon $endDate): array
    {
        $validOrdersQuery = Order::whereNotIn('status', ['cancelled'])->whereBetween('created_at', [$startDate, $endDate]);

        return [
            'total_customers' => User::where('role_id', 3)->count(),
            'new_customers' => User::where('role_id', 3)->whereBetween('created_at', [$startDate, $endDate])->count(),
            'total_orders' => Order::whereBetween('created_at', [$startDate, $endDate])->count(),
            'pending_orders' => Order::where('status', 'pending')->whereBetween('created_at', [$startDate, $endDate])->count(),
            'processing_orders' => Order::whereIn('status', ['processing', 'confirmed', 'packed'])->whereBetween('created_at', [$startDate, $endDate])->count(),
            'delivered_orders' => Order::where('status', 'delivered')->whereBetween('created_at', [$startDate, $endDate])->count(),
            'cancelled_orders' => Order::where('status', 'cancelled')->whereBetween('created_at', [$startDate, $endDate])->count(),
            'total_revenue' => (float) $validOrdersQuery->sum('total_amount'),
            'avg_order_value' => (float) ($validOrdersQuery->avg('total_amount') ?? 0),
            'total_products' => Product::count(),
            'active_products' => Product::where('status', 'Active')->count(),
            'low_stock_products' => Product::where('stock', '<=', 5)->where('stock', '>', 0)->count(),
            'out_of_stock_products' => Product::where('stock', '=', 0)->count(),
            'pending_reviews' => ProductReview::where('status', 'Pending')->count(),
        ];
    }

    private function getChartData(Carbon $startDate, Carbon $endDate): array
    {
        $isMonthly = $startDate->diffInDays($endDate) > 60;

        if ($isMonthly) {
            $dateFormat = "%Y-%m";
            $dbSelect = DB::raw("DATE_FORMAT(created_at, '%Y-%m') as date");
        } else {
            $dateFormat = "%Y-%m-%d";
            $dbSelect = DB::raw("DATE(created_at) as date");
        }

        $orderTrend = Order::select($dbSelect, DB::raw('count(*) as count'))
            ->whereBetween('created_at', [$startDate, $endDate])
            ->groupBy('date')
            ->orderBy('date', 'asc')
            ->pluck('count', 'date');

        $revenueTrend = Order::select($dbSelect, DB::raw('sum(total_amount) as total'))
            ->where('status', 'delivered')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->groupBy('date')
            ->orderBy('date', 'asc')
            ->pluck('total', 'date');

        $customerTrend = User::select($dbSelect, DB::raw('count(*) as count'))
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
            'order_trend' => $this->prepareChartData($orderTrend, $startDate, $endDate, $isMonthly),
            'revenue_trend' => $this->prepareChartData($revenueTrend, $startDate, $endDate, $isMonthly),
            'customer_trend' => $this->prepareChartData($customerTrend, $startDate, $endDate, $isMonthly),
            'order_status_distribution' => $orderStatusDistribution,
        ];
    }

    private function prepareChartData($data, Carbon $startDate, Carbon $endDate, bool $isMonthly = false): array
    {
        $labels = [];
        $values = [];

        if ($isMonthly) {
            $current = $startDate->copy()->startOfMonth();
            $end = $endDate->copy()->endOfMonth();
            while ($current <= $end) {
                $key = $current->format('Y-m');
                $labels[] = $key;
                $values[] = (float) ($data[$key] ?? 0);
                $current->addMonth();
            }
        } else {
            for ($date = $startDate->copy(); $date <= $endDate; $date->addDay()) {
                $key = $date->format('Y-m-d');
                $labels[] = $key;
                $values[] = (float) ($data[$key] ?? 0);
            }
        }

        return ['labels' => $labels, 'values' => $values];
    }
}
