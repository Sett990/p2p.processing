<?php

namespace App\DTO\SMS;

use App\DTO\BaseDTO;
use App\Enums\SmsType;
use App\Models\User;

readonly class SmsDTO extends BaseDTO
{
    public function __construct(
        public string $sender,
        public string $message,
        public int $timestamp,
        public SmsType $type,
        public User $user
    )
    {}

    public static function fromArray(array $data): self
    {
        $data['type'] = SmsType::from($data['type']);
        return new self(...$data);
    }
}
