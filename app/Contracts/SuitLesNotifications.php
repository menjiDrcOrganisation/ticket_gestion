<?php

namespace App\Contracts;

use App\Models\NotificationEnvoi;

/**
 * Modèle métier qui souhaite être informé du sort des notifications le concernant
 * (ex. : l'événement met à jour ses colonnes mail_sent_at / last_mail_error).
 */
interface SuitLesNotifications
{
    public function notificationEnFile(NotificationEnvoi $notification): void;

    public function notificationEnvoyee(NotificationEnvoi $notification): void;

    public function notificationEchouee(NotificationEnvoi $notification): void;
}
