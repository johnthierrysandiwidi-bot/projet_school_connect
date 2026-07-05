<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePaiementRequest;
use App\Models\Paiement;
use App\Models\Eleve;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Barryvdh\DomPDF\Facade\Pdf;

class PaiementController extends Controller
{
    // Liste des paiements
    public function index()
    {
        $paiements = Paiement::with(['eleve.classe', 'user'])
                             ->orderBy('created_at', 'desc')
                             ->paginate(20);

        return view('admin.paiements.index', compact('paiements'));
    }

    // Formulaire de paiement
    public function create(Request $request)
    {
        $eleve = null;
        if ($request->eleve_id) {
            $eleve = Eleve::with(['classe', 'paiements'])->findOrFail($request->eleve_id);
        }

        $eleves = Eleve::with('classe')
                       ->where('statut', 'actif')
                       ->orderBy('nom')
                       ->get();

        return view('admin.paiements.create', compact('eleve', 'eleves'));
    }

    // Enregistrer un paiement
    public function store(StorePaiementRequest $request)
    {
        $validated = $request->validated();

        $validated['reference'] = Paiement::genererReference();
        $validated['user_id']   = Auth::id();

        $paiement = Paiement::create($validated);

        return redirect()
            ->route('paiements.recu', $paiement)
            ->with('success', "Paiement enregistré ! Référence : {$paiement->reference}");
    }

    // Voir un paiement : la fiche détaillée d'un paiement et son reçu sont la
    // même page, on évite donc une vue dupliquée.
    public function show(Paiement $paiement)
    {
        return redirect()->route('paiements.recu', $paiement);
    }

    // Afficher le reçu
    public function recu(Paiement $paiement)
    {
        $paiement->load(['eleve.classe', 'user']);
        return view('admin.paiements.recu', compact('paiement'));
    }

    // Télécharger le reçu en PDF
    public function recuPdf(Paiement $paiement)
    {
        $paiement->load(['eleve.classe', 'user']);
        $pdf = Pdf::loadView('admin.paiements.recu-pdf', compact('paiement'));
        $pdf->setPaper('A5', 'portrait');
        return $pdf->download("recu-{$paiement->reference}.pdf");
    }
}