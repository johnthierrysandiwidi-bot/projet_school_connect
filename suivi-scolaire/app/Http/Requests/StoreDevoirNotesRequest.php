<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreDevoirNotesRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        // Un devoir ne porte que sur une seule matière : on valide donc
        // directement contre le barème de CETTE matière (10 ou 20).
        $bareme = $this->route('devoir')?->matiere?->bareme ?? 20;

        return [
            'notes'      => 'nullable|array',
            'notes.*'    => "nullable|numeric|min:0|max:{$bareme}",
            'remarques'  => 'nullable|array',
            'remarques.*'=> 'nullable|string|max:255',
        ];
    }

    public function messages(): array
    {
        $bareme = $this->route('devoir')?->matiere?->bareme ?? 20;

        return [
            'notes.*.numeric' => 'Une note doit être un nombre.',
            'notes.*.min'     => 'Une note ne peut pas être inférieure à 0.',
            'notes.*.max'     => "Une note ne peut pas dépasser {$bareme} (barème de cette matière).",
        ];
    }
}
