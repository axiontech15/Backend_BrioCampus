<?php

namespace App\Http\Controllers\V1;

use App\Http\Controllers\Controller;
use App\Http\Services\V1\LogService;
use App\Http\Services\V1\PalmaresService;
use App\Http\Services\V1\UniversityService;
use App\Jobs\ProcessPalmaresImport;
use App\Models\Graduate;
use App\Models\Palmares;
use App\Models\ProfileSearch;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use RuntimeException;

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
            'file' => ['required', 'file', 'mimes:csv,txt,xlsx', 'extensions:csv,xlsx', 'max:5120'],
        ], [
            'file.required' => 'Veuillez sélectionner un fichier de palmarès.',
            'file.mimes' => 'Fichier non reconnu. Utilisez un CSV (.csv) ou un fichier Excel (.xlsx) valide.',
            'file.extensions' => 'Seuls les fichiers .csv et .xlsx sont acceptés.',
            'file.max' => 'Le fichier ne peut pas dépasser 5 Mo.',
        ]);

        try {
            $palmares = $this->palmaresService->createPendingImport(
                file: $request->file('file'),
                universityId: (int) $university->id,
                uploadedBy: (int) $user->id,
                universityName: $university->name,
            );
        } catch (RuntimeException $exception) {
            return back()->withErrors(['file' => $exception->getMessage()]);
        }

        ProcessPalmaresImport::dispatch((int) $palmares->id)->afterCommit();

        $this->logService->createLog([
            'user_id' => $user->id,
            'action' => 'UPLOAD',
            'description' => "Palmarès envoyé pour traitement : {$palmares->file}",
            'ip_address' => $request->ip(),
            'user_agent' => (string) $request->userAgent(),
        ]);

        return back()->with('success', 'Fichier reçu. Son import est en attente de traitement ; son statut sera mis à jour automatiquement.');
    }

    public function palmaresStatus(): JsonResponse
    {
        $university = Auth::user()->university;

        if (! $university) {
            abort(404);
        }

        return response()->json([
            'pending' => $university->palmares()->where('status', 'pending')->count(),
        ]);
    }

    public function storeGraduate(Request $request): RedirectResponse
    {
        $user = Auth::user();
        $university = $user->university;

        if (! $university) {
            abort(404);
        }

        $data = $request->validate([
            'matricule' => ['required', 'string', 'max:255', 'unique:graduates,matricule'],
            'full_name' => ['required', 'string', 'max:255'],
            'faculty' => ['required', 'string', 'max:255'],
            'diploma_date' => ['required', 'date_format:Y-m-d'],
        ]);

        DB::transaction(function () use ($data, $university, $user): void {
            $palmares = Palmares::create([
                'university_id' => $university->id,
                'file' => null,
                'uploaded_by' => $user->id,
                'status' => 'approved',
                'uploaded_at' => now(),
                'verified_at' => now(),
                'verified_by' => $user->id,
            ]);

            Graduate::create([
                'university_id' => $university->id,
                'palmares_id' => $palmares->id,
                'matricule' => $data['matricule'],
                'name' => $data['full_name'],
                'faculty' => $data['faculty'],
                'diploma_date' => $data['diploma_date'],
            ]);
        });

        $this->logService->createLog([
            'user_id' => $user->id,
            'action' => 'MANUAL_GRADUATE_CREATE',
            'description' => "Ajout manuel du diplômé {$data['full_name']} ({$data['matricule']}).",
            'ip_address' => $request->ip(),
            'user_agent' => (string) $request->userAgent(),
        ]);

        return back()->with('success', 'Le diplômé a été ajouté au registre.');
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
