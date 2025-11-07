<?php

use App\Http\Controllers\DemandeEvenementController;
use Illuminate\Support\Facades\Route;

Route::get('/dmd_events', [DemandeEvenementController::class, 'index'])->name('demandeEvenement.index');

Route::post('/dmd_events', [DemandeEvenementController::class, 'store'])->name('demandeEvenement.store');
Route::get('/dmd_events/{demandeEvenement}/edit', [DemandeEvenementController::class, 'edit'])->name('demandeEvenement.edit');
Route::put('/dmd_events/{demandeEvenement}', [DemandeEvenementController::class, 'update'])->name('demandeEvenement.update');
Route::delete('/dmd_events/{demandeEvenement}', [DemandeEvenementController::class, 'destroy'])->name('demandeEvenement.destroy');    


//type d'evenement routes
use App\Http\Controllers\TypeEvenementController;
use App\Http\Controllers\EvenementBilletTypeBilletController;

Route::get('/type_evenements', [TypeEvenementController::class, 'index'])->name('typeEvenement.index');
Route::post('/type_evenements', [TypeEvenementController::class, 'store'])->name('typeEvenement.store');
Route::get('/type_evenements/{typeEvenement}/edit', [TypeEvenementController::class, 'edit'])->name('typeEvenement.edit');
Route::put('/type_evenements/{typeEvenement}', [TypeEvenementController::class, 'update'])->name('typeEvenement.update');
Route::delete('/type_evenements/{typeEvenement}', [TypeEvenementController::class, 'destroy'])->name('typeEvenement.destroy');

Route::post('demandeEvenement/{demandeEvenement}/change-status', [DemandeEvenementController::class, 'changeStatus'])->name('demandeEvenement.changeStatus');



Route::get('achat/billet/', [DemandeEvenementController::class, 'achatBillet'])->name('achats.index');
Route::post('achat/billet/', [DemandeEvenementController::class, 'processAchatBillet'])->name('billets.index');


// Route pour générer le billet PDF
Route::get('resume/', [EvenementBilletTypeBilletController::class,  'index'])->name('billet.generatePDF');

Route::get('achatbillet/{evenementId}', [EvenementBilletTypeBilletController::class,  'achatbillet'])->name('billets.index');



Route::get('dashboard_event/{id}', [EvenementBilletTypeBilletController::class, 'show'])
    ->name('achatbillet.show');

?>