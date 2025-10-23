<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        //$locale = config()->get('locale', 'es');
        //$locale = config('app.locale', 'es');
        $locale = 'en';

        $ava = $this->customerGroups()
            ->where('product_id', $this->id)
            ->first()->pivot;

        $description = '';

        if (isset(json_decode($this->attribute_data, true)['description'][$locale])) {
            $description = strip_tags(json_decode($this->attribute_data, true)['description'][$locale]);
        }

        return [
            'id' => $this->id,
            'name' => json_decode($this->attribute_data, true)['name'][$locale],
            'description' => $description,
            'brand_name' => $this->brand->name ?? '-',
            'product_type_name' => $this->productType->name,
            'status' => $this->status,
            //'availability' => $ava,
            'availability' => [
                'purchasable' => (bool)$ava['purchasable'],
                'visible' => (bool)$ava['visible'],
                'enabled' => (bool)$ava['enabled'],
            ],
            'stock' => $this->variants()->first()->stock,
            'sku' => $this->variants()->first()->sku,
            'price' => round((float) $this->variants()->first()->getPrices()->first()->price->value / 100, 2),
            'images' => ImageResource::collection($this->getMedia('images')->all()),
            //'images' => ImageResource::collection($this->images->all()),
            //'images' => $this->images->all(),
            //'images' => $this->getMedia('images'),
        ];
    }
}
