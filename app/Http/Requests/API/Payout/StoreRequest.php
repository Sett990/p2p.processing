<?php

namespace App\Http\Requests\API\Payout;

use App\Models\Payout;
use App\Models\PayoutGateway;
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
        $payoutGateway = PayoutGateway::where('code', $this->payment_gateway)->first();

        return [
            'external_id' => [
                'required',
                Rule::unique('payouts')->where(function ($query) use ($payoutGateway) {
                    return $query->where('external_id', $this->external_id)
                        ->where('payout_gateway_id', $payoutGateway?->id);
                }),
            ],
            'detail' => ['required', 'string', 'min:3', 'max:30'], //TODO validation
            'detail_type' => ['required', 'in:card,phone'],
            'detail_initials' => ['required', 'string', 'min:3', 'max:30'],
            'amount' => ['required', 'integer', 'min:1'],
            'payment_gateway' => ['required', 'exists:payment_gateways,code'],
            'callback_url' => ['nullable', 'string', 'url:https', 'max:256'],
        ];
    }
}
