<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Note extends Model
{
    use HasFactory;

    protected $fillable = [
        'eleve_id', 'matiere_id', 'valeur',
        'trimestre', 'annee_scolaire',
        'user_id', 'appreciation',
    ];

    protected $casts = [
        'valeur' => 'decimal:2',
    ];

    // Une note appartient à un élève
    public function eleve()
    {
        return $this->belongsTo(Eleve::class);
    }

    // Une note appartient à une matière
    public function matiere()
    {
        return $this->belongsTo(Matiere::class);
    }

    // Une note est saisie par un enseignant
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // La mention selon la note
    public function getMentionAttribute(): string
    {
        return match(true) {
            $this->valeur >= 18 => 'Excellent',
            $this->valeur >= 16 => 'Très Bien',
            $this->valeur >= 14 => 'Bien',
            $this->valeur >= 12 => 'Assez Bien',
            $this->valeur >= 10 => 'Passable',
            default             => 'Insuffisant',
        };
    }
}