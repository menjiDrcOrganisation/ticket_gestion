<?php

namespace App\Http\Controllers\Web\Admin;
use App\Http\Controllers\Controller;
use App\Models\Billet;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;

class PortefeulleController extends Controller
{
    public function showMontantEvent(Request $request)
    {
        // Récupération des billets avec le type et les événements

        try {
            //code...
       
        $achats = Billet::with('type_billet','evenement')->get();

        $montantParEvenement = [];
        $totalEnUsd = 0;
        $typesBillets = [[],[],[]];
        $totalEnCdf = 0;

    

        foreach ($achats as $billet) {
            $eventType = $billet->evenementTypeBillet();
            if (!$eventType) {
                continue;
            }
      
           if($billet->evenement) {
    
            if (!isset($montantParEvenement[$billet->evenement->id])) {
                    $montantParEvenement[$billet->evenement->id] = [
                        'nom' => $billet->evenement->nom ?? "Événement ",
                        'CDF' => 0,
                        'USD' => 0,
                        'nb_billets' => 0,
                        'types' => [],
                        'prix_unitaire' => [],
                        'devise' => [],
                        'date' => $billet->evenement->date_debut ?? null,
                    ];
            }

            $montantParEvenement[$billet->evenement->id]['nb_billets']+=$billet->quantite;
        
               if ($eventType->devise === "CDF") {

                    $montantParEvenement[$billet->evenement->id]['CDF'] += $eventType->prix_unitaire * $billet->quantite;
                    $totalEnCdf += $eventType->prix_unitaire * $billet->quantite;
                }

                if ($eventType->devise === "USD") {
                    $montantParEvenement[$billet->evenement->id]['USD'] += $eventType->prix_unitaire * $billet->quantite;
                    $totalEnUsd += $eventType->prix_unitaire * $billet->quantite;
                }

                $typeName = $billet-> type_billet->nom_type ?? "Type";
                $montantParEvenement[$billet->evenement->id]['types'][$typeName] =($montantParEvenement[$billet->evenement->id]['types'][$typeName] ?? 0) + $billet->quantite;
                $montantParEvenement[$billet->evenement->id]['prix_unitaire'][$typeName] = $eventType->prix_unitaire;
                $montantParEvenement[$billet->evenement->id]['devise'][$typeName] = $eventType->devise;
            }else{
                continue;
            }
        }

        $search = trim((string) $request->query('q', ''));
        $devise = trim((string) $request->query('devise', ''));

        $eventsCollection = collect($montantParEvenement)
            ->values()
            ->when($search !== '', function ($collection) use ($search) {
                return $collection->filter(function (array $event) use ($search): bool {
                    return str_contains(mb_strtolower((string) ($event['nom'] ?? '')), mb_strtolower($search));
                });
            })
            ->when($devise === 'CDF', function ($collection) {
                return $collection->filter(fn (array $event): bool => ((float) ($event['CDF'] ?? 0)) > 0);
            })
            ->when($devise === 'USD', function ($collection) {
                return $collection->filter(fn (array $event): bool => ((float) ($event['USD'] ?? 0)) > 0);
            })
            ->sortByDesc('date')
            ->values();

        $perPage = 10;
        $currentPage = LengthAwarePaginator::resolveCurrentPage();
        $pagedEvents = new LengthAwarePaginator(
            $eventsCollection->forPage($currentPage, $perPage)->values(),
            $eventsCollection->count(),
            $perPage,
            $currentPage,
            [
                'path' => $request->url(),
                'query' => $request->query(),
            ]
        );

        return view('portefeulle.showMontantEvent', compact(
            'montantParEvenement',
            'totalEnCdf',
            'totalEnUsd',
            'pagedEvents',
            'search',
            'devise'
        ));

         } catch (\Throwable $th) {
            return "une erreur est survenu".$th;
        }
    }
}





