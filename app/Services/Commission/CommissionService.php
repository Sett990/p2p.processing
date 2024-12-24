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
    public function getOrderServiceCommission(PaymentGateway $paymentGateway, Merchant $merchant): OrderServiceCommissionValue
    {
        $user = $merchant->user;
        return (new ServiceCommission($paymentGateway, $user))->getOrderServiceCommission($merchant);
    }

    public function getPayoutServiceCommission(PaymentGateway $paymentGateway, PayoutGateway $payoutGateway): float
    {
        return (new ServiceCommission($paymentGateway, $payoutGateway->owner))->getPayoutServiceCommission();
    }

    public function getBuyPriceMarkup(PaymentGateway $paymentGateway): float
    {
        return (new ExchangeMarkupRate($paymentGateway))->getBuyPriceMarkup();
    }

    public function getSellPriceMarkup(PaymentGateway $paymentGateway): float
    {
        return (new ExchangeMarkupRate($paymentGateway))->getSellPriceMarkup();
    }
}
