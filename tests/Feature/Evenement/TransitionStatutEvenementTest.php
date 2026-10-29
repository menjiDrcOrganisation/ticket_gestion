<?php

namespace Tests\Feature\Evenement;

use App\Models\AuditLog;
use App\Models\Evenement;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Tests\Concerns\CreeDesDonneesMetier;
use Tests\TestCase;

/**
 * Cycle de vie d'un événement : encours (à la création) ⇄ ferme.
 */
class TransitionStatutEvenementTest extends TestCase
{
    use CreeDesDonneesMetier;
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = $this->creerAdmin();
    }

    private function changerStatut(Evenement $evenement, string $statut)
    {
        return $this->actingAs($this->admin)
            ->from(route('evenements.index'))
            ->patch(route('evenements.updateStatus', $evenement->id), ['statut' => $statut]);
    }

    public function test_un_evenement_cree_est_en_cours(): void
    {
        Mail::fake();
        Storage::fake('public');

        $this->actingAs($this->admin)
            ->post(route('evenements.web.store'), $this->donneesEvenement($this->creerTypeBillet()))
            ->assertSessionHasNoErrors();

        $this->assertSame('encours', Evenement::firstOrFail()->statut);
    }

    public function test_fermeture_d_un_evenement_en_cours(): void
    {
        $evenement = $this->creerEvenement(null, null, ['statut' => 'encours']);

        $this->changerStatut($evenement, 'ferme')
            ->assertRedirect(route('evenements.index'))
            ->assertSessionHas('success');

        $this->assertSame('ferme', $evenement->fresh()->statut);
    }

    public function test_reouverture_d_un_evenement_ferme(): void
    {
        $evenement = $this->creerEvenement(null, null, ['statut' => 'ferme']);

        $this->changerStatut($evenement, 'encours')->assertSessionHasNoErrors();

        $this->assertSame('encours', $evenement->fresh()->statut);
    }

    public function test_une_transition_vers_le_meme_statut_est_sans_effet(): void
    {
        $evenement = $this->creerEvenement(null, null, ['statut' => 'ferme']);

        $this->changerStatut($evenement, 'ferme')->assertSessionHasNoErrors();

        $this->assertSame('ferme', $evenement->fresh()->statut);
    }

    public function test_un_statut_inconnu_est_refuse_et_l_etat_est_conserve(): void
    {
        $evenement = $this->creerEvenement(null, null, ['statut' => 'encours']);

        $this->changerStatut($evenement, 'inactif')->assertSessionHasErrors('statut');

        $this->assertSame('encours', $evenement->fresh()->statut);
    }

    public function test_changer_le_statut_d_un_evenement_inexistant_renvoie_404(): void
    {
        $this->actingAs($this->admin)
            ->patch(route('evenements.updateStatus', 9999), ['statut' => 'ferme'])
            ->assertNotFound();
    }

    public function test_le_changement_de_statut_est_trace_dans_le_journal_d_audit(): void
    {
        $evenement = $this->creerEvenement(null, null, ['statut' => 'encours']);

        $this->changerStatut($evenement, 'ferme');

        $this->assertTrue(AuditLog::where('user_id', $this->admin->id)->exists());
    }

    public function test_les_compteurs_de_la_liste_suivent_les_statuts(): void
    {
        $this->creerEvenement(null, null, ['statut' => 'encours']);
        $this->creerEvenement(null, null, ['statut' => 'encours']);
        $ferme = $this->creerEvenement(null, null, ['statut' => 'encours']);

        $this->changerStatut($ferme, 'ferme');

        $this->actingAs($this->admin)
            ->get(route('evenements.index'))
            ->assertOk()
            ->assertViewHas('evenementsEncours', 2)
            ->assertViewHas('evenementsPasse', 1);

        $this->actingAs($this->admin)
            ->get(route('evenements.index', ['statut' => 'ferme']))
            ->assertViewHas('evenements', fn ($page) => $page->pluck('id')->all() === [$ferme->id]);
    }
}
