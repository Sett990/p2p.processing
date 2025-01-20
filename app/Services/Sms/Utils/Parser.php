<?php

namespace App\Services\Sms\Utils;

use App\Exceptions\SmsServiceException;
use App\Models\PaymentGateway;
use App\Models\SmsParser;
use App\Services\Money\Money;
use App\Services\Sms\ValueObjects\ParserResultValue;
use Illuminate\Database\Eloquent\Collection;

class Parser
{
    public function parse(string $sender, string $message): ?ParserResultValue
    {
        $message = str_replace("\u{A0}", ' ', $message);
        $message = str_replace("\n", '', $message);
        $message = trim($message);

        $smsParsers = $this->getParsers();

        $result = null;

        $sms_senders = [];
        $p = PaymentGateway::get(['sms_senders'])->pluck('sms_senders')->toArray();

        foreach ($p as $item) {
            $item = array_map('strtolower', $item);
            $sms_senders = array_merge(array_values($item), $sms_senders);
        }
        $sms_senders = array_unique($sms_senders);

        if (! in_array($sender, $sms_senders)) {
            return null;
        }

        foreach ($smsParsers as $smsParser) {
            $props = $this->parseMessage($message, $smsParser);

            if (empty($props['amount'])) {
                continue;
            }
            $amount = explode(',', $props['amount']);
            if (intval($amount[1])) {
                continue;
            }

            if ($result) {
                throw new SmsServiceException('The text message was matched by two or more parsers. - ' . $message);
            }

            $props = $this->prepareProps($props, $smsParser);

            $result = new ParserResultValue(
                amount: $props['amount'],
                card_type: $props['card_type'],
                card_last_digits: $props['card_last_digits'],
                paymentGateway: $smsParser->paymentGateway,
            );
        }

        return $result;
    }

    protected function prepareProps(array $props, SmsParser $smsParser): array
    {
        $amount = explode(',', $props['amount']);
        $props['amount'] = $amount[0];
        $props['amount'] = preg_replace('~[^.\d]~', '', $props['amount']);
        $props['amount'] = Money::fromPrecision($props['amount'], $smsParser->paymentGateway->currency);

        $props['card_type'] = ! empty($props['card_type']) ? strtoupper($props['card_type']) : null;

        $props['card_last_digits'] = ! empty($props['card_last_digits']) ? $props['card_last_digits'] : null;

        return $props;
    }

    private function parseMessage(string $message, SmsParser $smsParser): array
    {
        $regex = '/' . $smsParser->regex . '/mi';
        preg_match_all($regex, $message, $matches, PREG_SET_ORDER);

        return empty($matches[0]) ? [] : $matches[0];
    }

    /**
     * @return Collection<int, SmsParser>
     */
    protected function getParsers(): Collection
    {
        return SmsParser::get();
    }
}
