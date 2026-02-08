<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\ProviderType;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Настройки провайдера каскада
 *
 * Хранит конфигурацию и настройки провайдеров ликвидности (внутренних и внешних).
 * Используется для управления включением/выключением провайдеров, распределения трафика,
 * приоритизации и хранения конфигурации (API ключи, URL и т.д.).
 *
 * @property int $id
 * @property string $code Уникальный код провайдера (например, 'internal', 'external_provider_1')
 * @property string $name Название провайдера для отображения
 * @property ProviderType $provider_type Тип провайдера (internal/external)
 * @property bool $is_active Включен ли провайдер (попадает ли в обработчик)
 * @property float|null $weight Вес для распределения трафика (процент от 0 до 100)
 * @property int|null $priority Порядок приоритета (чем меньше число, тем выше приоритет)
 * @property array|null $config Конфигурация провайдера (API ключи, URL, endpoints и т.д.)
 * @property string|null $description Описание провайдера
 * @property Carbon $created_at
 * @property Carbon $updated_at
 */
class CascadeProvider extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'name',
        'provider_type',
        'is_active',
        'weight',
        'priority',
        'config',
        'description',
    ];

    protected $casts = [
        'provider_type' => ProviderType::class,
        'is_active' => 'boolean',
        'weight' => 'float',
        'priority' => 'integer',
        'config' => 'array',
    ];
}
