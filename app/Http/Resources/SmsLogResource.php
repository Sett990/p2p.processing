<?php

namespace App\Http\Resources;

use App\Models\SmsLog;
use App\Services\Sms\Parser;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SmsLogResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        /**
         * @var SmsLog $this
         */
        return [
            'id' => $this->id,
            'device' => $this->whenLoaded('device', function() {
                return [
                    'id' => $this->device->id,
                    'name' => $this->device->name,
                    'android_id' => $this->device->android_id,
                ];
            }),
            'order' => $this->whenLoaded('order', function() {
                return [
                    'id' => $this->order->id,
                    'uuid' => $this->order->uuid,
                ];
            }),
            'sender' => $this->sender,
            'message' => $this->message,
            'sender_exists' => (bool)(new Parser())->getGatewayBySender($this->sender),
            'parsing_result' => (new Parser())->parseRaw($this->message),
            'timestamp' => Carbon::createFromTimestamp($this->timestamp)->toDateTimeString(),
            'type' => $this->type->value,
            'created_at' => $this->created_at->toDateTimeString(),
            'user' => $this->whenLoaded('user', function() {
                return [
                    'id' => $this->user->id,
                    'email' => $this->user->email,
                ];
            }),
        ];
    }
}
