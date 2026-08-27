<?php

namespace App\Http\Controllers\Web\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Organisateur\StoreAdminOrganisateurRequest;
use App\Http\Requests\Organisateur\UpdateAdminOrganisateurRequest;
use App\Models\Organisateur;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class OrganisateurController extends Controller
{
    public function index()
    {
        $organisateurs = Organisateur::with('user')->latest()->paginate(10);

        return view('admin.organisateurs.index', compact('organisateurs'));
    }

    public function store(StoreAdminOrganisateurRequest $request)
    {
        $validated = $request->validated();

        DB::transaction(function () use ($validated): void {
            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
                'role' => 'organisateur',
            ]);

            Organisateur::create([
                'user_id' => $user->id,
                'telephone' => $validated['telephone'],
            ]);
        });

        return redirect()->back()->with('success', 'Organisateur créé avec succès.');
    }

    public function update(UpdateAdminOrganisateurRequest $request, Organisateur $organisateur)
    {
        $validated = $request->validated();

        DB::transaction(function () use ($validated, $organisateur): void {
            $organisateur->user->update([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => !empty($validated['password'])
                    ? Hash::make($validated['password'])
                    : $organisateur->user->password,
            ]);

            $organisateur->update([
                'telephone' => $validated['telephone'],
            ]);
        });

        return redirect()->back()->with('success', 'Organisateur mis à jour avec succès.');
    }

    public function destroy(Organisateur $organisateur)
    {
        if ($organisateur->evenements()->exists()) {
            return redirect()->back()->with('error', 'Impossible de supprimer un organisateur lié à des événements.');
        }

        DB::transaction(function () use ($organisateur): void {
            $user = $organisateur->user;
            $organisateur->delete();

            if ($user) {
                $user->delete();
            }
        });

        return redirect()->back()->with('success', 'Organisateur supprimé avec succès.');
    }
}