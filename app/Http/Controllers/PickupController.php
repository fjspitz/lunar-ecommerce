<?php

namespace App\Http\Controllers;

use App\Services\PickupService;
use Illuminate\Http\Request;

class PickupController extends Controller
{
    public function index(Request $request, PickupService $pickupService)
    {
        $validated = $request->validate([
            'customer_group' => 'lowercase|exists:addresses'
        ]);
        
        $customer_group = $validated['customer_group'] ?? '';

        return $pickupService->getAddressesByCustomerGroup($customer_group);
    }
}
