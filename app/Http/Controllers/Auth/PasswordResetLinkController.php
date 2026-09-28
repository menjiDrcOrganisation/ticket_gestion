<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Mail\EnvoiMotDePasseOublieMail;
use App\Services\NotificationQueueService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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
    

public function store(Request $request, NotificationQueueService $notifications): RedirectResponse
{
    $request->validate([
        'email' => ['required', 'email'],
    ]);

    // Vérifier que l'utilisateur existe
    $user = \App\Models\User::where('email', $request->email)->first();

    if (!$user) {
        return back()->withErrors(['email' => 'Cette adresse e-mail est introuvable.']);
    }

    // Le mail est mis en file d'attente (envoi par le worker). Si un lien est déjà en attente d'envoi
    // pour cet utilisateur, aucun nouveau jeton n'est créé : le premier mail reste valide (pas de doublon).
    $notifications->envoyerMail(
        'auth.reinitialisation_mot_de_passe',
        $user->email,
        'user:'.$user->id.':reinitialisation-mot-de-passe',
        function () use ($user) {
            // 1️⃣ Laravel génère un token sécurisé et l’enregistre dans la base
            $token = Password::createToken($user);

            // 2️⃣ Créer le lien officiel de Laravel
            $resetUrl = url('/reset-password/'.$token.'?email='.$user->email);

            // 3️⃣ Construire TON e-mail personnalisé
            return new EnvoiMotDePasseOublieMail(
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
            );
        },
        $user,
    );

    return back()->with('status', 'Un lien de réinitialisation vous a été envoyé par e-mail.');
}
}
