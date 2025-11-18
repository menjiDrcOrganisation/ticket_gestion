<?php

namespace App\Http\Controllers;

use App\Models\Organisateur;
use App\Models\Retrait;
use Illuminate\Http\Request;

class DemandeRetraitAdminController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $retraits = Retrait::with('organisateur.user')->get();
        $organisateurs = Organisateur::with('user')->get();
        return view('dmd_retraits.index', compact('retraits', 'organisateurs'));
    }

    public function updateStatut(Request $request, $id)
    {
        $retrait = Retrait::findOrFail($id);
        $retrait->statut = $request->input('statut');
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
