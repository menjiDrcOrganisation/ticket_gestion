<?php

namespace App\Http\Controllers\Web\Organisateur;

use App\Http\Controllers\Controller;
use App\Models\Evenement;
use App\Models\TypeEvenement;
use Illuminate\Http\Request;

class EventScannerController extends Controller
{
public function index(Request $request)
    {
        $organisateur = auth()->user()->organisateur;
        $search = trim((string) $request->query('q', ''));
        $status = trim((string) $request->query('statut', ''));
        $typeEvenementId = $request->query('type_evenement_id');
        $perPage = (int) $request->query('per_page', 10);

        if (!in_array($perPage, [10, 25, 50, 100], true)) {
            $perPage = 10;
        }

        $baseQuery = Evenement::with(['organisateur.user', 'typeBillets', 'typeEvenement', 'scanneur.user'])
            ->where('organisateur_id', $organisateur->id);

        $evenements = (clone $baseQuery)
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($subQuery) use ($search) {
                    $subQuery->where('nom', 'like', '%' . $search . '%')
                        ->orWhere('url_evenement', 'like', '%' . $search . '%')
                        ->orWhere('adresse', 'like', '%' . $search . '%')
                        ->orWhere('salle', 'like', '%' . $search . '%')
                        ->orWhereHas('scanneur.user', function ($scannerUserQuery) use ($search) {
                            $scannerUserQuery->where('name', 'like', '%' . $search . '%')
                                ->orWhere('email', 'like', '%' . $search . '%');
                        });
                });
            })
            ->when($status !== '', function ($query) use ($status) {
                $query->where('statut', $status);
            })
            ->when(!empty($typeEvenementId), function ($query) use ($typeEvenementId) {
                $query->where('type_evenement_id', (int) $typeEvenementId);
            })
            ->orderByDesc('date_debut')
            ->paginate($perPage)
            ->withQueryString();

        $totalEvenements = (clone $baseQuery)->count();
        $evenementsEncours = (clone $baseQuery)->where('statut', 'encours')->count();
        $evenementsFermes = (clone $baseQuery)->where('statut', 'ferme')->count();
        $evenementsSansScanneur = (clone $baseQuery)->whereNull('scanneur_id')->count();

        $typeEvenements = TypeEvenement::orderBy('nom_type')->get(['id', 'nom_type']);

        return view('event_scanner.index', compact(
            'evenements',
            'search',
            'status',
            'typeEvenementId',
            'perPage',
            'totalEvenements',
            'evenementsEncours',
            'evenementsFermes',
            'evenementsSansScanneur',
            'typeEvenements'
        ));
    }
}
