<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\Api\UserService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class UserController extends Controller
{
    protected $userService;

    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }

    public function getAll()
    {
        return response()->json($this->userService->getAll());
    }

    public function add(Request $request)
    {
        $user = $this->userService->create($request->all());
        return response()->json($user, Response::HTTP_CREATED);
    }

    public function get($user_id)
    {
        return response()->json($this->userService->getById($user_id));
    }

    public function getBatch(int $pageNo)
    {
        return response()->json($this->userService->getPaginated($pageNo));
    }

    public function update(Request $request, $user_id)
    {
        $user = $this->userService->update($user_id, $request->all());
        return response()->json($user);
    }

    public function delete($user_id)
    {
        $this->userService->delete($user_id);
        return response()->json(null, Response::HTTP_NO_CONTENT);
    }
}
