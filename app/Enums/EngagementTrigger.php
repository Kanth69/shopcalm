<?php

namespace App\Enums;

enum EngagementTrigger: string
{
    case FIRST_LOGIN = 'FIRST_LOGIN';
    case ACCOUNT_PAGE = 'ACCOUNT_PAGE';
    case ORDER_PAGE = 'ORDER_PAGE';
    case EVENT_BASED = 'EVENT_BASED';
}
