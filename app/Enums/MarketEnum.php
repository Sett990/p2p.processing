<?php

namespace App\Enums;

use App\Traits\Enumable;

enum MarketEnum: string
{
    use Enumable;

    case BYBIT = 'bybit';
    case RAPIRA = 'rapira';
}
