<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PickupResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        parent::wrap(null);
        return [
            'id' => $this->id,
            'name' => $this->name,
            'line_one' => $this->line_one,
            'line_two' => $this->line_two,
            'line_three' => $this->line_three,
            'city' => $this->city,
            'state' => $this->state,
            'postcode' => $this->postcode,
            'country_id' => $this->country_id,
        ];
    }
}
