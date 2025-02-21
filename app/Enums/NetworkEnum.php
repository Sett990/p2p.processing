<?php

namespace App\Enums;

use App\Traits\Enumable;

enum NetworkEnum: string
{
    use Enumable;

    case BSC = 'bsc';
    case TRX = 'trx';
    case ARB = 'arb';
}
