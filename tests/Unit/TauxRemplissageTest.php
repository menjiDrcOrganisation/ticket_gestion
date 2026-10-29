<?php

namespace Tests\Unit;

use App\Models\Evenement;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class TauxRemplissageTest extends TestCase
{
    public static function cas(): array
    {
        return [
            'aucune vente' => [0, 100, 0.0],
            'moitié' => [50, 100, 50.0],
            'complet' => [100, 100, 100.0],
            'arrondi au dixième' => [1, 3, 33.3],
            'arrondi supérieur' => [2, 3, 66.7],
            'capacité nulle' => [10, 0, 0.0],
            'capacité négative' => [10, -5, 0.0],
            'ventes négatives' => [-3, 100, 0.0],
            'dépassement borné à 100' => [120, 100, 100.0],
        ];
    }

    #[DataProvider('cas')]
    public function test_calcul_du_taux_de_remplissage(int $vendus, int $capacite, float $attendu): void
    {
        $this->assertSame($attendu, Evenement::calculerTauxRemplissage($vendus, $capacite));
    }
}
