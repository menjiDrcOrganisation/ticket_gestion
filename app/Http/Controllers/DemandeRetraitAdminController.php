<?php

namespace App\Http\Controllers;

use App\Models\Retrait;
use Illuminate\Http\Request;

class DemandeRetraitAdminController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $dmd_retraits = Retrait::all();
        return view('dmd_retraits.index', compact('dmd_retraits'));
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
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        //
    }
}
