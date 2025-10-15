<?php

namespace App\Services;

use App\Http\Resources\PickupResource;
use App\Models\Address;

class PickupService
{
    public function getAddressesByCustomerGroup(string $customer_group)
    {
        return PickupResource::collection(
            Address::where('customer_group', $customer_group)
            ->where('enabled', true)->get()
        );
    }
}
