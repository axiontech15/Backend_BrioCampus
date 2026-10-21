<?php 
    namespace App\Http\Services\V1;
    
    use App\Models\Palmares;

    class PalmaresService{

    public function getAllPalmares()
    {
        return Palmares::all();
    }
    public function getPalmaresById(int $id)
    {
        $palmares = Palmares::find($id);
        if (!$palmares) {
            throw new \Exception('Palmares not found');
        }
        return $palmares;
    }
    public function createPalmares(array $data)
    {
        $palmares = Palmares::create($data);
        if (!$palmares) {
            throw new \Exception('Failed to create Palmares');
        }
        return $palmares;
    }
    
    public function updatePalmares(int $id, array $data)
    {
        $palmares = Palmares::find($id);
        if (!$palmares) {
            throw new \Exception('Palmares not found');
        }
        $palmares->update($data);
        return $palmares;
    }
    public function deletePalmares(int $id)
    {
        $palmares = Palmares::find($id);
        if (!$palmares) {
            throw new \Exception('Palmares not found');
        }
        $palmares->delete();
        return $palmares;
    }   
    }
?>