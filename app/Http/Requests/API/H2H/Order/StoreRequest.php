<?php

namespace App\Http\Requests\API\H2H\Order;

use App\Enums\DetailType;
use App\Services\Money\Currency;
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
        $merchant_id = $this->merchant_id ? queries()->merchant()->findByUUID($this->merchant_id)->id : null;

        return [
            'external_id' => [
                'required',
                Rule::unique('orders')->where(function ($query) use ($merchant_id) {
                    return $query->where('external_id', $this->external_id)
                        ->where('merchant_id', $merchant_id);
                }),
                'max:255',
            ],
            'amount' => ['required', 'integer', 'min:1'],
            'callback_url' => ['nullable', 'string', 'url:https', 'max:256'],
            'payment_gateway' => [
                'required_without:currency',
                'prohibits:currency',
                'exists:payment_gateways,code'
            ],
            'sub_payment_gateway' => [
                'nullable',
                'prohibits:currency',
                'exists:payment_gateways,code'
            ],
            'currency' => [
                'required_without:payment_gateway',
                'prohibits:payment_gateway',
                Rule::in(Currency::getAllCodes())
            ],
            'payment_detail_type' => ['nullable', Rule::in(DetailType::values())],
            'merchant_id' => ['required', 'exists:merchants,uuid'],
        ];
    }
}
