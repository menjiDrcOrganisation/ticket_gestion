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
    public function index(Request $request)
    {
        $user = Auth::user();

        if ($user->role === 'organisateur') {
            $evenementsOrganisateur = Evenement::where('organisateur_id', $user->organisateur->id)
                ->orderBy('date_debut', 'desc')
                ->get(['id', 'nom', 'date_debut']);
        } elseif ($user->role === 'scanneur') {
            $evenementsOrganisateur = Evenement::where('scanneur_id', $user->scanneur->id)
                ->orderBy('date_debut', 'desc')
                ->get(['id', 'nom', 'date_debut']);
        } else {
            return redirect()->back()->with('error', 'Type d’utilisateur inconnu.');
        }

        $selectedEventId = (int) $request->query('event_id', 0);
        $allowedEventIds = $evenementsOrganisateur->pluck('id')->all();

        if ($selectedEventId > 0 && !in_array($selectedEventId, $allowedEventIds, true)) {
            $selectedEventId = 0;
        }

        $evenement = $selectedEventId > 0
            ? $evenementsOrganisateur->firstWhere('id', $selectedEventId)
            : $evenementsOrganisateur->first();

        $billetsQuery = Billet::with('evenement', 'type_billet')
            ->whereIn('evenement_id', $allowedEventIds);

        if ($selectedEventId > 0) {
            $billetsQuery->where('evenement_id', $selectedEventId);
        }

        $billets = $billetsQuery->get();

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

        $derniersAchats = $billets->sortByDesc('created_at')->take(5)->values();

        return view('organisateurs.dashboard_org', compact(
            'evenement',
            'evenementsOrganisateur',
            'selectedEventId',
            'totalBilletsVendus',
            'revenusCDF',
            'revenusUSD',
            'typesBillets',
            'billetsScannes',
            'derniersAchats'
        ));
    }
}





