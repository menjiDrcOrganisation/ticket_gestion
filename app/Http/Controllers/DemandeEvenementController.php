<?php

namespace App\Http\Controllers;

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
    public function store(Request $request)
    {
        $request->validate([
            'nom_evenement' => 'required|string|max:255',
            'contact_organisateur' => 'required|string|max:255',
            'description' => 'required|string',
            'type_evenement' => 'required|string|max:255',
            'statut' => 'required|string|in:en_attente,valide,ferme',
            'affiche' => 'nullable|image|max:2048', // 2MB max
        ]);

        $affichePath = null;
        if($request->hasFile('affiche')){
            $affichePath = $request->file('affiche')->store('affiches', 'public');
        }

        DemandeEvenement::create([
            'nom_evenement' => $request->nom_evenement,
            'contact_organisateur' => $request->contact_organisateur,
            'description' => $request->description,
            'type_evenement' => $request->type_evenement,
            'statut' => $request->statut,
            'affiche' => $affichePath,
        ]);

        return redirect()->back()->with('success', 'Demande ajoutée avec succès.');
    }

    // Modifier une demande
    public function update(Request $request, DemandeEvenement $demandeEvenement)
    {
        $request->validate([
            'nom_evenement' => 'required|string|max:255',
            'contact_organisateur' => 'required|string|max:255',
            'description' => 'required|string',
            'type_evenement' => 'required|string|max:255',
            'statut' => 'required|string|in:en_attente,valide,ferme',
            'affiche' => 'nullable|image|max:2048',
        ]);

        if($request->hasFile('affiche')){
            // Supprimer l'ancienne affiche si elle existe
            if($demandeEvenement->affiche){
                Storage::disk('public')->delete($demandeEvenement->affiche);
            }
            $demandeEvenement->affiche = $request->file('affiche')->store('affiches', 'public');
        }

        $demandeEvenement->update([
            'nom_evenement' => $request->nom_evenement,
            'contact_organisateur' => $request->contact_organisateur,
            'description' => $request->description,
            'type_evenement' => $request->type_evenement,
            'statut' => $request->statut,
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
public function changeStatus(Request $request, DemandeEvenement $demandeEvenement)
{
    $request->validate([
        'statut' => 'required|string|in:en_attente,valide,ferme'
    ]);

    $demandeEvenement->update([
        'statut' => $request->statut
    ]);

    return redirect()->back()->with('success', 'Statut mis à jour avec succès.');
}

}
