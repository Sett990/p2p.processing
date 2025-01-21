<?php

namespace App\Services\Sms;

use App\Contracts\SmsServiceContract;
use App\DTO\SMS\SmsDTO;
use App\Enums\OrderStatus;
use App\Exceptions\SmsServiceException;
use App\Models\SenderStopList;
use App\Models\SmsLog;
use App\Services\Sms\Utils\Parser;

class SmsService implements SmsServiceContract
{
    /**
     * @throws SmsServiceException
     */
    public function handleSms(SmsDTO $sms): void
    {
        $senderInStopList = SenderStopList::query()->where('sender', $sms->sender)->exists();

        if ($senderInStopList) {
            return;
        }

        $smsLog = $this->logSms($sms);

        $result = (new Parser())->parse($sms->sender, $sms->message);

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
        return SmsLog::create([
            'sender' => $sms->sender,
            'message' => $sms->message,
            'timestamp' => $sms->timestamp / 1000,
            'type' => $sms->type,
            'user_id' => $sms->user->id,
        ]);
    }
}
