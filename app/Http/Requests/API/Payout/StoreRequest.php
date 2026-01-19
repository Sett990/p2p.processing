<?php

namespace App\Http\Requests\API\Payout;

use App\Enums\PayoutMethodType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class StoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $merchant = queries()->merchant()->findByUUID($this->merchant_id);

        return [
            'merchant_id' => ['required', 'exists:merchants,uuid'],
            'external_id' => [
                'required',
                'string',
                'max:255',
                function ($attribute, $value, $fail) use ($merchant) {
                    if (! $merchant) {
                        return;
                    }

                    $cacheKey = "payout_external_id_{$value}_merchant_{$merchant->id}";
                    $exists = Cache::get($cacheKey);

                    if ($exists === null) {
                        $exists = DB::table('payouts')
                            ->where('external_id', $value)
                            ->where('merchant_id', $merchant->id)
                            ->exists();

                        if ($exists) {
                            Cache::put($cacheKey, true, 3600);
                        }
                    }

                    if ($exists) {
                        $fail('Выплата с таким external_id уже существует для данного мерчанта.');
                        return;
                    }
                },
            ],
            'amount' => ['required', 'integer', 'gt:0'],
            'payout_method_type' => ['required', 'string', Rule::in(PayoutMethodType::values())],
            'payment_method_id' => [
                'required',
                'integer',
                Rule::exists('payment_gateways', 'id')
                    ->where('is_active', 1)
                    ->where('is_payouts_enabled', true),
            ],
            'requisites' => ['required', 'string', 'max:255'],
            'initials' => ['required', 'string', 'max:255'],
            'callback_url' => ['nullable', 'string', 'url:https', 'max:256'],
        ];
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('payout_method_type') && is_string($this->payout_method_type)) {
            $this->merge(['payout_method_type' => strtolower($this->payout_method_type)]);
        }
    }
}

