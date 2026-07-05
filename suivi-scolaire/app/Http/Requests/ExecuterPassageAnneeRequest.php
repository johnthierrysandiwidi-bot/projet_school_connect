<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ExecuterPassageAnneeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'classe_id'                          => 'required|exists:classes,id',
            'classe_destination_promotion'       => 'nullable|exists:classes,id',
            'classe_destination_redoublement'    => 'nullable|exists:classes,id',
            'decisions'                           => 'required|array|min:1',
            'decisions.*'                         => 'required|in:promouvoir,redoubler,quitter',
        ];
    }

    public function messages(): array
    {
        return [
            'classe_id.required' => 'La classe est obligatoire.',
            'decisions.required' => 'Aucune décision à traiter.',
            'decisions.min'      => 'Aucune décision à traiter.',
        ];
    }
}
