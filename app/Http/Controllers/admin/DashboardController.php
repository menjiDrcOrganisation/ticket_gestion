<?php

namespace App\Http\Controllers\admin;

use App\Models\Evenement;
use App\Models\EvenementBilletTypeBillet;
use App\Models\EvenementTypeBillet;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class DashboardController extends Controller
{

     public function index()
   
    {
    $evenementsActifs = Evenement::where('statut', 'actif')->count();
        $billetsVendus = EvenementBilletTypeBillet::sum('quantite');
        $revenusCDF = EvenementTypeBillet::where('devise', 'CDF')->get()->sum(fn($a) => $a->prix_unitaire * $a->quantite);
        $revenusUSD = EvenementTypeBillet::where('devise', 'USD')->get()->sum(fn($a) => $a->prix_unitaire * $a->quantite);
        $tauxRemplissage = $evenementsActifs > 0 
            ? round(($billetsVendus / ($evenementsActifs * 100)) * 100, 2)
            : 0;
        $derniersAchats = EvenementBilletTypeBillet::with(['evenement', 'billet'])
            ->latest()
            ->take(5)
            ->get();
        $evenementsPopulaires = EvenementBilletTypeBillet::with('evenement')
            ->selectRaw('evenement_id, SUM(quantite) as total')
            ->groupBy('evenement_id')
            ->orderByDesc('total')
            ->take(3)
            ->get();

        return view('billet', compact(
            'evenementsActifs',
            'billetsVendus',
            'revenusCDF',
            'revenusUSD',
            'tauxRemplissage',
            'derniersAchats',
            'evenementsPopulaires'
        ));
    }
}
