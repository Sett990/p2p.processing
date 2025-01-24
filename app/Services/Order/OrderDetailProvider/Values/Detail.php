<?php

namespace App\Services\Order\OrderDetailProvider\Values;

use App\Services\Money\Currency;
use App\Services\Money\Money;

class Detail
{
    public function __construct(
        public int      $id,
        public int      $userID,
        public int      $paymentGatewayID,
        public ?int     $subPaymentGatewayID,
        public Money    $dailyLimit,
        public Money    $currentDailyLimit,
        public Currency $currency,
        public Money    $exchangePriceInitial,
        public Money    $exchangePriceWithMarkup,
        public Money    $profitTotal,
        public Money    $profitServicePart,
        public Money    $profitMerchantPart,
        public float    $traderMarkupRate,
        public Money    $traderMarkup,
        public Gateway  $gateway,
        public Trader   $trader,
        public Money    $initialAmount,
        public Money    $finalAmount,
    )
    {}
}
