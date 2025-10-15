<?php

namespace App\Services;

use App\Enums\ProductSearchCriteria;
use App\Exceptions\SearchCriteriaNotImplementedException;
use App\Http\Resources\ProductResource;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Lunar\Models\Product;

class SearchService
{
    public function searchBy(string $customer_group, string $criteria, int $value, int $page_size): ResourceCollection
    {
        $result = match ($criteria) {
            ProductSearchCriteria::BY_CATEGORY->value =>
            ProductResource::collection(
                Product::where('product_type_id', $value)
                    ->where('status', 'published')
                    ->whereHas('customerGroups', function (Builder $query) use ($customer_group) {
                        $query->where('handle', $customer_group)
                            ->where('visible', true);
                    })
                    ->paginate($page_size)
            ),
            ProductSearchCriteria::BY_BRAND->value =>
            ProductResource::collection(
                Product::where('brand_id', $value)
                    ->where('status', 'published')
                    ->whereHas('customerGroups', function (Builder $query) use ($customer_group) {
                        $query->where('handle', $customer_group)
                            ->where('visible', true);
                    })
                    ->paginate($page_size)
            ),
            default => throw new SearchCriteriaNotImplementedException()
        };

        return $result;
    }
}
