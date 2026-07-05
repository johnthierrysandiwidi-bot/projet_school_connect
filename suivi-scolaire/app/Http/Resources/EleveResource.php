<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EleveResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'          => $this->id,
            'matricule'   => $this->matricule,
            'nom'         => $this->nom,
            'prenom'      => $this->prenom,
            'nom_complet' => $this->nom_complet,
            'sexe'        => $this->sexe,
            'date_naissance' => $this->date_naissance?->format('Y-m-d'),
            'photo_url'   => $this->photo_url,
            'classe'      => $this->whenLoaded('classe', fn () => [
                'id'     => $this->classe->id,
                'niveau' => $this->classe->niveau,
                'nom'    => $this->classe->nom,
            ]),
        ];
    }
}
