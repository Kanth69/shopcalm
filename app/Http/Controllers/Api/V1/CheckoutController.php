<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\Order;
use App\Models\Pincode;
use App\Models\Setting;
use App\Services\CartService;
use App\Services\CheckoutService;
use App\Services\CouponService;
use App\Services\OrderService;
use App\Services\RazorpayService;
use App\Services\WalletService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Exception;

class CheckoutController extends BaseApiController
{
    protected CheckoutService $checkoutService;
    protected OrderService $orderService;
    protected CartService $cartService;
    protected CouponService $couponService;
    protected WalletService $walletService;
    protected RazorpayService $razorpayService;

    public function __construct(
        CheckoutService $checkoutService,
        OrderService $orderService,
        CartService $cartService,
        CouponService $couponService,
        WalletService $walletService,
        RazorpayService $razorpayService
    ) {
        $this->checkoutService = $checkoutService;
        $this->orderService = $orderService;
        $this->cartService = $cartService;
        $this->couponService = $couponService;
        $this->walletService = $walletService;
        $this->razorpayService = $razorpayService;
    }

    /**
     * Pincode Serviceability & COD Fee Checker Endpoint.
     */
    public function checkPincode(Request $request): JsonResponse
    {
        $request->validate([
            'pincode' => 'required|string|size:6',
        ]);

        $deliveryService = app(\App\Services\DeliveryService::class);
        $result = $deliveryService->checkServiceability($request->pincode);

        if (!$result['is_serviceable']) {
            return $this->sendResponse([
                'is_serviceable' => false,
                'pincode'        => $request->pincode,
            ], 'Sorry, delivery is currently unavailable to this PIN code.');
        }

        $pincodeRecord = Pincode::where('pincode', $request->pincode)->first();
        $isCodAllowed = $pincodeRecord ? (bool) $pincodeRecord->is_cod_available : true;
        $estimatedDays = $pincodeRecord && $pincodeRecord->estimated_days ? $pincodeRecord->estimated_days : ($result['delivery_days'] ?? 3) . ' Business Days';

        return $this->sendResponse([
            'is_serviceable' => true,
            'pincode'        => $request->pincode,
            'city'           => $result['city'] ?? "PIN {$request->pincode}",
            'state'          => $result['state'] ?? 'India',
            'is_cod_allowed' => $isCodAllowed,
            'cod_fee'        => (float) $result['cod_fee'],
            'delivery_charge'=> (float) $result['delivery_charge'],
            'estimated_days' => $estimatedDays,
        ], 'PIN code is serviceable.');
    }

    /**
     * Validate Checkout Summary & Apply Coupon / Wallet.
     */
    public function validateCheckout(Request $request): JsonResponse
    {
        $user = $request->user();
        $cart = $this->cartService->getSelectedCart();

        if ($cart->items->isEmpty()) {
            return $this->sendError('Your cart has no items selected for checkout.', [], 400);
        }

        $subtotal = (float) $this->cartService->subtotal();
        $freeShippingMin = (float) Setting::get('free_shipping_min', 499);
        $pincode = $request->input('shipping_pincode') ?: $request->input('shipping_zip');

        $deliveryCharge = ($subtotal >= $freeShippingMin) ? 0.00 : 40.00;
        $codFee = (float) Setting::get('cod_flat_fee', 40.00);

        if ($pincode) {
            $pincodeRecord = Pincode::where('pincode', $pincode)->first();
            if ($pincodeRecord) {
                $codFee = (float) $pincodeRecord->cod_fee;
                if ($subtotal < $freeShippingMin && $pincodeRecord->delivery_charge !== null) {
                    $deliveryCharge = (float) $pincodeRecord->delivery_charge;
                }
            }
        }

        // Coupon Handling
        $couponCode = $request->input('coupon_code');
        $couponDiscount = 0.00;
        $couponMessage = null;

        if ($couponCode) {
            try {
                $coupon = $this->couponService->validateCoupon($couponCode, $user, $subtotal);
                $couponDiscount = (float) $this->couponService->calculateDiscount($coupon, $subtotal);
                $couponMessage = "Coupon '{$couponCode}' applied successfully (-₹{$couponDiscount}).";
            } catch (Exception $e) {
                return $this->sendError($e->getMessage(), [], 422);
            }
        }

        // Wallet Balance
        $wallet = $this->walletService->getOrCreateWallet($user);
        $walletBalance = (float) $wallet->balance;
        $useWallet = (bool) $request->input('use_wallet', false);

        $payableBeforeWallet = max(0, $subtotal - $couponDiscount + $deliveryCharge);
        $walletUsed = 0.00;

        if ($useWallet && $walletBalance > 0) {
            $walletUsed = min($walletBalance, $payableBeforeWallet);
        }

        $grandTotal = max(0, $payableBeforeWallet - $walletUsed);

        return $this->sendResponse([
            'subtotal'                => round($subtotal, 2),
            'coupon_code'             => $couponCode,
            'coupon_discount'         => round($couponDiscount, 2),
            'coupon_message'          => $couponMessage,
            'free_shipping_min'       => $freeShippingMin,
            'delivery_charge'         => round($deliveryCharge, 2),
            'cod_fee'                 => round($codFee, 2),
            'wallet_balance'          => round($walletBalance, 2),
            'wallet_amount_used'      => round($walletUsed, 2),
            'grand_total'             => round($grandTotal, 2),
            'grand_total_with_cod'    => round($grandTotal + $codFee, 2),
            'item_count'              => $cart->items->count(),
        ], 'Checkout validation successful.');
    }

