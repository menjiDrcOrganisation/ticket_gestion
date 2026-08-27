<?php

namespace App\Http\Controllers\Web\Organisateur;

use App\Http\Controllers\Controller;
use App\Http\Requests\Organisateur\RegisterOrganisateurRequest;
use App\Models\Organisateur;
use App\Models\User;
use Illuminate\Http\Request;

class OrganisateurController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function registerOrganisateur(RegisterOrganisateurRequest $request)
    {
      try {
        $validated = $request->validated();
        $UserOrganisateur = User::create([
          'name' => $validated['name'],
          'email' => $validated['email'],
          'password' => $validated['password'],
          'role' => $validated['role'],
        ])->organisateur()->create([
          'telephone' => $validated['telephone'],
        ]); 
        return response()->json([
          'message' => 'Organisateur creer avec  succes',
          'data' => $UserOrganisateur
        ], 201);  

      } catch (\Throwable $th) {
        
      }  
    }
    public function index()
    {
        //
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
    public function show(Organisateur $organisateur)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Organisateur $organisateur)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Organisateur $organisateur)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Organisateur $organisateur)
    {
        //
    }
}
