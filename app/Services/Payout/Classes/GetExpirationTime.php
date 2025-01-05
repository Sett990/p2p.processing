<?php

namespace App\Services\Payout\Classes;

class GetExpirationTime
{
    public function get()
    {
        return now()->addMinutes(1);
    }
}
