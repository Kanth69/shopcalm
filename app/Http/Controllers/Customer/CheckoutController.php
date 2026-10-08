<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Http\Requests\PlaceOrderRequest;
use App\Models\Order;
use App\Models\User;
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

    /**
     * Unified, Single-Source-of-Truth calculation for checkout totals.
     */
    private function calculateCheckoutTotals($cart, ?User $user, ?string $shippingZip = null): array
    {
        $subtotal = $this->cartService->subtotal();

        $totalMrp = $cart->items->sum(function ($item) {
            return $item->quantity * ($item->product->price ?? $item->unit_price);
        });
        $totalDiscount = max(0, $totalMrp - $subtotal);

        $couponCode = Session::get('applied_coupon');
        $discountAmount = 0;
        $couponError = null;

        if ($couponCode) {
            try {
                $coupon = $this->couponService->validateCoupon($couponCode, $user, $subtotal, $cart);
                $discountAmount = $this->couponService->calculateDiscount($coupon, $subtotal);
            } catch (\Exception $e) {
                Session::forget('applied_coupon');
                $couponError = $e->getMessage();
                $couponCode = null;
            }
        }

        $offerDiscount = app(\App\Services\OfferService::class)->calculateCheckoutOfferDiscount($cart);

        // Active Address Shipping Fee & COD Handling Fee
        $deliveryService = app(\App\Services\DeliveryService::class);
        $shippingFee = 0.00;
        $resolvedCodFee = 40.00;

        if ($shippingZip) {
            $pincodeCheck = $deliveryService->checkServiceability($shippingZip);
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
        $totalSavings = $totalDiscount + $discountAmount + $offerDiscount + $walletDiscount;

        return [
            'subtotal' => $subtotal,
            'totalMrp' => $totalMrp,
            'totalDiscount' => $totalDiscount,
            'couponCode' => $couponCode,
            'discountAmount' => $discountAmount,
            'couponError' => $couponError,
            'offerDiscount' => $offerDiscount,
            'shippingFee' => $shippingFee,
            'resolvedCodFee' => $resolvedCodFee,
            'userWallet' => $userWallet,
            'isWalletFrozen' => $isWalletFrozen,
            'useWallet' => $useWallet,
            'walletBalance' => $walletBalance,
            'walletDiscount' => $walletDiscount,
            'grandTotal' => $grandTotal,
            'totalSavings' => $totalSavings,
        ];
    }

    public function index()
    {
        $cart = $this->cartService->getSelectedCart();
        if ($cart->items->isEmpty()) {
            return redirect()->route('cart.index')->with('toast', ['type' => 'warning', 'title' => 'No items selected', 'message' => 'Please select at least one item to proceed to checkout.']);
        }

        $user = Auth::guard('customer')->user() ?? Auth::user();
        $addresses = $user ? $user->addresses()->latest()->get() : collect();
        $activeAddr = $addresses->count() > 0 ? ($addresses->firstWhere('is_default', true) ?? $addresses->first()) : null;
        $shippingZip = $activeAddr ? $activeAddr->zip : null;

        $totals = $this->calculateCheckoutTotals($cart, $user, $shippingZip);

        $availableCoupons = \App\Models\Coupon::where('is_active', true)
            ->where(function($q) {
                $q->whereNull('valid_until')->orWhere('valid_until', '>=', now()->startOfDay());
            })
            ->get();

        foreach ($availableCoupons as $c) {
            try {
                $this->couponService->validateCoupon($c->code, $user, $totals['subtotal'], $cart);
                $c->is_eligible = true;
                $c->ineligibility_reason = null;
                $c->calculated_discount = $this->couponService->calculateDiscount($c, $totals['subtotal']);
            } catch (\Exception $e) {
                $c->is_eligible = false;
                $c->ineligibility_reason = $e->getMessage();
                $c->calculated_discount = 0;
            }
        }

        $pendingOrder = $this->checkoutService->getActivePendingOrder($user);

        return view('customer.checkout.index', array_merge([
            'cart' => $cart,
            'addresses' => $addresses,
            'availableCoupons' => $availableCoupons,
            'pendingOrder' => $pendingOrder,
        ], $totals));
    }

    /**
     * AJAX Endpoint to toggle wallet usage on checkout.
     */
    public function toggleWallet(Request $request)
    {
        $user = Auth::guard('customer')->user() ?? Auth::user();
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

        $cart = $this->cartService->getSelectedCart();
        $addresses = $user ? $user->addresses()->latest()->get() : collect();
        $activeAddr = $addresses->count() > 0 ? ($addresses->firstWhere('is_default', true) ?? $addresses->first()) : null;
        $shippingZip = $activeAddr ? $activeAddr->zip : null;

        $totals = $this->calculateCheckoutTotals($cart, $user, $shippingZip);

        return response()->json([
            'success' => true,
            'use_wallet' => $enabled,
            'wallet_discount' => number_format($totals['walletDiscount'], 2),
            'wallet_discount_raw' => $totals['walletDiscount'],
            'wallet_balance' => number_format($totals['walletBalance'], 2),
            'grand_total' => number_format($totals['grandTotal'], 2),
            'grand_total_raw' => $totals['grandTotal'],
            'total_savings' => number_format($totals['totalSavings'], 2),
            'message' => $enabled ? "Applied ₹" . number_format($totals['walletDiscount'], 2) . " from your ShopCalm Wallet!" : "Wallet balance removed from order."
        ]);
    }

    public function applyCoupon(Request $request)
    {
        $request->validate(['coupon_code' => 'required|string']);

        $cart = $this->cartService->getSelectedCart();
        $user = Auth::guard('customer')->user() ?? Auth::user();
        $subtotal = $this->cartService->subtotal();

        try {
            $coupon = $this->couponService->validateCoupon($request->coupon_code, $user, $subtotal, $cart);
            Session::put('applied_coupon', $coupon->code);

            $addresses = $user ? $user->addresses()->latest()->get() : collect();
            $activeAddr = $addresses->count() > 0 ? ($addresses->firstWhere('is_default', true) ?? $addresses->first()) : null;
            $shippingZip = $activeAddr ? $activeAddr->zip : null;

            $totals = $this->calculateCheckoutTotals($cart, $user, $shippingZip);

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Coupon applied successfully!',
                    'coupon_code' => $coupon->code,
                    'discount_amount' => $totals['discountAmount'],
                    'formatted_discount' => number_format($totals['discountAmount'], 2),
                    'subtotal' => number_format($totals['subtotal'], 2),
                    'grand_total' => number_format($totals['grandTotal'], 2),
                    'grand_total_raw' => $totals['grandTotal'],
                    'wallet_discount' => number_format($totals['walletDiscount'], 2),
                    'wallet_discount_raw' => $totals['walletDiscount'],
                    'total_savings' => number_format($totals['totalSavings'], 2),
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
        $cart = $this->cartService->getSelectedCart();
        $user = Auth::guard('customer')->user() ?? Auth::user();

        $addresses = $user ? $user->addresses()->latest()->get() : collect();
        $activeAddr = $addresses->count() > 0 ? ($addresses->firstWhere('is_default', true) ?? $addresses->first()) : null;
        $shippingZip = $activeAddr ? $activeAddr->zip : null;

        $totals = $this->calculateCheckoutTotals($cart, $user, $shippingZip);

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Coupon removed.',
                'subtotal' => number_format($totals['subtotal'], 2),
                'grand_total' => number_format($totals['grandTotal'], 2),
                'grand_total_raw' => $totals['grandTotal'],
                'wallet_discount' => number_format($totals['walletDiscount'], 2),
                'wallet_discount_raw' => $totals['walletDiscount'],
                'total_savings' => number_format($totals['totalSavings'], 2)
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

            // Enforce rule: Customer with any pending order cannot place a new order until they cancel or proceed with it
            $pendingOrder = $this->checkoutService->getActivePendingOrder($user);
            if ($pendingOrder) {
                $msg = "You already have a pending order (#{$pendingOrder->order_number}). You cannot place a new order until you cancel or proceed with that order.";
                if ($request->ajax() || $request->wantsJson()) {
                    return response()->json([
                        'success'              => false,
                        'has_pending_order'    => true,
                        'pending_order_id'     => $pendingOrder->id,
                        'pending_order_number' => $pendingOrder->order_number,
                        'pending_order_total'  => number_format((float) $pendingOrder->total_amount, 2),
                        'proceed_url'          => route('checkout.payment_failed', $pendingOrder),
                        'order_url'            => route('account.orders.show', $pendingOrder),
                        'cancel_url'           => route('account.orders.cancel', $pendingOrder),
                        'message'              => $msg,
                    ], 422);
                }

                return redirect()->route('checkout.payment_failed', $pendingOrder)
                    ->with('toast', ['type' => 'warning', 'title' => 'Pending Order Exists', 'message' => $msg]);
            }

            // Check if wallet balance 100% covers the order (Zero remaining to pay)
            $cart = $this->cartService->getSelectedCart();
            if ($cart->items->isEmpty()) {
                throw new \Exception("No items selected in your cart for checkout.");
            }

            $subtotalAmount = $this->cartService->subtotal();
            $discountAmount = 0;
            $appliedCouponCode = Session::get('applied_coupon');
            if ($appliedCouponCode) {
                $coupon = $this->couponService->validateCoupon($appliedCouponCode, $user, $subtotalAmount);
                $discountAmount = $this->couponService->calculateDiscount($coupon, $subtotalAmount);
            }
            $offerDiscount = app(\App\Services\OfferService::class)->calculateCheckoutOfferDiscount($cart);

            $deliveryService = app(\App\Services\DeliveryService::class);
            $pincodeCheck = $deliveryService->checkServiceability($data['shipping_zip'] ?? '');
            $freeShippingMin = (float) \App\Models\Setting::get('free_shipping_min', 499);
            $shippingFee = ($subtotalAmount >= $freeShippingMin) ? 0.00 : (float) ($pincodeCheck['delivery_charge'] ?? 0.00);

            $payableBeforeWallet = max(0, $subtotalAmount - $discountAmount - $offerDiscount + $shippingFee);
            $useWallet = !empty($data['use_wallet']) || Session::get('use_wallet', false);
            $userWallet = $user ? app(\App\Services\WalletService::class)->getOrCreateWallet($user) : null;
            $walletBalance = ($userWallet && $userWallet->status === 'active') ? (float) $userWallet->balance : 0.00;

            $isFullyPaidByWallet = ($useWallet && $walletBalance >= $payableBeforeWallet);

            // IF 100% COVERED BY WALLET: Bypass payment gateway (no 0.00 gateway error!), debit wallet & confirm order immediately!
            if ($isFullyPaidByWallet) {
                $order = $this->checkoutService->placeOrder($data);
                Session::forget('applied_coupon');

                app(\App\Services\EmailService::class)->sendOrderConfirmation($order);

                if ($request->ajax() || $request->wantsJson()) {
                    return response()->json([
                        'success'      => true,
                        'payment_mode' => 'wallet',
                        'redirect_url' => route('checkout.success', $order),
                    ]);
                }

                return redirect()->route('checkout.success', $order);
            }

            // 1. ONLINE PREPAID FLOW (Razorpay UPI / Cards / NetBanking)
            if ($paymentMethod === 'online' || $paymentMethod === 'online_razorpay') {
                // Check serviceability
                if (!$pincodeCheck['is_serviceable']) {
                    throw new \Exception("Delivery is currently unavailable to PIN code " . ($data['shipping_zip'] ?? '') . ".");
                }

                // Check stock
                foreach ($cart->items as $item) {
                    $availStock = $item->product ? $item->product->getOptionStock($item->selected_option) : 0;
                    if (!$item->product || $availStock < $item->quantity) {
                        throw new \Exception("Product {$item->product->name} has insufficient stock (Only {$availStock} available).");
                    }
                }

                // EAGER ONLINE ORDER CREATION
                $order = $this->checkoutService->createPendingOnlineOrder($data, $user);

                // Call Razorpay Service to create Razorpay Order
                try {
                    $razorpay = app(\App\Services\RazorpayService::class);
                    $rzResult = $razorpay->createRazorpayOrder($order->order_number, (float) $order->total_amount, [
                        'shipping_name' => $order->shipping_name,
                        'email'         => $order->shipping_email,
                    ]);
                } catch (\Exception $e) {
                    $this->checkoutService->markOrderPaymentFailed($order, $e->getMessage());
                    throw $e;
                }

                // Update payment record with gateway order ID
                $payment = \App\Models\Payment::where('order_id', $order->id)->latest()->first();
                if ($payment) {
                    $payment->update([
                        'gateway'          => 'razorpay',
                        'gateway_order_id' => $rzResult['razorpay_order_id'] ?? null,
                    ]);
                }

                if ($request->ajax() || $request->wantsJson()) {
                    return response()->json([
                        'success'           => true,
                        'payment_mode'      => 'online',
                        'gateway'           => 'razorpay',
                        'razorpay_key_id'   => $razorpay->getKeyId(),
                        'razorpay_order_id' => $rzResult['razorpay_order_id'],
                        'amount_paise'      => $rzResult['amount'],
                        'currency'          => $rzResult['currency'],
                        'order_number'      => $order->order_number,
                        'order_id'          => $order->id,
                        'failed_url'        => route('checkout.payment_failed', $order),
                        'prefill'           => [
                            'name'    => $order->shipping_name,
                            'email'   => $order->shipping_email,
                            'contact' => $order->shipping_phone,
                        ],
                    ]);
                }

                return redirect()->route('checkout.index')->with('toast', ['type' => 'info', 'title' => 'Complete Payment', 'message' => 'Please complete your Razorpay payment.']);
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
        } catch (\InvalidArgumentException $e) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['success' => false, 'message' => $e->getMessage()], 400);
            }
            return back()->with('toast', ['type' => 'error', 'title' => 'Error', 'message' => $e->getMessage()]);
        } catch (\RuntimeException $e) {
            $status = in_array($e->getCode(), [400, 401, 500], true) ? $e->getCode() : 500;
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['success' => false, 'message' => $e->getMessage()], $status);
            }
            return back()->with('toast', ['type' => 'error', 'title' => 'Error', 'message' => $e->getMessage()]);
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
     * Generates a fresh gateway order reference via Razorpay.
     */
    public function retryPayment(Request $request, Order $order)
    {
        $user = Auth::guard('customer')->user() ?? Auth::user();
        if ($order->user_id !== $user->id) {
            abort(403);
        }

        if ($order->status === 'cancelled') {
            return response()->json([
                'success' => false,
                'message' => 'This order has been cancelled and cannot be retried.',
            ], 422);
        }

        if ($order->payment_status === 'paid') {
            return response()->json([
                'success' => true,
                'message' => 'Order is already paid.',
                'redirect_url' => route('checkout.success', $order)
            ]);
        }

        try {
            $razorpay = app(\App\Services\RazorpayService::class);
            $rzpOrder = $razorpay->createRazorpayOrder($order->order_number, (float) $order->total_amount, [
                'user_id'  => $user->id,
                'order_id' => $order->id,
            ]);

            $rzpOrderId = $rzpOrder['razorpay_order_id'] ?? $rzpOrder['id'] ?? null;

            \App\Models\Payment::create([
                'order_id'             => $order->id,
                'user_id'              => $user->id,
                'order_number'         => $order->order_number,
                'gateway'              => 'razorpay',
                'gateway_order_id'     => $rzpOrderId,
                'amount'               => (float) $order->total_amount,
                'currency'             => 'INR',
                'status'               => 'PENDING',
                'payment_method_group' => 'online',
                'gateway_message'      => 'Payment retry initiated via Razorpay.',
            ]);

            return response()->json([
                'success'           => true,
                'order_id'          => $order->id,
                'razorpay_order_id' => $rzpOrderId,
                'key_id'            => $razorpay->getKeyId(),
                'amount'            => $rzpOrder['amount'],
                'currency'          => 'INR',
                'name'              => config('app.name', 'ShopCalm'),
                'order_number'      => $order->order_number,
                'prefill'           => [
                    'name'    => $order->shipping_name,
                    'email'   => $order->shipping_email,
                    'contact' => $order->shipping_phone,
                ],
                'verify_url'        => route('checkout.razorpay.verify'),
                'callback_url'      => route('account.orders.show', $order),
            ]);
        } catch (\RuntimeException $e) {
            $status = in_array($e->getCode(), [400, 401, 500], true) ? $e->getCode() : 500;
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], $status);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
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

        if ($order->status === 'cancelled') {
            return back()->with('toast', ['type' => 'error', 'title' => 'Error', 'message' => 'This order has been cancelled and cannot be converted to COD.']);
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
     * Verify Razorpay Payment Signature and Mark Order Paid.
     */
    public function verifyRazorpayPayment(Request $request)
    {
        $orderId = $request->input('order_id');
        $razorpayOrderId = (string) $request->input('razorpay_order_id', '');
        $razorpayPaymentId = (string) $request->input('razorpay_payment_id', '');
        $razorpaySignature = (string) $request->input('razorpay_signature', '');

        if (empty($orderId) || $razorpayOrderId === '' || $razorpayPaymentId === '' || $razorpaySignature === '') {
            return response()->json([
                'success' => false,
                'message' => 'Missing required payment verification fields.',
            ], 400);
        }

        $order = Order::find($orderId);
        if (!$order) {
            return response()->json([
                'success' => false,
                'message' => 'Order not found.',
            ], 400);
        }

        $user = Auth::guard('customer')->user() ?? Auth::user() ?? $order->user;

        $razorpay = app(\App\Services\RazorpayService::class);
        $isValid = $razorpay->verifyPaymentSignature(
            $razorpayOrderId,
            $razorpayPaymentId,
            $razorpaySignature
        );

        if (!$isValid) {
            \Illuminate\Support\Facades\Log::warning("Razorpay Signature Verification Failed for Order #{$order->order_number}");
            return response()->json([
                'success' => false,
                'message' => 'Payment verification failed due to invalid signature.',
            ], 400);
        }

        $paymentDetails = [
            'gateway'                => 'razorpay',
            'gateway_order_id'       => $request->razorpay_order_id,
            'gateway_payment_id'     => $request->razorpay_payment_id,
            'payment_method_group'   => 'online',
            'bank_reference'         => $request->razorpay_payment_id,
            'payment_time'           => now(),
            'gateway_message'        => 'Razorpay Transaction Verified Successfully',
        ];

        $order = $this->checkoutService->markOrderPaid($order, $paymentDetails, $user);
        Session::forget('applied_coupon');

        app(\App\Services\EmailService::class)->sendOrderConfirmation($order);

        return response()->json([
            'success'      => true,
            'redirect_url' => route('checkout.success', $order),
            'message'      => 'Payment verified successfully!',
        ]);
    }

    /**
     * Razorpay Asynchronous Webhook Handler.
     */
    public function razorpayWebhook(Request $request)
    {
        try {
            $data = $request->json()->all();
            $event = $data['event'] ?? '';
            $paymentEntity = $data['payload']['payment']['entity'] ?? [];
            $razorpayOrderId = $paymentEntity['order_id'] ?? null;

            if ($razorpayOrderId && in_array($event, ['payment.captured', 'order.paid'])) {
                $payment = \App\Models\Payment::where('gateway_order_id', $razorpayOrderId)->first();
                if ($payment && $payment->order) {
                    $order = $payment->order;
                    $user = $order->user;

                    $paymentDetails = [
                        'gateway'            => 'razorpay',
                        'gateway_order_id'   => $razorpayOrderId,
                        'gateway_payment_id' => $paymentEntity['id'] ?? '',
                        'bank_reference'     => $paymentEntity['acquirer_data']['rrn'] ?? $paymentEntity['id'] ?? '',
                        'payment_time'       => now(),
                        'gateway_message'    => 'Payment captured via Razorpay Webhook',
                    ];

                    $this->checkoutService->markOrderPaid($order, $paymentDetails, $user);
                }
            }
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error("Razorpay Webhook error: " . $e->getMessage());
        }

        return response()->json(['status' => 'OK']);
    }

    public function success(Order $order)
    {
        $order->load(['items.product', 'fulfillment', 'coupon', 'user', 'payments']);
        return view('customer.checkout.success', compact('order'));
    }
}
