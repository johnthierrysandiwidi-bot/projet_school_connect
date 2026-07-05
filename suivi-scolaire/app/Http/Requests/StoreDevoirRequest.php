<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreDevoirRequest extends FormRequest
{
    /**
     * L'appartenance à la bonne classe (pour un Enseignant) est vérifiée
     * dans le contrôleur, en plus du middleware de rôle sur la route.
     */
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'classe_id'      => 'required|exists:classes,id',
            'matiere_id'     => 'required|exists:matieres,id',
            'titre'          => 'required|string|max:150',
            'description'    => 'nullable|string|max:1000',
            'date_devoir'    => 'required|date',
            'date_limite'    => 'nullable|date|after_or_equal:date_devoir',
            'trimestre'      => 'required|integer|in:1,2,3',
            'noter'          => 'boolean',
        ];
    }

    public function messages(): array
    {
        return [
            'classe_id.required'   => 'La classe est obligatoire.',
            'matiere_id.required'  => 'La matière est obligatoire.',
            'matiere_id.exists'    => 'La matière sélectionnée est invalide.',
            'titre.required'       => 'Le titre du devoir est obligatoire.',
            'date_devoir.required' => 'La date du devoir est obligatoire.',
            'date_limite.after_or_equal' => 'La date limite doit être postérieure ou égale à la date du devoir.',
            'trimestre.required'   => 'Le trimestre est obligatoire.',
        ];
    }
}
