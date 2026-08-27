<?php

namespace App\Http\Controllers\Web\Admin;

use App\Http\Controllers\Controller;
use App\Services\DashboardMetricsService;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function __construct(private DashboardMetricsService $dashboardMetricsService)
    {
    }

    public function index(Request $request)
    {
        try {
            $days = (int) $request->integer('days', 30);
            $dashboard = $this->dashboardMetricsService->adminData($days);

            return view('dashboard.viewDash', [
                'dashboard' => $dashboard,
            ]);
        } catch (\Throwable $th) {
            return view('dashboard.viewDash', [
                'dashboard' => [
                    'periodDays' => 30,
                    'kpis' => [
                        'evenementsActifs' => 0,
                        'ventesAujourdhui' => 0,
                        'caAujourdhuiCDF' => 0,
                        'caAujourdhuiUSD' => 0,
                    ],
                    'charts' => [
                        'salesLabels' => [],
                        'salesByDay' => [],
                        'salesByTypeLabels' => [],
                        'salesByTypeValues' => [],
                    ],
                    'queues' => [
                        'demandesEnAttente' => collect(),
                        'retraitsEnAttente' => collect(),
                    ],
                    'lists' => [
                        'topEvenements' => collect(),
                        'transactionsEchouees' => collect(),
                        'activityRecent' => collect(),
                    ],
                    'alerts' => [
                        'eventsWithoutSales' => collect(),
                        'retraitsRetard' => 0,
                        'failedRate24h' => 0.0,
                    ],
                ],
            ])->with('error', 'Impossible de charger les statistiques du dashboard.');
        }
    }
}





