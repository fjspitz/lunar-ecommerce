<?php

namespace App\Services;

use App\Exceptions\CustomerAlreadyExistsException;
use App\Exceptions\MissingCustomerGroupException;
use Illuminate\Support\Facades\DB;
use Lunar\Models\Customer;
use Lunar\Models\CustomerGroup;

class CustomerService
{
    public function create(array $customer)
    {
        $exists = Customer::where('company_name', $customer['company_name'])
            ->whereJsonContains('meta->curp', $customer['curp'])
            ->first();

        if ($exists) {
            throw new CustomerAlreadyExistsException;
        }

        $customer_group = CustomerGroup::where('handle', $customer['company_name'])->first();

        if (! $customer_group) {
            throw new MissingCustomerGroupException;
        }

        $customer = Customer::create([
            'title' => $customer['title'],
            'first_name' => $customer['first_name'],
            'last_name' => $customer['last_name'],
            'company_name' => $customer['company_name'],
            'vat_no' => null,
            'account_ref' => $customer['curp'],
            'meta' => [
                'curp' => $customer['curp'],
                'address' => $customer['address'],
                'email' => $customer['email'],
                'phone' => isset($customer['phone']) ? $customer['phone'] : null,
            ],
        ]);

        $customer->customerGroups()->save($customer_group);
        $customer->save();

        return $customer;
    }

    public function destroy(Customer $customer): array
    {
        DB::table('lunar_customer_customer_group')
            ->where('customer_id', $customer->id)
            ->delete();
        $customer->customerGroups()->delete();
        $customer->delete();

        return [
            'result' => true,
            'message' => 'Customer has been deleted.',
        ];
    }
}
