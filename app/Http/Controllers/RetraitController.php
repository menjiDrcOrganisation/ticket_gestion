<?php

namespace App\Http\Controllers;

use App\Models\Admin;
use App\Models\Retrait;
use Illuminate\Http\Request;

class RetraitController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
         
        $userOrganisateur  = auth()->user()->admin;
        dd($userOrganisateur->id);
        $retraits = Retrait::with('organisateur')->where('organisateur_id', $userOrganisateur->id)->get();
        return view('retraits.index', compact('retraits'));
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
    public function show(Retrait $retrait)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Retrait $retrait)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Retrait $retrait)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Retrait $retrait)
    {
        //
    }
}
