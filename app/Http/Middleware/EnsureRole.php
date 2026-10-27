<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureRole
{
    /**
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        if (! $user || ! $user->status) {
            abort(403, 'Compte inactif ou non authentifié.');
        }

        $user->loadMissing('role');
        $roleName = strtolower((string) $user->role?->name);

        $allowed = array_map('strtolower', $roles);

        if (! in_array($roleName, $allowed, true)) {
            abort(403, 'Accès réservé à un autre rôle.');
        }

        return $next($request);
    }
}
