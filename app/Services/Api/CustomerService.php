<?php


namespace App\Services\Api;

use App\Models\Customer;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class CustomerService
{
    public function getAll()
    {
        return Customer::all();
    }

    public function create(array $data)
    {
        $validator = Validator::make($data, [
            'customer_id'     => 'required|string|unique:customer,customer_id',
            'first_name'      => 'required|string|max:100',
            'last_name'       => 'required|string|max:100',
            'address'         => 'nullable|string|max:255',
            'date_of_birth'   => 'nullable|date',
            'customer_status' => 'required|string',
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        return Customer::create($validator->validated());
    }

    public function getById(string $id)
    {
        return Customer::findOrFail($id);
    }

    public function getPaginated(int $pageNo, int $perPage = 20)
    {
        return Customer::paginate($perPage, ['*'], 'page', $pageNo);
    }

    public function update(string $id, array $data)
    {
        $customer = Customer::findOrFail($id);

        $validator = Validator::make($data, [
            'first_name'      => 'sometimes|required|string|max:100',
            'last_name'       => 'sometimes|required|string|max:100',
            'address'         => 'nullable|string|max:255',
            'date_of_birth'   => 'nullable|date',
            'customer_status' => 'sometimes|required|string',
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        $customer->update($validator->validated());
        return $customer;
    }

    public function delete(string $id): void
    {
        $customer = Customer::findOrFail($id);
        $customer->delete();
    }
}
