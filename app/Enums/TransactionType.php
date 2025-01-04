<?php

namespace App\Enums;

use App\Traits\Enumable;

enum TransactionType: string
{
    use Enumable;

    //out
    case PAYMENT_FOR_OPENED_PAYOUT = 'payment_for_opened_payout';
    case PAYMENT_FOR_OPENED_ORDER = 'payment_for_opened_order';
    case PAYMENT_FOR_OPENED_DISPUTE = 'payment_for_opened_dispute';
    case WITHDRAWAL_BY_ADMIN = 'withdrawal_by_admin';
    case WITHDRAWAL_BY_USER = 'withdrawal_by_user';
    case ROLLBACK_INCOME_FROM_A_SUCCESSFUL_ORDER = 'rollback_income_from_a_successful_order';

    //in
    case REFUND_FOR_CANCELED_PAYOUT = 'refund_for_canceled_payout';
    case REFUND_FOR_CANCELED_ORDER = 'refund_for_canceled_order';
    case REFUND_FOR_CANCELED_DISPUTE = 'refund_for_canceled_dispute';
    case DEPOSIT_BY_ADMIN = 'deposit_by_admin';
    case DEPOSIT_BY_USER = 'deposit_by_user';
    case ROLLBACK_FOR_USER_WITHDRAWAL = 'rollback_for_user_withdrawal';
    case INCOME_FROM_A_SUCCESSFUL_ORDER = 'income_from_a_successful_order';
    case INCOME_FROM_A_SUCCESSFUL_PAYOUT = 'income_from_a_successful_payout';

    public function direction(): TransactionDirection
    {
        return match ($this)
        {
            static::PAYMENT_FOR_OPENED_ORDER,
            static::PAYMENT_FOR_OPENED_DISPUTE,
            static::WITHDRAWAL_BY_ADMIN,
            static::WITHDRAWAL_BY_USER => TransactionDirection::OUT,
            static::REFUND_FOR_CANCELED_ORDER,
            static::REFUND_FOR_CANCELED_DISPUTE,
            static::DEPOSIT_BY_ADMIN,
            static::DEPOSIT_BY_USER,
            static::ROLLBACK_FOR_USER_WITHDRAWAL => TransactionDirection::IN,
        };
    }
}
