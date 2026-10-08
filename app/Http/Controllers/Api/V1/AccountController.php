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

        $query = $user->orders()->with(['items.product.brand', 'items.product.category', 'fulfillment'])->latest();

        if ($status) {
            $query->where('status', $status);
        }

        $orders = $query->paginate(20);

        $allowCancellationSetting = Setting::get('allow_customer_cancellation', '1') == '1';

        $formattedOrders = collect($orders->items())->map(function (Order $order) use ($allowCancellationSetting) {
            $isPendingUnpaid = in_array($order->status, ['pending', 'failed', '']) || $order->payment_status === 'failed';
            $canCustomerCancel = !in_array($order->status, ['cancelled', 'delivered']) &&
                ($isPendingUnpaid || ($allowCancellationSetting && in_array($order->status, ['confirmed', 'processing'])));

            return [
                'id'                          => $order->id,
                'order_number'                => $order->order_number,
                'status'                      => $order->status,
                'payment_method'              => $order->payment_method,
                'payment_status'              => $order->payment_status,
                'item_count'                  => $order->items->count(),
                'subtotal_amount'             => (float) $order->subtotal_amount,
                'shipping_charge'             => (float) ($order->shipping_charge ?? 0),
                'wallet_amount_used'          => (float) ($order->wallet_amount_used ?? 0),
                'total_amount'                => (float) $order->total_amount,
                'delivery_otp'                => $order->delivery_otp,
                'is_local_bengaluru'          => $order->isLocalBengaluruDelivery(),
                'can_customer_cancel'         => $canCustomerCancel,
                'allow_customer_cancellation' => $allowCancellationSetting,
                'created_at'                  => $order->created_at ? $order->created_at->format('d M, Y') : '',
                'created_at_full'             => $order->created_at ? $order->created_at->format('d M, Y h:i A') : '',
                'items'                       => $order->items->map(function ($item) {
                    $imgPath = $item->product ? ($item->product->main_image ?? $item->product->featured_image ?? null) : null;
                    $unitPrice = (float) ($item->unit_price ?? $item->price ?? 0);
                    $qty = (int) ($item->quantity ?? 1);
                    $totPrice = (float) ($item->total_price ?? $item->subtotal ?? ($unitPrice * $qty));
                    return [
                        'id'              => $item->id,
                        'product_id'      => $item->product_id,
                        'product_slug'    => $item->product?->slug,
                        'product_name'    => $item->product_name ?? $item->product?->name ?? 'Product',
                        'brand_name'      => $item->product?->brand?->name,
                        'category_name'   => $item->product?->category?->name,
                        'product_image'   => $imgPath ? (str_starts_with($imgPath, 'http') ? $imgPath : asset('storage/' . $imgPath)) : null,
                        'selected_option' => $item->selected_option,
                        'quantity'        => $qty,
                        'original_price'  => (float) ($item->original_price ?? $unitPrice),
                        'unit_price'      => $unitPrice,
                        'price'           => $unitPrice,
                        'total_price'     => $totPrice,
                        'subtotal'        => $totPrice,
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

        $order->load(['items.product.brand', 'items.product.category', 'fulfillment', 'cancellation.cancelledBy', 'coupon', 'primaryPayment']);

        $cancellation = $order->cancellation;
        $cancellationData = null;
        if ($cancellation) {
            $cancellationData = [
                'cancelled_by_type'    => $cancellation->cancelled_by_type,
                'cancellation_reason'  => $cancellation->cancellation_reason,
                'admin_notes'          => $cancellation->admin_notes,
                'cancellation_fee'     => (float) $cancellation->cancellation_fee,
                'refund_amount'        => (float) $cancellation->refund_amount,
                'wallet_refund_amount' => (float) ($cancellation->wallet_refund_amount ?? 0),
                'online_refund_amount' => (float) ($cancellation->online_refund_amount ?? 0),
                'refund_status'        => $cancellation->refund_status,
                'online_refund_status' => $cancellation->online_refund_status,
                'refund_method'        => $cancellation->refund_method,
                'payment_reference'    => $cancellation->razorpay_refund_id ?: $cancellation->payment_reference,
                'cancelled_at'         => $cancellation->created_at ? $cancellation->created_at->format('d M, Y h:i A') : null,
            ];
        }

        $isPendingUnpaid = in_array($order->status, ['pending', 'failed', '']) || $order->payment_status === 'failed';
        $allowCancellationSetting = Setting::get('allow_customer_cancellation', '1') == '1';
        $canCustomerCancel = !in_array($order->status, ['cancelled', 'delivered']) &&
            ($isPendingUnpaid || ($allowCancellationSetting && in_array($order->status, ['confirmed', 'processing'])));

        return $this->sendResponse([
            'id'                          => $order->id,
            'order_number'                => $order->order_number,
            'invoice_number'              => 'INV-' . $order->order_number,
            'invoice_url'                 => url('/orders/' . $order->id . '/tax-invoice'),
            'status'                      => $order->status,
            'payment_method'              => $order->payment_method,
            'payment_status'              => $order->payment_status,
            'bank_reference'              => $order->primaryPayment?->bank_reference,
            'gateway_payment_id'          => $order->primaryPayment?->gateway_payment_id,
            'subtotal'                    => (float) $order->subtotal_amount,
            'shipping_cost'               => (float) ($order->shipping_charge ?? $order->shipping_cost ?? 0),
            'cod_fee'                     => (float) $order->cod_fee,
            'coupon_code'                 => $order->coupon?->code,
            'coupon_discount'             => (float) $order->coupon_discount_amount,
            'wallet_amount_used'          => (float) $order->wallet_amount_used,
            'total_amount'                => (float) $order->total_amount,
            'shipping_name'               => $order->shipping_name,
            'shipping_email'              => $order->shipping_email,
            'shipping_phone'              => $order->shipping_phone,
            'shipping_address'            => $order->shipping_address,
            'shipping_city'               => $order->shipping_city,
            'shipping_state'              => $order->shipping_state,
            'shipping_pincode'            => $order->shipping_zip,
            'shipping_country'            => $order->shipping_country ?? 'India',
            'is_local_bengaluru'          => $order->isLocalBengaluruDelivery(),
            'courier_partner'             => $order->courier_partner,
            'tracking_number'             => $order->tracking_number,
            'tracking_url'                => $order->tracking_url,
            'rider_name'                  => $order->rider_name,
            'rider_phone'                 => $order->rider_phone,
            'delivery_slot'               => $order->delivery_slot,
            'delivery_otp'                => $order->delivery_otp,
            'delivered_at'                => $order->delivered_at ? $order->delivered_at->format('d M, Y h:i A') : null,
            'can_customer_cancel'         => $canCustomerCancel,
            'allow_customer_cancellation' => $allowCancellationSetting,
            'cancellation'                => $cancellationData,
            'created_at'                  => $order->created_at ? $order->created_at->format('d M, Y h:i A') : '',
            'created_at_date'             => $order->created_at ? $order->created_at->format('d M, Y') : '',
            'items'                       => $order->items->map(function ($item) {
                $imgPath = $item->product ? ($item->product->main_image ?? $item->product->featured_image ?? null) : null;
                $unitPrice = (float) ($item->unit_price ?? $item->price ?? 0);
                $qty = (int) ($item->quantity ?? 1);
                $totPrice = (float) ($item->total_price ?? $item->subtotal ?? ($unitPrice * $qty));
                $sku = $item->product?->sku ?: ('SC-' . str_pad((string) ($item->product_id ?? $item->id), 5, '0', STR_PAD_LEFT));
                return [
                    'id'              => $item->id,
                    'product_id'      => $item->product_id,
                    'product_slug'    => $item->product?->slug,
                    'product_name'    => $item->product_name ?? $item->product?->name ?? 'Product',
                    'sku'             => $sku,
                    'brand_name'      => $item->product?->brand?->name,
                    'category_name'   => $item->product?->category?->name,
                    'product_image'   => $imgPath ? (str_starts_with($imgPath, 'http') ? $imgPath : asset('storage/' . $imgPath)) : null,
                    'selected_option' => $item->selected_option,
                    'quantity'        => $qty,
                    'original_price'  => (float) ($item->original_price ?? $unitPrice),
                    'offer_discount'  => (float) ($item->offer_discount ?? max(0, (float) ($item->original_price ?? $unitPrice) - $unitPrice)),
                    'unit_price'      => $unitPrice,
                    'price'           => $unitPrice,
                    'total_price'     => $totPrice,
                    'subtotal'        => $totPrice,
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
        $isPendingUnpaid = in_array($order->status, ['pending', 'failed']) || $order->payment_status === 'failed';

        if (!$isPendingUnpaid && Setting::get('allow_customer_cancellation', '1') != '1') {
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
        $isPendingUnpaid = in_array($order->status, ['pending', 'failed']) || $order->payment_status === 'failed';

        if (!$isPendingUnpaid && Setting::get('allow_customer_cancellation', '1') != '1') {
            return $this->sendError('Customer order cancellation is currently disabled by store management.', [], 403);
        }

        if ((int)$order->user_id !== (int)$user->id) {
            return $this->sendError('Unauthorized access to this order.', [], 403);
        }

        $request->validate([
            'cancellation_reason' => 'required|string|max:255',
            'refund_method'       => 'nullable|string|in:original_source,wallet,none',
        ]);

        try {
            $reason = $request->cancellation_reason;
            $refundMethod = $request->input('refund_method', 'original_source');
            $upiId = null;

            if ($order->payment_method === 'cod' && $order->payment_status !== 'paid') {
                $cancellation = $this->orderService->cancelCodOrderFree($order, $reason);
            } else {
                $cancellation = $this->orderService->cancelPrepaidOrder($order, $reason, $refundMethod, $upiId);
            }

            return $this->sendResponse([
                'cancellation_id' => $cancellation->id,
                'order_number'    => $order->order_number,
                'refund_amount'   => (float) $cancellation->refund_amount,
                'refund_status'   => $cancellation->refund_status,
            ], 'Order cancelled successfully.');
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

    /**
     * Store New Customer Shipping Address.
     */
    public function storeAddress(Request $request): JsonResponse
    {
        $user = $request->user();

        if (!$request->has('zip') && $request->filled('pincode')) {
            $request->merge(['zip' => $request->input('pincode')]);
        }

        $validated = $request->validate([
            'name'     => 'required|string|max:255',
            'phone'    => 'required|string|max:20',
            'address'  => 'required|string|max:500',
            'city'     => 'required|string|max:100',
            'state'    => 'required|string|max:100',
            'zip'      => 'required|string|max:10',
            'country'  => 'nullable|string|max:100',
        ]);

        $validated['country'] = $validated['country'] ?? 'India';

        $address = $user->addresses()->create($validated);

        return $this->sendResponse([
            'address' => $address,
        ], 'Delivery address saved successfully.', 201);
    }

    /**
     * Update Existing Shipping Address.
     */
    public function updateAddress(Request $request, Address $address): JsonResponse
    {
        $user = $request->user();

        if ((int) $address->user_id !== (int) $user->id) {
            return $this->sendError('Unauthorized access to this address.', [], 403);
        }

        if (!$request->has('zip') && $request->filled('pincode')) {
            $request->merge(['zip' => $request->input('pincode')]);
        }

        $validated = $request->validate([
            'name'     => 'sometimes|required|string|max:255',
            'phone'    => 'sometimes|required|string|max:20',
            'address'  => 'sometimes|required|string|max:500',
            'city'     => 'sometimes|required|string|max:100',
            'state'    => 'sometimes|required|string|max:100',
            'zip'      => 'sometimes|required|string|max:10',
            'country'  => 'nullable|string|max:100',
        ]);

        $address->update($validated);

        return $this->sendResponse([
            'address' => $address,
        ], 'Delivery address updated successfully.');
    }

    /**
     * Delete a Shipping Address.
     */
    public function deleteAddress(Request $request, Address $address): JsonResponse
    {
        $user = $request->user();

        if ((int) $address->user_id !== (int) $user->id) {
            return $this->sendError('Unauthorized access to this address.', [], 403);
        }

        $address->delete();

        return $this->sendResponse([], 'Delivery address deleted successfully.');
    }

    /**
     * Update Customer Profile (Name, Email, Avatar, or Password).
     */
    public function updateProfile(Request $request): JsonResponse
    {
        $user = $request->user();

        if ($request->filled('current_password') && $request->filled('password')) {
            return $this->changePassword($request);
        }

        $validated = $request->validate([
            'name'   => 'sometimes|required|string|max:255',
            'email'  => 'nullable|email|max:255|unique:users,email,' . $user->id,
            'avatar' => 'nullable|image|max:2048',
        ]);

        if ($request->hasFile('avatar')) {
            if ($user->avatar) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($user->avatar);
            }
            $validated['avatar'] = $request->file('avatar')->store('avatars', 'public');
        }

        $user->update($validated);

        return $this->sendResponse([
            'user' => [
                'id'            => $user->id,
                'name'          => $user->name,
                'email'         => $user->email,
                'mobile_number' => $user->mobile_number,
                'avatar'        => $user->avatar ? asset('storage/' . $user->avatar) : null,
            ],
        ], 'Profile updated successfully.');
    }

    /**
     * Change Customer Account Password.
     */
    public function changePassword(Request $request): JsonResponse
    {
        $user = $request->user();

        $request->validate([
            'current_password' => 'required|string',
            'password'         => 'required|string|min:8|confirmed',
        ]);

        if (!\Illuminate\Support\Facades\Hash::check($request->current_password, $user->password)) {
            return $this->sendError('The current password you entered is incorrect.', [
                'current_password' => ['The current password is incorrect.'],
            ], 422);
        }

        $user->update([
            'password' => \Illuminate\Support\Facades\Hash::make($request->password),
        ]);

        return $this->sendResponse([], 'Password updated successfully.');
    }
}

