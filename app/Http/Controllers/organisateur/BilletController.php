<?php

namespace App\Http\Controllers\organisateur;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Billet;
use Illuminate\Support\Facades\Auth;

class BilletController extends Controller
{
    
    /**
     * Show the form for creating a new resource.
     */
        public function index(Request $request)
    {
        try {
            $user = Auth::user();
            $evenementId = $user->organisateur->evenements[0]->id;

            $achats = Billet::with(['type_billet.evenements', 'evenements', 'type_billet'])
            ->whereHas('evenements', function($query) use ($evenementId) {
                $query->where('evenements.id', $evenementId);
            })
            ->paginate(10);



        $totalCDF = $achats->sum(function ($billet)  use ($evenementId)  {
            $prixCDF=0;
            foreach ($billet->type_billet[0]->evenements as  $value) {
                if($value->id==$evenementId && $value->pivot->devise=="CDF" ){
                    $prixCDF=$prixCDF+$value->pivot->prix_unitaire;
                }
            }
            return $prixCDF;
        });

       
        $totalUSD = $achats->sum(function ($billet)  use ($evenementId)  {
            $prixUSD=0;
            foreach ($billet->type_billet[0]->evenements as  $value) {
                if($value->id==$evenementId && $value->pivot->devise=="USD" ){
                    $prixUSD=$prixUSD+$value->pivot->prix_unitaire;
                }
            }
            return  $prixUSD;
        });

        $totalPaye = $totalCDF + $totalUSD;

        return view('organisateurs.achat', compact('achats', 'totalCDF', 'totalUSD', 'totalPaye'));

        } catch (\Throwable $th) {
            return $th;
        }
    }
}
