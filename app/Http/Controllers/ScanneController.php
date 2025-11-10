<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ScanneController extends Controller
{
      
  public function showScanner()
    {
        
        return view('scanne.scanner');
    }

    // Méthode pour traiter le résultat du scan
    public function processScan(Request $request)
    {
        $data = $request->input('scan_data'); // Les données récupérées par le scan

        // Logique pour traiter les données scannées
        // Par exemple, chercher un produit ou un événement correspondant
        $result = SomeModel::where('code', $data)->first();

        if ($result) {
            return response()->json([
                'success' => true,
                'message' => 'Donnée trouvée',
                'data' => $result
            ]);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Donnée non trouvée'
            ]);
        }
    }
}
