<?php

namespace App\Http\Controllers;

use App\Models\TypeEvenement;
use Illuminate\Http\Request;

class TypeEvenementController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $typeEvenements = TypeEvenement::all();
        return view('typeEvenement.index', compact('typeEvenements'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
    
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            $request->validate([
                'nom_type' => 'required|string|max:255',
            ]);
            $typeEvenement = TypeEvenement::create([
                'nom_type' => $request->nom_type,
            ]);
            return redirect()->back()->with('success', 'Type d\'événement créé avec succès');
        } catch (\Throwable $th) {
            return redirect()->back()->with('error', 'Erreur lors de la création du type d\'événement');
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(TypeEvenement $typeEvenement)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(TypeEvenement $typeEvenement)
    {
        
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, TypeEvenement $typeEvenement)
    {
        try {
            $request->validate([
                'nom_type' => 'sometimes|required|string|max:255',
            ]);
            $typeEvenement->update($request->only('nom_type'));
            return redirect()->back()->with('success', 'Type d\'événement mis à jour avec succès');
        } catch (\Throwable $th) {
            return redirect()->back()->with('error', 'Erreur lors de la mise à jour du type d\'événement');
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(TypeEvenement $typeEvenement)
    {
        try {
            $typeEvenement->delete();
            return redirect()->back()->with('success', 'Type d\'événement supprimé avec succès'); 
        } catch (\Throwable $th) {
            return redirect()->back()->with('error', 'Erreur lors de la suppression du type d\'événement');
        }
    }
}
