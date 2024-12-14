<?php

namespace App\Models;

use App\Casts\CurrencyCast;
use App\Casts\MoneyCast;
use App\Enums\BalanceType;
use App\Enums\InvoiceStatus;
use App\Enums\InvoiceType;
use App\Services\Money\Currency;
use App\Services\Money\Money;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property Money $amount
 * @property Currency $currency
 * @property string $address
 * @property InvoiceType $type
 * @property BalanceType $balance_type
 * @property InvoiceStatus $status
 * @property int $wallet_id
 * @property Wallet $wallet
 * @property Carbon $created_at
 * @property Carbon $updated_at
 */
class Invoice extends Model
{
    use HasFactory;

    protected $fillable = [
        'amount',
        'currency',
        'address',
        'type',
        'balance_type',
        'status',
        'wallet_id',
    ];

    protected $casts = [
        'amount' => MoneyCast::class,
        'currency' => CurrencyCast::class,
        'type' => InvoiceType::class,
        'balance_type' => BalanceType::class,
        'status' => InvoiceStatus::class,
    ];

    public function wallet(): BelongsTo
    {
        return $this->belongsTo(Wallet::class);
    }
}
