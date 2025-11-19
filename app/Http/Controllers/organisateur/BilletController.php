<?php

namespace App\Http\Controllers\organisateur;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Billet;
use Illuminate\Support\Facades\Auth;

class BilletController extends Controller
{
 
        public function index(Request $request)
    {
        try {
            $totalRestant= 0;
             $totalAchat= 0;
             
            $user = Auth::user();
            $evenementId = $user->organisateur->evenements[0]->id;

            $achats = Billet::with(['evenements', 'type_billet'])
            ->whereHas('evenements', function($query) use ($evenementId) {
                $query->where('evenements.id', $evenementId);
            })
            ->paginate(10);

              $totalBilletsVendus=0;
                $totalCDF = 0;
                $totalUSD = 0;



        foreach ($achats as $billet) {
           

            $typeBillet = $billet->type_billet->first();
            $evenement=$billet->evenements->first();
            $evenement_type_billet= $evenement->typeBillets->first()->pivot;
             $totalAchat+= $typeBillet->pivot->quantite;
             $totalRestant+=  $evenement_type_billet->nombre_billet;


             if (!isset($detailleParBillet[$billet->id])) {
                    $detailleParBillet[$billet->id] = [
                        'id' =>$billet->id,
                        'auteur' =>$billet->nom_auteur,
                        'numero_auteur' =>$billet->nom_auteur,
                        'devise' => $evenement_type_billet->devise,
                        'type' =>$typeBillet->nom_type,
                        'quantite' => $typeBillet->pivot->quantite,
                        'quantite_fictif' => $typeBillet->pivot->quantite_fictif,
                        'prix_unitaire' => $evenement_type_billet->prix_unitaire,
                        'total' => $evenement_type_billet->prix_unitaire*$typeBillet->pivot->quantite,
                        'date' => $billet->date_achat,
                        'code' => $billet->code_billet,
                        'statut' => $typeBillet->pivot->statut
                       
                    ];
            }

            $totalBilletsVendus+=$typeBillet->pivot->quantite;

                // Montants par devise
                if ( $evenement_type_billet->devise === "CDF") {
                    $totalCDF +=  $evenement_type_billet->prix_unitaire;
                }

                if ( $evenement_type_billet->devise === "USD") {
                    $totalUSD +=  $evenement_type_billet->prix_unitaire;
                }
        }

        

        
        

        $totalPaye = $totalCDF + $totalUSD;

        return view('organisateurs.achat', compact('detailleParBillet', 'totalCDF', 'totalUSD', 'totalPaye','totalAchat','totalRestant'));

        } catch (\Throwable $th) {
            return $th;
        }
    }
}
