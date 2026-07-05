<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\Concerns\VerifiesParentAccess;
use App\Http\Controllers\Controller;
use App\Http\Resources\EleveResource;
use App\Models\Eleve;
use App\Models\Note;
use App\Services\MoyenneService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EnfantController extends Controller
{
    use VerifiesParentAccess;

    // Liste des enfants du parent connecté (utile s'il en a plusieurs)
    public function index()
    {
        $enfants = Auth::user()->enfants()->with('classe')->get();

        return EleveResource::collection($enfants);
    }

    // Tableau de bord d'un enfant : infos de base, moyenne générale,
    // rang dans la classe, résumé des dernières notes.
    public function show(Request $request, Eleve $eleve)
    {
        $this->assertEnfantAutorise($eleve);

        $eleve->load('classe');
        $annee = config('app.annee_scolaire');
        $trimestre = $request->trimestre ?? 1;

        $moyenne = MoyenneService::moyenneEleve($eleve, $trimestre, $annee);
        ['rang' => $rang, 'total_eleves' => $totalEleves] = MoyenneService::rangEleve($eleve, $trimestre, $annee);

        $dernieresNotes = Note::where('eleve_id', $eleve->id)
                              ->where('annee_scolaire', $annee)
                              ->with('matiere')
                              ->orderByDesc('created_at')
                              ->take(5)
                              ->get()
                              ->map(fn($note) => [
                                  'matiere'   => $note->matiere->nom,
                                  'valeur'    => (float) $note->valeur,
                                  'bareme'    => $note->matiere->bareme,
                                  'trimestre' => (int) $note->trimestre,
                                  'date'      => $note->created_at->format('Y-m-d'),
                              ]);

        return response()->json([
            'eleve'           => new EleveResource($eleve),
            'trimestre'       => (int) $trimestre,
            'annee_scolaire'  => $annee,
            'moyenne_generale'=> $moyenne,
            'rang'            => $rang,
            'total_eleves'    => $totalEleves,
            'dernieres_notes' => $dernieresNotes,
        ]);
    }
}
