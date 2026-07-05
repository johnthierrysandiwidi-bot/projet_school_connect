<?php

namespace App\Services;

use App\Models\Eleve;
use App\Models\Note;

/**
 * Centralise le calcul de la moyenne pondérée (par coefficient) et du rang
 * dans la classe, pour qu'il soit identique partout dans l'application
 * (application web Gestionnaire/Enseignant et API mobile Parent).
 */
class MoyenneService
{
    // Moyenne pondérée d'un élève pour un trimestre donné, toujours sur 10
    // (chaque note est d'abord ramenée sur 10 selon le barème propre à sa
    // matière — 10 ou 20 — avant d'être pondérée par le coefficient), ou
    // null si aucune note n'a encore été saisie.
    public static function moyenneEleve(Eleve $eleve, int $trimestre, string $annee): ?float
    {
        $notes = Note::where('eleve_id', $eleve->id)
                     ->where('trimestre', $trimestre)
                     ->where('annee_scolaire', $annee)
                     ->with('matiere')
                     ->get();

        if ($notes->isEmpty()) {
            return null;
        }

        $totalPoints = $notes->sum(function ($n) {
            $noteSur10 = ($n->valeur / $n->matiere->bareme) * 10;
            return $noteSur10 * $n->matiere->coefficient;
        });
        $totalCoeff  = $notes->sum(fn($n) => $n->matiere->coefficient);

        return $totalCoeff > 0 ? round($totalPoints / $totalCoeff, 2) : null;
    }

    // Classement de tous les élèves actifs d'une classe pour un trimestre,
    // trié par moyenne décroissante (les élèves sans note n'ont pas de rang).
    public static function classement(int $classeId, int $trimestre, string $annee)
    {
        $eleves = Eleve::where('classe_id', $classeId)
                       ->where('annee_scolaire', $annee)
                       ->where('statut', 'actif')
                       ->get();

        $classement = $eleves->map(function (Eleve $eleve) use ($trimestre, $annee) {
            $eleve->moyenne = self::moyenneEleve($eleve, $trimestre, $annee);
            return $eleve;
        })
        ->sortByDesc(fn($e) => $e->moyenne ?? -1)
        ->values();

        $rang = 1;
        foreach ($classement as $eleve) {
            $eleve->rang = $eleve->moyenne !== null ? $rang++ : null;
        }

        return $classement;
    }

    // Rang d'un élève précis + effectif total de sa classe, pour un trimestre.
    public static function rangEleve(Eleve $eleve, int $trimestre, string $annee): array
    {
        $classement = self::classement($eleve->classe_id, $trimestre, $annee);

        $place = $classement->search(fn($e) => $e->id === $eleve->id);
        $eleveClasse = $place !== false ? $classement[$place] : null;

        return [
            'rang'         => $eleveClasse->rang ?? null,
            'total_eleves' => $classement->count(),
        ];
    }
}
