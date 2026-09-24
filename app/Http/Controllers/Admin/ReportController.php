<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        if (!auth()->user()->isAdmin()) {
            abort(403);
        }

        $type = $request->input('type', 'sales');
        if ($type === 'profit_loss' && !auth()->user()->isSuperAdmin()) {
            abort(403, 'Unauthorized access to Super Admin Financial P&L Reports.');
        }

        $preset = $request->input('preset');
        $now = Carbon::now();

        if ($preset === 'today') {
            $start = $now->toDateString();
            $end = $now->toDateString();
        } elseif ($preset === '7_days' || $preset === 'last_7_days' || $preset === 'this_week') {
            $start = $now->copy()->subDays(6)->toDateString();
            $end = $now->toDateString();
        } elseif ($preset === 'this_month') {
            $start = $now->copy()->startOfMonth()->toDateString();
            $end = $now->toDateString();
        } elseif ($preset === 'this_year') {
            $start = $now->copy()->startOfYear()->toDateString();
            $end = $now->toDateString();
        } elseif ($preset === 'all_time') {
            $start = '2024-01-01';
            $end = $now->toDateString();
        } else {
            $start = $request->input('start_date', now()->startOfMonth()->toDateString());
            $end = $request->input('end_date', now()->toDateString());
        }

        $data = match($type) {
            'orders' => $this->getOrdersReport($start, $end),
            'sales' => $this->getSalesReport($start, $end),
            'revenue' => $this->getRevenueReport($start, $end),
            'customers' => $this->getCustomersReport($start, $end),
            'products' => $this->getProductsReport($start, $end),
            'profit_loss' => $this->getProfitLossReport($start, $end),
            default => []
        };

        if ($request->has('export')) {
            return $this->exportCsv($type, $data);
        }

        return view('admin.reports.index', compact('data', 'type', 'start', 'end', 'preset'));
    }

    private function getOrdersReport($start, $end)
    {
        return Order::with(['user', 'items.product'])
            ->whereBetween('created_at', [$start . ' 00:00:00', $end . ' 23:59:59'])
            ->latest()
            ->get();
    }

    private function getSalesReport($start, $end)
    {
        return Order::with(['user', 'items.product'])
            ->whereNotIn('status', ['cancelled', 'returned'])
            ->whereBetween('created_at', [$start . ' 00:00:00', $end . ' 23:59:59'])
            ->latest()
            ->get();
    }

    private function getRevenueReport($start, $end)
    {
        return Order::select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('COUNT(id) as orders_count'),
                DB::raw('SUM(total_amount) as revenue')
            )
            ->whereNotIn('status', ['cancelled', 'returned'])
            ->whereBetween('created_at', [$start . ' 00:00:00', $end . ' 23:59:59'])
            ->groupBy('date')
            ->orderBy('date', 'desc')
            ->get();
    }

    private function getCustomersReport($start, $end)
    {
        return User::where('role_id', User::ROLE_CUSTOMER)
            ->withCount(['orders as orders_count' => fn($q) => $q->whereBetween('created_at', [$start . ' 00:00:00', $end . ' 23:59:59'])])
            ->withSum(['orders as total_spent' => fn($q) => $q->whereBetween('created_at', [$start . ' 00:00:00', $end . ' 23:59:59'])], 'total_amount')
            ->latest()
            ->get();
    }

    private function getProductsReport($start, $end)
    {
        return Product::with('category')
            ->withCount(['orderItems as total_qty' => function($q) use ($start, $end) {
                $q->whereBetween('created_at', [$start . ' 00:00:00', $end . ' 23:59:59']);
            }])
            ->withSum(['orderItems as revenue' => function($q) use ($start, $end) {
                $q->whereBetween('created_at', [$start . ' 00:00:00', $end . ' 23:59:59']);
            }], 'total_price')
            ->orderBy('total_qty', 'desc')
            ->get();
    }

    private function getProfitLossReport($start, $end)
    {
        return Order::with(['user', 'items.product'])
            ->whereNotIn('status', ['cancelled', 'returned'])
            ->whereBetween('created_at', [$start . ' 00:00:00', $end . ' 23:59:59'])
            ->latest()
            ->get();
    }

    private function exportCsv($type, $data)
    {
        $filename = "shopcalm_statement_{$type}_" . date('Y-m-d_His') . ".csv";
        $headers = [
            "Content-type"        => "text/csv",
            "Content-Disposition" => "attachment; filename=$filename",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        ];

        $callback = function() use($type, $data) {
            $file = fopen('php://output', 'w');

            if ($type == 'orders') {
                fputcsv($file, ['Order #', 'Customer Name', 'Email', 'Amount (INR)', 'Payment Status', 'Fulfillment Status', 'Placed Date']);
                foreach ($data as $row) {
                    fputcsv($file, [
                        $row->order_number, 
                        $row->user?->name ?? 'Guest User', 
                        $row->user?->email ?? 'N/A', 
                        $row->total_amount, 
                        strtoupper($row->payment_status), 
                        strtoupper($row->status), 
                        $row->created_at->format('Y-m-d H:i:s')
                    ]);
                }
            } elseif ($type == 'sales') {
                fputcsv($file, ['Order #', 'Customer Name', 'Payment Method', 'Items Count', 'Gross Revenue (INR)', 'Delivered Date']);
                foreach ($data as $row) {
                    fputcsv($file, [
                        $row->order_number, 
                        $row->user?->name ?? 'Guest User', 
                        strtoupper($row->payment_method ?? 'COD'),
                        $row->items->sum('quantity'),
                        $row->total_amount, 
                        $row->created_at->format('Y-m-d H:i:s')
                    ]);
                }
            } elseif ($type == 'profit_loss') {
                fputcsv($file, ['Order #', 'Placed Date', 'Customer Name', 'Items Count', 'Revenue Collected (INR)', 'Product Cost COGS (INR)', 'Gross Profit (INR)', 'Margin (%)', 'Payment Method', 'Status']);
                foreach ($data as $row) {
                    fputcsv($file, [
                        $row->order_number,
                        $row->created_at->format('Y-m-d H:i:s'),
                        $row->user?->name ?? 'Guest User',
                        $row->items->sum('quantity'),
                        $row->total_amount,
                        $row->total_cost,
                        $row->gross_profit,
                        $row->profit_margin . '%',
                        strtoupper($row->payment_method ?? 'COD'),
                        strtoupper($row->status)
                    ]);
                }
            } elseif ($type == 'revenue') {
                fputcsv($file, ['Date', 'Delivered Orders Count', 'Total Revenue (INR)']);
                foreach ($data as $row) {
                    fputcsv($file, [$row->date, $row->orders_count, $row->revenue]);
                }
            } elseif ($type == 'customers') {
                fputcsv($file, ['Customer Name', 'Email', 'Total Orders', 'Total Spent (INR)', 'Registration Date']);
                foreach ($data as $row) {
                    fputcsv($file, [
                        $row->name, 
                        $row->email, 
                        $row->orders_count ?? 0, 
                        $row->total_spent ?? 0, 
                        $row->created_at->format('Y-m-d H:i:s')
                    ]);
                }
            } elseif ($type == 'products') {
                fputcsv($file, ['Product Name', 'SKU', 'Category', 'Base Price (INR)', 'Units Sold', 'Total Sales Revenue (INR)']);
                foreach ($data as $row) {
                    fputcsv($file, [
                        $row->name, 
                        $row->sku, 
                        $row->category?->name ?? 'Uncategorized', 
                        $row->price, 
                        $row->total_qty ?? 0, 
                        $row->revenue ?? 0
                    ]);
                }
            }

            fclose($file);
        };

        return Response::stream($callback, 200, $headers);
    }
}
