<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Mail\EnvoiMotDePasseOublieMail;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Password;
use Illuminate\View\View;
use Pest\Support\Str;

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

        $user = \App\Models\User::where('email', $request->email)->first();

    if (!$user) {
        return back()->withErrors(['email' => 'Cette adresse e-mail est introuvable.']);
    }

    // Générer un token personnalisé
    $token = Str::random(64);

    

    // Construire le lien de réinitialisation
    $resetUrl = url('/reset-password/'.$token.'?email='.$user->email);

    // Envoyer le mail via ton Mailable personnalisé
    Mail::to($user->email)->send(new EnvoiMotDePasseOublieMail(
        $user->email,
        $token,
        $resetUrl,
        [
            'appName' => 'Kimiaticket',
            'expires' => '60 minutes',
            'supportEmail' => 'support@kimiaticket.com',
            'supportPhone' => '+243 99 000 0000',
            'logo' => env('APP_URL').'/assets/logo-email.png',
        ]
    ));
    

    // Retourner le statut (tu peux utiliser un message personnalisé)
    return back()->with('status', 'Un lien de réinitialisation vous a été envoyé par e-mail.');

    }
}
