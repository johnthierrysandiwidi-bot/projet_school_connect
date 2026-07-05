<?php

namespace App\Services;

/**
 * Convertit un montant en francs CFA en toutes lettres (français), pour
 * l'affichage sur les reçus de paiement — une mention attendue sur un
 * reçu officiel, en plus du montant en chiffres.
 *
 * Exemple : 35750 → "trente-cinq mille sept cent cinquante".
 */
class NombreEnLettresHelper
{
    private const UNITES = [
        '', 'un', 'deux', 'trois', 'quatre', 'cinq', 'six', 'sept', 'huit', 'neuf',
        'dix', 'onze', 'douze', 'treize', 'quatorze', 'quinze', 'seize',
        'dix-sept', 'dix-huit', 'dix-neuf',
    ];

    private const DIZAINES = [
        2 => 'vingt', 3 => 'trente', 4 => 'quarante', 5 => 'cinquante', 6 => 'soixante',
    ];

    /** Convertit un entier positif (0 à 999 999 999) en toutes lettres. */
    public static function convertir(int $nombre): string
    {
        if ($nombre === 0) {
            return 'zéro';
        }
        if ($nombre < 0) {
            return 'moins ' . self::convertir(-$nombre);
        }

        $millions  = intdiv($nombre, 1_000_000);
        $reste     = $nombre % 1_000_000;
        $milliers  = intdiv($reste, 1000);
        $unites    = $reste % 1000;

        $parts = [];

        if ($millions > 0) {
            $parts[] = ($millions === 1 ? 'un' : self::centaines($millions)) . ' million' . ($millions > 1 ? 's' : '');
        }
        if ($milliers > 0) {
            $parts[] = ($milliers === 1 ? 'mille' : self::centaines($milliers) . ' mille');
        }
        if ($unites > 0 || empty($parts)) {
            $parts[] = self::centaines($unites);
        }

        return trim(implode(' ', $parts));
    }

    /** Montant en lettres suivi de la devise, prêt à afficher sur un reçu. */
    public static function montantEnLettres(float $montant): string
    {
        $entier = (int) round($montant);
        $lettres = self::convertir($entier);

        return ucfirst($lettres) . ' franc' . ($entier > 1 ? 's' : '') . ' CFA';
    }

    /** Convertit un nombre de 0 à 999. */
    private static function centaines(int $n): string
    {
        if ($n < 100) {
            return self::dizainesUnites($n);
        }

        $c = intdiv($n, 100);
        $r = $n % 100;

        if ($c === 1) {
            $debut = 'cent';
        } else {
            $debut = self::UNITES[$c] . ' cent' . ($r === 0 ? 's' : '');
        }

        return $r === 0 ? $debut : $debut . ' ' . self::dizainesUnites($r);
    }

    /** Convertit un nombre de 0 à 99, avec les règles d'usage du français. */
    private static function dizainesUnites(int $r): string
    {
        if ($r < 20) {
            return self::UNITES[$r];
        }

        if ($r < 70) {
            $d = intdiv($r, 10);
            $u = $r % 10;
            $base = self::DIZAINES[$d];

            if ($u === 0) return $base;
            if ($u === 1) return $base . ' et un';
            return $base . '-' . self::UNITES[$u];
        }

        if ($r < 80) {
            // 70 à 79 : "soixante" + dix..dix-neuf, sauf 71 qui prend "et".
            $u = $r - 60; // 10 à 19
            return $u === 11 ? 'soixante et onze' : 'soixante-' . self::UNITES[$u];
        }

        if ($r < 90) {
            // 80 à 89 : "quatre-vingts" (avec s) seul, sinon sans s, jamais de "et".
            $u = $r - 80; // 0 à 9
            return $u === 0 ? 'quatre-vingts' : 'quatre-vingt-' . self::UNITES[$u];
        }

        // 90 à 99 : "quatre-vingt-dix" à "quatre-vingt-dix-neuf", jamais de "et".
        $u = $r - 80; // 10 à 19
        return 'quatre-vingt-' . self::UNITES[$u];
    }
}
