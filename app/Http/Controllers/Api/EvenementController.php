<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\DemandeEvenement;
use App\Models\Evenement;
use Illuminate\Support\Facades\Validator;

class EvenementController extends Controller
{
    public function getEvenement(Request $request,$short_url)
    {
       
        $evenement = Evenement::with(['organisateur', 'typeBillets'])
        ->where('url_evenement', $short_url)
        ->first();

    if (!$evenement) {
        return response()->json([
            'success' => false,
            'message' => 'Événement non trouvé',
        ], 404);
    }

    return response()->json([
        'success' => true,
        'message' => 'Événement récupéré avec succès',
        'data' => $evenement
    ], 200);
    }


    public function getAll(Request $request)
    {
        $evenements = Evenement::with([ 'typeBillets'])->get();

    if (!$evenements) {
        return response()->json([
            'success' => false,
            'message' => 'Événement non trouvé',
        ], 404);
    }

    return response()->json([
        'success' => true,
        'message' => 'Événement récupéré avec succès',
        'data' => $evenements
    ], 200);
    }
}


