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
        $validated = $request->validate([
    'nom_evenement' => 'required|string|max:255',

    'contact_organisateur' => [
        'required',
        'string',
        'max:255',
        function ($attribute, $value, $fail) {
            $isEmail = filter_var($value, FILTER_VALIDATE_EMAIL);

            // Exemple : +243812345678, 0812345678, 081 234 56 78
            $isPhone = preg_match('/^\+?[0-9][0-9\s\-]{7,20}$/', $value);

            if (!$isEmail && !$isPhone) {
                $fail('Le contact doit être un numéro de téléphone ou une adresse email valide.');
            }
        },
    ],

    'description' => 'required|string',
    'type_evenement' => 'required|string|max:255',
    'statut' => 'required|string|in:en_attente,valide,ferme',
    'affiche' => 'nullable|image|max:2048',
]);

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



