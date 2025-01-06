<?php

namespace App\Services\Payout\Utils;

use App\Enums\BalanceType;
use App\Enums\PayoutStatus;
use App\Enums\PayoutSubStatus;
use App\Enums\TransactionType;
use App\Exceptions\PayoutException;
use App\Jobs\AutoRefusePayoutJob;
use App\Jobs\SendTelegramNotificationJob;
use App\Models\PaymentGateway;
use App\Models\Payout;
use App\Models\PayoutOffer;
use App\Models\User;
use App\Services\Payout\Classes\GetExpirationTime;
use App\Services\Payout\Classes\PickPayoutOffer;
use App\Services\TelegramBot\Notifications\NewPayout;
use App\Services\TelegramBot\Notifications\NewPayoutRefuse;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

class PayoutOperator
{
    public function finishPayoutByAdmin(Payout $payout): Payout
    {
        $payout->update([
            'status' => PayoutStatus::SUCCESS,
            'sub_status' => PayoutSubStatus::FULLY_COMPLETED,
            'finished_at' => now()
        ]);

        if ($payout->fundsOnHold) { //TODO
            services()->fundsHolder()->changeDestination(
                fundsOnHold: $payout->fundsOnHold,
                destinationWallet: auth()->user()->wallet,
                destinationWalletBalanceType: BalanceType::TRUST
            );
            services()->fundsHolder()->setTimer($payout->fundsOnHold, now());
            services()->fundsHolder()->execute($payout->fundsOnHold);
        }

        return $payout;
    }

    public function finishPayout(Payout $payout, ?UploadedFile $videoReceipt = null): Payout
    {
        $receiptName = null;
        if ($videoReceipt) {
            $receiptName = 'video_receipt_'.strtolower(Str::random(32)).'.'.$videoReceipt->extension();
            $videoReceipt->move(storage_path('video_receipts'), $receiptName);
        }

        $payout->trader->update([
            'is_payout_online' => false,
        ]);

        $payout->update([
            'status' => PayoutStatus::SUCCESS,
            'sub_status' => PayoutSubStatus::FULLY_COMPLETED,
            'video_receipt' => $receiptName,
            'finished_at' => now()
        ]);

        $payout->payoutOffer->owner_id;

        PayoutOffer::query()
            ->where('owner_id', $payout->payoutOffer->owner_id)
            ->update([
                'occupied' => false,
            ]);

        if ($payout->fundsOnHold) {
            $holdMinutes = services()->settings()->getFundsOnHoldTime();
            services()->fundsHolder()->setTimer($payout->fundsOnHold, now()->addMinutes($holdMinutes));
        }

        return $payout;
    }

    public function refusePayout(Payout $payout, string $reason): Payout
    {
        PayoutOffer::query()
            ->where('owner_id', $payout->payoutOffer->owner_id)
            ->update([
                'occupied' => false,
            ]);

        $payout->trader->update([
            'is_payout_online' => false,
        ]);

        $payout->update([
            'sub_status' => PayoutSubStatus::PROCESSING_BY_ADMINISTRATOR,
            'trader_id' => null,
            'payout_offer_id' => null,
            'refuse_reason' => $reason,
            'previous_trader_id' => $payout->trader_id,
        ]);

        $admitUsers = User::role('Super Admin')->with('telegram')->get(); //TODO refactoring
        $admitUsers->each(function ($user) use ($payout) {
            if ($user->telegram) {
                SendTelegramNotificationJob::dispatch(
                    new NewPayoutRefuse(
                        telegram: $payout->trader->telegram,
                        payout: $payout
                    )
                );
            }
        });

        if ($payout->fundsOnHold) {
            services()->fundsHolder()->changeDestination(
                fundsOnHold: $payout->fundsOnHold,
                destinationWallet: null,
                destinationWalletBalanceType: null
            );
        }

        return $payout;
    }

    public function cancelPayout(Payout $payout, ?string $reason = null): Payout
    {
        $payout->update([
            'status' => PayoutStatus::FAIL,
            'sub_status' => PayoutSubStatus::FULLY_COMPLETED,
            'cancel_reason' => $reason,
            'finished_at' => now(),
        ]);

        $payout->owner->wallet->giveToMerchant(
            amount: $payout->liquidity_amount,
            type: TransactionType::REFUND_FOR_CANCELED_PAYOUT
        );

        if ($payout->fundsOnHold) {
            services()->fundsHolder()->cancel($payout->fundsOnHold);
        }

        return $payout;
    }

    public function passToTrader(Payout $payout): Payout
    {
        $payoutOffer = (new PickPayoutOffer())
            ->pick($payout->payout_amount, $payout->detail_type, $payout->paymentGateway, $payout->previousTrader);

        if (! $payoutOffer) {
            throw PayoutException::freeTraderNotFound();
        }

        $expires_at = $this->getExpirationTime($payout->paymentGateway);

        $payout->update([
            'sub_status' => PayoutSubStatus::PROCESSING_BY_TRADER,
            'trader_id' => $payoutOffer->owner->id,
            'payout_offer_id' => $payoutOffer->id,
            'refuse_reason' => null,
            'previous_trader_id' => null,
            'expires_at' => $expires_at,
        ]);

        if ($payout->fundsOnHold) {
            services()->fundsHolder()->changeDestination(
                fundsOnHold: $payout->fundsOnHold,
                destinationWallet: $payoutOffer->owner->wallet,
                destinationWalletBalanceType: BalanceType::TRUST
            );
        }

        AutoRefusePayoutJob::dispatch($payout, $payoutOffer->owner)->delay($expires_at);

        return $payout;
    }

    protected function getExpirationTime(PaymentGateway $paymentGateway): Carbon
    {
        return (new GetExpirationTime($paymentGateway))->get();
    }
}
