<?php

namespace App\Services;

use App\Models\AuditLog;
use App\Models\DemandeEvenement;
use App\Models\Evenement;
use App\Models\Retrait;
use App\Models\Transaction;
use Carbon\Carbon;

class DashboardMetricsService
{
    /**
     * @var array<int, string>
     */
    private array $successStatuts = [
        'paye',
        'completee',
        'paye_sans_billet',
    ];

    /**
     * @var array<int, string>
     */
    private array $failedStatuts = [
        'echoue',
        'echouee',
        'annulee',
    ];

    /**
     * Build all admin dashboard metrics and lists for the selected period.
     */
    public function adminData(?int $periodDays = 30): array
    {
        $days = in_array($periodDays, [7, 30, 90], true) ? $periodDays : 30;

        $now = now();
        $today = now()->toDateString();
        $periodStart = now()->copy()->subDays($days - 1)->startOfDay();

        $evenementsActifs = Evenement::query()
            ->where('statut', 'encours')
            ->count();

        $ventesAujourdhui = (int) Transaction::query()
            ->whereIn('statut', $this->successStatuts)
            ->whereDate('created_at', $today)
            ->sum('nombre_billet');

        $caAujourdhuiCDF = (float) Transaction::query()
            ->whereIn('statut', $this->successStatuts)
            ->whereDate('created_at', $today)
            ->where('devise', 'CDF')
            ->sum('montant');

        $caAujourdhuiUSD = (float) Transaction::query()
            ->whereIn('statut', $this->successStatuts)
            ->whereDate('created_at', $today)
            ->where('devise', 'USD')
            ->sum('montant');

        $salesByDayRaw = Transaction::query()
            ->selectRaw('DATE(created_at) as jour, COALESCE(SUM(nombre_billet), 0) as total')
            ->whereIn('statut', $this->successStatuts)
            ->whereBetween('created_at', [$periodStart, $now])
            ->groupBy('jour')
            ->pluck('total', 'jour');

        $salesByDay = [];
        $salesLabels = [];

        for ($i = 0; $i < $days; $i++) {
            $date = $periodStart->copy()->addDays($i);
            $dateKey = $date->toDateString();

            $salesLabels[] = $date->format('d/m');
            $salesByDay[] = (int) ($salesByDayRaw[$dateKey] ?? 0);
        }

        $salesByType = Transaction::query()
            ->join('type_billets', 'type_billets.id', '=', 'transactions.type_billet_id')
            ->whereIn('transactions.statut', $this->successStatuts)
            ->whereBetween('transactions.created_at', [$periodStart, $now])
            ->selectRaw('type_billets.nom_type as label, COALESCE(SUM(transactions.nombre_billet), 0) as total')
            ->groupBy('type_billets.nom_type')
            ->orderByDesc('total')
            ->limit(6)
            ->get();

        $demandesEnAttente = DemandeEvenement::query()
            ->whereIn('statut', ['en_attente', 'attente', 'pending'])
            ->latest()
            ->limit(8)
            ->get();

        $retraitsEnAttente = Retrait::query()
            ->with('organisateur.user')
            ->whereIn('statut', ['en attente', 'en_attente', 'attente', 'pending'])
            ->latest()
            ->limit(8)
            ->get();

        $topEvenements = Transaction::query()
            ->with('evenement')
            ->whereIn('statut', $this->successStatuts)
            ->whereNotNull('evenement_id')
            ->whereBetween('created_at', [$periodStart, $now])
            ->selectRaw('evenement_id, COALESCE(SUM(nombre_billet), 0) as billets_vendus, COALESCE(SUM(montant), 0) as montant_total')
            ->groupBy('evenement_id')
            ->orderByDesc('billets_vendus')
            ->limit(5)
            ->get();

        $transactionsEchouees = Transaction::query()
            ->with('evenement')
            ->whereIn('statut', $this->failedStatuts)
            ->latest()
            ->limit(8)
            ->get();

        $activityRecent = AuditLog::query()
            ->with('user')
            ->latest()
            ->limit(8)
            ->get();

        $eventsWithoutSales = Evenement::query()
            ->where('statut', 'encours')
            ->whereDoesntHave('billets.transactions', function ($query): void {
                $query->whereIn('statut', $this->successStatuts)
                    ->where('created_at', '>=', now()->subHours(24));
            })
            ->limit(5)
            ->get(['id', 'nom', 'date_debut']);

        $retraitsRetard = Retrait::query()
            ->whereIn('statut', ['en attente', 'en_attente', 'attente', 'pending'])
            ->where('created_at', '<=', now()->subHours(72))
            ->count();

        $totalTransactions24h = Transaction::query()
            ->where('created_at', '>=', now()->subDay())
            ->count();

        $failedTransactions24h = Transaction::query()
            ->whereIn('statut', $this->failedStatuts)
            ->where('created_at', '>=', now()->subDay())
            ->count();

        $failedRate24h = $totalTransactions24h > 0
            ? round(($failedTransactions24h / $totalTransactions24h) * 100, 1)
            : 0.0;

        return [
            'periodDays' => $days,
            'kpis' => [
                'evenementsActifs' => $evenementsActifs,
                'ventesAujourdhui' => $ventesAujourdhui,
                'caAujourdhuiCDF' => $caAujourdhuiCDF,
                'caAujourdhuiUSD' => $caAujourdhuiUSD,
            ],
            'charts' => [
                'salesLabels' => $salesLabels,
                'salesByDay' => $salesByDay,
                'salesByTypeLabels' => $salesByType->pluck('label')->values()->all(),
                'salesByTypeValues' => $salesByType->pluck('total')->map(fn ($v) => (int) $v)->values()->all(),
            ],
            'queues' => [
                'demandesEnAttente' => $demandesEnAttente,
                'retraitsEnAttente' => $retraitsEnAttente,
            ],
            'lists' => [
                'topEvenements' => $topEvenements,
                'transactionsEchouees' => $transactionsEchouees,
                'activityRecent' => $activityRecent,
            ],
            'alerts' => [
                'eventsWithoutSales' => $eventsWithoutSales,
                'retraitsRetard' => $retraitsRetard,
                'failedRate24h' => $failedRate24h,
            ],
        ];
    }
}
