<?php

namespace App\Http\Controllers\admin;
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
        
               if ($billet->evenementTypeBillet()->devise === "CDF") {

                    $montantParEvenement[$billet->evenement->id]['CDF'] +=$billet->evenementTypeBillet()->prix_unitaire * $billet->quantite;
                    $totalEnCdf += $billet->evenementTypeBillet()->prix_unitaire* $billet->quantite;
                }

                if ($billet-> evenementTypeBillet()->devise === "USD") {
                    $montantParEvenement[$billet->evenement->id]['USD'] +=$billet->evenementTypeBillet()->prix_unitaire* $billet->quantite;
                    $totalEnUsd += $billet->evenementTypeBillet()->prix_unitaire* $billet->quantite;
                }

                $typeName = $billet-> type_billet->nom_type ?? "Type";
                $montantParEvenement[$billet->evenement->id]['types'][$typeName] =($montantParEvenement[$billet->evenement->id]['types'][$typeName] ?? 0) + $billet->quantite;
                $montantParEvenement[$billet->evenement->id]['prix_unitaire'][$typeName] =$billet->evenementTypeBillet()->prix_unitaire;
                $montantParEvenement[$billet->evenement->id]['devise'][$typeName] =$billet->evenementTypeBillet()->devise;
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
