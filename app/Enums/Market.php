<?php

namespace App\Enums;

use App\Traits\Enumable;

enum Market: string
{
    use Enumable;

    case BYBIT = 'bybit';
    case GARANTEX = 'garantex';
}
