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
                'city'           => $result['city'] ?? null,
                'state'          => $result['state'] ?? null,
                'message'        => $result['message'] ?? 'Sorry, delivery is currently unavailable to this PIN code.',
            ], $result['message'] ?? 'Sorry, delivery is currently unavailable to this PIN code.');
        }

        $estimatedText = $result['estimated_delivery'] ?? (($result['delivery_days'] ?? 3) . ' Business Days');

        return $this->sendResponse([
            'is_serviceable'     => true,
            'pincode'            => $result['pincode'],
            'city'               => $result['city'],
            'state'              => $result['state'],
            'location_text'      => $result['location_text'],
            'is_cod_allowed'     => (bool) $result['is_cod_available'],
            'is_cod_available'   => (bool) $result['is_cod_available'],
            'cod_fee'            => (float) $result['cod_fee'],
            'delivery_charge'    => (float) $result['delivery_charge'],
            'free_shipping_min'  => (float) $result['free_shipping_min'],
            'delivery_days'      => (int) $result['delivery_days'],
            'estimated_delivery' => $estimatedText,
            'estimated_days'     => $estimatedText,
            'message'            => $result['message'],
        ], $result['message'] ?? 'PIN code is serviceable.');
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

        $deliveryCharge = 0.00;
        $codFee = 0.00;
        $isServiceable = false;
        $isCodAllowed = true;
        $estimatedDelivery = null;
        $deliveryDays = null;
        $pincodeCity = null;
        $pincodeState = null;
        $pincodeMessage = null;

        if ($pincode) {
            $deliveryService = app(\App\Services\DeliveryService::class);
            $pinCheck = $deliveryService->checkServiceability((string) $pincode);
            $isServiceable = (bool) ($pinCheck['is_serviceable'] ?? false);
            $pincodeCity = $pinCheck['city'] ?? null;
            $pincodeState = $pinCheck['state'] ?? null;
            $pincodeMessage = $pinCheck['message'] ?? null;

            if ($isServiceable) {
                $freeShippingMin = (float) ($pinCheck['free_shipping_min'] ?? $freeShippingMin);
                $rawDeliveryCharge = (float) ($pinCheck['delivery_charge'] ?? 0.00);
                $deliveryCharge = ($subtotal >= $freeShippingMin) ? 0.00 : $rawDeliveryCharge;
                $isCodAllowed = (bool) ($pinCheck['is_cod_available'] ?? true);
                $codFee = $isCodAllowed ? (float) ($pinCheck['cod_fee'] ?? 0.00) : 0.00;
                $estimatedDelivery = $pinCheck['estimated_delivery'] ?? null;
                $deliveryDays = isset($pinCheck['delivery_days']) ? (int) $pinCheck['delivery_days'] : null;
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

        // Fetch Active Available Coupons with Eligibility & Savings (matching Web CheckoutController)
        $availableCoupons = \App\Models\Coupon::where('is_active', true)
            ->where(function ($q) {
                $q->whereNull('valid_until')->orWhere('valid_until', '>=', now()->startOfDay());
            })
            ->get()
            ->map(function ($c) use ($user, $subtotal, $cart) {
                $discType = is_object($c->discount_type) ? $c->discount_type->value : (string) $c->discount_type;
                try {
                    $this->couponService->validateCoupon($c->code, $user, $subtotal, $cart);
                    $isEligible = true;
                    $reason = null;
                    $calcDiscount = (float) $this->couponService->calculateDiscount($c, $subtotal);
                } catch (Exception $e) {
                    $isEligible = false;
                    $reason = $e->getMessage();
                    $calcDiscount = 0.0;
                }

                return [
                    'id'                      => $c->id,
                    'code'                    => $c->code,
                    'name'                    => $c->name,
                    'description'             => $c->description,
                    'discount_type'           => $discType,
                    'discount_value'          => (float) $c->discount_value,
                    'minimum_order_amount'    => (float) $c->minimum_order_amount,
                    'maximum_discount_amount' => $c->maximum_discount_amount ? (float) $c->maximum_discount_amount : null,
                    'is_eligible'             => $isEligible,
                    'ineligibility_reason'    => $reason,
                    'calculated_discount'     => round($calcDiscount, 2),
                ];
            })
            ->values();

        return $this->sendResponse([
            'subtotal'                => round($subtotal, 2),
            'coupon_code'             => $couponCode,
            'coupon_discount'         => round($couponDiscount, 2),
            'coupon_message'          => $couponMessage,
            'is_serviceable'          => $isServiceable,
            'pincode'                 => $pincode,
            'city'                    => $pincodeCity,
            'state'                   => $pincodeState,
            'pincode_message'         => $pincodeMessage,
            'is_cod_allowed'          => $isCodAllowed,
            'estimated_delivery'      => $estimatedDelivery,
            'estimated_days'          => $estimatedDelivery,
            'delivery_days'           => $deliveryDays,
            'free_shipping_min'       => $freeShippingMin,
            'delivery_charge'         => round($deliveryCharge, 2),
            'cod_fee'                 => round($codFee, 2),
            'wallet_balance'          => round($walletBalance, 2),
            'wallet_amount_used'      => round($walletUsed, 2),
            'grand_total'             => round($grandTotal, 2),
            'grand_total_with_cod'    => round($grandTotal + $codFee, 2),
            'item_count'              => $cart->items->count(),
            'available_coupons'       => $availableCoupons,
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
        $validated['shipping_country'] = $request->input('shipping_country', 'India');
        $validated['shipping_email'] = !empty($validated['shipping_email'])
            ? $validated['shipping_email']
            : ($user->email ?? 'customer@shopcalm.in');

        try {
            // If user had a previous uncompleted/abandoned pending online order, release any locked wallet balance and cancel it
            $pendingOrder = $this->checkoutService->getActivePendingOrder($user);
            if ($pendingOrder && $pendingOrder->payment_status !== 'paid') {
                $this->checkoutService->markOrderPaymentFailed($pendingOrder, 'Superseded by new checkout order.');
                $pendingOrder->update(['status' => 'cancelled']);
            }

            if ($validated['payment_method'] === 'cod') {
                // Cash on Delivery Direct Confirmation
                $order = $this->checkoutService->placeOrder($validated);

                return $this->sendResponse([
                    'order'          => $order,
                    'order_id'       => (int) $order->id,
                    'order_number'   => $order->order_number,
                    'status'         => $order->status,
                    'total_amount'   => (float) $order->total_amount,
                    'payment_method' => $order->payment_method,
                    'payment_status' => $order->payment_status,
                    'created_at'     => $order->created_at->format('Y-m-d H:i:s'),
                ], 'Order placed successfully via Cash on Delivery.', 201);

            } else {
                // Check if ShopCalm Wallet covers 100% of the order amount
                $cart = $this->cartService->getSelectedCart();
                if ($cart->items->isEmpty()) {
                    return $this->sendError('Your cart has no items selected for checkout.', [], 422);
                }

                $subtotalAmount = (float) $this->cartService->subtotal();
                $discountAmount = 0.0;
                if (!empty($validated['coupon_code'])) {
                    $coupon = $this->couponService->validateCoupon($validated['coupon_code'], $user, $subtotalAmount);
                    $discountAmount = (float) $this->couponService->calculateDiscount($coupon, $subtotalAmount);
                }
                $offerDiscount = (float) app(\App\Services\OfferService::class)->calculateCheckoutOfferDiscount($cart);
                $pincodeCheck = app(\App\Services\DeliveryService::class)->checkServiceability($validated['shipping_zip']);
                if (!$pincodeCheck['is_serviceable']) {
                    return $this->sendError($pincodeCheck['message'] ?? "Delivery is currently unavailable to PIN code {$validated['shipping_zip']}.", [], 422);
                }
                $freeShippingMin = (float) Setting::get('free_shipping_min', 499);
                $shippingFee = ($subtotalAmount >= $freeShippingMin) ? 0.00 : (float) ($pincodeCheck['delivery_charge'] ?? 0.00);
                $payableBeforeWallet = max(0, $subtotalAmount - $discountAmount - $offerDiscount + $shippingFee);

                $useWallet = !empty($validated['use_wallet']);
                $userWallet = $this->walletService->getOrCreateWallet($user);
                $walletBalance = ($userWallet && $userWallet->status === 'active') ? (float) $userWallet->balance : 0.00;

                if ($useWallet && $walletBalance >= $payableBeforeWallet) {
                    $order = $this->checkoutService->placeOrder($validated);

                    return $this->sendResponse([
                        'order'          => $order,
                        'order_id'       => (int) $order->id,
                        'order_number'   => $order->order_number,
                        'status'         => $order->status,
                        'total_amount'   => 0.0,
                        'payment_method' => 'wallet',
                        'payment_status' => 'paid',
                        'created_at'     => $order->created_at->format('Y-m-d H:i:s'),
                    ], 'Order paid 100% via ShopCalm Wallet and confirmed!', 201);
                }

                // Online Payment Flow: Create Pending Order + Razorpay Gateway Order
                $order = $this->checkoutService->createPendingOnlineOrder($validated, $user);

                try {
                    $rzpOrder = $this->razorpayService->createRazorpayOrder(
                        $order->order_number,
                        (float) $order->total_amount,
                        [
                            'order_id' => (string) $order->id,
                            'customer' => $user->name ?? $validated['shipping_name'],
                        ]
                    );
                } catch (Exception $e) {
                    $this->checkoutService->markOrderPaymentFailed($order, $e->getMessage());
                    $order->update(['status' => 'cancelled']);
                    throw $e;
                }

                $payment = \App\Models\Payment::where('order_id', $order->id)->latest()->first();
                if ($payment) {
                    $payment->update([
                        'gateway'          => 'razorpay',
                        'gateway_order_id' => $rzpOrder['razorpay_order_id'] ?? null,
                    ]);
                }

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
            'order_id'            => 'required|exists:orders,id',
            'razorpay_order_id'   => 'required|string',
            'razorpay_payment_id' => 'required|string',
            'razorpay_signature'  => 'required|string',
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
                'gateway'              => 'razorpay',
                'gateway_order_id'     => $request->razorpay_order_id,
                'gateway_payment_id'   => $request->razorpay_payment_id,
                'payment_method_group' => 'online',
                'bank_reference'       => $request->razorpay_payment_id,
                'payment_time'         => now(),
                'gateway_message'      => 'Payment Verified Successfully via ShopCalm App',
            ];

            $confirmedOrder = $this->checkoutService->markOrderPaid($order, $paymentData, $user);

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
            $confirmedOrder = $this->checkoutService->switchToCod($order, $user);

            return $this->sendResponse([
                'order_id'       => (int) $confirmedOrder->id,
                'order_number'   => $confirmedOrder->order_number,
                'status'         => $confirmedOrder->status,
                'payment_method' => 'cod',
                'total_amount'   => (float) $confirmedOrder->total_amount,
            ], 'Order switched to Cash on Delivery & confirmed.');
        } catch (Exception $e) {
            return $this->sendError($e->getMessage(), [], 422);
        }
    }
}
