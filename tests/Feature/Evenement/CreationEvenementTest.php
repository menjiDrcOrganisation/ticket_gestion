<?php

namespace Tests\Feature\Evenement;

use App\Mail\EnvoiMotDePasseMail;
use App\Models\Evenement;
use App\Models\EvenementTypeBillet;
use App\Models\Organisateur;
use App\Models\Ressource;
use App\Models\TypeBillet;
use App\Models\TypeEvenement;
use App\Models\User;
use App\Services\EvenementCreationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Tests\Concerns\CreeDesDonneesMetier;
use Tests\TestCase;

class CreationEvenementTest extends TestCase
{
    use CreeDesDonneesMetier;
    use RefreshDatabase;

    private TypeBillet $vip;
    private TypeBillet $standard;

    protected function setUp(): void
    {
        parent::setUp();

        Mail::fake();
        Storage::fake('public');

        $this->vip = $this->creerTypeBillet('VIP');
        $this->standard = $this->creerTypeBillet('Standard');
    }

    private function service(): EvenementCreationService
    {
        return app(EvenementCreationService::class);
    }

    public function test_creation_complete_d_un_evenement(): void
    {
        $creation = $this->service()->create($this->donneesEvenement($this->vip, [
            'ticket_type_id' => [$this->vip->id, $this->standard->id],
            'quantite' => [$this->vip->id => 50, $this->standard->id => 200],
            'prix' => [$this->vip->id => 100, $this->standard->id => 5000],
            'devise' => [$this->vip->id => 'usd', $this->standard->id => 'CDF'],
        ]));

        $evenement = $creation['evenement']->fresh();

        $this->assertSame('Concert de test', $evenement->nom);
        $this->assertSame('concert-de-test', $evenement->url_evenement);
        $this->assertSame('encours', $evenement->statut, 'Un nouvel événement démarre à l\'état "encours".');
        $this->assertSame('2026-12-01 18:00:00', (string) $evenement->date_debut);
        $this->assertSame('2026-12-01 23:00:00', (string) $evenement->date_fin);
        $this->assertSame('Salle A', $evenement->salle);
        $this->assertSame('Concert', $evenement->typeEvenement->nom_type);
        $this->assertNotNull($evenement->organisateur_id);
        $this->assertNotNull($evenement->scanneur_id);

        $stocks = EvenementTypeBillet::where('evenement_id', $evenement->id)->orderBy('type_billet_id')->get();
        $this->assertCount(2, $stocks);
        $this->assertSame([50, 100, 'USD'], [$stocks[0]->nombre_billet, $stocks[0]->prix_unitaire, $stocks[0]->devise]);
        $this->assertSame([200, 5000, 'CDF'], [$stocks[1]->nombre_billet, $stocks[1]->prix_unitaire, $stocks[1]->devise]);

        $ressource = Ressource::where('evenement_id', $evenement->id)->firstOrFail();
        $this->assertSame('Artiste', $ressource->nom_artiste);
        Storage::disk('public')->assertExists($ressource->photo_affiche);
    }

    public function test_les_lignes_de_billet_sans_quantite_ou_sans_prix_sont_ignorees(): void
    {
        $creation = $this->service()->create($this->donneesEvenement($this->vip, [
            'ticket_type_id' => [$this->vip->id, $this->standard->id],
            'quantite' => [$this->vip->id => 10, $this->standard->id => 0],
            'prix' => [$this->vip->id => 20, $this->standard->id => 5],
            'devise' => [$this->vip->id => 'USD', $this->standard->id => 'USD'],
        ]));

        $this->assertSame([$this->vip->id], EvenementTypeBillet::where('evenement_id', $creation['evenement']->id)->pluck('type_billet_id')->all());
    }

    public function test_les_urls_d_evenement_sont_uniques(): void
    {
        $premier = $this->service()->create($this->donneesEvenement($this->vip))['evenement'];
        $deuxieme = $this->service()->create($this->donneesEvenement($this->vip))['evenement'];
        $troisieme = $this->service()->create($this->donneesEvenement($this->vip))['evenement'];

        $this->assertSame(['concert-de-test', 'concert-de-test-2', 'concert-de-test-3'], [
            $premier->url_evenement,
            $deuxieme->url_evenement,
            $troisieme->url_evenement,
        ]);
    }

