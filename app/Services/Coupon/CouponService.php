<?php

namespace App\Services\Coupon;

use App\Models\Coupon;
use App\Models\User;
use App\Models\Order;
use App\DTOs\CouponValidationResult;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class CouponService
{
    protected $validator;
    protected $calculator;

    public function __construct(CouponValidator $validator, CouponCalculator $calculator)
    {
        $this->validator = $validator;
        $this->calculator = $calculator;
    }

    /**
     * Entry point to validate and calculate a coupon.
     */
    public function applyCoupon(string $code, User $user, float $totalAmount, Collection $cartItems): CouponValidationResult
    {
        $coupon = Coupon::where('code', strtoupper($code))->first();

        if (!$coupon) {
            return CouponValidationResult::failure('Invalid coupon code.');
        }

        $validation = $this->validator->validate($coupon, $user, $totalAmount, $cartItems);

        if (!$validation->isValid) {
            return $validation;
        }

        $discount = $this->calculator->calculate($coupon, $totalAmount);

        return CouponValidationResult::success($coupon, $discount);
    }

    /**
     * Persist coupon usage within a transaction.
     */
    public function recordUsage(Coupon $coupon, User $user, Order $order, float $discountAmount): void
    {
        DB::transaction(function () use ($coupon, $user, $order, $discountAmount) {
            // Re-fetch and lock for update to prevent race conditions on used_count
            $lockedCoupon = Coupon::where('id', $coupon->id)->lockForUpdate()->first();

            $lockedCoupon->usages()->create([
                'user_id' => $user->id,
                'order_id' => $order->id,
                'discount_amount' => $discountAmount
            ]);

            $lockedCoupon->increment('used_count');
        });
    }
}
