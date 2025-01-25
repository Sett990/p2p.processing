<?php

namespace App\Services\Order\Features;

use App\Enums\OrderStatus;
use App\Enums\TransactionType;
use App\Events\OrderFullyCreatedEvent;
use App\Exceptions\OrderException;
use App\Models\Order;
use App\Models\PaymentDetail;
use App\Models\PaymentGateway;
use App\Services\Order\Features\OrderDetailProvider\OrderDetailProvider;
use App\Services\Order\Utils\DailyLimit;
use Illuminate\Support\Facades\DB;

class OrderDetailSetter
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

        $details = (new OrderDetailProvider(
            merchant: $this->order->merchant,
            amount: $this->order->base_amount,
            currency: null,
            gateway: $this->paymentGateway,
            subGateway: null,
            detailType: null,
        ))->provide();

        DB::transaction(function () use ($details) {
            $expiresAt = now()->addMinutes($details->gateway->reservationTime);

            $paymentDetail = PaymentDetail::find($details->id);

            (new DailyLimit(
                paymentDetail: $paymentDetail,
                amount: $details->finalAmount
            ))->increment();

            $paymentDetail->user->wallet->takeFromTrust(
                amount: $details->profitTotal,
                type: TransactionType::PAYMENT_FOR_OPENED_ORDER
            );

            $this->order->update([
                'amount' => $details->finalAmount,
                'profit' => $details->profitTotal,
                'merchant_profit' => $details->profitMerchantPart,
                'service_profit' => $details->profitServicePart,
                'trader_profit' => $details->traderMarkup,
                'base_conversion_price' => $details->exchangePriceInitial,
                'conversion_price' => $details->exchangePriceWithMarkup,
                'trader_commission_rate' => $details->traderMarkupRate,
                'service_commission_rate_total' => $details->gateway->serviceCommissionRateTotal,
                'service_commission_rate_merchant' => $details->gateway->serviceCommissionRateMerchant,
                'service_commission_rate_client' => $details->gateway->serviceCommissionRateClient,
                'payment_gateway_id' => $details->gateway->id,
                'payment_detail_id' => $details->id,
                'expires_at' => $expiresAt,
            ]);
        });

        OrderFullyCreatedEvent::dispatch($this->order);

        return $this->order;
    }
}
