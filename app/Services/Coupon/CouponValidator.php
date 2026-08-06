<?php

namespace App\Services\Coupon;

use App\Models\Coupon;
use App\Models\User;
use App\Models\Product;
use Carbon\Carbon;
use App\DTOs\CouponValidationResult;
use Illuminate\Support\Collection;

class CouponValidator
{
    public function validate(Coupon $coupon, User $user, float $totalAmount, Collection $cartItems): CouponValidationResult
    {
        if (!$coupon->is_active) {
            return CouponValidationResult::failure('This coupon is currently inactive.');
        }

        $now = Carbon::now();
        if ($coupon->valid_from && $now->lt($coupon->valid_from)) {
            return CouponValidationResult::failure('This coupon is not yet active.');
        }

        if ($coupon->valid_until && $now->gt($coupon->valid_until)) {
            return CouponValidationResult::failure('This coupon has expired.');
        }

        if ($coupon->usage_limit && $coupon->used_count >= $coupon->usage_limit) {
            return CouponValidationResult::failure('This coupon has reached its maximum usage limit.');
        }

        if ($totalAmount < $coupon->minimum_order_amount) {
            return CouponValidationResult::failure('Minimum order amount of ₹' . number_format($coupon->minimum_order_amount, 2) . ' required.');
        }

        // Per customer limit
        $userUsageCount = $coupon->usages()->where('user_id', $user->id)->count();
        if ($userUsageCount >= $coupon->usage_limit_per_customer) {
            return CouponValidationResult::failure('You have already used this coupon the maximum number of times.');
        }

        // Applicability check (Category/Brand/Product)
        if ($coupon->applicable_type->value !== 'ALL') {
            $isEligible = $this->checkApplicability($coupon, $cartItems);
            if (!$isEligible) {
                return CouponValidationResult::failure('This coupon is not applicable to the items in your cart.');
            }
        }

        return CouponValidationResult::success($coupon, 0);
    }

    private function checkApplicability(Coupon $coupon, Collection $cartItems): bool
    {
        foreach ($cartItems as $item) {
            $product = $item->product;

            switch ($coupon->applicable_type->value) {
                case 'PRODUCT':
                    if ($item->product_id == $coupon->applicable_id) return true;
                    break;
                case 'CATEGORY':
                    if ($product->category_id == $coupon->applicable_id) return true;
                    break;
                case 'BRAND':
                    if ($product->brand_id == $coupon->applicable_id) return true;
                    break;
            }
        }
        return false;
    }
}
