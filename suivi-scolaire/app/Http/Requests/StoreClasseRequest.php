<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreClasseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'niveau'                  => 'required|in:CP1,CP2,CE1,CE2,CM1,CM2',
            'nom'                     => [
                'required',
                'string',
                'max:50',
                // Le nom distingue les classes d'un même niveau (ex. CP1 A /
                // CP1 B) : il doit donc être unique pour l'année scolaire en
                // cours, mais le même niveau peut être réutilisé librement.
                Rule::unique('classes', 'nom')->where('annee_scolaire', config('app.annee_scolaire')),
            ],
            'frais_scolarite'         => 'required|numeric|min:0',
            'capacite_max'            => 'required|integer|min:1|max:100',
            'matieres'                => 'nullable|array',
            'matieres.*.nom'          => 'nullable|string|max:100',
            'matieres.*.coefficient'  => 'nullable|numeric|min:0.5|max:5',
            'matieres.*.bareme'       => 'nullable|in:10,20',
        ];
    }

    public function messages(): array
    {
        return [
            'niveau.required'          => 'Le niveau est obligatoire.',
            'niveau.in'                 => 'Le niveau sélectionné est invalide.',
            'nom.required'              => 'Le nom de la classe est obligatoire.',
            'nom.unique'                => 'Une classe porte déjà ce nom cette année — choisis-en un autre (ex. « CP1 B »).',
            'frais_scolarite.required' => 'Les frais sont obligatoires.',
            'capacite_max.required'    => 'La capacité est obligatoire.',
        ];
    }
}
