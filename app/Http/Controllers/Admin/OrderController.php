<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Services\OrderService;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    protected $orderService;

    public function __construct(OrderService $orderService)
    {
        $this->orderService = $orderService;
    }

    public function index(Request $request)
    {
        $orders = $this->orderService->getAdminOrders($request);

        $stats = [
            'total_orders'     => Order::count(),
            'total_revenue'    => Order::where('status', '!=', 'cancelled')->sum('total_amount'),
            'pending_count'    => Order::where('status', 'pending')->count(),
            'processing_count' => Order::whereIn('status', ['confirmed', 'packed', 'shipped', 'out for delivery'])->count(),
            'delivered_count'  => Order::where('status', 'delivered')->count(),
            'cancelled_count'  => Order::where('status', 'cancelled')->count(),
        ];

        $statusCounts = Order::selectRaw('status, count(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();

        return view('admin.orders.index', compact('orders', 'stats', 'statusCounts'));
    }

    public function show(Order $order)
    {
        $order->load('items.product', 'user');
        return view('admin.orders.show', compact('order'));
    }

    public function updateStatus(Request $request, Order $order)
    {
        $request->validate(['status' => 'required|in:pending,confirmed,packed,shipped,out for delivery,delivered,cancelled']);
        $this->orderService->updateOrderStatus($order, $request->status);
        return back()->with('success', 'Order status updated successfully.');
    }
}
