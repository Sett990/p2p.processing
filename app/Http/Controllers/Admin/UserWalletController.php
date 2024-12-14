<?php

namespace App\Http\Controllers\Admin;

use App\Enums\BalanceType;
use App\Enums\InvoiceStatus;
use App\Enums\OrderStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\User\Wallet\DepositRequest;
use App\Http\Requests\Admin\User\Wallet\WithdrawRequest;
use App\Http\Resources\InvoiceResource;
use App\Http\Resources\TransactionResource;
use App\Http\Resources\UserResource;
use App\Http\Resources\WalletResource;
use App\Models\Invoice;
use App\Models\Order;
use App\Models\Transaction;
use App\Models\User;
use App\Services\Money\Currency;
use App\Services\Money\Money;
use Inertia\Inertia;

class UserWalletController extends Controller
{
    public function index(User $user)
    {
        $wallet = $user->wallet;
        $invoices = Invoice::query()
            ->with('wallet.user')
            ->where('wallet_id', $wallet->id)
            ->orderByDesc('id')
            ->paginate(10);
        $transactions = Transaction::query()
            ->where('wallet_id', $wallet->id)
            ->orderByDesc('id')
            ->paginate(10);

        $total_trust_withdrawable_amount = intval($wallet->trust_balance->add($wallet->reserve_balance)->toBeauty());
        $total_merchant_withdrawable_amount = intval($wallet->merchant_balance->toBeauty());

        $trust_locked_for_withdrawal = Invoice::query()
            ->where('wallet_id', $wallet->id)
            ->where('status', InvoiceStatus::PENDING)
            ->where('balance_type', BalanceType::TRUST)
            ->sum('amount');
        $trust_locked_for_withdrawal = Money::fromUnits($trust_locked_for_withdrawal, Currency::USDT())->toBeauty();

        $merchant_locked_for_withdrawal = Invoice::query()
            ->where('wallet_id', $wallet->id)
            ->where('status', InvoiceStatus::PENDING)
            ->where('balance_type', BalanceType::MERCHANT)
            ->sum('amount');
        $merchant_locked_for_withdrawal = Money::fromUnits($merchant_locked_for_withdrawal, Currency::USDT())->toBeauty();

        $escrowOrders = Order::query()
            ->where('status', OrderStatus::PENDING)
            ->whereRelation('paymentDetail', 'user_id', $wallet->user_id)
            ->whereDoesntHave('dispute')
            ->get();
        $escrow_balance = Money::fromUnits(0, Currency::USDT());
        $escrow_balance_rub = Money::fromUnits(0, Currency::RUB());
        foreach ($escrowOrders as $order) {
            /**
             * @var Order $order
             */
            $escrow_balance = $escrow_balance->add($order->profit);
            $escrow_balance_rub = $escrow_balance_rub->add(
                $order->profit->mul($order->conversion_price)->toPrecision()
            );
        }
        $escrow_balance = $escrow_balance->toBeauty();
        $escrow_balance_rub = $escrow_balance_rub->toBeauty();
        $orders_count = $escrowOrders->count();

        $disputeOrders = Order::query()
            ->where('status', OrderStatus::PENDING)
            ->whereRelation('paymentDetail', 'user_id', $wallet->user_id)
            ->whereHas('dispute')
            ->get();
        $dispute_balance = Money::fromUnits(0, Currency::USDT());
        $dispute_balance_rub = Money::fromUnits(0, Currency::RUB());
        foreach ($disputeOrders as $order) {
            /**
             * @var Order $order
             */
            $dispute_balance = $dispute_balance->add($order->profit);
            $dispute_balance_rub = $dispute_balance_rub->add(
                $order->profit->mul($order->conversion_price)->toPrecision()
            );
        }
        $dispute_balance = $dispute_balance->toBeauty();
        $dispute_balance_rub = $dispute_balance_rub->toBeauty();
        $disputes_count = $disputeOrders->count();

        $wallet = WalletResource::make($wallet)->resolve();
        $invoices = InvoiceResource::collection($invoices);
        $transactions = TransactionResource::collection($transactions);
        $user = UserResource::make($user)->resolve();

        $reserve_balance = services()->wallet()->getMaxReserveBalance();

        return Inertia::render('Wallet/Index', compact('wallet', 'reserve_balance', 'invoices', 'transactions', 'user', 'total_trust_withdrawable_amount', 'total_merchant_withdrawable_amount', 'trust_locked_for_withdrawal', 'merchant_locked_for_withdrawal', 'escrow_balance', 'escrow_balance_rub', 'dispute_balance', 'dispute_balance_rub', 'orders_count', 'disputes_count'));
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
