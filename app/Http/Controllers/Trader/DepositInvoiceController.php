<?php

namespace App\Http\Controllers\Trader;

use App\Enums\BalanceType;
use App\Exceptions\InvoiceException;
use App\Http\Controllers\Controller;
use App\Services\Money\Currency;
use App\Services\Money\Money;
use Illuminate\Http\Request;

class DepositInvoiceController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'amount' => ['required', 'numeric', 'min:1'],
        ]);

        try {
            $result = services()->invoice()->createExternalDeposit(
                walletID: auth()->user()->wallet->id,
                amount: Money::fromPrecision($validated['amount'], Currency::USDT()),
                balanceType: BalanceType::TRUST,
            );

            $external = $result['external'] ?? [];
            return response()->json([
                'payment_url' => $external['payment_url'] ?? null,
                'external_invoice_id' => $external['id'] ?? null,
                'invoice_id' => $result['invoice']->id ?? null,
            ]);
        } catch (InvoiceException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }
}


