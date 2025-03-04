<?php

namespace App\Models;

use App\Enums\DetailType;
use App\Services\Money\Currency;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property string|null $external_id
 * @property string|null $amount
 * @property string|null $currency
 * @property string|null $payment_gateway
 * @property string|null $payment_detail_type
 * @property array|null $request_data
 * @property array|null $response_data
 * @property string|null $ip_address
 * @property string|null $user_agent
 * @property bool $is_successful
 * @property string|null $error_message
 * @property int $merchant_id
 * @property int|null $order_id
 * @property Merchant $merchant
 * @property Order|null $order
 * @property Carbon $created_at
 * @property Carbon $updated_at
 */
class MerchantApiRequestLog extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'external_id',
        'amount',
        'currency',
        'payment_gateway',
        'payment_detail_type',
        'request_data',
        'response_data',
        'ip_address',
        'user_agent',
        'is_successful',
        'error_message',
        'merchant_id',
        'order_id',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'request_data' => 'array',
        'response_data' => 'array',
        'is_successful' => 'boolean',
    ];

    public function merchant(): BelongsTo
    {
        return $this->belongsTo(Merchant::class);
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }
}
