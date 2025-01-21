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
        //поиск отправителя
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

        //парсинг
        $smsParsers = $this->getParsers();
        $result = [];
        foreach ($smsParsers as $smsParser) {
            $r = $this->parserByParser($message, $smsParser);

            if (! empty($r)) {
                $result[] = $r;
            }
        }

        if (empty($result[0])) {
            return null;
        }
        if (count($result) > 1) {
            throw new SmsServiceException('The text message was matched by two or more parsers. - ' . $message);
        }

        return new ParserResultValue(
            amount: Money::fromPrecision($result[0]['amount'], $smsParser->paymentGateway->currency),
            card_type: $result[0]['card_type'],
            card_last_digits: $result[0]['card_last_digits'],
            paymentGateway: $smsParser->paymentGateway,
        );
    }

    public function parserByParser(string $message, SmsParser $smsParser): array
    {
        $message = str_replace("\u{A0}", ' ', $message);
        $message = str_replace("\n", '', $message);
        $message = trim($message);

        $props = $this->parseMessage($message, $smsParser);

        if (empty($props['amount'])) {
            return [];
        }

        $props = $this->prepareProps($props);

        return [
            'amount' => $props['amount'],
            'card_type' => $props['card_type'],
            'card_last_digits' => $props['card_last_digits'],
        ];
    }

    protected function prepareProps(array $props): array
    {
        $props['amount'] = $this->prepareAmount($props['amount']);

        $props['card_type'] = ! empty($props['card_type']) ? strtoupper($props['card_type']) : null;

        $props['card_last_digits'] = ! empty($props['card_last_digits']) ? $props['card_last_digits'] : null;

        return $props;
    }

    protected function prepareAmount(string $amount): string
    {
        if (str_contains($amount, '.')) {
            $parts = explode('.', $amount);
            $lastPart = $parts[count($parts) - 1];
            if (strlen($lastPart) === 2) {
                unset($parts[count($parts) - 1]);
                $amount = implode('', $parts);

                if (intval($lastPart) > 0) {
                    $amount .= ',' . $lastPart;
                }
            }
        }

        if (str_contains($amount, ',')) {
            $parts = explode(',', $amount);
            $lastPart = $parts[count($parts) - 1];
            if (strlen($lastPart) === 2) {
                unset($parts[count($parts) - 1]);
                $amount = implode('', $parts);

                if (intval($lastPart) > 0) {
                    $amount .= ',' . $lastPart;
                }
            }
        }

        $amount = preg_replace(['/[^\d,]+/', '/,,+/'], ['', ','], $amount);

        return $amount;
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
