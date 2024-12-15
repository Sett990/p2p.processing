<?php

namespace App\Http\Controllers;

use App\Enums\BalanceType;
use App\Http\Resources\InvoiceResource;
use App\Http\Resources\TransactionResource;
use App\Http\Resources\WalletResource;
use App\Models\Transaction;
use App\Models\Wallet;
use Illuminate\Http\Request;
use Inertia\Inertia;

class WalletController extends Controller
{
    public function index(Request $request)
    {
        if ($request->route()->action['as'] === 'wallet.index') {
            $balanceType = BalanceType::TRUST;
        } else if ($request->route()->action['as'] === 'merchant.finances.index') {
            $balanceType = BalanceType::MERCHANT;
        }

        /**
         * @var Wallet $wallet
         */
        $wallet = $request->user()->wallet;
        $invoices = queries()->invoice()->paginate($wallet, $balanceType);
        $transactions = Transaction::query()
            ->where('wallet_id', $wallet->id)
            ->orderByDesc('id')
            ->paginate(10);

        $walletStats = services()->wallet()->getWalletStats($wallet);

        $wallet = WalletResource::make($wallet)->resolve();
        $invoices = InvoiceResource::collection($invoices);
        $transactions = TransactionResource::collection($transactions);

        $reserve_balance = services()->wallet()->getMaxReserveBalance();

        return Inertia::render('Wallet/Index', compact('wallet', 'reserve_balance', 'invoices', 'transactions', 'walletStats'));
    }
}
