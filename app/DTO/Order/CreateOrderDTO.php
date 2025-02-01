<?php

namespace App\DTO\Order;

use App\DTO\BaseDTO;
use App\Enums\DetailType;
use App\Models\Merchant;
use App\Models\PaymentGateway;
use App\Services\Money\Money;

readonly class CreateOrderDTO extends BaseDTO
{
    public function __construct(
        public Money       $amount,
        public Merchant    $merchant,
        public bool        $h2h = false,
        public bool        $manually = false,
        public ?string     $externalID = null,
        public ?string     $callbackURL = null,
        public ?string     $successURL = null,
        public ?string     $failURL = null,
        public ?PaymentGateway $paymentGateway = null,
        public ?PaymentGateway $subPaymentGateway = null,
        public ?DetailType $paymentDetailType = null,
    )
    {}

    public static function makeFromRequest(array $data): static
    {
        if (! empty($data['payment_gateway'])) {
            $paymentGateway = queries()->paymentGateway()->getByCode($data['payment_gateway']);

            $data['amount'] = Money::fromPrecision($data['amount'], $paymentGateway->currency);
            $data['payment_gateway'] = $paymentGateway;
        } else if (! empty($data['currency'])) {
            $data['amount'] = Money::fromPrecision($data['amount'], $data['currency']);
        }

        $data['payment_detail_type'] = ! empty($data['payment_detail_type']) ? DetailType::from($data['payment_detail_type']) : null;
        if (empty($data['merchant'])) {
            $data['merchant'] = Merchant::where('uuid', $data['merchant_id'])->first();
        }

        if (! empty($data['sub_payment_gateway']) && $data['sub_payment_gateway'] === 0) {
            $data['sub_payment_gateway'] = null;
        }

        if (! empty($data['sub_payment_gateway'])) {
            $data['sub_payment_gateway'] = queries()->paymentGateway()->getByCode($data['sub_payment_gateway']);
        }

        return new static(
            amount: $data['amount'],
            merchant: $data['merchant'],
            h2h: $data['h2h'] ?? false,
            manually: $data['manually'] ?? false,
            externalID: $data['external_id'] ?? null,
            callbackURL: $data['callback_url'] ?? null,
            successURL: $data['success_url'] ?? null,
            failURL: $data['fail_url'] ?? null,
            paymentGateway: $data['payment_gateway'] ?? null,
            subPaymentGateway: $data['sub_payment_gateway'] ?? null,
            paymentDetailType: $data['payment_detail_type'] ?? null,
        );
    }
}
