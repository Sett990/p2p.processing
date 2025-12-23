<?php

namespace App\DTO\Payout;

use App\DTO\BaseDTO;
use App\Enums\DetailType;
use App\Enums\PayoutRequisiteType;
use App\Models\PaymentGateway;
use App\Models\PayoutGateway;
use App\Services\Money\Money;

readonly class PayoutCreateDTO extends BaseDTO
{
    public function __construct(
        public string         $externalId,
        public Money          $amount,
        public string         $detail,
        public DetailType     $detailType,
        public PayoutRequisiteType $requisiteType,
        public string         $detailInitials,
        public PayoutGateway  $payoutGateway,
        public PaymentGateway $paymentGateway,
        public ?PaymentGateway $subPaymentGateway,
        public ?string        $callbackUrl = null,
    )
    {
    }

    public static function makeFromRequest(array $data): static
    {
        $paymentGateway = queries()->paymentGateway()->getByCode($data['payment_gateway']);

        $data['amount'] = Money::fromPrecision($data['amount'], $paymentGateway->currency);
        $data['payout_gateway'] = PayoutGateway::where('uuid', $data['payout_gateway_id'])->first();
        $data['payment_gateway'] = queries()->paymentGateway()->getByCode($data['payment_gateway']);

        if (! empty($data['sub_payment_gateway'])) {
            $data['sub_payment_gateway'] = queries()->paymentGateway()->getByCode($data['sub_payment_gateway']);
        }

        $requisiteType = PayoutRequisiteType::from($data['requisite_type']);

        $detailType = match ($requisiteType) {
            PayoutRequisiteType::SBP => DetailType::PHONE,
            PayoutRequisiteType::CARD => DetailType::CARD,
        };

        return new static(
            externalId: $data['external_id'],
            amount: $data['amount'],
            detail: $data['detail'],
            detailType: $detailType,
            requisiteType: $requisiteType,
            detailInitials: $data['detail_initials'],
            payoutGateway: $data['payout_gateway'],
            paymentGateway: $data['payment_gateway'],
            subPaymentGateway: $data['sub_payment_gateway'] ?? null,
            callbackUrl: $data['callback_url'] ?? null,
        );
    }
}
