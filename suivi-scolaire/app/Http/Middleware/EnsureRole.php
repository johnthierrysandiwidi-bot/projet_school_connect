<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Restreint l'accès à une route en fonction du rôle de l'utilisateur connecté
 * (gestionnaire ou enseignant), conformément au cahier des charges.
 *
 * Exemple d'utilisation dans les routes :
 *   ->middleware('role:gestionnaire')
 *   ->middleware('role:gestionnaire,enseignant')
 */
class EnsureRole
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        if (! $user || ! in_array($user->role, $roles, true)) {
            abort(403, "Vous n'avez pas l'autorisation d'accéder à cette page.");
        }

        return $next($request);
    }
}
