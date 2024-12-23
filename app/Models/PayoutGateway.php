<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property string $uuid
 * @property string $name
 * @property string $domain
 * @property string $callback_url
 * @property boolean $enabled
 * @property int $owner_id
 * @property User $owner
 * @property Carbon $created_at
 * @property Carbon $updated_at
 */
class PayoutGateway extends Model
{
    use HasFactory;

    protected $fillable = [
        'uuid',
        'name',
        'domain',
        'callback_url',
        'enabled',
        'owner_id',
    ];

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }
}
