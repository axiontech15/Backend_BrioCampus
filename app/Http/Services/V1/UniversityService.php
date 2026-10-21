<?php 
   namespace App\Http\Services\V1;

   use App\Models\University;

   class UniversityService
   {

     public function getAllUniversities()
     {
      return University::all();
     }
  
     public function getUniversityById(string $id)
     {
      return University::find($id);
     }

     public function createUniversity(array $data)
     {
      return University::create($data);
     }

     public function updateUniversity(string $id, array $data)
     {
      return University::where('id', $id)->update($data);
     }

     public function deleteUniversity(string $id):bool
     {
       $university = University::find($id);
       if (!$university) {
         return false;
       }
       $university->delete();
       return true;
     }
   }

?>