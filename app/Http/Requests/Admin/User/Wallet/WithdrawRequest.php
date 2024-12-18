<?php

namespace App\Http\Requests\Admin\User\Wallet;

use App\Enums\BalanceType;
use App\Models\Wallet;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class WithdrawRequest extends FormRequest
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
         * @var Wallet $wallet
         */
        $wallet = $this->route('user')->wallet;

        $max = intval($wallet->trust_balance->add($wallet->reserve_balance)->toBeauty());

        return [
            'amount' => ['required', 'integer', 'min:1', 'max:' . $max],
            'balance_type' => ['required', Rule::enum(BalanceType::class)],
        ];
    }
}
