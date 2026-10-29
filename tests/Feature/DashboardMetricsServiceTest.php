<?php

namespace Tests\Feature;

use App\Models\Transaction;
use App\Services\DashboardMetricsService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\Concerns\CreeDesDonneesMetier;
use Tests\TestCase;

/**
 * Indicateurs métier du tableau de bord administrateur.
 */
class DashboardMetricsServiceTest extends TestCase
{
    use CreeDesDonneesMetier;
    use RefreshDatabase;

    private function transaction(string $statut, float $montant, int $billets = 1, string $devise = 'USD', array $attributs = []): Transaction
    {
        return Transaction::create(array_merge([
            'reference' => 'CMD-' . Str::upper(Str::random(12)),
            'montant' => $montant,
            'nombre_billet' => $billets,
            'statut' => $statut,
            'devise' => $devise,
        ], $attributs));
    }

    private function donnees(?int $periode = 30): array
    {
        return app(DashboardMetricsService::class)->adminData($periode);
    }

    public function test_seules_les_ventes_reussies_du_jour_sont_comptees(): void
    {
        $this->transaction('paye', 50, 2, 'USD');
        $this->transaction('paye_sans_billet', 10, 1, 'USD');
        $this->transaction('paye', 20000, 3, 'CDF');
        $this->transaction('echoue', 999, 9, 'USD');
        $this->transaction('en_attente', 999, 9, 'USD');
        $hier = $this->transaction('paye', 999, 9, 'USD');
        $hier->forceFill(['created_at' => now()->subDay()])->save();

        $kpis = $this->donnees()['kpis'];

        $this->assertSame(6, $kpis['ventesAujourdhui']);
        $this->assertSame(60.0, $kpis['caAujourdhuiUSD']);
        $this->assertSame(20000.0, $kpis['caAujourdhuiCDF']);
    }

    public function test_taux_d_echec_sur_24_heures(): void
    {
        $this->transaction('paye', 10);
        $this->transaction('paye', 10);
        $this->transaction('en_attente', 10);
        $this->transaction('echoue', 10);
        $this->transaction('annulee', 10);
        $this->transaction('echoue', 10)->forceFill(['created_at' => now()->subDays(2)])->save();

        // 2 échecs sur 5 transactions des dernières 24 h.
        $this->assertSame(40.0, $this->donnees()['alerts']['failedRate24h']);
    }

    public function test_taux_d_echec_nul_sans_transaction(): void
    {
        $this->assertSame(0.0, $this->donnees()['alerts']['failedRate24h']);
    }

    public function test_seuls_les_evenements_en_cours_sont_actifs(): void
    {
        $this->creerEvenement(null, null, ['statut' => 'encours']);
        $this->creerEvenement(null, null, ['statut' => 'encours']);
        $this->creerEvenement(null, null, ['statut' => 'ferme']);

        $this->assertSame(2, $this->donnees()['kpis']['evenementsActifs']);
    }

    public function test_la_periode_est_limitee_aux_valeurs_autorisees(): void
    {
        $this->assertSame(7, $this->donnees(7)['periodDays']);
        $this->assertSame(90, $this->donnees(90)['periodDays']);
        $this->assertSame(30, $this->donnees(45)['periodDays']);
        $this->assertSame(30, $this->donnees(null)['periodDays']);
        $this->assertCount(7, $this->donnees(7)['charts']['salesByDay']);
    }
}
