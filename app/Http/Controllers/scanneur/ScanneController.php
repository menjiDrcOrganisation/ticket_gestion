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

    // Méthode pour traiter le résultat du scan
    public function processScan(Request $request)
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

            $evenementBilletTypeBillet = EvenementBilletTypeBillet::where('billet_id', $billet->id)->first();

            if (!$evenementBilletTypeBillet) {
                return response()->json([
                    'valid' => false,
                    'message' => 'Aucune correspondance trouvée pour ce billet'
                ], 404);
            }

            if ($evenementBilletTypeBillet->quantite_fictif > 1) {
                $evenementBilletTypeBillet->decrement('quantite_fictif');
                $message = 'Billet validé';
            } else {
                $evenementBilletTypeBillet->update([
                    'quantite_fictif' => 0
                ]);
                $billet->update([
                    'statut_billet' => 'used'
                ]);
                $message = 'Dernier billet utilisé';
            }

            return response()->json([
                'valid' => true,
                'nom' => $billet->nom_auteur ?? '',
                'quantite_fictif' => $evenementBilletTypeBillet->quantite_fictif,
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
