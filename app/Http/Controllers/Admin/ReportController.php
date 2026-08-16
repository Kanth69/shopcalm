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
        $start = $request->input('start_date', now()->startOfMonth()->toDateString());
        $end = $request->input('end_date', now()->toDateString());

        $data = match($type) {
            'orders' => $this->getOrdersReport($start, $end),
            'sales' => $this->getSalesReport($start, $end),
            'revenue' => $this->getRevenueReport($start, $end),
            'customers' => $this->getCustomersReport($start, $end),
            'products' => $this->getProductsReport($start, $end),
            default => []
        };

        if ($request->has('export')) {
            return $this->exportCsv($type, $data);
        }

        return view('admin.reports.index', compact('data', 'type', 'start', 'end'));
    }

    private function getOrdersReport($start, $end)
    {
        return Order::with(['user' => fn($q) => $q->withTrashed()])
            ->whereBetween('created_at', [$start . ' 00:00:00', $end . ' 23:59:59'])
            ->latest()
            ->get();
    }

    private function getSalesReport($start, $end)
    {
        return Order::with(['user' => fn($q) => $q->withTrashed()])
            ->where('status', 'delivered')
            ->where('payment_status', 'paid')
            ->whereBetween('created_at', [$start . ' 00:00:00', $end . ' 23:59:59'])
            ->latest()
            ->get();
    }

    private function getRevenueReport($start, $end)
    {
        return Order::select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('COUNT(id) as order_count'),
                DB::raw('SUM(total_amount) as total')
            )
            ->where('status', 'delivered')
            ->where('payment_status', 'paid')
            ->whereBetween('created_at', [$start . ' 00:00:00', $end . ' 23:59:59'])
            ->groupBy('date')
            ->orderBy('date', 'desc')
            ->get();
    }

    private function getCustomersReport($start, $end)
    {
        return User::where('role_id', User::ROLE_CUSTOMER)
            ->whereBetween('created_at', [$start . ' 00:00:00', $end . ' 23:59:59'])
            ->withCount('orders')
            ->withSum(['orders' => fn($q) => $q->where('payment_status', 'paid')], 'total_amount')
            ->latest()
            ->get();
    }

    private function getProductsReport($start, $end)
    {
        return Product::with('category')
            ->withCount(['orderItems as sold_count' => function($q) use ($start, $end) {
                $q->whereBetween('created_at', [$start . ' 00:00:00', $end . ' 23:59:59']);
            }])
            ->withSum(['orderItems as total_sales_amount' => function($q) use ($start, $end) {
                $q->whereBetween('created_at', [$start . ' 00:00:00', $end . ' 23:59:59']);
            }], 'total_price')
            ->orderBy('sold_count', 'desc')
            ->get();
    }

    private function exportCsv($type, $data)
    {
        $filename = "shopcalm_report_{$type}_" . date('Y-m-d_His') . ".csv";
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
                        $row->user?->name ?? 'Guest/Deleted User', 
                        $row->user?->email ?? 'N/A', 
                        $row->total_amount, 
                        strtoupper($row->payment_status), 
                        strtoupper($row->status), 
                        $row->created_at->format('Y-m-d H:i:s')
                    ]);
                }
            } elseif ($type == 'sales') {
                fputcsv($file, ['Order #', 'Customer Name', 'Gross Income (INR)', 'Status', 'Delivered Date']);
                foreach ($data as $row) {
                    fputcsv($file, [
                        $row->order_number, 
                        $row->user?->name ?? 'Guest/Deleted User', 
                        $row->total_amount, 
                        strtoupper($row->status), 
                        $row->created_at->format('Y-m-d H:i:s')
                    ]);
                }
            } elseif ($type == 'revenue') {
                fputcsv($file, ['Date', 'Delivered Orders Count', 'Total Revenue (INR)']);
                foreach ($data as $row) {
                    fputcsv($file, [$row->date, $row->order_count, $row->total]);
                }
            } elseif ($type == 'customers') {
                fputcsv($file, ['Customer Name', 'Email', 'Mobile', 'Total Orders', 'Total Spent (INR)', 'Registration Date']);
                foreach ($data as $row) {
                    fputcsv($file, [
                        $row->name, 
                        $row->email, 
                        $row->mobile_number ?? 'N/A', 
                        $row->orders_count, 
                        $row->orders_sum_total_amount ?? 0, 
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
                        $row->sold_count ?? 0, 
                        $row->total_sales_amount ?? 0
                    ]);
                }
            }

            fclose($file);
        };

        return Response::stream($callback, 200, $headers);
    }
}
