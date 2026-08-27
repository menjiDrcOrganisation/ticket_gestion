<?php

namespace App\Http\Controllers\Web\Organisateur;

use App\Http\Controllers\Controller;
use App\Http\Requests\Retrait\StoreRetraitRequest;
use App\Models\Evenement;
use App\Models\Retrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class RetraitController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $userOrganisateur  = auth()->user()->organisateur;
        $retraits = Retrait::with('organisateur')->where('organisateur_id', $userOrganisateur->id)->get();
        $evenementsCount = Evenement::where('organisateur_id', $userOrganisateur->id)->count();

        return view('retraits.index', compact('retraits', 'evenementsCount'));
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
    public function store(StoreRetraitRequest $request)
    {
    
        try {
            $validatedData = $request->validated();
                
            Retrait::create($validatedData);

            return redirect()->back()
                             ->with('success', 'Retrait créé avec succès.');
        } catch (\Exception $e) {
            Log::error('Erreur creation retrait organisateur', [
                'error_message' => $e->getMessage(),
                'exception' => $e,
            ]);

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
