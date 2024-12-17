<?php

namespace App\Http\Controllers\Admin;

use App\Enums\BalanceType;
use App\Enums\InvoiceType;
use App\Enums\TransactionDirection;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\User\Wallet\DepositRequest;
use App\Http\Requests\Admin\User\Wallet\WithdrawRequest;
use App\Http\Resources\InvoiceResource;
use App\Http\Resources\TransactionResource;
use App\Http\Resources\UserResource;
use App\Models\User;
use App\Services\Money\Currency;
use App\Services\Money\Money;
use Inertia\Inertia;

class UserWalletController extends Controller
{
    public function index(User $user)
    {
        $tabs = [
            'invoices' => [
                'key' => 'invoices',
                'name' => 'Инвойсы',
            ],
            'transactions' => [
                'key' => 'transactions',
                'name' => 'Транзакции',
            ]
        ];

        $filters = [
            'invoices' => [
                'invoiceTypes' => [
                    'all' => [
                        'key' => 'all',
                        'name' => 'Тип инвойса',
                    ],
                    InvoiceType::DEPOSIT->value => [
                        'key' => InvoiceType::DEPOSIT->value,
                        'name' => 'Пополнение',
                    ],
                    InvoiceType::WITHDRAWAL->value => [
                        'key' => InvoiceType::WITHDRAWAL->value,
                        'name' => 'Вывод',
                    ],
                ],
                'balanceTypes' => [
                    'all' => [
                        'key' => 'all',
                        'name' => 'Тип кошелька',
                    ],
                    BalanceType::TRUST->value => [
                        'key' => BalanceType::TRUST->value,
                        'name' => 'Траст',
                    ],
                    BalanceType::MERCHANT->value => [
                        'key' => BalanceType::MERCHANT->value,
                        'name' => 'Мерчант',
                    ],
                ],
            ]
        ];
        $balanceTypes = BalanceType::values();
        $transactionDirections = TransactionDirection::values();

        $tab = request()->input('tab', 'invoices');
        if (! in_array($tab, $tabs)) {
            $tab = 'invoices';
        }
        $currentFilters = [
            'invoices' => [
                'invoiceTypes' => request()->input('currentFilters.invoices.invoiceTypes', 'all'),
                'balanceTypes' => request()->input('currentFilters.invoices.balanceTypes', 'all'),
            ],
        ];

        $wallet = $user->wallet;

        $walletStats = services()->wallet()->getWalletStats($wallet)->toArray();

        $invoices = null;
        $transactions = null;

        if ($tab === 'invoices') {
            $invoices = queries()->invoice()->paginate(
                wallet: $wallet,
                invoiceType: InvoiceType::tryFrom($currentFilters['invoices']['invoiceTypes']),
                balanceType: BalanceType::tryFrom($currentFilters['invoices']['balanceTypes']),
            );
            $invoices = InvoiceResource::collection($invoices);
        } else if ($tab === 'transactions') {
            $transactions = queries()->transaction()->paginate($wallet);
            $transactions = TransactionResource::collection($transactions);
        }

        $user = UserResource::make($user)->resolve();

        return Inertia::render('Wallet/Index', compact('walletStats', 'invoices', 'transactions', 'user', 'tab', 'currentFilters', 'tabs', 'filters', 'balanceTypes', 'transactionDirections'));
    }

    public function deposit(DepositRequest $request, User $user)
    {
        services()->invoice()->deposit(
            wallet: $user->wallet,
            amount: Money::fromPrecision($request->amount, Currency::USDT()),
            balanceType: BalanceType::from($request->balance_type)
        );
    }

    public function withdraw(WithdrawRequest $request, User $user)
    {
        services()->invoice()->withdraw(
            wallet: $user->wallet,
            amount: Money::fromPrecision($request->amount, Currency::USDT()),
            balanceType: BalanceType::from($request->balance_type)
        );
    }
}
