<?php

namespace App\Http\Controllers\organisateur;

use App\Models\Evenement;
use App\Models\EvenementBilletTypeBillet;
use App\Models\EvenementTypeBillet;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $evenement = Evenement::where('organisateur_id', $user->organisateur->id)->first();
        if (!$evenement) {
            return redirect()->back()->with('error', 'Aucun événement trouvé pour cet organisateur.');
        }

        $achats = EvenementBilletTypeBillet::where('evenement_id', $evenement->id)->get();
        $typesBillets = EvenementTypeBillet::where('evenement_id', $evenement->id)
            ->with('type_billet')
            ->get();

       
        $totalBilletsVendus = $achats->sum('quantite');
        $revenusCDF = $achats->where('devise', 'CDF')->sum(fn($a) => $a->prix_unitaire * $a->quantite);
        $revenusUSD = $achats->where('devise', 'USD')->sum(fn($a) => $a->prix_unitaire * $a->quantite);

        $totalBilletsDisponibles = $typesBillets->sum('nombre_billet');
        $tauxRemplissage = $totalBilletsDisponibles > 0 
            ? round(($totalBilletsVendus / $totalBilletsDisponibles) * 100, 1)
            : 0;

      
        $derniersAchats = $achats->sortByDesc('date_achat')->take(5);

     
        return view('organisateurs.resume', compact(
            'evenement',
            'totalBilletsVendus',
            'revenusCDF',
            'revenusUSD',
            'tauxRemplissage',
            'typesBillets',
            'derniersAchats'
        ));
    }
}
