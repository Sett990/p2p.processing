<?php

namespace App\Services\Order;

use App\Contracts\OrderServiceContract;
use App\DTO\Order\OrderCreateDTO;
use App\Enums\OrderStatus;
use App\Enums\TransactionType;
use App\Exceptions\OrderException;
use App\Models\Order;
use App\Models\PaymentGateway;
use App\Services\Money\Currency;
use App\Services\Money\Money;
use App\Services\Order\Features\CreateOrder;
use App\Services\Order\Features\FailOrder;
use App\Services\Order\Features\RollbackOrder;
use App\Services\Order\Features\SetPaymentDetailFeature;
use App\Services\Order\Features\SucceedOrder;

class OrderService implements OrderServiceContract
{
    /**
     * @throws OrderException
     */
    public function create(OrderCreateDTO $dto): Order
    {
        return (new CreateOrder($dto))->handle();
    }

    /**
     * @throws OrderException
     */
    public function setPaymentDetail(Order $order, PaymentGateway $paymentGateway): Order
    {
        return (new SetPaymentDetailFeature($order, $paymentGateway))->handle();
    }

    /**
     * @throws OrderException
     */
    public function succeed(Order $order): bool
    {
        return (new SucceedOrder($order))->handle();
    }

    /**
     * @throws OrderException
     */
    public function fail(Order $order, TransactionType $transactionType): bool
    {
        return (new FailOrder($order, $transactionType))->handle();
    }

    /**
     * @throws OrderException
     */
    public function rollback(Order $order, TransactionType $transactionType): bool
    {
        return (new RollbackOrder($order, $transactionType))->handle();
    }

    public function updateAmount(Order $order, Money $amount): bool
    {
        if ($order->status->notEquals(OrderStatus::FAIL)) {
            throw OrderException::make('Order must be failed.');
        }

        $profit = $amount
            ->convert($order->conversion_price, Currency::USDT());
        $serviceProfit = $profit->mul($order->service_commission_rate_total / 100);
        $merchantProfit = $profit->sub($serviceProfit);

        $traderMarkup = $amount
            ->convert($order->base_conversion_price, Currency::USDT())
            ->sub($profit);

        return $order->update([
            'amount' => $amount,
            'trader_profit' => $traderMarkup,
            'profit' => $profit,
            'merchant_profit' => $merchantProfit,
            'service_profit' => $serviceProfit,
        ]);
    }
}
