<?php

namespace App\Contracts;

use App\DTO\Payout\PayoutCreateDTO;
use App\Exceptions\PayoutException;
use App\Models\Payout;

interface PayoutServiceContract
{
    /**
     * @throws PayoutException
     */
    public function create(PayoutCreateDTO $dto): Payout;

    public function getOffersMenu(): array;

    public function makeOffersMenu(): void;
}
