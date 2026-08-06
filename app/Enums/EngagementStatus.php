<?php

namespace App\Enums;

enum EngagementStatus: string
{
    case PENDING = 'PENDING';
    case VIEWED = 'VIEWED';
    case DISMISSED = 'DISMISSED';
    case COMPLETED = 'COMPLETED';
    case EXPIRED = 'EXPIRED';
}
