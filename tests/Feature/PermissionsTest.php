<?php

namespace Tests\Feature;

use App\Http\Middleware\RoleMiddleware;
use App\Models\Evenement;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\Concerns\CreeDesDonneesMetier;
use Tests\TestCase;

class PermissionsTest extends TestCase
{
    use CreeDesDonneesMetier;
    use RefreshDatabase;

    // ---- RoleMiddleware ------------------------------------------------------------------

    private function routeProtegee(string $roles): string
    {
        Route::middleware(['web', RoleMiddleware::class . ':' . $roles])
            ->get('/_test/protegee', fn () => 'ok');

        return '/_test/protegee';
    }

    public function test_le_middleware_de_role_redirige_un_invite_vers_la_connexion(): void
    {
        $this->get($this->routeProtegee('admin'))->assertRedirect(route('login'));
    }

    public function test_le_middleware_de_role_autorise_le_bon_role(): void
    {
        $url = $this->routeProtegee('admin,organisateur');

        $this->actingAs(User::factory()->create(['role' => 'admin']))->get($url)->assertOk()->assertSee('ok');
        $this->actingAs(User::factory()->create(['role' => 'organisateur']))->get($url)->assertOk();
    }

    public function test_le_middleware_de_role_refuse_un_autre_role(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'scanneur']))
            ->get($this->routeProtegee('admin,organisateur'))
            ->assertForbidden();
    }

    // ---- Gestion des événements : administrateurs uniquement ------------------------------

    public static function routesAdminEvenements(): array
    {
        return [
            'liste' => ['get', 'evenements.index', false],
            'formulaire de création' => ['get', 'evenements.create', false],
            'création' => ['post', 'evenements.web.store', false],
            'recherche organisateur' => ['get', 'evenements.organisateurLookup', false],
            'modification' => ['put', 'evenements.update', true],
            'changement de statut' => ['patch', 'evenements.updateStatus', true],
            'suppression' => ['delete', 'evenements.destroy', true],
            'renvoi du mail' => ['post', 'evenements.resendMail', true],
        ];
    }

    private function appeler(string $methode, string $route, bool $avecId)
    {
        $url = $avecId ? route($route, $this->creerEvenement()->id) : route($route);

        return $this->call(strtoupper($methode), $url, ['statut' => 'ferme']);
    }

    #[DataProvider('routesAdminEvenements')]
    public function test_un_invite_ne_peut_pas_gerer_les_evenements(string $methode, string $route, bool $avecId): void
    {
        $this->appeler($methode, $route, $avecId)->assertRedirect(route('login'));
    }

    #[DataProvider('routesAdminEvenements')]
    public function test_un_organisateur_ne_peut_pas_gerer_les_evenements(string $methode, string $route, bool $avecId): void
    {
        $this->actingAs(User::factory()->create(['role' => 'organisateur']));

        $this->appeler($methode, $route, $avecId)->assertForbidden();
    }

    #[DataProvider('routesAdminEvenements')]
    public function test_un_scanneur_ne_peut_pas_gerer_les_evenements(string $methode, string $route, bool $avecId): void
    {
        $this->actingAs(User::factory()->create(['role' => 'scanneur']));

        $this->appeler($methode, $route, $avecId)->assertForbidden();
    }

    public function test_un_organisateur_ne_peut_ni_fermer_ni_supprimer_un_evenement(): void
    {
        $organisateur = $this->creerOrganisateur();
        $evenement = $this->creerEvenement($organisateur, null, ['statut' => 'encours']);

        $this->actingAs($organisateur->user)
            ->patch(route('evenements.updateStatus', $evenement->id), ['statut' => 'ferme'])
            ->assertForbidden();
        $this->actingAs($organisateur->user)
            ->delete(route('evenements.destroy', $evenement->id))
            ->assertForbidden();

        $this->assertSame('encours', $evenement->fresh()->statut);
    }

    public function test_l_admin_accede_a_la_gestion_des_evenements(): void
    {
        $admin = $this->creerAdmin();

        $this->actingAs($admin)->get(route('evenements.index'))->assertOk();
        $this->actingAs($admin)->get(route('evenements.create'))->assertOk();
    }

    public function test_l_admin_supprime_un_evenement(): void
    {
        $evenement = $this->creerEvenement();

        $this->actingAs($this->creerAdmin())
            ->delete(route('evenements.destroy', $evenement->id))
            ->assertRedirect(route('evenements.index'));

        $this->assertModelMissing($evenement);
    }

    // ---- Authentification requise ------------------------------------------------------

    public static function routesAuthentifiees(): array
    {
        return [
            'tableau de bord admin' => ['dashboard.admin.viewDash'],
            'tableau de bord organisateur' => ['dashboard_orginasateur.show'],
            'utilisateurs' => ['user.index'],
            'organisateurs' => ['admin.organisateurs.index'],
            'profil' => ['profile.edit'],
            'changement de mot de passe' => ['password.change'],
        ];
    }

    #[DataProvider('routesAuthentifiees')]
    public function test_un_invite_est_redirige_vers_la_connexion(string $route): void
    {
        $this->get(route($route))->assertRedirect(route('login'));
    }

    // ---- Périmètre des données ----------------------------------------------------------

    public function test_un_organisateur_ne_voit_pas_les_evenements_d_un_autre(): void
    {
        $moi = $this->creerOrganisateur();
        $autre = $this->creerOrganisateur();
        $monEvenement = $this->creerEvenement($moi);
        $evenementAutre = $this->creerEvenement($autre);

        // Un event_id n'appartenant pas à l'organisateur est ignoré.
        $this->actingAs($moi->user)
            ->get(route('dashboard_orginasateur.show', ['event_id' => $evenementAutre->id]))
            ->assertOk()
            ->assertViewHas('selectedEventId', 0)
            ->assertViewHas('evenementsOrganisateur', fn ($liste) => $liste->pluck('id')->all() === [$monEvenement->id]);
    }

    public function test_un_organisateur_ne_peut_pas_scanner_les_billets_d_un_autre_organisateur(): void
    {
        $moi = $this->creerOrganisateur();
        $autre = $this->creerOrganisateur();
        $this->creerEvenement($moi);
        $evenementAutre = $this->creerEvenement($autre);
        $billet = $this->vendreBillet($evenementAutre, $this->creerTypeBillet(), 1);

        $this->actingAs($moi->user)
            ->postJson(route('scanneur.processScan'), ['code' => $billet->code_billet, 'quantite' => 1, 'event_id' => $evenementAutre->id])
            ->assertForbidden();

        $this->assertSame('valide', $billet->fresh()->statut);
    }

    public function test_un_admin_n_a_aucun_evenement_a_scanner(): void
    {
        $this->actingAs($this->creerAdmin())
            ->postJson(route('scanneur.processScan'), ['code' => 'X', 'quantite' => 1])
            ->assertForbidden();
    }

    // ---- Super administrateur ------------------------------------------------------------

    public function test_seul_le_super_admin_accede_a_la_vue_de_gestion_complete_des_utilisateurs(): void
    {
        $superAdmin = $this->creerAdmin(['email' => 'SuperAdmin@Example.test']);
        $admin = $this->creerAdmin();

        $this->assertTrue($superAdmin->isSuperAdmin(), 'La comparaison de l\'e-mail est insensible à la casse.');
        $this->assertFalse($admin->isSuperAdmin());

        $this->actingAs($superAdmin)->get(route('user.index'))->assertOk()->assertViewIs('users.superAdmin');
        $this->actingAs($admin)->get(route('user.index'))->assertOk()->assertViewIs('users.index');
    }

    public function test_un_organisateur_avec_l_email_du_super_admin_n_est_pas_super_admin(): void
    {
        $user = User::factory()->create(['role' => 'organisateur', 'email' => 'superadmin@example.test']);

        $this->assertFalse($user->isSuperAdmin());
    }

    public function test_sans_email_de_super_admin_configure_personne_n_est_super_admin(): void
    {
        config(['app.super_admin_email' => '']);

        $this->assertFalse($this->creerAdmin(['email' => 'superadmin@example.test'])->isSuperAdmin());
    }

    // ---- Mot de passe temporaire ---------------------------------------------------------

    public function test_un_mot_de_passe_temporaire_bloque_les_appels_json(): void
    {
        $user = User::factory()->create(['role' => 'organisateur', 'must_change_password' => true]);

        $this->actingAs($user)
            ->getJson(route('dashboard_orginasateur.show'))
            ->assertForbidden()
            ->assertJsonPath('message', 'Vous devez changer votre mot de passe temporaire avant de continuer.');
    }

    public function test_un_mot_de_passe_temporaire_laisse_acceder_a_la_deconnexion(): void
    {
        $user = User::factory()->create(['role' => 'organisateur', 'must_change_password' => true]);

        $this->actingAs($user)->post(route('logout'))->assertRedirect(route('login'));

        $this->assertGuest();
    }

    public function test_un_mauvais_mot_de_passe_actuel_n_enleve_pas_l_obligation_de_changement(): void
    {
        $user = User::factory()->create(['role' => 'organisateur', 'must_change_password' => true]);

        $this->actingAs($user)
            ->put(route('password.update'), [
                'current_password' => 'mauvais',
                'password' => 'NouveauMotDePasse1',
                'password_confirmation' => 'NouveauMotDePasse1',
            ])
            ->assertSessionHasErrors();

        $this->assertTrue($user->fresh()->must_change_password);
    }
}
