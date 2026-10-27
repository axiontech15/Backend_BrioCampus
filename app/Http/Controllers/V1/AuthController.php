<?php

namespace App\Http\Controllers\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\V1\LoginRequest;
use App\Http\Services\V1\LogService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthController extends Controller
{
    public function __construct(private LogService $logService) {}

    public function index(): View|RedirectResponse
    {
        if (Auth::check()) {
            return redirect()->intended($this->dashboardFor(Auth::user()));
        }

        return view('auth.login');
    }

    public function login(LoginRequest $request): RedirectResponse
    {
        $credentials = $request->only('email', 'password');
        $remember = $request->boolean('remember');

        if (! Auth::attempt($credentials, $remember)) {
            return back()->withErrors([
                'email' => 'Identifiants incorrects. Vérifiez votre e-mail et votre mot de passe.',
            ])->onlyInput('email');
        }

        $request->session()->regenerate();
        $user = Auth::user();

        if (! $user->status) {
            Auth::logout();

            return back()->withErrors([
                'email' => 'Ce compte est suspendu. Contactez l’administrateur.',
            ])->onlyInput('email');
        }

        $this->logService->createLog([
            'user_id' => $user->id,
            'action' => 'LOGIN',
            'description' => 'Connexion réussie',
            'ip_address' => $request->ip(),
            'user_agent' => (string) $request->userAgent(),
        ]);

        return redirect()->intended($this->dashboardFor($user));
    }

    public function logout(Request $request): RedirectResponse
    {
        if ($user = Auth::user()) {
            $this->logService->createLog([
                'user_id' => $user->id,
                'action' => 'LOGOUT',
                'description' => 'Déconnexion',
                'ip_address' => $request->ip(),
                'user_agent' => (string) $request->userAgent(),
            ]);
        }

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('auth.login');
    }

    public function forgotPassword(): View
    {
        return view('auth.forgot-password');
    }

    private function dashboardFor($user): string
    {
        return match ($user->roleName()) {
            'admin' => route('admin.index'),
            'rh' => route('rh.index'),
            'university', 'universite' => route('university.index'),
            default => route('home'),
        };
    }
}
