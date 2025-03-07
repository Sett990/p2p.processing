<?php

namespace App\Http\Controllers\Admin;

use App\Enums\BalanceType;
use App\Enums\InvoiceStatus;
use App\Enums\InvoiceType;
use App\Http\Controllers\Controller;
use App\Http\Resources\UserBalanceResource;
use App\Models\Invoice;
use App\Models\User;
use App\Services\Money\Currency;
use App\Services\Money\Money;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Inertia\Inertia;

class UserBalanceController extends Controller
{
    public function index(Request $request)
    {
        $filters = $this->getTableFilters();

        $users = User::query()
            ->with(['roles', 'wallet', 'wallet.invoices'])
            ->when($filters->user, function ($query) use ($filters) {
                $query->where(function ($query) use ($filters) {
                    $query->where('email', 'like', '%' . $filters->user . '%');
                    $query->orWhere('name', 'like', '%' . $filters->user . '%');
                });
            })
            ->orderByDesc('id')
            ->paginate(10);

        $users = UserBalanceResource::collection($users);

        // Получаем общую сумму всех балансов
        $totalTrustBalance = Money::fromPrecision(0, Currency::USDT());
        $totalMerchantBalance = Money::fromPrecision(0, Currency::USDT());

        User::query()
            ->chunk(100, function (Collection $users) use (&$totalTrustBalance, &$totalMerchantBalance) {
                $users->each(function ($user) use (&$totalTrustBalance, &$totalMerchantBalance) {
                    $totalTrustBalance = $totalTrustBalance->add($user->wallet->trust_balance)->add($user->wallet->reserve_balance);
                    $totalMerchantBalance = $totalMerchantBalance->add($user->wallet->merchant_balance);
                });
            });

        // Получаем общие суммы зачислений и выводов из базы данных
        $totalTrustDeposits = Invoice::query()
            ->where('status', InvoiceStatus::SUCCESS)
            ->where('type', InvoiceType::DEPOSIT)
            ->where('balance_type', BalanceType::TRUST)
            ->sum('amount');

        $totalTrustWithdrawals = Invoice::query()
            ->where('status', InvoiceStatus::SUCCESS)
            ->where('type', InvoiceType::WITHDRAWAL)
            ->where('balance_type', BalanceType::TRUST)
            ->sum('amount');

        $totalMerchantDeposits = Invoice::query()
            ->where('status', InvoiceStatus::SUCCESS)
            ->where('type', InvoiceType::DEPOSIT)
            ->where('balance_type', BalanceType::MERCHANT)
            ->sum('amount');

        $totalMerchantWithdrawals = Invoice::query()
            ->where('status', InvoiceStatus::SUCCESS)
            ->where('type', InvoiceType::WITHDRAWAL)
            ->where('balance_type', BalanceType::MERCHANT)
            ->sum('amount');

        $totals = [
            'trust_balance' => $totalTrustBalance->toBeauty(),
            'merchant_balance' => $totalMerchantBalance->toBeauty(),
            'total_balance' => $totalTrustBalance->add($totalMerchantBalance)->toBeauty(),
            'trust_deposits' => Money::fromUnits($totalTrustDeposits, Currency::USDT())->toBeauty(),
            'trust_withdrawals' => Money::fromUnits($totalTrustWithdrawals, Currency::USDT())->toBeauty(),
            'merchant_deposits' => Money::fromUnits($totalMerchantDeposits, Currency::USDT())->toBeauty(),
            'merchant_withdrawals' => Money::fromUnits($totalMerchantWithdrawals, Currency::USDT())->toBeauty(),
        ];

        return Inertia::render('UserBalance/Index', compact('users', 'filters', 'totals'));
    }
}
