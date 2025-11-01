<?php

namespace App\Http\Controllers;

use App\Models\Organisateur;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class OrganisateurController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function registerOrganisateur(Request $request)
    {
      try {
        Validator::make($request->all(), [
          'name' => 'required|string|max:255',
          'email' => 'required|string|email|max:255|unique:users',
          'password' => 'required|string|min:8|',
          'telephone' => 'required|string|max:15',
          'role' => 'required|string|in:organisateur',
        ])->validate();
        $UserOrganisateur = User::create([
          'name' => $request->name,
          'email' => $request->email,
          'password' => $request->password,
          'role' => $request->role,
        ])->organisateur()->create([
          'telephone' => $request->telephone,
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
