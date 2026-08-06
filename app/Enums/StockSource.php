<?php

namespace App\Enums;

enum StockSource: string
{
    case PURCHASE = 'PURCHASE';
    case ORDER = 'ORDER';
    case MANUAL = 'MANUAL';
}
