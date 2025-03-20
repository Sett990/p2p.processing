<?php

namespace App\Providers;

use Illuminate\Support\Facades\Gate;
use Laravel\Telescope\IncomingEntry;
use Laravel\Telescope\Telescope;
use Laravel\Telescope\TelescopeApplicationServiceProvider;

class TelescopeServiceProvider extends TelescopeApplicationServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        Telescope::night();

        $this->hideSensitiveRequestDetails();

        $isLocal = $this->app->environment('local');
        $isDevelopment = $this->app->environment('development');
        $isProduction = $this->app->environment('production');

        // Получаем текущий HTTP-запрос
        $request = request();

        // Проверяем, является ли это запросом на конкретный эндпоинт
        $isSpecificEndpoint = $request && str_starts_with($request->path(), 'api/h2h/order');

        Telescope::filter(function (IncomingEntry $entry) {


            return false;
        });

        Telescope::filter(function (IncomingEntry $entry) use ($isLocal, $isSpecificEndpoint, $isDevelopment, $isProduction) {
            // Проверяем, что это HTTP-запрос
            if ($entry->type === 'request' && $isSpecificEndpoint && $isProduction) {
                $content = $entry->content;

                // Определяем время выполнения запроса
                $duration = $content['duration'] ?? 0;

                // Логируем только запросы дольше 500 мс
                return $duration > 500;
            }

            return $isLocal ||
                   $isDevelopment ||
                   $entry->isSlowQuery() ||
                   $entry->isReportableException() ||
                   $entry->isFailedRequest() ||
                   $entry->isFailedJob();
        });
    }

    /**
     * Prevent sensitive request details from being logged by Telescope.
     */
    protected function hideSensitiveRequestDetails(): void
    {
        if ($this->app->environment('local')) {
            return;
        }

        Telescope::hideRequestParameters(['_token']);

        Telescope::hideRequestHeaders([
            'cookie',
            'x-csrf-token',
            'x-xsrf-token',
        ]);
    }

    /**
     * Register the Telescope gate.
     *
     * This gate determines who can access Telescope in non-local environments.
     */
    protected function gate(): void
    {
        Gate::define('viewTelescope', function ($user) {
            return $user->hasRole('Super Admin');
        });
    }
}
