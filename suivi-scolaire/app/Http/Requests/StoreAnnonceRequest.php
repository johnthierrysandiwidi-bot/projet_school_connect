<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreAnnonceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'titre'            => 'required|string|max:150',
            'contenu'          => 'required|string|max:2000',
            'type'             => 'required|in:info,examen,reunion,paiement',
            'classe_id'        => 'nullable|exists:classes,id',
            'date_publication' => 'required|date',
        ];
    }

    public function messages(): array
    {
        return [
            'titre.required'            => 'Le titre est obligatoire.',
            'contenu.required'          => 'Le contenu est obligatoire.',
            'type.required'             => "Le type d'annonce est obligatoire.",
            'date_publication.required' => 'La date de publication est obligatoire.',
        ];
    }
}
