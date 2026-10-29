<?php

namespace Tests\Feature\Evenement;

use App\Mail\EnvoiMotDePasseMail;
use App\Mail\EvenementCreeMail;
use App\Models\Evenement;
use App\Models\Organisateur;
use App\Models\TypeBillet;
use App\Models\User;
use App\Services\EvenementCreationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Tests\Concerns\CreeDesDonneesMetier;
use Tests\TestCase;

class PlusieursEvenementsOrganisateurTest extends TestCase
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
     * @return array<int, Evenement>
     */
    private function creerTroisEvenementsPourLeMemeOrganisateur(): array
    {
        $admin = $this->creerAdmin();
        $evenements = [];

        foreach (['Concert A', 'Concert B', 'Concert C'] as $i => $nom) {
            // Seul le premier formulaire fournit nom et téléphone : l'organisateur est ensuite reconnu par son e-mail.
            $surcharges = ['nom_evenement' => $nom];
            if ($i > 0) {
                $surcharges += ['nom_organisateur' => null, 'telephone' => null];
            }

            $this->actingAs($admin)
                ->post(route('evenements.web.store'), $this->donneesEvenement($this->typeBillet, $surcharges))
                ->assertSessionHasNoErrors();

            $evenements[] = Evenement::where('nom', $nom)->firstOrFail();
        }

        return $evenements;
    }

    public function test_un_organisateur_peut_avoir_plusieurs_evenements(): void
    {
        $evenements = $this->creerTroisEvenementsPourLeMemeOrganisateur();

        $this->assertSame(1, User::where('role', 'organisateur')->count());
        $this->assertSame(1, Organisateur::count());

        $organisateur = Organisateur::firstOrFail();
        $this->assertEqualsCanonicalizing(
            array_map(fn (Evenement $e) => $e->id, $evenements),
            $organisateur->evenements()->pluck('id')->all()
        );

        // Chaque événement a son propre scanneur.
        $this->assertCount(3, array_unique(array_map(fn (Evenement $e) => $e->scanneur_id, $evenements)));
        $this->assertSame(3, User::where('role', 'scanneur')->count());

        // Mot de passe temporaire uniquement pour le premier événement.
        Mail::assertSent(EnvoiMotDePasseMail::class, 1);
        Mail::assertSent(EvenementCreeMail::class, 2);
    }

    public function test_le_tableau_de_bord_de_l_organisateur_liste_tous_ses_evenements(): void
    {
        $this->creerTroisEvenementsPourLeMemeOrganisateur();
        $autre = $this->creerEvenement(null, null, ['nom' => 'Evenement d\'un autre organisateur']);

        $organisateurUser = User::where('role', 'organisateur')->where('email', 'orga@example.com')->firstOrFail();
        $organisateurUser->update(['must_change_password' => false]);

        $response = $this->actingAs($organisateurUser)->get(route('dashboard_orginasateur.show'))->assertOk();

        $liste = $response->viewData('evenementsOrganisateur');
        $this->assertEqualsCanonicalizing(['Concert A', 'Concert B', 'Concert C'], $liste->pluck('nom')->all());
        $this->assertNotContains($autre->id, $liste->pluck('id')->all());
    }

    public function test_le_tableau_de_bord_filtre_par_evenement_de_l_organisateur(): void
    {
        [$a, $b] = $this->creerTroisEvenementsPourLeMemeOrganisateur();
        $this->vendreBillet($a, $this->typeBillet, 2);
        $this->vendreBillet($b, $this->typeBillet, 5);

        $organisateurUser = User::where('email', 'orga@example.com')->firstOrFail();
        $organisateurUser->update(['must_change_password' => false]);

        $this->actingAs($organisateurUser)
            ->get(route('dashboard_orginasateur.show', ['event_id' => $b->id]))
            ->assertOk()
            ->assertViewHas('selectedEventId', $b->id)
            ->assertViewHas('totalBilletsVendus', 5);

        $this->actingAs($organisateurUser)
            ->get(route('dashboard_orginasateur.show'))
            ->assertViewHas('totalBilletsVendus', 7);
    }

    public function test_chaque_scanneur_ne_voit_que_son_propre_evenement(): void
    {
        [$a, $b] = $this->creerTroisEvenementsPourLeMemeOrganisateur();

        $scanneurA = $a->scanneur->user;

        $this->actingAs($scanneurA)
            ->get(route('dashboard_orginasateur.show'))
            ->assertOk()
            ->assertViewHas('evenementsOrganisateur', fn ($liste) => $liste->pluck('id')->all() === [$a->id]);

        $this->assertNotSame($a->scanneur_id, $b->scanneur_id);
    }

    public function test_creation_concurrente_de_plusieurs_evenements_via_le_service(): void
    {
        $service = app(EvenementCreationService::class);

        $resultats = collect(range(1, 4))->map(fn (int $i) => $service->create(
            $this->donneesEvenement($this->typeBillet, ['nom_evenement' => "Soirée $i"])
        ));

        $this->assertFalse($resultats[0]['organisateur_existant']);
        $this->assertNotNull($resultats[0]['organisateur_code']);

        foreach ($resultats->slice(1) as $resultat) {
            $this->assertTrue($resultat['organisateur_existant']);
            $this->assertNull($resultat['organisateur_code'], 'Aucun nouveau mot de passe pour un organisateur existant.');
            $this->assertSame($resultats[0]['organisateur']->id, $resultat['organisateur']->id);
        }

        $this->assertSame(4, Organisateur::firstOrFail()->evenements()->count());
    }
}
