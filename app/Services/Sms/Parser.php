<?php

namespace App\Services\Sms;

use App\Models\PaymentGateway;
use App\Services\Money\Money;
use App\Services\Sms\Utils\NormalizeMessage;
use App\Services\Sms\ValueObjects\ParserResultValue;

class Parser
{
    public function parse(string $sender, string $message): ?ParserResultValue
    {
        $sender = mb_strtolower($sender);
        $sender = trim($sender);

        $gateway = $this->getGatewayBySender($sender);

        if (! $gateway) {
            return null;
        }

        $amount = $this->parseAmountFromMessage($message);

        if (empty($amount)) {
            return null;
        }

        return new ParserResultValue(
            amount: Money::fromPrecision($amount, $gateway->currency),
            paymentGateway: $gateway,
        );
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

    public function parseAmountFromMessage($message): ?string
    {
        $triggerPatterns = [
            'перевод\s(?<amount>\d+(.\d+){0,3})р\sот\s.+\sбаланс',
            'перевод\sна\sсумму\s.+\sиз\s.+\sот\s',
            'perevod\s.+\sot\s.+\siz\s.+\sna\sschet\s',
            'зачислен перевод по',
            'поступление',
            'пополнение',
            'зачисление',
            '[а-я]+\sпополнена',
            'popolnenie scheta',
            'postuplenie sredstv na schet',
            'postuplenie',
            'получен перевод',
            'popolnenie',
            'приход на карту',
            'перевод из',
            'vneseno',
            'перевел\(а\) вам',
            'postupil perevod',
            'перевод денежных средств',
            'перевод на карту',
            'zachislenie',
        ];

        $stopPatterns = [
            'поступил платёж',
        ];

        $exceptions = [
            '^\+\s(?<amount>\d+(.\d+){0,3})\s₽\.\sтеперь\sна\sкарте\s.+₽$',
            '^\+\s(?<amount>\d+(.\d+){0,3})\s₽\s-\sбаланс\:\s.+$',
            '^\d{2}\.\d{2}\.\d{2}\s\d{2}\:\d{2}\sзачисление\s\*(?<card_last_digits>\d{4})\srur\s(?<amount>\d+(.\d+){0,3})\;\sостаток\s.+$',
            '^\+\s(?<amount>\d+(.\d+){0,3})\s₽\sот\s.+теперь\sна\sсчете\s.+₽$',
            '^\+\s(?<amount>\d+(.\d+){0,3})\s₽\s—\sтеперь\sу\sвас\:\s.+$',
            '^\d{2}\:\d{2}\sперевод\s(?<amount>\d+(.\d+){0,3})р\sна\sкарту\s.+\sбаланс\s.+$',
            '^\+\s(?<amount>\d+(.\d+){0,3})\s₽\s—\sбаланс\:\s.+$',
            '^совкомбанк\s\+\s(?<amount>\d+(.\d+){0,3})\s₽\s—\sбаланс\:\s.+(?<card_last_digits>\d{4})$'
        ];

        $message = NormalizeMessage::normalize($message);

        $amount = null;

        foreach ($stopPatterns as $stopPattern) {
            $regex = '/' . $stopPattern . '/mi';
            preg_match_all($regex, $message, $matches, PREG_SET_ORDER);

            if (! empty($matches[0])) {
                return null;
            }
        }

        foreach ($exceptions as $exception) {
            $regex = '/' . $exception . '/mi';
            preg_match_all($regex, $message, $matches, PREG_SET_ORDER);

            if (! empty($matches[0]['amount'])) {
                $amount = $matches[0]['amount'];
                break;
            }
        }

        if (empty($amount)) {
            foreach ($triggerPatterns as $triggerWord) {
                $triggerWord = mb_strtolower($triggerWord);

                $regex = '/' . $triggerWord . '/mi';
                preg_match_all($regex, $message, $matches, PREG_SET_ORDER);

                if (! empty($matches[0])) {
                    $amount = $this->findAmount($message);
                    break;
                }
            }
        }

        if ($amount) {
            $amount = $this->prepareAmount($amount);
        }

        return $amount;
    }

    protected function findAmount($message): ?string
    {
        $amountRegex = '(\s|\+)(?<amount>\d+(.\d+){0,3})\s{0,1}(RUB|rub|р|p|₽|RUR|rur|rurcard2card|руб)(\s|\.|\,|\;)';

        $regex = '/' . $amountRegex . '/mi';
        preg_match_all($regex, $message, $matches, PREG_SET_ORDER);

        $amount = null;
        if (! empty($matches[0]['amount'])) {
            $amount = $matches[0]['amount'];
        }

        return $amount;
    }



    protected function getGatewayBySender(string $sender): ?PaymentGateway
    {
        /**
         * @var PaymentGateway $paymentGateway
         */
        $paymentGateways = PaymentGateway::get(['id', 'code', 'name', 'sms_senders']);
        $paymentGateway = null;

        $sender = NormalizeMessage::normalize($sender);

        foreach ($paymentGateways as $gateway) {
            if (empty($gateway->sms_senders)) {
                continue;
            }

            $smsSenders = $gateway->sms_senders;

            $smsSenders = array_map(function ($sender) {
                return NormalizeMessage::normalize($sender);
            }, $smsSenders);

            if (in_array($sender, $smsSenders)) {
                $paymentGateway = $gateway;
            }
        }

        if (! $paymentGateway) {
            return null;
        }

        return $paymentGateway;
    }
}
