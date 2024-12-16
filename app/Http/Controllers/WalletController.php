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

        $walletStats = services()->wallet()->getWalletStats($wallet)->toArray();

        $invoices = queries()->invoice()->paginate($wallet, $balanceType);
        $transactions = queries()->transaction()->paginate($wallet, $balanceType);

        $invoices = InvoiceResource::collection($invoices);
        $transactions = TransactionResource::collection($transactions);

        return Inertia::render('Wallet/Index', compact('walletStats', 'invoices', 'transactions'));
    }
}
