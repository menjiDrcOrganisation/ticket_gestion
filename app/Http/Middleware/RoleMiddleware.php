<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        $user = $request->user();

        // Vérifie que l'utilisateur est connecté
        if (! $user) {
            return redirect()->route('login');
        }

        // Vérifie que le rôle de l'utilisateur est autorisé
        if (! in_array($user->role, $roles)) {
            abort(403, 'Accès refusé : vous n’avez pas les permissions nécessaires.');
        }

        return $next($request);
    }
}
