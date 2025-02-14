<?php

namespace App\Http\Requests\PaymentDetail;

use App\Enums\DetailType;
use App\Models\PaymentGateway;
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
        /**
         * @var PaymentGateway $gateway
         */
        $gateway = PaymentGateway::find($this->payment_gateway_id);


        if (DetailType::PHONE->equals($this->detail_type)) {
            $detail = [
                'required',
                'starts_with:7',
                'phone:RU',
                'unique:payment_details,detail'
            ];
        } elseif (DetailType::ACCOUNT_NUMBER->equals($this->detail_type)) {
            $detail = [
                'required',
                'digits:20',
                'unique:payment_details,detail'
            ];
        } else {
            $detail = [
                'required',
                'digits:16',
                'unique:payment_details,detail'
            ];
        }

        return [
            'name' => ['required', 'string', 'min:3', 'max:30'],
            'detail' => $detail,
            'detail_type' => ['required', Rule::in(DetailType::values())],
            'initials' => ['required', 'string', 'min:3', 'max:40'],
            'is_active' => ['required', 'boolean'],
            'daily_limit' => ['required', 'integer', 'min:1', 'max:100000000'],
            'payment_gateway_id' => ['required', 'integer', 'exists:payment_gateways,id'],
            'sub_payment_gateway_id' => [
                !$gateway->sub_payment_gateways ? 'nullable' : 'required',
                'integer',
                'exists:payment_gateways,id'
            ],
            'max_pending_orders_quantity' => ['required', 'integer', 'min:1', 'max:100000000'],
        ];
    }

    public function attributes()
    {
        return [
            'detail' => __('реквизит'),
            'initials' => __('инициалы'),
            'payment_gateway_id' => __('платежный метод'),
            'sub_payment_gateway_id' => __('метод'),
            'is_active' => __('активность'),
            'daily_limit' => __('дневной лимит'),
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'detail' => preg_replace('~\D+~','', $this->detail),
        ]);
    }
}
