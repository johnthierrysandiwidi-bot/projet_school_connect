<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Paiement extends Model
{
    use HasFactory;

    protected $fillable = [
        'reference', 'eleve_id', 'montant',
        'date_paiement', 'mode_paiement',
        'numero_transaction', 'observation', 'user_id',
    ];

    protected $casts = [
        'date_paiement' => 'date',
        'montant' => 'decimal:2',
    ];

    // Un paiement appartient à un élève
    public function eleve()
    {
        return $this->belongsTo(Eleve::class);
    }

    // Un paiement est enregistré par un user
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Générer automatiquement la référence
    public static function genererReference(): string
    {
        $annee = date('Y');
        $count = self::whereYear('created_at', $annee)->count() + 1;
        return 'REF-' . $annee . '-' . str_pad($count, 5, '0', STR_PAD_LEFT);
    }
}