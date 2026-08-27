<?php

namespace App\Http\Controllers\Web\Admin;

use App\Http\Controllers\Controller;
use App\Models\Organisateur;
use App\Models\Retrait;
use Illuminate\Http\Request;

class DemandeRetraitAdminController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)

    {
        $search = trim((string) $request->query('q', ''));
        $status = trim((string) $request->query('statut', ''));
        $dateFrom = $request->query('date_from');
        $dateTo = $request->query('date_to');

        $statusMap = [
            'en_attente' => ['en_attente', 'en attente', 'attente', 'pending'],
            'approuve' => ['approuve', 'approuvé', 'approved'],
            'refuse' => ['refuse', 'refusé', 'rejected'],
        ];

        $retraitsQuery = Retrait::query()->with('organisateur.user')
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($subQuery) use ($search) {
                    $subQuery->where('nom_detenteur', 'like', '%' . $search . '%')
                        ->orWhere('montant', 'like', '%' . $search . '%')
                        ->orWhereHas('organisateur.user', function ($userQuery) use ($search) {
                            $userQuery->where('name', 'like', '%' . $search . '%')
                                ->orWhere('email', 'like', '%' . $search . '%');
                        });
                });
            })
            ->when($status !== '', function ($query) use ($status, $statusMap) {
                $query->whereIn('statut', $statusMap[$status] ?? [$status]);
            })
            ->when(!empty($dateFrom), function ($query) use ($dateFrom) {
                $query->whereDate('date', '>=', $dateFrom);
            })
            ->when(!empty($dateTo), function ($query) use ($dateTo) {
                $query->whereDate('date', '<=', $dateTo);
            });

        //totale de retraits demandés
        $totaldmd = Retrait::count();
        //montant total des retraits demandés
        $totalmontantdmd = Retrait::sum('montant');
        //montage total des retraits demandés approuves
        $totalmontantdmdapprouve = Retrait::whereIn('statut', $statusMap['approuve'])->sum('montant');
       //
        
        //montant total des retraits demandés en attente
        $totalmontantdmdenattente = Retrait::whereIn('statut', $statusMap['en_attente'])->sum('montant');
        //montant total des retraits demandés refusés
        $totalmontantdmdrefuse = Retrait::whereIn('statut', $statusMap['refuse'])->sum('montant');
$stats = [
    'totaldmd' => Retrait::count(),

    'totalmontantdmd' => Retrait::sum('montant'),

    'totalmontantdmdapprouve' => Retrait::whereIn('statut', $statusMap['approuve'])
                ->sum('montant'),

    'totalmontantdmdenattente' => Retrait::whereIn('statut', $statusMap['en_attente'])
                ->sum('montant'),

    'totalmontantdmdrefuse' => Retrait::whereIn('statut', $statusMap['refuse'])
                ->sum('montant'),
];
        $retraits = $retraitsQuery->latest()->paginate(10)->withQueryString();
        $organisateurs = Organisateur::with('user')->get();

        return view('dmd_retraits.index', compact('retraits', 'organisateurs', 'totaldmd',
         'totalmontantdmd', 'totalmontantdmdapprouve',
          'totalmontantdmdenattente', 'totalmontantdmdrefuse', 'stats',
          'search', 'status', 'dateFrom', 'dateTo'));
    }

    public function updateStatut(Request $request, $id)
    {
        $retrait = Retrait::findOrFail($id);

        $incomingStatus = trim((string) $request->input('statut'));
        $normalizedStatus = match ($incomingStatus) {
            'en attente', 'en_attente', 'attente', 'pending' => 'en_attente',
            'approuvé', 'approuve', 'approved' => 'approuve',
            'refusé', 'refuse', 'rejected' => 'refuse',
            default => 'en_attente',
        };

        $retrait->statut = $normalizedStatus;
        $retrait->save();

        return redirect()->back()
                         ->with('success', 'Statut du retrait mis à jour avec succès.');
    }   

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }


    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        //
    }
}
