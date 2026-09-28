<?php

namespace App\Console\Commands;

use App\Models\NotificationEnvoi;
use App\Services\NotificationQueueService;
use Illuminate\Console\Command;
use Illuminate\Support\Str;
use RuntimeException;

class RejouerNotificationsCommand extends Command
{
    protected $signature = 'notifications:rejouer
                            {ids?* : Identifiants des notifications échouées à rejouer}
                            {--tous : Rejouer toutes les notifications définitivement échouées}
                            {--lister : Afficher les notifications échouées sans les rejouer}';

    protected $description = 'Consulte et rejoue les notifications (e-mails) définitivement échouées';

    public function handle(NotificationQueueService $service): int
    {
        $query = NotificationEnvoi::where('statut', NotificationEnvoi::ECHOUE)->orderBy('id');

        if ($this->option('lister')) {
            $this->table(
                ['ID', 'Type', 'Destinataire', 'Tentatives', 'Échouée le', 'Erreur'],
                $query->get()->map(fn (NotificationEnvoi $n) => [
                    $n->id, $n->type, $n->destinataire, $n->tentatives, $n->echoue_at?->format('d/m/Y H:i'), Str::limit((string) $n->derniere_erreur, 60),
                ])
            );

            return self::SUCCESS;
        }

        $ids = $this->argument('ids');

        if (empty($ids) && !$this->option('tous')) {
            $this->error('Précisez des identifiants ou l\'option --tous (ou --lister pour consulter).');

            return self::INVALID;
        }

        $notifications = empty($ids) ? $query->get() : NotificationEnvoi::whereIn('id', $ids)->orderBy('id')->get();

        $rejouees = 0;
        foreach ($notifications as $notification) {
            try {
                $service->rejouer($notification);
                $rejouees++;
                $this->info("#{$notification->id} remise en file d'attente.");
            } catch (RuntimeException $e) {
                $this->warn("#{$notification->id} ignorée : {$e->getMessage()}");
            }
        }

        $this->line("{$rejouees} notification(s) remise(s) en file d'attente.");

        return self::SUCCESS;
    }
}
