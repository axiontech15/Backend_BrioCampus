<?php

namespace App\Http\Controllers\V1;

use App\Http\Controllers\Controller;
use App\Http\Services\V1\CategoryService;
use App\Http\Services\V1\LogService;
use App\Http\Services\V1\RoleService;
use App\Http\Services\V1\UniversityService;
use App\Http\Services\V1\UserService;
use App\Models\ProfileSearch;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminController extends Controller
{
    public function __construct(
        private UserService $userService,
        private UniversityService $universityService,
        private LogService $logService,
        private CategoryService $categoryService,
        private RoleService $roleService,
    ) {}

    public function index(): View
    {
        $users = User::with('role')->latest()->get();
        $universities = $this->universityService->getAllUniversities();
        $logs = $this->logService->getAllLogs()->load('user')->sortByDesc('created_at')->take(20)->values();
        $categories = $this->categoryService->getAllCategories();
        $roles = $this->roleService->getAllRoles();
        $searches = ProfileSearch::with(['graduate', 'university', 'rhUser'])->latest('created_at')->take(20)->get();

        $stats = [
            'users' => $users->count(),
            'users_active' => $users->where('status', true)->count(),
            'rh_active' => $users->filter(fn (User $u) => $u->isRh() && $u->status)->count(),
            'rh_total' => $users->filter(fn (User $u) => $u->isRh())->count(),
            'universities' => $universities->count(),
            'universities_active' => $universities->where('status', 'active')->count(),
            'searches' => ProfileSearch::count(),
            'tickets' => (int) Ticket::where('status', 'approved')->sum('quantity'),
        ];

        return view('admin.index', compact('users', 'universities', 'logs', 'categories', 'roles', 'searches', 'stats'));
    }

    public function toggleUser(User $user): RedirectResponse
    {
        if ($user->isAdmin()) {
            return back()->with('error', 'Le compte administrateur ne peut pas être suspendu.');
        }

        $user->update(['status' => ! $user->status]);

        return back()->with('success', 'Compte '.$user->email.' : '.($user->status ? 'actif' : 'suspendu').'.');
    }

    public function storeCategory(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120', 'unique:categories,name'],
            'description' => ['nullable', 'string', 'max:500'],
            'quantity' => ['nullable', 'integer', 'min:1'],
        ]);

        $data['status'] = 'active';
        $this->categoryService->createCategory($data);

        return back()->with('success', 'Catégorie créée.');
    }

    public function storeUniversity(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:200'],
            'address' => ['required', 'string', 'max:255'],
            'agent_id' => ['required', 'exists:users,id'],
            'status' => ['required', 'in:active,inactive,pending'],
        ]);

        $this->universityService->createUniversity($data);

        return back()->with('success', 'Université ajoutée.');
    }

    public function storeRole(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:80', 'unique:roles,name'],
        ]);

        $this->roleService->createRole($data);

        return back()->with('success', 'Rôle créé.');
    }

    public function destroyRole(int $role): RedirectResponse
    {
        $deleted = $this->roleService->deleteRole($role);

        return back()->with(
            $deleted ? 'success' : 'error',
            $deleted ? 'Rôle supprimé.' : 'Rôle introuvable ou déjà utilisé.'
        );
    }
}
