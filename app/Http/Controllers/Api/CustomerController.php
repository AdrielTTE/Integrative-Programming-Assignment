<?php



namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\Api\CustomerService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class CustomerController extends Controller
{
    protected $customerService;

    public function __construct(CustomerService $customerService)
    {
        $this->customerService = $customerService;
    }

    public function getAll()
    {
        return response()->json($this->customerService->getAll());
    }

    public function add(Request $request)
    {
        $customer = $this->customerService->create($request->all());
        return response()->json($customer, Response::HTTP_CREATED);
    }

    public function get($customer_id)
    {
        return response()->json($this->customerService->getById($customer_id));
    }

    public function getBatch(int $pageNo)
    {
        return response()->json($this->customerService->getPaginated($pageNo));
    }

    public function update(Request $request, $customer_id)
    {
        $customer = $this->customerService->update($customer_id, $request->all());
        return response()->json($customer);
    }

    public function delete($customer_id)
    {
        $this->customerService->delete($customer_id);
        return response()->json(null, Response::HTTP_NO_CONTENT);
    }
}
