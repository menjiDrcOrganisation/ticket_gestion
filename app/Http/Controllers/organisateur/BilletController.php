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
            $detailleParBillet=[];
            $totalCDF = 0;
            $totalUSD = 0;

             
            $user = Auth::user();
            $evenementId = $user->organisateur->evenements[0]->id;

            $billets = Billet::with('evenement','type_billet')
            ->where('evenement_id', $evenementId)
            ->paginate(10);
            

        foreach ($billets as $billet) {
        
             if (!isset($detailleParBillet[$billet->id])) {
                    $detailleParBillet[$billet->id] = [
                        'id' =>$billet->id,
                        'auteur' =>$billet->nom_auteur,
                        'numero_auteur' =>$billet->nom_auteur,
                        'devise' => $billet->evenementTypeBillet()->devise ,
                        'type' =>$billet->type_billet->nom_type,
                        'quantite' => $billet->quantite,
                        'quantite_fictif' => $billet->quantite_fictif,
                        'prix_unitaire' => $billet->evenementTypeBillet()->prix_unitaire,
                        'total' => $billet->evenementTypeBillet()->prix_unitaire*$billet->quantite,
                        'date' => $billet->date_achat,
                        'code' => $billet->code_billet,
                        'statut' => $billet->statut
                       
                    ];
            }

            $totalAchat+=$billet->quantite;


                // Montants par devise
                if ( $billet->evenementTypeBillet()->devise === "CDF") {
                    $totalCDF +=  $billet->evenementTypeBillet()->prix_unitaire;
                }

                if ( $billet->evenementTypeBillet()->devise === "USD") {
                    $totalUSD +=  $billet->evenementTypeBillet()->prix_unitaire;
                }
        }

        

        return view('organisateurs.achat', compact('detailleParBillet', 'totalCDF', 'totalUSD', 'totalAchat','totalRestant'));

        } catch (\Throwable $th) {
            return $th;
        }
    }
}
