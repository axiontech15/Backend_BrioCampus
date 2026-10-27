<?php

namespace App\Http\Controllers\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\V1\PalmaresRequest;
use App\Http\Services\V1\PalmaresService;
use Illuminate\Http\JsonResponse;

class PalmaresController extends Controller
{
    public function __construct(private PalmaresService $palmaresService) {}

    public function index(): JsonResponse
    {
        return response()->json($this->palmaresService->getAllPalmares(), 200);
    }

    public function store(PalmaresRequest $request): JsonResponse
    {
        $data = $request->validated();

        if (! $request->hasFile('file')) {
            return response()->json(['message' => 'Le fichier palmarès est requis.'], 422);
        }

        try {
            $result = $this->palmaresService->importUploadedFile(
                file: $request->file('file'),
                universityId: (int) $data['university_id'],
                uploadedBy: (int) $data['uploaded_by'],
                status: $data['status'] ?? 'pending',
            );
        } catch (\Throwable $e) {
            return response()->json([
                'message' => 'Échec de l’import du palmarès.',
                'error' => $e->getMessage(),
            ], 422);
        }

        return response()->json([
            'message' => 'Palmarès enregistré et fichier traité.',
            'data' => $result['palmares'],
            'imported' => $result['imported'],
            'skipped' => $result['skipped'],
            'errors' => $result['errors'],
        ], 201);
    }

    public function show(int $id): JsonResponse
    {
        return response()->json($this->palmaresService->getPalmaresById($id)->load('graduates'), 200);
    }

    public function update(PalmaresRequest $request, int $id): JsonResponse
    {
        $data = $request->validated();
        unset($data['file']);

        if ($request->hasFile('file')) {
            return response()->json([
                'message' => 'Pour remplacer un fichier, créez un nouvel import palmarès.',
            ], 422);
        }

        $palmares = $this->palmaresService->updatePalmares($id, $data);

        return response()->json(['message' => 'Palmarès mis à jour.', 'data' => $palmares], 200);
    }

    public function destroy(int $id): JsonResponse
    {
        $this->palmaresService->deletePalmares($id);

        return response()->json(['message' => 'Palmarès supprimé.'], 200);
    }
}
