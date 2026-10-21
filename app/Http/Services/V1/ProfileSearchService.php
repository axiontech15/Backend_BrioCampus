<?php 
    namespace App\Http\Services\V1;
    use App\Models\ProfileSearch;
    class ProfileSearchService{
        
    public function getAllProfileSearches()
    {
        return ProfileSearch::all();
    }

    public function getProfileSearchById(string $id)
    {
        return ProfileSearch::find($id);
    }
    public function createProfileSearch(array $data){
        return ProfileSearch::create($data);
    }
    public function updateProfileSearch(string $id, array $data)
    {
        return ProfileSearch::where('id', $id)->update($data);
    }
    public function deleteProfileSearch(string $id):bool
    {
        $profileSearch = ProfileSearch::find($id);
        if (!$profileSearch) {
            return false;
        }
       
       $profileSearch->delete();
       return true;
    }
}
?>