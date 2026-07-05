<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateParentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $parent = $this->route('parent');

        return [
            'name'        => 'required|string|max:100',
            'email'       => ['required', 'email', Rule::unique('users', 'email')->ignore($parent)],
            'password'    => 'nullable|min:6',
            'enfants'     => 'required|array|min:1',
            'enfants.*'   => 'exists:eleves,id',
            'is_active'   => 'boolean',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required'    => 'Le nom est obligatoire.',
            'email.required'   => "L'email est obligatoire.",
            'email.unique'     => 'Cet email est déjà utilisé.',
            'password.min'     => 'Le mot de passe doit avoir au moins 6 caractères.',
            'enfants.required' => 'Sélectionnez au moins un enfant.',
            'enfants.min'      => 'Sélectionnez au moins un enfant.',
        ];
    }
}
