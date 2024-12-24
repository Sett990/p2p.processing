<?php

namespace App\Contracts;

use App\Models\Merchant;
use App\Models\PaymentGateway;
use App\Models\PayoutGateway;
use App\Services\Commission\ValueObjects\OrderServiceCommissionValue;

interface CommissionServiceContract
{
    public function getOrderServiceCommission(PaymentGateway $paymentGateway, Merchant $merchant): OrderServiceCommissionValue;

    public function getPayoutServiceCommission(PaymentGateway $paymentGateway, PayoutGateway $payoutGateway): float;

    public function getBuyPriceMarkup(PaymentGateway $paymentGateway): float;

    public function getSellPriceMarkup(PaymentGateway $paymentGateway): float;
}
