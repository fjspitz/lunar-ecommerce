<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CartItemResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'purchasable_id' => $this->purchasable_id,
            'quantity' => $this->quantity,
        ];
    }
}
