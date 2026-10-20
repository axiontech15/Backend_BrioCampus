<?php

namespace App\Http\Controllers\V1;

use Illuminate\Http\Request;
use App\Http\Requests\V1\RoleRequest;
use App\Http\Services\V1\RoleService;

class RoleController 
{
  
   private $roleService;

    public function __construct(RoleService $roleService)
    {
        $this->roleService = $roleService;
        
    }
    public function index()
    {
        $roles = $this->roleService->getAllRoles();
        return response()->json($roles,200);   
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(RoleRequest $request)
    {
        $data = $request->validated();
        $this->roleService->createRole($data);
        return response()->json(['message' => 'Role created successfully'], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(int $id)
    {
        $role = $this->roleService->getRoleById($id);
        if ($role) {
            return response()->json($role, 200);
        } else {
            return response()->json(['message' => 'Role not found'], 404);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, int $id)
    {
        $data = $request->only(['name']);
        $this->roleService->updateRole($id, $data);
        return response()->json(['message' => 'Role updated successfully'], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(int $id)
    {
        $this->roleService->deleteRole($id);
        return response()->json(['message' => 'Role deleted successfully'], 200);
    }
}
