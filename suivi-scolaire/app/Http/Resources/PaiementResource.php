<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PaiementResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'             => $this->id,
            'reference'      => $this->reference,
            'montant'        => (float) $this->montant,
            'date_paiement'  => $this->date_paiement->format('Y-m-d'),
            'mode_paiement'  => $this->mode_paiement,
        ];
    }
}
