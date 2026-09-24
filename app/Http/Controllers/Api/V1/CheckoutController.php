<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\Order;
use App\Models\Pincode;
use App\Models\Setting;
use App\Services\CheckoutService;
use App\Services\OrderService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CheckoutController extends BaseApiController
{
    protected $checkoutService;
    protected $orderService;

    public function __construct(CheckoutService $checkoutService, OrderService $orderService)
    {
        $this->checkoutService = $checkoutService;
        $this->orderService = $orderService;
    }

    /**
     * Pincode Serviceability & COD Fee Checker Endpoint.
     */
    public function checkPincode(Request $request): JsonResponse
    {
        $request->validate([
            'pincode' => 'required|string|size:6',
        ]);

        $pincodeRecord = Pincode::where('pincode', $request->pincode)->first();

        if ($pincodeRecord && !$pincodeRecord->is_serviceable) {
            return $this->sendResponse([
                'is_serviceable' => false,
                'pincode'        => $request->pincode,
            ], 'Sorry, we do not deliver to this pincode currently.');
        }

        $defaultCodFee = (float) Setting::get('cod_flat_fee', 40.00);
        $codFee = $pincodeRecord ? (float) $pincodeRecord->cod_fee : $defaultCodFee;
        $isCodAllowed = $pincodeRecord ? (bool) $pincodeRecord->is_cod_available : true;
        $estimatedDays = $pincodeRecord && $pincodeRecord->estimated_days ? $pincodeRecord->estimated_days : '2 - 4';

        return $this->sendResponse([
            'is_serviceable' => true,
            'pincode'        => $request->pincode,
            'city'           => $pincodeRecord->city ?? null,
            'state'          => $pincodeRecord->state ?? null,
            'is_cod_allowed' => $isCodAllowed,
            'cod_fee'        => $codFee,
            'estimated_days' => $estimatedDays . ' Business Days',
        ], 'Pincode is serviceable.');
    }

    /**
     * Validate Checkout Summary & Apply Coupon / Wallet.
     */
    public function validateCheckout(Request $request): JsonResponse
    {
        $user = $request->user();
        $cart = session()->get('cart', []);

        if (empty($cart)) {
            return $this->sendError('Your cart is empty.', [], 400);
        }

        $subtotal = array_sum(array_map(function($i) { return $i['price'] * $i['quantity']; }, $cart));
        $freeShippingMin = (float) Setting::get('free_shipping_min', 499);
        $deliveryCharge = ($subtotal >= $freeShippingMin) ? 0.00 : 40.00;

        $pincode = $request->input('shipping_pincode');
        $codFee = 40.00;
        if ($pincode) {
            $pincodeRecord = Pincode::where('pincode', $pincode)->first();
            if ($pincodeRecord) {
                $codFee = (float) $pincodeRecord->cod_fee;
            }
        }

        $walletBalance = (float) app(\App\Services\WalletService::class)->getOrCreateWallet($user)->balance;

        return $this->sendResponse([
            'subtotal'        => round($subtotal, 2),
            'delivery_charge' => round($deliveryCharge, 2),
            'cod_fee'         => round($codFee, 2),
            'wallet_balance'  => round($walletBalance, 2),
            'grand_total'     => round($subtotal + $deliveryCharge, 2),
        ], 'Checkout validation successful.');
    }

    /**
     * Place Order Endpoint (Prepaid or Cash on Delivery).
     */
    public function placeOrder(Request $request): JsonResponse
    {
        $user = $request->user();

        $validated = $request->validate([
            'shipping_name'    => 'required|string|max:255',
            'shipping_phone'   => 'required|string|max:15',
            'shipping_address' => 'required|string|max:500',
            'shipping_city'    => 'required|string|max:100',
            'shipping_state'   => 'required|string|max:100',
            'shipping_pincode' => 'required|string|size:6',
            'payment_method'   => 'required|string|in:cod,online',
            'use_wallet'       => 'nullable|boolean',
            'coupon_code'      => 'nullable|string',
        ]);

        try {
            $order = $this->checkoutService->placeOrder($user, $validated);

            // Send HTML Email Confirmation (Only if customer provided an email address)
            app(\App\Services\EmailService::class)->sendOrderConfirmation($order);

            return $this->sendResponse([
                'order_id'       => $order->id,
                'order_number'   => $order->order_number,
                'status'         => $order->status,
                'total_amount'   => (float) $order->total_amount,
                'payment_method' => $order->payment_method,
                'payment_status' => $order->payment_status,
                'created_at'     => $order->created_at->format('Y-m-d H:i:s'),
            ], 'Order placed successfully.', 201);
        } catch (\Exception $e) {
            return $this->sendError($e->getMessage(), [], 422);
        }
    }

    /**
     * Switch Failed Online Payment Order to COD.
     */
    public function switchToCod(Request $request, Order $order): JsonResponse
    {
        $user = $request->user();

        if ((int)$order->user_id !== (int)$user->id) {
            return $this->sendError('Unauthorized access to this order.', [], 403);
        }

        if (!in_array($order->payment_status, ['failed', 'pending'])) {
            return $this->sendError('This order cannot be switched to COD.', [], 400);
        }

        try {
            // Apply COD fee based on shipping pincode
            $pincodeRecord = Pincode::where('pincode', $order->shipping_zip)->first();
            $codFee = $pincodeRecord ? (float) $pincodeRecord->cod_fee : (float) Setting::get('cod_flat_fee', 40.00);

            $order->update([
                'payment_method' => 'cod',
                'payment_status' => 'pending',
                'cod_fee'        => $codFee,
                'total_amount'   => $order->subtotal_amount + $order->shipping_cost + $codFee - $order->coupon_discount_amount - $order->wallet_amount_used,
                'status'         => 'confirmed', // COD orders are directly confirmed
            ]);

            return $this->sendResponse([
                'order_id'       => $order->id,
                'order_number'   => $order->order_number,
                'status'         => $order->status,
                'payment_method' => 'cod',
                'total_amount'   => (float) $order->total_amount,
            ], 'Order switched to Cash on Delivery & confirmed.');
        } catch (\Exception $e) {
            return $this->sendError($e->getMessage(), [], 422);
        }
    }
}
