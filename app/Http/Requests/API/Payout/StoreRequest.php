<?php

namespace App\Http\Requests\API\Payout;

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
        return [
            'merchant_id' => ['required', 'exists:merchants,uuid'],
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
            'initials' => ['nullable', 'string', 'max:255'],
        ];
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('payout_method_type') && is_string($this->payout_method_type)) {
            $this->merge(['payout_method_type' => strtolower($this->payout_method_type)]);
        }
    }
}

