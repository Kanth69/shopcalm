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
            'total_revenue'    => Order::whereIn('status', Order::INCOME_STATUSES)->sum('total_amount'),
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
        $order->load('items.product', 'user', 'coupon');
        return view('admin.orders.show', compact('order'));
    }

    public function cancelOrder(Request $request, Order $order)
    {
        $request->validate([
            'cancellation_reason' => 'required|string|max:255',
            'refund_method'       => 'required|in:wallet,bank',
            'admin_notes'         => 'nullable|string|max:1000',
        ]);

        try {
            $cancellation = $this->orderService->cancelOrderByAdmin(
                $order,
                $request->cancellation_reason,
                $request->refund_method,
                $request->admin_notes
            );

            return back()->with('toast', [
                'type'    => 'success',
                'title'   => 'Order Cancelled',
                'message' => "Order #{$order->order_number} has been cancelled by store admin. 100% refund credited & inventory restocked.",
            ]);
        } catch (\Exception $e) {
            return back()->with('toast', [
                'type'    => 'error',
                'title'   => 'Cancellation Failed',
                'message' => $e->getMessage(),
            ]);
        }
    }

    public function invoice(Order $order)
    {
        $order->load(['user', 'items.product', 'coupon']);
        return view('order-manager.orders.invoice', compact('order'));
    }

    public function packingSlip(Order $order)
    {
        $order->load(['user', 'items.product']);
        return view('order-manager.orders.packing-slip', compact('order'));
    }
}
