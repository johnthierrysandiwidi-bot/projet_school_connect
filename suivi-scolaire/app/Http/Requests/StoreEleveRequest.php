<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreEleveRequest extends FormRequest
{
    /**
     * L'autorisation d'accès est déjà gérée par le middleware
     * `role:gestionnaire` sur la route.
     */
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nom'              => 'required|string|max:100',
            'prenom'           => 'required|string|max:100',
            'date_naissance'   => 'required|date|before:today',
            'lieu_naissance'   => 'nullable|string|max:150',
            'sexe'             => 'required|in:M,F',
            'nationalite'      => 'nullable|string|max:100',
            'classe_id'        => 'required|exists:classes,id',
            'parent_nom'       => 'required|string|max:100',
            'parent_prenom'    => 'required|string|max:100',
            'parent_telephone' => 'required|string|max:20',
            'parent_telephone2'=> 'nullable|string|max:20',
            'parent_adresse'   => 'nullable|string|max:255',
            'parent_lien'      => 'required|in:père,mère,tuteur,autre',
            'photo'            => 'nullable|image|mimes:jpeg,jpg,png,webp|max:2048',
        ];
    }

    public function messages(): array
    {
        return [
            'nom.required'              => 'Le nom est obligatoire.',
            'prenom.required'           => 'Le prénom est obligatoire.',
            'date_naissance.required'   => 'La date de naissance est obligatoire.',
            'date_naissance.before'     => 'La date de naissance doit être antérieure à aujourd\'hui.',
            'sexe.required'             => 'Le sexe est obligatoire.',
            'classe_id.required'        => 'La classe est obligatoire.',
            'classe_id.exists'          => 'La classe sélectionnée est invalide.',
            'parent_nom.required'       => 'Le nom du parent est obligatoire.',
            'parent_prenom.required'    => 'Le prénom du parent est obligatoire.',
            'parent_telephone.required' => 'Le téléphone est obligatoire.',
            'parent_lien.required'      => 'Le lien de parenté est obligatoire.',
            'photo.image'               => 'Le fichier doit être une image.',
            'photo.mimes'               => 'La photo doit être au format JPEG, PNG ou WEBP.',
            'photo.max'                 => 'La photo ne doit pas dépasser 2 Mo.',
        ];
    }
}
