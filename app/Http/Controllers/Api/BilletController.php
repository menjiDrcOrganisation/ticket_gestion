<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\MobileMoneyService;

class BilletController extends Controller
{
    public function achatbillet(Request $request)
    {
             try {
            $validated = $request->validate([
                'type_billet' => 'required|string',
                'nombre_reel' => 'required|integer|min:1',
                'nom_complet_client' => 'required|string',
                'numero_client' => 'required|string',
                'service' => 'required|string',
                'id_evenement' => 'required|string'
            ]);

        
            $result = MobileMoneyService::sendPayment($validated);

            return $result;

        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Une erreur est survenue.',
                'error' => $e->getMessage(),
            ], 500);
        }


    }
}
