<?php

namespace App\Services;

use App\Contracts\SuitLesNotifications;
use App\Jobs\EnvoyerNotificationJob;
use App\Models\NotificationEnvoi;
use Closure;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Mail\Mailable;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use RuntimeException;
use Throwable;

/**
 * Point d'entrée unique pour mettre une notification (e-mail) en file d'attente.
 *
 * - l'envoi réel est fait par le worker (EnvoyerNotificationJob) : la requête n'attend jamais le SMTP ;
 * - chaque notification est tracée dans `notification_envois` (statut, tentatives, erreur) ;
 * - deux notifications de même clé ne peuvent pas être en file en même temps (déduplication) ;
 * - une notification définitivement échouée peut être rejouée (rejouer()).
 */
class NotificationQueueService
{
    /**
     * Met un e-mail en file d'attente.
     *
     * @param string $type Identifiant fonctionnel (ex. « evenement.creation »).
     * @param string $cle Clé de déduplication : tant qu'une notification de même clé est en attente
     *                    ou en cours, aucune autre n'est créée.
     * @param Mailable|Closure(): Mailable $mailable Mail à envoyer. Une closure n'est exécutée que si la
     *                    notification n'est pas un doublon : utile quand construire le mail a un effet de bord
     *                    (régénération d'un mot de passe, création d'un jeton…).
     * @param Model|null $sujet Modèle concerné (ex. l'événement), informé via SuitLesNotifications.
     *
     * @return NotificationEnvoi|null null si une notification identique est déjà en file (doublon).
     *                                La notification retournée peut être au statut « echoue » si la mise
     *                                en file elle-même a échoué.
     */
    public function envoyerMail(string $type, string $destinataire, string $cle, Mailable|Closure $mailable, ?Model $sujet = null): ?NotificationEnvoi
    {
        $notification = null;

        try {
            DB::transaction(function () use ($type, $destinataire, $cle, $mailable, $sujet, &$notification): void {
                $notification = NotificationEnvoi::create([
                    'type' => $type,
                    'canal' => 'mail',
                    'destinataire' => $destinataire,
                    'cle' => $cle,
                    'cle_active' => $cle,
                    'statut' => NotificationEnvoi::EN_ATTENTE,
                    'sujet_type' => $sujet?->getMorphClass(),
                    'sujet_id' => $sujet?->getKey(),
                ]);

                $mail = $mailable instanceof Closure ? $mailable() : $mailable;

                // afterCommit : le worker ne voit le job qu'une fois la notification (et les éventuels
                // mots de passe régénérés) réellement enregistrés.
                EnvoyerNotificationJob::dispatch($notification->id, $mail)->afterCommit();

                if ($sujet instanceof SuitLesNotifications) {
                    $sujet->notificationEnFile($notification);
                }
            });
        } catch (UniqueConstraintViolationException) {
            Log::info('Notification ignoree : doublon deja en file', ['type' => $type, 'cle' => $cle]);

            return null;
        } catch (Throwable $e) {
            // La notification a pu être enregistrée avant l'erreur (ex. file indisponible, ou worker « sync ») :
            // on la marque échouée pour qu'elle soit visible et rejouable, sans faire échouer la requête.
            Log::error('Echec de mise en file de la notification', [
                'type' => $type,
                'cle' => $cle,
                'error_message' => $e->getMessage(),
                'exception' => $e,
            ]);

            $notification = $notification?->exists ? $notification->fresh() : null;

            if ($notification && !$notification->estTerminee()) {
                $notification->marquerEchouee(EnvoyerNotificationJob::resumerErreur($e));
                EnvoyerNotificationJob::informerSujet($notification, 'notificationEchouee');
            }

            // Rien n'a été enregistré (transaction annulée) : l'appelant doit être prévenu.
            if ($notification === null) {
                throw $e;
            }

            return $notification;
        }

        return $notification->fresh();
    }

    /**
     * Rejoue une notification définitivement échouée : son job est remis dans la file
     * (même contenu, compteur de tentatives remis à zéro).
     *
     * @throws RuntimeException si la notification ne peut pas être rejouée (message affichable).
     */
    public function rejouer(NotificationEnvoi $notification): void
    {
        $notification->refresh();

        if ($notification->statut !== NotificationEnvoi::ECHOUE) {
            throw new RuntimeException('Seules les notifications échouées peuvent être rejouées.');
        }

        if (!$notification->job_uuid || !$this->failedJobExiste($notification->job_uuid)) {
            throw new RuntimeException('La tâche d\'origine est introuvable dans les tâches échouées : renvoyez la notification depuis son écran métier.');
        }

        $plusRecente = NotificationEnvoi::where('cle', $notification->cle)
            ->where('id', '>', $notification->id)
            ->whereIn('statut', [NotificationEnvoi::EN_ATTENTE, NotificationEnvoi::EN_COURS, NotificationEnvoi::ENVOYE])
            ->exists();

        if ($plusRecente) {
            throw new RuntimeException('Une notification plus récente du même type a déjà été envoyée ou est en cours : celle-ci est obsolète.');
        }

        try {
            $notification->update([
                'statut' => NotificationEnvoi::EN_ATTENTE,
                'cle_active' => $notification->cle,
                'echoue_at' => null,
            ]);
        } catch (UniqueConstraintViolationException) {
            throw new RuntimeException('Une notification identique est déjà en file d\'attente.');
        }

        if ($notification->sujet instanceof SuitLesNotifications) {
            $notification->sujet->notificationEnFile($notification);
        }

        Artisan::call('queue:retry', ['id' => [$notification->job_uuid]]);
    }

    /**
     * Rejoue une tâche échouée quelconque de la table failed_jobs (hors notifications).
     */
    public function rejouerTache(string $uuid): void
    {
        if (!$this->failedJobExiste($uuid)) {
            throw new RuntimeException('Tâche échouée introuvable.');
        }

        $notification = NotificationEnvoi::where('job_uuid', $uuid)->first();

        if ($notification) {
            $this->rejouer($notification);

            return;
        }

        Artisan::call('queue:retry', ['id' => [$uuid]]);
    }

    private function failedJobExiste(string $uuid): bool
    {
        return DB::table(config('queue.failed.table', 'failed_jobs'))->where('uuid', $uuid)->exists();
    }
}
