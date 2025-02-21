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
            $serviceCommissionRateMerchant = isset($gatewaySettings[$this->paymentGateway->id]['merchant_commission'])
                ? $gatewaySettings[$this->paymentGateway->id]['merchant_commission']
                : $serviceCommissionRateTotal;

            $serviceCommissionRateClient = $serviceCommissionRateTotal - $serviceCommissionRateMerchant;

            return new OrderServiceCommissionValue(
                total: $serviceCommissionRateTotal,
                merchant: $serviceCommissionRateMerchant,
                client: $serviceCommissionRateClient
            );
        } else {
            return new OrderServiceCommissionValue(
                total: 0,
                merchant: 0,
                client: 0
            );
        }

        //
        //$service_commissions = $merchant->user->meta->service_commissions;

        $service_commission_rate_total = $this->paymentGateway->order_service_commission_rate;
        $service_commission_rate_merchant = $service_commission_rate_total;
        $service_commission_rate_client = 0;

        if (isset($service_commissions[$merchant->id][$paymentGateway->id])) {
            if ($service_commissions[$merchant->id][$paymentGateway->id]['gateway_total_commission'] > 0) {
                $service_commission_rate_total = $service_commissions[$merchant->id][$paymentGateway->id]['gateway_total_commission'];
            }

            $service_commission_rate_merchant = $service_commissions[$merchant->id][$paymentGateway->id]['merchant_commission'];
            $service_commission_rate_client = $service_commission_rate_total - $service_commission_rate_merchant;

            if ($service_commissions[$merchant->id][$paymentGateway->id]['gateway_total_commission'] === 0) {
                $service_commission_rate_total = 0;
                $service_commission_rate_merchant = 0;
                $service_commission_rate_client = 0;
            }
        }

        return [
            'total' => (float)$service_commission_rate_total,
            'merchant' => (float)$service_commission_rate_merchant,
            'client' => (float)$service_commission_rate_client,
        ];
    }

    public function getPayoutServiceCommissionRate(): float
    {
        return $this->payoutServiceCommission;
    }
}
