<?php

namespace App\Services\Order\Features\OrderDetailProvider\Filters;

use App\Services\Order\Features\OrderDetailProvider\Values\Detail;

class TrustBalance extends BaseFilter
{
    public function filter(Detail $detail): bool
    {
        $trustBalance = (int)$detail->trader->trustBalance->toUnits();
        $amount = (int)$detail->profitTotal->toUnits();

        return $trustBalance >= $amount;
    }
}
