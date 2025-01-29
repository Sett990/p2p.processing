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
            'sender' => $this->sender,
            'message' => $this->message,
            'sender_exists' => (bool)(new Parser())->getGatewayBySender($this->sender),
            'parsed_amount' => (new Parser())->parseAmountFromMessage($this->message),
            'timestamp' => Carbon::createFromTimestamp($this->timestamp)->toDateTimeString(),
            'type' => $this->type->value,
            'created_at' => $this->created_at->toDateTimeString(),
            $this->mergeWhen($this->resource->relationLoaded('user'), function () {
                return [
                    'user' => UserResource::make($this->user)
                ];
            }),
        ];
    }
}
