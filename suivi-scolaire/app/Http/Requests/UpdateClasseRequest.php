<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateClasseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nom'                                => [
                'required',
                'string',
                'max:50',
                Rule::unique('classes', 'nom')
                    ->where('annee_scolaire', $this->route('classe')->annee_scolaire)
                    ->ignore($this->route('classe')->id),
            ],
            'frais_scolarite'                  => 'required|numeric|min:0',
            'capacite_max'                      => 'required|integer|min:1|max:100',
            'matieres'                          => 'nullable|array',
            'matieres.*.nom'                    => 'nullable|string|max:100',
            'matieres.*.coefficient'            => 'nullable|numeric|min:0.5|max:5',
            'matieres.*.bareme'                 => 'nullable|in:10,20',
            'nouvelles_matieres'                => 'nullable|array',
            'nouvelles_matieres.*.nom'          => 'nullable|string|max:100',
            'nouvelles_matieres.*.coefficient'  => 'nullable|numeric|min:0.5|max:5',
            'nouvelles_matieres.*.bareme'       => 'nullable|in:10,20',
        ];
    }

    public function messages(): array
    {
        return [
            'nom.required'              => 'Le nom de la classe est obligatoire.',
            'nom.unique'                => 'Une autre classe porte déjà ce nom cette année.',
            'frais_scolarite.required' => 'Les frais sont obligatoires.',
            'capacite_max.required'    => 'La capacité est obligatoire.',
        ];
    }
}
