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
    public function create(PayoutCreateDTO $dto): Payout;

    /**
     * @throws PayoutException
     */
    public function getOffersMenu(): array;

    /**
     * @throws PayoutException
     */
    public function updateOffersMenu(): void;

    /**
     * @throws PayoutException
     */
    public function addOffer(User $user, array $data): PayoutOffer;

    /**
     * @throws PayoutException
     */
    public function updateOffer(PayoutOffer $payoutOffer, array $data): PayoutOffer;
}
