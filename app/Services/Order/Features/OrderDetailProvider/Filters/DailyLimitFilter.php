<?php

namespace App\Services\Order\Features\OrderDetailProvider\Filters;

use App\Services\Order\Features\OrderDetailProvider\Values\Detail;

class DailyLimitFilter extends BaseFilter
{
    public function filter(Detail $detail): bool
    {
        $limit = (int)$detail->dailyLimit->sub($detail->currentDailyLimit)->toUnits();
        $amount = (int)$detail->finalAmount->toUnits();

        return $limit >= $amount;
    }
}
