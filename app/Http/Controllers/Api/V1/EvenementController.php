<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\StoreEvenementApiRequest;
use Illuminate\Http\Request;
use App\Models\Evenement;
use App\Services\EvenementCreationService;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class EvenementController extends Controller
{
    public function store(StoreEvenementApiRequest $request, EvenementCreationService $evenementCreationService)
    {
        try {
            $validated = $request->validated();
            $creation = $evenementCreationService->create($validated, true);
            $evenement = $creation['evenement']->load(['organisateur.user', 'scanneur.user', 'typeBillets', 'ressource', 'typeEvenement']);

            return response()->json([
                'success' => true,
                'message' => 'Evenement cree avec succes',
                'data' => $evenement,
                'credentials' => [
                    'organisateur_code' => $creation['organisateur_code'],
                    'scanneur_code' => $creation['scanneur_code'],
                    'scanneur_email' => $creation['scanneur_email'],
                ],
            ], 201);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur de validation',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Throwable $th) {
            Log::error('Erreur API storeEvenement', [
                'payload_keys' => array_keys($request->all()),
                'message' => $th->getMessage(),
                'file' => $th->getFile(),
                'line' => $th->getLine(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erreur serveur lors de la creation de l evenement',
            ], 500);
        }
    }

    public function getEvenement(Request $request,$short_url){
        try {
            $evenement = Evenement::with(['organisateur', 'typeBillets','ressource','typeEvenement'])
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
        } catch (\Throwable $th) {
            Log::error('Erreur API getEvenement', [
                'short_url' => $short_url,
                'query' => $request->query(),
                'message' => $th->getMessage(),
                'file' => $th->getFile(),
                'line' => $th->getLine(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erreur serveur lors de la récupération de l\'événement',
            ], 500);
        }
    }


    public function getAll(Request $request){
        try {
            $evenements = Evenement::with(['typeBillets','ressource','typeEvenement'])->get();

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
        } catch (\Throwable $th) {
            Log::error('Erreur API getAllEvenements', [
                'query' => $request->query(),
                'message' => $th->getMessage(),
                'file' => $th->getFile(),
                'line' => $th->getLine(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erreur serveur lors de la récupération des événements',
            ], 500);
        }
    }
}





