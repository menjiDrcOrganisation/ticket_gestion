<?php

use App\Http\Controllers\Web\Admin\DemandeEvenementController;
use App\Http\Middleware\AuditTrail;
use Illuminate\Support\Facades\Route;

Route::get('/dmd_events', [DemandeEvenementController::class, 'index'])->name('demandeEvenement.index');

Route::post('/dmd_events', [DemandeEvenementController::class, 'store'])->middleware(['auth', AuditTrail::class])->name('demandeEvenement.store');
Route::get('/dmd_events/{demandeEvenement}/edit', [DemandeEvenementController::class, 'edit'])->name('demandeEvenement.edit');
Route::put('/dmd_events/{demandeEvenement}', [DemandeEvenementController::class, 'update'])->middleware(['auth', AuditTrail::class])->name('demandeEvenement.update');
Route::delete('/dmd_events/{demandeEvenement}', [DemandeEvenementController::class, 'destroy'])->middleware(['auth', AuditTrail::class])->name('demandeEvenement.destroy');    


//type d'evenement routes
use App\Http\Controllers\Web\Admin\TypeEvenementController;
use App\Http\Controllers\Web\Organisateur\EvenementBilletTypeBilletController;


Route::get('/type_evenements', [TypeEvenementController::class, 'index'])->name('typeEvenement.index');
Route::post('/type_evenements', [TypeEvenementController::class, 'store'])->middleware(['auth', AuditTrail::class])->name('typeEvenement.store');
Route::get('/type_evenements/{typeEvenement}/edit', [TypeEvenementController::class, 'edit'])->name('typeEvenement.edit');
Route::put('/type_evenements/{typeEvenement}', [TypeEvenementController::class, 'update'])->middleware(['auth', AuditTrail::class])->name('typeEvenement.update');
Route::delete('/type_evenements/{typeEvenement}', [TypeEvenementController::class, 'destroy'])->middleware(['auth', AuditTrail::class])->name('typeEvenement.destroy');
Route::post('demandeEvenement/{demandeEvenement}/change-status', [DemandeEvenementController::class, 'changeStatus'])->middleware(['auth', AuditTrail::class])->name('demandeEvenement.changeStatus');


Route::get('achat/billet/', [DemandeEvenementController::class, 'achatBillet'])->name('achats.index');
Route::post('achat/billet/', [DemandeEvenementController::class, 'processAchatBillet'])->name('billets.index');

Route::get('achatbillet/{evenementId}', [EvenementBilletTypeBilletController::class,  'achatbillet'])->name('billets.index');

?>






