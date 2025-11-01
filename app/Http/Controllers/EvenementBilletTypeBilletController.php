<?php

namespace App\Http\Controllers;

use App\Models\EvenementBilletTypeBillet;
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
        // Logique pour afficher les billets disponibles pour l'événement spécifié

    $achats = EvenementBilletTypeBillet::where('evenement_id', $evenementId)->get();

    return view('achat', compact('achats'));


    } catch (\Throwable $th) {
        return redirect()->back()->with('error', 'Erreur lors de la récupération des billets.');
       
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
