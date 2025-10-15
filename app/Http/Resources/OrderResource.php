<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'order_id' => $this->id,
            'customer_id' => $this->customer_id,
            'cart_id' => $this->cart_id,
            'channel_id' => $this->channel_id,
            'status' => $this->status,
            'reference' => $this->reference,
            'customer_reference' => $this->customer_reference,
            'subtotal' => $this->sub_total->value / 100,
            'total_discount' => $this->discount_total->value / 100,
            'shipping_total' => $this->shipping_total->value / 100,
            'tax_total' => $this->tax_total->value / 100,
            'total' => $this->total->value / 100,
            'currency_code' => $this->currency_code,
            'customer_id' => $this->customer_id,
            'cart_id' => $this->cart_id,
            'channel_id' => $this->channel->id,
            'transactions' => $this->transactions,
            //'product_lines' => ProductCollection::collection($this->product_lines),
            'pickup_point' => $this->addresses->where('type', 'shipping')->first(),
            'lines' => $this->lines,
        ];
    }
}
