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
 * Cycle de vie d'un événement : encours (à la création) ⇄ suspendu.
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

    public function test_suspension_d_un_evenement_en_cours(): void
    {
        $evenement = $this->creerEvenement(null, null, ['statut' => 'encours']);

        $this->changerStatut($evenement, 'suspendu')
            ->assertRedirect(route('evenements.index'))
            ->assertSessionHas('success');

        $this->assertSame('suspendu', $evenement->fresh()->statut);
    }

    public function test_reouverture_d_un_evenement_suspendu(): void
    {
        $evenement = $this->creerEvenement(null, null, ['statut' => 'suspendu']);

        $this->changerStatut($evenement, 'encours')->assertSessionHasNoErrors();

        $this->assertSame('encours', $evenement->fresh()->statut);
    }

    public function test_un_evenement_en_cours_ne_peut_pas_etre_rouvert(): void
    {
        $evenement = $this->creerEvenement(null, null, ['statut' => 'encours']);

        $this->changerStatut($evenement, 'encours')->assertSessionHasErrors('statut');

        $this->assertSame('encours', $evenement->fresh()->statut);
    }

    public function test_un_evenement_suspendu_ne_peut_pas_etre_suspendu_une_seconde_fois(): void
    {
        $evenement = $this->creerEvenement(null, null, ['statut' => 'suspendu']);

        $this->changerStatut($evenement, 'suspendu')->assertSessionHasErrors('statut');

        $this->assertSame('suspendu', $evenement->fresh()->statut);
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
            ->patch(route('evenements.updateStatus', 9999), ['statut' => 'suspendu'])
            ->assertNotFound();
    }

    public function test_le_changement_de_statut_est_trace_dans_le_journal_d_audit(): void
    {
        $evenement = $this->creerEvenement(null, null, ['statut' => 'encours']);

        $this->changerStatut($evenement, 'suspendu');

        $journal = AuditLog::where('user_id', $this->admin->id)->firstOrFail();

        $this->assertSame('status_change', $journal->action);
        $this->assertSame($evenement->id, $journal->entity_id);
        $this->assertSame('suspendu', $journal->after_values['statut']);
    }

    public function test_les_compteurs_de_la_liste_suivent_les_statuts(): void
    {
        $this->creerEvenement(null, null, ['statut' => 'encours']);
        $this->creerEvenement(null, null, ['statut' => 'encours']);
        $suspendu = $this->creerEvenement(null, null, ['statut' => 'encours']);

        $this->changerStatut($suspendu, 'suspendu');

        $this->actingAs($this->admin)
            ->get(route('evenements.index'))
            ->assertOk()
            ->assertViewHas('evenementsEncours', 2)
            ->assertViewHas('evenementsSuspendus', 1);

        $this->actingAs($this->admin)
            ->get(route('evenements.index', ['statut' => 'suspendu']))
            ->assertViewHas('evenements', fn ($page) => $page->pluck('id')->all() === [$suspendu->id]);
    }

    public function test_l_action_suspendre_est_affichee_uniquement_pour_un_evenement_en_cours(): void
    {
        $this->creerEvenement(null, null, ['statut' => 'encours']);

        $this->actingAs($this->admin)
            ->get(route('evenements.index'))
            ->assertOk()
            ->assertSee('aria-label="Suspendre"', false)
            ->assertDontSee('aria-label="Ouvrir"', false);
    }

    public function test_l_action_ouvrir_est_affichee_uniquement_pour_un_evenement_suspendu(): void
    {
        $this->creerEvenement(null, null, ['statut' => 'suspendu']);

        $this->actingAs($this->admin)
            ->get(route('evenements.index'))
            ->assertOk()
            ->assertSee('aria-label="Ouvrir"', false)
            ->assertDontSee('aria-label="Suspendre"', false);
    }
}
