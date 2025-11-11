<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Lunar\Models\ProductVariant;

class CartItemResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $product = ProductVariant::find($this->purchasable_id)->product()->first();

        return [
            'purchasable_id' => $this->purchasable_id,
            'quantity' => $this->quantity,
            'product' => new ProductResource($product),
        ];
    }
}
