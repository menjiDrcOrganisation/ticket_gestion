<?php

namespace App\Http\Controllers\Web\Organisateur;
use App\Models\EvenementBilletTypeBillet;

use App\Models\Evenement;
use App\Models\Billet;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        // Récupérer l'événement de l'organisateur
        if ($user->role === 'organisateur') {

    // Cherche l'événement de cet organisateur
        $evenement = Evenement::where('organisateur_id', $user->organisateur->id)->first();

        } elseif ($user->role === 'scanneur') {

            // Cherche l'événement lié au scanneur
            $evenement = Evenement::where('scanneur_id', $user->scanneur->id)->first();
        } else {

            // Rôle non géré
            return redirect()->back()->with('error', 'Type d’utilisateur inconnu.');
        }

        // Récupérer tous les billets liés à cet événement
        $billets = Billet::with('evenement','type_billet')
            ->where('evenement_id', $evenement->id)
            ->get();

        $totalBilletsVendus=0;
        $revenusCDF = 0;
        $revenusUSD = 0;
        $typesBillets = [[],[],[]];
        $billetsScannes = 0;

        foreach ($billets as $billet) {
            $eventType = $billet->evenementTypeBillet();
            if (!$eventType) {
                continue;
            }

            $totalBilletsVendus+=$billet->quantite;

                // Montants par devise
                if ($eventType->devise === "CDF") {
                    $revenusCDF += $eventType->prix_unitaire * $billet->quantite;
                }

                if ($eventType->devise === "USD") {
                    $revenusUSD += $eventType->prix_unitaire * $billet->quantite;
                }

                // Compter billets par type
                
                $typeName = $billet->type_billet->nom_type ?? "Type";
                $typesBillets[0][$typeName] = ($typesBillets[0][$typeName] ?? 0) + $billet->quantite;
                $typesBillets[1][$typeName] = $eventType->prix_unitaire;
                $typesBillets[2][$typeName] = $eventType->devise;

                // Compter billets scannés
                
                $billetsScannes+=$billet->quantite-$billet->quantite_fictif;
                
        }

        // Derniers achats (5 derniers billets)

        $derniersAchats = $billets->sortByDesc('created_at')->take(5);

        return view('organisateurs.dashboard_org', compact(
            'evenement',
            'totalBilletsVendus',
            'revenusCDF',
            'revenusUSD',
            'typesBillets',
            'billetsScannes',
            'derniersAchats'
        ));
    }
}





