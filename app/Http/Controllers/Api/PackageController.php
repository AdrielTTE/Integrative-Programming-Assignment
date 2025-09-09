<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Package;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class PackageController extends Controller
{
    // GET /api/admin/packages
    public function getAll()
    {
        // Include relations for admin UI
        return response()->json(
            Package::with(['customer','delivery','assignment'])->get()
        );
    }

    // POST /api/admin/packages
    public function add(Request $request)
    {
        $validated = $request->validate([
            'package_id'         => 'required|string|unique:package,package_id',
            'customer_id'        => 'required|string|exists:customer,customer_id',
            'tracking_number'    => 'required|string|unique:package,tracking_number',
            'package_weight'     => 'nullable|numeric',
            'package_dimensions' => 'nullable|string|max:100',
            'package_contents'   => 'nullable|string',
            'sender_address'     => 'required|string',
            'recipient_address'  => 'required|string',
            'package_status'     => 'required|string',
            'created_at'         => 'nullable|date',
        ]);

        $pkg = Package::create($validated);
        return response()->json($pkg->load(['customer','delivery','assignment']), Response::HTTP_CREATED);
    }

    // GET /api/admin/packages/{package_id}
    public function get($package_id)
    {
        $pkg = Package::with(['customer','delivery','assignment'])->findOrFail($package_id);
        return response()->json($pkg);
    }

    // GET /api/admin/packages/page/{pageNo}
    public function getBatch(int $pageNo)
    {
        $perPage = 20;
        $pkgs = Package::with(['customer','delivery','assignment'])
            ->paginate($perPage, ['*'], 'page', $pageNo);
        return response()->json($pkgs);
    }

    // PUT /api/admin/packages/{package_id}
    public function update(Request $request, $package_id)
    {
        $pkg = Package::findOrFail($package_id);

        $validated = $request->validate([
            'customer_id'        => 'sometimes|required|string|exists:customer,customer_id',
            'tracking_number'    => "sometimes|required|string|unique:package,tracking_number,{$package_id},package_id",
            'package_weight'     => 'nullable|numeric',
            'package_dimensions' => 'nullable|string|max:100',
            'package_contents'   => 'nullable|string',
            'sender_address'     => 'sometimes|required|string',
            'recipient_address'  => 'sometimes|required|string',
            'package_status'     => 'sometimes|required|string',
            'created_at'         => 'nullable|date',
        ]);

        $pkg->update($validated);
        return response()->json($pkg->load(['customer','delivery','assignment']));
    }

    // DELETE /api/admin/packages/{package_id}
    public function delete($package_id)
    {
        $pkg = Package::findOrFail($package_id);
        $pkg->delete();
        return response()->json(null, Response::HTTP_NO_CONTENT);
    }
}
