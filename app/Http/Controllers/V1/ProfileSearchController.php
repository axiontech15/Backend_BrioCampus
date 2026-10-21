<?php

namespace App\Http\Controllers\V1;

use Illuminate\Http\Request;
use App\Http\Services\V1\ProfileSearchService;


class ProfileSearchController
{
   
    private $profileSearchService;

    public function __construct(ProfileSearchService $profileSearchService)
    {
        $this->profileSearchService = $profileSearchService;
    }
    public function index()
    {
        $profileSearches = $this->profileSearchService->getAllProfileSearches();
        return response()->json($profileSearches, 200);
    }

    public function store(Request $request)
    {
        $data = $request->validated();
        $profileSearch = $this->profileSearchService->createProfileSearch($data);
        return response()->json($profileSearch, 201);
    }

    public function show(string $id)
    {
        $profileSearch = $this->profileSearchService->getProfileSearchById($id);
        return response()->json($profileSearch, 200);
    }

    public function update(Request $request, string $id)
    {
        $data = $request->validated();
        $profileSearch = $this->profileSearchService->updateProfileSearch($id, $data);
        return response()->json($profileSearch, 200);
    }

    public function destroy(string $id)
    {
        $result = $this->profileSearchService->deleteProfileSearch($id);
        if ($result) {
            return response()->json(['message' => 'Profile search deleted successfully'], 200);
        } else {
            return response()->json(['message' => 'Profile search not found'], 404);
        }
    }
}
