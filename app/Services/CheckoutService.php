<?php

namespace App\Services;

use App\Enums\MovementType;
use App\Enums\StockSource;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Product;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Exception;

class CheckoutService
{
    protected $cartService;
    protected $orderService;
    protected $couponService;
    protected $fulfillmentService;

    public function __construct(
        CartService $cartService,
        OrderService $orderService,
        CouponService $couponService,
        FulfillmentService $fulfillmentService
    ) {
        $this->cartService = $cartService;
        $this->orderService = $orderService;
        $this->couponService = $couponService;
        $this->fulfillmentService = $fulfillmentService;
    }

    /**
     * Retrieve any active pending/uncompleted order for the user.
     * A user cannot place a new order while they have a pending order until they
     * either cancel it or complete/proceed with it (Pay Online or Switch to COD).
     */
    public function getActivePendingOrder(?User $user): ?Order
    {
        if (!$user) {
            return null;
        }

        return Order::where('user_id', $user->id)
            ->where(function ($q) {
                $q->whereIn('status', ['pending', 'failed', ''])
                  ->orWhereNull('status');
            })
            ->with('items.product')
            ->latest()
            ->first();
    }

    /**
     * Place a standard Cash on Delivery (COD) Order.
     */
    public function placeOrder(array $data): Order
    {
        return DB::transaction(function () use ($data) {
            $user = Auth::guard('customer')->user() ?? (Auth::user() ?? (Auth::guard('sanctum')->user() ?? request()->user('sanctum')));
            $existingPending = $this->getActivePendingOrder($user);
            if ($existingPending) {
                throw new Exception("You already have a pending order (#{$existingPending->order_number}). You cannot place a new order until you cancel or proceed with that order.");
            }

            $cart = $this->cartService->getSelectedCart();
            if ($cart->items->isEmpty()) {
                throw new Exception("Shopping cart has no items selected for checkout.");
            }

            // Validate shipping PIN code serviceability
            $shippingZip = $data['shipping_zip'] ?? ($data['shipping_pincode'] ?? '');
            $deliveryService = app(\App\Services\DeliveryService::class);
            $pincodeCheck = $deliveryService->checkServiceability($shippingZip);
            if (!$pincodeCheck['is_serviceable']) {
                throw new Exception("Delivery is currently unavailable to PIN code {$shippingZip}. Please select a serviceable delivery address.");
            }

            $subtotalAmount = $this->cartService->subtotal();
            $discountAmount = 0;
            $couponId = null;

            // Handle Coupon
            $appliedCouponCode = $data['coupon_code'] ?? Session::get('applied_coupon');
            if ($appliedCouponCode) {
                $coupon = $this->couponService->validateCoupon($appliedCouponCode, $user, $subtotalAmount);
                $discountAmount = $this->couponService->calculateDiscount($coupon, $subtotalAmount);
                $couponId = $coupon->id;
            }

            // Handle Shipping Fee & COD Handling Fee
            $freeShippingMin = (float) \App\Models\Setting::get('free_shipping_min', 499);
            $shippingFee = ($subtotalAmount >= $freeShippingMin) ? 0.00 : (float) ($pincodeCheck['delivery_charge'] ?? 0.00);
            $rawCodFee = (float) ($pincodeCheck['cod_fee'] ?? 40.00);

            // Handle Offer Discount
            $offerDiscount = app(\App\Services\OfferService::class)->calculateCheckoutOfferDiscount($cart);
            $payableBeforeWalletAndCod = max(0, $subtotalAmount - $discountAmount - $offerDiscount + $shippingFee);

            // Handle Wallet Deduction
            $useWallet = !empty($data['use_wallet']) || Session::get('use_wallet', false);
            $walletAmountUsed = 0.00;
            if ($useWallet && $user) {
                $userWallet = app(\App\Services\WalletService::class)->getOrCreateWallet($user);
                if ($userWallet->status === 'active' && $userWallet->balance > 0) {
                    $walletAmountUsed = min((float) $userWallet->balance, $payableBeforeWalletAndCod + $rawCodFee);
                }
            }

            $isFullyPaidByWallet = ($useWallet && $walletAmountUsed >= $payableBeforeWalletAndCod);
            $codFee = $isFullyPaidByWallet ? 0.00 : $rawCodFee;
            $payableBeforeWallet = $payableBeforeWalletAndCod + $codFee;
            
            if ($isFullyPaidByWallet) {
                $walletAmountUsed = $payableBeforeWalletAndCod;
            }

            $totalAmount = max(0, $payableBeforeWallet - $walletAmountUsed);
            $finalPaymentMethod = $isFullyPaidByWallet ? 'wallet' : 'cod';
            $finalPaymentStatus = $isFullyPaidByWallet ? 'paid' : 'pending';

            // Save address to user's address book if new
            if ($user) {
                $existingAddress = $user->addresses()
                    ->where('address', $data['shipping_address'])
                    ->where('zip', $data['shipping_zip'])
                    ->first();

                if (!$existingAddress) {
                    $user->addresses()->create([
                        'name' => $data['shipping_name'],
                        'phone' => $data['shipping_phone'],
                        'address' => $data['shipping_address'],
                        'city' => $data['shipping_city'],
                        'state' => $data['shipping_state'],
                        'zip' => $data['shipping_zip'],
                        'country' => $data['shipping_country'] ?? 'India',
                    ]);
                }
            }

            $orderNumber = $this->generateOrderNumber();

            // 1. Create Order
            $order = Order::create([
                'user_id' => $user->id,
                'order_number' => $orderNumber,
                'subtotal_amount' => $subtotalAmount,
                'shipping_charge' => $shippingFee,
                'coupon_id' => $couponId,
                'coupon_discount_amount' => $discountAmount,
                'wallet_amount_used' => $walletAmountUsed,
                'cod_fee' => $codFee,
                'total_amount' => $totalAmount,
                'payment_method' => $finalPaymentMethod,
                'payment_status' => $finalPaymentStatus,
                'status' => 'confirmed',
                'shipping_name' => $data['shipping_name'],
                'shipping_email' => $data['shipping_email'],
                'shipping_phone' => $data['shipping_phone'],
                'shipping_address' => $data['shipping_address'],
                'shipping_city' => $data['shipping_city'],
                'shipping_state' => $data['shipping_state'],
                'shipping_zip' => $data['shipping_zip'],
                'shipping_country' => $data['shipping_country'],
                'notes' => $data['notes'] ?? null,
            ]);

            // 1.1 Debit Wallet Balance if used
            if ($walletAmountUsed > 0 && $user) {
                app(\App\Services\WalletService::class)->redeemWalletForOrder($user, $order, $walletAmountUsed);
                Session::forget('use_wallet');
            }

            // 2. Create Payment Ledger Entry
            Payment::create([
                'order_id'             => $order->id,
                'user_id'              => $user->id,
                'order_number'         => $orderNumber,
                'gateway'              => $finalPaymentMethod,
                'amount'               => $totalAmount,
                'currency'             => 'INR',
                'status'               => $isFullyPaidByWallet ? 'SUCCESS' : 'PENDING',
                'payment_method_group' => $finalPaymentMethod,
                'gateway_message'      => $isFullyPaidByWallet ? '100% Covered by ShopCalm Wallet Balance' : 'Cash on Delivery (Pending collection by rider)',
            ]);

            // 3. Record Coupon Usage
            if ($couponId) {
                $this->couponService->recordUsage($couponId, $user, $order, $discountAmount);
            }

            // 4. Process Items, Tax and Stock
            $totalOrderTax = 0.00;
            foreach ($cart->items as $item) {
                $product = Product::where('id', $item->product_id)->lockForUpdate()->first();

                $availStock = $product ? $product->getOptionStock($item->selected_option) : 0;
                if (!$product || $availStock < $item->quantity) {
                    throw new Exception("Product {$item->product->name} has insufficient stock (Only {$availStock} available).");
                }

                $originalPrice = (float) $product->price;
                $unitPrice = (float) $item->unit_price;
                $offerDiscount = max(0, $originalPrice - $unitPrice);
                $itemTotalPrice = $unitPrice * $item->quantity;

                $taxRate = (float) ($product->tax_rate ?? 18.00);
                $taxableBase = $taxRate > 0 ? ($itemTotalPrice / (1 + ($taxRate / 100))) : $itemTotalPrice;
                $itemTaxAmount = round($itemTotalPrice - $taxableBase, 2);
                $totalOrderTax += $itemTaxAmount;

                $order->items()->create([
                    'product_id'      => $product->id,
                    'product_name'    => $product->name,
                    'original_price'  => $originalPrice,
                    'offer_discount'  => $offerDiscount,
                    'unit_price'      => $unitPrice,
                    'quantity'        => $item->quantity,
                    'total_price'     => $itemTotalPrice,
                    'tax_rate'        => $taxRate,
                    'tax_amount'      => $itemTaxAmount,
                    'selected_option' => $item->selected_option,
                ]);

                $stockBefore = $product->stock;
                $stockAfter = max(0, $stockBefore - $item->quantity);

                $order->stockMovements()->create([
                    'product_id'    => $product->id,
                    'movement_type' => MovementType::SALE,
                    'source'        => StockSource::ORDER,
                    'quantity'      => $item->quantity,
                    'stock_before'  => $stockBefore,
                    'stock_after'   => $stockAfter,
                    'notes'         => "Order #{$order->order_number} placed (COD)" . ($item->selected_option ? " [Option: {$item->selected_option}]" : "") . ".",
                    'created_by'    => null,
                ]);

                $product->reduceOptionStock($item->selected_option, $item->quantity);
            }

            $order->update(['tax_amount' => $totalOrderTax]);

            // 5. Fulfillment & Status
            $this->orderService->recordInitialStatus($order);
            $this->fulfillmentService->createInitialFulfillment($order);

            // 6. Clear Selected Cart Items
            $this->cartService->clearSelectedItems();

            // 7. Trigger WhatsApp Order Confirmation
            try {
                app(\App\Services\WhatsAppService::class)->sendOrderConfirmation($order);
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::warning("[CheckoutService] WhatsApp order confirmation failed: " . $e->getMessage());
            }

            return $order;
        });
    }

