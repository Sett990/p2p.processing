<?php

namespace App\Services\Sms;

use App\Contracts\SmsServiceContract;
use App\DTO\SMS\SmsDTO;
use App\Enums\OrderStatus;
use App\Exceptions\SmsServiceException;
use App\Models\SenderStopList;
use App\Models\SmsLog;
use App\Services\Sms\Utils\NormalizeMessage;

class SmsService implements SmsServiceContract
{
    /**
     * @throws SmsServiceException
     */
    public function handleSms(SmsDTO $sms): void
    {
        $sender = $this->normalizeMessage($sms->sender);
        $senderInStopList = SenderStopList::query()->where('sender', $sender)->exists();

        if ($senderInStopList) {
            return;
        }

        $smsLog = $this->logSms($sms);

        $result = (new Parser())->parse($sender, $sms->message);

        if (empty($result)) {
            return;
        }

        $order = queries()
            ->order()
            ->findPending($result->amount, $sms->user);

        if (! $order) {
            return;
        }

        if ($order->paymentGateway->code !== $result->paymentGateway->code) {
            return;
        }

        if ($order && $order->status->equals(OrderStatus::PENDING)) {
            services()->order()->succeed($order);

            $smsLog->update([
                'order_id' => $order->id,
            ]);
        }
    }

    protected function logSms(SmsDTO $sms): SmsLog
    {
        $parsingResult = (new Parser())->parseRaw($sms->message);

        return SmsLog::create([
            'sender' => $this->normalizeMessage($sms->sender),
            'message' => $this->normalizeMessage($sms->message),
            'parsing_result' => $parsingResult['amount'] ? $parsingResult : null,
            'timestamp' => $sms->timestamp / 1000,
            'type' => $sms->type,
            'user_id' => $sms->user->id,
        ]);
    }

    protected function normalizeMessage(string $message): string
    {
        return NormalizeMessage::normalize($message);
    }
}
