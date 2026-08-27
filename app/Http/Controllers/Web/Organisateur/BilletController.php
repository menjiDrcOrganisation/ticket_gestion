<?php

namespace App\Http\Controllers\Web\Organisateur;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Billet;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Pagination\LengthAwarePaginator;
use App\Models\Evenement;
use App\Models\EvenementTypeBillet;
use App\Services\TicketPdfService;

class BilletController extends Controller
{
 
    public function index(Request $request)
    {
        try {
            $totalRestant = 0;
            $totalAchat = 0;
            $detailleParBillet = [];
            $totalCDF = 0;
            $totalUSD = 0;

            $search = trim((string) $request->query('q', ''));
            $status = trim((string) $request->query('statut', ''));
            $devise = trim((string) $request->query('devise', ''));
            $type = trim((string) $request->query('type', ''));
            $selectedEventId = (int) $request->query('event_id', 0);
            $perPage = (int) $request->query('per_page', 10);
            if (!in_array($perPage, [10, 25, 50, 100], true)) {
                $perPage = 10;
            }

             
            $user = Auth::user();
            $evenementsOrganisateur = Evenement::where('organisateur_id', $user->organisateur->id)
                ->orderBy('date_debut', 'desc')
                ->get(['id', 'nom', 'date_debut']);

            $allowedEventIds = $evenementsOrganisateur->pluck('id')->all();
            if (empty($allowedEventIds)) {
                $emptyPaginator = new LengthAwarePaginator([], 0, $perPage, 1, [
                    'path' => $request->url(),
                    'query' => $request->query(),
                ]);

                return view('organisateurs.achat', [
                    'detailleParBillet' => $emptyPaginator,
                    'totalCDF' => 0,
                    'totalUSD' => 0,
                    'totalAchat' => 0,
                    'totalRestant' => 0,
                    'search' => $search,
                    'status' => $status,
                    'devise' => $devise,
                    'type' => $type,
                    'selectedEventId' => 0,
                    'perPage' => $perPage,
                    'statusOptions' => [],
                    'deviseOptions' => [],
                    'typeOptions' => [],
                    'evenementsOrganisateur' => collect(),
                ]);
            }

            if ($selectedEventId > 0 && !in_array($selectedEventId, $allowedEventIds, true)) {
                $selectedEventId = 0;
            }

            $billets = Billet::with('evenement','type_billet')
                ->whereIn('evenement_id', $allowedEventIds)
                ->when($selectedEventId > 0, function ($query) use ($selectedEventId) {
                    $query->where('evenement_id', $selectedEventId);
                })
                ->orderBy('id', 'desc')
                ->get();
            

        foreach ($billets as $billet) {
            $eventType = $billet->evenementTypeBillet();
            if (!$eventType) {
                continue;
            }
        
            if (!isset($detailleParBillet[$billet->id])) {
                    $detailleParBillet[$billet->id] = [
                        'id' =>$billet->id,
                        'auteur' =>$billet->nom_auteur,
                        'numero_auteur' =>$billet->nom_auteur,
                        'devise' => $eventType->devise,
                        'type' =>$billet->type_billet->nom_type,
                        'quantite' => $billet->quantite,
                        'quantite_fictif' => $billet->quantite_fictif,
                        'prix_unitaire' => $eventType->prix_unitaire,
                        'total' => $eventType->prix_unitaire * $billet->quantite,
                        'date' => $billet->date_achat,
                        'code' => $billet->code_billet,
                        'statut' => $billet->statut,
                        'billetImage'=> $billet->billetImage
                    ];
            }

            $totalAchat+=$billet->quantite ;

                // Montants par devise
                if ($eventType->devise === "CDF") {
                    $totalCDF += $eventType->prix_unitaire * $billet->quantite;
                }

                if ($eventType->devise === "USD") {
                    $totalUSD += $eventType->prix_unitaire * $billet->quantite;
                }
        }

        $detailleCollection = collect($detailleParBillet)->values();

        $statusOptions = $detailleCollection
            ->pluck('statut')
            ->filter()
            ->unique()
            ->sort()
            ->values();

        $deviseOptions = $detailleCollection
            ->pluck('devise')
            ->filter()
            ->unique()
            ->sort()
            ->values();

        $typeOptions = $detailleCollection
            ->pluck('type')
            ->filter()
            ->unique()
            ->sort()
            ->values();

        $filtered = $detailleCollection->filter(function (array $item) use ($search, $status, $devise, $type): bool {
            if ($search !== '') {
                $haystack = mb_strtolower(
                    implode(' ', [
                        (string) ($item['auteur'] ?? ''),
                        (string) ($item['type'] ?? ''),
                        (string) ($item['code'] ?? ''),
                    ])
                );

                if (!str_contains($haystack, mb_strtolower($search))) {
                    return false;
                }
            }

            if ($status !== '' && mb_strtolower((string) ($item['statut'] ?? '')) !== mb_strtolower($status)) {
                return false;
            }

            if ($devise !== '' && (string) ($item['devise'] ?? '') !== $devise) {
                return false;
            }

            if ($type !== '' && (string) ($item['type'] ?? '') !== $type) {
                return false;
            }

            return true;
        })->values();

        $currentPage = LengthAwarePaginator::resolveCurrentPage();
        $currentPageItems = $filtered->forPage($currentPage, $perPage)->values();

        $detailleParBillet = new LengthAwarePaginator(
            $currentPageItems,
            $filtered->count(),
            $perPage,
            $currentPage,
            [
                'path' => $request->url(),
                'query' => $request->query(),
            ]
        );

        
        return view('organisateurs.achat', compact(
            'detailleParBillet',
            'totalCDF',
            'totalUSD',
            'totalAchat',
            'totalRestant',
            'search',
            'status',
            'devise',
            'type',
            'selectedEventId',
            'perPage',
            'statusOptions',
            'deviseOptions',
            'typeOptions',
            'evenementsOrganisateur'
        ));

        } catch (\Throwable $th) {
            Log::error('Erreur chargement billets organisateur', [
                'error_message' => $th->getMessage(),
                'exception' => $th,
            ]);

            return redirect()->back()->with('error', 'Impossible de charger les billets pour le moment.');
        }
    }

     public function destroy($id)
    {
        // Récupérer le billet par son id
        $billet = Billet::find($id);

        if (!$billet) {
            return redirect()->back()->with('error', 'Billet introuvable.');
        }

        // Supprimer le billet
        $billet->delete();

        // Rediriger avec message de succès
        return redirect()->back()->with('success', 'Billet supprimé avec succès.');
    }


  public function regenererDepuisDB($id, TicketPdfService $ticketPdfService)
{
    try {
        $billet = Billet::with(['evenement.ressource', 'type_billet'])->findOrFail($id);

        $type_billet = EvenementTypeBillet::where('type_billet_id', $billet->type_billet_id)
                ->where('evenement_id', $billet->evenement_id)
                ->first();
        
        if (!$type_billet) {
            return back()->with('error', 'Type de billet introuvable pour cet evenement.');
        }
       
        $prix = (float) $type_billet->prix_unitaire;
        $total = $prix * (int) $billet->quantite;

        $ticketPdfService->generate(
            $billet,
            $prix,
            (string) $type_billet->devise,
            $total,
            (string) $billet->code_billet
        );

        
        return back()->with('success', 'Billet régénéré avec succès.');

    } catch (\Exception $e) {
        return back()->with('error', $e->getMessage());
    }
}
}





