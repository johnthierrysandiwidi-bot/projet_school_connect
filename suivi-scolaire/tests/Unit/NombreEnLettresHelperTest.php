<?php

namespace Tests\Unit;

use App\Services\NombreEnLettresHelper;
use PHPUnit\Framework\TestCase;

/**
 * Vérifie les cas particuliers du français écrit (vingt/cent variables,
 * "et" avant un, exceptions de soixante-dix/quatre-vingts), pour le
 * montant en toutes lettres affiché sur les reçus de paiement.
 */
class NombreEnLettresHelperTest extends TestCase
{
    /** @dataProvider nombres */
    public function test_convertit_correctement(int $nombre, string $attendu): void
    {
        $this->assertSame($attendu, NombreEnLettresHelper::convertir($nombre));
    }

    public static function nombres(): array
    {
        return [
            [0, 'zéro'],
            [1, 'un'],
            [21, 'vingt et un'],
            [29, 'vingt-neuf'],
            [70, 'soixante-dix'],
            [71, 'soixante et onze'],   // exception : "et" malgré la famille soixante-dix
            [72, 'soixante-douze'],
            [80, 'quatre-vingts'],       // "vingt" prend un s, seul
            [81, 'quatre-vingt-un'],     // ... mais pas suivi d'un autre chiffre
            [90, 'quatre-vingt-dix'],
            [91, 'quatre-vingt-onze'],   // jamais de "et" dans cette famille
            [100, 'cent'],
            [101, 'cent un'],
            [200, 'deux cents'],         // "cent" pluriel seul
            [235, 'deux cent trente-cinq'], // ... mais pas suivi d'un autre chiffre
            [1000, 'mille'],             // jamais "un mille"
            [1001, 'mille un'],
            [21000, 'vingt et un mille'],
            [80000, 'quatre-vingts mille'],
            [100000, 'cent mille'],
            [1000000, 'un million'],
            [2500000, 'deux millions cinq cents mille'],
        ];
    }

    public function test_montant_en_lettres_ajoute_la_devise(): void
    {
        $this->assertSame('Quinze mille francs CFA', NombreEnLettresHelper::montantEnLettres(15000));
        $this->assertSame('Un franc CFA', NombreEnLettresHelper::montantEnLettres(1));
    }
}
