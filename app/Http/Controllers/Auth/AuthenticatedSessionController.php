<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): View
    {
        return view('auth.login');
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();
        $request->session()->regenerate();

        // Vérifier le rôle de l'utilisateur et rediriger
        $user = $request->user();

        if ($user->role === 'admin') {
            return redirect()->route('dashboard.admin.viewDash');
        } elseif ($user->role === 'organisateur') {
            return redirect()->route('dashboard_orginasateur.show');
            
        } elseif ($user->role === 'scanneur') {
            return redirect()->route('dashboard_orginasateur.show');
        } 

    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
