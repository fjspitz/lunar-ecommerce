<?php

namespace App\Services;

use App\Exceptions\CustomerGroupAlreadyExistsException;
use Lunar\Models\CustomerGroup;
use Lunar\Models\Product;

class CustomerGroupService
{
    public function create(array $customer_group)
    {
        $exists = CustomerGroup::where('name', $customer_group['name'])->first();

        if ($exists) {
            throw new CustomerGroupAlreadyExistsException();
        }
        
        $customer_group = CustomerGroup::create([
            'name' => $customer_group['name'],
            'handle' => $customer_group['handle'],
            'default' => false,
        ]);
        
        $customer_group->save();
        
        $products = Product::all();
        foreach ($products as $product) {
            $product->customerGroups()->save($customer_group);
            $product->customerGroups()->updateExistingPivot($customer_group->id, [
                'enabled' => true,
                'visible' => true, 
                'purchasable' => true,
            ]);
        }

        return $customer_group;
    }
}
