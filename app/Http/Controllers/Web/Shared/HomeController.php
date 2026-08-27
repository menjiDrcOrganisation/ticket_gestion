<?php

namespace App\Http\Controllers\Web\Shared;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class HomeController extends Controller
{
     public function home(Request $request)
    {
        try {
         $user = $request->user();

            if ($user->role === 'admin') {
                return redirect()->route('dashboard.admin.viewDash');
            } elseif ($user->role === 'organisateur') {
                return redirect()->route('dashboard_orginasateur.show');
            } 
         } catch (\Throwable $th) {
           
        }
        
    }
}
