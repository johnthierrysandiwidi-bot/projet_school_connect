<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Matiere extends Model
{
    use HasFactory;

    protected $fillable = [
        'nom', 'code', 'coefficient', 'bareme',
        'classe_id', 'is_active',
    ];

    protected $casts = [
        'coefficient' => 'decimal:1',
        'bareme'      => 'integer',
        'is_active'   => 'boolean',
    ];

    // Une matière appartient à une classe
    public function classe()
    {
        return $this->belongsTo(Classe::class);
    }

    // Une matière a plusieurs notes
    public function notes()
    {
        return $this->hasMany(Note::class);
    }
}