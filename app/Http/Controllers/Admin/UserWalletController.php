<?php

namespace App\Http\Controllers\Admin;

use App\Enums\BalanceType;
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
        $wallet = $user->wallet;

        $walletStats = services()->wallet()->getWalletStats($wallet)->toArray();

        $invoices = queries()->invoice()->paginate($wallet);
        $transactions = queries()->transaction()->paginate($wallet);

        $invoices = InvoiceResource::collection($invoices);
        $transactions = TransactionResource::collection($transactions);

        $user = UserResource::make($user)->resolve();

        return Inertia::render('Wallet/Index', compact('walletStats', 'invoices', 'transactions', 'user'));
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
