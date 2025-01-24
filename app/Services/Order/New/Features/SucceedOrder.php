<?php

namespace App\Services\Order\Features;

use App\Enums\OrderStatus;
use App\Exceptions\OrderException;
use App\Models\Order;
use App\Services\Money\Currency;
use App\Services\Money\Money;
use Illuminate\Support\Facades\DB;

class SucceedOrder extends BaseFeature
{
    public function __construct(
        protected Order $order,
    )
    {}

    /**
     * @throws OrderException
     */
    public function handle(): bool
    {
        if ($this->order->status->equals(OrderStatus::PENDING)) {
            DB::transaction(function () {
                $this->order->update([
                    'status' => OrderStatus::SUCCESS,
                    'finished_at' => now()
                ]);

                $current_daily_limit = $this->calcCurrentDailyLimit();

                $this->order->paymentDetail->update([
                    'current_daily_limit' => $current_daily_limit
                ]);

                $meta = $this->order->paymentDetail->user->meta;
                $paymentDetailTurnover = $meta->payment_detail_turnover;
                $currency = $this->order->currency;

                if (empty($meta->payment_detail_turnover[$currency->getCode()])) {
                    $paymentDetailTurnover[$currency->getCode()] = [
                        'primary' => 0,
                        'secondary' => 0,
                        'primary_currency' => Currency::USDT()->getCode(),
                        'secondary_currency' => $currency->getCode(),
                    ];
                }

                $primary = Money::fromUnits($paymentDetailTurnover[$currency->getCode()]['primary'], Currency::USDT());
                $secondary = Money::fromUnits($paymentDetailTurnover[$currency->getCode()]['secondary'], $currency);

                $paymentDetailTurnover[$currency->getCode()]['primary'] = $primary->add($this->order->profit)->toUnits();
                $paymentDetailTurnover[$currency->getCode()]['secondary'] = $secondary->add($this->order->amount)->toUnits();

                $meta->update([
                    'payment_detail_turnover' => $paymentDetailTurnover
                ]);

                services()->wallet()->giveMerchant(
                    wallet: $this->order->merchant->user->wallet,
                    amount: $this->order->merchant_profit,
                );
            });
        } else {
            throw OrderException::make('Cant succeed not pending order');
        }

        return true;
    }

    protected function calcCurrentDailyLimit(): Money
    {
        return $this->order->paymentDetail->current_daily_limit->add($this->order->amount);
    }
}
