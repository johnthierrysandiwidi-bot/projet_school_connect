<?php

namespace App\Http\Requests;

use App\Models\Matiere;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class StoreNoteRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'trimestre'      => 'required|integer|in:1,2,3',
            'annee_scolaire' => 'required|string',
            'notes'          => 'nullable|array',
            'notes.*.*'      => 'nullable|numeric|min:0',
        ];
    }

    public function messages(): array
    {
        return [
            'notes.*.*.numeric' => 'Une note doit être un nombre.',
            'notes.*.*.min'     => 'Une note ne peut pas être inférieure à 0.',
        ];
    }

    /**
     * Chaque matière a son propre barème (10 ou 20) : une note ne doit
     * jamais dépasser le barème de SA matière, pas une limite fixe de 20.
     */
    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            $notes = $this->input('notes', []);
            if (empty($notes)) {
                return;
            }

            $matiereIds = collect($notes)->flatMap(fn ($parMatiere) => array_keys($parMatiere))->unique();
            $baremes = Matiere::whereIn('id', $matiereIds)->pluck('bareme', 'id');

            foreach ($notes as $eleveId => $parMatiere) {
                foreach ($parMatiere as $matiereId => $valeur) {
                    if ($valeur === null || $valeur === '') {
                        continue;
                    }

                    $bareme = $baremes->get($matiereId);
                    if ($bareme !== null && (float) $valeur > $bareme) {
                        $validator->errors()->add(
                            "notes.{$eleveId}.{$matiereId}",
                            "Cette note ne peut pas dépasser {$bareme} (barème de cette matière)."
                        );
                    }
                }
            }
        });
    }
}
