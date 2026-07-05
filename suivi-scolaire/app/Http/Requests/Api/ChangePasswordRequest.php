<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class ChangePasswordRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'current_password' => 'required|string',
            'password'          => 'required|string|min:6|confirmed',
        ];
    }

    public function messages(): array
    {
        return [
            'current_password.required' => 'Le mot de passe actuel est obligatoire.',
            'password.required'         => 'Le nouveau mot de passe est obligatoire.',
            'password.min'              => 'Le nouveau mot de passe doit contenir au moins 6 caractères.',
            'password.confirmed'        => 'La confirmation ne correspond pas au nouveau mot de passe.',
        ];
    }
}
