<?php

namespace App\Http\Controllers;

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
        $eventbillet = EvenementBilletTypeBillet::all();
        dd( $eventbillet);
        return view('evenementBilletTypeBillet.index', compact('eventbillet'));
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

        return view('achat', compact('achats', 'totalPaye', 'totalCDF', 'totalUSD'));

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
    public function show(EvenementBilletTypeBillet $evenementBilletTypeBillet)
    {
        //
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
