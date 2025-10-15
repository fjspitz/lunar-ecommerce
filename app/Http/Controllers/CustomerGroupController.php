<?php

namespace App\Http\Controllers;

use App\Exceptions\CustomerGroupAlreadyExistsException;
use App\Services\CustomerGroupService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class CustomerGroupController extends Controller
{

    public function create(Request $request, CustomerGroupService $service)
    {
        $validated = $request->validate([
            'name' => 'required|string',
            'handle' => 'required|string',
        ]);

        try {
            $customer_group = $service->create($validated);

            Log::info("Creación de customer group: {$customer_group->name}");
            
            return response($customer_group, 201);
        } catch (CustomerGroupAlreadyExistsException $e) {
            Log::error("Se produjo un error al crear un customer group: {$e->errorMessage()}");

            return response([
                'message' => $e->errorMessage(),
            ], 400);
        }
    }
}
