<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Product;
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
        $sinceProductId = (int) $request->query('since_product_id', 0);
        $sinceRejectedId = (int) $request->query('since_rejected_id', 0);
        $sinceAssignedId = (int) $request->query('since_assigned_id', 0);
        $portal = $request->query('portal', 'order_manager'); // 'order_manager', 'admin', 'product_manager', 'delivery'

        $maxId = Order::max('id') ?? 0;
        $maxProductId = Product::where('status', 'Pending_Approval')->max('id') ?? 0;
        $maxRejectedId = Product::where('status', 'Rejected')->max('id') ?? 0;
        $pendingProductsCount = Product::where('status', 'Pending_Approval')->count();

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

        // Real-time pending product submissions tracking for Admin
        $newPendingProducts = [];
        if ($sinceProductId > 0 && $maxProductId > $sinceProductId) {
            $rawProducts = Product::with('submitter')
                ->where('status', 'Pending_Approval')
                ->where('id', '>', $sinceProductId)
                ->orderBy('id', 'desc')
                ->take(10)
                ->get();

            foreach ($rawProducts as $prod) {
                $newPendingProducts[] = [
                    'id'             => $prod->id,
                    'name'           => $prod->name,
                    'sku'            => $prod->sku,
                    'submitter'      => $prod->submitter->name ?? 'Product Manager',
                    'created_at_human' => $prod->created_at->diffForHumans(),
                    'image_url'      => $prod->main_image ? asset('storage/' . $prod->main_image) : null,
                    'url'            => route('admin.products.show', $prod),
                ];
            }
        }

        // Real-time rejected product alerts for Product Manager
        $newRejectedProducts = [];
        if ($sinceRejectedId > 0 && $maxRejectedId > $sinceRejectedId) {
            $rawRejected = Product::where('status', 'Rejected')
                ->where('id', '>', $sinceRejectedId)
                ->orderBy('id', 'desc')
                ->take(10)
                ->get();

            foreach ($rawRejected as $prod) {
                $newRejectedProducts[] = [
                    'id'               => $prod->id,
                    'name'             => $prod->name,
                    'rejection_reason' => $prod->active_rejection_reason ?? $prod->rejection_reason ?? 'Please revise specifications or media.',
                    'url'              => route('product-manager.products.edit', $prod),
                ];
            }
        }

        // Real-time order assignment alerts for Delivery Partner
        $deliveryUser = (config('auth.guards.delivery_partner') ? Auth::guard('delivery_partner')->user() : null)
            ?? (config('auth.guards.delivery') ? Auth::guard('delivery')->user() : null)
            ?? Auth::user();
        $maxAssignedOrderId = 0;
        $newAssignedOrders = [];
        if ($deliveryUser) {
            $maxAssignedOrderId = Order::whereHas('fulfillment', fn($q) => $q->where('rider_id', $deliveryUser->id))->max('id') ?? 0;
            if ($sinceAssignedId > 0 && $maxAssignedOrderId > $sinceAssignedId) {
                $rawAssigned = Order::whereHas('fulfillment', fn($q) => $q->where('rider_id', $deliveryUser->id))
                    ->where('id', '>', $sinceAssignedId)
                    ->orderBy('id', 'desc')
                    ->take(10)
                    ->get();

                foreach ($rawAssigned as $ord) {
                    $newAssignedOrders[] = [
                        'id'           => $ord->id,
                        'order_number' => $ord->order_number,
                        'city'         => $ord->shipping_city,
                        'url'          => route('delivery.orders.show', $ord->id),
                    ];
                }
            }
        }

        $stats = [
            'total_orders'           => Order::count(),
            'needs_processing'       => Order::whereIn('status', ['pending', 'confirmed'])->count(),
            'packing_queue'          => Order::whereIn('status', ['processing', 'packed'])->count(),
            'in_transit'             => Order::whereIn('status', ['shipped', 'out for delivery'])->count(),
            'delivered'              => Order::where('status', 'delivered')->count(),
            'today_orders'           => Order::whereDate('created_at', today())->count(),
            'today_revenue'          => (float) Order::whereDate('created_at', today())->where('status', '!=', 'cancelled')->sum('total_amount'),
            'today_revenue_fmt'      => '₹' . number_format(Order::whereDate('created_at', today())->where('status', '!=', 'cancelled')->sum('total_amount'), 2),
            'pending_products_count' => $pendingProductsCount,
        ];

        return response()->json([
            'latest_id'                => $maxId,
            'latest_product_id'        => $maxProductId,
            'latest_rejected_id'       => $maxRejectedId,
            'latest_assigned_order_id' => $maxAssignedOrderId,
            'has_new'                  => count($newOrders) > 0,
            'new_count'                => count($newOrders),
            'new_orders'               => $newOrders,
            'has_new_products'         => count($newPendingProducts) > 0,
            'new_pending_products'     => $newPendingProducts,
            'has_new_rejected'         => count($newRejectedProducts) > 0,
            'new_rejected_products'    => $newRejectedProducts,
            'has_new_assigned'         => count($newAssignedOrders) > 0,
            'new_assigned_orders'      => $newAssignedOrders,
            'pending_products_count'   => $pendingProductsCount,
            'stats'                    => $stats,
        ]);
    }
}
