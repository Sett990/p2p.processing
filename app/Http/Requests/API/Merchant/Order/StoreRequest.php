<?php

namespace App\Http\Requests\API\Merchant\Order;

use App\Enums\DetailType;
use App\Models\Merchant;
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
        $merchant_id = Merchant::where('uuid', $this->merchant_id)->first()?->id;

        return [
            'external_id' => [
                'required',
                Rule::unique('orders')->where(function ($query) use ($merchant_id) {
                    return $query->where('external_id', $this->external_id)
                        ->where('merchant_id', $merchant_id);
                }),
            ],
            'amount' => ['required', 'integer'],
            'callback_url' => ['nullable', 'string', 'url:https', 'max:256'],
            'success_url' => ['nullable', 'string', 'url:https', 'max:256'],
            'fail_url' => ['nullable', 'string', 'url:https', 'max:256'],
            'payment_gateway' => [
                'required_without:currency',
                'prohibits:currency,manually',
                'exists:payment_gateways,code'
            ],
            'sub_payment_gateway' => [
                'nullable',
                'prohibits:currency',
                'exists:payment_gateways,code'
            ],
            'currency' => [
                'required_without:payment_gateway',
                'required_if:manually,1',
                'prohibits:payment_gateway',
                Rule::in(Currency::getAllCodes())
            ],
            'payment_detail_type' => ['nullable', Rule::in(DetailType::values())],
            'merchant_id' => ['required', 'exists:merchants,uuid'],
            'manually' => [
                'nullable',
                'in:1',
                'prohibits:payment_gateway',
            ],
        ];
    }
}
