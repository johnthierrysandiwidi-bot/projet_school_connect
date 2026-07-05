<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AbsenceResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'            => $this->id,
            'date_absence'  => $this->date_absence->format('Y-m-d'),
            'justifiee'     => $this->justifiee,
            'motif'         => $this->motif,
        ];
    }
}
