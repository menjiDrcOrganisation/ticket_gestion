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

class EvenementController extends Controller
{
    
     
   public function index()
    {
        $evenements = Evenement::with(['organisateur.user', 'typeBillets'])
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

        try {
            $uuid = Str::uuid(); 
            $code_organi = substr($uuid, 0, 10);

            $uuid = Str::uuid();          
            $code_scanneur = substr($uuid, 0, 8);
        
            $validated = $request->validated(); 
            $email_scanneur=uniqid()
        . '@gmail.com';

            if (!empty($validated['nom_organisateur'])) {

                //organisateur
                $user = User::create(
                     // condition
                    ['email' => $validated['email_organisateur'],
                        'name' => $validated['nom_organisateur'],
                        'password' => Hash::make($code_organi),
                        'role' => 'organisateur',
                    ]
                );

                $organisateur = Organisateur::create([
                    'user_id' => $user->id,
                    'telephone' => $validated['telephone'],
                ]);

                // scanneur

                 $user = User::create(['email' => $email_scanneur,
                        'name' => "Scanneur",
                        'password' => Hash::make($code_scanneur),
                        'role' => 'scanneur',
                    ]
                );

                $scanneur = Scanneur::create([
                    'user_id' => $user->id
                ]);

                

            } else {
                $organisateur = null;
            }

            // Création de l'événement
            $evenement = Evenement::create([
                'nom' => $validated['nom_evenement'],
                'url_evenement' => Str::slug($validated['nom_evenement']),
                'organisateur_id' => $organisateur?->id,
                'scanneur_id' => $scanneur?->id,
                'adresse' => $validated['adresse'],
                'salle' => $validated['salle'],
                'date_debut' => $validated['date_debut'],
                'date_fin' => $validated['date_fin'],
                'heure_debut' => $validated['heure_debut'],
                'heure_fin' => $validated['heure_fin'],
                'statut' => 'encours',
            ]);

            $photo_affiche=$validated['photo_affiche'];
            $image_path = $photo_affiche->store("affiches","public");

            Ressource::create([
                'nom_artiste' => $validated['nom_artiste'],
                'phrase_accroche'=> $validated['acroche'],
                'a_propos'=> $validated['a_propos'],
                'photo_affiche'=>$image_path,
                'evenement_id'=> $evenement->id
            ]);

            // Boucle sur type les billets
            foreach ($validated['ticket_type_id'] as $index => $typeId) {
                $quantite = $validated['quantite'][$index] ?? 0;
                $prix = $validated['prix'][$index] ?? 0;
                $devise = $validated['devise'][$index];

                if ($quantite > 0 && $prix  > 0) {
                    EvenementTypeBillet::create([
                        'evenement_id' => $evenement->id,
                        'type_billet_id' => $typeId,
                        'nombre_billet' => $quantite,
                        'prix_unitaire' => $prix,
                        'devise'=> $devise
                    ]);
                }
            }
             try {
                
               $error= Mail::to($validated['email_organisateur'])->send(new EnvoiMotDePasseMail(
                    $validated['nom_organisateur'],
                    $validated['email_organisateur'],
                    $code_organi,
                    env('ACHAT_URL', 'https://kimiaticket.com')."/".Str::slug($validated['nom_evenement']),
                    $email_scanneur,
                    $code_scanneur
                ));
            
                $message = 'Événement créé avec succès et mail envoyé à l’organisateur.';
            } catch (\Exception $e) {

                $message = 'Événement créé avec succès, mais le mail n’a pas pu être envoyé. Erreur : ' . $e->getMessage();
            }

            return redirect()->route('evenements.index')->with('success', $message);
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
    public function update(Request $request, $id)
    {

         try {
            $evenement =Evenement::find($id);
            $evenement->update($request->all());
    
            return redirect()->back()->with('success', 'Evenement modifier avec  succces.');
        } catch (\Throwable $th) {
            dd($th);
            return redirect()->back()->with('error', 'Erreur lors de la mise à jour de l evenement : ' . $th->getMessage());

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
