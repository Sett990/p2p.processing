<?php

declare(strict_types=1);

namespace App\Services\Cascade;

use App\Contracts\CascadeProviderServiceContract;
use App\Models\CascadeProvider;
use App\Services\Cascade\Providers\CascadeProviderInterface;
use App\Services\Cascade\Providers\ExampleCascadeProvider;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

/**
 * Сервис работы с провайдерами каскада
 *
 * Предоставляет интерфейс для получения и работы с провайдерами ликвидности
 */
class CascadeProviderService implements CascadeProviderServiceContract
{
    /**
     * Кэш загруженных провайдеров
     *
     * @var array<string, CascadeProviderInterface>
     */
    private array $providersCache = [];

    /**
     * Получить провайдера по коду
     *
     * @param string $code Код провайдера
     * @return CascadeProviderInterface|null
     */
    public function getProvider(string $code): ?CascadeProviderInterface
    {
        if (isset($this->providersCache[$code])) {
            return $this->providersCache[$code];
        }

        $providerModel = CascadeProvider::where('code', $code)->first();

        if (!$providerModel) {
            return null;
        }

        return $this->getProviderByModel($providerModel);
    }

    /**
     * Получить провайдера по модели CascadeProvider
     *
     * @param CascadeProvider $provider Модель провайдера
     * @return CascadeProviderInterface|null
     */
    public function getProviderByModel(CascadeProvider $provider): ?CascadeProviderInterface
    {
        if (isset($this->providersCache[$provider->code])) {
            return $this->providersCache[$provider->code];
        }

        $providerInstance = $this->createProviderInstance($provider);

        if ($providerInstance) {
            $this->providersCache[$provider->code] = $providerInstance;
        }

        return $providerInstance;
    }

    /**
     * Получить все активные провайдеры
     *
     * @return array<string, CascadeProviderInterface>
     */
    public function getActiveProviders(): array
    {
        return CascadeProvider::where('is_active', true)
            ->get()
            ->mapWithKeys(function (CascadeProvider $provider) {
                $instance = $this->getProviderByModel($provider);
                return $instance ? [$provider->code => $instance] : [];
            })
            ->toArray();
    }

    /**
     * Получить все провайдеры (включая неактивные)
     *
     * @return array<string, CascadeProviderInterface>
     */
    public function getAllProviders(): array
    {
        return CascadeProvider::all()
            ->mapWithKeys(function (CascadeProvider $provider) {
                $instance = $this->getProviderByModel($provider);
                return $instance ? [$provider->code => $instance] : [];
            })
            ->toArray();
    }

    /**
     * Получить список всех доступных кодов провайдеров
     *
     * @return array<string> Массив кодов провайдеров
     */
    public function getAvailableProviderCodes(): array
    {
        // Получаем коды из базы данных (из модели CascadeProvider)
        // Это гарантирует, что мы возвращаем только те провайдеры, которые реально зарегистрированы
        return CascadeProvider::pluck('code')->toArray();
    }

    /**
     * Создать экземпляр провайдера на основе модели
     *
     * @param CascadeProvider $provider Модель провайдера
     * @return CascadeProviderInterface|null
     */
    private function createProviderInstance(CascadeProvider $provider): ?CascadeProviderInterface
    {
        // Маппинг кодов провайдеров на классы реализации
        $providerClasses = [
            'example' => ExampleCascadeProvider::class,
            // TODO: Добавить другие провайдеры по мере их реализации
            // 'internal' => InternalProvider::class,
            // 'external_provider_1' => ExternalProvider1::class,
        ];

        $providerClass = $providerClasses[$provider->code] ?? null;

        if (!$providerClass || !class_exists($providerClass)) {
            // Если класс не найден, используем ExampleCascadeProvider как заглушку
            // В продакшене здесь должна быть обработка ошибки или логирование
            $providerClass = ExampleCascadeProvider::class;
        }

        try {
            $config = $provider->config ?? [];
            return new $providerClass($provider->code, $config);
        } catch (\Throwable $e) {
            // Логируем ошибку создания провайдера
            Log::error('Failed to create cascade provider instance', [
                'code' => $provider->code,
                'error' => $e->getMessage(),
            ]);
            
            return null;
        }
    }
}
