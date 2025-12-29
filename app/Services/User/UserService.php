<?php

namespace App\Services\User;

use App\Contracts\UserServiceContract;
use App\DTO\User\UserCreateDTO;
use App\DTO\User\UserUpdateDTO;
use App\Models\PromoCode;
use App\Models\User;
use App\Utils\Transaction;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;

class UserService implements UserServiceContract
{
    public function create(UserCreateDTO $data): User
    {
        return $this->transaction(function () use ($data) {
            $promoCodeId = null;
            $promoUsedAt = null;

            if ($data->promo_code) {
                $promoCode = PromoCode::where('code', $data->promo_code)->first();
                if ($promoCode && $promoCode->canBeUsed()) {
                    $promoCodeId = $promoCode->id;
                    $promoUsedAt = now();
                }
            }

            $roleName = Role::find($data->role_id)?->name;

            $user = User::create([
                'name' => '',
                'email' => strtolower($data->login),
                'password' => Hash::make($data->password),
                'apk_access_token' => strtolower(Str::random(32)),
                'api_access_token' => strtolower(Str::random(32)),
                'avatar_uuid' => $data->login,
                'avatar_style' => 'adventurer',
                'promo_code_id' => $promoCodeId,
                'promo_used_at' => $promoUsedAt,
                'traffic_enabled_at' => now(),
                'reserve_balance_limit' => services()->settings()->getDefaultReserveBalanceLimit(),
            ]);

            $user->assignRole($data->role_id);

            services()->wallet()->create($user);

            if (isset($promoCode) && $promoCodeId) {
                $promoCode->incrementUsedCount();
            }

            return $user;
        });
    }

    public function update(UserUpdateDTO $data, User $user): User
    {
        return $this->transaction(function () use ($data, $user) {
            $wasTrafficStopped = $user->stop_traffic;

            $user->update([
                'email' => strtolower($data->login),
                'banned_at' => $data->banned ? now() : null,
                'stop_traffic' => $data->stop_traffic,
                'can_work_without_device' => $data->can_work_without_device,
                'is_vip' => $data->is_vip,
                'referral_commission_percentage' => $data->referral_commission_percentage,
                'reserve_balance_limit' => $data->reserve_balance_limit,
                'traffic_enabled_at' => $wasTrafficStopped && ! $data->stop_traffic ? now() : $user->traffic_enabled_at,
            ]);

            if (! $user->promo_code_id && $data->promo_code) {
                $promoCode = PromoCode::where('code', $data->promo_code)->first();
                if ($promoCode && $promoCode->canBeUsed()) {
                    $user->update([
                        'promo_code_id' => $promoCode->id,
                        'promo_used_at' => now(),
                    ]);
                    $promoCode->incrementUsedCount();
                }
            }

            if ($user->id !== 1) {
                $user->syncRoles($data->role_id);
            }

            if ($user->banned_at) {
                $user->paymentDetails()->update([
                    'is_active' => false,
                ]);
            }

            return $user;
        });
    }

    protected function transaction(callable $callback): mixed
    {
        return Transaction::run(function () use ($callback) {
            return $callback();
        });
    }
}


