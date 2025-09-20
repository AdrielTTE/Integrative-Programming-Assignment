<?php


namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\Api\DeliveryService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

use Illuminate\Support\Facades\Auth; // Import Auth facade
use App\Models\Package; // 

class DeliveryController extends Controller
{
    protected $deliveryService;

    public function __construct(DeliveryService $deliveryService)
    {
        $this->deliveryService = $deliveryService;
    }

    public function getAll()
    {
        return response()->json($this->deliveryService->getAll());
    }

    public function add(Request $request)
    {
        $delivery = $this->deliveryService->create($request->all());
        return response()->json($delivery, Response::HTTP_CREATED);
    }

    public function get($delivery_id)
    {
        return response()->json($this->deliveryService->getById($delivery_id));
    }

    public function getBatch(int $pageNo)
    {
        return response()->json($this->deliveryService->getPaginated($pageNo));
    }

    public function update(Request $request, $delivery_id)
    {
        $delivery = $this->deliveryService->update($delivery_id, $request->all());
        return response()->json($delivery);
    }

    public function delete($delivery_id)
    {
        $this->deliveryService->delete($delivery_id);
        return response()->json(null, Response::HTTP_NO_CONTENT);
    }

    public function getCountDeliveries(){
        return response()->json($this->deliveryService->getCountDeliveries());
    }

    public function getCountByStatus($status){
        return response()->json($this->deliveryService->getCountByStatus($status));
    }

    public function getDeliveryPackageDetails(string $packageId)
    {
        try {
            $package = $this->deliveryService->getPackageForDriver($packageId, Auth::id());
            return response()->json($package);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 404);
        }
    }

    // --- NEW API METHOD 2 ---
    /**
     * Update the status of a package and its delivery.
     * Secure Coding: Ensures the logged-in driver is the one performing the update.
     */
    public function updatePackageStatus(Request $request, string $packageId)
    {
        $validated = $request->validate([
            'status' => 'required|string|in:PICKED_UP,IN_TRANSIT,DELIVERED,FAILED',
        ]);

        try {
            $this->deliveryService->updateStatusForDriver($packageId, Auth::id(), $validated['status']);
            return response()->json(['message' => 'Status updated successfully.']);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Failed to update status: ' . $e->getMessage()], 403);
        }
    }
}

