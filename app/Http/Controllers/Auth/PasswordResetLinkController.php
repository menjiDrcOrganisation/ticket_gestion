<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Mail\EnvoiMotDePasseOublieMail;
use App\Services\EmailDeliveryService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
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
    public function store(Request $request, EmailDeliveryService $emailDelivery): RedirectResponse
    {
        $request->validate([
            'email' => ['required', 'email'],
        ]);

        $user = \App\Models\User::where('email', $request->email)->first();

        if (! $user) {
            return back()->with('status', 'Si un compte correspond à cette adresse, un lien de réinitialisation lui sera envoyé.');
        }

        $token = Password::createToken($user);
        $resetUrl = route('password.reset', ['token' => $token, 'email' => $user->email]);

        $emailDelivery->send(new EnvoiMotDePasseOublieMail(
            $resetUrl,
            [
                'expires' => '60 minutes',
            ]
        ), $user->email);

        return back()->with('status', 'Si un compte correspond à cette adresse, un lien de réinitialisation lui sera envoyé.');
    }
}
