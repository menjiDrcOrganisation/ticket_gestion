<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Tant qu'un utilisateur utilise un mot de passe temporaire (must_change_password),
 * il est redirigé vers la page de changement de mot de passe.
 */
class EnsurePasswordIsChanged
{
    /**
     * Routes accessibles pendant que le changement de mot de passe est en attente.
     */
    private const ROUTES_AUTORISEES = [
        'password.change',
        'password.update',
        'logout',
    ];

    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (!$user || !$user->must_change_password || $request->routeIs(...self::ROUTES_AUTORISEES)) {
            return $next($request);
        }

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Vous devez changer votre mot de passe temporaire avant de continuer.',
            ], 403);
        }

        return redirect()->route('password.change');
    }
}
