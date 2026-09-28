<?php

namespace Tests\Feature\Notification;

use App\Jobs\EnvoyerNotificationJob;
use App\Mail\EnvoiMotDePasseMail;
use App\Mail\EnvoiMotDePasseOublieMail;
use App\Mail\EvenementCreeMail;
use App\Models\Evenement;
use App\Models\NotificationEnvoi;
use App\Models\TypeBillet;
use App\Models\User;
use App\Services\EvenementMailService;
use App\Services\NotificationQueueService;
use Illuminate\Contracts\Queue\ShouldBeEncrypted;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;
use Tests\Concerns\CreeDesDonneesMetier;
use Tests\TestCase;

/**
 * Issue #14 : les notifications (e-mails) passent par une file d'attente traitée par un worker.
 */
class FileAttenteNotificationTest extends TestCase
{
    use CreeDesDonneesMetier;
    use RefreshDatabase;

    private User $admin;
    private TypeBillet $vip;

    protected function setUp(): void
    {
        parent::setUp();

        Mail::fake();
        Storage::fake('public');

        $this->admin = $this->creerAdmin();
        $this->vip = $this->creerTypeBillet('VIP');
    }

    private function creerEvenementViaWeb(): Evenement
    {
        $this->actingAs($this->admin)
            ->post(route('evenements.web.store'), $this->donneesEvenement($this->vip))
            ->assertRedirect(route('evenements.index'));

        return Evenement::firstOrFail();
    }

    private function jobPousse(): EnvoyerNotificationJob
    {
        $job = null;
        Queue::assertPushed(EnvoyerNotificationJob::class, function (EnvoyerNotificationJob $pousse) use (&$job) {
            $job = $pousse;

            return true;
        });

        return $job;
    }

    private function notificationEchouee(array $attributs = []): NotificationEnvoi
    {
        $uuid = (string) Str::uuid();

        DB::table('failed_jobs')->insert([
            'uuid' => $uuid,
            'connection' => 'database',
            'queue' => 'notifications',
            'payload' => json_encode(['uuid' => $uuid, 'displayName' => EnvoyerNotificationJob::class, 'attempts' => 3, 'data' => []]),
            'exception' => 'RuntimeException: SMTP indisponible',
            'failed_at' => now(),
        ]);

        return NotificationEnvoi::create(array_merge([
            'type' => 'evenement.creation',
            'destinataire' => 'orga@example.com',
            'cle' => 'test:cle',
            'statut' => NotificationEnvoi::ECHOUE,
            'tentatives' => 3,
            'derniere_erreur' => 'RuntimeException : SMTP indisponible',
            'job_uuid' => $uuid,
            'echoue_at' => now(),
        ], $attributs));
    }

    // ---- Mise en file : la requête n'est pas bloquée par l'envoi ------------------------------

    public function test_la_creation_d_evenement_met_le_mail_en_file_sans_l_envoyer_pendant_la_requete(): void
    {
        Queue::fake();

        $this->actingAs($this->admin)
            ->post(route('evenements.web.store'), $this->donneesEvenement($this->vip))
            ->assertRedirect(route('evenements.index'))
            ->assertSessionHas('success', fn (string $m) => str_contains($m, 'file d’attente'));

        Mail::assertNothingSent();
        Queue::assertPushedOn('notifications', EnvoyerNotificationJob::class);
        $this->assertInstanceOf(EnvoiMotDePasseMail::class, $this->jobPousse()->mailable);

        $evenement = Evenement::firstOrFail();
        $notification = NotificationEnvoi::sole();
        $this->assertSame(NotificationEnvoi::EN_ATTENTE, $notification->statut);
        $this->assertSame('evenement.creation', $notification->type);
        $this->assertSame('orga@example.com', $notification->destinataire);
        $this->assertTrue($notification->sujet->is($evenement));
        $this->assertNull($evenement->mail_sent_at);
        $this->assertNull($evenement->last_mail_error);
    }

