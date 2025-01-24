<?php

namespace App\Services\Order\Features;

use App\Enums\OrderStatus;
use App\Enums\TransactionType;
use App\Exceptions\OrderException;
use App\Models\Order;
use App\Services\Money\Currency;
use App\Services\Money\Money;

class RollbackOrder extends BaseFeature
{
    public function __construct(
        protected Order $order,
        protected TransactionType $transactionType,
    )
    {}

    /**
     * @throws OrderException
     */
    public function handle(): bool
    {
        if ($this->order->status->notEquals(OrderStatus::PENDING)) {
            if ($this->order->status->equals(OrderStatus::FAIL)) {
                services()->wallet()->takeTrust(
                    wallet: $this->order->paymentDetail->user->wallet,
                    amount: $this->order->profit,
                    type: $this->transactionType
                );
            }
            if ($this->order->status->equals(OrderStatus::SUCCESS)) {
                services()->wallet()->takeMerchant(
                    wallet: $this->order->merchant->user->wallet,
                    amount: $this->order->merchant_profit,
                );

                $current_daily_limit = $this->calcCurrentDailyLimit();

                $this->order->paymentDetail->update([
                    'current_daily_limit' => $current_daily_limit
                ]);

                $meta = $this->order->paymentDetail->user->meta;
                $paymentDetailTurnover = $meta->payment_detail_turnover;
                $currency = $this->order->currency;

                $primary = Money::fromUnits($paymentDetailTurnover[$currency->getCode()]['primary'], Currency::USDT());
                $secondary = Money::fromUnits($paymentDetailTurnover[$currency->getCode()]['secondary'], $currency);

                $paymentDetailTurnover[$currency->getCode()]['primary'] = $primary->sub($this->order->profit)->toUnits();
                $paymentDetailTurnover[$currency->getCode()]['secondary'] = $secondary->sub($this->order->amount)->toUnits();

                $meta->update([
                    'payment_detail_turnover' => $paymentDetailTurnover
                ]);
            }

            $this->order->update([
                'status' => OrderStatus::PENDING,
                'finished_at' => null
            ]);
        } else {
            throw OrderException::make('Cant rollback order');
        }

        return true;
    }

    protected function calcCurrentDailyLimit(): Money
    {
        return $this->order->paymentDetail->current_daily_limit->sub($this->order->amount);
    }
}
