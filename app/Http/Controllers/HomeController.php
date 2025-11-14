<?php

namespace App\Http\Controllers;
use App\Http\Requests\Auth\LoginRequest;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class HomeController extends Controller
{
     public function home(LoginRequest $request)
    {
        try {
         $user = $request->user();

            if ($user->role === 'admin') {
                return redirect()->route('dashboard_admin.show');
            } elseif ($user->role === 'organisateur') {
                return redirect()->route('dashboard_orginasateur.show');
            } 
         } catch (\Throwable $th) {
           
        }
        
    }
}
