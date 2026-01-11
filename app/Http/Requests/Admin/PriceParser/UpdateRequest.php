<?php

namespace App\Http\Requests\Admin\PriceParser;

use Illuminate\Foundation\Http\FormRequest;

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
        return [
            'amount' => ['nullable', 'integer', 'min:1'],
            'payment_methods' => ['nullable', 'array'],
            'payment_methods.*' => ['integer', 'distinct'],
            'ad_quantity' => ['nullable', 'integer', 'min:1', 'max:200'],
            'min_recent_orders' => ['nullable', 'integer', 'min:0'],
        ];
    }

    public function attributes(): array
    {
        return [
            'payment_methods' => 'платежные методы',
            'ad_quantity' => 'количество объявлений',
            'min_recent_orders' => 'минимум ордеров',
        ];
    }
}
