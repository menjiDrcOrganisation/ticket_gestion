<?php

namespace App\Http\Controllers\organisateur;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Billet;
use Illuminate\Support\Facades\Auth;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\Image\SvgImageBackEnd; 

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
                        'statut' => $billet->statut,
                        'billetImage'=> $billet->billetImage
                    ];
            }

            $totalAchat+=$billet->quantite ;

                // Montants par devise
                if ( $billet->evenementTypeBillet()->devise === "CDF") {
                    $totalCDF +=  $billet->evenementTypeBillet()->prix_unitaire * $billet->quantite;
                }

                if ( $billet->evenementTypeBillet()->devise === "USD") {
                    $totalUSD +=  $billet->evenementTypeBillet()->prix_unitaire * $billet->quantite;
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


  public function regenererDepuisDB($id)
{
    try {
        

        $billet = Billet::with(['evenement.ressource', 'type_billet'])->findOrFail($id);

        // Si déjà généré
       
        // =========================
        // RECONSTRUIRE LES DONNÉES
        // =========================
        $prix = $billet->type_billet->prix_unitaire;
        $devise = $billet->type_billet->devise;
        $total = $prix * $billet->quantite;
        
        $qrSvg = QrCode::format('svg')
            ->size(300)
            ->generate($billet->code_billet);

        // encoder proprement
        $qrImage = base64_encode($qrSvg);

        // créer l’URL base64
        $qrCodeUrl = 'data:image/svg+xml;base64,' . $qrImage;

        // passer à la vue
        
        $data = [
            'ticket' => [
                'user_name' => $billet->nom_auteur,
                'event_name' => $billet->evenement->nom ?? 'Evenement',
                'location' => $billet->evenement->adresse ?? 'Non définie',

                'type' => $billet->type_billet->nom_type ?? 'Standard',
                'quantity' => $billet->quantite,

                'price' => number_format($prix, 2, ',', ' '),
                'devise' => $devise,
                'total' => number_format($total, 2, ',', ' '),

                'qrcode_url' => $qrCodeUrl,
                'purchase_date' => $billet->date_achat,
                'event_date' => $billet->evenement->date_debut,
                'event_time' => $billet->evenement->heure_debut,

                'photo_affiche' => $billet->evenement->ressource[0]->photo_affiche ?? null,

                'ticket_id' => strtoupper(substr(md5($billet->code_billet), 0, 8)),
                'transaction_ref' => $billet->code_billet
            ]
        ];
    

        // =========================
        // GENERATION PDF
        // =========================
        $pdf = Pdf::loadView('billetPdf.billet', $data);

        $fileName = 'billets/' . $billet->nom_auteur . '.pdf';

        Storage::disk('public')->put($fileName, $pdf->output());

        // Update DB
        $billet->update([
            'billetImage' => $fileName
        ]);

        
        return back()->with('success', 'Billet régénéré avec succès.');

    } catch (\Exception $e) {
        dd($e->getMessage());
        return back()->with('error', $e->getMessage());
    }
}
}
