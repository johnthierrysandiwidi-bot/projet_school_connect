<?php

namespace App\Http\Controllers\Api\Concerns;

use App\Models\Eleve;
use Illuminate\Support\Facades\Auth;

/**
 * Un parent ne doit jamais pouvoir consulter les données d'un élève qui
 * n'est pas le sien, même en changeant l'identifiant dans l'URL de l'API.
 */
trait VerifiesParentAccess
{
    protected function assertEnfantAutorise(Eleve $eleve): void
    {
        $estSonEnfant = Auth::user()
            ->enfants()
            ->where('eleves.id', $eleve->id)
            ->exists();

        abort_unless($estSonEnfant, 403, "Cet élève n'est pas associé à votre compte.");
    }
}
