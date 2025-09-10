<?php

namespace App\Services\Api;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class UserService
{
    public function getAll()
    {
        return User::all()->makeHidden(['password_hash', 'remember_token']);
    }

    public function create(array $data)
    {
        $validator = Validator::make($data, [
            'user_id'      => 'required|string|unique:users,user_id',
            'username'     => 'required|string|max:100|unique:users,username',
            'email'        => 'required|email|max:255|unique:users,email',
            'password'     => 'required|string|min:8',
            'phone_number' => 'nullable|string|max:50',
            'created_at'   => 'nullable|date',
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        $data = $validator->validated();
        $data['password_hash'] = Hash::make($data['password']);
        unset($data['password']);

        return User::create($data)->makeHidden(['password_hash', 'remember_token']);
    }

    public function getById(string $id)
    {
        return User::findOrFail($id)->makeHidden(['password_hash', 'remember_token']);
    }

    public function getPaginated(int $pageNo, int $perPage = 20)
    {
        $users = User::paginate($perPage, ['*'], 'page', $pageNo);
        $users->getCollection()->transform(fn ($u) => $u->makeHidden(['password_hash', 'remember_token']));
        return $users;
    }

    public function update(string $id, array $data)
    {
        $user = User::findOrFail($id);

        $validator = Validator::make($data, [
            'username'     => "sometimes|required|string|max:100|unique:users,username,{$id},user_id",
            'email'        => "sometimes|required|email|max:255|unique:users,email,{$id},user_id",
            'password'     => 'sometimes|required|string|min:8',
            'phone_number' => 'nullable|string|max:50',
            'created_at'   => 'nullable|date',
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        $data = $validator->validated();

        if (isset($data['password'])) {
            $data['password_hash'] = Hash::make($data['password']);
            unset($data['password']);
        }

        $user->update($data);

        return $user->fresh()->makeHidden(['password_hash', 'remember_token']);
    }

    public function delete(string $id): void
    {
        $user = User::findOrFail($id);
        $user->delete();
    }
}
