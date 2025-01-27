<?php

namespace App\Services\Order\Features\OrderDetailProvider\Filters;

use App\Services\Order\Features\OrderDetailProvider\Values\Detail;

abstract class BaseFilter
{
    abstract public function filter(Detail $detail): bool;
}
