<?php

namespace App\Services;

use App\Mail\EnvoiMotDePasseMail;
use App\Mail\EvenementCreeMail;
use App\Models\Evenement;
use App\Models\NotificationEnvoi;
use App\Models\User;
use Illuminate\Mail\Mailable;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * Met en file d'attente le mail adapté à la situation de l'organisateur après la création d'un événement :
 * - nouvel organisateur  → identifiants + mot de passe temporaire + événement + scanneur ;
 * - organisateur existant → événement + scanneur (aucun mot de passe organisateur).
 *
 * L'envoi réel est fait par le worker (voir NotificationQueueService / EnvoyerNotificationJob) :
 * la requête HTTP n'attend pas le serveur SMTP.
 */
class EvenementMailService
{
    public function __construct(private NotificationQueueService $notifications)
    {
    }

    /**
     * Clé de déduplication : un seul mail d'accès (création ou renvoi) en file à la fois par événement,
     * sinon un mail en attente contiendrait des mots de passe déjà remplacés par un renvoi.
     */
    public static function cleMailAcces(Evenement $evenement): string
    {
        return 'evenement:' . $evenement->id . ':acces';
    }

    /**
     * @param array $creation Résultat de EvenementCreationService::create()
     * @return bool true si le mail est en file d'attente (ou déjà envoyé), false sinon
     *              (l'erreur est journalisée et tracée sur l'événement).
     */
    public function envoyerApresCreation(array $creation): bool
    {
        /** @var Evenement $evenement */
        $evenement = $creation['evenement'];
        $organisateurUser = $creation['organisateur']?->user;

        if (!$organisateurUser || empty($creation['scanneur_email'])) {
            return false;
        }

        try {
            $notification = $this->notifications->envoyerMail(
                'evenement.creation',
                $organisateurUser->email,
                self::cleMailAcces($evenement),
                $this->construireMail(
                    $evenement,
                    $organisateurUser,
                    $creation['organisateur_existant'] ? null : $creation['organisateur_code'],
                    (string) $creation['scanneur_email'],
                    (string) $creation['scanneur_code'],
                ),
                $evenement,
            );
        } catch (\Throwable $e) {
            Log::error('Echec mise en file du mail apres creation evenement', [
                'evenement_id' => $evenement->id,
                'organisateur_email' => $organisateurUser->email,
                'error_message' => $e->getMessage(),
                'exception' => $e,
            ]);

            $evenement->update([
                'mail_send_attempts' => ((int) $evenement->mail_send_attempts) + 1,
                'mail_sent_at' => null,
                'last_mail_error' => 'MAIL_SEND_FAILED',
            ]);

            return false;
        }

        return $notification !== null && $notification->statut !== NotificationEnvoi::ECHOUE;
    }

    /**
     * Met en file un nouveau mail d'accès pour un événement. Le mot de passe du scanneur est régénéré ;
     * celui de l'organisateur ne l'est QUE s'il n'a jamais changé son mot de passe temporaire,
     * afin de ne pas réinitialiser le compte d'un organisateur actif possédant d'autres événements.
     *
     * Les mots de passe ne sont régénérés que si aucun mail d'accès n'est déjà en file pour cet événement.
     *
     * @throws MailDejaEnFileException si un mail d'accès est déjà en attente d'envoi (doublon).
     * @throws \Throwable
     */
    public function renvoyer(Evenement $evenement): NotificationEnvoi
    {
        $evenement->loadMissing(['organisateur.user', 'scanneur.user']);

        $organisateurUser = $evenement->organisateur?->user;
        $scanneurUser = $evenement->scanneur?->user;

        if (!$organisateurUser || !$scanneurUser) {
            throw new \RuntimeException('Organisateur ou scanneur introuvable.');
        }

        $notification = $this->notifications->envoyerMail(
            'evenement.renvoi',
            $organisateurUser->email,
            self::cleMailAcces($evenement),
            function () use ($evenement, $organisateurUser, $scanneurUser): Mailable {
                $scanneurCode = Str::password(10, symbols: false);
                $scanneurUser->update(['password' => Hash::make($scanneurCode)]);

                $organisateurCode = null;
                if ($organisateurUser->must_change_password) {
                    $organisateurCode = Str::password(12, symbols: false);
                    $organisateurUser->update(['password' => Hash::make($organisateurCode)]);
                }

                return $this->construireMail($evenement, $organisateurUser, $organisateurCode, $scanneurUser->email, $scanneurCode);
            },
            $evenement,
        );

        if ($notification === null) {
            throw new MailDejaEnFileException('Un mail d\'accès est déjà en file d\'attente pour cet événement.');
        }

        return $notification;
    }

    public static function urlAchat(Evenement $evenement): string
    {
        return rtrim((string) config('app.achat_url'), '/') . '/' . $evenement->url_evenement;
    }

    private function construireMail(Evenement $evenement, User $organisateurUser, ?string $motDePasseTemporaire, string $scanneurEmail, string $scanneurCode): Mailable
    {
        $url = self::urlAchat($evenement);

        return $motDePasseTemporaire !== null
            ? new EnvoiMotDePasseMail(
                $organisateurUser->name,
                $organisateurUser->email,
                $motDePasseTemporaire,
                $url,
                $scanneurEmail,
                $scanneurCode,
                $evenement
            )
            : new EvenementCreeMail($organisateurUser->name, $evenement, $url, $scanneurEmail, $scanneurCode);
    }
}
