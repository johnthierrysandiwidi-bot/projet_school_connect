<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\Concerns\VerifiesParentAccess;
use App\Http\Controllers\Controller;
use App\Http\Resources\PaiementResource;
use App\Models\Eleve;
use App\Models\Paiement;
use Barryvdh\DomPDF\Facade\Pdf;

class PaiementController extends Controller
{
    use VerifiesParentAccess;

    // Historique des versements, montant payé et reste à payer.
    public function index(Eleve $eleve)
    {
        $this->assertEnfantAutorise($eleve);

        $paiements = $eleve->paiements()->orderByDesc('date_paiement')->get();

        return response()->json([
            'frais_total'     => (float) ($eleve->classe->frais_scolarite ?? 0),
            'montant_paye'    => (float) $eleve->montant_paye,
            'reste_a_payer'   => (float) $eleve->reste_a_payer,
            'paiements'       => PaiementResource::collection($paiements),
        ]);
    }

    // Téléchargement du reçu PDF d'un paiement précis de cet enfant.
    public function recu(Eleve $eleve, Paiement $paiement)
    {
        $this->assertEnfantAutorise($eleve);

        abort_unless($paiement->eleve_id === $eleve->id, 404);

        $paiement->load(['eleve.classe', 'user']);
        $pdf = Pdf::loadView('admin.paiements.recu-pdf', compact('paiement'));
        $pdf->setPaper('A5', 'portrait');

        return $pdf->download("recu-{$paiement->reference}.pdf");
    }
}
