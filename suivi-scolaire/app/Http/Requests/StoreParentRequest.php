<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreParentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name'        => 'required|string|max:100',
            'email'       => 'required|email|unique:users,email',
            'password'    => 'required|min:6',
            'enfants'     => 'required|array|min:1',
            'enfants.*'   => 'exists:eleves,id',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required'     => 'Le nom est obligatoire.',
            'email.required'    => "L'email est obligatoire.",
            'email.unique'      => 'Cet email est déjà utilisé.',
            'password.required' => 'Le mot de passe est obligatoire.',
            'password.min'      => 'Le mot de passe doit avoir au moins 6 caractères.',
            'enfants.required'  => 'Sélectionnez au moins un enfant.',
            'enfants.min'       => 'Sélectionnez au moins un enfant.',
        ];
    }
}
