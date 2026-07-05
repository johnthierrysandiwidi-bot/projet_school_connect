<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Classe extends Model
{
    use HasFactory;

    protected $table = 'classes';

    // Colonnes qu'on peut remplir
    protected $fillable = [
        'nom',
        'niveau',
        'frais_scolarite',
        'annee_scolaire',
        'capacite_max',
    ];

    // Une classe a plusieurs élèves
    public function eleves()
    {
        return $this->hasMany(Eleve::class);
    }

    // Une classe a plusieurs matières
    public function matieres()
    {
        return $this->hasMany(Matiere::class);
    }

    // Une classe a plusieurs devoirs
    public function devoirs()
    {
        return $this->hasMany(Devoir::class);
    }
}