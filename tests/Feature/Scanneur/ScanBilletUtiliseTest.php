<?php

namespace Tests\Feature\Scanneur;

use App\Models\Billet;
use App\Models\Evenement;
use App\Models\Scanneur;
use App\Models\TypeBillet;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\CreeDesDonneesMetier;
use Tests\TestCase;

/**
 * Un billet dont toutes les entrées ont été scannées ne doit plus être accepté.
 */
class ScanBilletUtiliseTest extends TestCase
{
    use CreeDesDonneesMetier;
    use RefreshDatabase;

    private Scanneur $scanneur;
    private Evenement $evenement;
    private TypeBillet $typeBillet;

    protected function setUp(): void
    {
        parent::setUp();

        $this->scanneur = $this->creerScanneur();
        $this->evenement = $this->creerEvenement(null, $this->scanneur);
        $this->typeBillet = $this->creerTypeBillet();
    }

    private function billetUtilise(): Billet
    {
        return $this->vendreBillet($this->evenement, $this->typeBillet, 2, [
            'quantite_fictif' => 0,
            'statut' => 'utilisee',
        ]);
    }

    public function test_l_apercu_refuse_un_billet_deja_utilise(): void
    {
        $billet = $this->billetUtilise();

        $this->actingAs($this->scanneur->user)
            ->postJson(route('scanneur.previewScanne'), ['code' => $billet->code_billet])
            ->assertUnprocessable()
            ->assertJson(['valid' => false, 'message' => 'Billet déjà utilisé']);
    }

    public function test_le_scan_refuse_un_billet_deja_utilise(): void
    {
        $billet = $this->billetUtilise();

        $this->actingAs($this->scanneur->user)
            ->postJson(route('scanneur.processScan'), ['code' => $billet->code_billet, 'quantite' => 1])
            ->assertUnprocessable()
            ->assertJson(['valid' => false, 'message' => 'Billet déjà utilisé']);

        $billet->refresh();
        $this->assertSame(0, $billet->quantite_fictif);
        $this->assertSame('utilisee', $billet->statut);
    }

    public function test_un_billet_sans_entree_restante_est_refuse_meme_si_son_statut_est_valide(): void
    {
        $billet = $this->vendreBillet($this->evenement, $this->typeBillet, 2, ['quantite_fictif' => 0]);

        $this->actingAs($this->scanneur->user)
            ->postJson(route('scanneur.processScan'), ['code' => $billet->code_billet, 'quantite' => 1])
            ->assertUnprocessable()
            ->assertJsonPath('valid', false);
    }

    public function test_le_billet_est_refuse_au_passage_suivant_le_dernier(): void
    {
        $billet = $this->vendreBillet($this->evenement, $this->typeBillet, 1);

        $this->actingAs($this->scanneur->user)
            ->postJson(route('scanneur.processScan'), ['code' => $billet->code_billet, 'quantite' => 1])
            ->assertOk()
            ->assertJson(['valid' => true, 'message' => 'Dernier billet utilisé']);

        $this->actingAs($this->scanneur->user)
            ->postJson(route('scanneur.processScan'), ['code' => $billet->code_billet, 'quantite' => 1])
            ->assertUnprocessable()
            ->assertJsonPath('valid', false);
    }
}
