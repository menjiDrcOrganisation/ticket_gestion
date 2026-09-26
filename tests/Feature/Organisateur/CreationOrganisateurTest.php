<?php

namespace Tests\Feature\Organisateur;

use App\Models\Organisateur;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\Concerns\CreeDesDonneesMetier;
use Tests\TestCase;

/**
 * Gestion des organisateurs par l'administrateur (création directe, hors création d'événement).
 */
class CreationOrganisateurTest extends TestCase
{
    use CreeDesDonneesMetier;
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = $this->creerAdmin();
    }

    private function donnees(array $surcharges = []): array
    {
        return array_merge([
            'name' => 'Marie Organisatrice',
            'email' => 'marie@example.com',
            'password' => 'MotDePasse123',
            'telephone' => '0991112233',
        ], $surcharges);
    }

    public function test_l_admin_cree_un_organisateur(): void
    {
        $this->actingAs($this->admin)
            ->post(route('admin.organisateurs.store'), $this->donnees())
            ->assertSessionHasNoErrors()
            ->assertSessionHas('success');

        $user = User::where('email', 'marie@example.com')->firstOrFail();
        $this->assertSame('organisateur', $user->role);
        $this->assertSame('Marie Organisatrice', $user->name);
        $this->assertTrue(Hash::check('MotDePasse123', $user->password));
        $this->assertSame('0991112233', $user->organisateur->telephone);
    }

    public function test_l_organisateur_cree_peut_se_connecter(): void
    {
        $this->actingAs($this->admin)->post(route('admin.organisateurs.store'), $this->donnees());
        auth()->logout();

        $this->post('/login', ['email' => 'marie@example.com', 'password' => 'MotDePasse123'])
            ->assertRedirect(route('dashboard_orginasateur.show'));

        $this->assertAuthenticatedAs(User::where('email', 'marie@example.com')->first());
    }

    public static function donneesInvalides(): array
    {
        return [
            'nom manquant' => [['name' => ''], 'name'],
            'email manquant' => [['email' => ''], 'email'],
            'email invalide' => [['email' => 'marie'], 'email'],
            'mot de passe trop court' => [['password' => 'court'], 'password'],
            'téléphone manquant' => [['telephone' => ''], 'telephone'],
            'téléphone trop long' => [['telephone' => str_repeat('9', 16)], 'telephone'],
        ];
    }

    #[DataProvider('donneesInvalides')]
    public function test_validation_du_formulaire_organisateur(array $surcharges, string $champ): void
    {
        $this->actingAs($this->admin)
            ->post(route('admin.organisateurs.store'), $this->donnees($surcharges))
            ->assertSessionHasErrors($champ);

        $this->assertSame(0, Organisateur::count());
    }

    public function test_un_email_deja_utilise_est_refuse(): void
    {
        User::factory()->create(['email' => 'marie@example.com', 'role' => 'scanneur']);

        $this->actingAs($this->admin)
            ->post(route('admin.organisateurs.store'), $this->donnees())
            ->assertSessionHasErrors('email');

        $this->assertSame(0, Organisateur::count());
    }

    public function test_rollback_si_la_creation_du_profil_organisateur_echoue(): void
    {
        // Simule une panne à l'insertion du profil, après la création du compte utilisateur.
        Organisateur::creating(function (): void {
            throw new \RuntimeException('Insertion organisateur impossible');
        });

        $this->withoutExceptionHandling();

        try {
            $this->actingAs($this->admin)->post(route('admin.organisateurs.store'), $this->donnees());
            $this->fail('Une exception était attendue.');
        } catch (\RuntimeException $e) {
            $this->assertSame('Insertion organisateur impossible', $e->getMessage());
        }

        $this->assertDatabaseMissing('users', ['email' => 'marie@example.com']);
        $this->assertSame(0, Organisateur::count());
    }

    public function test_mise_a_jour_d_un_organisateur_sans_changer_son_mot_de_passe(): void
    {
        $organisateur = $this->creerOrganisateur(['email' => 'ancien@example.com', 'password' => Hash::make('Inchange123')]);

        $this->actingAs($this->admin)
            ->put(route('admin.organisateurs.update', $organisateur), [
                'name' => 'Nouveau Nom',
                'email' => 'nouveau@example.com',
                'telephone' => '0812223344',
                'password' => '',
            ])
            ->assertSessionHasNoErrors();

        $user = $organisateur->user->fresh();
        $this->assertSame('Nouveau Nom', $user->name);
        $this->assertSame('nouveau@example.com', $user->email);
        $this->assertTrue(Hash::check('Inchange123', $user->password));
        $this->assertSame('0812223344', $organisateur->fresh()->telephone);
    }

    public function test_mise_a_jour_peut_conserver_son_propre_email(): void
    {
        $organisateur = $this->creerOrganisateur(['email' => 'marie@example.com']);

        $this->actingAs($this->admin)
            ->put(route('admin.organisateurs.update', $organisateur), $this->donnees(['password' => null]))
            ->assertSessionHasNoErrors();
    }

    public function test_suppression_d_un_organisateur_sans_evenement(): void
    {
        $organisateur = $this->creerOrganisateur();
        $userId = $organisateur->user_id;

        $this->actingAs($this->admin)
            ->delete(route('admin.organisateurs.destroy', $organisateur))
            ->assertSessionHas('success');

        $this->assertModelMissing($organisateur);
        $this->assertDatabaseMissing('users', ['id' => $userId]);
    }

    public function test_un_organisateur_lie_a_des_evenements_ne_peut_pas_etre_supprime(): void
    {
        $organisateur = $this->creerOrganisateur();
        $this->creerEvenement($organisateur);

        $this->actingAs($this->admin)
            ->delete(route('admin.organisateurs.destroy', $organisateur))
            ->assertSessionHas('error');

        $this->assertModelExists($organisateur);
        $this->assertModelExists($organisateur->user);
    }
}