    public function test_le_type_d_evenement_saisi_est_reutilise_sans_tenir_compte_de_la_casse(): void
    {
        $existant = TypeEvenement::create(['nom_type' => 'Concert']);

        $creation = $this->service()->create($this->donneesEvenement($this->vip, ['type_evenement_nom' => '  CONCERT ']));

        $this->assertSame($existant->id, $creation['evenement']->type_evenement_id);
        $this->assertSame(1, TypeEvenement::count());
    }

    public function test_le_type_d_evenement_peut_etre_choisi_par_identifiant(): void
    {
        $festival = TypeEvenement::create(['nom_type' => 'Festival']);

        $creation = $this->service()->create($this->donneesEvenement($this->vip, [
            'type_evenement_id' => $festival->id,
            'type_evenement_nom' => null,
        ]));

        $this->assertSame($festival->id, $creation['evenement']->type_evenement_id);
    }

    public function test_creation_par_l_admin_depuis_le_formulaire_web(): void
    {
        $admin = $this->creerAdmin();

        $this->actingAs($admin)
            ->post(route('evenements.web.store'), $this->donneesEvenement($this->vip))
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('evenements.index'));

        $this->assertSame(1, Evenement::count());
        Mail::assertSent(EnvoiMotDePasseMail::class);
    }

    public function test_creation_via_l_api(): void
    {
        $this->postJson('/api/v1/evenements', $this->donneesEvenement($this->vip))
            ->assertCreated()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.nom', 'Concert de test')
            ->assertJsonPath('data.statut', 'encours')
            ->assertJsonPath('mail_envoye', true);
    }

    // ---- Cas d'échec -----------------------------------------------------------------

    public function test_echec_de_validation_via_l_api_renvoie_422_en_json(): void
    {
        $this->postJson('/api/v1/evenements', $this->donneesEvenement($this->vip, ['nom_evenement' => '']))
            ->assertStatus(422)
            ->assertJsonPath('success', false)
            ->assertJsonValidationErrors('nom_evenement');

        $this->assertSame(0, Evenement::count());
    }

    public function test_aucun_billet_valide_leve_une_erreur_de_validation(): void
    {
        $this->expectException(ValidationException::class);

        $this->service()->create($this->donneesEvenement($this->vip, [
            'prix' => [$this->vip->id => 0],
        ]));
    }

    public function test_une_erreur_inattendue_est_rattrapee_et_rien_n_est_cree(): void
    {
        $this->mock(EvenementCreationService::class)
            ->shouldReceive('create')
            ->andThrow(new \RuntimeException('Panne base de données'));

        $this->actingAs($this->creerAdmin())
            ->from(route('evenements.create'))
            ->post(route('evenements.web.store'), $this->donneesEvenement($this->vip))
            ->assertRedirect(route('evenements.create'))
            ->assertSessionHas('error');

        $this->assertSame(0, Evenement::count());
        Mail::assertNothingSent();
    }

    public function test_un_echec_d_envoi_du_mail_n_annule_pas_la_creation(): void
    {
        Mail::shouldReceive('to')->andThrow(new \RuntimeException('SMTP indisponible'));

        $this->actingAs($this->creerAdmin())
            ->post(route('evenements.web.store'), $this->donneesEvenement($this->vip))
            ->assertRedirect(route('evenements.index'))
            ->assertSessionHas('success', fn (string $message) => str_contains($message, 'n\'a pas pu être envoyé'));

        $evenement = Evenement::firstOrFail();
        $this->assertNull($evenement->mail_sent_at);
        $this->assertSame('MAIL_SEND_FAILED', $evenement->last_mail_error);
        $this->assertSame(1, (int) $evenement->mail_send_attempts);
        $this->assertSame(1, Organisateur::count());
        $this->assertSame(1, User::where('role', 'scanneur')->count());
    }
}
