<?php

namespace App\Http\Services\V1;

use App\Models\User;

class UserService
{
    public function getAllUsers()
    {
        return User::all();
    }

    public function findUserById(int $id)
    {
        $user = User::find($id);
        if(!$user){
            throw new \Exception('User not found');
        }
        return $user;
    }

    public function createUser(array $data)
    {
        return User::create($data);
    }

    public function updateUser(int $id, array $data)
    {
        return User::find($id)->update($data);
    }
}