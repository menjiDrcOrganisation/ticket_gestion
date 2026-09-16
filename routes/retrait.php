<?php

use App\Http\Controllers\Web\Organisateur\RetraitController;
use App\Http\Middleware\AuditTrail;
use Illuminate\Support\Facades\Route;

Route::get('/retrait', 
    [RetraitController::class, 'index'])
    ->name('retrait.index')->middleware(['auth']);
Route::post('/retrait', 
    [RetraitController::class, 'store'])
    ->middleware(['auth', AuditTrail::class])
    ->name('retraits.store');
Route::put('/retrait/{retrait}', 
    [RetraitController::class, 'update'])
    ->middleware(['auth', AuditTrail::class])
    ->name('retraits.update');
Route::delete('/retrait/{retrait}', 
    [RetraitController::class, 'destroy'])
    ->middleware(['auth', AuditTrail::class])
    ->name('retraits.destroy');  
Route::get('/retrait/{retrait}/edit', 
    [RetraitController::class, 'updateStatut'])
    ->name('retraits.updateStatut');
Route::get('/retrait/create', 
    [RetraitController::class, 'show'])
    ->name('retraits.show');