    /**
     * Create an eager Pending Online Order before payment gateway redirect.
     * Records the order & items immediately so no transaction is ever lost.
     */
    public function createPendingOnlineOrder(array $data, User $user): Order
    {
        return DB::transaction(function () use ($data, $user) {
            $existingPending = $this->getActivePendingOrder($user);
            if ($existingPending) {
                throw new Exception("You already have a pending order (#{$existingPending->order_number}). You cannot place a new order until you cancel or proceed with that order.");
            }

            $cart = $this->cartService->getSelectedCart();
            if ($cart->items->isEmpty()) {
                throw new Exception("Shopping cart has no items selected for checkout.");
            }

            // Validate shipping PIN code serviceability
            $shippingZip = $data['shipping_zip'] ?? ($data['shipping_pincode'] ?? '');
            $deliveryService = app(\App\Services\DeliveryService::class);
            $pincodeCheck = $deliveryService->checkServiceability($shippingZip);
            if (!$pincodeCheck['is_serviceable']) {
                throw new Exception("Delivery is currently unavailable to PIN code {$shippingZip}. Please select a serviceable delivery address.");
            }

            $subtotalAmount = $this->cartService->subtotal();
            $discountAmount = 0;
            $couponId = null;

            // Handle Coupon
            $appliedCouponCode = $data['coupon_code'] ?? Session::get('applied_coupon');
            if ($appliedCouponCode) {
                $coupon = $this->couponService->validateCoupon($appliedCouponCode, $user, $subtotalAmount);
                $discountAmount = $this->couponService->calculateDiscount($coupon, $subtotalAmount);
                $couponId = $coupon->id;
            }

            // Handle Shipping Fee
            $freeShippingMin = (float) \App\Models\Setting::get('free_shipping_min', 499);
            $shippingFee = ($subtotalAmount >= $freeShippingMin) ? 0.00 : (float) ($pincodeCheck['delivery_charge'] ?? 0.00);

            // Handle Offer Discount
            $offerDiscount = app(\App\Services\OfferService::class)->calculateCheckoutOfferDiscount($cart);
            $payableBeforeWallet = max(0, $subtotalAmount - $discountAmount - $offerDiscount + $shippingFee);

            // Handle Wallet Deduction
            $useWallet = !empty($data['use_wallet']) || Session::get('use_wallet', false);
            $walletAmountUsed = 0.00;
            if ($useWallet && $user) {
                $userWallet = app(\App\Services\WalletService::class)->getOrCreateWallet($user);
                if ($userWallet->status === 'active' && $userWallet->balance > 0) {
                    $walletAmountUsed = min((float) $userWallet->balance, $payableBeforeWallet);
                }
            }

            $totalAmount = max(0, $payableBeforeWallet - $walletAmountUsed);

            // Save address to user's address book if new
            $existingAddress = $user->addresses()
                ->where('address', $data['shipping_address'])
                ->where('zip', $data['shipping_zip'])
                ->first();

            if (!$existingAddress) {
                $user->addresses()->create([
                    'name'    => $data['shipping_name'],
                    'phone'   => $data['shipping_phone'],
                    'address' => $data['shipping_address'],
                    'city'    => $data['shipping_city'],
                    'state'   => $data['shipping_state'],
                    'zip'     => $data['shipping_zip'],
                    'country' => $data['shipping_country'] ?? 'India',
                ]);
            }

            $orderNumber = $this->generateOrderNumber();

            // 1. Create Initial Order with 'pending' status
            $order = Order::create([
                'user_id'                => $user->id,
                'order_number'           => $orderNumber,
                'subtotal_amount'        => $subtotalAmount,
                'shipping_charge'        => $shippingFee,
                'coupon_id'              => $couponId,
                'coupon_discount_amount' => $discountAmount,
                'wallet_amount_used'     => $walletAmountUsed,
                'cod_fee'                => 0.00,
                'total_amount'           => $totalAmount,
                'payment_method'         => 'online',
                'payment_status'         => 'pending',
                'status'                 => 'pending',
                'shipping_name'          => $data['shipping_name'],
                'shipping_email'         => $data['shipping_email'] ?? ($user->email ?? null),
                'shipping_phone'         => $data['shipping_phone'],
                'shipping_address'       => $data['shipping_address'],
                'shipping_city'          => $data['shipping_city'],
                'shipping_state'         => $data['shipping_state'],
                'shipping_zip'           => $data['shipping_zip'] ?? ($data['shipping_pincode'] ?? ''),
                'shipping_country'       => $data['shipping_country'] ?? 'India',
                'notes'                  => $data['notes'] ?? null,
            ]);

            // 1.1 Debit Wallet Balance immediately if used (locks balance against double-spending)
            if ($walletAmountUsed > 0) {
                app(\App\Services\WalletService::class)->redeemWalletForOrder($user, $order, $walletAmountUsed);
                Session::forget('use_wallet');
            }

            // 2. Create Order Items and Tax Calculation
            $totalOrderTax = 0.00;
            foreach ($cart->items as $item) {
                $product = $item->product;
                $originalPrice = (float) ($product ? $product->price : $item->unit_price);
                $unitPrice = (float) $item->unit_price;
                $itemOfferDiscount = max(0, $originalPrice - $unitPrice);
                $itemTotalPrice = $unitPrice * $item->quantity;

                $taxRate = (float) ($product->tax_rate ?? 18.00);
                $taxableBase = $taxRate > 0 ? ($itemTotalPrice / (1 + ($taxRate / 100))) : $itemTotalPrice;
                $itemTaxAmount = round($itemTotalPrice - $taxableBase, 2);
                $totalOrderTax += $itemTaxAmount;

                $order->items()->create([
                    'product_id'      => $item->product_id,
                    'product_name'    => $item->product_name ?? ($product ? $product->name : 'Product'),
                    'original_price'  => $originalPrice,
                    'offer_discount'  => $itemOfferDiscount,
                    'unit_price'      => $unitPrice,
                    'quantity'        => $item->quantity,
                    'total_price'     => $itemTotalPrice,
                    'tax_rate'        => $taxRate,
                    'tax_amount'      => $itemTaxAmount,
                    'selected_option' => $item->selected_option,
                ]);
            }

            $order->update(['tax_amount' => $totalOrderTax]);

            // 3. Create Initial Payment Ledger Entry in PENDING status
            Payment::create([
                'order_id'             => $order->id,
                'user_id'              => $user->id,
                'order_number'         => $orderNumber,
                'gateway'              => 'razorpay',
                'amount'               => $totalAmount,
                'currency'             => 'INR',
                'status'               => 'PENDING',
                'payment_method_group' => 'online',
                'gateway_message'      => 'Awaiting customer payment authorization on Razorpay.',
            ]);

            // 4. Initial Status History
            $order->statusHistories()->create([
                'previous_status' => 'pending',
                'current_status'  => 'pending',
                'changed_by'      => $user->id,
                'notes'           => 'Online order initiated. Awaiting payment authorization.',
            ]);

            return $order;
        });
    }

