<?php

namespace App\Services\Payout\Utils;

use App\Enums\PayoutStatus;
use App\Enums\TransactionType;
use App\Models\Payout;
use App\Models\PayoutOffer;

class PayoutOperator
{
    public function passToAdmin(Payout $payout)
    {
        //TODO
    }

    public function finishPayout(Payout $payout): Payout
    {
        $payout->update([
            'status' => PayoutStatus::SUCCESS
        ]);

        $payout->payoutOffer->owner_id;

        PayoutOffer::query()
            ->where('owner_id', $payout->payoutOffer->owner_id)
            ->update([
                'occupied' => false,
            ]);

        $payout->payoutOffer->update([
            'active' => false,
        ]);

        return $payout;
    }

    public function cancelPayout(Payout $payout): Payout
    {
        $payout->update([
            'status' => PayoutStatus::FAIL
        ]);

        PayoutOffer::query()
            ->where('owner_id', $payout->payoutOffer->owner_id)
            ->update([
                'occupied' => false,
            ]);

        $payout->owner->wallet->giveToMerchant(
            amount: $payout->liquidity_amount,
            type: TransactionType::REFUND_FOR_CANCELED_PAYOUT
        );

        return $payout;
    }
}
