<?php

namespace Tests\Feature;

use App\Mail\EnvoiMotDePasseMail;
use App\Mail\EvenementCreeMail;
use App\Models\Evenement;
use App\Models\Organisateur;
use App\Models\TypeBillet;
use App\Models\User;
use App\Services\EvenementCreationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class CreationEvenementOrganisateurTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private TypeBillet $typeBillet;

    protected function setUp(): void
    {
        parent::setUp();

        Mail::fake();
        Storage::fake('public');

        $this->admin = User::factory()->create(['role' => 'admin']);
        $this->typeBillet = TypeBillet::create(['nom_type' => 'VIP']);
    }

    private function payload(array $overrides = []): array
    {
        $id = $this->typeBillet->id;

        return array_merge([
            'nom_evenement' => 'Concert de test',
            'type_evenement_nom' => 'Concert',
            'nom_organisateur' => 'Jean Organisateur',
            'email_organisateur' => 'orga@example.com',
            'telephone' => '0990000000',
            'adresse' => '1 avenue du Test',
            'salle' => 'Salle A',
            'date_debut' => '2026-12-01',
            'date_fin' => '2026-12-01',
            'heure_debut' => '18:00',
            'heure_fin' => '23:00',
            'ticket_type_id' => [$id],
            'quantite' => [$id => 100],
            'prix' => [$id => 10],
            'devise' => [$id => 'USD'],
            'nom_artiste' => 'Artiste',
            'acroche' => 'Accroche',
            'a_propos' => 'A propos',
            'photo_affiche' => UploadedFile::fake()->image('affiche.jpg'),
        ], $overrides);
    }

    private function creerViaWeb(array $overrides = [])
    {
        return $this->actingAs($this->admin)->post(route('evenements.web.store'), $this->payload($overrides));
    }

    // ---- Cas 1 : nouvel organisateur -------------------------------------------------

    public function test_nouvel_organisateur_compte_evenement_et_scanneur_crees(): void
    {
        $response = $this->creerViaWeb();

        $response->assertRedirect(route('evenements.index'));
        $response->assertSessionHas('success', fn ($m) => str_contains($m, 'Votre événement a été créé avec succès'));
        $response->assertSessionHas('scanneur_credentials');

        $user = User::where('email', 'orga@example.com')->firstOrFail();
        $this->assertSame('organisateur', $user->role);
        $this->assertTrue($user->must_change_password);
        $this->assertNotNull($user->organisateur);

        $evenement = Evenement::firstOrFail();
        $this->assertSame($user->organisateur->id, $evenement->organisateur_id);
        $this->assertNotNull($evenement->scanneur_id);
        $this->assertSame('scanneur', $evenement->scanneur->user->role);
        $this->assertNotNull($evenement->mail_sent_at);

        Mail::assertSent(EnvoiMotDePasseMail::class, function (EnvoiMotDePasseMail $mail) use ($user, $evenement) {
            return $mail->hasTo('orga@example.com')
                && Hash::check($mail->mot_de_passe, $user->password)
                && $mail->email_scanneur === $evenement->scanneur->user->email
                && Hash::check($mail->mot_de_passe_scanneur, $evenement->scanneur->user->password)
                && $mail->evenement?->is($evenement);
        });
        Mail::assertNotSent(EvenementCreeMail::class);
    }

    public function test_nouvel_organisateur_doit_fournir_nom_et_telephone(): void
    {
        $response = $this->creerViaWeb(['nom_organisateur' => '', 'telephone' => '']);

        $response->assertSessionHasErrors(['nom_organisateur', 'telephone']);
        $this->assertSame(0, Evenement::count());
    }

    // ---- Cas 2 : organisateur existant -----------------------------------------------

    public function test_organisateur_existant_cree_un_deuxieme_evenement_sans_nouveau_compte(): void
    {
        $this->creerViaWeb();
        $user = User::where('email', 'orga@example.com')->firstOrFail();
        $user->update(['password' => Hash::make('MonMotDePasse1'), 'must_change_password' => false]);
        $premierScanneurId = Evenement::firstOrFail()->scanneur_id;

        Mail::fake();

        // Informations déjà enregistrées non redemandées ; e-mail saisi avec une casse différente.
        $response = $this->creerViaWeb([
            'nom_evenement' => 'Deuxième concert',
            'email_organisateur' => '  ORGA@example.com ',
            'nom_organisateur' => null,
            'telephone' => null,
        ]);

        $response->assertSessionHasNoErrors();
        $response->assertSessionHas('success', fn ($m) => str_contains($m, 'Votre événement a été créé avec succès'));
        $response->assertSessionHas('scanneur_credentials');

        $this->assertSame(1, User::where('role', 'organisateur')->count());
        $this->assertSame(1, Organisateur::count());
        $this->assertSame(2, $user->organisateur->evenements()->count());

        $deuxieme = Evenement::where('nom', 'Deuxième concert')->firstOrFail();
        $this->assertNotSame($premierScanneurId, $deuxieme->scanneur_id);

        // Le mot de passe de l'organisateur n'est pas touché.
        $user->refresh();
        $this->assertTrue(Hash::check('MonMotDePasse1', $user->password));
        $this->assertFalse($user->must_change_password);
        $this->assertSame('Jean Organisateur', $user->name);

        Mail::assertNotSent(EnvoiMotDePasseMail::class);
        Mail::assertSent(EvenementCreeMail::class, fn (EvenementCreeMail $mail) => $mail->hasTo('orga@example.com')
            && $mail->evenement->is($deuxieme)
            && $mail->email_scanneur === $deuxieme->scanneur->user->email);
    }

    public function test_email_utilise_par_un_autre_role_est_refuse(): void
    {
        User::factory()->create(['email' => 'scan@example.com', 'role' => 'scanneur']);

        $response = $this->creerViaWeb(['email_organisateur' => 'scan@example.com']);

        $response->assertSessionHasErrors('email_organisateur');
        $this->assertSame(0, Evenement::count());
    }

    // ---- Règles transverses ----------------------------------------------------------

    public function test_chaque_evenement_a_un_scanneur_aux_identifiants_uniques(): void
    {
        $service = app(EvenementCreationService::class);
        $emails = [];

        foreach (range(1, 5) as $i) {
            $creation = $service->create($this->payload(['nom_evenement' => 'Même nom']));
            $emails[] = $creation['scanneur_email'];
        }

        $this->assertCount(5, array_unique($emails));
        $this->assertSame(5, Evenement::distinct()->count('scanneur_id'));
        $this->assertSame(1, User::where('role', 'organisateur')->count());
    }

    public function test_creation_atomique_rien_n_est_cree_en_cas_d_echec(): void
    {
        $id = $this->typeBillet->id;

        try {
            // Aucun billet valide → l'exception est levée après la création de l'organisateur et du scanneur.
            app(EvenementCreationService::class)->create($this->payload(['quantite' => [$id => 0]]));
            $this->fail('Une ValidationException était attendue.');
        } catch (ValidationException $e) {
            $this->assertArrayHasKey('ticket_type_id', $e->errors());
        }

        $this->assertSame(0, User::whereIn('role', ['organisateur', 'scanneur'])->count());
        $this->assertSame(0, Organisateur::count());
        $this->assertSame(0, Evenement::count());
        $this->assertEmpty(Storage::disk('public')->allFiles('affiches'));
    }

    public function test_api_indique_si_l_organisateur_existait(): void
    {
        $premiere = $this->postJson('/api/v1/evenements', $this->payload());
        $premiere->assertCreated()
            ->assertJsonPath('organisateur_existant', false)
            ->assertJsonPath('changement_mot_de_passe_requis', true);
        $this->assertNotNull($premiere->json('credentials.organisateur_code'));

        $seconde = $this->postJson('/api/v1/evenements', $this->payload([
            'nom_evenement' => 'Autre',
            'nom_organisateur' => null,
            'telephone' => null,
        ]));
        $seconde->assertCreated()
            ->assertJsonPath('message', 'Votre événement a été créé avec succès')
            ->assertJsonPath('organisateur_existant', true)
            ->assertJsonPath('credentials.organisateur_code', null);
        $this->assertNotNull($seconde->json('credentials.scanneur_email'));
        $this->assertNotSame($premiere->json('credentials.scanneur_email'), $seconde->json('credentials.scanneur_email'));

        $this->assertSame(1, User::where('email', 'orga@example.com')->count());
    }

    // ---- Changement de mot de passe obligatoire ---------------------------------------

    public function test_mot_de_passe_temporaire_force_le_changement(): void
    {
        $user = User::factory()->create([
            'role' => 'organisateur',
            'password' => Hash::make('Temporaire123'),
            'must_change_password' => true,
        ]);

        $this->actingAs($user)->get(route('dashboard_orginasateur.show'))
            ->assertRedirect(route('password.change'));

        $this->actingAs($user)->get(route('password.change'))->assertOk();

        $this->actingAs($user)->put(route('password.update'), [
            'current_password' => 'Temporaire123',
            'password' => 'NouveauMotDePasse1',
            'password_confirmation' => 'NouveauMotDePasse1',
        ])->assertRedirect(route('home'));

        $user->refresh();
        $this->assertFalse($user->must_change_password);
        $this->assertTrue(Hash::check('NouveauMotDePasse1', $user->password));
    }

    public function test_renvoi_mail_ne_reinitialise_pas_un_organisateur_actif(): void
    {
        $this->creerViaWeb();
        $user = User::where('email', 'orga@example.com')->firstOrFail();
        $user->update(['password' => Hash::make('MonMotDePasse1'), 'must_change_password' => false]);
        $evenement = Evenement::firstOrFail();

        Mail::fake();

        $this->actingAs($this->admin)->post(route('evenements.resendMail', $evenement->id))
            ->assertSessionHas('success');

        $this->assertTrue(Hash::check('MonMotDePasse1', $user->fresh()->password));
        Mail::assertSent(EvenementCreeMail::class);
        Mail::assertNotSent(EnvoiMotDePasseMail::class);
    }

    public function test_lookup_organisateur(): void
    {
        $this->actingAs($this->admin)->get(route('evenements.create'))
            ->assertOk()
            ->assertSee('organisateur-existant', false)
            ->assertSee(route('evenements.organisateurLookup'), false);

        $this->creerViaWeb();

        $this->actingAs($this->admin)
            ->getJson(route('evenements.organisateurLookup', ['email' => 'ORGA@example.com']))
            ->assertOk()
            ->assertJson(['exists' => true, 'name' => 'Jean Organisateur', 'evenements_count' => 1]);

        $this->actingAs($this->admin)
            ->getJson(route('evenements.organisateurLookup', ['email' => 'inconnu@example.com']))
            ->assertJson(['exists' => false]);

        $organisateur = User::where('email', 'orga@example.com')->first();
        $organisateur->update(['must_change_password' => false]);
        $this->actingAs($organisateur)
            ->getJson(route('evenements.organisateurLookup', ['email' => 'orga@example.com']))
            ->assertForbidden();
    }
}