    public function test_une_panne_smtp_ne_bloque_pas_la_requete(): void
    {
        Queue::fake();
        Mail::shouldReceive('to')->andThrow(new RuntimeException('SMTP indisponible'));

        $this->actingAs($this->admin)
            ->post(route('evenements.web.store'), $this->donneesEvenement($this->vip))
            ->assertRedirect(route('evenements.index'))
            ->assertSessionHas('success', fn (string $m) => !str_contains($m, 'n\'a pas pu'));

        Queue::assertPushed(EnvoyerNotificationJob::class, 1);
    }

    public function test_l_api_indique_que_le_mail_est_en_file_d_attente(): void
    {
        Queue::fake();

        $this->actingAs($this->admin)
            ->postJson('/api/v1/evenements', $this->donneesEvenement($this->vip))
            ->assertCreated()
            ->assertJsonPath('mail_en_file_attente', true);

        Mail::assertNothingSent();
        Queue::assertPushed(EnvoyerNotificationJob::class, 1);
    }

    public function test_le_mot_de_passe_oublie_est_mis_en_file(): void
    {
        Queue::fake();
        $user = User::factory()->create();

        $this->post('/forgot-password', ['email' => $user->email])->assertSessionHas('status');

        Mail::assertNothingSent();
        $this->assertInstanceOf(EnvoiMotDePasseOublieMail::class, $this->jobPousse()->mailable);
        $this->assertSame('auth.reinitialisation_mot_de_passe', NotificationEnvoi::sole()->type);
    }

    // ---- Le job : traitement asynchrone par le worker -----------------------------------------

    public function test_le_job_est_chiffre_et_configure_pour_les_tentatives(): void
    {
        $job = new EnvoyerNotificationJob(1, new EvenementCreeMail('x', new Evenement(), 'u', 's', 'p'));

        $this->assertInstanceOf(ShouldQueue::class, $job);
        $this->assertInstanceOf(ShouldBeEncrypted::class, $job);
        $this->assertSame(3, $job->tries);
        $this->assertSame([60, 300, 900], $job->backoff());
        $this->assertSame('notifications', $job->queue);
    }

    public function test_le_worker_envoie_le_mail_et_trace_le_succes(): void
    {
        Queue::fake();
        $evenement = $this->creerEvenementViaWeb();
        $job = $this->jobPousse();

        $job->handle();

        Mail::assertSent(EnvoiMotDePasseMail::class, fn ($mail) => $mail->hasTo('orga@example.com'));

        $notification = NotificationEnvoi::sole();
        $this->assertSame(NotificationEnvoi::ENVOYE, $notification->statut);
        $this->assertSame(1, $notification->tentatives);
        $this->assertNotNull($notification->envoye_at);
        $this->assertNull($notification->cle_active);

        $evenement->refresh();
        $this->assertNotNull($evenement->mail_sent_at);
        $this->assertSame(1, (int) $evenement->mail_send_attempts);
    }

    public function test_un_job_deja_envoye_n_envoie_pas_de_doublon(): void
    {
        Queue::fake();
        $this->creerEvenementViaWeb();
        $job = $this->jobPousse();

        $job->handle();
        $job->handle(); // job livré deux fois / rejoué par erreur

        Mail::assertSent(EnvoiMotDePasseMail::class, 1);
        $this->assertSame(1, NotificationEnvoi::sole()->tentatives);
    }

    public function test_une_tentative_echouee_est_identifiee_puis_relancee_par_le_worker(): void
    {
        Queue::fake();
        $this->creerEvenementViaWeb();
        $job = $this->jobPousse();

        Mail::shouldReceive('to')->andThrow(new RuntimeException('SMTP indisponible'));

        try {
            $job->handle();
            $this->fail('L\'exception doit remonter au worker pour déclencher une nouvelle tentative.');
        } catch (RuntimeException $e) {
            $this->assertSame('SMTP indisponible', $e->getMessage());
        }

        $notification = NotificationEnvoi::sole();
        $this->assertSame(NotificationEnvoi::EN_ATTENTE, $notification->statut);
        $this->assertSame(1, $notification->tentatives);
        $this->assertStringContainsString('SMTP indisponible', $notification->derniere_erreur);
        $this->assertNotNull($notification->cle_active);
    }

