<?php

namespace App\Http\Controllers\V1;

use App\Models\User;
use App\Http\Services\V1\UserService;
use Illuminate\Http\Request;

class UserController
{
    /**
     * Display a listing of the resource.
     */
    protected  $userService;

    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }
    public function index()
    {
        $users = $this->userService->getAllUsers();
        return response()->json($users,200);
    }

    public function show(int $id)
    {
        $user = $this->userService->findUserById($id);
        if(!$user){
            return response()->json(['message' => 'User not found'],404);
        }
        return response()->json($user,200);
    }


}
