<?php

namespace App\Services\Payout\Utils;

use App\Enums\PayoutStatus;
use App\Enums\PayoutSubStatus;
use App\Enums\TransactionType;
use App\Models\Payout;
use App\Models\PayoutOffer;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;

class PayoutOperator
{
    public function finishPayout(Payout $payout, UploadedFile $videoReceipt): Payout
    {
        $receipt_name = 'video_receipt_'.strtolower(Str::random(32)).'.'.$videoReceipt->extension();
        $videoReceipt->move(storage_path('video_receipts'), $receipt_name);

        $payout->update([
            'status' => PayoutStatus::SUCCESS,
            'video_receipt' => $receipt_name
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

    public function refusePayout(Payout $payout, string $reason): Payout
    {
        $payout->trader->update([
            'is_payout_online' => false,
        ]);

        $payout->update([
            'sub_status' => PayoutSubStatus::PROCESSING_BY_ADMINISTRATOR,
            'trader_id' => null,
            'refuse_reason' => $reason,
            'previous_trader_id' => $payout->trader_id,
            'was_refused' => true,
        ]);

        PayoutOffer::query()
            ->where('owner_id', $payout->payoutOffer->owner_id)
            ->update([
                'occupied' => false,
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
