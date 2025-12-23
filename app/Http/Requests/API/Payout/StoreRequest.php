<?php

namespace App\Http\Requests\API\Payout;

use App\Enums\PayoutRequisiteType;
use App\Models\PaymentGateway;
use App\Models\PayoutGateway;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use LVR\CreditCard\CardNumber;

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
        $payoutGateway = PayoutGateway::where('uuid', $this->payout_gateway_id)->first();

        $requisiteType = $this->requisite_type ? PayoutRequisiteType::tryFrom($this->requisite_type) : null;

        $detailRules = ['required', 'string', 'min:3', 'max:50'];
        if ($requisiteType === PayoutRequisiteType::CARD) {
            $detailRules = ['required', new CardNumber()];
        }

        if ($requisiteType === PayoutRequisiteType::SBP) {
            $detailRules = ['required', 'starts_with:7', 'phone:RU'];
        }

        return [
            'payout_gateway_id' => ['required', 'exists:payout_gateways,uuid'],
            'external_id' => [
                'required',
                Rule::unique('payouts')->where(function ($query) use ($payoutGateway) {
                    return $query->where('external_id', $this->external_id)
                        ->where('payout_gateway_id', $payoutGateway?->id);
                }),
            ],
            'detail' => $detailRules,
            'requisite_type' => ['required', Rule::enum(PayoutRequisiteType::class)],
            'detail_initials' => ['required', 'string', 'min:3', 'max:30'],
            'amount' => ['required', 'integer', 'min:1'],
            'payment_gateway' => [
                'required',
                Rule::exists('payment_gateways', 'code')->where('payouts_enabled', true),
            ],
            'sub_payment_gateway' => ['nullable', 'exists:payment_gateways,code'],
            'callback_url' => ['nullable', 'string', 'url:https', 'max:256'],
        ];
    }
}
