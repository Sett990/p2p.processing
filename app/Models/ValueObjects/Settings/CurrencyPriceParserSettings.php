<?php

namespace App\Models\ValueObjects\Settings;

class CurrencyPriceParserSettings
{
    public function __construct(
        public ?int $amount = null,
        public array $payment_methods = [],
        public ?int $ad_quantity = null,
        public ?int $min_recent_orders = null,
    ) {}

    public static function fromArray(?array $data): self
    {
        $data = $data ?? [];

        $paymentMethods = $data['payment_methods'] ?? $data['payment_method'] ?? [];
        if (! is_array($paymentMethods)) {
            $paymentMethods = $paymentMethods ? [$paymentMethods] : [];
        }

        return new self(
            amount: isset($data['amount']) ? (int) $data['amount'] : null,
            payment_methods: array_values(array_filter(
                array_map(fn ($method) => is_numeric($method) ? (int) $method : null, $paymentMethods),
                fn ($method) => $method !== null
            )),
            ad_quantity: isset($data['ad_quantity']) ? (int) $data['ad_quantity'] : null,
            min_recent_orders: isset($data['min_recent_orders']) ? (int) $data['min_recent_orders'] : null,
        );
    }

    public function toArray(): array
    {
        return [
            'amount' => $this->amount,
            'payment_methods' => $this->payment_methods,
            'ad_quantity' => $this->ad_quantity,
            'min_recent_orders' => $this->min_recent_orders,
        ];
    }
}
