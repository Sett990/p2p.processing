<?php

namespace App\Services\Commission\Classes;

use App\Models\Merchant;
use App\Models\PaymentGateway;
use App\Models\User;
use App\Services\Commission\ValueObjects\OrderServiceCommissionValue;

class ServiceCommission
{
    protected float $orderServiceCommission;
    protected float $payoutServiceCommission;

    public function __construct(
        protected PaymentGateway $paymentGateway,
        protected User $user
    )
    {
        $this->orderServiceCommission = $paymentGateway->order_service_commission_rate;
        $this->payoutServiceCommission = $paymentGateway->payout_service_commission_rate;

        /*$personalOrderServiceCommissionRate = $user->meta?->order_service_commission_rate;
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
        }*/
    }

    public function getOrderServiceCommissionRate(Merchant $merchant): OrderServiceCommissionValue
    {
        $gatewaySettings = $merchant->gateway_settings;

        $serviceCommissionRateTotal = $this->paymentGateway->order_service_commission_rate;

        if (isset($gatewaySettings[$this->paymentGateway->id]['custom_gateway_commission']) && $gatewaySettings[$this->paymentGateway->id]['custom_gateway_commission'] > 0) {
            $serviceCommissionRateTotal = $gatewaySettings[$this->paymentGateway->id]['custom_gateway_commission'];
        } else if (isset($gatewaySettings[$this->paymentGateway->id]['custom_gateway_commission']) && (int)$gatewaySettings[$this->paymentGateway->id]['custom_gateway_commission'] === 0) {
            $serviceCommissionRateTotal = 0;
        }

        if ($serviceCommissionRateTotal) {
            /*$serviceCommissionRateMerchant = isset($gatewaySettings[$this->paymentGateway->id]['merchant_commission'])
                ? $gatewaySettings[$this->paymentGateway->id]['merchant_commission']
                : $serviceCommissionRateTotal;

            $serviceCommissionRateClient = $serviceCommissionRateTotal - $serviceCommissionRateMerchant;

            return new OrderServiceCommissionValue(
                total: $serviceCommissionRateTotal,
                merchant: $serviceCommissionRateMerchant,
                client: $serviceCommissionRateClient
            );*/

            /*$serviceCommissionRateMerchant = isset($gatewaySettings[$this->paymentGateway->id]['merchant_commission'])
                ? $gatewaySettings[$this->paymentGateway->id]['merchant_commission']
                : $serviceCommissionRateTotal;

            $serviceCommissionRateClient = $serviceCommissionRateTotal - $serviceCommissionRateMerchant;*/

            return new OrderServiceCommissionValue(//TODO fix
                total: $serviceCommissionRateTotal,
                merchant: $serviceCommissionRateTotal,
                client: 0
            );
        } else {
            return new OrderServiceCommissionValue(
                total: 0,
                merchant: 0,
                client: 0
            );
        }
    }

    public function getPayoutServiceCommissionRate(): float
    {
        return $this->payoutServiceCommission;
    }
}
