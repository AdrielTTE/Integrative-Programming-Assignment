<?php

namespace App\Services\Api;

use App\Models\Delivery;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

use Exception; // Import Exception class

class DeliveryService
{
    public function getAll()
    {
        return Delivery::all();
    }

    public function create(array $data)
    {
        // Corrected table names in 'exists' rules
        $validator = Validator::make($data, [
            'delivery_id'             => 'required|string|unique:delivery,delivery_id',
            'package_id'              => 'required|string|exists:package,package_id',
            'driver_id'               => 'required|string|exists:deliverydriver,driver_id',
            'vehicle_id'              => 'required|string|exists:vehicle,vehicle_id',
            'route_id'                => 'nullable|string|exists:route,route_id',
            'pickup_time'             => 'required|date',
            'estimated_delivery_time' => 'required|date|after_or_equal:pickup_time',
            'actual_delivery_time'    => 'nullable|date',
            'delivery_status'         => 'required|string',
            'delivery_cost'           => 'nullable|numeric',
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        return Delivery::create($validator->validated());
    }

    public function getById(string $id)
    {
        return Delivery::findOrFail($id);
    }

    public function getPaginated(int $pageNo, int $perPage = 20)
    {
        return Delivery::paginate($perPage, ['*'], 'page', $pageNo);
    }

    public function update(string $id, array $data)
    {
        $delivery = Delivery::findOrFail($id);

        // Corrected table names in 'exists' rules
        $validator = Validator::make($data, [
            'package_id'              => 'sometimes|required|string|exists:package,package_id',
            'driver_id'               => 'sometimes|required|string|exists:deliverydriver,driver_id',
            'vehicle_id'              => 'sometimes|required|string|exists:vehicle,vehicle_id',
            'route_id'                => 'nullable|string|exists:route,route_id',
            'pickup_time'             => 'sometimes|required|date',
            'estimated_delivery_time' => 'sometimes|required|date|after_or_equal:pickup_time',
            'actual_delivery_time'    => 'nullable|date',
            'delivery_status'         => 'sometimes|required|string',
            'delivery_cost'           => 'nullable|numeric',
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        $delivery->update($validator->validated());
        return $delivery;
    }

    public function delete(string $id): void
    {
        $delivery = Delivery::findOrFail($id);
        $delivery->delete();
    }

    public function getCountDeliveries(): int
    {
        return Delivery::count();
    }

    public function getCountByStatus(string $status): Collection{
        if (strtolower($status) === 'all') {
        return Delivery::query()
            ->select('delivery_status', DB::raw('count(*) as count'))
            ->groupBy('delivery_status')
            ->get()
            ->collect();
    }
     $count = Delivery::where('delivery_status', $status)->count();

    return collect([
        ['delivery_status' => $status, 'count' => $count]
    ]);
    }


    public function getPackageForDriver(string $packageId, string $driverId): Package
    {
        $package = Package::with('customer', 'delivery')
            ->where('package_id', $packageId)
            ->whereHas('delivery', function ($query) use ($driverId) {
                $query->where('driver_id', $driverId);
            })
            ->first();

        if (!$package) {
            throw new Exception('Package not found or you are not authorized to view it.');
        }
        return $package;
    }

    // --- NEW SERVICE METHOD 2 ---
    /**
     * Updates the status of a delivery and its package within a transaction.
     * Secure Coding: A transaction ensures that if one update fails, both are rolled back.
     */
    public function updateStatusForDriver(string $packageId, string $driverId, string $newStatus): void
    {
        DB::transaction(function () use ($packageId, $driverId, $newStatus) {
            $package = $this->getPackageForDriver($packageId, $driverId); // Re-uses the security check

            $package->delivery->update(['delivery_status' => $newStatus]);
            $package->update(['package_status' => $newStatus]);

            // If the delivery is final, free up the driver.
            if (in_array($newStatus, ['DELIVERED', 'FAILED'])) {
                $package->delivery->driver->update(['driver_status' => 'AVAILABLE']);
            }
        });
    }
}