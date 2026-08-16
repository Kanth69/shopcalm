<?php

namespace App\Services;

use App\Models\Coupon;
use App\Models\User;
use App\Models\Order;
use App\Models\Category;
use App\Models\Brand;
use App\Models\Product;
use Carbon\Carbon;
use Exception;

class CouponService
{
    /**
     * Validate coupon eligibility for a user and cart subtotal.
     */
    public function validateCoupon(string $code, ?User $user, float $totalAmount, $cart = null): Coupon
    {
        $coupon = Coupon::with(['categories', 'brands', 'products'])->where('code', strtoupper(trim($code)))->first();

        if (!$coupon) {
            throw new Exception("Invalid coupon code.");
        }

        if (!$coupon->isValid()) {
            throw new Exception("This coupon is inactive or has expired.");
        }

        if ($totalAmount < $coupon->minimum_order_amount) {
            throw new Exception("Minimum order amount of ₹" . number_format($coupon->minimum_order_amount, 2) . " required for this coupon.");
        }

        // Per customer limit check
        if ($user) {
            $userUsageCount = $coupon->usages()->where('user_id', $user->id)->count();
            if ($userUsageCount >= $coupon->usage_limit_per_customer) {
                throw new Exception("You have already used this coupon the maximum allowed number of times.");
            }
        }

        // Category / Brand / Product Restrictions Validation
        if ($cart && $cart->items && $cart->items->isNotEmpty()) {
            $this->validateApplicability($coupon, $cart);
        }

        return $coupon;
    }

    /**
     * Alias method for validateCoupon
     */
    public function validate(string $code, ?User $user, float $totalAmount, $cart = null): Coupon
    {
        return $this->validateCoupon($code, $user, $totalAmount, $cart);
    }

    /**
     * Validate Category, Brand, or Product applicability rules against cart items.
     */
    private function validateApplicability(Coupon $coupon, $cart): void
    {
        $type = is_object($coupon->applicable_type) ? $coupon->applicable_type->value : (string) $coupon->applicable_type;

        if ($type === 'ALL') {
            return;
        }

        $cartItems = $cart->items;

        if ($type === 'CATEGORY') {
            $allowedCatIds = $coupon->categories->pluck('id')->toArray();
            if ($coupon->applicable_id) {
                $allowedCatIds[] = $coupon->applicable_id;
            }

            $hasEligibleCategory = $cartItems->contains(function ($item) use ($allowedCatIds) {
                return in_array($item->product->category_id, $allowedCatIds);
            });

            if (!$hasEligibleCategory) {
                $catName = Category::find($coupon->applicable_id)?->name ?? 'the specified';
                throw new Exception("This coupon is only valid for products in the '{$catName}' category.");
            }
        }

        if ($type === 'BRAND') {
            $allowedBrandIds = $coupon->brands->pluck('id')->toArray();
            if ($coupon->applicable_id) {
                $allowedBrandIds[] = $coupon->applicable_id;
            }

            $hasEligibleBrand = $cartItems->contains(function ($item) use ($allowedBrandIds) {
                return in_array($item->product->brand_id, $allowedBrandIds);
            });

            if (!$hasEligibleBrand) {
                $brandName = Brand::find($coupon->applicable_id)?->name ?? 'the specified';
                throw new Exception("This coupon is only valid for '{$brandName}' brand products.");
            }
        }

        if ($type === 'PRODUCT') {
            $allowedProductIds = $coupon->products->pluck('id')->toArray();
            if ($coupon->applicable_id) {
                $allowedProductIds[] = $coupon->applicable_id;
            }

            $hasEligibleProduct = $cartItems->contains(function ($item) use ($allowedProductIds) {
                return in_array($item->product_id, $allowedProductIds);
            });

            if (!$hasEligibleProduct) {
                $prodName = Product::find($coupon->applicable_id)?->name ?? 'the specified';
                throw new Exception("This coupon is only valid when purchasing '{$prodName}'.");
            }
        }
    }

    /**
     * Calculate discount amount.
     */
    public function calculateDiscount(Coupon $coupon, float $totalAmount): float
    {
        $discount = 0;
        $type = is_object($coupon->discount_type) ? $coupon->discount_type->value : (string) $coupon->discount_type;

        if ($type === 'PERCENTAGE') {
            $discount = ($totalAmount * $coupon->discount_value) / 100;
            if ($coupon->maximum_discount_amount && $discount > $coupon->maximum_discount_amount) {
                $discount = $coupon->maximum_discount_amount;
            }
        } else {
            $discount = $coupon->discount_value;
        }

        return min($discount, $totalAmount);
    }

    /**
     * Safely record coupon usage with a database lock.
     */
    public function recordUsage(int $couponId, User $user, Order $order, float $discountAmount): void
    {
        $coupon = Coupon::where('id', $couponId)->lockForUpdate()->first();
        if ($coupon) {
            $this->markUsed($coupon, $user, $order, $discountAmount);
        }
    }

    /**
     * Mark coupon as used after successful order placement.
     */
    public function markUsed(Coupon $coupon, User $user, Order $order, float $discountAmount): void
    {
        $coupon->usages()->create([
            'user_id' => $user->id,
            'order_id' => $order->id,
            'discount_amount' => $discountAmount
        ]);

        $coupon->increment('used_count');
    }
}
