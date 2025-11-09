<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\EvenementController;


Route::get('/test-mail', function () {
    // Simuler des données d’un utilisateur

    try {
        $nom_client = 'Marien Manima';
    $email = 'manimamarien08@gmail.com';
    $mot_de_passe_temporaire = 'ABC12345';

    // Envoi de l’e-mail
    Mail::to($email)->send(new EnvoiMotDePasseMail(
        $nom_client,
        $email,
        $mot_de_passe_temporaire
    ));

    return "E-mail envoyé avec succès à $email";
    } catch (\Throwable $th) {
        throw $th;
    }
    
});


Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});


require __DIR__.'/auth.php';
require __DIR__ . '/evenement.php';
require __DIR__.'/dmd_event.php';
require __DIR__.'/dashboard.php';