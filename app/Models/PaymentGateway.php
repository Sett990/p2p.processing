<?php

namespace App\Models;

use App\Casts\CurrencyCast;
use App\Enums\DetailType;
use App\Services\Money\Currency;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * @property int $id
 * @property string $name
 * @property string $code
 * @property string $nspk_schema
 * @property string $name_with_currency
 * @property string $min_limit
 * @property string $max_limit
 * @property array $sms_senders
 * @property float $trader_commission_rate_for_orders
 * @property float $trader_commission_rate_for_payouts
 * @property float $total_service_commission_rate_for_orders
 * @property float $total_service_commission_rate_for_payouts
 * @property string $is_active
 * @property int $reservation_time_for_orders
 * @property int $reservation_time_for_payouts
 * @property string $logo
 * @property array<int, DetailType> $detail_types
 * @property Collection<int, PaymentGateway> $sub_payment_gateways
 * @property boolean $is_sbp
 * @property Currency $currency
 * @property Collection<int, PaymentDetail> $paymentDetails
 * @property Collection<int, Order> $orders
 */
class PaymentGateway extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'name',
        'code',
        'nspk_schema',
        'currency',
        'min_limit',
        'max_limit',
        'sms_senders',
        'trader_commission_rate_for_orders',
        'trader_commission_rate_for_payouts',
        'total_service_commission_rate_for_orders',
        'total_service_commission_rate_for_payouts',
        'is_active',
        'reservation_time_for_orders',
        'reservation_time_for_payouts',
        'logo',
        'detail_types',
        'sub_payment_gateways',
    ];

    protected $casts = [
        'currency' => CurrencyCast::class,
        'detail_types' => 'array',
        'sub_payment_gateways' => 'array',
        'sms_senders' => 'array',
    ];

    public $timestamps = false;

    protected function detailTypes(): Attribute
    {
        return Attribute::make(
            get: function (string $value)  {
                $detail_types = json_decode($value, true);

                foreach ($detail_types as $key => $item) {
                    $detail_types[$key] = DetailType::from($item);
                }

                return $detail_types;
            },
        );
    }

    protected function subPaymentGateways(): Attribute
    {
        return Attribute::make(
            get: function (string $value)  {
                $value = json_decode($value, true);

                if (empty($value)) {
                    return null;
                }

                return PaymentGateway::whereIn('id', $value)->get();
            },
        );
    }

    protected function firstName(): Attribute
    {
        return Attribute::make(
            get: fn (string $value) => ucfirst($value),
        );
    }

    protected function nameWithCurrency(): Attribute
    {
        return Attribute::make(
            get: fn (mixed $value, array $attributes) => $attributes['name'] . ' ' . strtoupper($attributes['currency']),
        );
    }

    protected function isSBP(): Attribute
    {
        return Attribute::make(
            get: function ()  {
                return $this->code === 'sbp_rub';
            },
        );
    }

    public function paymentDetails(): BelongsToMany
    {
        return $this->belongsToMany(PaymentDetail::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function scopeActive(Builder $query): void
    {
        $query->where('is_active', 1);
    }
}
