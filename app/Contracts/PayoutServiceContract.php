<?php

namespace App\Contracts;

use App\DTO\Payout\PayoutCreateDTO;
use App\Exceptions\PayoutException;
use App\Models\Payout;
use App\Models\PayoutOffer;
use App\Models\User;

interface PayoutServiceContract
{
    /**
     * @throws PayoutException
     */
    public function createPayout(PayoutCreateDTO $dto): Payout;

    /**
     * @throws PayoutException
     */
    public function finishPayout(Payout $payout): Payout;

    /**
     * @throws PayoutException
     */
    public function cancelPayout(Payout $payout): Payout;

    /**
     * @throws PayoutException
     */
    public function getOffersMenu(): array;

    /**
     * @throws PayoutException
     */
    public function addOffer(User $user, array $data): PayoutOffer;

    /**
     * @throws PayoutException
     */
    public function updateOffer(PayoutOffer $payoutOffer, array $data): PayoutOffer;

    /**
     * @throws PayoutException
     */
    public function toggleTraderOffersActivity(User $user): void;
}
