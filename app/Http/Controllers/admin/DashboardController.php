<?php

namespace App\Http\Controllers\admin;

use App\Models\Evenement;

use App\Models\EvenementBilletTypeBillet;

use App\Models\DemandeEvenement;

use App\Models\EvenementTypeBillet;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class DashboardController extends Controller
{

     public function index()
    {
        try {
        
        $evenementsPopulaires = EvenementTypeBillet::select('evenement_id')
            ->selectRaw('SUM(nombre_billet) as total_billets')
            ->groupBy('evenement_id')
            ->orderByDesc('total_billets')
            ->take(5)
            ->with('evenement') 
            ->get();

        $demandeEvenements=DemandeEvenement::all()->count();

        $evenementsEncours= Evenement::where('statut', 'encours')
            ->with(['organisateur.user', 'typeBillets'])
            ->latest()
            ->get()->count();

        $evenementsPasses= Evenement::where('statut', 'ferme')
            ->with(['organisateur.user', 'typeBillets'])
            ->latest()
            ->get()->count();

        
        $eventsPerMonthRaw = Evenement::selectRaw('MONTH(created_at) as mois, COUNT(*) as total')
            ->groupBy('mois')
            ->orderBy('mois')
            ->pluck('total', 'mois');

            $eventsPerMonth = [];

            for ($i = 1; $i <= 12; $i++) {
                $eventsPerMonth[$i] = $eventsPerMonthRaw[$i] ?? 0; // si pas d'événement → 0
            }

            return view('dashboard.viewDash', compact(
                'evenementsEncours',
                'evenementsPasses',
                'demandeEvenements',
                'eventsPerMonth',
                'evenementsPopulaires'
            ));

            } catch (\Throwable $th) {
            //throw $th;
        }
    }
}
