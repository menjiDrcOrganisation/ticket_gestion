<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Mail\EnvoiMotDePasseOublieMail;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Password;
use Illuminate\View\View;

class PasswordResetLinkController extends Controller
{
    /**
     * Display the password reset link request view.
     */
    public function create(): View
    {
        return view('auth.forgot-password');
    }

    /**
     * Handle an incoming password reset link request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'email' => ['required', 'email'],
        ]);

        // Vérifier que l'utilisateur existe
        $user = \App\Models\User::where('email', $request->email)->first();

        if (! $user) {
            return back()->with(
                'status',
                'Si un compte correspond à cette adresse, un lien de réinitialisation lui sera envoyé.'
            );
        }

        // 1️⃣ Laravel génère un token sécurisé et l’enregistre dans la base
        $token = Password::broker('users')->createToken($user);

        $resetUrl = url('/reset-password/'.$token.'?email='.urlencode($user->email));

        // 3️⃣ Envoyer TON e-mail personnalisé
        Mail::to($user->email)->send(new EnvoiMotDePasseOublieMail(
            $user->email,
            $token,
            $resetUrl,
            [
                'appName' => 'Kimiaticket',
                'expires' => '60 minutes',
                'supportEmail' => 'support@kimiaticket.com',
                'supportPhone' => '+243 847 473 745',
                'logo' => env('APP_URL').'/assets/img/logo.png',
            ]
        ));

        return back()->with('status', 'Un lien de réinitialisation vous a été envoyé par e-mail.');
    }
}
