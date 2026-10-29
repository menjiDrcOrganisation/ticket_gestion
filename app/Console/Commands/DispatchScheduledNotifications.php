<?php

namespace App\Console\Commands;

use App\Models\Evenement;
use App\Models\NotificationCampaign;
use App\Services\NotificationDispatchService;
use Illuminate\Console\Command;

class DispatchScheduledNotifications extends Command
{
    protected $signature = 'notifications:dispatch-scheduled';

    protected $description = 'Envoie les notifications planifiées et les rappels d’événement à 24 heures';

    public function handle(NotificationDispatchService $dispatch): int
    {
        NotificationCampaign::query()
            ->whereNotNull('scheduled_at')
            ->where('scheduled_at', '<=', now())
            ->whereNull('sent_at')
            ->whereNull('cancelled_at')
            ->each(fn (NotificationCampaign $campaign) => $dispatch->send($campaign->load('recipients')));

        $from = now()->addHours(24)->subMinute();
        $until = $from->copy()->addMinute();

        Evenement::query()
            ->where('statut', 'encours')
            ->whereBetween('date_debut', [$from, $until])
            ->each(function (Evenement $event) use ($dispatch): void {
                $dispatch->notifyEventBuyers(
                    $event,
                    'Rappel : votre événement a lieu demain',
                    sprintf("Bonjour,\n\nRappel : l’événement « %s » commence le %s à %s, à %s.", $event->nom, $event->date_debut, $event->adresse, $event->salle),
                    'event_reminder_24h',
                    'event-reminder-24h-' . $event->id
                );
            });

        return self::SUCCESS;
    }
}
