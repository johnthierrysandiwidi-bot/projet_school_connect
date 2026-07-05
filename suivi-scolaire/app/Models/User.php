<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasFactory, Notifiable, HasApiTokens;

    protected $fillable = [
        'name', 'email', 'password', 
        'role', 'classe_id', 'is_active',
    ];

    protected $hidden = [
        'password', 'remember_token',
    ];

    protected $casts = [
            'email_verified_at' => 'datetime',
            'is_active' => 'boolean',
  ];
    // Un enseignant appartient à une classe
    public function classe()
    {
        return $this->belongsTo(Classe::class);
    }

    // Est-ce un gestionnaire ?
    public function isGestionnaire(): bool
    {
        return $this->role === 'gestionnaire';
    }

    // Est-ce un enseignant ?
    public function isEnseignant(): bool
    {
        return $this->role === 'enseignant';
    }

    // Est-ce un parent ?
    public function isParent(): bool
    {
        return $this->role === 'parent';
    }

    // Les enfants d'un parent (un parent peut avoir plusieurs enfants
    // inscrits dans l'établissement)
    public function enfants()
    {
        return $this->belongsToMany(Eleve::class, 'parent_eleve');
    }

    // Utilise une notification en français, plutôt que celle par défaut
    // de Laravel (en anglais), pour rester cohérent avec le reste de
    // l'application.
    public function sendPasswordResetNotification($token): void
    {
        $this->notify(new \App\Notifications\ReinitialisationMotDePasse($token));
    }
}