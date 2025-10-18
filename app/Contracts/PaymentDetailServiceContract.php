<?php

namespace App\Contracts;

use App\DTO\PaymentDetail\PaymentDetailCreateDTO;
use App\DTO\PaymentDetail\PaymentDetailUpdateDTO;
use App\Models\PaymentDetail;

interface PaymentDetailServiceContract
{
    public function create(PaymentDetailCreateDTO $data): PaymentDetail;

    public function update(PaymentDetailUpdateDTO $data, PaymentDetail $paymentDetail): PaymentDetail;

    public function toggleActive(PaymentDetail $paymentDetail): void;
}


