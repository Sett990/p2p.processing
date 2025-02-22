<?php

namespace App\Models;

use App\Casts\CurrencyCast;
use App\Casts\MoneyCast;
use App\Enums\DetailType;
use App\Enums\MarketEnum;
use App\Services\Money\Currency;
use App\Services\Money\Money;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property string $name
 * @property string $detail
 * @property DetailType $detail_type
 * @property string $initials
 * @property boolean $is_active
 * @property Money $daily_limit
 * @property Money $current_daily_limit
 * @property Money $max_pending_orders_quantity
 * @property Currency $currency
 * @property int $payment_gateway_id
 * @property int $sub_payment_gateway_id
 * @property int $user_id
 * @property User $user
 * @property PaymentGateway $paymentGateway
 * @property PaymentGateway $subPaymentGateway
 * @property Collection<int, Order> $orders
 * @property Carbon $last_used_at
 * @property Carbon $created_at
 * @property Carbon $updated_at
 */
class PaymentDetail extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'detail',
        'detail_type',
        'initials',
        'is_active',
        'daily_limit',
        'current_daily_limit',
        'max_pending_orders_quantity',
        'currency',
        'payment_gateway_id',
        'sub_payment_gateway_id',
        'user_id',
        'last_used_at'
    ];

    protected $casts = [
        'daily_limit' => MoneyCast::class,
        'current_daily_limit' => MoneyCast::class,
        'currency' => CurrencyCast::class,
        'detail_type' => DetailType::class,
        'last_used_at' => 'datetime',
    ];

    public function paymentGateway(): BelongsTo
    {
        return $this->belongsTo(PaymentGateway::class);
    }

    public function subPaymentGateway(): BelongsTo
    {
        return $this->belongsTo(PaymentGateway::class, 'sub_payment_gateway_id');
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function scopeActive(Builder $query): void
    {
        $query->where('is_active', true);
    }
}
