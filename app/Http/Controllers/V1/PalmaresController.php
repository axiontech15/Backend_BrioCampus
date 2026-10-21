<?php

namespace App\Http\Controllers\V1;

use App\Http\Requests\V1\PalmaresRequest;
use App\Http\Services\V1\PalmaresService;


class PalmaresController
{

    private $palmaresService;

    public function __construct(PalmaresService $palmaresService)
    {
      $this->palmaresService = $palmaresService;
    }

    public function index()
    {
        return $this->palmaresService->getAllPalmares();
    }
    public function store(PalmaresRequest $request)
    {
        $data = $request->validated();

        if($request->hasFile('file'))
        {
            $path = $request->file('file')->store('palmares', 'public');
            $data['file'] = $path;  
        }
        $palmares = $this->palmaresService->createPalmares($data);
        if(!$palmares){
            return response()->json(['message' => 'Failed to create Palmares'], 500);
        }
        return response()->json(['message' => 'Palmares created successfully', 'data' => $palmares], 201);
    }

    public function update(PalmaresRequest $request, int $id)
    {
        return $this->palmaresService->updatePalmares($id, $request->validated());
    }

    public function destroy(int $id)
    {
        return $this->palmaresService->deletePalmares($id);
    }
}
