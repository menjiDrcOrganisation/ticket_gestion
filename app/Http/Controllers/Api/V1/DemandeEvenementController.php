<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Requests\Api\V1\StoreDemandeEvenementApiRequest;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\DemandeEvenement;

class DemandeEvenementController extends Controller
{
    public function storeDemande(StoreDemandeEvenementApiRequest $request)
    {
        $validated = $request->validated();

        // Upload affiche si existe
        $affichePath = null;
        if ($request->hasFile('affiche')) {
            $affichePath = $request->file('affiche')->store('affiches', 'public');
        }

        // Création de la demande
        $demande = DemandeEvenement::create([
            'nom_evenement' => $validated['nom_evenement'],
            'contact_organisateur' => $validated['contact_organisateur'],
            'description' => $validated['description'],
            'type_evenement' => $validated['type_evenement'],
            'affiche' => $affichePath,
            'statut' => $validated['statut'],
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Demande créée avec succès',
            'data' => $demande
        ], 201);
    }
}



