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
       
        $achats = Billet::with('type_billet','evenements')->paginate(10);

        $montantParEvenement = [];
        $totalEnUsd = 0;
        $totalEnCdf = 0;

        foreach ($achats as $billet) {
            $typeBillet = $billet->type_billet->first();
            $evenement=$billet->evenements->first();

            dd($billet);

            

           $evenement_type_billet= $evenement->typeBillets->first()->pivot;

           

            if (!isset($montantParEvenement[$evenement->id])) {
                    $montantParEvenement[$evenement->id] = [
                        'nom' => $evenement->nom ?? "Événement $eventId",
                        'CDF' => 0,
                        'USD' => 0,
                        'nb_billets' => 0,
                        'types' => [],
                        'date' => $$evenement->date_debut ?? null,
                    ];
            }

            $montantParEvenement[$evenement->id]['nb_billets']+=$typeBillet->pivot->quantite;
        
               if ($evenement_type_billet->devise === "CDF") {
                    $montantParEvenement[$evenement->id]['CDF'] +=$evenement_type_billet->prix_unitaire;
                    $totalEnCdf += $evenement_type_billet->prix_unitaire;
                }

                if ($evenement_type_billet->devise === "USD") {
                    $montantParEvenement[$evenement->id]['USD'] += $evenement_type_billet->prix_unitaire;
                    $totalEnUsd += $evenement_type_billet->prix_unitaire;
                }

                $typeName = $typeBillet->nom_type ?? "Type";
                $montantParEvenement[$evenement->id]['types'][$typeName] =
                ($montantParEvenement[$evenement->id]['types'][$typeName] ?? 0) + + $typeBillet->pivot->quantite;

            
    
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
