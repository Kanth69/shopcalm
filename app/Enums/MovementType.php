<?php

namespace App\Enums;

enum MovementType: string
{
    case PURCHASE = 'PURCHASE';
    case SALE = 'SALE';
    case ADJUSTMENT = 'ADJUSTMENT';
}