    /**
     * Apply Coupon Code Endpoint.
     */
    public function applyCoupon(Request $request): JsonResponse
    {
        $request->validate([
            'coupon_code' => 'required|string|max:50',
        ]);

        $user = $request->user();
        $subtotal = (float) $this->cartService->subtotal();

        try {
            $coupon = $this->couponService->validateCoupon($request->coupon_code, $user, $subtotal);
            $discount = (float) $this->couponService->calculateDiscount($coupon, $subtotal);

            return $this->sendResponse([
                'code'            => $coupon->code,
                'discount_amount' => round($discount, 2),
                'discount_type'   => $coupon->discount_type,
                'discount_value'  => (float) $coupon->discount_value,
            ], "Coupon '{$coupon->code}' applied successfully!");
        } catch (Exception $e) {
            return $this->sendError($e->getMessage(), [], 422);
        }
    }

    /**
     * Place Order Endpoint (Handles both COD and Online Razorpay checkout).
     */
    public function placeOrder(Request $request): JsonResponse
    {
        $user = $request->user();

        // Normalize inputs from mobile Flutter app
        $request->merge([
            'payment_method'   => strtolower($request->input('payment_method', 'cod')),
            'shipping_pincode' => $request->input('shipping_pincode') ?: $request->input('shipping_zip'),
        ]);

        $validated = $request->validate([
            'shipping_name'    => 'required|string|max:255',
            'shipping_phone'   => 'required|string|max:15',
            'shipping_email'   => 'nullable|email|max:255',
            'shipping_address' => 'required|string|max:500',
            'shipping_city'    => 'required|string|max:100',
            'shipping_state'   => 'required|string|max:100',
            'shipping_pincode' => 'required|string|size:6',
            'payment_method'   => 'required|string|in:cod,online',
            'use_wallet'       => 'nullable|boolean',
            'coupon_code'      => 'nullable|string',
            'notes'            => 'nullable|string|max:500',
        ]);

        // Standardize shipping PIN code for checkout service
        $validated['shipping_zip'] = $validated['shipping_pincode'];
        $validated['shipping_email'] = !empty($validated['shipping_email'])
            ? $validated['shipping_email']
            : ($user->email ?? 'customer@shopcalm.in');

        try {
            if ($validated['payment_method'] === 'cod') {
                // Cash on Delivery Direct Confirmation
                $order = $this->checkoutService->placeOrder($validated);

                return $this->sendResponse([
                    'order'          => $order,
                    'order_id'       => (int) $order->id,
                    'order_number'   => $order->order_number,
                    'status'         => $order->status,
                    'total_amount'   => (float) $order->total_amount,
                    'payment_method' => 'cod',
                    'payment_status' => $order->payment_status,
                    'created_at'     => $order->created_at->format('Y-m-d H:i:s'),
                ], 'Order placed successfully via Cash on Delivery.', 201);

            } else {
                // Online Payment Flow: Create Pending Order + Razorpay Gateway Order
                $order = $this->checkoutService->createPendingOnlineOrder($validated, $user);

                $rzpOrder = $this->razorpayService->createRazorpayOrder(
                    $order->order_number,
                    (float) $order->total_amount,
                    [
                        'order_id' => (string) $order->id,
                        'customer' => $user->name ?? $validated['shipping_name'],
                    ]
                );

                return $this->sendResponse([
                    'order_id'          => (int) $order->id,
                    'order_number'      => $order->order_number,
                    'status'            => $order->status,
                    'total_amount'      => (float) $order->total_amount,
                    'payment_method'    => 'online',
                    'payment_status'    => 'pending',
                    'razorpay'          => [
                        'key_id'            => $this->razorpayService->getKeyId(),
                        'razorpay_order_id' => $rzpOrder['razorpay_order_id'] ?? null,
                        'amount'            => $rzpOrder['amount'] ?? ((int) round($order->total_amount * 100)),
                        'currency'          => 'INR',
                        'name'              => Setting::get('store_name', 'ShopCalm'),
                        'description'       => "Order #{$order->order_number}",
                        'prefill'           => [
                            'name'    => $user->name ?? $validated['shipping_name'],
                            'email'   => $user->email ?? $validated['shipping_email'],
                            'contact' => $validated['shipping_phone'],
                        ],
                    ],
                ], 'Razorpay payment initialized. Proceed to native checkout sheet.', 201);
            }
        } catch (Exception $e) {
            return $this->sendError($e->getMessage(), [], 422);
        }
    }

