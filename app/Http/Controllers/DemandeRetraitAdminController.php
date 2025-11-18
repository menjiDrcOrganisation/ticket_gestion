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

        //totale de retraits demandés
        $totaldmd = Retrait::count();
        //montant total des retraits demandés
        $totalmontantdmd = Retrait::sum('montant');
        //montage total des retraits demandés approuves
        $totalmontantdmdapprouve = Retrait::where('statut', 'approuve')->sum('montant');
       //
        
        //montant total des retraits demandés en attente
        $totalmontantdmdenattente = Retrait::where('statut', 'en_attente')->sum('montant');
        //montant total des retraits demandés refusés
        $totalmontantdmdrefuse = Retrait::where('statut', 'refuse')->sum('montant');
$stats = [
    'totaldmd' => Retrait::count(),

    'totalmontantdmd' => Retrait::sum('montant'),

    'totalmontantdmdapprouve' => Retrait::where('statut', 'approuve')
                ->sum('montant'),

    'totalmontantdmdenattente' => Retrait::where('statut', 'en attente')
                ->sum('montant'),

    'totalmontantdmdrefuse' => Retrait::where('statut', 'refuse')
                ->sum('montant'),
];
        $retraits = Retrait::with('organisateur.user')->get();
        $organisateurs = Organisateur::with('user')->get();

        return view('dmd_retraits.index', compact('retraits', 'organisateurs', 'totaldmd',
         'totalmontantdmd', 'totalmontantdmdapprouve',
          'totalmontantdmdenattente', 'totalmontantdmdrefuse', 'stats'));
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
