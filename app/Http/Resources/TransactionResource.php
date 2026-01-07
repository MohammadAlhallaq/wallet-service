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
            'id' => $this->resource->id,
            'wallet' => $this->resource->wallet,
            'related_wallet' => $this->resource->relatedWallet,
            'type' => $this->resource->type,
            'amount' => $this->resource->amount,
            'created_at' => $this->resource->created_at->toDateTimeString(),
        ];
    }
}
