<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Annonce extends Model
{
    use HasFactory;

    protected $fillable = [
        'titre', 'contenu', 'type', 'classe_id', 'date_publication', 'user_id',
    ];

    protected $casts = [
        'date_publication' => 'date',
    ];

    public function classe()
    {
        return $this->belongsTo(Classe::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function lectures()
    {
        return $this->hasMany(AnnonceLecture::class);
    }

    // Icône selon le type, pour l'affichage mobile
    public function getIconeAttribute(): string
    {
        return match ($this->type) {
            'examen'   => '📝',
            'reunion'  => '👥',
            'paiement' => '💰',
            default    => '📢',
        };
    }
}
