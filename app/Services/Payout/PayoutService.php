<?php

namespace App\Services\Payout;

use App\Contracts\PayoutServiceContract;
use App\DTO\Payout\PayoutCreateDTO;
use App\Models\Payout;
use App\Models\PayoutOffer;
use App\Models\User;
use App\Services\Payout\Utils\OfferMaker;
use App\Services\Payout\Utils\OffersMenu;
use App\Services\Payout\Utils\PayoutMaker;
use App\Services\Payout\Utils\PayoutOperator;
use Illuminate\Support\Facades\DB;

class PayoutService implements PayoutServiceContract
{
    public function createPayout(PayoutCreateDTO $dto): Payout
    {
        return $this->lock(function () use ($dto) {
            return (new PayoutMaker())->create($dto);
        });
    }

    public function finishPayout(Payout $payout): Payout
    {
        return $this->lock(function () use ($payout) {
            return (new PayoutOperator())->finishPayout($payout);
        });
    }

    public function cancelPayout(Payout $payout): Payout
    {
        return $this->lock(function () use ($payout) {
            return (new PayoutOperator())->cancelPayout($payout);
        });
    }

    public function getOffersMenu(): array
    {
        return $this->lock(function () {
            return OffersMenu::getMenu();
        });
    }

    public function addOffer(User $user, array $data): PayoutOffer
    {
        return $this->lock(function () use ($user, $data) {
            return (new OfferMaker())->add($user, $data);
        });
    }

    public function updateOffer(PayoutOffer $payoutOffer, array $data): PayoutOffer
    {
        return $this->lock(function () use ($payoutOffer, $data) {
            return (new OfferMaker())->update($payoutOffer, $data);
        });
    }

    protected function lock(callable $callback): mixed
    {
       return cache()->lock('payout-lock', 5)
            ->block(8, function () use ($callback) {
                return DB::transaction(function () use ($callback) {
                    $result = $callback();
                    $this->updateOffersMenu();

                    return $result;
                });
            });
    }

    protected function updateOffersMenu(): void
    {
        OffersMenu::updateMenu();
    }
}
