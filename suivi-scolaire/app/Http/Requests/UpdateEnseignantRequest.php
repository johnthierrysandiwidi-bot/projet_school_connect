<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateEnseignantRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge(['is_active' => $this->has('is_active')]);
    }

    public function rules(): array
    {
        $enseignant = $this->route('enseignant');

        return [
            'name'      => 'required|string|max:100',
            'email'     => ['required', 'email', Rule::unique('users', 'email')->ignore($enseignant)],
            'password'  => 'nullable|min:6',
            'classe_id' => 'required|exists:classes,id',
            'is_active' => 'required|boolean',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required'     => 'Le nom est obligatoire.',
            'email.required'    => "L'email est obligatoire.",
            'email.unique'      => 'Cet email est déjà utilisé.',
            'password.min'      => 'Le mot de passe doit avoir au moins 6 caractères.',
            'classe_id.required'=> 'La classe est obligatoire.',
        ];
    }
}