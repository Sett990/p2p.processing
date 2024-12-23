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
        $this->serviceCommissionRateTotal = $paymentGateway->order_service_commission_rate;

        $gatewaySettings = $this->merchant->gateway_settings;
        $this->serviceCommissionRateMerchant = isset($gatewaySettings[$paymentGateway->id]['merchant_commission'])
            ? $gatewaySettings[$this->paymentGateway->id]['merchant_commission']
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
