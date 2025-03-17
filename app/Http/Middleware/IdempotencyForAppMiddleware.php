<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Square1\LaravelIdempotency\Http\Middleware\IdempotencyMiddleware;
use Symfony\Component\HttpFoundation\Response;

class IdempotencyForAppMiddleware extends IdempotencyMiddleware
{
    /**
     * Resolve the user ID from a config value if it's not a closure.
     *
     * @param  mixed  $userIdResolver
     * @return mixed
     */
    protected function resolveUserIdFromConfig($userIdResolver)
    {
        $token = request()->header('Access-Token');

        $deviceID = $token ? services()->device()->get($token)?->id : null;

        if (is_array($userIdResolver) && count($userIdResolver) === 2) {
            // Assuming the configuration is in the format [ClassName::class, 'methodName']
            [$class, $method] = $userIdResolver;

            return app($class)->$method();
        }

        return 'device-'.$deviceID ?? 'global';
    }
}
