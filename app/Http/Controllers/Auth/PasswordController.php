<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Session;
use Illuminate\Validation\Rules\Password;

class PasswordController extends Controller
{
    /**
     * Update the user's password.
     */
    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            //'current_password' => ['required', 'current_password'],
            'password' => ['required', Password::defaults(), 'confirmed'],
        ]);

        $request->user()->update([
            'password' => Hash::make($validated['password']),
        ]);

       $this->deleteOtherSessions();

        return back();
    }

    /**
     * Удаляет все сессии пользователя, кроме текущей
     */
    protected function deleteOtherSessions()
    {
        // Получаем ID текущей сессии
        $currentSessionId = Session::getId();

        // Получаем ID пользователя
        $userId = auth()->id();

        if (!$userId) {
            return; // Пользователь не аутентифицирован
        }

        // Определяем драйвер сессий
        $sessionDriver = config('session.driver');

        if ($sessionDriver === 'database') {
            // Для драйвера database
            DB::table('sessions')
                ->where('user_id', $userId)
                ->where('id', '!=', $currentSessionId)
                ->delete();
        } elseif ($sessionDriver === 'redis') {
            // Для драйвера redis
            $prefix = config('session.prefix', '');
            $sessionKeyPattern = $prefix . '*';

            // Получаем Redis соединение
            $redis = Redis::connection(config('session.connection'));

            // Получаем все ключи сессий
            $sessionKeys = $redis->keys($sessionKeyPattern);

            foreach ($sessionKeys as $sessionKey) {
                // Пропускаем текущую сессию
                if (str_contains($sessionKey, $currentSessionId)) {
                    continue;
                }

                // Получаем данные сессии
                $sessionData = $redis->get($sessionKey);

                if ($sessionData) {
                    // Десериализуем данные сессии
                    $sessionData = unserialize($sessionData);

                    // Проверяем, принадлежит ли сессия текущему пользователю
                    if (isset($sessionData['auth']) &&
                        isset($sessionData['auth']['user_id']) &&
                        $sessionData['auth']['user_id'] == $userId) {
                        // Удаляем сессию
                        $redis->del($sessionKey);
                    }
                }
            }
        }
    }
}
