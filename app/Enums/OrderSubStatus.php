<?php

namespace App\Enums;

use App\Traits\Enumable;

enum OrderSubStatus: string
{
    use Enumable;

    case SUCCESSFULLY_PAID = 'successfully_paid';
    case SUCCESSFULLY_PAID_BY_RESOLVED_DISPUTE = 'successfully_paid_by_resolved_dispute';
    case WAITING_FOR_PAYMENT = 'waiting_for_payment';
    case WAITING_FOR_DISPUTE_TO_BE_RESOLVED = 'waiting_for_dispute_to_be_resolved';
    case CANCELED_BY_DISPUTE = 'canceled_by_dispute';
    case EXPIRED = 'expired';
}
