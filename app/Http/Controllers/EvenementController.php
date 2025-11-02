<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

use App\Models\Evenement;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Organisateur;
use App\Models\EvenementTypeBillet;
use App\Models\TypeBillet;

class EvenementController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $evenements = Evenement::with(['organisateur.user','typeBillets'])->latest()->get();
        return view('evenements.showAll',compact('evenements'));
    }

    public function create()
    {
        $typeBillets = TypeBillet::all();
         return view('evenements.create',compact('typeBillets'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nom_evenement' => 'required|string|max:255',
            'nom_organisateur' => 'nullable|string|max:255',
            'email_organisateur' => 'nullable|string|max:255',
            'adresse' => 'required|string|max:255',
            'salle' => 'required|string|max:255',
            'date_debut' => 'required|date',
            'date_fin' => 'required|date|after_or_equal:date_debut',
            'heure_debut' => 'required',
            'heure_fin' => 'required',
            'ticket_type_id' => 'required|array',
            'quantite' => 'required|array',
            'prix' => 'required|array',
            'telephone' => 'required',
        ]);

        try {
            // Création de l'organisateur si fourni
            if (!empty($validated['nom_organisateur'])) {
                $user = User::create([
                    'name' => $validated['nom_organisateur'],
                    'email' => $validated['email_organisateur'],
                    'password' => Hash::make('password123'),
                    'role' => 'organisateur',
                ]);

                $organisateur = Organisateur::create([
                    'user_id' => $user->id,
                    'telephone' => $validated['telephone'],
                ]);
            } else {
                $organisateur = null;
            }

            // Création de l'événement
            $evenement = Evenement::create([
                'nom' => $validated['nom_evenement'],
                'url_evenement' => Str::slug($validated['nom_evenement']),
                'organisateur_id' => $organisateur?->id,
                'adresse' => $validated['adresse'],
                'salle' => $validated['salle'],
                'date_debut' => $validated['date_debut'],
                'date_fin' => $validated['date_fin'],
                'heure_debut' => $validated['heure_debut'],
                'heure_fin' => $validated['heure_fin'],
                'statut' => 'encours',
            ]);

            // Boucle sur les billets
            foreach ($validated['ticket_type_id'] as $index => $typeId) {
                $quantite = $validated['quantite'][$index] ?? 0;
                $prix = $validated['prix'][$index] ?? 0;

                if ($quantite > 0) {
                    EvenementTypeBillet::create([
                        'evenement_id' => $evenement->id,
                        'type_billet_id' => $typeId,
                        'nombre_billet' => $quantite,
                        'prix_unitaire' => $prix,
                    ]);
                }
            }

            return redirect()->route('evenements.index')->with('success', 'Événement créé avec succès.');
        } catch (\Throwable $th) {
            return $th;
        }
    }


    
    public function show($url_evenement)
    {
        try {
            // On récupère l'événement via son URL unique
            $evenement = Evenement::with([
                'organisateur.user', 
                'typeBillets' => function ($query) {
                    $query->withPivot('nombre_billet');
                }
            ])->where('url_evenement', $url_evenement)->firstOrFail();

            return view('evenements.show', compact('evenement'));

        } catch (\Exception $e) {
            return redirect()->route('evenements.index')
                            ->with('error', "Événement introuvable.");
        }
    }


    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Evenement $evenement)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Evenement $evenement)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
          try {
        $evenement = Evenement::with('typeBillets')->findOrFail($id);
        $evenement->typeBillets()->detach();
        $evenement->delete();
        return redirect()->route('evenements.index')->with('success', 'Événement supprimé avec succès.');

    } catch (\Exception $e) {
        return redirect()->route('evenements.index')->with('error', 'Une erreur est survenue lors de la suppression.');
    }
    }


   public function updateStatus(Request $request, $id)
{
    $evenement = Evenement::findOrFail($id);
    $evenement->statut = $request->statut;
    $evenement->save();

    return redirect()->back()->with('success', 'Statut de l’événement mis à jour avec succès.');
}

}
