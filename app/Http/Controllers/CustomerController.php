<?php

namespace App\Http\Controllers;

use App\Exceptions\CustomerAlreadyExistsException;
use App\Exceptions\MissingCustomerGroupException;
use App\Services\CustomerService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Lunar\Models\Customer;

class CustomerController extends Controller
{
    public function index()
    {
        Log::info("Consulta listado de clientes");

        return response()->json(Customer::all(), 200);
    }

    public function show(Customer $customer)
    {
        Log::info("Consulta de cliente por id {$customer->id}");

        return response($customer);
    }

    public function create(Request $request, CustomerService $service)
    {
        $validated = $request->validate([
            'title' => 'required',
            'first_name' => 'required',
            'last_name' => 'required',
            'company_name' => 'required',
            'curp' => 'required',
            'address' => 'required',
            'email' => 'required|email',
            'phone' => 'string',
        ]);

        try {
            $customer = $service->create($validated);

            Log::info("Creación de cliente {$customer->first_name}");

            return response($customer, 201);
        } catch (CustomerAlreadyExistsException|MissingCustomerGroupException $e) {

            Log::error("Se produjo error: {$e->errorMessage()}");

            return response([
                'message' => $e->errorMessage(),
            ], 400);
        }
    }

    public function destroy(Customer $customer, CustomerService $service)
    {
        $result = $service->destroy($customer);

        Log::info("Eliminación de cliente {$customer->first_name}");

        return response([
            'message' => $result['message'],
        ], 200);
    }
}
