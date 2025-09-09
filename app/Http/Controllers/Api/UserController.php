<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    // GET /api/admin/users
    public function getAll()
    {
        return response()->json(
            User::all()->makeHidden(['password_hash', 'remember_token'])
        );
    }

    // POST /api/admin/users
    public function add(Request $request)
    {
        $validated = $request->validate([
            'user_id'      => 'required|string|unique:users,user_id',
            'username'     => 'required|string|max:100|unique:users,username',
            'email'        => 'required|email|max:255|unique:users,email',
            'password'     => 'required|string|min:8',
            'phone_number' => 'nullable|string|max:50',
            'created_at'   => 'nullable|date',
        ]);

        $data = $validated;
        $data['password_hash'] = Hash::make($validated['password']);
        unset($data['password']);

        $user = User::create($data);

        return response()->json(
            $user->makeHidden(['password_hash', 'remember_token']),
            Response::HTTP_CREATED
        );
    }

    // GET /api/admin/users/{user_id}
    public function get($user_id)
    {
        $user = User::findOrFail($user_id);
        return response()->json($user->makeHidden(['password_hash', 'remember_token']));
    }

    // GET /api/admin/users/page/{pageNo}
    public function getBatch(int $pageNo)
    {
        $perPage = 20;
        $users = User::paginate($perPage, ['*'], 'page', $pageNo);
        $users->getCollection()->transform(fn ($u) => $u->makeHidden(['password_hash', 'remember_token']));
        return response()->json($users);
    }

    // PUT /api/admin/users/{user_id}
    public function update(Request $request, $user_id)
    {
        $user = User::findOrFail($user_id);

        $validated = $request->validate([
            'username'     => "sometimes|required|string|max:100|unique:users,username,{$user_id},user_id",
            'email'        => "sometimes|required|email|max:255|unique:users,email,{$user_id},user_id",
            'password'     => 'sometimes|required|string|min:8',
            'phone_number' => 'nullable|string|max:50',
            'created_at'   => 'nullable|date',
        ]);

        $data = $validated;
        if (isset($data['password'])) {
            $data['password_hash'] = Hash::make($data['password']);
            unset($data['password']);
        }

        $user->update($data);

        return response()->json(
            $user->fresh()->makeHidden(['password_hash', 'remember_token'])
        );
    }

    // DELETE /api/admin/users/{user_id}
    public function delete($user_id)
    {
        $user = User::findOrFail($user_id);
        $user->delete();

        return response()->json(null, Response::HTTP_NO_CONTENT);
    }
}
