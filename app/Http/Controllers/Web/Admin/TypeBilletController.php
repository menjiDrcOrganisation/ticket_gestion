<?php

namespace App\Http\Controllers\Web\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\TypeBillet\StoreTypeBilletRequest;
use App\Http\Requests\TypeBillet\UpdateTypeBilletRequest;
use App\Models\TypeBillet;
use Illuminate\Http\Request;

class TypeBilletController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $search = trim((string) $request->query('q', ''));

        $typeBillets = TypeBillet::query()
            ->when($search !== '', function ($query) use ($search) {
                $query->where('nom_type', 'like', '%' . $search . '%');
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();
        
        return view('type_billets.index', compact('typeBillets', 'search'));

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
    public function store(StoreTypeBilletRequest $request)
    {
        try {
            $validatedData = $request->validated();

            $typeBillet = TypeBillet::create($validatedData);

            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Type de billet cree avec succes.',
                    'data' => [
                        'id' => $typeBillet->id,
                        'nom_type' => $typeBillet->nom_type,
                    ],
                ], 201);
            }

            return redirect()->back()->with('success', 'Type de billet créé avec succès.');
        } catch (\Throwable $th) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Erreur lors de la creation du type de billet.',
                ], 500);
            }

            return redirect()->back()->with('error', 'Erreur lors de la création du type de billet : ' . $th->getMessage());

        }
    }

    /**
     * Display the specified resource.
     */
    public function show(TypeBillet $typeBillet)
    {
        
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(TypeBillet $typeBillet)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateTypeBilletRequest $request, TypeBillet $typeBillet)
    {
        try {
            $validatedData = $request->validated();
    
            $typeBillet->update($validatedData);
    
            return redirect()->back()->with('success', 'Type de billet mis à jour avec succès.');
        } catch (\Throwable $th) {
            return redirect()->back()->with('error', 'Erreur lors de la mise à jour du type de billet : ' . $th->getMessage());

        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(TypeBillet $typeBillet)
    {
        try {
            $typeBillet->delete();
    
            return redirect()->back()->with('success', 'Type de billet supprimé avec succès.');
        } catch (\Throwable $th) {
            return redirect()->back()->with('error', 'Erreur lors de la suppression du type de billet : ' . $th->getMessage());

        }
    }
}
