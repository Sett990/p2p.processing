<?php

namespace App\Http\Requests\Admin\PaymentGateway;

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
        return [
            'name' => ['required', 'string', 'min:3', 'max:30'],
            'code' => ['required', 'string', 'min:3', 'max:30', 'unique:payment_gateways,code'],
            'nspk_schema' => ['required', 'string', 'min:3', 'max:50', 'unique:payment_gateways,nspk_schema'],
            'currency' => ['required', Rule::in(Currency::getAllCodes())],
            'detail_types' => ['required', 'array'],
            'detail_types.*' => ['nullable', Rule::in(DetailType::values())],
            'sub_payment_gateways' => ['nullable', 'array'],
            'sub_payment_gateways.*' => ['required', 'exists:payment_gateways,id'],
            'min_limit' => ['required', 'integer', 'min:1'],
            'max_limit' => ['required', 'integer', 'min:1'],
            'trader_commission_rate_for_orders' => ['required', 'numeric', 'min:0'],
            'trader_commission_rate_for_payouts' => ['required', 'numeric', 'min:0'],
            'total_service_commission_rate_for_orders' => ['required', 'numeric', 'min:0'],
            'total_service_commission_rate_for_payouts' => ['required', 'numeric', 'min:0'],
            'is_active' => ['required', 'boolean'],
            'reservation_time' => ['required', 'integer', 'min:1'],
            'payout_reservation_time' => ['required', 'integer', 'min:1'],
            'sms_senders' => ['nullable', 'array'],
            'sms_senders.*' => ['required', 'string'],
            'logo' => ['required', 'image', 'mimes:png', 'max:2048', Rule::dimensions()->ratio(1.0)],
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
            'trader_commission_rate_for_orders' => __('комиссия трейдера'),
            'trader_commission_rate_for_payouts' => __('комиссия трейдера'),
            'total_service_commission_rate_for_orders' => __('комиссия сервиса'),
            'total_service_commission_rate_for_payouts' => __('комиссия сервиса'),
            'is_active' => __('активность'),
            'reservation_time' => __('время резервирования'),
            'payout_reservation_time' => __('время резервирования'),
            'sms_senders' => __('отправители смс/push'),
        ];
    }

    protected function prepareForValidation()
    {
        $currency = strtolower($this->currency ?? '');
        $this->merge([
            'currency' => $currency,
        ]);
    }
}
