<?php

namespace App\Http\Requests\PaymentDetail;

use App\Enums\DetailType;
use App\Models\PaymentGateway;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
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
        /**
         * @var PaymentGateway $gateway
         */
        $gateway = PaymentGateway::find($this->payment_gateway_id);


        if (DetailType::PHONE->equals($this->detail_type)) {
            $detail = [
                'required',
                'phone:RU,KZ,UZ,KG,TJ,AZ',
                'unique:payment_details,detail',
                // Дополнительная логика: определяем страну по префиксу
                function ($attribute, $value, $fail) {
                    // Удаляем пробелы/дефисы, чтобы не мешали при проверке
                    $normalized = preg_replace('/\\s+|-/u', '', $value);

                    // Пытаемся определить страну по префиксу
                    $country = $this->guessCountryByPrefix($normalized);

                    if (!$country) {
                        $fail('Не удалось определить страну по номеру телефона.');
                        return;
                    }
                },
            ];
        } else if (DetailType::CARD->equals($this->detail_type)) {
            $detail = [
                'required',
                new CardNumber(),
                'unique:payment_details,detail'
            ];
        } else if (DetailType::ACCOUNT_NUMBER->equals($this->detail_type)) {
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

    private function guessCountryByPrefix(string $number): ?string
    {
        if (Str::startsWith($number, '77')) {
            return 'KZ'; // Казахстан
        } elseif (Str::startsWith($number, '7')) {
            return 'RU'; // Россия
        } elseif (Str::startsWith($number, '998')) {
            return 'UZ'; // Узбекистан
        } elseif (Str::startsWith($number, '996')) {
            return 'KG'; // Киргизия
        } elseif (Str::startsWith($number, '992')) {
            return 'TJ'; // Таджикистан
        } elseif (Str::startsWith($number, '994')) {
            return 'AZ'; // Азербайджан
        }

        // Если префикс не подходит ни под одну известную страну
        return null;
    }
}
