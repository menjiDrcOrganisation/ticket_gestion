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
use App\Models\Scanneur;
use App\Models\Ressource;

use App\Mail\EnvoiMotDePasseMail;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Carbon\Carbon;

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
         return view('evenements.create',compact('typeBillets'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(\App\Http\Requests\StoreEvenementRequest $request)
    {
        $validated = $request->validated();

        try {
            $code_organi = substr((string) Str::uuid(), 0, 10);
            $code_scanneur = substr((string) Str::uuid(), 0, 8);
            $email_scanneur = uniqid() . '@gmail.com';

            $evenement = DB::transaction(function () use ($validated, $code_organi, $code_scanneur, $email_scanneur) {
                $userOrganisateur = User::create([
                    'email' => $validated['email_organisateur'],
                    'name' => $validated['nom_organisateur'],
                    'password' => Hash::make($code_organi),
                    'role' => 'organisateur',
                ]);

                $organisateur = Organisateur::create([
                    'user_id' => $userOrganisateur->id,
                    'telephone' => $validated['telephone'],
                ]);

                $userScanneur = User::create([
                    'email' => $email_scanneur,
                    'name' => 'Scanneur',
                    'password' => Hash::make($code_scanneur),
                    'role' => 'scanneur',
                ]);

                $scanneur = Scanneur::create([
                    'user_id' => $userScanneur->id,
                ]);

                $evenement = Evenement::create([
                    'nom' => $validated['nom_evenement'],
                    'url_evenement' => $this->generateUniqueEventSlug($validated['nom_evenement']),
                    'organisateur_id' => $organisateur->id,
                    'scanneur_id' => $scanneur->id,
                    'adresse' => $validated['adresse'],
                    'salle' => $validated['salle'],
                    'date_debut' => Carbon::parse($validated['date_debut'] . ' ' . $validated['heure_debut']),
                    'date_fin' => Carbon::parse($validated['date_fin'] . ' ' . $validated['heure_fin']),
                    'heure_debut' => $validated['heure_debut'],
                    'heure_fin' => $validated['heure_fin'],
                    'statut' => 'encours',
                ]);

                $photoAffiche = $validated['photo_affiche'];
                $imagePath = $photoAffiche->store('affiches', 'public');

                Ressource::create([
                    'nom_artiste' => $validated['nom_artiste'],
                    'phrase_accroche' => $validated['acroche'],
                    'a_propos' => $validated['a_propos'],
                    'photo_affiche' => $imagePath,
                    'evenement_id' => $evenement->id,
                ]);

                $ticketCount = 0;
                foreach ($validated['ticket_type_id'] as $index => $typeId) {
                    $typeId = (int) $typeId;
                    $quantite = (int) (
                        $validated['quantite'][$typeId]
                        ?? $validated['quantite'][$index]
                        ?? 0
                    );
                    $prix = (float) (
                        $validated['prix'][$typeId]
                        ?? $validated['prix'][$index]
                        ?? 0
                    );
                    $devise = strtoupper((string) (
                        $validated['devise'][$typeId]
                        ?? $validated['devise'][$index]
                        ?? 'CDF'
                    ));

                    if ($quantite > 0 && $prix > 0) {
                        EvenementTypeBillet::create([
                            'evenement_id' => $evenement->id,
                            'type_billet_id' => $typeId,
                            'nombre_billet' => $quantite,
                            'prix_unitaire' => $prix,
                            'devise' => $devise,
                        ]);
                        $ticketCount++;
                    }
                }

                if ($ticketCount === 0) {
                    throw ValidationException::withMessages([
                        'ticket_type_id' => 'Ajoutez au moins un type de billet avec une quantite et un prix superieurs a 0.',
                    ]);
                }

                return $evenement;
            });
            
             try {
                
                Mail::to($validated['email_organisateur'])->send(new EnvoiMotDePasseMail(
                    $validated['nom_organisateur'],
                    $validated['email_organisateur'],
                    $code_organi,
                    env('ACHAT_URL', 'https://kimiaticket.com') . "/" . $evenement->url_evenement,
                    $email_scanneur,
                    $code_scanneur
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

    private function generateUniqueEventSlug(string $name): string
    {
        $baseSlug = Str::slug($name);
        $baseSlug = $baseSlug !== '' ? $baseSlug : 'evenement';

        $slug = $baseSlug;
        $counter = 2;

        while (Evenement::where('url_evenement', $slug)->exists()) {
            $slug = $baseSlug . '-' . $counter;
            $counter++;
        }

        return $slug;
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
    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'nom' => 'nullable|string|max:255',
            'date_debut' => 'nullable|date',
            'date_fin' => 'nullable|date|after_or_equal:date_debut',
            'adresse' => 'nullable|string|max:255',
            'salle' => 'nullable|string|max:255',
            'url_evenement' => 'nullable|string|max:255',
            'photo_affiche' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:5120',
        ]);

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


   public function updateStatus(Request $request, $id)
{
    $evenement = Evenement::findOrFail($id);
    $evenement->statut = $request->statut;
    $evenement->save();

    return redirect()->back()->with('success', 'Statut de l’événement mis à jour avec succès.');
}

}
