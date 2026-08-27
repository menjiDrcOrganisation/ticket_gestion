<?php

use App\Http\Controllers\Web\Admin\DemandeRetraitAdminController;
use App\Http\Middleware\AuditTrail;
use Illuminate\Support\Facades\Route;

Route::get('/demande-retrait', 
    [DemandeRetraitAdminController::class, 'index'])
    ->name('dmd_retrait.index')->middleware(['auth']);

Route::post('/demande-retrait', 
    [DemandeRetraitAdminController::class, 'store'])
    ->middleware(['auth', AuditTrail::class])
    ->name('dmd_retrait.store');
Route::put('/demande-retrait/{demandeRetrait}', 
    [DemandeRetraitAdminController::class, 'update'])
    ->middleware(['auth', AuditTrail::class])
    ->name('dmd_retrait.update');
Route::delete('/demande-retrait/{demandeRetrait}', 
    [DemandeRetraitAdminController::class, 'destroy'])
    ->middleware(['auth', AuditTrail::class])
    ->name('dmd_retrait.destroy');

Route::patch('/demande-retrait/{id}/statut', 
    [DemandeRetraitAdminController::class, 'updateStatut'])
    ->middleware(['auth', AuditTrail::class])
    ->name('dmd_retrait.updateStatut');
Route::get('/demande-retrait/{id}/edit', 
    [DemandeRetraitAdminController::class, 'show'])
    ->name('dmd_retrait.show');

?>







