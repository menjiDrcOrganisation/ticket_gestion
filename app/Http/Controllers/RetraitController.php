<?php

namespace App\Http\Controllers;

use App\Models\Admin;
use App\Models\Retrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validate;

class RetraitController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $userOrganisateur  = auth()->user()->organisateur;
        $retraits = Retrait::with('organisateur')->where('organisateur_id', $userOrganisateur->id)->get();
        return view('retraits.index', compact('retraits'));
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
    
        try {
            $validatedData = $request->validate([
                'organisateur_id' => 'required|exists:organisateurs,id',
                'nom_detenteur' => 'required|string|max:255',
                'montant' => 'required|numeric',
                'date' => 'required|date',
                'statut' => 'required|string|max:50',
                
            ]);
                
            Retrait::create($validatedData);

            return redirect()->back()
                             ->with('success', 'Retrait créé avec succès.');
        } catch (\Exception $e) {
            dd($e->getMessage());
            return redirect()->back()
                             ->with('error', 'Une erreur est survenue lors de la création du retrait.');
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
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Retrait $retrait)
    {
        //
    }
}
