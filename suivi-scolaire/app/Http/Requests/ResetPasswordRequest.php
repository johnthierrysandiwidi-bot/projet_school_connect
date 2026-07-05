<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ResetPasswordRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'token'    => 'required|string',
            'email'    => 'required|email',
            'password' => 'required|string|min:6|confirmed',
        ];
    }

    public function messages(): array
    {
        return [
            'email.required'    => "L'email est obligatoire.",
            'password.required' => 'Le mot de passe est obligatoire.',
            'password.min'      => 'Le mot de passe doit contenir au moins 6 caractères.',
            'password.confirmed'=> 'La confirmation ne correspond pas au mot de passe.',
        ];
    }
}
