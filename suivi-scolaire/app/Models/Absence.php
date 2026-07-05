<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Absence extends Model
{
    use HasFactory;

    protected $fillable = [
        'eleve_id', 'date_absence', 'justifiee', 'motif', 'user_id',
    ];

    protected $casts = [
        'date_absence' => 'date',
        'justifiee'    => 'boolean',
    ];

    public function eleve()
    {
        return $this->belongsTo(Eleve::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
