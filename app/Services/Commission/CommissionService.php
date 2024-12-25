<?php

namespace App\Services\Commission;

use App\Contracts\CommissionServiceContract;
use App\Models\Merchant;
use App\Models\PaymentGateway;
use App\Models\PayoutGateway;
use App\Services\Commission\Classes\ExchangeMarkupRate;
use App\Services\Commission\Classes\ServiceCommission;
use App\Services\Commission\ValueObjects\OrderServiceCommissionValue;

class CommissionService implements CommissionServiceContract
{
    public function getOrderServiceCommissionRate(PaymentGateway $paymentGateway, Merchant $merchant): OrderServiceCommissionValue
    {
        $user = $merchant->user;
        return (new ServiceCommission($paymentGateway, $user))->getOrderServiceCommissionRate($merchant);
    }

    public function getPayoutServiceCommissionRate(PaymentGateway $paymentGateway, PayoutGateway $payoutGateway): float
    {
        return (new ServiceCommission($paymentGateway, $payoutGateway->owner))->getPayoutServiceCommissionRate();
    }

    public function getBuyPriceMarkupRate(PaymentGateway $paymentGateway): float
    {
        return (new ExchangeMarkupRate($paymentGateway))->getBuyPriceMarkupRate();
    }

    public function getSellPriceMarkupRate(PaymentGateway $paymentGateway): float
    {
        return (new ExchangeMarkupRate($paymentGateway))->getSellPriceMarkupRate();
    }
}
