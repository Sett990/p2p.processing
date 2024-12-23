<?php

namespace App\Contracts;

use App\DTO\Payout\PayoutCreateDTO;
use App\Models\Payout;

interface PayoutServiceContract
{
    public function create(PayoutCreateDTO $dto): Payout;

    public function getOffersMenu(): array;

    public function makeOffersMenu(): void;
}
