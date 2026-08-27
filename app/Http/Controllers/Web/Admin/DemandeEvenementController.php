<?php

namespace App\Http\Controllers\Web\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\DemandeEvenement\ChangeDemandeEvenementStatusRequest;
use App\Http\Requests\DemandeEvenement\StoreDemandeEvenementRequest;
use App\Http\Requests\DemandeEvenement\UpdateDemandeEvenementRequest;
use App\Models\DemandeEvenement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class DemandeEvenementController extends Controller
{
    // Afficher toutes les demandes
    public function index()
    {
        $demandeEvenements = DemandeEvenement::latest()->get();
        return view('dmdEvent.index', compact('demandeEvenements'));
    }

    // Ajouter une demande
    public function store(StoreDemandeEvenementRequest $request)
    {
        $validated = $request->validated();

        $affichePath = null;
        if($request->hasFile('affiche')){
            $affichePath = $request->file('affiche')->store('affiches', 'public');
        }

        DemandeEvenement::create([
            'nom_evenement' => $validated['nom_evenement'],
            'contact_organisateur' => $validated['contact_organisateur'],
            'description' => $validated['description'],
            'type_evenement' => $validated['type_evenement'],
            'statut' => $validated['statut'],
            'affiche' => $affichePath,
        ]);

        return redirect()->back()->with('success', 'Demande ajoutée avec succès.');
    }

    // Modifier une demande
    public function update(UpdateDemandeEvenementRequest $request, DemandeEvenement $demandeEvenement)
    {
        $validated = $request->validated();

        if($request->hasFile('affiche')){
            // Supprimer l'ancienne affiche si elle existe
            if($demandeEvenement->affiche){
                Storage::disk('public')->delete($demandeEvenement->affiche);
            }
            $demandeEvenement->affiche = $request->file('affiche')->store('affiches', 'public');
        }

        $demandeEvenement->update([
            'nom_evenement' => $validated['nom_evenement'],
            'contact_organisateur' => $validated['contact_organisateur'],
            'description' => $validated['description'],
            'type_evenement' => $validated['type_evenement'],
            'statut' => $validated['statut'],
        ]);

        return redirect()->back()->with('success', 'Demande modifiée avec succès.');
    }

    // Supprimer une demande
    public function destroy(DemandeEvenement $demandeEvenement)
    {
        if($demandeEvenement->affiche){
            Storage::disk('public')->delete($demandeEvenement->affiche);
        }
        $demandeEvenement->delete();

        return redirect()->back()->with('success', 'Demande supprimée avec succès.');
    }
    // Changer le statut rapidement
public function changeStatus(ChangeDemandeEvenementStatusRequest $request, DemandeEvenement $demandeEvenement)
{
    $validated = $request->validated();

    $demandeEvenement->update([
        'statut' => $validated['statut']
    ]);

    return redirect()->back()->with('success', 'Statut mis à jour avec succès.');
}

}
