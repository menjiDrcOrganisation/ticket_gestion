<?php

namespace Tests\Feature\Scanneur;

use App\Models\Billet;
use App\Models\Evenement;
use App\Models\Scanneur;
use App\Models\TypeBillet;
use App\Models\User;
use App\Services\EvenementCreationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Tests\Concerns\CreeDesDonneesMetier;
use Tests\TestCase;

/**
 * Création automatique du scanneur d'un événement et utilisation de ses identifiants.
 */
class CreationScanneurTest extends TestCase
{
    use CreeDesDonneesMetier;
    use RefreshDatabase;

    private TypeBillet $typeBillet;

    protected function setUp(): void
    {
        parent::setUp();

        Mail::fake();
        Storage::fake('public');

        $this->typeBillet = $this->creerTypeBillet('VIP');
    }

    /**
     * @return array{evenement: Evenement, scanneur_email: string, scanneur_code: string}
     */
    private function creerEvenementAvecScanneur(array $surcharges = []): array
    {
        return app(EvenementCreationService::class)->create($this->donneesEvenement($this->typeBillet, $surcharges));
    }

    public function test_un_scanneur_est_cree_avec_l_evenement(): void
    {
        $creation = $this->creerEvenementAvecScanneur(['nom_evenement' => 'Grand Gala 2026']);

        $scanneur = $creation['evenement']->scanneur;
        $this->assertInstanceOf(Scanneur::class, $scanneur);

        $user = $scanneur->user;
        $this->assertSame('scanneur', $user->role);
        $this->assertSame($creation['scanneur_email'], $user->email);
        $this->assertSame('Scanneur - Grand Gala 2026', $user->name);
        $this->assertTrue(Hash::check($creation['scanneur_code'], $user->password));
        $this->assertFalse((bool) $user->must_change_password);

        // Format de l'identifiant : scan-<slug>-<6 caractères>@<domaine configuré>.
        $this->assertMatchesRegularExpression('/^scan-grand-gala-2026-[a-z0-9]{6}@scanneur\.example\.test$/', $user->email);
        $this->assertSame(10, strlen($creation['scanneur_code']));
    }

    public function test_un_nom_d_evenement_sans_caractere_latin_produit_un_email_valide(): void
    {
        $creation = $this->creerEvenementAvecScanneur(['nom_evenement' => '!!!']);

        $this->assertMatchesRegularExpression('/^scan-[a-z0-9]{6}@scanneur\.example\.test$/', $creation['scanneur_email']);
    }

    public function test_la_creation_sans_scanneur_est_possible(): void
    {
        $creation = app(EvenementCreationService::class)->create($this->donneesEvenement($this->typeBillet), false);

        $this->assertNull($creation['evenement']->scanneur_id);
        $this->assertNull($creation['scanneur_email']);
        $this->assertSame(0, User::where('role', 'scanneur')->count());
    }

    public function test_le_scanneur_se_connecte_avec_les_identifiants_generes(): void
    {
        $creation = $this->creerEvenementAvecScanneur();

        $this->post('/login', [
            'email' => $creation['scanneur_email'],
            'password' => $creation['scanneur_code'],
        ])->assertRedirect(route('dashboard_orginasateur.show'));

        $this->assertAuthenticatedAs($creation['evenement']->scanneur->user);
    }

    public function test_le_renvoi_du_mail_regenere_le_mot_de_passe_du_scanneur(): void
    {
        $creation = $this->creerEvenementAvecScanneur();
        $scanneurUser = $creation['evenement']->scanneur->user;

        $this->actingAs($this->creerAdmin())
            ->post(route('evenements.resendMail', $creation['evenement']->id))
            ->assertSessionHas('success');

        $this->assertFalse(Hash::check($creation['scanneur_code'], $scanneurUser->fresh()->password));
    }

    // ---- Utilisation : scan des billets -------------------------------------------------

    public function test_le_scanneur_valide_un_billet_de_son_evenement(): void
    {
        $evenement = $this->creerEvenementAvecScanneur()['evenement'];
        $billet = $this->vendreBillet($evenement, $this->typeBillet, 3);

        $this->actingAs($evenement->scanneur->user)
            ->postJson(route('scanneur.previewScanne'), ['code' => $billet->code_billet])
            ->assertOk()
            ->assertJson(['valid' => true, 'quantite_fictif' => 3, 'message' => 'Billet validé']);

        $this->actingAs($evenement->scanneur->user)
            ->postJson(route('scanneur.processScan'), ['code' => $billet->code_billet, 'quantite' => 2])
            ->assertOk()
            ->assertJsonPath('valid', true);

        $this->assertSame(1, $billet->fresh()->quantite_fictif);
        $this->assertSame('valide', $billet->fresh()->statut);
    }

    public function test_le_dernier_passage_marque_le_billet_comme_utilise(): void
    {
        $evenement = $this->creerEvenementAvecScanneur()['evenement'];
        $billet = $this->vendreBillet($evenement, $this->typeBillet, 1);

        $this->actingAs($evenement->scanneur->user)
            ->postJson(route('scanneur.processScan'), ['code' => $billet->code_billet, 'quantite' => 1])
            ->assertOk()
            ->assertJsonPath('message', 'Dernier billet utilisé');

        $billet->refresh();
        $this->assertSame(0, $billet->quantite_fictif);
        $this->assertSame('utilisee', $billet->statut);
    }

    public function test_un_code_inconnu_est_refuse(): void
    {
        $evenement = $this->creerEvenementAvecScanneur()['evenement'];

        $this->actingAs($evenement->scanneur->user)
            ->postJson(route('scanneur.processScan'), ['code' => 'INCONNU', 'quantite' => 1])
            ->assertNotFound()
            ->assertJsonPath('valid', false);
    }

    public function test_un_scanneur_ne_peut_pas_valider_le_billet_d_un_autre_evenement(): void
    {
        $evenementA = $this->creerEvenementAvecScanneur(['nom_evenement' => 'A'])['evenement'];
        $evenementB = $this->creerEvenementAvecScanneur(['nom_evenement' => 'B'])['evenement'];
        $billetB = $this->vendreBillet($evenementB, $this->typeBillet, 2);

        // Code d'un autre événement : introuvable dans son périmètre.
        $this->actingAs($evenementA->scanneur->user)
            ->postJson(route('scanneur.processScan'), ['code' => $billetB->code_billet, 'quantite' => 1])
            ->assertNotFound();

        // Ciblage explicite d'un autre événement : interdit.
        $this->actingAs($evenementA->scanneur->user)
            ->postJson(route('scanneur.processScan'), ['code' => $billetB->code_billet, 'quantite' => 1, 'event_id' => $evenementB->id])
            ->assertForbidden();

        $this->assertSame(2, $billetB->fresh()->quantite_fictif);
    }

    public function test_un_utilisateur_non_connecte_ne_peut_pas_scanner(): void
    {
        $evenement = $this->creerEvenementAvecScanneur()['evenement'];
        $billet = $this->vendreBillet($evenement, $this->typeBillet, 1);

        $this->postJson(route('scanneur.processScan'), ['code' => $billet->code_billet, 'quantite' => 1])
            ->assertForbidden();

        $this->assertSame(1, Billet::findOrFail($billet->id)->quantite_fictif);
    }
}
