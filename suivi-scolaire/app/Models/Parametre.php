<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Parametre extends Model
{
    protected $fillable = ['cle', 'valeur'];

    public static function lire(string $cle, ?string $defaut = null): ?string
    {
        return static::where('cle', $cle)->value('valeur') ?? $defaut;
    }

    public static function ecrire(string $cle, ?string $valeur): void
    {
        static::updateOrCreate(['cle' => $cle], ['valeur' => $valeur]);
    }
}
