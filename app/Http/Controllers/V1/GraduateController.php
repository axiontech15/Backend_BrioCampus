<?php

namespace App\Http\Controllers\V1;

use Illuminate\Http\Request;
use App\Http\Services\V1\GraduateService;
use App\Http\Requests\V1\GraduateRequest;
class GraduateController
{
   
    private $graduateService;

    public function __construct(GraduateService $graduateService)
    {
        $this->graduateService = $graduateService;
    }

    public function index()
    {
        return $this->graduateService->getAllGraduates();   
    }

    public function store(GraduateRequest $request)
    {
        return $this->graduateService->createGraduate($request->validated());
    }

    public function show(int $id)
    {
        return $this->graduateService->getGraduateById($id);
    }

    public function update(GraduateRequest $request, int $id)
    {
        return $this->graduateService->updateGraduate($id, $request->validated());
    }

    public function destroy(int $id)
    {
        return $this->graduateService->deleteGraduate($id);
    }
}