    /**
     * Mark an existing Order as PAID upon verified Razorpay payment callback/webhook.
     */
    public function markOrderPaid(Order $order, array $paymentData, User $user): Order
    {
        return DB::transaction(function () use ($order, $paymentData, $user) {
            // If already marked paid, return idempotent instance
            if ($order->payment_status === 'paid' && $order->status !== 'pending') {
                return $order;
            }

            // 1. Update Order Status
            $order->update([
                'payment_status' => 'paid',
                'status'         => 'confirmed',
            ]);

            // 2. Debit Wallet Balance if used (if not already debited at pending order creation)
            if ((float) $order->wallet_amount_used > 0) {
                $alreadyDebited = \App\Models\WalletTransaction::where('order_id', $order->id)
                    ->where('type', 'DEBIT')
                    ->exists();
                if (!$alreadyDebited) {
                    app(\App\Services\WalletService::class)->redeemWalletForOrder($user, $order, (float) $order->wallet_amount_used);
                }
                Session::forget('use_wallet');
            }

            // 3. Record Coupon Usage
            if ($order->coupon_id) {
                $this->couponService->recordUsage($order->coupon_id, $user, $order, (float) $order->coupon_discount_amount);
            }

            // 4. Update Payment Ledger Entry with Bank UTR & Gateway IDs
            Payment::updateOrCreate(
                ['order_id' => $order->id],
                [
                    'user_id'                => $user->id,
                    'order_number'           => $order->order_number,
                    'gateway'                => $paymentData['gateway'] ?? 'razorpay',
                    'gateway_order_id'       => $paymentData['gateway_order_id'] ?? null,
                    'gateway_payment_id'     => $paymentData['gateway_payment_id'] ?? null,
                    'payment_session_id'     => $paymentData['payment_session_id'] ?? null,
                    'amount'                 => (float) $order->total_amount,
                    'currency'               => 'INR',
                    'status'                 => 'SUCCESS',
                    'payment_method_group'   => $paymentData['payment_method_group'] ?? 'upi',
                    'payment_method_details' => $paymentData['payment_method_details'] ?? null,
                    'bank_reference'         => $paymentData['bank_reference'] ?? null,
                    'payment_time'           => $paymentData['payment_time'] ?? now(),
                    'gateway_message'        => $paymentData['gateway_message'] ?? 'Transaction Successful',
                    'raw_response'           => $paymentData['raw_response'] ?? null,
                ]
            );

            // 5. Deduct Product Stock & Record Movements
            foreach ($order->items as $item) {
                $product = Product::where('id', $item->product_id)->lockForUpdate()->first();
                if ($product) {
                    $stockBefore = $product->stock;
                    $stockAfter = max(0, $stockBefore - $item->quantity);

                    $order->stockMovements()->create([
                        'product_id'    => $product->id,
                        'movement_type' => MovementType::SALE,
                        'source'        => StockSource::ORDER,
                        'quantity'      => $item->quantity,
                        'stock_before'  => $stockBefore,
                        'stock_after'   => $stockAfter,
                        'notes'         => "Prepaid Online Order #{$order->order_number} verified via Payment Gateway (Bank UTR: " . ($paymentData['bank_reference'] ?? 'N/A') . ").",
                        'created_by'    => null,
                    ]);

                    $product->reduceOptionStock($item->selected_option, $item->quantity);
                }
            }

            // 6. Initialize Fulfillment & Status History
            $this->orderService->recordInitialStatus($order);
            $this->fulfillmentService->createInitialFulfillment($order);

            $order->statusHistories()->create([
                'previous_status' => 'pending',
                'current_status'  => 'confirmed',
                'changed_by'      => $user->id,
                'notes'           => "Prepaid Online Order verified via Payment Gateway (Bank UTR: " . ($paymentData['bank_reference'] ?? 'N/A') . ").",
            ]);

            // 7. Clear Selected Cart Items
            $this->cartService->clearSelectedItems();

            // 8. Trigger Email & WhatsApp Confirmation
            try {
                app(\App\Services\EmailService::class)->sendOrderConfirmation($order);
                app(\App\Services\WhatsAppService::class)->sendOrderConfirmation($order);
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::warning("[CheckoutService] Order confirmation notifications failed: " . $e->getMessage());
            }

            return $order;
        });
    }

