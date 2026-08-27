<?php

namespace App\Http\Controllers\Web\Organisateur;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Billet;
use Illuminate\Support\Facades\Auth;
use App\Models\EvenementTypeBillet;
use App\Services\TicketPdfService;

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
            ->orderBy('id', 'desc')
            ->get();
            

        foreach ($billets as $billet) {
            $eventType = $billet->evenementTypeBillet();
            if (!$eventType) {
                continue;
            }
        
             if (!isset($detailleParBillet[$billet->id])) {
                    $detailleParBillet[$billet->id] = [
                        'id' =>$billet->id,
                        'auteur' =>$billet->nom_auteur,
                        'numero_auteur' =>$billet->nom_auteur,
                        'devise' => $eventType->devise,
                        'type' =>$billet->type_billet->nom_type,
                        'quantite' => $billet->quantite,
                        'quantite_fictif' => $billet->quantite_fictif,
                        'prix_unitaire' => $eventType->prix_unitaire,
                        'total' => $eventType->prix_unitaire * $billet->quantite,
                        'date' => $billet->date_achat,
                        'code' => $billet->code_billet,
                        'statut' => $billet->statut,
                        'billetImage'=> $billet->billetImage
                    ];
            }

            $totalAchat+=$billet->quantite ;

                // Montants par devise
                if ($eventType->devise === "CDF") {
                    $totalCDF += $eventType->prix_unitaire * $billet->quantite;
                }

                if ($eventType->devise === "USD") {
                    $totalUSD += $eventType->prix_unitaire * $billet->quantite;
                }
        }

        

        return view('organisateurs.achat', compact('detailleParBillet', 'totalCDF', 'totalUSD', 'totalAchat','totalRestant'));

        } catch (\Throwable $th) {
            return $th;
        }
    }

     public function destroy($id)
    {
        // Récupérer le billet par son id
        $billet = Billet::find($id);

        if (!$billet) {
            return redirect()->back()->with('error', 'Billet introuvable.');
        }

        // Supprimer le billet
        $billet->delete();

        // Rediriger avec message de succès
        return redirect()->back()->with('success', 'Billet supprimé avec succès.');
    }


  public function regenererDepuisDB($id, TicketPdfService $ticketPdfService)
{
    try {
        $billet = Billet::with(['evenement.ressource', 'type_billet'])->findOrFail($id);

        $type_billet = EvenementTypeBillet::where('type_billet_id', $billet->type_billet_id)
                ->where('evenement_id', $billet->evenement_id)
                ->first();
        
        if (!$type_billet) {
            return back()->with('error', 'Type de billet introuvable pour cet evenement.');
        }
       
        $prix = (float) $type_billet->prix_unitaire;
        $total = $prix * (int) $billet->quantite;

        $ticketPdfService->generate(
            $billet,
            $prix,
            (string) $type_billet->devise,
            $total,
            (string) $billet->code_billet
        );

        
        return back()->with('success', 'Billet régénéré avec succès.');

    } catch (\Exception $e) {
        return back()->with('error', $e->getMessage());
    }
}
}





