<?php

namespace App\Http\Resources;

use App\Models\SmsParser;
use App\Services\Sms\Parser;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SmsParserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        /**
         * @var SmsParser $this
         */
        return [
            'id' => $this->id,
            'format' => $this->format,
            'regex' => $this->regex,
            $this->mergeWhen($this->resource->relationLoaded('paymentGateway'), function () {
                return [
                    'payment_gateway_id' => $this->paymentGateway->id,
                    'payment_gateway_name' => $this->paymentGateway->name,
                ];
            }),
        ];
    }
}
