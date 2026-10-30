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

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use App\Services\EvenementCreationService;
use App\Services\NotificationDispatchService;
use App\Services\EvenementMailService;

class EvenementController extends Controller
{
    
     
   public function index(Request $request)
    {
        $search = trim((string) $request->query('q', ''));
        $status = (string) $request->query('statut', '');
        $typeEvenementId = $request->query('type_evenement_id');

        $evenementsQuery = Evenement::with(['organisateur.user', 'typeBillets', 'billets', 'typeEvenement', 'ressource'])
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($subQuery) use ($search) {
                    $subQuery->where('nom', 'like', '%' . $search . '%')
                        ->orWhere('url_evenement', 'like', '%' . $search . '%')
                        ->orWhere('adresse', 'like', '%' . $search . '%')
                        ->orWhere('salle', 'like', '%' . $search . '%')
                        ->orWhereHas('organisateur.user', function ($userQuery) use ($search) {
                            $userQuery->where('name', 'like', '%' . $search . '%')
                                ->orWhere('email', 'like', '%' . $search . '%');
                        });
                });
            })
            ->when($status !== '', function ($query) use ($status) {
                $query->where('statut', $status);
            })
            ->when(!empty($typeEvenementId), function ($query) use ($typeEvenementId) {
                $query->where('type_evenement_id', (int) $typeEvenementId);
            })
            ->latest();

        $evenements = $evenementsQuery->paginate(10)->withQueryString();

        $typeEvenements = TypeEvenement::orderBy('nom_type')->get(['id', 'nom_type']);

        $evenementsEncours= Evenement::where('statut', 'encours')
        ->with(['organisateur.user', 'typeBillets'])
        ->latest()
        ->get()->count();

         $evenementsPasse= Evenement::where('statut', 'ferme')
        ->with(['organisateur.user', 'typeBillets'])
        ->latest()
        ->get()->count();

        return view('evenements.showAll', compact(
            'evenements',
            'evenementsEncours',
            'evenementsPasse',
            'typeEvenements',
            'search',
            'status',
            'typeEvenementId'
        ));
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
    public function store(
        \App\Http\Requests\StoreEvenementRequest $request,
        EvenementCreationService $evenementCreationService,
        EvenementMailService $evenementMailService,
        NotificationDispatchService $notificationDispatch
    )
    {
        $validated = $request->validated();

        try {
            $creation = $evenementCreationService->create($validated, true);
            $evenement = $creation['evenement'];
            $mailEnvoye = $evenementMailService->envoyerApresCreation($creation);

            $message = $creation['organisateur_existant']
                ? 'Votre événement a été créé avec succès. Il a été rattaché au compte organisateur existant.'
                : 'Votre événement a été créé avec succès. Le compte organisateur a été créé avec un mot de passe temporaire.';

            $message .= $mailEnvoye
                ? ' Les informations ont été envoyées par e-mail à l’organisateur.'
                : ' Le mail n\'a pas pu être envoyé : vous pouvez le renvoyer depuis le tableau.';

            try {
                $notificationDispatch->notifyOrganizer(
                    $evenement,
                    'Nouvel événement créé',
                    sprintf("Bonjour %s,\n\nL’événement « %s » vient d’être créé sur la plateforme.", $evenement->organisateur?->user?->name ?? '', $evenement->nom),
                    'event_created',
                    'event-created-' . $evenement->id
                );
            } catch (\Throwable $exception) {
                Log::error('Échec de la notification de création d’événement', [
                    'evenement_id' => $evenement->id,
                    'error' => $exception->getMessage(),
                ]);
            }

            $response = redirect()->route('evenements.index')->with('success', $message);

            if ($creation['scanneur_email'] !== null && $creation['scanneur_code'] !== null) {
                $response->with('scanneur_credentials', [
                    'evenement' => $evenement->nom,
                    'email' => $creation['scanneur_email'],
                    'mot_de_passe' => $creation['scanneur_code'],
                ]);
            }

            return $response;
        } catch (ValidationException $e) {
            return redirect()->back()->withErrors($e->errors())->withInput();
        } catch (\Throwable $th) {
            Log::error('Erreur creation evenement', [
                'payload_keys' => array_keys($validated ?? []),
                'error_message' => $th->getMessage(),
                'exception' => $th,
            ]);

            return redirect()->back()->withInput()->with('error', 'Une erreur est survenue lors de la création de l\'événement.');
        }
    }

    /**
     * Indique au formulaire de création si l’e-mail saisi correspond à un organisateur existant,
     * afin de ne pas redemander ses informations.
     */
    public function lookupOrganisateur(Request $request)
    {
        $user = EvenementCreationService::findUserByEmail((string) $request->query('email', ''));

        if (!$user) {
            return response()->json(['exists' => false]);
        }

        if ($user->role !== 'organisateur') {
            return response()->json([
                'exists' => false,
                'conflict' => true,
                'message' => 'Cette adresse e-mail est déjà utilisée par un compte qui n\'est pas un organisateur.',
            ]);
        }

        $organisateur = $user->organisateur;

        return response()->json([
            'exists' => true,
            'name' => $user->name,
            'telephone' => $organisateur?->telephone,
            'evenements_count' => $organisateur ? $organisateur->evenements()->count() : 0,
        ]);
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
    public function update(UpdateEvenementRequest $request, $id, NotificationDispatchService $notificationDispatch)
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

            try {
                $notificationDispatch->notifyEventBuyers(
                    $evenement,
                    'Mise à jour de votre événement',
                    sprintf("Bonjour,\n\nLes informations de l’événement « %s » ont été mises à jour. Consultez vos informations d’achat pour les détails.", $evenement->nom),
                    'event_updated',
                    'event-updated-' . $evenement->id . '-' . (string) Str::uuid()
                );
            } catch (\Throwable $exception) {
                Log::error('Échec de la notification de mise à jour d’événement', [
                    'evenement_id' => $evenement->id,
                    'error' => $exception->getMessage(),
                ]);
            }

            return redirect()->back()->with('success', 'Evenement modifie avec succes.');
        } catch (\Throwable $th) {
            Log::error('Erreur mise a jour evenement', [
                'evenement_id' => $id,
                'error_message' => $th->getMessage(),
                'exception' => $th,
            ]);

            return redirect()->back()->with('error', 'Une erreur est survenue lors de la mise à jour de l\'événement.');
        }
        
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id, NotificationDispatchService $notificationDispatch)
    {
          try {
        $evenement = Evenement::with('typeBillets')->findOrFail($id);
        try {
            $notificationDispatch->notifyEventBuyers(
                $evenement,
                'Annulation de votre événement',
                sprintf("Bonjour,\n\nL’événement « %s » a été annulé. Veuillez contacter l’organisateur pour toute question.", $evenement->nom),
                'event_cancelled',
                'event-cancelled-' . $evenement->id
            );
        } catch (\Throwable $exception) {
            Log::error('Échec de la notification d’annulation d’événement', [
                'evenement_id' => $evenement->id,
                'error' => $exception->getMessage(),
            ]);
        }
        $evenement->typeBillets()->detach();
        $evenement->delete();
        return redirect()->route('evenements.index')->with('success', 'Événement supprimé avec succès.');

    } catch (\Exception $e) {
        return redirect()->route('evenements.index')->with('error', 'Une erreur est survenue lors de la suppression.');
    }
    }


    public function updateStatus(UpdateEvenementStatusRequest $request, $id, NotificationDispatchService $notificationDispatch)
{
     $validated = $request->validated();
    $evenement = Evenement::findOrFail($id);
        $previousStatus = $evenement->statut;
         $evenement->statut = $validated['statut'];
        $evenement->save();

        if ($previousStatus !== $evenement->statut) {
            try {
                $notificationDispatch->notifyEventBuyers(
                    $evenement,
                    'Changement de statut de votre événement',
                    sprintf("Bonjour,\n\nLe statut de l’événement « %s » est désormais « %s ».", $evenement->nom, $evenement->statut),
                    'event_status_changed',
                    'event-status-' . $evenement->id . '-' . (string) Str::uuid()
                );
            } catch (\Throwable $exception) {
                Log::error('Échec de la notification de changement de statut', [
                    'evenement_id' => $evenement->id,
                    'error' => $exception->getMessage(),
                ]);
            }
        }

        return redirect()->back()->with('success', 'Statut de l’événement mis à jour avec succès.');
    }

    public function resendMail($id, EvenementMailService $evenementMailService)
    {
        try {
            $evenement = Evenement::with(['organisateur.user', 'scanneur.user'])->findOrFail($id);

            if (!$evenement->organisateur?->user || !$evenement->scanneur?->user) {
                return redirect()->back()->with('error', 'Impossible de renvoyer le mail: organisateur ou scanneur introuvable.');
            }

            // Régénère le mot de passe du scanneur ; celui de l'organisateur uniquement s'il est encore temporaire.
            $evenementMailService->renvoyer($evenement);

            return redirect()->back()->with('success', 'Mail renvoyé avec succès pour l\'événement sélectionné.');
        } catch (\Throwable $th) {
            Log::error('Echec renvoi mail evenement', [
                'evenement_id' => $id,
                'error_message' => $th->getMessage(),
                'exception' => $th,
            ]);

            return redirect()->back()->with('error', 'Le renvoi du mail a échoué. Veuillez réessayer plus tard.');
        }
    }

}