    /**
     * Verify Razorpay Payment Signature Endpoint (Called by Mobile App SDK on completion).
     */
    public function verifyPayment(Request $request): JsonResponse
    {
        $request->validate([
            'order_id'           => 'required|exists:orders,id',
            'razorpay_order_id'  => 'required|string',
            'razorpay_payment_id'=> 'required|string',
            'razorpay_signature' => 'required|string',
        ]);

        $user = $request->user();
        $order = Order::findOrFail($request->order_id);

        if ((int)$order->user_id !== (int)$user->id) {
            return $this->sendError('Unauthorized access to this order.', [], 403);
        }

        // Verify cryptographic signature
        $isValid = $this->razorpayService->verifyPaymentSignature(
            $request->razorpay_order_id,
            $request->razorpay_payment_id,
            $request->razorpay_signature
        );

        if (!$isValid) {
            return $this->sendError('Payment signature verification failed.', [], 400);
        }

        try {
            $paymentData = [
                'gateway'            => 'razorpay',
                'gateway_order_id'   => $request->razorpay_order_id,
                'gateway_payment_id' => $request->razorpay_payment_id,
                'bank_reference'     => $request->razorpay_payment_id,
                'payment_time'       => now(),
                'gateway_message'    => 'Payment Verified Successfully via Native App SDK',
            ];

            $confirmedOrder = $this->checkoutService->confirmOnlinePayment($order, $paymentData);

            return $this->sendResponse([
                'order_id'       => (int) $confirmedOrder->id,
                'order_number'   => $confirmedOrder->order_number,
                'status'         => $confirmedOrder->status,
                'payment_status' => $confirmedOrder->payment_status,
                'total_amount'   => (float) $confirmedOrder->total_amount,
                'confirmed_at'   => now()->format('Y-m-d H:i:s'),
            ], 'Payment verified successfully! Your order is confirmed.');
        } catch (Exception $e) {
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
            $pincodeRecord = Pincode::where('pincode', $order->shipping_zip)->first();
            $codFee = $pincodeRecord ? (float) $pincodeRecord->cod_fee : (float) Setting::get('cod_flat_fee', 40.00);

            $order->update([
                'payment_method' => 'cod',
                'payment_status' => 'pending',
                'cod_fee'        => $codFee,
                'total_amount'   => $order->subtotal_amount + $order->shipping_cost + $codFee - $order->coupon_discount_amount - $order->wallet_amount_used,
                'status'         => 'confirmed',
            ]);

            return $this->sendResponse([
                'order_id'       => (int) $order->id,
                'order_number'   => $order->order_number,
                'status'         => $order->status,
                'payment_method' => 'cod',
                'total_amount'   => (float) $order->total_amount,
            ], 'Order switched to Cash on Delivery & confirmed.');
        } catch (Exception $e) {
            return $this->sendError($e->getMessage(), [], 422);
        }
    }
}
