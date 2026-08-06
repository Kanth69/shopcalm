<?php

namespace App\DTOs;

use App\Models\Coupon;

class CouponValidationResult
{
    public function __construct(
        public bool $isValid,
        public string $message,
        public float $discount = 0,
        public ?Coupon $coupon = null
    ) {}

    public static function success(Coupon $coupon, float $discount, string $message = 'Coupon applied successfully.'): self
    {
        return new self(true, $message, $discount, $coupon);
    }

    public static function failure(string $message): self
    {
        return new self(false, $message);
    }
}
