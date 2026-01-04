<?php

namespace App\Exceptions;

class PayoutException extends BaseException
{
    public static function gatewayInactive(): self
    {
        return new self('Выбранный банк недоступен.');
    }

    public static function gatewayPayoutsDisabled(): self
    {
        return new self('Для выбранного банка выплаты отключены.');
    }

    public static function merchantWalletMissing(): self
    {
        return new self('Для мерчанта не найден кошелёк.');
    }

    public static function insufficientMerchantFunds(): self
    {
        return new self('Недостаточно средств для резервирования выплаты.');
    }

    public static function marketPriceUnavailable(): self
    {
        return new self('Не удалось получить актуальный курс конвертации.');
    }

    public static function payoutNotOpen(): self
    {
        return new self('Выплату нельзя отменить в текущем статусе.');
    }

    public static function payoutAlreadyTaken(): self
    {
        return new self('Выплату нельзя отменить: она уже взята трейдером.');
    }
}

