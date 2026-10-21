<?php 
  namespace App\Http\Services\V1;

  use App\Models\Role;
  use Illuminate\Database\Eloquent\ModelNotFoundException;
  
  class RoleService
  {
      public function getAllRoles()
      {
          return Role::all();
      }

      public function getRoleById(int $id)
      {
        return Role::find($id);
      }

      public function createRole(array $data)
      {
          Role::create($data);
      }

      public function updateRole(int $id, array $data)
      {
          Role::where('id', $id)->update($data);
      }

      public function deleteRole(int $id):bool
      {
        $role = Role::find($id);
        if (!$role) {
            return false;
        }
        $role->delete();
        return true;
       
      }
  }
?>