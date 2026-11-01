<?php

namespace App\Http\Controllers\V1;

use App\Http\Controllers\Controller;
use App\Http\Services\V1\CategoryService;
use App\Http\Services\V1\LogService;
use App\Http\Services\V1\PaymentService;
use App\Http\Services\V1\ProfileSearchService;
use App\Http\Services\V1\TicketService;
use App\Models\Graduate;
use App\Models\ProfileSearch;
use App\Models\Ticket;
use App\Models\University;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Response;

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

    public function show(ProfileSearch $profileSearch, Request $request): View
    {
        abort_unless((int) $profileSearch->rh_user_id === (int) Auth::id(), 404);

        $profileSearch->load(['graduate', 'university']);

        return view('rh.searches.show', [
            'profileSearch' => $profileSearch,
            'user' => Auth::user(),
            'autoPrint' => $request->boolean('print'),
        ]);
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

    public function downloadTicket(Ticket $ticket): Response
    {
        $user = Auth::user();
        abort_unless((int) $ticket->user_id === (int) $user->id, 403);

        $ticket->loadMissing('category');
        $reference = 'BC-TKT-'.$ticket->created_at?->format('Y').'-'.str_pad((string) $ticket->id, 6, '0', STR_PAD_LEFT);
        $pdf = $this->buildTicketPdf($ticket, $user->name, $reference);
        $filename = 'ticket-brio-campus-'.$ticket->id.'.pdf';

        return response($pdf, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="'.$filename.'"',
            'Content-Length' => strlen($pdf),
        ]);
    }

    private function buildTicketPdf(Ticket $ticket, string $holderName, string $reference): string
    {
        $text = fn (string $value): string => $this->sanitizePdfText($value);
        $packName = $ticket->category?->name ?? 'Pack Brio Campus';
        $quantity = (int) $ticket->quantity;
        $issuedAt = $ticket->created_at?->format('d/m/Y') ?? now()->format('d/m/Y');

        $stream = "q\n0.12 0.25 0.65 rg 0 762 595 80 re f Q\n";
        $stream .= "BT /F1 22 Tf 1 1 1 rg 42 806 Td ({$text('BRIO CAMPUS')}) Tj ET\n";
        $stream .= "BT /F1 10 Tf 0.78 0.86 1 rg 44 789 Td ({$text('PLATEFORME DE VERIFICATION DES DIPLAMES')}) Tj ET\n";
        $stream .= "q 0.90 0.93 0.98 rg 42 565 511 155 re f 0.82 0.86 0.94 RG 42 565 511 155 re S Q\n";
        $stream .= "BT /F1 11 Tf 0.30 0.38 0.56 rg 68 690 Td ({$text('TICKET DE VERIFICATION')}) Tj ET\n";
        $stream .= "BT /F1 30 Tf 0.07 0.18 0.46 rg 68 635 Td ({$text((string) $quantity.' verification'.($quantity > 1 ? 's' : ''))}) Tj ET\n";
        $stream .= "BT /F1 13 Tf 0.30 0.38 0.56 rg 68 605 Td ({$text($packName)}) Tj ET\n";
        $stream .= "BT /F1 10 Tf 0.30 0.38 0.56 rg 68 580 Td ({$text('Reference : '.$reference)}) Tj ET\n";
        $stream .= "BT /F1 10 Tf 0.30 0.38 0.56 rg 68 490 Td ({$text('ATTRIBUE A')}) Tj ET\n";
        $stream .= "BT /F1 16 Tf 0.06 0.12 0.36 rg 68 462 Td ({$text($holderName)}) Tj ET\n";
        $stream .= "BT /F1 10 Tf 0.30 0.38 0.56 rg 68 430 Td ({$text('Statut : APPROUVE')}) Tj ET\n";
        $stream .= "BT /F1 10 Tf 0.30 0.38 0.56 rg 68 410 Td ({$text('Emis le : '.$issuedAt)}) Tj ET\n";
        $stream .= "BT /F1 10 Tf 0.30 0.38 0.56 rg 68 300 Td ({$text('1 ticket correspond a 1 verification de diplome.')}) Tj ET\n";
        $stream .= "BT /F1 10 Tf 0.30 0.38 0.56 rg 68 280 Td ({$text('Presentez ce document lors de vos operations sur Brio Campus.')}) Tj ET\n";
        $stream .= "BT /F1 9 Tf 0.48 0.53 0.65 rg 68 100 Td ({$text('Brio Campus - Document officiel - '.$issuedAt)}) Tj ET\n";

        $objects = [];
        $objects[] = '<< /Type /Catalog /Pages 2 0 R >>';
        $objects[] = '<< /Type /Pages /Kids [3 0 R] /Count 1 >>';
        $objects[] = '<< /Type /Page /Parent 2 0 R /MediaBox [0 0 595 842] /Resources << /Font << /F1 4 0 R >> >> /Contents 5 0 R >>';
        $objects[] = '<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica >>';
        $objects[] = '<< /Length '.strlen($stream)." >>\nstream\n{$stream}\nendstream";

        $pdf = "%PDF-1.4\n";
        $offsets = [0];
        foreach ($objects as $index => $object) {
            $offsets[] = strlen($pdf);
            $pdf .= ($index + 1)." 0 obj\n{$object}\nendobj\n";
        }
        $xrefOffset = strlen($pdf);
        $pdf .= "xref\n0 ".(count($objects) + 1)."\n";
        $pdf .= "0000000000 65535 f \n";
        foreach (array_slice($offsets, 1) as $offset) {
            $pdf .= str_pad((string) $offset, 10, '0', STR_PAD_LEFT).' 00000 n '."\n";
        }
        $pdf .= "trailer\n<< /Size ".(count($objects) + 1).' /Root 1 0 R >>'."\n";
        $pdf .= "startxref\n{$xrefOffset}\n%%EOF";

        return $pdf;
    }

    private function sanitizePdfText(string $value): string
    {
        $ascii = @iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $value);
        $ascii = $ascii === false ? $value : $ascii;

        return str_replace(['\\', '(', ')'], ['\\\\', '\\(', '\\)'], $ascii);
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