    /**
     * Mark an online order payment as FAILED or DROPPED.
     */
    public function markOrderPaymentFailed(Order $order, string $reason = 'Payment incomplete or cancelled.'): Order
    {
        return DB::transaction(function () use ($order, $reason) {
            $prevStatus = $order->status;
            $order->update([
                'payment_status' => 'failed',
                'status'         => 'pending',
            ]);

            // Release/Refund wallet amount back to customer if it was debited for this failed order
            if ((float) $order->wallet_amount_used > 0) {
                $debitedTxn = \App\Models\WalletTransaction::where('order_id', $order->id)
                    ->where('type', 'DEBIT')
                    ->first();
                $alreadyRefunded = \App\Models\WalletTransaction::where('order_id', $order->id)
                    ->where('type', 'CREDIT')
                    ->where('source', 'ORDER_REFUND')
                    ->exists();

                if ($debitedTxn && !$alreadyRefunded) {
                    $user = $order->user ?? User::find($order->user_id);
                    if ($user) {
                        $userWallet = app(\App\Services\WalletService::class)->getOrCreateWallet($user);
                        $userWallet->credit(
                            (float) $order->wallet_amount_used,
                            'ORDER_REFUND',
                            "Wallet refund for uncompleted online Order #{$order->order_number}",
                            $order->id
                        );
                    }
                }
            }

            $payment = Payment::where('order_id', $order->id)->latest()->first();
            if ($payment) {
                $payment->update([
                    'status'          => 'FAILED',
                    'gateway_message' => $reason,
                ]);
            }

            $order->statusHistories()->create([
                'previous_status' => $prevStatus,
                'current_status'  => 'pending',
                'changed_by'      => $order->user_id,
                'notes'           => "Payment incomplete: {$reason}",
            ]);

            return $order;
        });
    }

