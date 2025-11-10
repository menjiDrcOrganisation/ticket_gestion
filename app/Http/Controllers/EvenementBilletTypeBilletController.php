<?php

namespace App\Http\Controllers;

use App\Models\Evenement;
use App\Models\EvenementBilletTypeBillet;
use App\Models\EvenementTypeBillet;
use Illuminate\Http\Request;

class EvenementBilletTypeBilletController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
   
    {
        $evenementsActifs = Evenement::where('statut', 'actif')->count();

        $billetsVendus = EvenementBilletTypeBillet::sum('quantite');

        $revenusCDF = EvenementTypeBillet::where('devise', 'CDF')->get()->sum(fn($a) => $a->prix_unitaire * $a->quantite);
        $revenusUSD = EvenementTypeBillet::where('devise', 'USD')->get()->sum(fn($a) => $a->prix_unitaire * $a->quantite);

        $tauxRemplissage = $evenementsActifs > 0 
            ? round(($billetsVendus / ($evenementsActifs * 100)) * 100, 2)
            : 0;

        $derniersAchats = EvenementBilletTypeBillet::with(['evenement', 'billet'])
            ->latest()
            ->take(5)
            ->get();

        $evenementsPopulaires = EvenementBilletTypeBillet::with('evenement')
            ->selectRaw('evenement_id, SUM(quantite) as total')
            ->groupBy('evenement_id')
            ->orderByDesc('total')
            ->take(3)
            ->get();

        return view('billet', compact(
            'evenementsActifs',
            'billetsVendus',
            'revenusCDF',
            'revenusUSD',
            'tauxRemplissage',
            'derniersAchats',
            'evenementsPopulaires'
        ));
    }


    /**
     * Show the form for creating a new resource.
     */
    public function achatbillet(Request $request, $evenementId)
{
    try {
        // Récupère tous les achats pour l'événement
        $achats = EvenementBilletTypeBillet::with(['type_billet', 'evenement'])
            ->where('evenement_id', $evenementId)
            ->paginate(10);

        // Récupération des informations de tarification
        $tarifs = EvenementTypeBillet::where('evenement_id', $evenementId)->get();

        // Calcul des totaux par devise
        $totalCDF = $tarifs->where('devise', 'CDF')->sum(function ($item) {
            return $item->prix_unitaire * $item->nombre_billet;
        });

        $totalUSD = $tarifs->where('devise', 'USD')->sum(function ($item) {
            return $item->prix_unitaire * $item->nombre_billet;
        });

        // Total général (CDF + USD convertis si besoin)
        $totalPaye = $tarifs->sum(function ($item) {
            return $item->prix_unitaire * $item->nombre_billet;
        });

        // Debug ou affichage
        // dd($achats, $totalPaye, $totalCDF, $totalUSD);

        return view('organisateurs.achat', compact('achats', 'totalPaye', 'totalCDF', 'totalUSD'));

    } catch (\Throwable $th) {
        return redirect()->back()->with('error', 'Erreur lors de la récupération des billets : ' . $th->getMessage());
    }
}

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
         $evenement = Evenement::findOrFail($id);

        // Récupérer les billets et ventes de l'événement
        $achats = EvenementBilletTypeBillet::where('evenement_id', $id)->get();
        $typesBillets = EvenementTypeBillet::where('evenement_id', $id)->with('type_billet')->get();

        $totalBilletsVendus = $achats->sum('quantite');
        $revenusCDF = $achats->where('devise', 'CDF')->sum(fn($a) => $a->prix_unitaire * $a->quantite);
        $revenusUSD = $achats->where('devise', 'USD')->sum(fn($a) => $a->prix_unitaire * $a->quantite);

        // Taux de remplissage basé sur le nombre total disponible
        $totalBilletsDisponibles = $typesBillets->sum('nombre_billet');
        $tauxRemplissage = $totalBilletsDisponibles > 0 
            ? round(($totalBilletsVendus / $totalBilletsDisponibles) * 100, 1)
            : 0;

        // Achats récents
        $derniersAchats = $achats->sortByDesc('date_achat')->take(5);

        return view('organisateurs.resume', compact(
            'evenement',
            'totalBilletsVendus',
            'revenusCDF',
            'revenusUSD',
            'tauxRemplissage',
            'typesBillets',
            'derniersAchats'
        ));
    }


    /**
     * Show the form for editing the specified resource.
     */
    public function edit(EvenementBilletTypeBillet $evenementBilletTypeBillet)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, EvenementBilletTypeBillet $evenementBilletTypeBillet)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(EvenementBilletTypeBillet $evenementBilletTypeBillet)
    {
        //
    }
}
