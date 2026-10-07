<?php

namespace App\Services;

use App\Mail\EnvoiMotDePasseMail;
use App\Mail\EvenementCreeMail;
use App\Models\Evenement;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

/**
 * Envoie à l'organisateur le mail adapté à sa situation après la création d'un événement :
 * - nouvel organisateur  → identifiants + mot de passe temporaire + événement + scanneur ;
 * - organisateur existant → événement + scanneur (aucun mot de passe organisateur).
 */
class EvenementMailService
{
    /**
     * @param array $creation Résultat de EvenementCreationService::create()
     * @return bool true si le mail est parti, false sinon (l'erreur est journalisée et tracée sur l'événement).
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
            $this->envoyer(
                $evenement,
                $organisateurUser,
                $creation['organisateur_existant'] ? null : $creation['organisateur_code'],
                (string) $creation['scanneur_email'],
                (string) $creation['scanneur_code'],
            );

            $this->tracer($evenement, null);

            return true;
        } catch (\Throwable $e) {
            Log::error('Echec envoi mail apres creation evenement', [
                'evenement_id' => $evenement->id,
                'organisateur_email' => $organisateurUser->email,
                'error_message' => $e->getMessage(),
                'exception' => $e,
            ]);

            $this->tracer($evenement, 'MAIL_SEND_FAILED');

            return false;
        }
    }

    /**
     * Renvoie le mail d'un événement. Le mot de passe du scanneur est régénéré ; celui de
     * l'organisateur ne l'est QUE s'il n'a jamais changé son mot de passe temporaire,
     * afin de ne pas réinitialiser le compte d'un organisateur actif possédant d'autres événements.
     *
     * @throws \Throwable
     */
    public function renvoyer(Evenement $evenement): void
    {
        $evenement->loadMissing(['organisateur.user', 'scanneur.user']);

        $organisateurUser = $evenement->organisateur?->user;
        $scanneurUser = $evenement->scanneur?->user;

        if (!$organisateurUser || !$scanneurUser) {
            throw new \RuntimeException('Organisateur ou scanneur introuvable.');
        }

        try {
            $scanneurCode = Str::password(10, symbols: false);
            $scanneurUser->update(['password' => Hash::make($scanneurCode)]);

            $organisateurCode = null;
            if ($organisateurUser->must_change_password) {
                $organisateurCode = Str::password(12, symbols: false);
                $organisateurUser->update(['password' => Hash::make($organisateurCode)]);
            }

            $this->envoyer($evenement, $organisateurUser, $organisateurCode, $scanneurUser->email, $scanneurCode);

            $this->tracer($evenement, null);
        } catch (\Throwable $e) {
            $this->tracer($evenement, 'MAIL_RESEND_FAILED');

            throw $e;
        }
    }

    public static function urlAchat(Evenement $evenement): string
    {
        return rtrim((string) config('app.achat_url'), '/') . '/' . $evenement->url_evenement;
    }

    private function envoyer(Evenement $evenement, User $organisateurUser, ?string $motDePasseTemporaire, string $scanneurEmail, string $scanneurCode): void
    {
        $url = self::urlAchat($evenement);

        $mail = $motDePasseTemporaire !== null
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

        Mail::to($organisateurUser->email)->send($mail);
    }

    private function tracer(Evenement $evenement, ?string $erreur): void
    {
        $evenement->update([
            'mail_send_attempts' => ((int) $evenement->mail_send_attempts) + 1,
            'mail_sent_at' => $erreur === null ? now() : null,
            'last_mail_error' => $erreur,
        ]);
    }
}
