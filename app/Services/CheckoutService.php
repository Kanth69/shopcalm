<?php

namespace App\Services;

use App\Enums\MovementType;
use App\Enums\StockSource;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Exception;

class CheckoutService
{
    protected $cartService;
    protected $orderService;
    protected $couponService;

    public function __construct(CartService $cartService, OrderService $orderService, CouponService $couponService)
    {
        $this->cartService = $cartService;
        $this->orderService = $orderService;
        $this->couponService = $couponService;
    }

    public function placeOrder(array $data): Order
    {
        return DB::transaction(function () use ($data) {
            $cart = $this->cartService->getCart();
            if ($cart->items->isEmpty()) {
                throw new Exception("Shopping cart is empty.");
            }

            $user = Auth::user();
            $subtotalAmount = $this->cartService->subtotal();
            $discountAmount = 0;
            $couponId = null;

            // Handle Coupon
            $appliedCouponCode = Session::get('applied_coupon');
            if ($appliedCouponCode) {
                // Re-validate coupon inside transaction to ensure it's still valid at exact moment of purchase
                $coupon = $this->couponService->validateCoupon($appliedCouponCode, $user, $subtotalAmount);
                $discountAmount = $this->couponService->calculateDiscount($coupon, $subtotalAmount);
                $couponId = $coupon->id;
            }

            // Handle Offer Discount
            $offerDiscount = app(\App\Services\OfferService::class)->calculateCheckoutOfferDiscount($cart);

            $totalAmount = max(0, $subtotalAmount - $discountAmount - $offerDiscount);

            // Save address to user's address book if new or requested
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

            // 1. Create Order
            $order = Order::create([
                'user_id' => $user->id,
                'order_number' => $this->generateOrderNumber(),
                'subtotal_amount' => $subtotalAmount,
                'coupon_id' => $couponId,
                'discount_amount' => $discountAmount,
                'total_amount' => $totalAmount,
                'payment_method' => 'cod',
                'payment_status' => 'pending',
                'status' => 'pending', // Explicitly set initial status
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

            // 2. Record Coupon Usage (Now passing ID to ensure lockForUpdate is applied correctly within the service)
            if ($couponId) {
                $this->couponService->recordUsage($couponId, $user, $order, $discountAmount);
            }

            // 3. Process Items and Stock
            foreach ($cart->items as $item) {
                // Lock the product row for update to prevent race conditions during checkout
                $product = Product::where('id', $item->product_id)->lockForUpdate()->first();

                if (!$product || $product->stock < $item->quantity) {
                    throw new Exception("Product {$item->product->name} is out of stock or insufficient quantity.");
                }

                // Create Order Item
                $order->items()->create([
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'product_price' => $item->unit_price,
                    'quantity' => $item->quantity,
                ]);

                $stockBefore = $product->stock;
                $stockAfter = $stockBefore - $item->quantity;

                // Create Stock Movement (SALE)
                $order->stockMovements()->create([
                    'product_id' => $product->id,
                    'movement_type' => MovementType::SALE,
                    'source' => StockSource::ORDER,
                    'quantity' => $item->quantity,
                    'stock_before' => $stockBefore,
                    'stock_after' => $stockAfter,
                    'notes' => "Order #{$order->order_number} placed.",
                    'created_by' => null, // System action triggered by customer
                ]);

                // Deduct actual stock
                $product->stock = $stockAfter;
                $product->save();
            }

            // 4. Record Initial Order Status History
            $this->orderService->recordInitialStatus($order);

            // 5. Clear Cart
            $this->cartService->clearCart();

            return $order;
        });
    }

    private function generateOrderNumber(): string
    {
        return 'WK' . date('Ymd') . str_pad(Order::count() + 1, 6, '0', STR_PAD_LEFT);
    }
}
