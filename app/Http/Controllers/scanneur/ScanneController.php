<?php

namespace App\Http\Controllers\scanneur;

use Illuminate\Http\Request;
use App\Models\Billet;
use App\Models\TypeBillet;
use App\Models\EvenementBilletTypeBillet;
use App\Http\Controllers\Controller; 

class ScanneController extends Controller
{
      
  public function showScanner()
    {
        return view('scanneur.scanner');
    }

       public function previewScanne(Request $request)
{
        try {
            $code = $request->input('code');
            $billet = Billet::where('code_billet', $code)->first();
            if (!$billet) {
                return response()->json([
                    'valid' => false,
                    'message' => 'Code invalide'
                ], 404);
            }

    
            if (!$billet) {
                return response()->json([
                    'valid' => false,
                    'message' => 'Aucune correspondance trouvée pour ce billet'
                ], 404);
            }
             if ($billet->quantite_fictif > 1) {
                $message = 'Billet validé';
            } else {
                $message = 'Dernier billet utilisé';
            }

           
            return response()->json([
                'valid' => true,
                'nom' => $billet->nom_auteur ?? '',
                'quantite_fictif' => $billet->quantite_fictif,
                "code"=> $code,
                'message' => $message
            ]);
        } catch (\Throwable $th) {

            return response()->json([
                'valid' => false,
                'error' => $th->getMessage(),
                'nom' => '',
                'quantite_fictif' => ''
            ], 500);
        }
}

    // Méthode pour traiter le résultat du scan
    public function processScan(Request $request)
{
        try {
            $code = $request->input('code');
            $quantite = $request->input('quantite');
           
            $billet = Billet::where('code_billet', $code)->first();
            if (!$billet) {
                return response()->json([
                    'valid' => false,
                    'message' => 'Code invalide'
                ], 404);
            }

  
            if (!$billet) {
                return response()->json([
                    'valid' => false,
                    'message' => 'Aucune correspondance trouvée pour ce billet'
                ], 404);
            }

            if ($billet->quantite_fictif > 1) {
                $billet->decrement('quantite_fictif',$quantite);
                $message = 'Billet validé';
            } else {
                $billet->update([
                    'quantite_fictif' => 0
                ]);
                $billet->update([
                    'statut' => 'utilisee'
                ]);
                $message = 'Dernier billet utilisé';
            }

            return response()->json([
                'valid' => true,
                'nom' => $billet->nom_auteur ?? '',
                'quantite_fictif' => $billet->quantite_fictif,
                'message' => $message
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'valid' => false,
                'error' => $th->getMessage(),
                'nom' => '',
                'quantite_fictif' => ''
            ], 500);
        }
}

}
