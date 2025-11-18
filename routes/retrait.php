<?php

use App\Http\Controllers\RetraitController;
use Illuminate\Support\Facades\Route;

Route::get('/retrait', 
    [RetraitController::class, 'index'])
    ->name('retrait.index')->middleware(['auth']);
Route::post('/retrait', 
    [RetraitController::class, 'store'])
    ->name('retraits.store');
Route::put('/retrait/{retrait}', 
    [RetraitController::class, 'update'])
    ->name('retraits.update');
Route::delete('/retrait/{retrait}', 
    [RetraitController::class, 'destroy'])
    ->name('retraits.destroy');  
Route::get('/retrait/{retrait}/edit', 
    [RetraitController::class, 'updateStatut'])
    ->name('retraits.updateStatut');
Route::get('/retrait/create', 
    [RetraitController::class, 'show'])
    ->name('retraits.show');