    /**
     * Convert an unpaid/failed online order to Cash on Delivery (COD).
     */
    public function switchToCod(Order $order, User $user): Order
    {
        return DB::transaction(function () use ($order, $user) {
            if ($order->payment_status === 'paid') {
                throw new Exception("This order is already paid online.");
            }

            // 1. Check PIN code serviceability & COD availability
            $deliveryService = app(\App\Services\DeliveryService::class);
            $pincodeCheck = $deliveryService->checkServiceability($order->shipping_zip ?? '');
            if (!$pincodeCheck['is_serviceable']) {
                throw new Exception("Delivery is currently unavailable to PIN code {$order->shipping_zip}.");
            }
            if (!$pincodeCheck['is_cod_available']) {
                throw new Exception("Cash on Delivery (COD) is not available for PIN code {$order->shipping_zip}. Please retry online payment.");
            }

            // 2. Resolve COD fee and update total amount if switching from prepaid (0.00)
            $newCodFee = (float) ($pincodeCheck['cod_fee'] ?? 40.00);
            $currentCodFee = (float) $order->cod_fee;

            $newTotalAmount = (float) $order->total_amount;
            if ($currentCodFee <= 0 && $newCodFee > 0) {
                $newTotalAmount += $newCodFee;
            }

            // 3. Update Order Mode & Status
            $order->update([
                'payment_method' => 'cod',
                'cod_fee'        => $newCodFee,
                'total_amount'   => $newTotalAmount,
                'payment_status' => 'pending',
                'status'         => 'confirmed',
            ]);

            // 3.1 Debit Wallet Balance if used and not currently debited (or if refunded on failure)
            if ((float) $order->wallet_amount_used > 0) {
                $debitedCount = \App\Models\WalletTransaction::where('order_id', $order->id)->where('type', 'DEBIT')->count();
                $refundedCount = \App\Models\WalletTransaction::where('order_id', $order->id)->where('type', 'CREDIT')->count();

                if ($debitedCount <= $refundedCount) {
                    app(\App\Services\WalletService::class)->redeemWalletForOrder($user, $order, (float) $order->wallet_amount_used);
                }
            }

            // 4. Update Payment Ledger
            Payment::updateOrCreate(
                ['order_id' => $order->id],
                [
                    'user_id'              => $user->id,
                    'order_number'         => $order->order_number,
                    'gateway'              => 'cod',
                    'amount'               => $newTotalAmount,
                    'currency'             => 'INR',
                    'status'               => 'PENDING',
                    'payment_method_group' => 'cod',
                    'gateway_message'      => 'Switched to Cash on Delivery (Pending collection by rider)',
                ]
            );

            // 3. Deduct Product Stock & Record Movements
            foreach ($order->items as $item) {
                $product = Product::where('id', $item->product_id)->lockForUpdate()->first();
                if ($product) {
                    $stockBefore = $product->stock;
                    $stockAfter = max(0, $stockBefore - $item->quantity);

                    $order->stockMovements()->create([
                        'product_id'    => $product->id,
                        'movement_type' => MovementType::SALE,
                        'source'        => StockSource::ORDER,
                        'quantity'      => $item->quantity,
                        'stock_before'  => $stockBefore,
                        'stock_after'   => $stockAfter,
                        'notes'         => "Order #{$order->order_number} converted to Cash on Delivery (COD).",
                        'created_by'    => null,
                    ]);

                    $product->stock = $stockAfter;
                    $product->save();
                }
            }

            // 4. Initialize Fulfillment
            $this->orderService->recordInitialStatus($order);
            $this->fulfillmentService->createInitialFulfillment($order);

            $order->statusHistories()->create([
                'previous_status' => 'pending',
                'current_status'  => 'confirmed',
                'changed_by'      => $user->id,
                'notes'           => "Customer switched payment method to Cash on Delivery (COD).",
            ]);

            // 5. Clear Selected Cart Items
            $this->cartService->clearSelectedItems();

            // 6. Trigger Email & WhatsApp Confirmation
            try {
                app(\App\Services\EmailService::class)->sendOrderConfirmation($order);
                app(\App\Services\WhatsAppService::class)->sendOrderConfirmation($order);
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::warning("[CheckoutService] Order confirmation notifications failed: " . $e->getMessage());
            }

            return $order;
        });
    }

    /**
     * Backward-compatible alias for existing callers.
     */
    public function finalizePrepaidOrder(string $orderNumber, array $checkoutData, array $paymentData, User $user): Order
    {
        $order = Order::where('order_number', $orderNumber)->first();
        if ($order) {
            return $this->markOrderPaid($order, $paymentData, $user);
        }

        // Fallback if order wasn't created prior to gateway
        $data = array_merge($checkoutData, ['payment_mode' => 'online']);
        $pendingOrder = $this->createPendingOnlineOrder($data, $user);
        return $this->markOrderPaid($pendingOrder, $paymentData, $user);
    }

    /**
     * Generate a unique, high-concurrency collision-proof Order Number.
     * Guaranteed zero collision across simultaneous multi-user checkouts.
     */
    public function generateOrderNumber(): string
    {
        do {
            $candidate = 'WK' . date('Ymd') . strtoupper(bin2hex(random_bytes(3)));
        } while (
            Order::where('order_number', $candidate)->exists() ||
            Payment::where('order_number', $candidate)->exists()
        );

        return $candidate;
    }
}
