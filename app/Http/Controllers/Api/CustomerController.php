<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class CustomerController extends Controller
{
    // GET /api/admin/customers
    public function getAll()
    {
        return response()->json(Customer::all());
    }

    // POST /api/admin/customers
    public function add(Request $request)
    {
        $validated = $request->validate([
            'customer_id'    => 'required|string|unique:customer,customer_id',
            'first_name'     => 'required|string|max:100',
            'last_name'      => 'required|string|max:100',
            'address'        => 'nullable|string|max:255',
            'date_of_birth'  => 'nullable|date',
            'customer_status'=> 'required|string', // tighten with in:Active,Inactive,... if you have enums
        ]);

        $customer = Customer::create($validated);
        return response()->json($customer, Response::HTTP_CREATED);
    }

    // GET /api/admin/customers/{customer_id}
    public function get($customer_id)
    {
        $customer = Customer::findOrFail($customer_id);
        return response()->json($customer);
    }

    // GET /api/admin/customers/page/{pageNo}
    public function getBatch(int $pageNo)
    {
        $perPage = 20;
        $customers = Customer::paginate($perPage, ['*'], 'page', $pageNo);
        return response()->json($customers);
    }

    // PUT /api/admin/customers/{customer_id}
    public function update(Request $request, $customer_id)
    {
        $customer = Customer::findOrFail($customer_id);

        $validated = $request->validate([
            'first_name'     => 'sometimes|required|string|max:100',
            'last_name'      => 'sometimes|required|string|max:100',
            'address'        => 'nullable|string|max:255',
            'date_of_birth'  => 'nullable|date',
            'customer_status'=> 'sometimes|required|string',
        ]);

        $customer->update($validated);
        return response()->json($customer);
    }

    // DELETE /api/admin/customers/{customer_id}
    public function delete($customer_id)
    {
        $customer = Customer::findOrFail($customer_id);
        $customer->delete();

        return response()->json(null, Response::HTTP_NO_CONTENT);
    }
}
