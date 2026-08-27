<?php

namespace App\Http\Controllers\Web\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Evenement\UpdateEvenementRequest;
use App\Http\Requests\Evenement\UpdateEvenementStatusRequest;
use App\Models\Evenement;
use Illuminate\Http\Request;
use App\Models\TypeBillet;
use App\Models\TypeEvenement;
use App\Models\Ressource;

use App\Mail\EnvoiMotDePasseMail;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use App\Services\EvenementCreationService;

class EvenementController extends Controller
{
    
     
   public function index()
    {
        $evenements = Evenement::with(['organisateur.user', 'typeBillets','billets'])
            ->latest()
            ->paginate(10);

        $evenementsEncours= Evenement::where('statut', 'encours')
        ->with(['organisateur.user', 'typeBillets'])
        ->latest()
        ->get()->count();

         $evenementsPasse= Evenement::where('statut', 'ferme')
        ->with(['organisateur.user', 'typeBillets'])
        ->latest()
        ->get()->count();

        return view('evenements.showAll', compact('evenements','evenementsEncours',
    'evenementsPasse'));
    }

    public function create()
    {
        $typeBillets = TypeBillet::all();
        $typeEvenements = TypeEvenement::orderBy('nom_type')->get();

        return view('evenements.create', compact('typeBillets', 'typeEvenements'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(\App\Http\Requests\StoreEvenementRequest $request, EvenementCreationService $evenementCreationService)
    {
        $validated = $request->validated();

        try {
            $creation = $evenementCreationService->create($validated, true);
            $evenement = $creation['evenement'];
            $code_organi = $creation['organisateur_code'];
            $code_scanneur = $creation['scanneur_code'];
            $email_scanneur = $creation['scanneur_email'];
            
             try {
                
                Mail::to($validated['email_organisateur'])->send(new EnvoiMotDePasseMail(
                    $validated['nom_organisateur'],
                    $validated['email_organisateur'],
                    (string) $code_organi,
                    env('ACHAT_URL', 'https://kimiaticket.com') . "/" . $evenement->url_evenement,
                    (string) $email_scanneur,
                    (string) $code_scanneur
                ));
            
                $message = 'Événement créé avec succès et mail envoyé à l’organisateur.';
            } catch (\Exception $e) {

                $message = 'Événement créé avec succès, mais le mail n’a pas pu être envoyé. Erreur : ' . $e->getMessage();
            }

            return redirect()->route('evenements.index')->with('success', $message);
        } catch (ValidationException $e) {
            return redirect()->back()->withErrors($e->errors())->withInput();
        } catch (\Throwable $th) {
            return redirect()->back()->withInput()->with('error', 'Erreur lors de la creation de l evenement : ' . $th->getMessage());
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
    public function update(UpdateEvenementRequest $request, $id)
    {
        $validated = $request->validated();

        try {
            $evenement = Evenement::findOrFail($id);

            $evenement->update([
                'nom' => $validated['nom'] ?? $evenement->nom,
                'date_debut' => $validated['date_debut'] ?? $evenement->date_debut,
                'date_fin' => $validated['date_fin'] ?? $evenement->date_fin,
                'adresse' => $validated['adresse'] ?? $evenement->adresse,
                'salle' => $validated['salle'] ?? $evenement->salle,
                'url_evenement' => $validated['url_evenement'] ?? $evenement->url_evenement,
            ]);

            if ($request->hasFile('photo_affiche')) {
                $imagePath = $request->file('photo_affiche')->store('affiches', 'public');
                $ressource = $evenement->ressource()->latest('id')->first();

                if ($ressource && !empty($ressource->photo_affiche) && Storage::disk('public')->exists($ressource->photo_affiche)) {
                    Storage::disk('public')->delete($ressource->photo_affiche);
                }

                if ($ressource) {
                    $ressource->update([
                        'photo_affiche' => $imagePath,
                    ]);
                } else {
                    Ressource::create([
                        'nom_artiste' => 'Artiste',
                        'phrase_accroche' => null,
                        'a_propos' => null,
                        'photo_affiche' => $imagePath,
                        'evenement_id' => $evenement->id,
                    ]);
                }
            }

            return redirect()->back()->with('success', 'Evenement modifie avec succes.');
        } catch (\Throwable $th) {
            return redirect()->back()->with('error', 'Erreur lors de la mise a jour de l evenement : ' . $th->getMessage());
        }
        
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


    public function updateStatus(UpdateEvenementStatusRequest $request, $id)
{
     $validated = $request->validated();
    $evenement = Evenement::findOrFail($id);
     $evenement->statut = $validated['statut'];
    $evenement->save();

    return redirect()->back()->with('success', 'Statut de l’événement mis à jour avec succès.');
}

}
