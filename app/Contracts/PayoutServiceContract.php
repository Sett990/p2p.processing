<?php

namespace App\Contracts;

use App\DTO\Payout\PayoutCreateDTO;
use App\Exceptions\PayoutException;
use App\Models\Payout\Payout;
use App\Models\User;

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

    /**
     * @throws PayoutException
     */
    public function take(Payout $payout, User $trader): Payout;

    /**
     * @throws PayoutException
     */
    public function markSent(Payout $payout, User $trader): Payout;

}

