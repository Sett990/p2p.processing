<?php

namespace App\DTO\User;

use App\DTO\BaseDTO;

readonly class UserCreateDTO extends BaseDTO
{
    public function __construct(
        public string $login,
        public string $password,
        public int $role_id,
        public ?string $promo_code = null,
    ) {}

    public static function makeFromRequest(array $data): static
    {
        return new static(
            login: strtolower($data['login']),
            password: $data['password'],
            role_id: (int) $data['role_id'],
            promo_code: $data['promo_code'] ?? null,
        );
    }
}


