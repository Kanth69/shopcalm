<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use Carbon\Carbon;
use Illuminate\Http\Request;

class FinanceController extends Controller
{
    public function index(Request $request)
    {
        if (!auth()->user()->isSuperAdmin()) {
            abort(403, 'Unauthorized access. Super Admin credentials required for Financial Dashboard.');
        }

        $range = $request->input('range', 'this_month');
        $statusScope = $request->input('status_scope', 'realized');
        $now = Carbon::now();

        switch ($range) {
            case 'today':
                $start = $now->copy()->startOfDay();
                $end = $now->copy()->endOfDay();
                break;
            case 'last_7_days':
                $start = $now->copy()->subDays(6)->startOfDay();
                $end = $now->copy()->endOfDay();
                break;
            case 'last_30_days':
                $start = $now->copy()->subDays(29)->startOfDay();
                $end = $now->copy()->endOfDay();
                break;
            case 'this_year':
                $start = $now->copy()->startOfYear();
                $end = $now->copy()->endOfDay();
                break;
            case 'all_time':
                $start = Carbon::create(2024, 1, 1)->startOfDay();
                $end = $now->copy()->endOfDay();
                break;
            case 'custom':
                $start = $request->filled('start_date') ? Carbon::parse($request->start_date)->startOfDay() : $now->copy()->startOfMonth();
                $end = $request->filled('end_date') ? Carbon::parse($request->end_date)->endOfDay() : $now->copy()->endOfDay();
                break;
            case 'this_month':
            default:
                $start = $now->copy()->startOfMonth();
                $end = $now->copy()->endOfDay();
                $range = 'this_month';
                break;
        }

        // Query Builder based on Status Scope
        $query = Order::with(['user', 'items.product'])
            ->whereBetween('created_at', [$start, $end]);

        if ($statusScope === 'delivered') {
            $query->where('status', 'delivered');
        } elseif ($statusScope === 'paid') {
            $query->where('payment_status', 'paid')->whereIn('status', Order::INCOME_STATUSES);
        } elseif ($statusScope === 'all_active') {
            $query->whereIn('status', Order::INCOME_STATUSES);
        } else {
            // 'realized' default: Confirmed until Delivered (excludes pending, failed, cancelled, returned)
            $query->whereIn('status', Order::INCOME_STATUSES);
        }

        $orders = $query->latest()->get();

        // 1. Core Financial Metrics (Gross Level)
        $totalRevenue = (float) $orders->sum('total_amount');
        $totalCost = (float) $orders->sum('total_cost');
        $totalGrossProfit = $totalRevenue - $totalCost;
        $overallMargin = $totalRevenue > 0 ? round(($totalGrossProfit / $totalRevenue) * 100, 1) : 0.0;
        $ordersCount = $orders->count();
        $avgTicketSize = $ordersCount > 0 ? round($totalRevenue / $ordersCount, 2) : 0.0;
        $avgProfitPerOrder = $ordersCount > 0 ? round($totalGrossProfit / $ordersCount, 2) : 0.0;

        // 2. Unit Economics & Hidden Expenses Breakdown
        $totalItemsCount = $orders->sum(fn($o) => $o->items->sum('quantity'));
        $estimatedGatewayFees = (float) $orders->sum(function ($o) {
            return ($o->payment_method === 'cod') ? 0.0 : round($o->total_amount * 0.02, 2); // 2% gateway fee
        });
        $estimatedShippingCosts = $ordersCount * 60.0; // ₹60 avg courier cost
        $estimatedPackagingCosts = $ordersCount * 15.0; // ₹15 packaging box/materials
        $totalOperatingExpenses = $estimatedGatewayFees + $estimatedShippingCosts + $estimatedPackagingCosts;
        $netInHandProfit = $totalGrossProfit - $totalOperatingExpenses;
        $netInHandMargin = $totalRevenue > 0 ? round(($netInHandProfit / $totalRevenue) * 100, 1) : 0.0;

        // 3. Trajectory Trend Chart Data
        $trendLabels = [];
        $trendRevenue = [];
        $trendCost = [];
        $trendProfit = [];

        if ($range === 'this_year' || $range === 'all_time') {
            $monthsCount = $range === 'all_time' ? 12 : $now->month;
            for ($m = 1; $m <= $monthsCount; $m++) {
                $monthDate = Carbon::create($now->year, $m, 1);
                $mStart = $monthDate->copy()->startOfMonth();
                $mEnd = $monthDate->copy()->endOfMonth();

                $mOrders = $orders->filter(function ($o) use ($mStart, $mEnd) {
                    return $o->created_at >= $mStart && $o->created_at <= $mEnd;
                });

                $rev = (float) $mOrders->sum('total_amount');
                $cost = (float) $mOrders->sum('total_cost');

                $trendLabels[] = $monthDate->format('M Y');
                $trendRevenue[] = $rev;
                $trendCost[] = $cost;
                $trendProfit[] = round($rev - $cost, 2);
            }
        } else {
            // Day-by-Day (up to 31 days)
            $period = \Carbon\CarbonPeriod::create($start, $end);
            foreach ($period as $date) {
                $dStart = $date->copy()->startOfDay();
                $dEnd = $date->copy()->endOfDay();

                $dOrders = $orders->filter(function ($o) use ($dStart, $dEnd) {
                    return $o->created_at >= $dStart && $o->created_at <= $dEnd;
                });

                $rev = (float) $dOrders->sum('total_amount');
                $cost = (float) $dOrders->sum('total_cost');

                $trendLabels[] = $date->format('d M');
                $trendRevenue[] = $rev;
                $trendCost[] = $cost;
                $trendProfit[] = round($rev - $cost, 2);
            }
        }

        // 4. Payment Channels Split
        $paymentMethods = [];
        foreach ($orders->groupBy('payment_method') as $method => $methodOrders) {
            $mRev = (float) $methodOrders->sum('total_amount');
            $mCost = (float) $methodOrders->sum('total_cost');
            $paymentMethods[] = [
                'name' => strtoupper($method ?: 'COD'),
                'count' => $methodOrders->count(),
                'revenue' => $mRev,
                'profit' => round($mRev - $mCost, 2),
                'margin' => $mRev > 0 ? round((($mRev - $mCost) / $mRev) * 100, 1) : 0,
            ];
        }

        // 5. Top High-Profit Products
        $productProfits = [];
        foreach ($orders as $order) {
            foreach ($order->items as $item) {
                $prodId = $item->product_id;
                if (!isset($productProfits[$prodId])) {
                    $productProfits[$prodId] = [
                        'name' => $item->product_name ?? 'Product #' . $prodId,
                        'image' => $item->product?->main_image,
                        'qty' => 0,
                        'revenue' => 0.0,
                        'cost' => 0.0,
                        'profit' => 0.0,
                    ];
                }
                $productProfits[$prodId]['qty'] += $item->quantity;
                $productProfits[$prodId]['revenue'] += (float) $item->total_price;
                $productProfits[$prodId]['cost'] += (float) $item->total_cost;
                $productProfits[$prodId]['profit'] += (float) $item->profit;
            }
        }

        foreach ($productProfits as &$pp) {
            $pp['margin'] = $pp['revenue'] > 0 ? round(($pp['profit'] / $pp['revenue']) * 100, 1) : 0;
        }
        unset($pp);

        usort($productProfits, fn($a, $b) => $b['profit'] <=> $a['profit']);
        $topProducts = array_slice($productProfits, 0, 5);

        // 6. Recent Orders
        $recentOrders = $orders->take(8);

        return view('admin.finance.index', compact(
            'range',
            'statusScope',
            'start',
            'end',
            'totalRevenue',
            'totalCost',
            'totalGrossProfit',
            'overallMargin',
            'ordersCount',
            'avgTicketSize',
            'avgProfitPerOrder',
            'totalItemsCount',
            'estimatedGatewayFees',
            'estimatedShippingCosts',
            'estimatedPackagingCosts',
            'totalOperatingExpenses',
            'netInHandProfit',
            'netInHandMargin',
            'trendLabels',
            'trendRevenue',
            'trendCost',
            'trendProfit',
            'paymentMethods',
            'topProducts',
            'recentOrders'
        ));
    }
}
