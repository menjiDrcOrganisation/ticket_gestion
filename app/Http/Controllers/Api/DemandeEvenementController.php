<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\DemandeEvenement;
use Illuminate\Support\Facades\Validator;

class DemandeEvenementController extends Controller
{
    public function storeDemande(Request $request)
    {
        // Validation
        $validator = Validator::make($request->all(), [
            'nom_evenement' => 'required|string|max:255',
            'contact_organisateur' => 'required|string|max:255',
            'description' => 'required|string',
            'type_evenement' => 'required|string|max:255',
            'affiche' => 'nullable|image|max:2048', // max 2MB
            'statut' => 'required|in:en_attente,valide,ferme',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        // Upload affiche si existe
        $affichePath = null;
        if ($request->hasFile('affiche')) {
            $affichePath = $request->file('affiche')->store('affiches', 'public');
        }

        // Création de la demande
        $demande = DemandeEvenement::create([
            'nom_evenement' => $request->nom_evenement,
            'contact_organisateur' => $request->contact_organisateur,
            'description' => $request->description,
            'type_evenement' => $request->type_evenement,
            'affiche' => $affichePath,
            'statut' => $request->statut,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Demande créée avec succès',
            'data' => $demande
        ], 201);
    }
}
