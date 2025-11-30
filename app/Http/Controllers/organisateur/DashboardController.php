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
        $achats = Billet::with('type_billet','evenements')
                        ->whereHas('type_billet.evenements', function($q) use ($evenement) {
                            $q->where('evenement_id', $evenement->id);
                        })
                        ->get();

        $totalBilletsVendus=0;
        $revenusCDF = 0;
        $revenusUSD = 0;
        $typesBillets = [];
        $billetsScannes = 0;

        foreach ($achats as $billet) {

            $typeBillet = $billet->type_billet->first();
            $evenement=$billet->evenements->first();
            $evenement_type_billet= $evenement->typeBillets->first()->pivot;

            if (!$typeBillet) continue;
            $totalBilletsVendus+=$typeBillet->pivot->quantite;

                // Montants par devise
                if ( $evenement_type_billet->devise === "CDF") {
                    $revenusCDF +=  $evenement_type_billet->prix_unitaire;
                }

                if ( $evenement_type_billet->devise === "USD") {
                    $revenusUSD +=  $evenement_type_billet->prix_unitaire;
                }

                // Compter billets par type
                $typeName = $typeBillet->nom_type ?? "Type";
                $typesBillets[$typeName] = ($typesBillets[$typeName] ?? 0) + $typeBillet->pivot->quantite;

                // Compter billets scannés
                if ($billet->scanne ?? false) {
                    $billetsScannes++;
                }

          
        }

        // Derniers achats (5 derniers billets)
        $derniersAchats = $achats->sortByDesc('created_at')->take(5);

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
