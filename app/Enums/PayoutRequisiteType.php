<?php

namespace App\Enums;

use App\Traits\Enumable;

enum PayoutRequisiteType: string
{
    use Enumable;

    case CARD = 'card';
    case SBP = 'sbp';
}

