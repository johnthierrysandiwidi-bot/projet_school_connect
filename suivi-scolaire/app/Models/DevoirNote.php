<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DevoirNote extends Model
{
    use HasFactory;

    protected $fillable = [
        'devoir_id', 'eleve_id', 'valeur', 'remarque',
    ];

    protected $casts = [
        'valeur' => 'decimal:2',
    ];

    // Une note de devoir appartient à un devoir
    public function devoir()
    {
        return $this->belongsTo(Devoir::class);
    }

    // Une note de devoir appartient à un élève
    public function eleve()
    {
        return $this->belongsTo(Eleve::class);
    }
}
