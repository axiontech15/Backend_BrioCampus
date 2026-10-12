<?php 
   namespace App\Http\Services\V1;
   use App\Models\Log;

   class LogService
   {
       public function getAllLogs()
       {
           return Log::all();
       }

       public function getLogById(int $id)
       {
           return Log::find($id);
       }

       public function createLog(array $data)
       {
           Log::create($data);
       }

       public function updateLog(int $id, array $data)
       {
           Log::where('id', $id)->update($data);
       }

       public function deleteLog(int $id)
       {
           Log::destroy($id);
       }
   }
?>
