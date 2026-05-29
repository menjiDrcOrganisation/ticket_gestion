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
        
        $evenementsPopulaires = EvenementBilletTypeBillet::select('evenement_id')
            ->selectRaw('SUM(quantite) as total_billets')
            ->groupBy('evenement_id')
            ->orderByDesc('total_billets')
            ->take(5)
            ->with('evenement') 
            ->get();

        $demandeEvenements=DemandeEvenement::all()->count();

        $evenementsEncours = Evenement::encours()->count();
        $evenementsPasses = Evenement::ferme()->count();

        
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
