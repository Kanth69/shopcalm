<?php

namespace App\Services\Engagement; // Reusing relevant namespace or creating App\Services\Coupon

namespace App\Services\Coupon;

use App\Models\Coupon;
use App\Enums\CouponType;

class CouponCalculator
{
    public function calculate(Coupon $coupon, float $totalAmount): float
    {
        $discount = 0;

        if ($coupon->discount_type->value === CouponType::PERCENTAGE->value) {
            $discount = ($totalAmount * $coupon->discount_value) / 100;

            // Apply maximum discount cap
            if ($coupon->maximum_discount_amount && $discount > $coupon->maximum_discount_amount) {
                $discount = $coupon->maximum_discount_amount;
            }
        } else {
            $discount = $coupon->discount_value;
        }

        // Discount cannot exceed the total amount
        return min($discount, $totalAmount);
    }
}
