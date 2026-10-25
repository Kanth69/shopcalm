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

        // If custom dates are explicitly passed, automatically default range to custom
        if ($request->filled('start_date') || $request->filled('end_date') || $request->filled('from_date') || $request->filled('to_date')) {
            $range = 'custom';
        }

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
                $rawStart = $request->input('start_date') ?: $request->input('from_date');
                $rawEnd = $request->input('end_date') ?: $request->input('to_date');
                $start = $rawStart ? Carbon::parse($rawStart)->startOfDay() : $now->copy()->startOfMonth();
                $end = $rawEnd ? Carbon::parse($rawEnd)->endOfDay() : $now->copy()->endOfDay();
                if ($start->gt($end)) {
                    // Swap if user inverted start and end
                    [$start, $end] = [$end->copy()->startOfDay(), $start->copy()->endOfDay()];
                }
                break;
            case 'this_month':
            default:
                $start = $now->copy()->startOfMonth();
                $end = $now->copy()->endOfDay();
                $range = 'this_month';
                break;
        }

        // 1. Compute Scope Counts for the active date range (ensures exact count matching across tabs & dashboard)
        $baseRangeQuery = Order::whereBetween('created_at', [$start, $end]);
        $scopeCounts = [
            'all'       => (clone $baseRangeQuery)->count(),
            'realized'  => (clone $baseRangeQuery)->whereIn('status', Order::INCOME_STATUSES)->count(),
            'delivered' => (clone $baseRangeQuery)->where('status', 'delivered')->count(),
            'paid'      => (clone $baseRangeQuery)->where('payment_status', 'paid')->count(),
            'cancelled' => (clone $baseRangeQuery)->whereIn('status', ['cancelled', 'returned'])->count(),
            'pending'   => (clone $baseRangeQuery)->where('status', 'pending')->count(),
        ];

        // 2. Query Builder based on Status Scope
        $query = Order::with(['user', 'items.product'])
            ->whereBetween('created_at', [$start, $end]);

        if ($statusScope === 'all') {
            // Include all orders (matches Total Orders on Dashboard and Admin Orders page)
        } elseif ($statusScope === 'delivered') {
            $query->where('status', 'delivered');
        } elseif ($statusScope === 'paid') {
            $query->where('payment_status', 'paid');
        } elseif ($statusScope === 'cancelled') {
            $query->whereIn('status', ['cancelled', 'returned']);
        } elseif ($statusScope === 'pending') {
            $query->where('status', 'pending');
        } else {
            // 'realized' default: Confirmed until Delivered (excludes pending, failed, cancelled, returned)
            $statusScope = 'realized';
            $query->whereIn('status', Order::INCOME_STATUSES);
        }

        $orders = $query->latest()->get();

        // 3. Core Financial Metrics incorporating price reconciliation (Original Price, Cart Subtotal, Coupons, Wallet, ₹45 fixed delivery)
        $realizedOrders = $orders->filter(fn($o) => in_array($o->status, Order::INCOME_STATUSES));
        
        $ordersCount = $orders->count();
        $realizedCount = $realizedOrders->count();

        // 3.1 Original Price (MRP) & Discounts Breakdown
        $totalOriginalPrice = (float) $orders->sum(function ($order) {
            return $order->items->sum(function ($item) {
                $orig = (float) ($item->original_price > 0 
                    ? $item->original_price 
                    : ($item->product?->original_price > 0 ? $item->product->original_price : $item->unit_price));
                return $orig * (int) $item->quantity;
            });
        });
        $totalItemsSubtotal = (float) $orders->sum('subtotal_amount');
        $totalOfferDiscount = max(0.0, $totalOriginalPrice - $totalItemsSubtotal);
        $totalCouponDiscount = (float) $orders->sum('coupon_discount_amount');
        $totalWalletUsed = (float) $orders->sum('wallet_amount_used');

        // Fixed delivery charge at ₹45 per order (as requested: "delivery chnage as 45(fixed chey as of now next weill see")
        $fixedDeliveryRate = 45.0;
        $totalFixedDelivery = (float) ($ordersCount * $fixedDeliveryRate);

        // COD handling fee is excluded ("cod handling fee dont add here ,,,,")
        $totalCodFeesCollected = 0.0;

        // Clean Net in-hand display calculation:
        // Original Price - Offer Discounts - Coupon Discounts - Wallet Used - Delivery (Fixed ₹45/order)
        // = Cart Subtotal - Coupon Discounts - Wallet Used - Delivery (Fixed ₹45/order)
        $netDisplayAmount = (float) ($totalItemsSubtotal - $totalCouponDiscount - $totalWalletUsed - $totalFixedDelivery);

        // Revenue computation strictly excluding COD handling fee (courier collection, not store revenue)
        $computeOrderRevenue = function ($order) {
            $base = (float)$order->total_amount - (float)$order->cod_fee + (float)$order->wallet_amount_used;
            return max(0.0, $base);
        };

        if ($statusScope === 'all') {
            // When viewing All Orders, Revenue represents Realized Income + Wallet Credit (excluding COD fee)
            $totalRevenue = (float) $realizedOrders->sum($computeOrderRevenue);
            $totalCost = (float) $realizedOrders->sum('total_cost');
            $bookedPipelineValue = (float) $orders->sum($computeOrderRevenue);
            $cancelledValue = (float) $orders->whereIn('status', ['cancelled', 'returned'])->sum($computeOrderRevenue);
        } elseif ($statusScope === 'cancelled') {
            $totalRevenue = 0.0;
            $totalCost = 0.0;
            $bookedPipelineValue = (float) $orders->sum($computeOrderRevenue);
            $cancelledValue = (float) $orders->sum($computeOrderRevenue);
        } else {
            $totalRevenue = (float) $orders->sum($computeOrderRevenue);
            $totalCost = (float) $orders->sum('total_cost');
            $bookedPipelineValue = $totalRevenue;
            $cancelledValue = 0.0;
        }

        $totalGrossProfit = $totalRevenue - $totalCost;
        $overallMargin = $totalRevenue > 0 ? round(($totalGrossProfit / $totalRevenue) * 100, 1) : 0.0;
        $calcCount = ($statusScope === 'all') ? max(1, $realizedCount) : max(1, $ordersCount);
        $avgTicketSize = $totalRevenue > 0 ? round($totalRevenue / $calcCount, 2) : 0.0;
        $avgProfitPerOrder = $totalGrossProfit != 0 ? round($totalGrossProfit / $calcCount, 2) : 0.0;

        // 4. Unit Economics & Hidden Expenses Breakdown (Using ₹45 fixed courier fulfillment rate)
        $totalItemsCount = $orders->sum(fn($o) => $o->items->sum('quantity'));
        $estimatedGatewayFees = (float) $orders->sum(function ($o) {
            return in_array($o->payment_method, ['online', 'doorstep_upi']) ? round($o->total_amount * 0.02, 2) : 0.0; // 2% gateway fee
        });
        $estimatedShippingCosts = $totalFixedDelivery; // ₹45 courier fulfillment fixed as requested
        $totalShippingCollected = (float) $orders->sum('shipping_charge');
        $netShippingCost = max(0.0, $estimatedShippingCosts - $totalShippingCollected);
        $estimatedPackagingCosts = $ordersCount * 12.0; // ₹12 packaging materials
        $totalOperatingExpenses = $estimatedGatewayFees + $netShippingCost + $estimatedPackagingCosts;
        $netInHandProfit = $totalGrossProfit - $totalOperatingExpenses;
        $netInHandMargin = $totalRevenue > 0 ? round(($netInHandProfit / $totalRevenue) * 100, 1) : 0.0;

        // 5. Trajectory Trend Chart Data
        $trendLabels = [];
        $trendRevenue = [];
        $trendCost = [];
        $trendProfit = [];

        if ($range === 'this_year' || $range === 'all_time' || ($range === 'custom' && $start->diffInDays($end) > 45)) {
            $period = \Carbon\CarbonPeriod::create($start->copy()->startOfMonth(), '1 month', $end->copy()->endOfMonth());
            foreach ($period as $monthDate) {
                $mStart = $monthDate->copy()->startOfMonth();
                $mEnd = $monthDate->copy()->endOfMonth();

                $mOrders = $orders->filter(function ($o) use ($mStart, $mEnd) {
                    return $o->created_at >= $mStart && $o->created_at <= $mEnd;
                });

                $rev = (float) $mOrders->sum($computeOrderRevenue);
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

                $rev = (float) $dOrders->sum($computeOrderRevenue);
                $cost = (float) $dOrders->sum('total_cost');

                $trendLabels[] = $date->format('d M');
                $trendRevenue[] = $rev;
                $trendCost[] = $cost;
                $trendProfit[] = round($rev - $cost, 2);
            }
        }

        // 6. Payment Channels Split (User-friendly labels and accurate fees/wallet recognition)
        $paymentMethods = [];
        foreach ($orders->groupBy(fn($o) => strtolower($o->payment_method ?: 'cod')) as $method => $methodOrders) {
            $mRev = (float) $methodOrders->sum($computeOrderRevenue);
            $mCost = (float) $methodOrders->sum('total_cost');
            $mProfit = round($mRev - $mCost, 2);

            $methodName = match($method) {
                'cod'          => 'Cash on Delivery (COD)',
                'online'       => 'Prepaid Online (Razorpay)',
                'doorstep_upi' => 'Doorstep UPI (Razorpay QR)',
                'wallet'       => 'ShopCalm Wallet (100% Paid)',
                default        => strtoupper($method),
            };

            $paymentMethods[] = [
                'method'  => $method,
                'name'    => $methodName,
                'count'   => $methodOrders->count(),
                'revenue' => $mRev,
                'profit'  => $mProfit,
                'margin'  => $mRev > 0 ? round(($mProfit / $mRev) * 100, 1) : 0,
            ];
        }

        // 7. Top High-Profit Products
        $productProfits = [];
        $ordersForProductProfit = ($statusScope === 'all') ? $realizedOrders : $orders;
        foreach ($ordersForProductProfit as $order) {
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

        // 8. Recent Orders
        $recentOrders = $orders->take(10);

        return view('admin.finance.index', compact(
            'range',
            'statusScope',
            'start',
            'end',
            'scopeCounts',
            'totalOriginalPrice',
            'totalOfferDiscount',
            'totalItemsSubtotal',
            'fixedDeliveryRate',
            'totalFixedDelivery',
            'netDisplayAmount',
            'totalShippingCollected',
            'totalCodFeesCollected',
            'totalCouponDiscount',
            'totalWalletUsed',
            'bookedPipelineValue',
            'cancelledValue',
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
            'netShippingCost',
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
