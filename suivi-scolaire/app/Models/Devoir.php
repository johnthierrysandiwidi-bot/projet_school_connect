<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Devoir extends Model
{
    use HasFactory;

    protected $fillable = [
        'classe_id', 'matiere_id', 'user_id',
        'titre', 'description',
        'date_devoir', 'date_limite',
        'trimestre', 'annee_scolaire', 'noter',
    ];

    protected $casts = [
        'date_devoir' => 'date',
        'date_limite' => 'date',
        'noter'       => 'boolean',
    ];

    // Un devoir appartient à une classe
    public function classe()
    {
        return $this->belongsTo(Classe::class);
    }

    // Un devoir porte sur une matière
    public function matiere()
    {
        return $this->belongsTo(Matiere::class);
    }

    // Un devoir est créé par un enseignant
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Un devoir a une note par élève (si noter = true)
    public function devoirNotes()
    {
        return $this->hasMany(DevoirNote::class);
    }

    // Nombre d'élèves déjà notés pour ce devoir
    public function getNombreNotesAttribute(): int
    {
        return $this->devoirNotes()->whereNotNull('valeur')->count();
    }

    // Moyenne de la classe pour ce devoir
    public function getMoyenneClasseAttribute(): ?float
    {
        $moyenne = $this->devoirNotes()->whereNotNull('valeur')->avg('valeur');
        return $moyenne !== null ? round($moyenne, 2) : null;
    }
}
