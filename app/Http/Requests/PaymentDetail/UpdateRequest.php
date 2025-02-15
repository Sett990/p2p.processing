<?php

namespace App\Http\Requests\PaymentDetail;

use App\Enums\DetailType;
use App\Models\PaymentDetail;
use App\Models\PaymentGateway;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use LVR\CreditCard\CardNumber;

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
        /**
         * @var PaymentGateway $gateway
         * @var PaymentDetail $payment_detail
         */
        $gateway = PaymentGateway::find($this->payment_gateway_id);
        $payment_detail = $this->route('payment_detail');

        if (DetailType::PHONE->equals($this->detail_type)) {
            $detail = [
                'required',
                'starts_with:7',
                'phone:RU',
                Rule::unique('payment_details', 'detail')->ignore($payment_detail->id)
            ];
        } else if (DetailType::CARD->equals($this->detail_type)) {
            $detail = [
                'required',
                new CardNumber(),
                'unique:payment_details,detail'
            ];
        } elseif (DetailType::ACCOUNT_NUMBER->equals($this->detail_type)) {
            $detail = [
                'required',
                'digits:20',
                Rule::unique('payment_details', 'detail')->ignore($payment_detail->id)
            ];
        } else {
            $detail = [
                'required',
                'digits:16',
                Rule::unique('payment_details', 'detail')->ignore($payment_detail->id)
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
