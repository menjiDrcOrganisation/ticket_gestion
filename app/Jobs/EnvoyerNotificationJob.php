<?php

namespace App\Jobs;

use App\Contracts\SuitLesNotifications;
use App\Models\NotificationEnvoi;
use Illuminate\Contracts\Queue\ShouldBeEncrypted;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Throwable;

/**
 * Envoie un e-mail en arrière-plan, depuis le worker, et tient à jour sa trace NotificationEnvoi.
 *
 * Le job est chiffré (ShouldBeEncrypted) : certains mails contiennent des mots de passe
 * temporaires qui ne doivent pas apparaître en clair dans les tables jobs / failed_jobs.
 */
class EnvoyerNotificationJob implements ShouldQueue, ShouldBeEncrypted
{
    use Queueable;

    public int $tries;

    public function __construct(public int $notificationId, public Mailable $mailable)
    {
        $this->tries = max(1, (int) config('notifications.tentatives', 3));
        $this->onQueue(config('notifications.queue', 'notifications'));
    }

    /**
     * @return array<int, int>
     */
    public function backoff(): array
    {
        return config('notifications.backoff', [60, 300, 900]);
    }

    public function handle(): void
    {
        $notification = NotificationEnvoi::find($this->notificationId);

        // Idempotence : un job rejoué ou livré deux fois n'envoie jamais le même mail une seconde fois.
        if (!$notification || $notification->statut === NotificationEnvoi::ENVOYE) {
            return;
        }

        $notification->marquerEnCours($this->job?->uuid());

        try {
            Mail::to($notification->destinataire)->sendNow($this->mailable);
        } catch (Throwable $e) {
            $notification->marquerTentativeEchouee(self::resumerErreur($e));

            throw $e;
        }

        $notification->marquerEnvoyee();
        self::informerSujet($notification, 'notificationEnvoyee');
    }

    /**
     * Appelé par le worker quand toutes les tentatives sont épuisées.
     */
    public function failed(?Throwable $exception): void
    {
        $notification = NotificationEnvoi::find($this->notificationId);

        if (!$notification || $notification->statut === NotificationEnvoi::ENVOYE) {
            return;
        }

        $notification->marquerEchouee($exception ? self::resumerErreur($exception) : 'Échec inconnu');
        self::informerSujet($notification, 'notificationEchouee');

        Log::error('Notification definitivement echouee', [
            'notification_id' => $notification->id,
            'type' => $notification->type,
            'destinataire' => $notification->destinataire,
            'tentatives' => $notification->tentatives,
            'error_message' => $exception?->getMessage(),
        ]);
    }

    public static function informerSujet(NotificationEnvoi $notification, string $methode): void
    {
        $sujet = $notification->sujet;

        if ($sujet instanceof SuitLesNotifications) {
            $sujet->{$methode}($notification);
        }
    }

    public static function resumerErreur(Throwable $e): string
    {
        return Str::limit(class_basename($e) . ' : ' . $e->getMessage(), 1000);
    }
}
