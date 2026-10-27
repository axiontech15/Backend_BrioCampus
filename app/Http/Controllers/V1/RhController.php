<?php

namespace App\Http\Controllers\V1;

use App\Http\Controllers\Controller;
use App\Http\Services\V1\CategoryService;
use App\Http\Services\V1\LogService;
use App\Http\Services\V1\PaymentService;
use App\Http\Services\V1\ProfileSearchService;
use App\Http\Services\V1\TicketService;
use App\Models\Graduate;
use App\Models\University;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\View\View;

class RhController extends Controller
{
    public function __construct(
        private ProfileSearchService $profileSearchService,
        private TicketService $ticketService,
        private CategoryService $categoryService,
        private PaymentService $paymentService,
        private LogService $logService,
    ) {}

    public function index(): View
    {
        $user = Auth::user();
        $searches = $user->profileSearches()
            ->with(['graduate', 'university'])
            ->latest('created_at')
            ->get();
        $universities = University::where('status', 'active')->orderBy('name')->get();
        $categories = $this->categoryService->getAllCategories()->where('status', 'active');
        $tickets = $user->tickets()->with('category')->latest()->get();
        $balance = $user->ticketBalance();

        return view('rh.index', compact('user', 'searches', 'universities', 'categories', 'tickets', 'balance'));
    }

    public function search(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'query' => ['required', 'string', 'min:2', 'max:120'],
            'university_id' => ['nullable', 'exists:universities,id'],
        ]);

        $user = Auth::user();

        if ($user->ticketBalance() < 1) {
            return back()->with('error', 'Aucun ticket disponible. Achetez des tickets pour lancer une recherche.');
        }

        $query = Graduate::query()->with('university');

        if (! empty($data['university_id'])) {
            $query->where('university_id', $data['university_id']);
        }

        $term = $data['query'];
        $graduate = $query->where(function ($q) use ($term) {
            $q->where('matricule', 'like', '%'.$term.'%')
                ->orWhere('name', 'like', '%'.$term.'%');
        })->first();

        if (! $graduate) {
            return back()->with('error', 'Aucun diplômé trouvé pour cette recherche. Aucun ticket n’a été consommé.');
        }

        $ticket = $user->tickets()
            ->where('status', 'approved')
            ->latest()
            ->first();

        if (! $ticket) {
            return back()->with('error', 'Aucun ticket approuvé disponible.');
        }

        $this->profileSearchService->createProfileSearch([
            'rh_user_id' => $user->id,
            'graduate_id' => $graduate->id,
            'university_id' => $graduate->university_id,
            'ticket_id' => $ticket->id,
        ]);

        $this->logService->createLog([
            'user_id' => $user->id,
            'action' => 'SEARCH',
            'description' => 'Recherche diplômé '.$graduate->matricule.' — profil trouvé',
            'ip_address' => $request->ip(),
            'user_agent' => (string) $request->userAgent(),
        ]);

        return back()->with('success', 'Profil trouvé : '.$graduate->name.' ('.$graduate->matricule.'). 1 ticket consommé.');
    }

    public function buyTickets(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'category_id' => ['required', 'exists:categories,id'],
        ]);

        $user = Auth::user();
        $category = $this->categoryService->getCategoryById((int) $data['category_id']);
        $quantity = (int) ($category->quantity ?: 1);

        DB::transaction(function () use ($user, $category, $quantity) {
            $ticket = $this->ticketService->createTicket([
                'user_id' => $user->id,
                'category_id' => $category->id,
                'quantity' => $quantity,
                'status' => 'approved',
            ]);

            $this->paymentService->createPayment([
                'user_id' => $user->id,
                'category_id' => $category->id,
                'amount' => $quantity * 5,
                'reference' => 'PAY-'.Str::upper(Str::random(10)),
                'status' => 'completed',
                'payment_date' => now(),
            ]);

            unset($ticket);
        });

        $this->logService->createLog([
            'user_id' => $user->id,
            'action' => 'PAYMENT',
            'description' => 'Achat de '.$quantity.' ticket(s) — catégorie '.$category->name,
            'ip_address' => $request->ip(),
            'user_agent' => (string) $request->userAgent(),
        ]);

        return back()->with('success', $quantity.' ticket(s) ajouté(s) à votre solde.');
    }

    public function profile(): View
    {
        return view('rh.profile', ['user' => Auth::user()]);
    }

    public function updateProfile(Request $request): RedirectResponse
    {
        $user = Auth::user();
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'phone' => ['nullable', 'string', 'max:20', 'unique:users,phone,'.$user->id],
        ]);

        $user->update($data);

        return back()->with('success', 'Profil mis à jour.');
    }
}
