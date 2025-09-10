<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\Api\PackageService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class PackageController extends Controller
{
    protected $packageService;

    public function __construct(PackageService $packageService)
    {
        $this->packageService = $packageService;
    }

    public function getAll()
    {
        return response()->json($this->packageService->getAll());
    }

    public function add(Request $request)
    {
        $pkg = $this->packageService->create($request->all());
        return response()->json($pkg, Response::HTTP_CREATED);
    }

    public function get($package_id)
    {
        return response()->json($this->packageService->getById($package_id));
    }

    public function getBatch(int $pageNo)
    {
        return response()->json($this->packageService->getPaginated($pageNo));
    }

    public function update(Request $request, $package_id)
    {
        $pkg = $this->packageService->update($package_id, $request->all());
        return response()->json($pkg);
    }

    public function delete($package_id)
    {
        $this->packageService->delete($package_id);
        return response()->json(null, Response::HTTP_NO_CONTENT);
    }

    public function getCountPackage(){
        return response()->json($this->packageService->getCountPackage());
    }

    public function getRecentPackages(int $noOfRecords){
        return response()->json($this->packageService->getRecentPackages($noOfRecords));
    }
}
