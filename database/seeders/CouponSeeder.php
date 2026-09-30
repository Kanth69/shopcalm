<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Coupon;
use App\Enums\CouponType;
use App\Enums\CouponApplicableType;

class CouponSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $coupons = [
            [
                'code'                     => 'WELCOME10',
                'name'                     => 'Welcome New Customer Discount',
                'description'              => 'Get 10% instant discount on your first purchase.',
                'discount_type'            => CouponType::PERCENTAGE,
                'discount_value'           => 10.00,
                'minimum_order_amount'     => 500.00,
                'maximum_discount_amount'  => 500.00,
                'usage_limit'              => 1000,
                'usage_limit_per_customer' => 1,
                'used_count'               => 0,
                'valid_from'               => now()->subDays(30),
                'valid_until'              => now()->addYear(),
                'is_active'                => true,
                'stackable'                => false,
                'priority'                 => 1,
                'applicable_type'          => CouponApplicableType::ALL,
            ],
            [
                'code'                     => 'SHOPCALM100',
                'name'                     => 'ShopCalm Special Flat ₹100 Off',
                'description'              => 'Flat ₹100 instant discount on orders above ₹999.',
                'discount_type'            => CouponType::FLAT,
                'discount_value'           => 100.00,
                'minimum_order_amount'     => 999.00,
                'maximum_discount_amount'  => 100.00,
                'usage_limit'              => 500,
                'usage_limit_per_customer' => 2,
                'used_count'               => 0,
                'valid_from'               => now()->subDays(30),
                'valid_until'              => now()->addYear(),
                'is_active'                => true,
                'stackable'                => false,
                'priority'                 => 1,
                'applicable_type'          => CouponApplicableType::ALL,
            ],
            [
                'code'                     => 'FESTIVE20',
                'name'                     => 'Festive Season Super Savings',
                'description'              => 'Get 20% flat discount up to ₹2,000 on orders over ₹3,000.',
                'discount_type'            => CouponType::PERCENTAGE,
                'discount_value'           => 20.00,
                'minimum_order_amount'     => 3000.00,
                'maximum_discount_amount'  => 2000.00,
                'usage_limit'              => 200,
                'usage_limit_per_customer' => 1,
                'used_count'               => 0,
                'valid_from'               => now()->subDays(10),
                'valid_until'              => now()->addMonths(6),
                'is_active'                => true,
                'stackable'                => false,
                'priority'                 => 1,
                'applicable_type'          => CouponApplicableType::ALL,
            ],
        ];

        foreach ($coupons as $coupon) {
            Coupon::updateOrCreate(['code' => $coupon['code']], $coupon);
        }
    }
}
