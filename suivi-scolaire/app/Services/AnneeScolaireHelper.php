<?php

namespace App\Services;

class AnneeScolaireHelper
{
    public static function suivante(string $anneeScolaire): string
    {
        [$debut, $fin] = explode('-', $anneeScolaire);

        return ((int) $debut + 1) . '-' . ((int) $fin + 1);
    }
}
