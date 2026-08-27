<?php

namespace App\Http\Controllers\Web\Admin;
use App\Http\Controllers\Controller;
use App\Models\Billet;
use Illuminate\Http\Request;

class PortefeulleController extends Controller
{
    public function showMontantEvent()
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

        return view('portefeulle.showMontantEvent', compact(
            'montantParEvenement',
            'totalEnCdf',
            'totalEnUsd'
        ));

         } catch (\Throwable $th) {
            return "une erreur est survenu".$th;
        }
    }
}





