<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreEnseignantRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name'      => 'required|string|max:100',
            'email'     => 'required|email|unique:users,email',
            'password'  => 'required|min:6',
            'classe_id' => 'required|exists:classes,id',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required'      => 'Le nom est obligatoire.',
            'email.required'     => 'L\'email est obligatoire.',
            'email.email'        => 'L\'email doit être une adresse valide.',
            'email.unique'       => 'Cet email est déjà utilisé.',
            'password.required'  => 'Le mot de passe est obligatoire.',
            'password.min'       => 'Le mot de passe doit avoir au moins 6 caractères.',
            'classe_id.required' => 'La classe est obligatoire.',
            'classe_id.exists'   => 'La classe sélectionnée est invalide.',
        ];
    }
}
