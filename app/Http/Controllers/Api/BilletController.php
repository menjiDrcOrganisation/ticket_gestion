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
                'devise' => 'required|string',
                'nom_complet_client' => 'required|string',
                'numero_client' => 'required|string',
                'service' => 'required|string',
            ]);

            return  $validated;

            $result = MobileMoneyService::sendPayment( $validated);

            return response()->json($result, $result['status'] ? 200 : 400);

        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Une erreur est survenue.',
                'error' => $e->getMessage(),
            ], 500);
        }


    }
}
