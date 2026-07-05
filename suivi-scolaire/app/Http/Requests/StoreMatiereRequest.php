<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreMatiereRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nom'         => 'required|string|max:100',
            'code'        => 'nullable|string|max:20',
            'coefficient' => 'required|numeric|min:0.5|max:5',
            'bareme'      => 'required|in:10,20',
            'classe_id'   => 'required|exists:classes,id',
        ];
    }

    public function messages(): array
    {
        return [
            'nom.required'         => 'Le nom de la matière est obligatoire.',
            'coefficient.required' => 'Le coefficient est obligatoire.',
            'coefficient.min'      => 'Le coefficient doit être au moins 0.5.',
            'coefficient.max'      => 'Le coefficient ne peut pas dépasser 5.',
            'bareme.required'      => 'Le barème est obligatoire.',
            'bareme.in'            => 'Le barème doit être 10 ou 20.',
            'classe_id.required'   => 'La classe est obligatoire.',
        ];
    }
}
