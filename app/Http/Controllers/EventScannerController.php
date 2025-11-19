<?php

namespace App\Http\Controllers;

use App\Models\Evenement;
use Illuminate\Http\Request;

class EventScannerController extends Controller
{
public function index()
    {

        $organisateur = auth()->user()->organisateur;
         
       $evenement = Evenement::with(['organisateur.user', 'typeBillets', 'scanneur'])
        ->where('organisateur_id', $organisateur->id)
        ->first();



       
       
        return view('event_scanner.index', compact('evenement'));
    }
}
