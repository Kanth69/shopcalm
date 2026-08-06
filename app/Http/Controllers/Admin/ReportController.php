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

    private function getSalesReport($start, $end)
    {
        return Order::with('user')
            ->whereBetween('created_at', [$start . ' 00:00:00', $end . ' 23:59:59'])
            ->latest()
            ->get();
    }

    private function getRevenueReport($start, $end)
    {
        return Order::select(DB::raw('DATE(created_at) as date'), DB::raw('SUM(total_amount) as total'))
            ->where('status', 'delivered')
            ->whereBetween('created_at', [$start . ' 00:00:00', $end . ' 23:59:59'])
            ->groupBy('date')
            ->get();
    }

    private function getCustomersReport($start, $end)
    {
        return User::where('role_id', 2)
            ->whereBetween('created_at', [$start . ' 00:00:00', $end . ' 23:59:59'])
            ->withCount('orders')
            ->withSum('orders', 'total_amount')
            ->get();
    }

    private function getProductsReport($start, $end)
    {
        return Product::withCount(['orderItems as sold_count' => function($q) use ($start, $end) {
            $q->whereBetween('created_at', [$start . ' 00:00:00', $end . ' 23:59:59']);
        }])->orderBy('sold_count', 'desc')->get();
    }

    private function exportCsv($type, $data)
    {
        $filename = "report_{$type}_" . date('Y-m-d') . ".csv";
        $headers = [
            "Content-type"        => "text/csv",
            "Content-Disposition" => "attachment; filename=$filename",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        ];

        $callback = function() use($type, $data) {
            $file = fopen('php://output', 'w');

            if ($type == 'sales') {
                fputcsv($file, ['Order #', 'Customer', 'Amount', 'Status', 'Date']);
                foreach ($data as $row) {
                    fputcsv($file, [$row->order_number, $row->user->name, $row->total_amount, $row->status, $row->created_at]);
                }
            } elseif ($type == 'revenue') {
                fputcsv($file, ['Date', 'Total Revenue']);
                foreach ($data as $row) {
                    fputcsv($file, [$row->date, $row->total]);
                }
            }

            fclose($file);
        };

        return Response::stream($callback, 200, $headers);
    }
}
