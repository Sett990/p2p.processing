<?php

namespace App\Http\Resources;

use App\Enums\BalanceType;
use App\Enums\InvoiceStatus;
use App\Enums\InvoiceType;
use App\Enums\OrderStatus;
use App\Models\Order;
use App\Models\User;
use App\Services\Money\Currency;
use App\Services\Money\Money;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserBalanceResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        /**
         * @var User $this
         */

        // Зачисления на траст баланс
        $trustDeposits = $this->wallet->invoices()
            ->where('status', InvoiceStatus::SUCCESS)
            ->where('type', InvoiceType::DEPOSIT)
            ->where('balance_type', BalanceType::TRUST)
            ->sum('amount');

        // Выводы с траст баланса
        $trustWithdrawals = $this->wallet->invoices()
            ->where('status', InvoiceStatus::SUCCESS)
            ->where('type', InvoiceType::WITHDRAWAL)
            ->where('balance_type', BalanceType::TRUST)
            ->sum('amount');

        // Зачисления на мерчант баланс
        $merchantDeposits = $this->wallet->invoices()
            ->where('status', InvoiceStatus::SUCCESS)
            ->where('type', InvoiceType::DEPOSIT)
            ->where('balance_type', BalanceType::MERCHANT)
            ->sum('amount');

        // Выводы с мерчант баланса
        $merchantWithdrawals = $this->wallet->invoices()
            ->where('status', InvoiceStatus::SUCCESS)
            ->where('type', InvoiceType::WITHDRAWAL)
            ->where('balance_type', BalanceType::MERCHANT)
            ->sum('amount');

        // Зачисления на баланс тимлидера
        $teamleaderDeposits = $this->wallet->invoices()
            ->where('status', InvoiceStatus::SUCCESS)
            ->where('type', InvoiceType::DEPOSIT)
            ->where('balance_type', BalanceType::TEAMLEADER)
            ->sum('amount');

        // Выводы с баланса тимлидера
        $teamleaderWithdrawals = $this->wallet->invoices()
            ->where('status', InvoiceStatus::SUCCESS)
            ->where('type', InvoiceType::WITHDRAWAL)
            ->where('balance_type', BalanceType::TEAMLEADER)
            ->sum('amount');

        $totalPaymentForOrders = $this->orders()
            ->where('status', OrderStatus::SUCCESS)
            ->whereNotNull('trader_paid_for_order')
            ->sum('trader_paid_for_order');

        $totalPaymentForOrders = $this->orders()
                ->where('status', OrderStatus::SUCCESS)
                ->whereNull('trader_paid_for_order')
                ->sum('total_profit') + $totalPaymentForOrders;

        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'avatar_uuid' => $this->avatar_uuid,
            'avatar_style' => $this->avatar_style,
            'banned_at' => $this->banned_at,
            'created_at' => $this->created_at?->format('d.m.Y H:i'),
            'role' => [
                'id' => $this->roles->first()?->id,
                'name' => $this->roles->first()?->name,
            ],
            'wallet' => [
                'id' => $this->wallet?->id,
                'trust_balance' => $this->wallet->trust_balance->add($this->wallet->reserve_balance)->toBeauty(),
                'merchant_balance' => $this->wallet->merchant_balance->toBeauty(),
                'teamleader_balance' => $this->wallet->teamleader_balance->toBeauty(),
                'total_balance' => $this->wallet->trust_balance
                        ->add($this->wallet->merchant_balance)
                        ->add($this->wallet->reserve_balance)
                        ->add($this->wallet->teamleader_balance)
                        ->toBeauty(),
                'trust_deposits' => Money::fromUnits($trustDeposits, Currency::USDT())->toBeauty(),
                'trust_withdrawals' => Money::fromUnits($trustWithdrawals, Currency::USDT())->toBeauty(),
                'merchant_deposits' => Money::fromUnits($merchantDeposits, Currency::USDT())->toBeauty(),
                'merchant_withdrawals' => Money::fromUnits($merchantWithdrawals, Currency::USDT())->toBeauty(),
                'teamleader_deposits' => Money::fromUnits($teamleaderDeposits, Currency::USDT())->toBeauty(),
                'teamleader_withdrawals' => Money::fromUnits($teamleaderWithdrawals, Currency::USDT())->toBeauty(),
                'payment_for_orders' => Money::fromUnits($totalPaymentForOrders, Currency::USDT())->toBeauty(),
            ],
        ];
    }
}
