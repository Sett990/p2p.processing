<?php

namespace App\Http\Requests\API\Payout;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

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
        return [
            'external_id' => [
                'required',
                Rule::unique('payouts')->where(function ($query) {
                    return $query->where('external_id', $this->external_id)
                        ->where('payout_gateway_id', $this->payout_gateway_id);
                }),
            ],
            'detail' => ['required', 'string', 'min:3', 'max:30'], //TODO validation
            'detail_type' => ['required', 'in:card,phone'],
            'detail_initials' => ['required', 'string', 'min:3', 'max:30'],
            'amount' => ['required', 'integer', 'min:1'],
            'service_commission_rate',
            'service_commission_amount',
            'trader_profit_amount',
            'trader_exchange_markup_rate',
            'trader_exchange_markup_amount',
            'base_exchange_price',
            'exchange_price',
            'status',
            'sub_status',
            'callback_url',
            'payout_offer_id',
            'payout_gateway_id',
            'trader_id',
            'merchant_id',
            'callback_url' => ['nullable', 'string', 'url:https', 'max:256'],
        ];
    }
}
