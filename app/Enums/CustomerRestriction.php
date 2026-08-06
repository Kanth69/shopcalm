<?php

namespace App\Enums;

enum CustomerRestriction: string
{
    case ALL = 'ALL';
    case NEW_ONLY = 'NEW_ONLY';
    case EXISTING_ONLY = 'EXISTING_ONLY';
}
