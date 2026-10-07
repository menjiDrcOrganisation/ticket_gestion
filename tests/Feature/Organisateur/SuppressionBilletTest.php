<?php

namespace Tests\Feature\Organisateur;

use App\Models\Billet;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\CreeDesDonneesMetier;
use Tests\TestCase;

/**
 * Suppression d'un billet (DELETE billet/{id}) : réservée à l'organisateur de l'événement.
 */
class SuppressionBilletTest extends TestCase
{
    use CreeDesDonneesMetier;
    use RefreshDatabase;

    private Billet $billet;

    protected function setUp(): void
    {
        parent::setUp();

        $evenement = $this->creerEvenement();
        $this->billet = $this->vendreBillet($evenement, $this->creerTypeBillet(), 2);
    }

    public function test_un_invite_ne_peut_pas_supprimer_un_billet(): void
    {
        $this->delete(route('billet.destroy', $this->billet->id))->assertRedirect(route('login'));

        $this->assertModelExists($this->billet);
    }

    public function test_l_organisateur_de_l_evenement_peut_supprimer_son_billet(): void
    {
        $organisateur = $this->billet->evenement->organisateur;

        $this->actingAs($organisateur->user)
            ->from(route('billet.all'))
            ->delete(route('billet.destroy', $this->billet->id))
            ->assertRedirect(route('billet.all'))
            ->assertSessionHas('success');

        $this->assertModelMissing($this->billet);
    }

    public function test_un_autre_organisateur_ne_peut_pas_supprimer_le_billet(): void
    {
        $this->actingAs($this->creerOrganisateur()->user)
            ->delete(route('billet.destroy', $this->billet->id))
            ->assertForbidden();

        $this->assertModelExists($this->billet);
    }

    public function test_un_scanneur_ne_peut_pas_supprimer_un_billet(): void
    {
        $this->actingAs($this->creerScanneur()->user)
            ->delete(route('billet.destroy', $this->billet->id))
            ->assertForbidden();

        $this->assertModelExists($this->billet);
    }

    public function test_un_admin_ne_peut_pas_supprimer_un_billet_depuis_l_espace_organisateur(): void
    {
        $this->actingAs($this->creerAdmin())
            ->delete(route('billet.destroy', $this->billet->id))
            ->assertForbidden();

        $this->assertModelExists($this->billet);
    }
}
