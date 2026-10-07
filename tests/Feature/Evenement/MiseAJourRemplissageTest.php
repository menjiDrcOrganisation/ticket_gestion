<?php

namespace Tests\Feature\Evenement;

use App\Models\Billet;
use App\Models\Evenement;
use App\Models\EvenementTypeBillet;
use App\Models\Transaction;
use App\Models\TypeBillet;
use App\Services\AnnulationBilletService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\Concerns\CreeDesDonneesMetier;
use Tests\TestCase;

/**
 * Taux de remplissage : mise à jour après annulation, suivi de l'évolution et affichage.
 */
class MiseAJourRemplissageTest extends TestCase
{
    use CreeDesDonneesMetier;
    use RefreshDatabase;

    private Evenement $evenement;
    private TypeBillet $vip;
    private EvenementTypeBillet $stock;

    protected function setUp(): void
    {
        parent::setUp();

        $this->evenement = $this->creerEvenement();
        $this->vip = $this->creerTypeBillet('VIP');
        // Capacité 100 : 20 billets vendus, 80 restants en stock.
        $this->stock = $this->ajouterStock($this->evenement, $this->vip, 80);
    }

    private function vendre(int $quantite, array $attributs = []): Billet
    {
        return $this->vendreBillet($this->evenement, $this->vip, $quantite, $attributs);
    }

    // ---- Annulation ----------------------------------------------------------------------

    public function test_un_billet_annule_n_est_plus_compte_et_libere_ses_places(): void
    {
        $billet = $this->vendre(20);
        $this->assertSame(20.0, $this->evenement->tauxRemplissage());

        $this->assertTrue(app(AnnulationBilletService::class)->annuler($billet));

        $this->assertSame(Billet::STATUT_ANNULE, $billet->fresh()->statut);
        $this->assertSame(100, $this->stock->fresh()->nombre_billet);
        $this->assertSame(0, $this->evenement->billetsVendus());
        $this->assertSame(100, $this->evenement->capaciteTotale(), 'La capacité ne change pas après une annulation.');
        $this->assertSame(0.0, $this->evenement->tauxRemplissage());
    }

    public function test_une_double_annulation_ne_restitue_les_places_qu_une_fois(): void
    {
        $billet = $this->vendre(20);
        $service = app(AnnulationBilletService::class);

        $service->annuler($billet);
        $this->assertFalse($service->annuler($billet));

        $this->assertSame(100, $this->stock->fresh()->nombre_billet);
    }

    public function test_le_remboursement_admin_annule_le_billet_et_met_a_jour_le_taux(): void
    {
        $billet = $this->vendre(20);
        $transaction = Transaction::create([
            'reference' => 'TRX-' . Str::upper(Str::random(8)),
            'montant' => 200,
            'nombre_billet' => 20,
            'statut' => 'completee',
            'type' => 'paiement',
            'devise' => 'USD',
            'billet_id' => $billet->id,
        ]);

        $this->actingAs($this->creerAdmin())
            ->post(route('transactions.refund', $transaction->id))
            ->assertSessionHas('success');

        $this->assertSame('annulee', $transaction->fresh()->statut);
        $this->assertSame(Billet::STATUT_ANNULE, $billet->fresh()->statut);
        $this->assertSame(0.0, $this->evenement->tauxRemplissage());

        // Un second remboursement est refusé et ne touche plus au stock.
        $this->actingAs($this->creerAdmin())
            ->post(route('transactions.refund', $transaction->id))
            ->assertSessionHas('error');
        $this->assertSame(100, $this->stock->fresh()->nombre_billet);
    }

    public function test_la_suppression_d_un_billet_par_l_organisateur_remet_ses_places_en_stock(): void
    {
        $billet = $this->vendre(20);

        $this->actingAs($this->evenement->organisateur->user)
            ->delete(route('billet.destroy', $billet->id))
            ->assertSessionHas('success');

        $this->assertModelMissing($billet);
        $this->assertSame(100, $this->evenement->capaciteTotale());
        $this->assertSame(0.0, $this->evenement->tauxRemplissage());
    }

