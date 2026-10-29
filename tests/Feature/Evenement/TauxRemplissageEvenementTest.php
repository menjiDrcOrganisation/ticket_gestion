<?php

namespace Tests\Feature\Evenement;

use App\Models\Evenement;
use App\Models\TypeBillet;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\CreeDesDonneesMetier;
use Tests\TestCase;

/**
 * Taux de remplissage calculé à partir des données (stock restant + billets vendus).
 */
class TauxRemplissageEvenementTest extends TestCase
{
    use CreeDesDonneesMetier;
    use RefreshDatabase;

    private Evenement $evenement;
    private TypeBillet $vip;
    private TypeBillet $standard;

    protected function setUp(): void
    {
        parent::setUp();

        $this->evenement = $this->creerEvenement();
        $this->vip = $this->creerTypeBillet('VIP');
        $this->standard = $this->creerTypeBillet('Standard');
    }

    public function test_sans_vente_le_taux_est_nul(): void
    {
        $this->ajouterStock($this->evenement, $this->vip, 100);

        $this->assertSame(0, $this->evenement->billetsVendus());
        $this->assertSame(100, $this->evenement->capaciteTotale());
        $this->assertSame(0.0, $this->evenement->tauxRemplissage());
    }

    public function test_sans_billetterie_le_taux_est_nul(): void
    {
        $this->assertSame(0, $this->evenement->capaciteTotale());
        $this->assertSame(0.0, $this->evenement->tauxRemplissage());
    }

    public function test_le_taux_tient_compte_du_stock_decremente_par_les_ventes(): void
    {
        // Capacité initiale : 60 VIP + 40 Standard = 100. 25 billets vendus → stock restant 75.
        $this->ajouterStock($this->evenement, $this->vip, 60 - 10);
        $this->ajouterStock($this->evenement, $this->standard, 40 - 15);
        $this->vendreBillet($this->evenement, $this->vip, 10);
        $this->vendreBillet($this->evenement, $this->standard, 15);

        $this->assertSame(25, $this->evenement->billetsVendus());
        $this->assertSame(100, $this->evenement->capaciteTotale());
        $this->assertSame(25.0, $this->evenement->tauxRemplissage());
    }

    public function test_evenement_complet(): void
    {
        $this->ajouterStock($this->evenement, $this->vip, 0);
        $this->vendreBillet($this->evenement, $this->vip, 30);

        $this->assertSame(100.0, $this->evenement->tauxRemplissage());
    }

    public function test_les_ventes_des_autres_evenements_ne_sont_pas_comptees(): void
    {
        $autre = $this->creerEvenement();
        $this->ajouterStock($this->evenement, $this->vip, 90);
        $this->ajouterStock($autre, $this->vip, 0);
        $this->vendreBillet($this->evenement, $this->vip, 10);
        $this->vendreBillet($autre, $this->vip, 500);

        $this->assertSame(10.0, $this->evenement->tauxRemplissage());
    }
}
