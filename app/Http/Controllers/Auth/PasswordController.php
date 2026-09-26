<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class PasswordController extends Controller
{
    /**
     * Page de changement obligatoire du mot de passe temporaire.
     */
    public function edit(Request $request): View|RedirectResponse
    {
        if (!$request->user()->must_change_password) {
            return redirect()->route('home');
        }

        return view('auth.change-password');
    }

    /**
     * Update the user's password.
     */
    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validateWithBag('updatePassword', [
            'current_password' => ['required', 'current_password'],
            'password' => ['required', Password::defaults(), 'confirmed', 'different:current_password'],
        ]);

        $user = $request->user();
        $etaitTemporaire = (bool) $user->must_change_password;

        $user->update([
            'password' => Hash::make($validated['password']),
            'must_change_password' => false,
        ]);

        if ($etaitTemporaire) {
            return redirect()->route('home')->with('success', 'Votre mot de passe a été modifié avec succès.');
        }

        return back()->with('status', 'password-updated');
    }
}
