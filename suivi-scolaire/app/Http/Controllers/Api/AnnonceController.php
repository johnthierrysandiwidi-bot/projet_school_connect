<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\AnnonceResource;
use App\Models\Annonce;
use App\Models\AnnonceLecture;
use Illuminate\Support\Facades\Auth;

class AnnonceController extends Controller
{
    // Annonces visibles par le parent : celles de toute l'école (classe_id
    // nul) + celles publiées pour la classe d'un de ses enfants.
    public function index()
    {
        $user = Auth::user();
        $classeIds = $user->enfants()->pluck('classe_id')->unique();

        $annonces = Annonce::where(function ($q) use ($classeIds) {
                $q->whereNull('classe_id')
                  ->orWhereIn('classe_id', $classeIds);
            })
            ->with('classe')
            ->orderByDesc('date_publication')
            ->get();

        $lues = AnnonceLecture::where('user_id', $user->id)
                              ->pluck('annonce_id')
                              ->all();

        $annonces->each(fn($a) => $a->lu = in_array($a->id, $lues, true));

        return response()->json([
            'non_lues' => $annonces->where('lu', false)->count(),
            'annonces' => AnnonceResource::collection($annonces),
        ]);
    }

    // Marque une annonce comme lue par le parent connecté.
    public function marquerLue(Annonce $annonce)
    {
        AnnonceLecture::firstOrCreate(
            ['annonce_id' => $annonce->id, 'user_id' => Auth::id()],
            ['lu_at' => now()]
        );

        return response()->json(['message' => 'Annonce marquée comme lue.']);
    }
}
