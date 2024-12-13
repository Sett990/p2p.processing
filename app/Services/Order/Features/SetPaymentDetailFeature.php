<?php

namespace App\Services\Order\Features;

use App\Enums\OrderStatus;
use App\Enums\TransactionType;
use App\Events\OrderFullyCreatedEvent;
use App\Exceptions\OrderException;
use App\Models\Order;
use App\Models\PaymentGateway;
use App\Services\Order\Utils\ConversionPriceCalculator;
use App\Services\Order\Utils\DailyLimit;
use App\Services\Order\Utils\PaymentAmountCalculator;
use App\Services\Order\Utils\PaymentDetailProvider;
use App\Services\Order\Utils\ProfitCalculator;
use App\Services\Order\Utils\ServiceCommission;
use App\Services\Order\Utils\TraderCommissionRate;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class SetPaymentDetailFeature
{
    public function __construct(
        protected Order $order,
        protected PaymentGateway $paymentGateway
    )
    {}

    public function handle(): Order
    {
        if ($this->order->status->notEquals(OrderStatus::PENDING)) {
            throw new OrderException('Сделка была закрыта.');
        }

        $paymentDetail = (new PaymentDetailProvider(
            amount: $this->order->base_amount,
            paymentGatewayCode: $this->paymentGateway->code,
            subPaymentGatewayCode: null,
            paymentDetailType: null,
        ))->provide();

        $expiresAt = $this->getExpirationTime($this->paymentGateway);

        $serviceCommission = (new ServiceCommission($this->paymentGateway, $this->order->merchant))->getCommissionRate();
        $traderCommissionRate = (new TraderCommissionRate($this->paymentGateway))->getCommissionRate();

        $amount = (new PaymentAmountCalculator(
            amount: $this->order->base_amount,
            serviceCommission: $serviceCommission
        ))->calculate();

        $conversionPrice = (new ConversionPriceCalculator(
            currency: $this->paymentGateway->currency,
            traderCommissionRate: $traderCommissionRate
        ))->calculate();

        $profit = (new ProfitCalculator(
            amount: $amount,
            conversionPrice: $conversionPrice,
            serviceCommission: $serviceCommission
        ))->calculate();

        DB::transaction(function () use ($amount, $paymentDetail, $profit, $traderCommissionRate, $conversionPrice, $serviceCommission, $expiresAt) {
            (new DailyLimit(
                paymentDetail: $paymentDetail,
                amount: $amount
            ))->increment();

            $paymentDetail->user->wallet->takeFromTrust(
                amount: $profit->profit,
                type: TransactionType::PAYMENT_FOR_OPENED_ORDER
            );

            $this->order->update([
                'amount' => $amount,
                'profit' => $profit->profit,
                'trader_profit' => $profit->traderProfit,
                'merchant_profit' => $profit->merchantProfit,
                'service_profit' => $profit->serviceProfit,
                'base_conversion_price' => $conversionPrice->basePrice,
                'conversion_price' => $conversionPrice->actualPrice,
                'trader_commission_rate' => $traderCommissionRate,
                'service_commission_rate_total' => $serviceCommission->serviceCommissionRateTotal,
                'service_commission_rate_merchant' => $serviceCommission->serviceCommissionRateMerchant,
                'service_commission_rate_client' => $serviceCommission->serviceCommissionRateClient,
                'payment_gateway_id' => $this->paymentGateway->id,
                'payment_detail_id' => $paymentDetail->id,
                'expires_at' => $expiresAt,
            ]);
        });

        OrderFullyCreatedEvent::dispatch($this->order);

        return $this->order;
    }

    protected function getExpirationTime(PaymentGateway $paymentGateway): Carbon
    {
        return now()->addMinutes($this->paymentGateway->reservation_time);
    }
}
