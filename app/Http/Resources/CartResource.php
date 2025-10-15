<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CartResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'customer_id' => $this->customer_id,
            //'can_create_order' => $this->canCreateOrder(),
            'lines' => CartItemResource::collection($this->lines()->get()),
        ];
    }
}
