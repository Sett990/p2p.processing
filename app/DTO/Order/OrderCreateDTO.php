<?php

namespace App\DTO\Order;

use App\DTO\BaseDTO;
use App\Enums\DetailType;
use App\Models\Merchant;
use App\Services\Money\Currency;
use App\Services\Money\Money;

readonly class OrderCreateDTO extends BaseDTO
{
    public function __construct(
        public Money $amount,
        public Merchant $merchant,
        public bool $h2h = false,
        public ?string $external_id = null,
        public ?string $callback_url = null,
        public ?string $success_url = null,
        public ?string $fail_url = null,
        public ?string $payment_gateway = null,
        public ?Currency $currency = null,
        public ?DetailType $payment_detail_type = null,
    )
    {}

    public static function formMerchantRequest(array $data): static
    {
        if (! empty($data['payment_gateway'])) {
            $paymentGateway = queries()->paymentGateway()->getByCode($data['payment_gateway']);

            $data['amount'] = Money::fromPrecision($data['amount'], $paymentGateway->currency);
        } else if (! empty($data['currency'])) {
            $data['currency'] = new Currency($data['currency']);
            $data['amount'] = Money::fromPrecision($data['amount'], $data['currency']);
        }

        $data['payment_detail_type'] = ! empty($data['payment_detail_type']) ? DetailType::from($data['payment_detail_type']) : null;
        $data['merchant'] = Merchant::where('id', $data['merchant_id'])->first();

        return make(static::class, $data);
    }

    public static function make(array $data): static
    {
        if (! empty($data['payment_gateway'])) {
            $paymentGateway = queries()->paymentGateway()->getByCode($data['payment_gateway']);

            $data['amount'] = Money::fromPrecision($data['amount'], $paymentGateway->currency);
        } else if (! empty($data['currency'])) {
            $data['currency'] = new Currency($data['currency']);
            $data['amount'] = Money::fromPrecision($data['amount'], $data['currency']);
        }

        $data['payment_detail_type'] = ! empty($data['payment_detail_type']) ? DetailType::from($data['payment_detail_type']) : null;
        $data['merchant'] = Merchant::where('uuid', $data['merchant_id'])->first();

        return make(static::class, $data);
    }
}
