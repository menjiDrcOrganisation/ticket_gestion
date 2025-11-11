<?php

namespace App\Http\Controllers;

use App\Models\Retrait;
use Illuminate\Http\Request;

class RetraitController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
            $retraits = Retrait::with('organisateur')->get();
            $organisateurs = \App\Models\Organisateur::with('user')->get();
            return view('retraits.index', compact('retraits','organisateurs'));
        } catch (\Throwable $th) {
            return redirect()->back()->with('error', 'Erreur lors de la récupération des retraits : ' . $th->getMessage());
        }
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
    public function updateStatut(Request $request, $id)
{
    $retrait = Retrait::findOrFail($id);
    $retrait->update(['statut' => $request->statut]);

    return back()->with('success', 'Statut mis à jour avec succès !');
}
    public function store(Request $request)
    {
        try {
            $validatedData = $request->validate([
                'organisateur_id' => 'required|exists:organisateurs,id',
                'nom_detenteur' => 'required|string|max:255',
                'montant' => 'required|numeric|min:0',
                'date' => 'required|date',
                'statut' => 'required|string|max:50',
            ]);
    
            Retrait::create($validatedData);
    
            return redirect()->back()->with('success', 'Retrait créé avec succès.');
        } catch (\Throwable $th) {
            return redirect()->back()->with('error', 'Erreur lors de la création du retrait : ' . $th->getMessage());
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Retrait $retrait)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Retrait $retrait)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Retrait $retrait)
    {
        try {
            $validatedData = $request->validate([
                'organisateur_id' => 'required|exists:organisateurs,id',
                'nom_detenteur' => 'required|string|max:255',
                'montant' => 'required|numeric|min:0',
                'date' => 'required|date',
                'statut' => 'required|string|max:50',
            ]);
    
            $retrait->update($validatedData);
    
            return redirect()->back()->with('success', 'Retrait mis à jour avec succès.');
        } catch (\Throwable $th) {
            return redirect()->back()->with('error', 'Erreur lors de la mise à jour du retrait : ' . $th->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Retrait $retrait)
    {
        try {
            $retrait->delete();
            return redirect()->back()->with('success', 'Retrait supprimé avec succès.');
        } catch (\Throwable $th) {
            return redirect()->back()->with('error', 'Erreur lors de la suppression du retrait : ' . $th->getMessage());
        }
    }
}
