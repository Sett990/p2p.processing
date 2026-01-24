<?php

namespace App\Exceptions;

class AntiFraudException extends OrderException
{
    public static function clientIdRequired(): self
    {
        return new self('Не указан client_id для антифрод-проверки.');
    }

    public static function blockedUntil(string $until): self
    {
        return new self("Клиент заблокирован до {$until}.");
    }

    public static function maxPendingExceeded(): self
    {
        return new self('Превышен лимит активных сделок для клиента.');
    }

    public static function rateLimitExceeded(int $count, int $minutes): self
    {
        return new self("Превышен лимит: {$count} сделок за {$minutes} минут.");
    }

    public static function failedLimitExceeded(): self
    {
        return new self('Превышен лимит подряд неуспешных сделок.');
    }
}
