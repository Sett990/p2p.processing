<?php

namespace App\Http\Resources;

use App\Enums\BalanceType;
use App\Enums\InvoiceStatus;
use App\Enums\InvoiceType;
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
        // Получаем суммы зачислений и выводов для траст и мерчант балансов
        $trustDeposits = 0;
        $trustWithdrawals = 0;
        $merchantDeposits = 0;
        $merchantWithdrawals = 0;

        if ($this->wallet) {
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
        }

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
                'total_balance' => $this->wallet->trust_balance
                        ->add($this->wallet->merchant_balance)
                        ->add($this->wallet->reserve_balance)
                        ->toBeauty(),
                'trust_deposits' => Money::fromUnits($trustDeposits, Currency::USDT())->toBeauty(),
                'trust_withdrawals' => Money::fromUnits($trustWithdrawals, Currency::USDT())->toBeauty(),
                'merchant_deposits' => Money::fromUnits($merchantDeposits, Currency::USDT())->toBeauty(),
                'merchant_withdrawals' => Money::fromUnits($merchantWithdrawals, Currency::USDT())->toBeauty(),
            ],
        ];
    }
}
