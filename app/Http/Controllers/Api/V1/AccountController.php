<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\Address;
use App\Models\Order;
use App\Models\Setting;
use App\Services\OrderService;
use App\Services\WalletService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AccountController extends BaseApiController
{
    protected $orderService;
    protected $walletService;

    public function __construct(OrderService $orderService, WalletService $walletService)
    {
        $this->orderService = $orderService;
        $this->walletService = $walletService;
    }

    /**
     * Customer Orders History List.
     */
    public function orders(Request $request): JsonResponse
    {
        $user = $request->user();
        $status = $request->query('status');

        $query = $user->orders()->with(['items.product'])->latest();

        if ($status) {
            $query->where('status', $status);
        }

        $orders = $query->paginate(10);

        $formattedOrders = collect($orders->items())->map(function (Order $order) {
            return [
                'id'             => $order->id,
                'order_number'   => $order->order_number,
                'status'         => $order->status,
                'payment_method' => $order->payment_method,
                'payment_status' => $order->payment_status,
                'item_count'     => $order->items->count(),
                'total_amount'   => (float) $order->total_amount,
                'created_at'     => $order->created_at ? $order->created_at->format('Y-m-d H:i:s') : null,
                'items'          => $order->items->map(function ($item) {
                    return [
                        'product_id'      => $item->product_id,
                        'product_name'    => $item->product_name ?? $item->product?->name,
                        'product_image'   => $item->product?->featured_image ? asset('storage/' . $item->product->featured_image) : null,
                        'selected_option' => $item->selected_option,
                        'quantity'        => (int) $item->quantity,
                        'price'           => (float) $item->price,
                    ];
                }),
            ];
        });

        return $this->sendResponse([
            'orders'       => $formattedOrders,
            'current_page' => $orders->currentPage(),
            'last_page'    => $orders->lastPage(),
            'total'        => $orders->total(),
        ], 'Customer orders retrieved.');
    }

    /**
     * Single Order Details with Stepper & Logistics Info.
     */
    public function orderDetails(Request $request, Order $order): JsonResponse
    {
        $user = $request->user();

        if ((int)$order->user_id !== (int)$user->id) {
            return $this->sendError('Unauthorized access to this order.', [], 403);
        }

        $order->load(['items.product', 'cancellation.cancelledBy']);

        $cancellation = $order->cancellation;
        $cancellationData = null;
        if ($cancellation) {
            $cancellationData = [
                'cancelled_by_type'   => $cancellation->cancelled_by_type,
                'cancellation_reason' => $cancellation->cancellation_reason,
                'admin_notes'         => $cancellation->admin_notes,
                'cancellation_fee'    => (float) $cancellation->cancellation_fee,
                'refund_amount'       => (float) $cancellation->refund_amount,
                'refund_status'       => $cancellation->refund_status,
                'refund_method'       => $cancellation->refund_method,
            ];
        }

        $allowCancellationSetting = Setting::get('allow_customer_cancellation', '1') == '1';
        $canCustomerCancel = $allowCancellationSetting && in_array($order->status, ['pending', 'confirmed']);

        return $this->sendResponse([
            'id'                       => $order->id,
            'order_number'             => $order->order_number,
            'status'                   => $order->status,
            'payment_method'           => $order->payment_method,
            'payment_status'           => $order->payment_status,
            'subtotal'                 => (float) $order->subtotal_amount,
            'shipping_cost'            => (float) $order->shipping_cost,
            'cod_fee'                  => (float) $order->cod_fee,
            'coupon_discount'          => (float) $order->coupon_discount_amount,
            'wallet_amount_used'       => (float) $order->wallet_amount_used,
            'total_amount'             => (float) $order->total_amount,
            'shipping_name'            => $order->shipping_name,
            'shipping_phone'           => $order->shipping_phone,
            'shipping_address'         => $order->shipping_address,
            'shipping_city'            => $order->shipping_city,
            'shipping_state'           => $order->shipping_state,
            'shipping_pincode'         => $order->shipping_zip,
            'courier_partner'          => $order->courier_partner,
            'tracking_number'          => $order->tracking_number,
            'rider_name'               => $order->rider_name,
            'can_customer_cancel'      => $canCustomerCancel,
            'cancellation'             => $cancellationData,
            'created_at'               => $order->created_at->format('Y-m-d H:i:s'),
            'items'                    => $order->items->map(function ($item) {
                return [
                    'product_id'      => $item->product_id,
                    'product_name'    => $item->product_name ?? $item->product?->name,
                    'product_image'   => $item->product?->featured_image ? asset('storage/' . $item->product->featured_image) : null,
                    'selected_option' => $item->selected_option,
                    'quantity'        => (int) $item->quantity,
                    'price'           => (float) $item->price,
                    'subtotal'        => (float) $item->subtotal,
                ];
            }),
        ], 'Order details retrieved.');
    }

    /**
     * Cancellation Breakdown Summary for Customer Modal.
     */
    public function cancellationSummary(Request $request, Order $order): JsonResponse
    {
        $user = $request->user();

        if (Setting::get('allow_customer_cancellation', '1') != '1') {
            return $this->sendError('Customer order cancellation is currently disabled by store management.', [], 403);
        }

        if ((int)$order->user_id !== (int)$user->id) {
            return $this->sendError('Unauthorized access to this order.', [], 403);
        }

        $summary = $this->orderService->calculateCancellationSummary($order);
        return $this->sendResponse([
            'summary' => $summary,
            'is_cod'  => $order->payment_method === 'cod',
            'is_paid' => $order->payment_status === 'paid',
        ], 'Cancellation summary calculated.');
    }

    /**
     * Customer Cancel Order Endpoint.
     */
    public function cancelOrder(Request $request, Order $order): JsonResponse
    {
        $user = $request->user();

        if (Setting::get('allow_customer_cancellation', '1') != '1') {
            return $this->sendError('Customer order cancellation is currently disabled by store management.', [], 403);
        }

        if ((int)$order->user_id !== (int)$user->id) {
            return $this->sendError('Unauthorized access to this order.', [], 403);
        }

        $request->validate([
            'cancellation_reason' => 'required|string|max:255',
            'refund_method'       => 'nullable|string|in:wallet,bank_upi,none',
            'refund_upi_id'       => 'nullable|string|max:100',
        ]);

        try {
            $reason = $request->cancellation_reason;
            $refundMethod = $request->input('refund_method', 'wallet');
            $upiId = $request->input('refund_upi_id');

            if ($order->payment_method === 'cod' && $order->payment_status !== 'paid') {
                return $this->sendError('For unpaid COD orders, please pay the GST cancellation fee or contact support.', [], 400);
            }

            $cancellation = $this->orderService->cancelPrepaidOrder($order, $reason, $refundMethod, $upiId);

            return $this->sendResponse([
                'cancellation_id' => $cancellation->id,
                'order_number'    => $order->order_number,
                'refund_amount'   => (float) $cancellation->refund_amount,
                'refund_status'   => $cancellation->refund_status,
            ], 'Order cancelled successfully. Refund processed to Store Wallet.');
        } catch (\Exception $e) {
            return $this->sendError($e->getMessage(), [], 422);
        }
    }

    /**
     * Get Store Wallet Balance & Transaction History.
     */
    public function wallet(Request $request): JsonResponse
    {
        $user = $request->user();
        $wallet = $this->walletService->getOrCreateWallet($user);

        $transactions = $wallet->transactions()->latest()->take(20)->get()->map(function ($tx) {
            return [
                'id'          => $tx->id,
                'type'        => $tx->type, // CREDIT or DEBIT
                'amount'      => (float) $tx->amount,
                'action'      => $tx->action,
                'description' => $tx->description,
                'created_at'  => $tx->created_at ? $tx->created_at->format('Y-m-d H:i:s') : null,
            ];
        });

        return $this->sendResponse([
            'balance'      => (float) $wallet->balance,
            'is_active'    => (bool) $wallet->is_active,
            'transactions' => $transactions,
        ], 'Store wallet data retrieved.');
    }

    /**
     * Get Saved Customer Shipping Addresses.
     */
    public function addresses(Request $request): JsonResponse
    {
        $user = $request->user();
        $addresses = Address::where('user_id', $user->id)->latest()->get();

        return $this->sendResponse([
            'addresses' => $addresses,
        ], 'Saved addresses retrieved.');
    }
}
