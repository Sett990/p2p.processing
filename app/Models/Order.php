<?php

namespace App\Models;

use App\Casts\BaseCurrencyMoneyCast;
use App\Casts\CurrencyCast;
use App\Casts\MoneyCast;
use App\Enums\Market;
use App\Enums\OrderStatus;
use App\Observers\OrderObserver;
use App\Services\Money\Currency;
use App\Services\Money\Money;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * @property int $id
 * @property string $uuid
 * @property string $external_id
 * @property Money $base_amount
 * @property Money $amount
 * @property Money $profit
 * @property Money $trader_profit
 * @property Money $merchant_profit
 * @property Money $service_profit
 * @property Currency $currency
 * @property Market $market
 * @property Money $base_conversion_price
 * @property Money $conversion_price
 * @property float $trader_commission_rate
 * @property float $service_commission_rate_total
 * @property float $service_commission_rate_merchant
 * @property float $service_commission_rate_client
 * @property OrderStatus $status
 * @property string $status_name
 * @property string $callback_url
 * @property string $success_url
 * @property string $fail_url
 * @property array $amount_updates_history
 * @property boolean $is_h2h
 * @property int $payment_gateway_id
 * @property int $payment_detail_id
 * @property int $merchant_id
 * @property PaymentGateway $paymentGateway
 * @property PaymentDetail $paymentDetail
 * @property Merchant $merchant
 * @property SmsLog $smsLog
 * @property Dispute $dispute
 * @property Carbon $finished_at
 * @property Carbon $expires_at
 * @property Carbon $created_at
 * @property Carbon $updated_at
 */
#[ObservedBy([OrderObserver::class])]
class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'uuid',
        'external_id',
        'base_amount',
        'amount',
        'profit',
        'trader_profit',
        'merchant_profit',
        'service_profit',
        'currency',
        'market',
        'base_conversion_price',
        'conversion_price',
        'trader_commission_rate',
        'service_commission_rate_total',
        'service_commission_rate_merchant',
        'service_commission_rate_client',
        'status',
        'callback_url',
        'success_url',
        'fail_url',
        'amount_updates_history',
        'is_h2h',
        'payment_gateway_id',
        'payment_detail_id',
        'merchant_id',
        'expires_at',
        'finished_at',
    ];

    protected $casts = [
        'status' => OrderStatus::class,
        'expires_at' => 'datetime',
        'finished_at' => 'datetime',
        'currency' => CurrencyCast::class,
        'market' => Market::class,
        'base_amount' => MoneyCast::class,
        'amount' => MoneyCast::class,
        'profit' => BaseCurrencyMoneyCast::class,
        'trader_profit' => BaseCurrencyMoneyCast::class,
        'merchant_profit' => BaseCurrencyMoneyCast::class,
        'service_profit' => BaseCurrencyMoneyCast::class,
        'base_conversion_price' => MoneyCast::class,
        'conversion_price' => MoneyCast::class,
        'amount_updates_history' => 'array',
    ];

    protected static function booted()
    {
        static::addGlobalScope(function (Builder $builder) {
            $builder->whereNotNull('payment_detail_id');
        });
    }

    protected function statusName(): Attribute
    {
        return Attribute::make(
            get: fn (mixed $value, array $attributes) => trans("order.status.{$attributes['status']}"),
        );
    }

    public function paymentGateway(): BelongsTo
    {
        return $this->belongsTo(PaymentGateway::class);
    }

    public function paymentDetail(): BelongsTo
    {
        return $this->belongsTo(PaymentDetail::class);
    }

    public function smsLog(): HasOne
    {
        return $this->hasOne(SmsLog::class);
    }

    public function dispute(): HasOne
    {
        return $this->hasOne(Dispute::class);
    }

    public function merchant(): BelongsTo
    {
        return $this->belongsTo(Merchant::class);
    }
}
