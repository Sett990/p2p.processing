<?php

namespace App\Enums;

use App\Traits\Enumable;

enum TrafficType: string
{
    use Enumable;

    case PRIMARY = 'primary';
    case SECONDARY = 'secondary';
}
