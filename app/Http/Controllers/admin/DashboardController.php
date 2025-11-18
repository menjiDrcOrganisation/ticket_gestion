<?php

namespace App\Http\Controllers\admin;

use App\Models\Evenement;
use App\Models\EvenementBilletTypeBillet;
use App\Models\EvenementTypeBillet;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class DashboardController extends Controller
{

     public function index()
    {
        $evenementsEncours= Evenement::where('statut', 'encours')
        ->with(['organisateur.user', 'typeBillets'])
        ->latest()
        ->get()->count();

         $evenementsPasses= Evenement::where('statut', 'ferme')
        ->with(['organisateur.user', 'typeBillets'])
        ->latest()
        ->get()->count();

    

    return view('dashboard.viewDash', compact(
        'evenementsEncours',
        'evenementsPasses'
    ));
    }
}
