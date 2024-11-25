<?php

namespace App\Services\Order\Utils;

use App\Models\PaymentDetail;
use App\Services\Money\Money;

class DailyLimit
{
    public function __construct(
        protected PaymentDetail $paymentDetail,
        protected Money $amount,
    )
    {}

    public function increment(): void
    {
        $current_daily_limit = $this->paymentDetail
            ->current_daily_limit
            ->add($this->amount);

        $this->paymentDetail->update([
            'current_daily_limit' => $current_daily_limit
        ]);
    }

    public function decrement(): void
    {
        $current_daily_limit = $this->paymentDetail
            ->current_daily_limit
            ->sub($this->amount);

        $this->paymentDetail->update([
            'current_daily_limit' => $current_daily_limit
        ]);
    }
}
