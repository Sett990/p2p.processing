<?php

namespace App\Http\Requests\Admin\PaymentGateway;

use App\Enums\DetailType;
use App\Models\PaymentGateway;
use App\Services\Money\Currency;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateRequest extends FormRequest
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
        $paymentGateway = $this->route('payment_gateway');

        return [
            'name' => ['required', 'string', 'min:3', 'max:30'],
            'code' => ['required', 'string', 'min:3', 'max:30', Rule::unique(PaymentGateway::class)->ignore($paymentGateway->id)],
            'currency' => ['required', Rule::in(Currency::getAllCodes())],
            'detail_types' => ['required', 'array'],
            'detail_types.*' => ['nullable', Rule::in(DetailType::values())],
            'sub_payment_gateways' => ['nullable', 'array'],
            'sub_payment_gateways.*' => ['required', 'exists:payment_gateways,id'],
            'min_limit' => ['required', 'integer', 'min:1'],
            'max_limit' => ['required', 'integer', 'min:1'],
            'buy_price_markup_rate' => ['required', 'numeric', 'min:0'],
            'sell_price_markup_rate' => ['required', 'numeric', 'min:0'],
            'order_service_commission_rate' => ['required', 'numeric', 'min:0'],
            'payout_service_commission_rate' => ['required', 'numeric', 'min:0'],
            'is_active' => ['required', 'boolean'],
            'reservation_time' => ['required', 'integer', 'min:1'],
            'payout_reservation_time' => ['required', 'integer', 'min:1'],
            'sms_senders' => ['nullable', 'array'],
            'sms_senders.*' => ['required', 'string'],
            'logo' => ['nullable', 'image', 'mimes:png', 'max:2048', Rule::dimensions()->ratio(1.0)],
        ];
    }

    public function attributes()
    {
        return [
            'name' => __('название'),
            'code' => __('код метода'),
            'currency' => __('валюта'),
            'detail_types' => __('тип реквизитов'),
            'sub_payment_gateways' => __('вспомогательный метод'),
            'max_limit' => __('макс лимит'),
            'buy_price_markup_rate' => __('комиссия трейдера'),
            'sell_price_markup_rate' => __('комиссия трейдера'),
            'order_service_commission_rate' => __('комиссия сервиса'),
            'payout_service_commission_rate' => __('комиссия сервиса'),
            'is_active' => __('активность'),
            'reservation_time' => __('время на сделку'),
            'payout_reservation_time' => __('время на выплату'),
            'sms_senders' => __('отправители смс/push'),
        ];
    }

    protected function prepareForValidation()
    {
        $currency = strtolower($this->currency ?? '');
        $this->merge([
            'code' => $this->code ? $this->code . '_' . $currency : null,
            'currency' => $currency,
        ]);
    }
}
