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
        $totalEnCdf = 0;

        dd($achats);

        foreach ($achats as $billet) {
      
dd($billet->evenement);
    
            if (!isset($montantParEvenement[$billet->evenement->id])) {
                    $montantParEvenement[$billet->evenement->id] = [
                        'nom' => $billet->evenement->nom ?? "Événement ",
                        'CDF' => 0,
                        'USD' => 0,
                        'nb_billets' => 0,
                        'types' => [],
                        'date' => $$evenement->date_debut ?? null,
                    ];
            }

            $montantParEvenement[$billet->evenement->id]['nb_billets']+=$billet->quantite;
        
               if ($billet-> type_billet->pivot->devise === "CDF") {

                    $montantParEvenement[$billet->evenement->id]['CDF'] +=$billet->type_billet->pivot->prix_unitaire;
                    $totalEnCdf += $billet->type_billet->pivot->prix_unitaire;
                }

                if ($billet-> type_billet->pivot->devise === "USD") {
                    $montantParEvenement[$billet->evenement->id]['USD'] +=$billet->type_billet->pivot->prix_unitaire;
                    $totalEnCdf += $billet->type_billet->pivot->prix_unitaire;
                }

                $typeName = $billet-> type_billet->nom_type ?? "Type";
                $montantParEvenement[$billet->evenement->id]['types'][$typeName] =
                ($montantParEvenement[$billet->evenement->id]['types'][$typeName] ?? 0) + + $typeBillet->pivot->quantite;

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
