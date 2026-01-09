<?php

namespace App\Http\Requests\Payout;

use App\Enums\PayoutMethodType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $callbackUrlRules = ['nullable', 'string', 'max:256'];

        if (app()->environment('production')) {
            $callbackUrlRules[] = 'url:https';
        } else {
            $callbackUrlRules[] = 'url:http,https';
        }

        return [
            'merchant_id' => ['required', 'integer', 'exists:merchants,id'],
            'amount' => ['required', 'integer', 'gt:0'],
            'payout_method_type' => ['required', 'string', Rule::in(PayoutMethodType::values())],
            'payment_method_id' => [
                'required',
                'integer',
                Rule::exists('payment_gateways', 'id')
                    ->where('is_active', 1)
                    ->where('is_payouts_enabled', true),
            ],
            'requisites' => [
                'required',
                'string',
                'max:255',
                function (string $attribute, mixed $value, \Closure $fail) {
                    $type = strtolower((string) $this->input('payout_method_type'));
                    $digits = preg_replace('/\s+/', '', (string) $value);

                    if ($type === PayoutMethodType::SBP->value) {
                        if (! preg_match('/^(\+7|7|8)\d{10}$/', $digits)) {
                            $fail('Укажите российский номер в формате +7XXXXXXXXXX.');
                        }
                        return;
                    }

                    if ($type === PayoutMethodType::CARD->value) {
                        if (! preg_match('/^\d{16}$/', $digits)) {
                            $fail('Укажите номер карты из 16 цифр без пробелов.');
                        }
                    }
                },
            ],
            'initials' => ['required', 'string', 'max:255'],
            'callback_url' => $callbackUrlRules,
        ];
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('payout_method_type') && is_string($this->payout_method_type)) {
            $this->merge(['payout_method_type' => strtolower($this->payout_method_type)]);
        }
    }
}