    public function test_le_scanneur_refuse_un_billet_annule(): void
    {
        $scanneur = $this->creerScanneur();
        $evenement = $this->creerEvenement(scanneur: $scanneur);
        $this->ajouterStock($evenement, $this->vip, 10);
        $billet = $this->vendreBillet($evenement, $this->vip, 2);
        app(AnnulationBilletService::class)->annuler($billet);

        $this->actingAs($scanneur->user)
            ->postJson(route('scanneur.processScan'), ['code' => $billet->code_billet, 'quantite' => 1])
            ->assertStatus(422)
            ->assertJson(['valid' => false, 'message' => 'Billet annulé (remboursé)']);
    }

    // ---- Calcul avec relations préchargées (listes) ----------------------------------------

    public function test_le_calcul_est_identique_avec_les_relations_prechargees(): void
    {
        $this->vendre(20);
        $this->vendre(5, ['statut' => Billet::STATUT_ANNULE]);

        $precharge = Evenement::with(['billets', 'typeBillets'])->find($this->evenement->id);

        $this->assertSame($this->evenement->billetsVendus(), $precharge->billetsVendus());
        $this->assertSame($this->evenement->capaciteTotale(), $precharge->capaciteTotale());
        $this->assertSame(20.0, $precharge->tauxRemplissage());
    }

    public function test_sans_billetterie_aucune_capacite_n_est_definie(): void
    {
        $sansStock = $this->creerEvenement();

        $this->assertFalse($sansStock->aUneCapacite());
        $this->assertTrue($this->evenement->aUneCapacite());
    }

    // ---- Évolution -------------------------------------------------------------------------

    public function test_l_evolution_cumule_les_ventes_jour_par_jour(): void
    {
        $this->vendre(10, ['date_achat' => '2026-10-01 10:00:00']);
        $this->vendre(5, ['date_achat' => '2026-10-01 18:00:00']);
        $this->vendre(5, ['date_achat' => '2026-10-03 09:00:00']);
        $this->vendre(30, ['date_achat' => '2026-10-02 12:00:00', 'statut' => Billet::STATUT_ANNULE]);

        $evolution = $this->evenement->evolutionRemplissage();

        $this->assertCount(2, $evolution, 'Le jour qui ne contient qu\'un billet annulé est ignoré.');
        $this->assertSame('2026-10-01', $evolution[0]['date']->toDateString());
        $this->assertSame([15, 15, 15.0], [$evolution[0]['vendus'], $evolution[0]['cumul'], $evolution[0]['taux']]);
        $this->assertSame('2026-10-03', $evolution[1]['date']->toDateString());
        $this->assertSame([5, 20, 20.0], [$evolution[1]['vendus'], $evolution[1]['cumul'], $evolution[1]['taux']]);
    }

    // ---- Affichage -------------------------------------------------------------------------

    public function test_la_liste_admin_affiche_le_taux_et_le_cas_sans_capacite(): void
    {
        $this->vendre(20, ['date_achat' => '2026-10-01 10:00:00']);
        $this->creerEvenement(attributs: ['nom' => 'Sans billetterie']);

        $this->actingAs($this->creerAdmin())
            ->get(route('evenements.index'))
            ->assertOk()
            ->assertSee('Remplissage')
            ->assertSee('20 %')
            ->assertSee('20 / 100')
            ->assertSee('Capacité non définie')
            ->assertSee('Évolution du remplissage')
            ->assertSee('01/10/2026');
    }

    public function test_le_tableau_de_bord_organisateur_affiche_le_taux_de_l_evenement(): void
    {
        $this->vendre(20);
        $this->vendre(10, ['statut' => Billet::STATUT_ANNULE]);

        $this->actingAs($this->evenement->organisateur->user)
            ->get(route('dashboard_orginasateur.show', ['event_id' => $this->evenement->id]))
            ->assertOk()
            ->assertViewHas('remplissageVendus', 20)
            ->assertViewHas('remplissageCapacite', 100)
            ->assertViewHas('totalBilletsVendus', 20)
            ->assertSee('Taux de remplissage')
            ->assertSee('20 %');
    }
}
