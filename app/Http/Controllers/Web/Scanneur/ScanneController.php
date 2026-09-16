<?php

namespace App\Http\Controllers\Web\Scanneur;

use Illuminate\Http\Request;
use App\Models\Billet;
use App\Models\TypeBillet;
use App\Models\EvenementBilletTypeBillet;
use App\Models\Evenement;
use App\Http\Controllers\Controller; 

class ScanneController extends Controller
{
      
  public function showScanner()
    {
        $user = auth()->user();
        $selectedEventId = (int) request()->query('event_id', 0);

        if ($user?->role === 'organisateur' && $user->organisateur) {
            $evenementsOrganisateur = Evenement::where('organisateur_id', $user->organisateur->id)
                ->orderBy('date_debut', 'desc')
                ->get(['id', 'nom', 'date_debut']);

            $allowedEventIds = $evenementsOrganisateur->pluck('id')->all();
            if ($selectedEventId > 0 && !in_array($selectedEventId, $allowedEventIds, true)) {
                $selectedEventId = 0;
            }
        } elseif ($user?->role === 'scanneur' && $user->scanneur) {
            $evenementsOrganisateur = Evenement::where('scanneur_id', $user->scanneur->id)
                ->orderBy('date_debut', 'desc')
                ->get(['id', 'nom', 'date_debut']);

            $selectedEventId = (int) ($evenementsOrganisateur->first()->id ?? 0);
        } else {
            $evenementsOrganisateur = collect();
            $selectedEventId = 0;
        }

        return view('scanneur.scanner', compact('evenementsOrganisateur', 'selectedEventId'));
    }

       public function previewScanne(Request $request)
{
        try {
            $code = $request->input('code');
            $selectedEventId = (int) $request->input('event_id', 0);
            $allowedEventIds = $this->resolveAllowedEventIds();

            if (empty($allowedEventIds)) {
                return response()->json([
                    'valid' => false,
                    'message' => 'Accès non autorisé à un événement'
                ], 403);
            }

            if ($selectedEventId > 0 && !in_array($selectedEventId, $allowedEventIds, true)) {
                return response()->json([
                    'valid' => false,
                    'message' => 'Événement non autorisé'
                ], 403);
            }

            $scopeEventIds = $selectedEventId > 0 ? [$selectedEventId] : $allowedEventIds;

            $billet = Billet::where('code_billet', $code)
                ->whereIn('evenement_id', $scopeEventIds)
                ->first();
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
            $selectedEventId = (int) $request->input('event_id', 0);
            $allowedEventIds = $this->resolveAllowedEventIds();

            if (empty($allowedEventIds)) {
                return response()->json([
                    'valid' => false,
                    'message' => 'Accès non autorisé à un événement'
                ], 403);
            }

            if ($selectedEventId > 0 && !in_array($selectedEventId, $allowedEventIds, true)) {
                return response()->json([
                    'valid' => false,
                    'message' => 'Événement non autorisé'
                ], 403);
            }

            $scopeEventIds = $selectedEventId > 0 ? [$selectedEventId] : $allowedEventIds;

            $billet = Billet::where('code_billet', $code)
                ->whereIn('evenement_id', $scopeEventIds)
                ->first();
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

private function resolveAllowedEventIds(): array
{
    $user = auth()->user();

    if (!$user) {
        return [];
    }

    if ($user->role === 'organisateur' && $user->organisateur) {
        return Evenement::where('organisateur_id', $user->organisateur->id)->pluck('id')->all();
    }

    if ($user->role === 'scanneur' && $user->scanneur) {
        return Evenement::where('scanneur_id', $user->scanneur->id)->pluck('id')->all();
    }

    return [];
}

}