    public function test_apres_la_derniere_tentative_la_notification_est_definitivement_echouee(): void
    {
        Queue::fake();
        $evenement = $this->creerEvenementViaWeb();
        $job = $this->jobPousse();

        $job->failed(new RuntimeException('SMTP indisponible'));

        $notification = NotificationEnvoi::sole();
        $this->assertSame(NotificationEnvoi::ECHOUE, $notification->statut);
        $this->assertNotNull($notification->echoue_at);
        $this->assertNull($notification->cle_active);

        $evenement->refresh();
        $this->assertSame('MAIL_SEND_FAILED', $evenement->last_mail_error);
        $this->assertNull($evenement->mail_sent_at);
    }

    // ---- Déduplication ------------------------------------------------------------------------

    public function test_un_renvoi_est_refuse_tant_qu_un_mail_d_acces_est_en_file(): void
    {
        Queue::fake();
        $evenement = $this->creerEvenementViaWeb();
        $scanneurUser = $evenement->scanneur->user;
        $hashAvant = $scanneurUser->password;

        $this->actingAs($this->admin)
            ->post(route('evenements.resendMail', $evenement->id))
            ->assertSessionHas('error', fn (string $m) => str_contains($m, 'déjà en cours d\'envoi'));

        Queue::assertPushed(EnvoyerNotificationJob::class, 1);
        $this->assertSame(1, NotificationEnvoi::count());
        // Les mots de passe ne sont pas régénérés : le mail en attente reste valide.
        $this->assertSame($hashAvant, $scanneurUser->fresh()->password);
    }

    public function test_un_renvoi_est_accepte_une_fois_le_mail_precedent_envoye(): void
    {
        Queue::fake();
        $evenement = $this->creerEvenementViaWeb();
        $this->jobPousse()->handle();

        $this->actingAs($this->admin)
            ->post(route('evenements.resendMail', $evenement->id))
            ->assertSessionHas('success', fn (string $m) => str_contains($m, 'file d\'attente'));

        Queue::assertPushed(EnvoyerNotificationJob::class, 2);
        $this->assertSame('evenement.renvoi', NotificationEnvoi::latest('id')->first()->type);
    }

    public function test_deux_demandes_de_mot_de_passe_oublie_ne_creent_qu_une_notification(): void
    {
        Queue::fake();
        $user = User::factory()->create();

        $this->post('/forgot-password', ['email' => $user->email])->assertSessionHas('status');
        $this->post('/forgot-password', ['email' => $user->email])->assertSessionHas('status');

        Queue::assertPushed(EnvoyerNotificationJob::class, 1);
        $this->assertSame(1, NotificationEnvoi::count());
        $this->assertSame(1, DB::table('password_reset_tokens')->count());
    }

    public function test_la_meme_cle_est_refusee_puis_acceptee_une_fois_terminee(): void
    {
        Queue::fake();
        $service = app(NotificationQueueService::class);
        $mail = fn () => new EvenementCreeMail('x', $this->creerEvenement(), 'u', 's', 'p');

        $premiere = $service->envoyerMail('test', 'a@example.com', 'cle:unique', $mail);
        $this->assertNotNull($premiere);
        $this->assertNull($service->envoyerMail('test', 'a@example.com', 'cle:unique', $mail));

        $premiere->marquerEnvoyee();

        $this->assertNotNull($service->envoyerMail('test', 'a@example.com', 'cle:unique', $mail));
        Queue::assertPushed(EnvoyerNotificationJob::class, 2);
    }

    // ---- Rejeu des tâches échouées ------------------------------------------------------------

