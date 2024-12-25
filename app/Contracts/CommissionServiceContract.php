<?php

namespace App\Contracts;

use App\Models\Merchant;
use App\Models\PaymentGateway;
use App\Models\PayoutGateway;
use App\Services\Commission\ValueObjects\OrderServiceCommissionValue;

interface CommissionServiceContract
{
    public function getOrderServiceCommissionRate(PaymentGateway $paymentGateway, Merchant $merchant): OrderServiceCommissionValue;

    public function getPayoutServiceCommissionRate(PaymentGateway $paymentGateway, PayoutGateway $payoutGateway): float;

    public function getBuyPriceMarkupRate(PaymentGateway $paymentGateway): float;

    public function getSellPriceMarkupRate(PaymentGateway $paymentGateway): float;
}
