<?php

namespace App\Http\Requests\API\H2H\Order;

use App\Enums\DetailType;
use App\Services\Money\Currency;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class StoreRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $merchant_id = $this->merchant_id ? queries()->merchant()->findByUUID($this->merchant_id)?->id : null;

        return [
            'external_id' => [
                'required',
                function ($attribute, $value, $fail) use ($merchant_id) {
                    // Проверяем пендинг заказы в кэше
                    $pendingKey = "pending_order_external_id_{$value}_merchant_{$merchant_id}";
                    if (Cache::has($pendingKey)) {
                        $fail('Заказ с таким external_id уже в процессе создания для данного мерчанта.');
                        return;
                    }
                    
                    // Проверяем существование в БД
                    $cacheKey = "order_external_id_{$value}_merchant_{$merchant_id}";
                    $exists = Cache::remember($cacheKey, 60, function () use ($value, $merchant_id) {
                        return DB::table('orders')
                            ->where('external_id', $value)
                            ->where('merchant_id', $merchant_id)
                            ->exists();
                    });

                    if ($exists) {
                        $fail('Заказ с таким external_id уже существует для данного мерчанта.');
                        return;
                    }
                    
                    // Помечаем, что заказ в процессе создания (час - достаточно для обработки очереди)
                    Cache::put($pendingKey, true, 60 * 60);
                },
                'max:255',
            ],
            'amount' => ['required', 'integer', 'min:1'],
            'callback_url' => ['nullable', 'string', 'url:https', 'max:256'],
            'payment_gateway' => [
                'required_without:currency',
                'prohibits:currency',
                function ($attribute, $value, $fail) {
                    $cacheKey = "payment_gateway_exists_{$value}";

                    $exists = Cache::remember($cacheKey, 3600, function () use ($value) {
                        return DB::table('payment_gateways')
                            ->where('code', $value)
                            ->exists();
                    });

                    if (!$exists) {
                        $fail('Выбранный платежный шлюз не существует.');
                    }
                }
            ],
            'currency' => [
                'required_without:payment_gateway',
                'prohibits:payment_gateway',
                Rule::in(Currency::getAllCodes())
            ],
            'payment_detail_type' => ['nullable', Rule::in(DetailType::values())],
            'merchant_id' => [
                'required',
                function ($attribute, $value, $fail) {
                    $cacheKey = "merchant_exists_{$value}";

                    $exists = Cache::remember($cacheKey, 3600, function () use ($value) {
                        return DB::table('merchants')
                            ->where('uuid', $value)
                            ->exists();
                    });

                    if (!$exists) {
                        $fail('Выбранный мерчант не существует.');
                    }
                }
            ],
        ];
    }
}
