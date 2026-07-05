<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateParametresRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'annee_scolaire_active' => 'required|string|regex:/^\d{4}-\d{4}$/',
            'nom_ecole'             => 'required|string|max:150',
            'adresse_ecole'         => 'nullable|string|max:200',
            'telephone_ecole'       => 'nullable|string|max:50',
        ];
    }

    public function messages(): array
    {
        return [
            'annee_scolaire_active.required' => "L'année scolaire est obligatoire.",
            'annee_scolaire_active.regex'    => 'Le format attendu est AAAA-AAAA, par exemple 2026-2027.',
            'nom_ecole.required'             => "Le nom de l'établissement est obligatoire.",
        ];
    }
}
