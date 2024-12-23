<?php

namespace App\Models;

use App\Casts\BaseCurrencyMoneyCast;
use App\Casts\CurrencyCast;
use App\Casts\MoneyCast;
use App\Enums\DetailType;
use App\Enums\PayoutStatus;
use App\Enums\PayoutSubStatus;
use App\Services\Money\Currency;
use App\Services\Money\Money;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property string $uuid
 * @property string $external_id
 * @property string $detail
 * @property DetailType $detail_type
 * @property string $detail_initials
 * @property Money $payout_amount
 * @property Currency $currency
 * @property Money $liquidity_amount
 * @property float $service_commission_rate
 * @property Money $service_commission_amount
 * @property Money $trader_profit_amount
 * @property float $trader_exchange_markup_rate
 * @property Money $trader_exchange_markup_amount
 * @property Money $base_exchange_price
 * @property Money $exchange_price
 * @property PayoutStatus $status
 * @property PayoutSubStatus $sub_status
 * @property string $callback_url
 * @property int $payout_offer_id
 * @property int $payout_gateway_id
 * @property int $trader_id
 * @property int $owner_id
 * @property PayoutOffer $payoutOffer
 * @property PaymentGateway $payoutGateway
 * @property User $trader
 * @property User $owner
 * @property Carbon $finished_at
 * @property Carbon $expires_at
 * @property Carbon $created_at
 * @property Carbon $updated_at
 */
class Payout extends Model
{
    use HasFactory;

    protected $fillable = [
        'uuid',
        'external_id',
        'detail',
        'detail_type',
        'detail_initials',
        'payout_amount',
        'currency',
        'liquidity_amount',
        'service_commission_rate',
        'service_commission_amount',
        'trader_profit_amount',
        'trader_exchange_markup_rate',
        'trader_exchange_markup_amount',
        'base_exchange_price',
        'exchange_price',
        'status',
        'sub_status',
        'callback_url',
        'payout_offer_id',
        'payout_gateway_id',
        'trader_id',
        'owner_id',
        'finished_at',
        'expires_at',
    ];

    protected $casts = [
        'status' => PayoutStatus::class,
        'sub_status' => PayoutSubStatus::class,
        'expires_at' => 'datetime',
        'finished_at' => 'datetime',
        'currency' => CurrencyCast::class,
        'payout_amount' => MoneyCast::class,
        'liquidity_amount' => BaseCurrencyMoneyCast::class,
        'service_commission_amount' => BaseCurrencyMoneyCast::class,
        'trader_profit_amount' => BaseCurrencyMoneyCast::class,
        'trader_exchange_markup_amount' => BaseCurrencyMoneyCast::class,
        'base_exchange_price' => MoneyCast::class,
        'exchange_price' => MoneyCast::class,
    ];

    public function payoutOffer(): BelongsTo
    {
        return $this->belongsTo(PayoutOffer::class);
    }

    public function payoutGateway(): BelongsTo
    {
        return $this->belongsTo(PaymentGateway::class);
    }

    public function trader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'trader_id');
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }
}
