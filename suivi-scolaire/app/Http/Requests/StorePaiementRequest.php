<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePaiementRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'eleve_id'      => 'required|exists:eleves,id',
            'montant'       => 'required|numeric|min:1',
            'date_paiement' => 'required|date',
            'mode_paiement' => 'required|in:espèces,mobile_money,virement,chèque',
            'observation'   => 'nullable|string|max:500',
        ];
    }

    public function messages(): array
    {
        return [
            'eleve_id.required'      => 'Veuillez sélectionner un élève.',
            'eleve_id.exists'        => 'L\'élève sélectionné est invalide.',
            'montant.required'       => 'Le montant est obligatoire.',
            'montant.min'            => 'Le montant doit être supérieur à 0.',
            'date_paiement.required' => 'La date est obligatoire.',
            'mode_paiement.required' => 'Le mode de paiement est obligatoire.',
        ];
    }
}
