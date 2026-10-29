<?php

namespace Tests\Feature\Evenement;

use App\Models\Evenement;
use App\Models\TypeBillet;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\Concerns\CreeDesDonneesMetier;
use Tests\TestCase;

/**
 * Règles de validation du formulaire de création d'événement (StoreEvenementRequest).
 */
class ValidationFormulaireEvenementTest extends TestCase
{
    use CreeDesDonneesMetier;
    use RefreshDatabase;

    private User $admin;
    private TypeBillet $typeBillet;

    protected function setUp(): void
    {
        parent::setUp();

        Mail::fake();
        Storage::fake('public');

        $this->admin = $this->creerAdmin();
        $this->typeBillet = $this->creerTypeBillet('VIP');
    }

    /**
     * Chaque cas : [surcharges du formulaire (closure recevant l'id du type de billet), champ en erreur attendu ({id} = id du type de billet)].
     */
    public static function donneesInvalides(): array
    {
        return [
            'nom manquant' => [fn (int $id) => ['nom_evenement' => ''], 'nom_evenement'],
            'nom trop long' => [fn (int $id) => ['nom_evenement' => str_repeat('a', 256)], 'nom_evenement'],
            'type d\'événement absent' => [fn (int $id) => ['type_evenement_nom' => null, 'type_evenement_id' => null], 'type_evenement_id'],
            'type d\'événement inexistant' => [fn (int $id) => ['type_evenement_nom' => null, 'type_evenement_id' => 999], 'type_evenement_id'],
            'email manquant' => [fn (int $id) => ['email_organisateur' => ''], 'email_organisateur'],
            'email invalide' => [fn (int $id) => ['email_organisateur' => 'pas-un-email'], 'email_organisateur'],
            'adresse manquante' => [fn (int $id) => ['adresse' => ''], 'adresse'],
            'salle manquante' => [fn (int $id) => ['salle' => ''], 'salle'],
            'date de début invalide' => [fn (int $id) => ['date_debut' => 'demain'], 'date_debut'],
            'date de fin avant le début' => [fn (int $id) => ['date_debut' => '2026-12-10', 'date_fin' => '2026-12-09'], 'date_fin'],
            'heure au mauvais format' => [fn (int $id) => ['heure_debut' => '18h'], 'heure_debut'],
            'heure de fin avant le début' => [fn (int $id) => ['heure_debut' => '20:00', 'heure_fin' => '19:00'], 'heure_fin'],
            'aucun type de billet' => [fn (int $id) => ['ticket_type_id' => []], 'ticket_type_id'],
            'type de billet inexistant' => [fn (int $id) => ['ticket_type_id' => [999]], 'ticket_type_id.0'],
            'type de billet en double' => [fn (int $id) => ['ticket_type_id' => [$id, $id]], 'ticket_type_id.0'],
            'quantité négative' => [fn (int $id) => ['quantite' => [$id => -1]], 'quantite.{id}'],
            'quantité non entière' => [fn (int $id) => ['quantite' => [$id => 'dix']], 'quantite.{id}'],
            'prix négatif' => [fn (int $id) => ['prix' => [$id => -5]], 'prix.{id}'],
            'devise non supportée' => [fn (int $id) => ['devise' => [$id => 'EUR']], 'devise.{id}'],
            'nom d\'artiste manquant' => [fn (int $id) => ['nom_artiste' => ''], 'nom_artiste'],
            'accroche manquante' => [fn (int $id) => ['acroche' => ''], 'acroche'],
            'à propos manquant' => [fn (int $id) => ['a_propos' => ''], 'a_propos'],
            'affiche manquante' => [fn (int $id) => ['photo_affiche' => null], 'photo_affiche'],
            'affiche qui n\'est pas une image' => [fn (int $id) => ['photo_affiche' => UploadedFile::fake()->create('affiche.pdf', 10, 'application/pdf')], 'photo_affiche'],
            'affiche trop lourde' => [fn (int $id) => ['photo_affiche' => UploadedFile::fake()->image('affiche.jpg')->size(6000)], 'photo_affiche'],
            'nom organisateur manquant (nouveau)' => [fn (int $id) => ['nom_organisateur' => ''], 'nom_organisateur'],
            'téléphone manquant (nouveau)' => [fn (int $id) => ['telephone' => ''], 'telephone'],
            'téléphone trop long' => [fn (int $id) => ['telephone' => str_repeat('9', 31)], 'telephone'],
        ];
    }

    #[DataProvider('donneesInvalides')]
    public function test_le_formulaire_refuse_les_donnees_invalides(\Closure $surcharges, string $champ): void
    {
        $this->actingAs($this->admin)
            ->post(route('evenements.web.store'), $this->donneesEvenement($this->typeBillet, $surcharges($this->typeBillet->id)))
            ->assertSessionHasErrors(str_replace('{id}', (string) $this->typeBillet->id, $champ));

        $this->assertSame(0, Evenement::count());
    }

    public function test_le_formulaire_accepte_des_donnees_valides(): void
    {
        $this->actingAs($this->admin)
            ->post(route('evenements.web.store'), $this->donneesEvenement($this->typeBillet))
            ->assertSessionHasNoErrors();

        $this->assertSame(1, Evenement::count());
    }

    public function test_nom_et_telephone_facultatifs_pour_un_organisateur_existant(): void
    {
        $this->creerOrganisateur(['email' => 'orga@example.com']);

        $this->actingAs($this->admin)
            ->post(route('evenements.web.store'), $this->donneesEvenement($this->typeBillet, [
                'nom_organisateur' => '',
                'telephone' => '',
            ]))
            ->assertSessionHasNoErrors();

        $this->assertSame(1, Evenement::count());
    }

    public function test_un_email_deja_utilise_par_un_admin_est_refuse(): void
    {
        $this->creerAdmin(['email' => 'orga@example.com']);

        $this->actingAs($this->admin)
            ->post(route('evenements.web.store'), $this->donneesEvenement($this->typeBillet))
            ->assertSessionHasErrors('email_organisateur');
    }

    // ---- Autres formulaires ------------------------------------------------------------

    public static function statutsInvalides(): array
    {
        return [
            'vide' => [''],
            'inconnu' => ['archive'],
            'hors enum base de données' => ['actif'],
        ];
    }

    #[DataProvider('statutsInvalides')]
    public function test_le_formulaire_de_statut_refuse_les_valeurs_invalides(string $statut): void
    {
        $evenement = $this->creerEvenement();

        $this->actingAs($this->admin)
            ->patch(route('evenements.updateStatus', $evenement->id), ['statut' => $statut])
            ->assertSessionHasErrors('statut');

        $this->assertSame('encours', $evenement->fresh()->statut);
    }

    public function test_le_formulaire_de_modification_refuse_une_date_de_fin_avant_le_debut(): void
    {
        $evenement = $this->creerEvenement();

        $this->actingAs($this->admin)
            ->put(route('evenements.update', $evenement->id), [
                'date_debut' => '2026-12-10',
                'date_fin' => '2026-12-01',
            ])
            ->assertSessionHasErrors('date_fin');
    }
}
