<?php

namespace App\Http\Controllers\organisateur;
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
        $typesBillets = [];
        $billetsScannes = 0;

        foreach ($billets as $billet) {
            $totalBilletsVendus+=$billet->quantite;

                // Montants par devise
                if ( $billet->evenementTypeBillet()->devise === "CDF") {
                    $revenusCDF += $billet->evenementTypeBillet()->prix_unitaire;
                }

                if ( $billet->evenementTypeBillet()->devise === "USD") {
                    $revenusUSD += $billet->evenementTypeBillet()->prix_unitaire;
                }

                // Compter billets par type
                
                $typeName = $billet->type_billet->nom_type ?? "Type";
                $typesBillets[$typeName] = ($typesBillets[$typeName] ?? 0) + $billet->quantite;

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
