<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Observers\UserObserver;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Lab404\Impersonate\Models\Impersonate;
use Spatie\Permission\Traits\HasRoles;

/**
 * @property int $id
 * @property string $name
 * @property string $email
 * @property string $apk_access_token
 * @property string $api_access_token
 * @property Collection<int, PaymentDetail> $paymentDetails
 * @property Collection<int, Order> $orders
 * @property Collection<int, Dispute> $disputes
 * @property Collection<int, SmsLog> $smsLogs
 * @property Collection<int, UserLoginHistory> $loginHistories
 * @property Wallet $wallet
 * @property Telegram $telegram
 * @property UserMeta $meta
 * @property boolean $is_online
 * @property boolean $is_payout_online
 * @property boolean $payouts_enabled
 * @property string $avatar_uuid
 * @property string $avatar_style
 * @property string $google2fa_secret
 * @property Carbon $banned_at
 * @property Carbon $created_at
 * @property Carbon $updated_At
 */
#[ObservedBy([UserObserver::class])]
class User extends Authenticatable
{
    use HasFactory, Notifiable, HasRoles, Impersonate;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'apk_access_token',
        'api_access_token',
        'is_online',
        'is_payout_online',
        'payouts_enabled',
        'avatar_uuid',
        'avatar_style',
        'google2fa_secret',
        'banned_at',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'google2fa_secret',
        'apk_access_token',
        'api_access_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'banned_at' => 'datetime',
        ];
    }

    protected function google2faSecret(): Attribute
    {
        return new Attribute(
            get: fn ($value) =>  $value ? decrypt($value) : null,
            set: fn ($value) =>  $value ? encrypt($value) : null,
        );
    }

    public function canImpersonate()
    {
        return $this->hasRole('Super Admin');
    }

    public function canBeImpersonated()
    {
        return !$this->hasRole('Super Admin');
    }

    public function paymentDetails(): HasMany
    {
        return $this->hasMany(PaymentDetail::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class, 'trader_id');
    }

    public function disputes(): HasMany
    {
        return $this->hasMany(Dispute::class, 'trader_id');
    }

    public function smsLogs(): HasMany
    {
        return $this->hasMany(SmsLog::class);
    }

    public function wallet(): HasOne
    {
        return $this->hasOne(Wallet::class);
    }

    public function telegram(): HasOne
    {
        return $this->hasOne(Telegram::class);
    }

    public function meta(): HasOne
    {
        return $this->hasOne(UserMeta::class);
    }

    /**
     * Get the login histories for the user.
     */
    public function loginHistories(): HasMany
    {
        return $this->hasMany(UserLoginHistory::class);
    }
}
