<?php

namespace App\Contracts;

use App\DTO\Payout\PayoutCreateDTO;
use App\Exceptions\PayoutException;
use App\Models\Payout\Payout;

interface PayoutServiceContract
{
    /**
     * @throws PayoutException
     */
    public function create(PayoutCreateDTO $data): Payout;

    /**
     * @throws PayoutException
     */
    public function cancel(Payout $payout): Payout;
}

