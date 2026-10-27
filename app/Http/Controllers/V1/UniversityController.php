<?php

namespace App\Http\Controllers\V1;

use App\Http\Controllers\Controller;
use App\Http\Services\V1\LogService;
use App\Http\Services\V1\PalmaresService;
use App\Http\Services\V1\UniversityService;
use App\Models\ProfileSearch;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class UniversityController extends Controller
{
    public function __construct(
        private UniversityService $universityService,
        private PalmaresService $palmaresService,
        private LogService $logService,
    ) {}

    public function index(): View|RedirectResponse
    {
        $user = Auth::user();
        $university = $user->university;

        if (! $university) {
            return redirect()->route('home')->with('error', 'Aucune université associée à ce compte.');
        }

        $graduates = $university->graduates()->latest()->get();
        $searches = ProfileSearch::with(['rhUser', 'graduate'])
            ->where('university_id', $university->id)
            ->latest('created_at')
            ->get();
        $palmares = $university->palmares()->latest()->get();

        $stats = [
            'graduates' => $graduates->count(),
            'rh_consultations' => $searches->pluck('rh_user_id')->unique()->count(),
            'searches' => $searches->count(),
            'palmares' => $palmares->count(),
        ];

        return view('university.index', compact('user', 'university', 'graduates', 'searches', 'palmares', 'stats'));
    }

    public function updateProfile(Request $request): RedirectResponse
    {
        $university = Auth::user()->university;

        if (! $university) {
            abort(404);
        }

        $data = $request->validate([
            'name' => ['required', 'string', 'max:200'],
            'address' => ['required', 'string', 'max:255'],
            'logo' => ['nullable', 'image', 'max:2048'],
        ]);

        if ($request->hasFile('logo')) {
            $data['logo'] = $request->file('logo')->store('universities', 'public');
        }

        $this->universityService->updateUniversity((string) $university->id, $data);

        return back()->with('success', 'Profil de l’université mis à jour.');
    }

    public function importPalmares(Request $request): RedirectResponse
    {
        $user = Auth::user();
        $university = $user->university;

        if (! $university) {
            abort(404);
        }

        $request->validate([
            'file' => ['required', 'file', 'mimes:csv,txt,xlsx,xls', 'max:5120'],
        ]);

        try {
            $result = $this->palmaresService->importUploadedFile(
                file: $request->file('file'),
                universityId: (int) $university->id,
                uploadedBy: (int) $user->id,
                status: 'approved',
            );
        } catch (\Throwable $e) {
            return back()->with('error', 'Import impossible : '.$e->getMessage());
        }

        $this->logService->createLog([
            'user_id' => $user->id,
            'action' => 'UPLOAD',
            'description' => "Import palmarès : {$result['imported']} importé(s), {$result['skipped']} ignoré(s)",
            'ip_address' => $request->ip(),
            'user_agent' => (string) $request->userAgent(),
        ]);

        $message = "Import terminé : {$result['imported']} diplômé(s) importé(s), {$result['skipped']} ligne(s) ignorée(s). Fichier enregistré.";

        return back()->with('success', $message)->with('import_errors', $result['errors']);
    }

    public function apiIndex()
    {
        $universities = $this->universityService->getAllUniversities();

        return response()->json($universities, 200);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'agent_id' => ['required', 'exists:users,id'],
            'name' => ['required', 'string'],
            'address' => ['required', 'string'],
            'status' => ['nullable', 'in:active,inactive,pending'],
        ]);

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
        $data = $request->validate([
            'name' => ['sometimes', 'string'],
            'address' => ['sometimes', 'string'],
            'status' => ['sometimes', 'in:active,inactive,pending'],
            'logo' => ['nullable', 'string'],
        ]);

        $university = $this->universityService->updateUniversity($id, $data);

        return response()->json($university, 200);
    }

    public function destroy(string $id)
    {
        $result = $this->universityService->deleteUniversity($id);

        if ($result) {
            return response()->json(['message' => 'University deleted successfully'], 200);
        }

        return response()->json(['message' => 'University not found'], 404);
    }
}
