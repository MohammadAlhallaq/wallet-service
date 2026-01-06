<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TransactionResource extends JsonResource
{
    /**
     * Transform the resource collection into an array.
     *
     * @return array<int|string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'from_wallet' => $this->resource->wallet_id,
            'to_wallet' => $this->resource->wallet_id,
            'type' => $this->resource->type,
            'amount' => $this->resource->amount,
            'created_at' => $this->resource->created_at,
        ];
    }
}
