<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class StaffNotificationController extends Controller
{
    /**
     * Fetch live order updates and queue metrics for staff dashboards.
     */
    public function liveOrders(Request $request): JsonResponse
    {
        $sinceId = (int) $request->query('since_id', 0);
        $portal = $request->query('portal', 'order_manager'); // 'order_manager' or 'admin'

        $maxId = Order::max('id') ?? 0;

        $newOrders = [];
        if ($sinceId > 0 && $maxId > $sinceId) {
            $rawOrders = Order::with(['items.product', 'fulfillment'])
                ->where('id', '>', $sinceId)
                ->orderBy('id', 'desc')
                ->take(10)
                ->get();

            foreach ($rawOrders as $order) {
                $viewUrl = ($portal === 'admin') 
                    ? route('admin.orders.show', $order)
                    : route('order-manager.orders.show', $order);

                $newOrders[] = [
                    'id'               => $order->id,
                    'order_number'     => $order->order_number,
                    'total_amount'     => (float) $order->total_amount,
                    'total_amount_fmt' => '₹' . number_format($order->total_amount, 2),
                    'customer_name'    => $order->shipping_name,
                    'city'             => $order->shipping_city,
                    'pincode'          => $order->shipping_zip,
                    'status'           => $order->status,
                    'status_label'     => ucfirst(str_replace('_', ' ', $order->status)),
                    'is_local'         => $order->isLocalBengaluruDelivery(),
                    'fulfillment_badge'=> $order->fulfillment_badge,
                    'items_count'      => $order->items->sum('quantity'),
                    'payment_method'   => strtoupper($order->payment_method),
                    'payment_status'   => $order->payment_status,
                    'created_at_human' => $order->created_at->diffForHumans(),
                    'created_at_fmt'   => $order->created_at->format('d M Y, h:i A'),
                    'url'              => $viewUrl,
                ];
            }
        }

        $stats = [
            'total_orders'       => Order::count(),
            'needs_processing'   => Order::whereIn('status', ['pending', 'confirmed'])->count(),
            'packing_queue'      => Order::whereIn('status', ['processing', 'packed'])->count(),
            'in_transit'         => Order::whereIn('status', ['shipped', 'out for delivery'])->count(),
            'delivered'          => Order::where('status', 'delivered')->count(),
            'today_orders'       => Order::whereDate('created_at', today())->count(),
            'today_revenue'      => (float) Order::whereDate('created_at', today())->where('status', '!=', 'cancelled')->sum('total_amount'),
            'today_revenue_fmt'  => '₹' . number_format(Order::whereDate('created_at', today())->where('status', '!=', 'cancelled')->sum('total_amount'), 2),
        ];

        return response()->json([
            'latest_id'  => $maxId,
            'has_new'    => count($newOrders) > 0,
            'new_count'  => count($newOrders),
            'new_orders' => $newOrders,
            'stats'      => $stats,
        ]);
    }
}
