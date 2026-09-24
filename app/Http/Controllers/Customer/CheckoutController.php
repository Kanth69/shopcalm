<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Http\Requests\PlaceOrderRequest;
use App\Models\Order;
use App\Services\CartService;
use App\Services\CheckoutService;
use App\Services\CouponService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class CheckoutController extends Controller
{
    protected $cartService;
    protected $checkoutService;
    protected $couponService;

    public function __construct(CartService $cartService, CheckoutService $checkoutService, CouponService $couponService)
    {
        $this->cartService = $cartService;
        $this->checkoutService = $checkoutService;
        $this->couponService = $couponService;
    }

    public function index()
    {
        $cart = $this->cartService->getCart();
        if ($cart->items->isEmpty()) {
            return redirect()->route('home')->with('toast', ['type' => 'error', 'title' => 'Error', 'message' => 'Your cart is empty.']);
        }

        $cart->load(['items.product.category', 'items.product.brand']);
        $subtotal = $this->cartService->subtotal();
        
        $totalMrp = $cart->items->sum(function ($item) {
            return $item->quantity * $item->product->price;
        });
        $totalDiscount = $totalMrp - $subtotal;

        $discountAmount = 0;
        $couponCode = Session::get('applied_coupon');
        $couponError = null;

        if ($couponCode) {
            try {
                $coupon = $this->couponService->validateCoupon($couponCode, Auth::user(), $subtotal, $cart);
                $discountAmount = $this->couponService->calculateDiscount($coupon, $subtotal);
            } catch (\Exception $e) {
                Session::forget('applied_coupon');
                $couponError = $e->getMessage();
                $couponCode = null;
            }
        }

        $offerDiscount = app(\App\Services\OfferService::class)->calculateCheckoutOfferDiscount($cart);

        // Wallet Balance & Active Address Shipping Fee
        $user = Auth::guard('customer')->user() ?? Auth::user();
        $addresses = $user ? $user->addresses()->latest()->get() : collect();
        $activeAddr = $addresses->count() > 0 ? ($addresses->firstWhere('is_default', true) ?? $addresses->first()) : null;

        $deliveryService = app(\App\Services\DeliveryService::class);
        $shippingFee = 0.00;
        $resolvedCodFee = 40.00;
        if ($activeAddr && $activeAddr->zip) {
            $pincodeCheck = $deliveryService->checkServiceability($activeAddr->zip);
            if ($pincodeCheck['is_serviceable']) {
                $freeMin = (float) ($pincodeCheck['free_shipping_min'] ?? 499);
                $shippingFee = ($subtotal >= $freeMin) ? 0.00 : (float) ($pincodeCheck['delivery_charge'] ?? 0.00);
                $resolvedCodFee = (float) ($pincodeCheck['cod_fee'] ?? 40.00);
            }
        }

        $payableBeforeWallet = max(0, $subtotal - $discountAmount - $offerDiscount + $shippingFee);

        $userWallet = $user ? app(\App\Services\WalletService::class)->getOrCreateWallet($user) : null;
        $isWalletFrozen = ($userWallet && $userWallet->status === 'frozen');
        $useWallet = Session::get('use_wallet', false);

        if ($isWalletFrozen) {
            Session::forget('use_wallet');
            $useWallet = false;
        }

        $walletBalance = ($userWallet && !$isWalletFrozen) ? (float) $userWallet->balance : 0.00;
        $walletDiscount = 0.00;

        if ($useWallet && $walletBalance > 0) {
            $walletDiscount = min($walletBalance, $payableBeforeWallet);
        }

        $grandTotal = max(0, $payableBeforeWallet - $walletDiscount);

        $availableCoupons = \App\Models\Coupon::where('is_active', true)
            ->where(function($q) {
                $q->whereNull('valid_until')->orWhere('valid_until', '>=', now()->startOfDay());
            })
            ->get();

        foreach ($availableCoupons as $c) {
            try {
                $this->couponService->validateCoupon($c->code, Auth::user(), $subtotal, $cart);
                $c->is_eligible = true;
                $c->ineligibility_reason = null;
                $c->calculated_discount = $this->couponService->calculateDiscount($c, $subtotal);
            } catch (\Exception $e) {
                $c->is_eligible = false;
                $c->ineligibility_reason = $e->getMessage();
                $c->calculated_discount = 0;
            }
        }

        return view('customer.checkout.index', compact(
            'cart',
            'subtotal',
            'totalMrp',
            'totalDiscount',
            'discountAmount',
            'offerDiscount',
            'shippingFee',
            'resolvedCodFee',
            'userWallet',
            'isWalletFrozen',
            'useWallet',
            'walletDiscount',
            'grandTotal',
            'couponCode',
            'couponError',
            'addresses',
            'availableCoupons'
        ));
    }

    /**
     * AJAX Endpoint to toggle wallet usage on checkout.
     */
    public function toggleWallet(Request $request)
    {
        $user = Auth::user();
        $userWallet = $user ? app(\App\Services\WalletService::class)->getOrCreateWallet($user) : null;

        if ($userWallet && $userWallet->status === 'frozen') {
            Session::forget('use_wallet');
            return response()->json([
                'success' => false,
                'is_frozen' => true,
                'message' => 'Your wallet is temporarily locked by admin for security. Balance is unavailable.'
            ], 422);
        }

        $enabled = (bool) $request->input('use_wallet', false);
        Session::put('use_wallet', $enabled);

        $cart = $this->cartService->getCart();
        $subtotal = $this->cartService->subtotal();

        $discountAmount = 0;
        $couponCode = Session::get('applied_coupon');
        if ($couponCode) {
            try {
                $coupon = $this->couponService->validateCoupon($couponCode, $user, $subtotal, $cart);
                $discountAmount = $this->couponService->calculateDiscount($coupon, $subtotal);
            } catch (\Exception $e) {
                Session::forget('applied_coupon');
            }
        }

        $offerDiscount = app(\App\Services\OfferService::class)->calculateCheckoutOfferDiscount($cart);
        $payableBeforeWallet = max(0, $subtotal - $discountAmount - $offerDiscount);

        $walletBalance = ($userWallet && $userWallet->status === 'active') ? (float) $userWallet->balance : 0.00;
        $walletDiscount = 0.00;

        if ($enabled && $walletBalance > 0) {
            $walletDiscount = min($walletBalance, $payableBeforeWallet);
        }

        $grandTotal = max(0, $payableBeforeWallet - $walletDiscount);
        $totalMrp = $cart->items->sum(fn($i) => $i->quantity * $i->product->price);
        $totalSavings = ($totalMrp - $subtotal) + $discountAmount + $offerDiscount + $walletDiscount;

        return response()->json([
            'success' => true,
            'use_wallet' => $enabled,
            'wallet_discount' => number_format($walletDiscount, 2),
            'wallet_discount_raw' => $walletDiscount,
            'wallet_balance' => number_format($walletBalance, 2),
            'grand_total' => number_format($grandTotal, 2),
            'grand_total_raw' => $grandTotal,
            'total_savings' => number_format($totalSavings, 2),
            'message' => $enabled ? "Applied ₹" . number_format($walletDiscount, 2) . " from your WiseKart Wallet!" : "Wallet balance removed from order."
        ]);
    }

    public function applyCoupon(Request $request)
    {
        $request->validate(['coupon_code' => 'required|string']);

        $cart = $this->cartService->getCart();
        $subtotal = $this->cartService->subtotal();

        try {
            $coupon = $this->couponService->validateCoupon($request->coupon_code, Auth::user(), $subtotal, $cart);
            $discountAmount = $this->couponService->calculateDiscount($coupon, $subtotal);
            Session::put('applied_coupon', $coupon->code);

            $offerDiscount = app(\App\Services\OfferService::class)->calculateCheckoutOfferDiscount($cart);
            $grandTotal = max(0, $subtotal - $discountAmount - $offerDiscount);
            
            $totalMrp = $cart->items->sum(function ($item) {
                return $item->quantity * $item->product->price;
            });
            $totalDiscount = $totalMrp - $subtotal;
            $totalSavings = $totalDiscount + $discountAmount + $offerDiscount;

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Coupon applied successfully!',
                    'coupon_code' => $coupon->code,
                    'discount_amount' => $discountAmount,
                    'formatted_discount' => number_format($discountAmount, 2),
                    'subtotal' => number_format($subtotal, 2),
                    'grand_total' => number_format($grandTotal, 2),
                    'total_savings' => number_format($totalSavings, 2),
                ]);
            }

            return back()->with('toast', ['type' => 'success', 'title' => 'Success', 'message' => 'Coupon applied successfully!']);
        } catch (\Exception $e) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage()
                ], 422);
            }

            return back()->with('toast', ['type' => 'error', 'title' => 'Error', 'message' => $e->getMessage()]);
        }
    }

    public function removeCoupon(Request $request)
    {
        Session::forget('applied_coupon');
        $cart = $this->cartService->getCart();
        $subtotal = $this->cartService->subtotal();
        
        $offerDiscount = app(\App\Services\OfferService::class)->calculateCheckoutOfferDiscount($cart);
        $grandTotal = max(0, $subtotal - $offerDiscount);
        
        $totalMrp = $cart->items->sum(function ($item) {
            return $item->quantity * $item->product->price;
        });
        $totalDiscount = $totalMrp - $subtotal;
        $totalSavings = $totalDiscount + $offerDiscount;

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Coupon removed.',
                'subtotal' => number_format($subtotal, 2),
                'grand_total' => number_format($grandTotal, 2),
                'total_savings' => number_format($totalSavings, 2)
            ]);
        }

        return back()->with('toast', ['type' => 'success', 'title' => 'Removed', 'message' => 'Coupon removed.']);
    }

    public function saveAddress(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:20',
            'address' => 'required|string|max:500',
            'city' => 'required|string|max:100',
            'state' => 'required|string|max:100',
            'zip' => 'required|string|max:20',
            'country' => 'nullable|string|max:100',
        ]);

        $user = Auth::guard('customer')->user() ?? Auth::user();
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Please log in to save your address.'
            ], 401);
        }

        $newAddress = $user->addresses()->create([
            'name' => $validated['name'],
            'phone' => $validated['phone'],
            'address' => $validated['address'],
            'city' => $validated['city'],
            'state' => $validated['state'],
            'zip' => $validated['zip'],
            'country' => $validated['country'] ?? 'India',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Address saved successfully!',
            'address' => $newAddress,
            'all_addresses' => $user->addresses()->latest()->get()
        ]);
    }

    public function placeOrder(PlaceOrderRequest $request)
    {
        try {
            $data = $request->validated();
            $paymentMethod = $request->input('payment_method', 'cod');
            $user = Auth::guard('customer')->user() ?? Auth::user();

            if (!$user) {
                return response()->json(['success' => false, 'message' => 'Please log in to place an order.'], 401);
            }

            // 1. ONLINE PREPAID FLOW (Cashfree UPI / Cards / NetBanking)
            // Order is NOT placed, stock is NOT deducted until payment is verified!
            if ($paymentMethod === 'online' || $paymentMethod === 'online_cashfree') {
                $cart = $this->cartService->getCart();
                if ($cart->items->isEmpty()) {
                    throw new \Exception("Your shopping cart is empty.");
                }

                // Check serviceability
                $deliveryService = app(\App\Services\DeliveryService::class);
                $pincodeCheck = $deliveryService->checkServiceability($data['shipping_zip'] ?? '');
                if (!$pincodeCheck['is_serviceable']) {
                    throw new \Exception("Delivery is currently unavailable to PIN code " . ($data['shipping_zip'] ?? '') . ".");
                }

                // Check stock
                foreach ($cart->items as $item) {
                    if (!$item->product || $item->product->stock < $item->quantity) {
                        throw new \Exception("Product {$item->product->name} is out of stock or insufficient quantity.");
                    }
                }

                $subtotalAmount = $this->cartService->subtotal();
                $discountAmount = 0;
                $couponId = null;

                $appliedCouponCode = Session::get('applied_coupon');
                if ($appliedCouponCode) {
                    $coupon = $this->couponService->validateCoupon($appliedCouponCode, $user, $subtotalAmount);
                    $discountAmount = $this->couponService->calculateDiscount($coupon, $subtotalAmount);
                    $couponId = $coupon->id;
                }

                $offerDiscount = app(\App\Services\OfferService::class)->calculateCheckoutOfferDiscount($cart);
                $grandTotal = max(0, $subtotalAmount - $discountAmount - $offerDiscount);

                // 1. EAGER ONLINE ORDER CREATION (Status Lifecycle Architecture)
                $order = $this->checkoutService->createPendingOnlineOrder($data, $user);

                // Call Cashfree PG to create payment session for this exact order
                $cashfree = app(\App\Services\CashfreeService::class);
                $cfResult = $cashfree->createPaymentSession(
                    $order->order_number,
                    (float) $order->total_amount,
                    [
                        'customer_id' => 'cust_' . $user->id,
                        'name'        => $order->shipping_name,
                        'email'       => $order->shipping_email,
                        'phone'       => $order->shipping_phone,
                    ],
                    route('checkout.cashfree.return')
                );

                // Update payment record with gateway session ID
                $payment = \App\Models\Payment::where('order_id', $order->id)->latest()->first();
                if ($payment) {
                    $payment->update([
                        'gateway_order_id'   => $cfResult['cf_order_id'] ?? null,
                        'payment_session_id' => $cfResult['payment_session_id'] ?? null,
                    ]);
                }

                if ($request->ajax() || $request->wantsJson()) {
                    return response()->json([
                        'success'            => true,
                        'payment_mode'       => 'online',
                        'payment_session_id' => $cfResult['payment_session_id'],
                        'order_number'       => $order->order_number,
                        'order_id'           => $order->id,
                        'environment'        => strtolower($cfResult['environment'] ?? 'sandbox'),
                    ]);
                }

                return redirect()->route('checkout.cashfree.return', ['order_id' => $order->order_number]);
            }

            // 2. CASH ON DELIVERY (COD) FLOW
            // Directly places the order, creates fulfillment, and clears cart
            $order = $this->checkoutService->placeOrder($data);
            Session::forget('applied_coupon');

            // Send HTML Email Confirmation (Only if customer provided an email address)
            app(\App\Services\EmailService::class)->sendOrderConfirmation($order);

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success'      => true,
                    'payment_mode' => 'cod',
                    'redirect_url' => route('checkout.success', $order),
                ]);
            }

            return redirect()->route('checkout.success', $order);
        } catch (\Exception $e) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage(),
                ], 422);
            }
            return back()->with('toast', ['type' => 'error', 'title' => 'Error', 'message' => $e->getMessage()]);
        }
    }

    /**
     * Cashfree Return URL Callback Handler.
     */
    public function cashfreeReturn(Request $request)
    {
        $rawOrderParam = $request->query('order_id');
        if (!$rawOrderParam) {
            return redirect()->route('checkout.index')->with('toast', [
                'type'    => 'error',
                'title'   => 'Payment Incomplete',
                'message' => 'No order reference received from payment gateway.',
            ]);
        }

        // Extract base order number if it has a retry suffix (e.g. "WK20260824F3A81D-R1" -> "WK20260824F3A81D")
        $baseOrderNumber = preg_replace('/-R\d+$/', '', $rawOrderParam);
        $order = Order::where('order_number', $baseOrderNumber)->orWhere('order_number', $rawOrderParam)->first();

        try {
            $cashfree = app(\App\Services\CashfreeService::class);
            $cfOrder = $cashfree->getOrder($rawOrderParam);
            $cfStatus = strtoupper($cfOrder['order_status'] ?? '');

            // Fetch detailed payment transactions from Cashfree (Bank UTR, failure reason, UPI ID)
            $payments = $cashfree->getOrderPayments($rawOrderParam);
            $successfulPayment = null;
            $failureReason = 'Customer cancelled or payment was declined by bank.';

            if (!empty($payments)) {
                foreach ($payments as $p) {
                    if (strtoupper($p['payment_status'] ?? '') === 'SUCCESS') {
                        $successfulPayment = $p;
                        break;
                    }
                }
                $lastAttempt = end($payments);
                if (!empty($lastAttempt['payment_message'])) {
                    $failureReason = $lastAttempt['payment_message'];
                } elseif (!empty($lastAttempt['payment_status'])) {
                    $failureReason = "Payment " . strtolower($lastAttempt['payment_status']);
                }
            }

            if ($cfStatus === 'PAID' && $successfulPayment) {
                $paymentData = [
                    'gateway'                => 'cashfree',
                    'gateway_order_id'       => $cfOrder['cf_order_id'] ?? $rawOrderParam,
                    'gateway_payment_id'     => (string) ($successfulPayment['cf_payment_id'] ?? $cfOrder['cf_order_id'] ?? ''),
                    'payment_session_id'     => $cfOrder['payment_session_id'] ?? null,
                    'payment_method_group'   => $successfulPayment['payment_group'] ?? 'upi',
                    'payment_method_details' => $successfulPayment['payment_method'] ?? [],
                    'bank_reference'         => (string) ($successfulPayment['bank_reference'] ?? $successfulPayment['cf_payment_id'] ?? ''),
                    'payment_time'           => isset($successfulPayment['payment_time']) ? date('Y-m-d H:i:s', strtotime($successfulPayment['payment_time'])) : now(),
                    'gateway_message'        => $successfulPayment['payment_message'] ?? 'Transaction Successful',
                    'raw_response'           => $successfulPayment ?? $cfOrder,
                ];

                $user = Auth::guard('customer')->user() ?? Auth::user() ?? ($order ? $order->user : null);

                if ($order && $user) {
                    $order = $this->checkoutService->markOrderPaid($order, $paymentData, $user);
                }

                Session::forget('applied_coupon');
                $utrNote = !empty($paymentData['bank_reference']) ? " (Bank Ref: {$paymentData['bank_reference']})" : "";

                return redirect()->route('checkout.success', $order)->with('toast', [
                    'type'    => 'success',
                    'title'   => 'Payment Successful! 🎉',
                    'message' => "Online payment of ₹" . number_format($order->total_amount, 2) . " verified{$utrNote}.",
                ]);
            }

            // Payment was not completed (dropped / cancelled / failed)
            if ($order) {
                $this->checkoutService->markOrderPaymentFailed($order, $failureReason);
                return redirect()->route('checkout.payment_failed', $order)->with('toast', [
                    'type'    => 'warning',
                    'title'   => 'Payment Incomplete',
                    'message' => $failureReason,
                ]);
            }
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error("Cashfree return verification error: " . $e->getMessage());
            if ($order) {
                $this->checkoutService->markOrderPaymentFailed($order, $e->getMessage());
                return redirect()->route('checkout.payment_failed', $order);
            }
        }

        return redirect()->route('checkout.index')->with('toast', [
            'type'    => 'warning',
            'title'   => 'Payment Not Completed',
            'message' => 'Your payment could not be verified. Please retry or choose Cash on Delivery.',
        ]);
    }

    /**
     * Render the dedicated Payment Failed & Recovery View.
     */
    public function paymentFailed(Order $order)
    {
        $user = Auth::guard('customer')->user() ?? Auth::user();
        if ($order->user_id !== $user->id) {
            abort(403);
        }

        if ($order->payment_status === 'paid') {
            return redirect()->route('checkout.success', $order);
        }

        $deliveryService = app(\App\Services\DeliveryService::class);
        $pincodeCheck = $deliveryService->checkServiceability($order->shipping_zip ?? '');
        $resolvedCodFee = ($pincodeCheck['is_serviceable'] && $pincodeCheck['is_cod_available'])
            ? (float) ($pincodeCheck['cod_fee'] ?? 40.00)
            : 0.00;
        $isCodAvailable = ($pincodeCheck['is_serviceable'] && $pincodeCheck['is_cod_available']);

        $order->load(['items.product', 'payments']);
        return view('customer.checkout.payment_failed', compact('order', 'resolvedCodFee', 'isCodAvailable'));
    }

    /**
     * Retry an online payment for an existing pending/failed order.
     * Generates a fresh gateway order reference to avoid Cashfree duplicate ID restrictions.
     */
    public function retryPayment(Request $request, Order $order)
    {
        $user = Auth::guard('customer')->user() ?? Auth::user();
        if ($order->user_id !== $user->id) {
            abort(403);
        }

        if ($order->payment_status === 'paid') {
            return redirect()->route('checkout.success', $order);
        }

        try {
            // Generate a unique retry reference (e.g. WK20260824F3A81D-R1, WK20260824F3A81D-R2)
            $attemptCount = \App\Models\Payment::where('order_id', $order->id)->count();
            $gatewayOrderRef = $order->order_number . '-R' . ($attemptCount);

            $cashfree = app(\App\Services\CashfreeService::class);
            $cfResult = $cashfree->createPaymentSession(
                $gatewayOrderRef,
                (float) $order->total_amount,
                [
                    'customer_id' => 'cust_' . $user->id,
                    'name'        => $order->shipping_name,
                    'email'       => $order->shipping_email,
                    'phone'       => $order->shipping_phone,
                ],
                route('checkout.cashfree.return')
            );

            // Record fresh payment attempt in ledger
            \App\Models\Payment::create([
                'order_id'           => $order->id,
                'user_id'            => $user->id,
                'order_number'       => $order->order_number,
                'gateway'            => 'cashfree',
                'gateway_order_id'   => $cfResult['cf_order_id'] ?? $gatewayOrderRef,
                'payment_session_id' => $cfResult['payment_session_id'] ?? null,
                'amount'             => (float) $order->total_amount,
                'currency'           => 'INR',
                'status'             => 'PENDING',
                'payment_method_group' => 'online',
                'gateway_message'    => 'Payment retry initiated.',
            ]);

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success'            => true,
                    'payment_session_id' => $cfResult['payment_session_id'],
                    'order_number'       => $order->order_number,
                    'environment'        => strtolower($cfResult['environment'] ?? 'sandbox'),
                ]);
            }

            return redirect()->route('checkout.payment_failed', $order);
        } catch (\Exception $e) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage(),
                ], 422);
            }
            return back()->with('toast', ['type' => 'error', 'title' => 'Payment Error', 'message' => $e->getMessage()]);
        }
    }

    /**
     * Switch an unpaid online order to Cash on Delivery (COD).
     */
    public function switchToCod(Order $order)
    {
        $user = Auth::guard('customer')->user() ?? Auth::user();
        if ($order->user_id !== $user->id) {
            abort(403);
        }

        try {
            $order = $this->checkoutService->switchToCod($order, $user);
            return redirect()->route('checkout.success', $order)->with('toast', [
                'type'    => 'success',
                'title'   => 'Switched to Cash on Delivery! 📦',
                'message' => 'Your order is confirmed. You can pay with cash or UPI QR upon doorstep delivery.',
            ]);
        } catch (\Exception $e) {
            return back()->with('toast', ['type' => 'error', 'title' => 'Error', 'message' => $e->getMessage()]);
        }
    }

    /**
     * Cashfree Asynchronous Webhook Handler.
     */
    public function cashfreeWebhook(Request $request)
    {
        try {
            $rawPayload = $request->getContent();
            $signature = $request->header('x-webhook-signature', '');
            $timestamp = $request->header('x-webhook-timestamp', '');

            $data = $request->json()->all();
            $event = $data['type'] ?? '';
            $orderData = $data['data']['order'] ?? [];
            $orderNumber = $orderData['order_id'] ?? null;
            $paymentDataRaw = $data['data']['payment'] ?? [];

            if ($orderNumber && in_array($event, ['PAYMENT_SUCCESS_WEBHOOK', 'ORDER_PAID'])) {
                $paymentDetails = [
                    'gateway'                => 'cashfree',
                    'gateway_order_id'       => $orderData['cf_order_id'] ?? null,
                    'gateway_payment_id'     => (string) ($paymentDataRaw['cf_payment_id'] ?? ''),
                    'payment_method_group'   => $paymentDataRaw['payment_group'] ?? 'upi',
                    'payment_method_details' => $paymentDataRaw['payment_method'] ?? [],
                    'bank_reference'         => (string) ($paymentDataRaw['bank_reference'] ?? ''),
                    'payment_time'           => isset($paymentDataRaw['payment_time']) ? date('Y-m-d H:i:s', strtotime($paymentDataRaw['payment_time'])) : now(),
                    'gateway_message'        => $paymentDataRaw['payment_message'] ?? 'Transaction Successful (Webhook)',
                    'raw_response'           => $data,
                ];

                $baseOrderNumber = preg_replace('/-R\d+$/', '', $orderNumber);
                $existingOrder = Order::where('order_number', $baseOrderNumber)->orWhere('order_number', $orderNumber)->first();
                if ($existingOrder) {
                    $orderUser = $existingOrder->user ?? \App\Models\User::find($existingOrder->user_id);
                    if ($orderUser) {
                        $this->checkoutService->markOrderPaid($existingOrder, $paymentDetails, $orderUser);
                    }
                }
            }
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error("Cashfree Webhook error: " . $e->getMessage());
        }

        return response()->json(['status' => 'OK']);
    }

    public function success(Order $order)
    {
        $order->load(['items.product', 'fulfillment', 'coupon', 'user', 'payments']);
        return view('customer.checkout.success', compact('order'));
    }
}
