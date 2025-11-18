<?php

use App\Http\Controllers\DemandeRetraitAdminController;
use Illuminate\Support\Facades\Route;

Route::get('/demande-retrait', 
    [DemandeRetraitAdminController::class, 'index'])
    ->name('dmd_retrait.index')->middleware(['auth']);

Route::post('/demande-retrait', 
    [DemandeRetraitAdminController::class, 'store'])
    ->name('dmd_retrait.store');
Route::put('/demande-retrait/{demandeRetrait}', 
    [DemandeRetraitAdminController::class, 'update'])
    ->name('dmd_retrait.update');
Route::delete('/demande-retrait/{demandeRetrait}', 
    [DemandeRetraitAdminController::class, 'destroy'])
    ->name('dmd_retrait.destroy');

Route::patch('/demande-retrait/{id}/statut', 
    [DemandeRetraitAdminController::class, 'updateStatut'])
    ->name('dmd_retrait.updateStatut');
Route::get('/demande-retrait/{id}/edit', 
    [DemandeRetraitAdminController::class, 'show'])
    ->name('dmd_retrait.show');

?>
