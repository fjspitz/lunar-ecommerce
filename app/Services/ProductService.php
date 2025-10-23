<?php

namespace App\Services;

use App\Exceptions\BrandDoesNotExistException;
use App\Exceptions\ProductTypeDoesNotExistException;
use App\Http\Resources\BrandResource;
use App\Http\Resources\ProductCategoryResource;
use App\Http\Resources\ProductResource;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Lunar\FieldTypes\Text;
use Lunar\FieldTypes\TranslatedText;
use Lunar\Models\Brand;
use Lunar\Models\Channel;
use Lunar\Models\Currency;
use Lunar\Models\CustomerGroup;
use Lunar\Models\Price;
use Lunar\Models\Product;
use Lunar\Models\ProductType;
use Lunar\Models\ProductVariant;
use Lunar\Models\TaxClass;
use Lunar\Models\Url;

class ProductService
{
    public function getSingleProduct(Product $product): ProductResource
    {
        return new ProductResource(Product::find($product->id));
    }

    public function getPublishedProductsByCustomerGroup(string $customer_group, int $page_size): ResourceCollection
    {
        return ProductResource::collection(
            Product::where('status', 'published')
                ->whereHas('variants', function (Builder $query) {
                    $query->whereIn('purchasable', ['in_stock', 'always']);
                })
                ->whereHas(
                    'customerGroups',
                    function (Builder $query) use ($customer_group) {
                        $query->where('handle', $customer_group)
                            ->where('visible', true);
                    }
                )->paginate($page_size)
        );
    }

    public function getCategories()
    {
        return ProductCategoryResource::collection(ProductType::all());
    }

    public function getBrands()
    {
        return BrandResource::collection(Brand::all());
    }

    public function create(array $product)
    {
        return DB::transaction(function () use ($product) {
            $product_type_id = (int) $product['product_type_id'];
            $brand_id = (int) $product['brand_id'];

            $product_type_exists = ProductType::where('id', $product_type_id)->first();

            if (!$product_type_exists) {
                throw new ProductTypeDoesNotExistException();
            }

            $brand_exists = Brand::where('id', $brand_id)->first();

            if (!$brand_exists) {
                throw new BrandDoesNotExistException();
            }

            $new_product = new Product();
            $new_product->product_type_id = $product['product_type_id'];
            $new_product->brand_id = $product['brand_id'];
            $new_product->status = 'published';
            $new_product->attribute_data = [
                'name' => new TranslatedText(collect([
                    'en' => new Text($product['name']),
                ])),
                'description' => new TranslatedText(collect([
                    'en' => new Text($product['description']),
                ])),
            ];
            $new_product->save();

            $image_exists = Arr::exists($product, 'image');
            //info($product['image']);

            if ($image_exists) {
                Log::info("Agregando imagen al producto");
                $new_product->addMedia($product['image'])
                    ->withCustomProperties(['caption' => $product['name'], 'primary' => true, 'position' => 1])
                    ->toMediaCollection('images');
            }

            // Esto era para Ademarket: acá no va
            // $customer_groups = CustomerGroup::all();
            // foreach ($customer_groups as $customer_group) {
            //     $new_product->customerGroups()->save($customer_group);
            //     $new_product->customerGroups()->updateExistingPivot($customer_group->id, [
            //         'enabled' => true,
            //         'visible' => true,
            //         'purchasable' => true,
            //     ]);
            // }

            $channel_id = Channel::first()->id;
            $new_product->channels()->updateExistingPivot($channel_id, ['enabled' => true]);

            $product_variant = new ProductVariant();
            $product_variant->product_id = $new_product->id;
            $product_variant->tax_class_id = TaxClass::first()->id;
            $product_variant->shippable = false;
            $product_variant->purchasable = 'in_stock';
            $product_variant->sku = $product['sku'];
            $product_variant->stock = (int) $product['stock'];
            $product_variant->attribute_data = [];
            $product_variant->save();

            $price = new Price();
            //$price->currency_id = Currency::first()->id;
            $price->currency_id = Currency::default()->first()->id;
            //$price->priceable_type = ProductVariant::class;
            $price->priceable_type = 'product_variant';
            $price->priceable_id = $product_variant->id;
            $price->price = $product['price'] * 100;
            $price->save();

            // Customer groups: asigna el producto creado exclusivamente al customer group del usuario que lo dió de alta.
            $customer_group = CustomerGroup::where('handle', $product['customer_group_handle'])->first();
            $new_product->customerGroups()->updateExistingPivot($customer_group->id, ['enabled' => true, 'visible' => true, 'purchasable' => true]);

            return new ProductResource(Product::find($new_product->id));
        });
    }

    public function update(Product $product, array $product_update_info)
    {
        return DB::transaction(function () use ($product, $product_update_info) {
            $this->updateBrandAndCategory($product, $product_update_info);

            if (isset($product_update_info['name']) || isset($product_update_info['description'])) {
                $this->updateProductAttributes($product, $product_update_info);
            }

            if (isset($product_update_info['stock'])) {
                $this->updateProductVariantStock($product, $product_update_info);
            }

            if (isset($product_update_info['price'])) {
                $this->updateProductPrice($product, $product_update_info);
            }

            if (isset($product_update_info['image'])) {
                $this->updateProductImage($product, $product_update_info);
            }

            return new ProductResource($product);
        });
    }

    private function updateBrandAndCategory(Product $product, array $product_update_info)
    {
        $product->update($product_update_info);
    }

    private function updateProductAttributes(Product $product, array $product_update_info)
    {
        $product->update([
            'attribute_data' => [
                'name' => new TranslatedText(collect([
                    'es' => new Text($product_update_info['name'] ?? $product->translateAttribute('name', 'es')),
                ])),
                'description' => new TranslatedText(collect([
                    'es' => new Text($product_update_info['description'] ?? $product->translateAttribute('description', 'es')),
                ])),
            ]
        ]);

        if (isset($product_update_info['name'])) {
            $product_url = Url::where('default', true)
                ->where('element_type', Product::class)
                ->where('element_id', $product->id)
                ->first();

            $product_url->update([
                'slug' => Str::of($product_update_info['name'])->slug('-')
            ]);
        }
    }

    private function updateProductVariantStock(Product $product, array $product_update_info)
    {
        $product_variant = ProductVariant::where('product_id', $product->id)->first();
        $product_variant->update([
            'stock' => $product_update_info['stock'] ?? $product_variant->stock,
        ]);
    }

    private function updateProductPrice(Product $product, array $product_update_info)
    {
        $product_variant = ProductVariant::where('product_id', $product->id)->first();
        $price = Price::where('priceable_id', $product_variant->id)->first();
        $price->update([
            'price' => $product_update_info['price'] * 100 ?? $price->price,
        ]);
    }

    private function updateProductImage(Product $product, array $product_update_info)
    {
        $product->clearMediaCollection('images');
        $product->addMedia($product_update_info['image'])->withCustomProperties(['caption' => null, 'primary' => true, 'position' => 1])->toMediaCollection('images');
    }
}
