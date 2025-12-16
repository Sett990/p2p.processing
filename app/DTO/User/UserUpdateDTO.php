<?php

namespace App\DTO\User;

use App\DTO\BaseDTO;

readonly class UserUpdateDTO extends BaseDTO
{
    public function __construct(
        public string $login,
        public ?bool $banned,
        public bool $payouts_enabled,
        public bool $stop_traffic,
        public bool $is_vip,
        public ?int $referral_commission_percentage,
        public ?int $reserve_balance_limit,
        public int $role_id,
        public ?string $promo_code = null,
    ) {}

    public static function makeFromRequest(array $data): static
    {
        return new static(
            login: strtolower($data['login']),
            banned: isset($data['banned']) ? (bool) $data['banned'] : null,
            payouts_enabled: (bool) ($data['payouts_enabled'] ?? false),
            stop_traffic: (bool) ($data['stop_traffic'] ?? false),
            is_vip: (bool) ($data['is_vip'] ?? false),
            referral_commission_percentage: isset($data['referral_commission_percentage']) ? (int) $data['referral_commission_percentage'] : null,
            reserve_balance_limit: isset($data['reserve_balance_limit']) ? (int) $data['reserve_balance_limit'] : null,
            role_id: (int) $data['role_id'],
            promo_code: $data['promo_code'] ?? null,
        );
    }
}


