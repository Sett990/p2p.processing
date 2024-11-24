<?php

namespace App\Services\Order\Utils;

use App\Models\Merchant;
use App\Models\PaymentGateway;
use App\Services\Order\ValueObjects\ServiceCommissionValue;

class ServiceCommission
{
    protected float $serviceCommissionRateTotal;
    protected float $serviceCommissionRateMerchant;

    public function __construct(
        protected PaymentGateway $paymentGateway,
        protected Merchant $merchant
    )
    {
        $this->serviceCommissionRateTotal = $paymentGateway->service_commission_rate;

        $commissionsSettings = $this->merchant->user->meta->service_commissions;
        $this->serviceCommissionRateMerchant = isset($commissionsSettings[$merchant->id][$paymentGateway->id])
            ? $commissionsSettings[$this->merchant->id][$this->paymentGateway->id]
            : $this->serviceCommissionRateTotal;
    }

    public function getCommissionRate(): ServiceCommissionValue
    {
        $serviceCommissionRateClient = $this->serviceCommissionRateTotal - $this->serviceCommissionRateMerchant;

        return new ServiceCommissionValue(
            serviceCommissionRateTotal: $this->serviceCommissionRateTotal,
            serviceCommissionRateMerchant: $this->serviceCommissionRateMerchant,
            serviceCommissionRateClient: $serviceCommissionRateClient
        );
    }
}
