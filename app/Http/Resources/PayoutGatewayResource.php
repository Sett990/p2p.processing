<?php

namespace App\Http\Resources;

use App\Models\PayoutGateway;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PayoutGatewayResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        /**
         * @var PayoutGateway $this
         */
        return [
            'id' => $this->id,
            'name' => $this->name,
            'domain' => $this->domain,
            'enabled' => $this->enabled,
            'created_at' => $this->created_at->toDateTimeString(),
        ];
    }
}
