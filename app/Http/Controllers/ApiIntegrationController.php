<?php

namespace App\Http\Controllers;

use App\Models\Merchant;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Inertia\Inertia;

class ApiIntegrationController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $token = $user->api_access_token;
        
        // Получаем первый мерчант пользователя
        $merchant = Merchant::where('user_id', $user->id)->first();
        $merchantId = $merchant?->uuid;

        return Inertia::render('Integration/Index', compact('token', 'merchantId'));
    }

    /**
     * Проксирование запросов к API для тестирования
     */
    public function proxy(Request $request): JsonResponse
    {
        $user = auth()->user();
        $token = $user->api_access_token;
        
        $method = strtoupper($request->input('method', 'GET'));
        $endpoint = $request->input('endpoint');
        $data = $request->input('data', []);
        $headers = $request->input('headers', []);

        if (!$endpoint) {
            return response()->json([
                'success' => false,
                'message' => 'Endpoint не указан'
            ], 400);
        }

        // Формируем URL API
        $apiUrl = config('app.url') . '/api/' . ltrim($endpoint, '/');
        
        // Фильтруем пустые значения из данных
        $data = array_filter($data, function($value) {
            return $value !== '' && $value !== null;
        });
        
        // Добавляем обязательные заголовки
        $requestHeaders = array_merge([
            'Accept' => 'application/json',
            'Access-Token' => $token,
        ], array_filter($headers, function($value) {
            return $value !== '' && $value !== null;
        }));

        try {
            // Выполняем запрос к API
            $response = Http::withHeaders($requestHeaders);
            
            if ($method === 'GET') {
                $response = $response->get($apiUrl, $data);
            } elseif ($method === 'POST') {
                $response = $response->post($apiUrl, $data);
            } elseif ($method === 'PATCH') {
                $response = $response->patch($apiUrl, $data);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Неподдерживаемый HTTP метод'
                ], 400);
            }

            return response()->json([
                'success' => $response->successful(),
                'status' => $response->status(),
                'data' => $response->json() ?? $response->body(),
                'headers' => $response->headers(),
            ], $response->status());
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Ошибка при выполнении запроса: ' . $e->getMessage(),
                'status' => 500,
            ], 500);
        }
    }
}
