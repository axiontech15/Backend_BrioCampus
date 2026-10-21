<?php

namespace App\Http\Controllers\V1;

use Illuminate\Http\Request;
use App\Http\Services\V1\UniversityService;
class UniversityController
{
 

    private $universityService;

    public function __construct(UniversityService $universityService)
    {
        $this->universityService = $universityService;
    }

    public function index()
    {
        $universities = $this->universityService->getAllUniversities();
        return response()->json($universities, 200);
    }

    public function store(Request $request)
    {
        $data = $request->validated();
        $university = $this->universityService->createUniversity($data);
        return response()->json($university, 201);
    }

    public function show(string $id)
    {
        $university = $this->universityService->getUniversityById($id);
        return response()->json($university, 200);
    }

    public function update(Request $request, string $id)
    {
        $data = $request->validated();
        $university = $this->universityService->updateUniversity($id, $data);
        return response()->json($university, 200);
    }

    public function destroy(string $id)
    {
        $result = $this->universityService->deleteUniversity($id);
        if ($result) {
            return response()->json(['message' => 'University deleted successfully'], 200);
        } else {
            return response()->json(['message' => 'University not found'], 404);
        }
    }
}
