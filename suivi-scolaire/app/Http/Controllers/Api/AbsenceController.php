<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\Concerns\VerifiesParentAccess;
use App\Http\Controllers\Controller;
use App\Http\Resources\AbsenceResource;
use App\Models\Eleve;

class AbsenceController extends Controller
{
    use VerifiesParentAccess;

    public function index(Eleve $eleve)
    {
        $this->assertEnfantAutorise($eleve);

        $absences = $eleve->absences()->orderByDesc('date_absence')->get();

        return response()->json([
            'total'     => $absences->count(),
            'absences'  => AbsenceResource::collection($absences),
        ]);
    }
}
