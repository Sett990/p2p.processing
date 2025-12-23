<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Enums\OrderStatus;
use App\Observers\UserObserver;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
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
 * @property string $login
 * @property string $apk_access_token
 * @property string $api_access_token
 * @property Collection<int, PaymentDetail> $paymentDetails
 * @property Collection<int, Order> $orders
 * @property Collection<int, Order> $teamLeaderOrders
 * @property Collection<int, Dispute> $disputes
 * @property Collection<int, SmsLog> $smsLogs
 * @property Collection<int, UserLoginHistory> $loginHistories
 * @property Collection<int, UserDevice> $devices
 * @property Collection<int, UserNote> $notes
 * @property Collection<int, Merchant> $merchants Мерчанты (магазины), к которым имеет доступ саппорт
 * @property Wallet $wallet
 * @property Telegram $telegram
 * @property UserMeta $meta
 * @property User $merchant
 * @property boolean $is_online
 * @property boolean $is_payout_online
 * @property boolean $is_vip
 * @property Carbon|null $temp_vip_active_until
 * @property bool $temp_vip_can_activate
 * @property Carbon|null $temp_vip_progress_start_at
 * @property boolean $payouts_enabled
 * @property boolean $stop_traffic
 * @property boolean $can_work_without_device
 * @property int|null $reserve_balance_limit
 * @property float $referral_commission_percentage
 * @property Carbon $traffic_enabled_at
 * @property string $avatar_uuid
 * @property string $avatar_style
 * @property string $google2fa_secret
 * @property int|null $promo_code_id
 * @property Carbon|null $promo_used_at
 * @property PromoCode|null $promoCode
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
        // Колонка email используется как login
        'email',
        'password',
        'apk_access_token',
        'api_access_token',
        'is_online',
        'is_payout_online',
        'is_vip',
        'temp_vip_active_until',
        'temp_vip_can_activate',
        'temp_vip_progress_start_at',
        'payouts_enabled',
        'stop_traffic',
        'can_work_without_device',
        'reserve_balance_limit',
        'referral_commission_percentage',
        'traffic_enabled_at',
        'avatar_uuid',
        'avatar_style',
        'google2fa_secret',
        'banned_at',
        'promo_code_id',
        'promo_used_at',
        'merchant_id',
        'payout_hold_enabled',
        'payout_hold_minutes',
        'payout_max_active_payouts',
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
            'promo_used_at' => 'datetime',
            'traffic_enabled_at' => 'datetime',
            'temp_vip_active_until' => 'datetime',
            'temp_vip_progress_start_at' => 'datetime',
            'temp_vip_can_activate' => 'boolean',
            'can_work_without_device' => 'boolean',
            'payout_hold_enabled' => 'boolean',
            'payout_hold_minutes' => 'integer',
            'payout_max_active_payouts' => 'integer',
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

    public function teamLeaderOrders(): HasMany
    {
        return $this->hasMany(Order::class, 'team_trader_id');
    }

    public function disputes(): HasMany
    {
        return $this->hasMany(Dispute::class, 'trader_id');
    }

    public function smsLogs(): HasMany
    {
        return $this->hasMany(SmsLog::class);
    }

    public function devices(): HasMany
    {
        return $this->hasMany(UserDevice::class);
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
     * Get the promo code that was used by this user.
     */
    public function promoCode(): BelongsTo
    {
        return $this->belongsTo(PromoCode::class);
    }

    /**
     * Get the notes for the user.
     */
    public function notes(): HasMany
    {
        return $this->hasMany(UserNote::class);
    }

    /**
     * Get the login histories for the user.
     */
    public function loginHistories(): HasMany
    {
        return $this->hasMany(UserLoginHistory::class);
    }

    /**
     * Получить мерчанта, к которому привязан саппорт
     */
    public function merchant(): BelongsTo
    {
        return $this->belongsTo(User::class, 'merchant_id');
    }

    /**
     * Получить саппортов мерчанта
     */
    public function supports(): HasMany
    {
        return $this->hasMany(User::class, 'merchant_id');
    }

    /**
     * Получить мерчанты (магазины), к которым имеет доступ саппорт
     */
    public function merchants(): BelongsToMany
    {
        return $this->belongsToMany(Merchant::class, 'merchant_supports', 'support_id', 'merchant_id')
            ->withTimestamps();
    }

    public function tempVipActivations(): HasMany
    {
        return $this->hasMany(UserTempVipActivation::class);
    }

    /**
     * Рассчитать данные прогресса временного VIP.
     */
    public function getTempVipProgressData(): array
    {
        if (! services()->settings()->isTempVipEnabled()) {
            return [
                'enabled' => false,
                'active' => false,
                'active_until' => null,
                'required' => 0,
                'count' => 0,
                'remaining' => 0,
                'can_activate' => false,
                'start_from' => null,
            ];
        }

        if ($this->is_vip) {
            return [
                'enabled' => false,
                'active' => false,
                'active_until' => null,
                'required' => 0,
                'count' => 0,
                'remaining' => 0,
                'can_activate' => false,
                'start_from' => null,
            ];
        }

        $activeUntil = $this->temp_vip_active_until;
        $isActive = $activeUntil && now()->lt($activeUntil);

        $lastActivationEnd = $this->tempVipActivations()
            ->latest('expires_at')
            ->value('expires_at');

        $startAt = $this->temp_vip_progress_start_at
            ?? ($lastActivationEnd ? Carbon::parse($lastActivationEnd) : $this->created_at);

        $required = services()->settings()->getTempVipRequiredDeals();

        $count = Order::query()
            ->where('status', OrderStatus::SUCCESS)
            ->whereRelation('paymentDetail', 'user_id', $this->id)
            ->when($startAt, function ($query) use ($startAt) {
                $query->where('created_at', '>=', $startAt);
            })
            ->count();

        $remaining = max($required - $count, 0);

        return [
            'enabled' => true,
            'active' => $isActive,
            'active_until' => $activeUntil?->toIso8601String(),
            'required' => $required,
            'count' => $count,
            'remaining' => $remaining,
            'can_activate' => ! $isActive && $count >= $required,
            'start_from' => $startAt?->toIso8601String(),
        ];
    }
}
