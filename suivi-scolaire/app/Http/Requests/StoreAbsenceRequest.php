<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreAbsenceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'classe_id'           => 'required|exists:classes,id',
            'date_absence'        => 'required|date|before_or_equal:today',
            'absences'            => 'nullable|array',
            'absences.*.justifiee'=> 'boolean',
            'absences.*.motif'    => 'nullable|string|max:255',
        ];
    }

    public function messages(): array
    {
        return [
            'date_absence.required'        => 'La date est obligatoire.',
            'date_absence.before_or_equal' => "La date ne peut pas être dans le futur.",
        ];
    }
}