    public function test_une_notification_echouee_peut_etre_rejouee(): void
    {
        Queue::fake();
        $notification = $this->notificationEchouee();

        app(NotificationQueueService::class)->rejouer($notification);

        $notification->refresh();
        $this->assertSame(NotificationEnvoi::EN_ATTENTE, $notification->statut);
        $this->assertSame('test:cle', $notification->cle_active);
        $this->assertNull($notification->echoue_at);
        $this->assertSame(0, DB::table('failed_jobs')->count());
        $this->assertCount(1, Queue::pushedRaw());
    }

    public function test_une_notification_non_echouee_ne_peut_pas_etre_rejouee(): void
    {
        $notification = $this->notificationEchouee(['statut' => NotificationEnvoi::ENVOYE]);

        $this->expectException(RuntimeException::class);
        app(NotificationQueueService::class)->rejouer($notification);
    }

    public function test_une_notification_obsolete_n_est_pas_rejouee(): void
    {
        $notification = $this->notificationEchouee();
        NotificationEnvoi::create([
            'type' => 'evenement.renvoi',
            'destinataire' => 'orga@example.com',
            'cle' => 'test:cle',
            'statut' => NotificationEnvoi::ENVOYE,
        ]);

        try {
            app(NotificationQueueService::class)->rejouer($notification);
            $this->fail('Le rejeu d\'une notification obsolète doit être refusé.');
        } catch (RuntimeException $e) {
            $this->assertStringContainsString('obsolète', $e->getMessage());
        }

        $this->assertSame(NotificationEnvoi::ECHOUE, $notification->fresh()->statut);
        $this->assertSame(1, DB::table('failed_jobs')->count());
    }

    public function test_la_commande_artisan_liste_et_rejoue_les_notifications_echouees(): void
    {
        Queue::fake();
        $notification = $this->notificationEchouee();

        $this->artisan('notifications:rejouer', ['--lister' => true])
            ->expectsOutputToContain('orga@example.com')
            ->assertSuccessful();

        $this->artisan('notifications:rejouer', ['--tous' => true])
            ->expectsOutputToContain('1 notification(s) remise(s)')
            ->assertSuccessful();

        $this->assertSame(NotificationEnvoi::EN_ATTENTE, $notification->fresh()->statut);
    }

    // ---- Consultation par l'administrateur ----------------------------------------------------

    public function test_l_admin_consulte_les_notifications_et_les_taches_echouees(): void
    {
        $this->notificationEchouee(['destinataire' => 'echec@example.com']);
        DB::table('failed_jobs')->insert([
            'uuid' => (string) Str::uuid(),
            'connection' => 'database',
            'queue' => 'default',
            'payload' => json_encode(['displayName' => 'App\\Jobs\\RegenerateTicketPdfJob', 'data' => []]),
            'exception' => "RuntimeException: PDF KO\n#0 trace",
            'failed_at' => now(),
        ]);

        $this->actingAs($this->admin)
            ->get(route('admin.notifications.index', ['statut' => 'echoue']))
            ->assertOk()
            ->assertSee('echec@example.com')
            ->assertSee('SMTP indisponible')
            ->assertSee('RegenerateTicketPdfJob')
            ->assertSee('RuntimeException: PDF KO');
    }

    public function test_l_admin_rejoue_une_notification_depuis_l_ecran(): void
    {
        Queue::fake();
        $notification = $this->notificationEchouee();

        $this->actingAs($this->admin)
            ->post(route('admin.notifications.rejouer', $notification))
            ->assertSessionHas('success');

        $this->assertSame(NotificationEnvoi::EN_ATTENTE, $notification->fresh()->statut);
    }

    public function test_un_non_admin_ne_peut_pas_consulter_les_notifications(): void
    {
        $organisateur = $this->creerOrganisateur()->user;

        $this->actingAs($organisateur)
            ->get(route('admin.notifications.index'))
            ->assertForbidden();
    }
}
