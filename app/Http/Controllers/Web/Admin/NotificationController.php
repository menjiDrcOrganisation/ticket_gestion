<?php

namespace App\Http\Controllers\Web\Admin;

use App\Http\Controllers\Controller;
use App\Models\NotificationEnvoi;
use App\Services\NotificationQueueService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use RuntimeException;

/**
 * Suivi de la file d'attente des notifications : consultation des envois, des échecs
 * définitifs et des autres tâches échouées, et rejeu.
 */
class NotificationController extends Controller
{
    public function index(Request $request)
    {
        $statut = (string) $request->query('statut', '');
        $type = (string) $request->query('type', '');
        $search = trim((string) $request->query('q', ''));

        $notifications = NotificationEnvoi::query()
            ->when(in_array($statut, NotificationEnvoi::STATUTS, true), fn ($q) => $q->where('statut', $statut))
            ->when($type !== '', fn ($q) => $q->where('type', $type))
            ->when($search !== '', fn ($q) => $q->where('destinataire', 'like', '%' . $search . '%'))
            ->latest('id')
            ->paginate(20)
            ->withQueryString();

        $compteurs = NotificationEnvoi::query()
            ->select('statut', DB::raw('count(*) as total'))
            ->groupBy('statut')
            ->pluck('total', 'statut');

        $types = NotificationEnvoi::query()->select('type')->distinct()->orderBy('type')->pluck('type');

        // Autres tâches échouées (génération de billets PDF, etc.) : celles des notifications
        // sont déjà visibles dans le tableau principal.
        $uuidsNotifications = NotificationEnvoi::whereNotNull('job_uuid')->pluck('job_uuid')->all();

        $tachesEchouees = DB::table(config('queue.failed.table', 'failed_jobs'))
            ->orderByDesc('failed_at')
            ->limit(50)
            ->get()
            ->reject(fn ($job) => in_array($job->uuid, $uuidsNotifications, true))
            ->map(function ($job) {
                $payload = json_decode($job->payload, true) ?: [];

                return (object) [
                    'uuid' => $job->uuid,
                    'nom' => class_basename($payload['displayName'] ?? 'Inconnu'),
                    'file' => $job->queue,
                    'erreur' => Str::limit(strtok((string) $job->exception, "\n"), 200),
                    'failed_at' => $job->failed_at,
                ];
            })
            ->values();

        return view('admin.notifications.index', compact(
            'notifications',
            'compteurs',
            'types',
            'tachesEchouees',
            'statut',
            'type',
            'search'
        ));
    }

    public function rejouer(NotificationEnvoi $notification, NotificationQueueService $service)
    {
        try {
            $service->rejouer($notification);
        } catch (RuntimeException $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }

        return redirect()->back()->with('success', 'La notification a été remise en file d\'attente.');
    }

    public function rejouerTache(string $uuid, NotificationQueueService $service)
    {
        try {
            $service->rejouerTache($uuid);
        } catch (RuntimeException $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }

        return redirect()->back()->with('success', 'La tâche a été remise en file d\'attente.');
    }
}
