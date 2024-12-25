<?php

namespace App\Services\Commission\Classes;

use App\Models\Merchant;
use App\Models\PaymentGateway;
use App\Models\User;
use App\Services\Commission\ValueObjects\OrderServiceCommissionValue;
use App\Services\Order\ValueObjects\ServiceCommissionValue;

class ServiceCommission
{
    protected float $orderServiceCommission;
    protected float $payoutServiceCommission;

    public function __construct(
        protected PaymentGateway $paymentGateway,
        protected User $user
    )
    {
        $personalOrderServiceCommissionRate = $user->meta?->order_service_commission_rate;
        if ($personalOrderServiceCommissionRate !== null) {
            $this->orderServiceCommission = $personalOrderServiceCommissionRate;
        } else {
            $this->orderServiceCommission = $paymentGateway->order_service_commission_rate;
        }

        $personaPayoutServiceCommissionRate = $user->meta?->payout_service_commission_rate;
        if ($personaPayoutServiceCommissionRate !== null) {
            $this->payoutServiceCommission = $personaPayoutServiceCommissionRate;
        } else {
            $this->payoutServiceCommission = $paymentGateway->payout_service_commission_rate;
        }
    }

    public function getOrderServiceCommissionRate(Merchant $merchant): OrderServiceCommissionValue
    {
        $gatewaySettings = $merchant->gateway_settings;
        $serviceCommissionRateMerchant = isset($gatewaySettings[$paymentGateway->id]['merchant_commission'])
            ? $gatewaySettings[$this->paymentGateway->id]['merchant_commission']
            : $this->orderServiceCommission;

        $serviceCommissionRateClient = $this->orderServiceCommission - $serviceCommissionRateMerchant;

        return new OrderServiceCommissionValue(
            total: $this->orderServiceCommission,
            merchant: $serviceCommissionRateMerchant,
            client: $serviceCommissionRateClient
        );
    }

    public function getPayoutServiceCommissionRate(): float
    {
        return $this->payoutServiceCommission;
    }
}
