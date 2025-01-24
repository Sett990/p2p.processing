<?php

namespace App\Services\Order\OrderDetails\Classes;

use App\Models\Merchant;
use App\Models\PaymentGateway;

class ServiceCommissionRate
{
    public static function calculate(Merchant $merchant, PaymentGateway $paymentGateway): array
    {
        $service_commissions = $merchant->user->meta->service_commissions;

        $service_commission_rate_total = $paymentGateway->service_commission_rate;
        $service_commission_rate_merchant = $paymentGateway->service_commission_rate;
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
}
