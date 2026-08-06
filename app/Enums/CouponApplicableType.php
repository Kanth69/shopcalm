<?php

namespace App\Enums;

enum CouponApplicableType: string
{
    case ALL = 'ALL';
    case CATEGORY = 'CATEGORY';
    case BRAND = 'BRAND';
    case PRODUCT = 'PRODUCT';
}
