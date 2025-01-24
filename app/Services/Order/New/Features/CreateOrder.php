<?php

namespace App\Services\Order\Features;

use App\DTO\Order\OrderCreateDTO;
use App\Enums\OrderStatus;
use App\Enums\TransactionType;
use App\Exceptions\OrderException;
use App\Models\Merchant;
use App\Models\Order;
use App\Models\User;
use App\Services\Order\OrderDetails\OrderDetailProvider;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CreateOrder extends BaseFeature
{
    public function __construct(
        protected OrderCreateDTO $dto
    )
    {}

    /**
     * @throws OrderException
     */
    public function handle(): Order
    {
        return DB::transaction(function () {
            $merchant = queries()->merchant()->findByUUID($this->dto->merchant_uuid);

            $this->validateMerchant($merchant);

            $detail = (new OrderDetailProvider(
                merchant: $merchant,
                amount: $this->dto->amount,
                currency: $this->dto->currency,
                gateway: $this->dto->payment_gateway,
                detailType: $this->dto->payment_detail_type,
            ))->provide();

            $expires_at = now()->addMinutes($detail->gateway->reservationTime);

            $wallet = User::find($detail->trader->id)->wallet;

            services()->wallet()->takeTrust(
                wallet: $wallet,
                amount: $detail->profitTotal,
                type: TransactionType::PAYMENT_FOR_OPENED_ORDER
            );

            return Order::create([
                'uuid' => (string)Str::uuid(),
                'external_id' => $this->dto->external_id,
                'merchant_id' => $merchant->id,
                'base_amount' => $this->dto->amount,
                'amount' => $detail->finalAmount,
                'profit' => $detail->profitTotal,
                'merchant_profit' => $detail->profitMerchantPart,
                'service_profit' => $detail->profitServicePart,
                'trader_profit' => $detail->traderMarkup,
                'currency' => $this->dto->amount->getCurrency(),
                'base_conversion_price' => $detail->exchangePriceInitial,
                'conversion_price' => $detail->exchangePriceWithMarkup,
                'trader_commission_rate' => $detail->traderMarkupRate,
                'service_commission_rate_total' => $detail->gateway->serviceCommissionRateTotal,
                'service_commission_rate_merchant' => $detail->gateway->serviceCommissionRateMerchant,
                'service_commission_rate_client' => $detail->gateway->serviceCommissionRateClient,
                'status' => OrderStatus::PENDING,
                'callback_url' => $this->dto->callback_url,
                'success_url' => $this->dto->success_url,
                'fail_url' => $this->dto->fail_url,
                'is_h2h' => $this->dto->h2h,
                'payment_gateway_id' => $detail->gateway->id,
                'payment_detail_id' => $detail->id,
                'expires_at' => $expires_at,
            ]);
        });
    }

    protected function validateMerchant(Merchant $merchant): void
    {
        if (!$merchant->validated_at) {
            throw new OrderException('Мерчант находится на модерации.');
        }
        if ($merchant->banned_at) {
            throw new OrderException('Мерчант заблокирован.');
        }
        if (!$merchant->active) {
            throw new OrderException('Мерчант отключен.');
        }
    }
}
