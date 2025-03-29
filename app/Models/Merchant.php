<?php

namespace App\Models;

use App\Enums\MarketEnum;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property string $uuid
 * @property string $name
 * @property string $description
 * @property string $domain
 * @property string $callback_url
 * @property boolean $active
 * @property int $user_id
 * @property User $user
 * @property Collection<int, Order> $orders
 * @property Collection<int, Category> $categories
 * @property array $settings
 * @property array $gateway_settings
 * @property MarketEnum $market
 * @property Carbon $validated_at
 * @property Carbon $banned_at
 * @property Carbon $created_at
 * @property Carbon $updated_at
 */
class Merchant extends Model
{
    use HasFactory;

    protected $fillable = [
        'uuid',
        'name',
        'description',
        'domain',
        'callback_url',
        'token',
        'user_id',
        'active',
        'settings',
        'gateway_settings',
        'market',
        'validated_at',
        'banned_at',
    ];

    protected $casts = [
        'settings' => 'array',
        'gateway_settings' => 'array',
        'validated_at' => 'datetime',
        'banned_at' => 'datetime',
        'market' => MarketEnum::class,
    ];

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
    
    /**
     * Получить категории, к которым принадлежит мерчант.
     */
    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(Category::class);
    }
}
